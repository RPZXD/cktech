<?php
/**
 * Department Home Entry Point
 * MVC Pattern - Uses department_app layout
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Allow head of department and admin
$allowedRoles = ['หัวหน้ากลุ่มสาระ', 'admin', 'ผู้บริหาร'];
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../login.php');
    exit;
}

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'];
$pageTitle = 'หน้าหลักหัวหน้ากลุ่มสาระ';

// Get department info from session or DB
$department = $_SESSION['user']['Teach_major'] ?? 'ไม่ระบุกลุ่มสาระ';

// Get teacher data
require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

$dbUsers = new DatabaseUsers();
$TeacherData = $dbUsers->getTeacherByUsername($_SESSION['username']);
$teacher_name = $TeacherData['Teach_name'] ?? ($_SESSION['name'] ?? 'ไม่ระบุชื่อ');

// Capture the view content
ob_start();
include __DIR__ . '/../views/department/index.php';
$content = ob_get_clean();

// Include the department layout
include __DIR__ . '/../views/layouts/department_app.php';
