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
        $teacher_id = isset($_GET['teacherId']) ? $_GET['teacherId'] : ($_SESSION['user_id'] ?? 0);
        error_log("DEBUG: SubjectController teacher_id = " . print_r($teacher_id, true));
        $subjects = $subjectModel->getAllByTeacherWithUsername($teacher_id);
        error_log("DEBUG: SubjectController subjects = " . print_r($subjects, true));
        echo json_encode($subjects);
        break;
    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        $teacher_id = $_SESSION['user_id'] ?? 0;
        $result = $subjectModel->create([
            'name' => $data['name'],
            'code' => $data['code'],
            'level' => $data['level'],
            'subject_type' => $data['subject_type'],
            'status' => $data['status'],
            'created_by' => $teacher_id
        ]);
        echo json_encode(['success' => $result]);
        break;
    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $subjectModel->update($data['id'], $data);
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



