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
            $teacher_id = $_SESSION['username'] ?? 0;
            // เพิ่ม: รับช่วงวันที่จาก query string
            $date_start = $_GET['date_start'] ?? null;
            $date_end = $_GET['date_end'] ?? null;
            if ($date_start && $date_end) {
                // เรียกใช้ฟังก์ชันใหม่ที่ filter ตามวันที่
                $reports = $reportModel->getAllByTeacherAndDateRange($teacher_id, $date_start, $date_end);
            } else {
                $reports = $reportModel->getAllByTeacher($teacher_id);
            }
            echo json_encode($reports);
            break;
        case 'detail':
            $id = $_GET['id'] ?? 0;
            $report = $reportModel->getById($id);
            echo json_encode($report);
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
            $success = $reportModel->createMultiple($rows, $attendanceLogs);
            echo json_encode(['success' => $success]);
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
