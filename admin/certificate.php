<?php
/**
 * Admin Certificate Report Entry Point
 * MVC Pattern - Routes to Admin Certificate View with Layout
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check session and role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'];
$pageTitle = 'รายงานเกียรติบัตร';

// Prepare user data
$user = [
    'name' => $_SESSION['user']['Teach_name'] ?? $_SESSION['username'] ?? 'ผู้ดูแลระบบ',
    'photo' => $_SESSION['user']['Teach_photo'] ?? '',
    'role' => 'ผู้ดูแลระบบ'
];

// Capture the view content
ob_start();
include __DIR__ . '/../views/admin/certificate.php';
$content = ob_get_clean();

// Include the admin layout
include __DIR__ . '/../views/layouts/admin_app.php';
