<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/TeachingReport.php';

use App\Models\TeachingReport;

try {
    $reportModel = new TeachingReport();

    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    switch ($action) {
        case 'list':
            $teacher_id = $_GET['teacher_id'] ?? $_POST['teacher_id'] ?? ($_SESSION['username'] ?? 0);
            $reports = $reportModel->getAllByTeacher($teacher_id);
            echo json_encode($reports);
            break;
        case 'listByTeachers':
            // รับ teacher_ids (comma-separated) และช่วงวันที่
            $teacher_ids = isset($_GET['teacher_ids']) ? explode(',', $_GET['teacher_ids']) : [];
            $week_start = $_GET['week_start'] ?? '';
            $week_end = $_GET['week_end'] ?? '';
            if (empty($teacher_ids) || !$week_start || !$week_end) {
                echo json_encode([]);
                exit;
            }
            // ดึงรายงานของครูในช่วงสัปดาห์นี้
            require_once __DIR__ . '/../models/TeachingReport.php';
            $reportModel = new TeachingReport();
            $pdo = $reportModel->pdo; // ใช้ property ที่มีอยู่แล้ว
            $in = implode(',', array_fill(0, count($teacher_ids), '?'));
            $sql = "SELECT r.*, s.name AS subject_name, s.level
                    FROM teaching_reports r
                    LEFT JOIN subjects s ON r.subject_id = s.id
                    WHERE r.teacher_id IN ($in)
                    AND r.report_date BETWEEN ? AND ?
                    ORDER BY r.teacher_id, r.report_date";
            $stmt = $pdo->prepare($sql);
            $params = array_merge($teacher_ids, [$week_start, $week_end]);
            $stmt->execute($params);
            $reports = $stmt->fetchAll();
            echo json_encode($reports);
            break;
        case 'detail':
            $id = $_GET['id'] ?? 0;
            $report = $reportModel->getById($id);
            // เพิ่มชื่อครูในผลลัพธ์เพื่อให้ frontend แสดงชื่อผู้รายงานได้
            if ($report && isset($report['teacher_id']) && $report['teacher_id'] !== '') {
                // ใช้ DatabaseUsers เพื่อดึงข้อมูลครู (ตาราง teacher)
                require_once __DIR__ . '/../classes/DatabaseUsers.php';
                $dbUsers = new \App\DatabaseUsers();
                $teacher = $dbUsers->getTeacherById($report['teacher_id']);
                $report['teacher_name'] = $teacher && isset($teacher['Teach_name']) ? $teacher['Teach_name'] : '';
            } else {
                $report['teacher_name'] = '';
            }
            echo json_encode($report);
            break;
        case 'attendance_log':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                echo json_encode([]);
                exit;
            }
            // ดึงข้อมูล attendance log ของรายงานนี้จาก model
            $logs = $reportModel->getAttendanceLogByReportId($id);
            echo json_encode($logs);
            break;
        case 'upload_images':
            $result = ['image1' => '', 'image2' => ''];
            foreach (['image1', 'image2'] as $imgKey) {
                if (!empty($_FILES[$imgKey]['name']) && is_uploaded_file($_FILES[$imgKey]['tmp_name'])) {
                    $ext = strtolower(pathinfo($_FILES[$imgKey]['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) continue;
                    $newName = uniqid($imgKey . '_') . '.' . $ext;
                    $uploadPath = __DIR__ . '/../uploads/' . $newName;
                    if (move_uploaded_file($_FILES[$imgKey]['tmp_name'], $uploadPath)) {
                        $result[$imgKey] = $newName;
                    }
                }
            }
            echo json_encode($result);
            break;
        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            $rows = $data['rows'] ?? [];
            $attendanceLogs = $data['attendance_logs'] ?? [];
            // --- เพิ่มการ trim และ validate student_id ---
            $attendanceLogs = array_values(array_filter(array_map(function($log) {
                $log['student_id'] = trim($log['student_id']);
                // กรองเฉพาะ student_id ที่เป็นตัวเลขเท่านั้น
                if ($log['student_id'] !== '' && ctype_digit($log['student_id'])) {
                    return $log;
                }
                return null;
            }, $attendanceLogs)));
            $result = $reportModel->createMultiple($rows, $attendanceLogs);
            if (is_array($result) && isset($result['success']) && $result['success'] === true) {
                echo json_encode(['success' => true]);
            } else {
                $errorMsg = is_array($result) && isset($result['error']) ? $result['error'] : 'ไม่สามารถบันทึกรายงานได้';
                echo json_encode(['success' => false, 'error' => $errorMsg]);
            }
            break;
        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $rows = $data['rows'] ?? [];
            $attendanceLogs = $data['attendance_logs'] ?? [];
            $success = false;
            if ($id && !empty($rows)) {
                require_once __DIR__ . '/../models/TeachingReport.php';
                $reportModel = new TeachingReport();
                $success = $reportModel->updateReport($id, $rows[0], $attendanceLogs);
            }
            echo json_encode(['success' => $success]);
            break;
        case 'delete':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'Missing id']);
                exit;
            }
            require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
            $db = new \App\DatabaseTeachingReport();
            $pdo = $db->getPDO();
            try {
                $pdo->beginTransaction();
                $stmt1 = $pdo->prepare("DELETE FROM teaching_attendance_logs WHERE report_id=?");
                $stmt1->execute([$id]);
                $stmt2 = $pdo->prepare("DELETE FROM teaching_reports WHERE id=?");
                $result = $stmt2->execute([$id]);
                $pdo->commit();
                echo json_encode(['success' => $result]);
            } catch (\PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
