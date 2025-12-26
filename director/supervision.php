<?php
/**
 * Director Supervision Entry Point
 * MVC Pattern - Entry point for director supervision page
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ผู้บริหาร') {
    header('Location: ../login.php');
    exit;
}

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'];
$pageTitle = 'การนิเทศการสอน';

// Capture the view content
ob_start();
include __DIR__ . '/../views/director/supervision.php';
$content = ob_get_clean();

// Include the director layout
include __DIR__ . '/../views/layouts/director_app.php';