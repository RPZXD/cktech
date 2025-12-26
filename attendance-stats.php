<?php
/**
 * Attendance Statistics Page Entry Point
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/AttendanceStatsController.php';

use App\Controllers\AttendanceStatsController;

$controller = new AttendanceStatsController();
$data = $controller->index();

$pageTitle = $data['pageTitle'];
$overallStats = $data['overallStats'];
$attendanceByStatus = $data['attendanceByStatus'];
$attendanceByMonth = $data['attendanceByMonth'];
$topAbsentStudents = $data['topAbsentStudents'];
$global = $data['global'];

ob_start();
include __DIR__ . '/views/attendance-stats/index.php';
$content = ob_get_clean();

include __DIR__ . '/views/layouts/app.php';
