<?php
/**
 * Department Supervision Entry Point
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
$pageTitle = 'การนิเทศการสอน';

$department = $_SESSION['user']['Teach_major'] ?? '';

// Capture the view content
ob_start();
include __DIR__ . '/../views/department/supervision.php';
$content = ob_get_clean();

// Include the department layout
include __DIR__ . '/../views/layouts/department_app.php';