<?php
/**
 * Teaching Report Page Entry Point
 * MVC Pattern - Routes to TeachingReportController
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// เช็ค session และ role
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
$pageTitle = 'บันทึกรายงานการสอน';

// Capture the view content
ob_start();
include __DIR__ . '/../views/teacher/teaching-report.php';
$content = ob_get_clean();

// Include the teacher layout
include __DIR__ . '/../views/layouts/teacher_app.php';
