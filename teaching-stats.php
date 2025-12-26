<?php
/**
 * Teaching Statistics Page Entry Point
 * MVC Pattern - Routes to TeachingStatsController
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/TeachingStatsController.php';

use App\Controllers\TeachingStatsController;

$controller = new TeachingStatsController();
$data = $controller->index();

// Extract data for view
$pageTitle = $data['pageTitle'];
$overallStats = $data['overallStats'];
$reportsByMonth = $data['reportsByMonth'];
$reportsBySubject = $data['reportsBySubject'];
$topTeachers = $data['topTeachers'];
$recentReports = $data['recentReports'];
$reportsByDay = $data['reportsByDay'];
$global = $data['global'];

// Capture the view content
ob_start();
include __DIR__ . '/views/teaching-stats/index.php';
$content = ob_get_clean();

// Include the main layout
include __DIR__ . '/views/layouts/app.php';
