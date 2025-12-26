<?php
/**
 * Teacher Index Page Entry Point
 * MVC Pattern - Routes to TeacherIndexController
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// เช็ค session และ role
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

// Allow teachers, department heads, and executives
$allowedRoles = ['ครู', 'หัวหน้ากลุ่มสาระ', 'ผู้บริหาร', 'admin'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../controllers/TeacherIndexController.php';

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$controller = new TeacherIndexController();
$data = $controller->index();

// Extract data for view
$pageTitle = $config['global']['pageTitle'];
$user = $data['user'];
$global = $config['global'];
$guides = $data['guides'];  
$quickStats = $data['quickStats'];

// Capture the view content
ob_start();
include __DIR__ . '/../views/teacher/index.php';
$content = ob_get_clean();

// Include the teacher layout
include __DIR__ . '/../views/layouts/teacher_app.php';
