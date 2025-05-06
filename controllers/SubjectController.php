<?php
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
require_once __DIR__ . '/../classes/DatabaseUsers.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TermPee.php'; // เพิ่มบรรทัดนี้

use App\DatabaseTeachingReport;
use App\DatabaseUsers;
use App\Models\Subject;

$db = new DatabaseTeachingReport();
$dbUsers = new DatabaseUsers();
$pdo = $db->getPDO();
$subjectModel = new Subject($pdo);

// ตรวจสอบ action
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        // รับ teacherId จาก GET ถ้ามี
        $teacher_id = isset($_GET['teacherId']) ? $_GET['teacherId'] : ($_SESSION['user']['Teach_id'] ?? 0);
        $subjects = $subjectModel->getAllByTeacherWithUsername($teacher_id);

        // ดึงคาบสอนแต่ละวิชา
        foreach ($subjects as &$subject) {
            $stmt = $pdo->prepare("SELECT class_room, day_of_week, period_start, period_end FROM subject_classes WHERE subject_id = ?");
            $stmt->execute([$subject['id']]);
            $subject['class_periods'] = $stmt->fetchAll();
        }

        echo json_encode($subjects);
        break;
    case 'detail':
        $subject_id = $_GET['subjectId'] ?? 0;
        // ดึงข้อมูล subject หลัก
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $subject = $stmt->fetch();
        // ดึงข้อมูล subject_classes
        $stmt2 = $pdo->prepare("SELECT class_room, day_of_week, period_start, period_end FROM subject_classes WHERE subject_id = ?");
        $stmt2->execute([$subject_id]);
        $classes = $stmt2->fetchAll();
        echo json_encode([
            'subject' => $subject,
            'classes' => $classes
        ]);
        break;
    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        // ใช้ Teach_id เหมือนกับ list
        $teacher_id = $_SESSION['user']['Teach_id'] ?? 0;
        // 1. เพิ่ม subject พร้อม class_rooms
        $result = $subjectModel->create([
            'name' => $data['name'],
            'code' => $data['code'],
            'level' => $data['level'],
            'subject_type' => $data['subject_type'],
            'status' => $data['status'],
            'created_by' => $teacher_id,
            'class_rooms' => $data['class_rooms'] ?? []
        ]);
        if (is_array($result) && !$result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? '']);
        } else {
            echo json_encode(['success' => true]);
        }
        break;
    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $subjectModel->update($data['id'], $data);
        echo json_encode(['success' => $result]);
        break;
    case 'updateStatus':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE subjects SET status=? WHERE id=?");
        $result = $stmt->execute([$data['status'], $data['id']]);
        echo json_encode(['success' => $result]);
        break;
    case 'delete':
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $subjectModel->delete($data['id']);
        echo json_encode(['success' => $result]);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}



