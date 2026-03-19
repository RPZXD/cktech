<?php
/**
 * Student Analysis Entry Point
 * MVC Pattern - Refactored from non-MVC teacher/student_link.php
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
$allowedRoles = ['ครู', 'หัวหน้ากลุ่มสาระ', 'ผู้บริหาร', 'admin', 'แอนมิน'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../login.php');
    exit;
}

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'];
$pageTitle = "วิเคราะห์ผู้เรียนรายบุคคล";

// Teacher Info from session
$teacherId = $_SESSION['user']['Teach_id'] ?? ($_SESSION['username'] ?? null);
$teacherName = $_SESSION['user']['Teach_name'] ?? ($_SESSION['username'] ?? '');
$teacherMajor = $_SESSION['user']['Teach_major'] ?? '';

// Set Base URL for student links
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
// Derive the base URL for the student analyze form
$baseUrl = $protocol . "://" . $host . rtrim(dirname(dirname($uri)), '/\\') . "/student/analyze.php";

// Capture the view content
ob_start();
include __DIR__ . '/../views/teacher/student_link.php';
$content = ob_get_clean();

// Include the teacher layout
include __DIR__ . '/../views/layouts/teacher_app.php';