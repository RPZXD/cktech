<?php
/**
 * Login Page Entry Point
 * MVC Pattern - Routes to LoginController and View
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to Thailand
date_default_timezone_set('Asia/Bangkok');

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$global = $config['global'];
$pageTitle = 'เข้าสู่ระบบ';

// Load controller
require_once __DIR__ . '/controllers/LoginController.php';

$error = null;
$success = false;
$redirect = null;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $controller = new LoginController();
    $result = $controller->login($username, $password, $role);
    
    if ($result === 'success') {
        $success = true;
        // Determine redirect URL based on role
        switch ($role) {
            case 'ครู':
                $redirect = 'teacher/index.php';
                break;
            case 'หัวหน้ากลุ่มสาระ':
                $redirect = 'department/index.php';
                break;
            case 'ผู้บริหาร':
                $redirect = 'director/index.php';
                break;
            case 'admin':
                $redirect = 'admin/index.php';
                break;
            default:
                $redirect = 'index.php';
        }
    } else {
        $error = $result;
    }
}

// Check for logout message
$logoutMessage = isset($_GET['logout']) && $_GET['logout'] == '1';

// Capture the view content
ob_start();
include __DIR__ . '/views/auth/login.php';
$content = ob_get_clean();

// Include the main layout
include __DIR__ . '/views/layouts/app.php';
