<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/Student.php';

use App\Models\Student;

$studentModel = new Student();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $subject_id = $_GET['subject_id'] ?? '';
        // rooms ต้องเป็น array ของ object ที่มีทั้ง class และ room
        // ตัวอย่าง: [{"class":"1","room":"1"}, {"class":"1","room":"2"}]
        $rooms = isset($_GET['rooms']) ? json_decode($_GET['rooms'], true) : [];
        $periods = isset($_GET['periods']) ? json_decode($_GET['periods'], true) : [];
        // ดึงนักเรียนตาม class และ room ที่เลือก
        $students = $studentModel->getStudentsByClassAndRooms($rooms);
        echo json_encode($students);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
