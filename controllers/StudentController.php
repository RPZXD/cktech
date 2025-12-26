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
    
    case 'search':
        // สำหรับ Select2 AJAX search
        $search = $_GET['q'] ?? $_GET['search'] ?? '';
        $classLevel = $_GET['class'] ?? '';
        $limit = intval($_GET['limit'] ?? 20);
        
        $students = $studentModel->searchStudents($search, $classLevel, $limit);
        
        // Format for Select2
        $results = [];
        foreach ($students as $student) {
            $results[] = [
                'id' => $student['Stu_id'],
                'text' => $student['fullname'],
                'class' => $student['Stu_major'],
                'room' => $student['Stu_room'],
                // Display format: ชื่อ (ม.X/Y)
                'display' => $student['fullname'] . ' (ม.' . $student['Stu_major'] . '/' . $student['Stu_room'] . ')'
            ];
        }
        
        echo json_encode([
            'results' => $results,
            'pagination' => ['more' => false]
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

