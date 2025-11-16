<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/Attendance.php';

use App\Models\Attendance;

try {
    $model = new Attendance();
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    switch ($action) {
        case 'attendance_by_date':
            // params: subject_id, date, rooms (optional: comma-separated or JSON array), teacher_id
            $subjectId = $_GET['subject_id'] ?? ($_POST['subject_id'] ?? 0);
            $date = $_GET['date'] ?? ($_POST['date'] ?? '');
            $roomsRaw = $_GET['rooms'] ?? ($_POST['rooms'] ?? '');
            $teacherId = $_GET['teacher_id'] ?? ($_POST['teacher_id'] ?? null);

            $rooms = [];
            if ($roomsRaw) {
                // try json decode first
                $tmp = json_decode($roomsRaw, true);
                if (is_array($tmp)) $rooms = $tmp;
                else $rooms = array_filter(array_map('trim', explode(',', $roomsRaw)));
            }

            if (!$subjectId || !$date) {
                echo json_encode(['success' => false, 'error' => 'Missing subject_id or date', 'data' => []]);
                exit;
            }

            // Debug helper: if debug=1, return raw teaching_reports rows for the subject+date
            if (isset($_GET['debug']) && ($_GET['debug'] == '1' || $_GET['debug'] === 1)) {
                require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
                $dbT = new \App\DatabaseTeachingReport();
                $pdo = $dbT->getPDO();
                $sql = "SELECT * FROM teaching_reports WHERE subject_id = ? AND report_date = ?";
                $params = [$subjectId, $date];
                if ($teacherId) { $sql .= ' AND teacher_id = ?'; $params[] = $teacherId; }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'debug' => true, 'subject_id' => $subjectId, 'date' => $date, 'teacher_id' => $teacherId, 'reports_found' => count($rows), 'reports' => $rows]);
                exit;
            }

            $data = $model->getAttendanceByDate($subjectId, $date, $rooms, $teacherId);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'calendar_grid':
            // params: subject_id, class_room, month (YYYY-MM), optional teacher_id
            $subjectId = $_GET['subject_id'] ?? ($_POST['subject_id'] ?? 0);
            $classRoom = $_GET['class_room'] ?? ($_POST['class_room'] ?? '');
            $month = $_GET['month'] ?? ($_POST['month'] ?? '');
            $teacherId = $_GET['teacher_id'] ?? ($_POST['teacher_id'] ?? null);
            $month = trim($month);
            if ($month && !preg_match('/^\d{4}-\d{2}$/', $month)) {
                $month = '';
            }
            if (!$subjectId || !$classRoom || !$month) {
                echo json_encode(['success' => false, 'error' => 'กรุณาเลือกวิชา ห้อง และเดือนให้ครบถ้วน']);
                exit;
            }
            $data = $model->getMonthlyGrid($subjectId, $classRoom, $month, $teacherId);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'save_attendance':
            // Accept JSON body with: subject_id, date, teacher_id, rows: [{ student_id?, student_no?, status, class_room, report_id? }]
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true);
            if (!$payload) {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
                exit;
            }
            $subjectId = $payload['subject_id'] ?? null;
            $date = $payload['date'] ?? null;
            $teacherId = $payload['teacher_id'] ?? null;
            $rows = $payload['rows'] ?? [];

            if (!$subjectId || !$date || empty($rows)) {
                echo json_encode(['success' => false, 'error' => 'Missing subject_id, date or rows']);
                exit;
            }

            try {
                $result = $model->saveAttendance($subjectId, $date, $rows, $teacherId);
                echo json_encode(['success' => true, 'result' => $result]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        case 'list':
        default:
            echo json_encode(['success' => true, 'message' => 'Attendance controller active']);
    }

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
