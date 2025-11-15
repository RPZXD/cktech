<?php
require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
require_once __DIR__ . '/../models/StudentAnalyze.php';

use App\Models\StudentAnalyze;

header('Content-Type: application/json');

$db = new \App\DatabaseTeachingReport();
$pdo = $db->getPDO();
$model = new StudentAnalyze($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // ⭐️ ADDED: ตรวจสอบการลบข้อมูลก่อน
    if (isset($data['action']) && $data['action'] === 'delete') {
        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'error' => 'ไม่พบ ID สำหรับลบ']);
            exit;
        }
        
        $result = $model->delete($data['id']);
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ลบข้อมูลไม่สำเร็จ']);
        }
        exit;
    }
    // ⭐️ END: ตรวจสอบการลบข้อมูล

    // ตรวจสอบข้อมูลที่จำเป็น (สำหรับการสร้าง)
    $required = [
        'subject_id', 'student_level_room', 'student_no', 'prefix', 'student_firstname', 'student_lastname',
        'student_phone', 'weight', 'height', 'disease', 'parent_name', 'live_with', 'address', 'parent_phone',
        'favorite_activity', 'special_skill', 'gpa', 'last_com_grade', 'like_subjects', 'dislike_subjects'
    ];
    foreach ($required as $key) {
        if (!isset($data[$key]) || $data[$key] === '') {
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }
    }

    $result = $model->create($data);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'บันทึกข้อมูลไม่สำเร็จ']);
    }
    exit;
}

// รองรับ GET (optional) สำหรับดึงข้อมูลรายวิชา
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']);
    $result = $model->getBySubject($subject_id);
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);