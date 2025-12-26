<?php
/**
 * Admin Dashboard Entry Point
 * MVC Pattern - Routes to Admin View with Layout
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to Thailand
date_default_timezone_set('Asia/Bangkok');

// Check session and role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'];
$pageTitle = 'หน้าหลักผู้ดูแลระบบ';

// Prepare user data
$user = [
    'name' => $_SESSION['user']['Teach_name'] ?? $_SESSION['username'] ?? 'ผู้ดูแลระบบ',
    'photo' => $_SESSION['user']['Teach_photo'] ?? '',
    'role' => 'ผู้ดูแลระบบ'
];

// Get quick stats (simplified - can be enhanced with actual Model)
$stats = [
    'total_teachers' => 0,
    'total_reports' => 0,
    'total_subjects' => 0,
    'total_students' => 0
];

// Try to get real stats from database
try {
    $dbReportFile = __DIR__ . '/../classes/DatabaseTeachingReport.php';
    $dbUsersFile = __DIR__ . '/../classes/DatabaseUsers.php';
    
    if (file_exists($dbReportFile) && file_exists($dbUsersFile)) {
        require_once $dbReportFile;
        require_once $dbUsersFile;
        
        // Get teachers count - query directly
        $dbUsers = new \App\DatabaseUsers();
        $pdoUsers = $dbUsers->getPDO();
        
        $stmtTeachers = $pdoUsers->query("SELECT COUNT(*) AS count FROM teacher WHERE Teach_status = 1");
        $stats['total_teachers'] = $stmtTeachers->fetch()['count'] ?? 0;
        
        // Get students count
        $stmtStudents = $pdoUsers->query("SELECT COUNT(*) AS count FROM student WHERE Stu_status = '1'");
        $stats['total_students'] = $stmtStudents->fetch()['count'] ?? 0;
        
        // Get reports count
        $dbReport = new \App\DatabaseTeachingReport();
        $pdoReport = $dbReport->getPDO();
        
        $stmtReports = $pdoReport->query("SELECT COUNT(*) AS count FROM teaching_reports");
        $stats['total_reports'] = $stmtReports->fetch()['count'] ?? 0;
        
        // Get subjects count
        $stmtSubjects = $pdoReport->query("SELECT COUNT(*) AS count FROM subjects");
        $stats['total_subjects'] = $stmtSubjects->fetch()['count'] ?? 0;
    }
} catch (Exception $e) {
    // Use default values if error
} catch (Error $e) {
    // Handle class not found or other errors
}

// Capture the view content
ob_start();
include __DIR__ . '/../views/admin/index.php';
$content = ob_get_clean();

// Include the admin layout
include __DIR__ . '/../views/layouts/admin_app.php';
