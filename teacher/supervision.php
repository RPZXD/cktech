<?php
/**
 * Supervision Page Entry Point
 * MVC Pattern - Uses teacher_app layout
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check session and role
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

// Allow teachers, department heads, executives, and admin
$allowedRoles = ['ครู', 'หัวหน้ากลุ่มสาระ', 'ผู้บริหาร', 'admin'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../login.php');
    exit;
}

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'];
$pageTitle = 'การนิเทศการสอน';

// Get teacher data
require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

$dbUsers = new DatabaseUsers();
$TeacherData = $dbUsers->getTeacherByUsername($_SESSION['username']);
$teacher_name = $TeacherData['Teach_name'] ?? '';
$teacher_degree = $TeacherData['Teach_HiDegree'] ?? '';
$teacherId = $TeacherData['Teach_id'] ?? null;

// Capture the view content
ob_start();
include __DIR__ . '/../views/teacher/supervision.php';
$content = ob_get_clean();

// Include the teacher layout
include __DIR__ . '/../views/layouts/teacher_app.php';
