<?php
/**
 * Timetable Page Entry Point
 * MVC Pattern - Uses teacher_app layout
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
$allowedRoles = ['ครู', 'หัวหน้ากลุ่มสาระ', 'ผู้บริหาร', 'admin'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../login.php');
    exit;
}

// Load config
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'];
$pageTitle = 'ตารางสอนของครู';

// Get teacher data
require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

$dbUsers = new DatabaseUsers();
$TeacherData = $dbUsers->getTeacherByUsername($_SESSION['username']);
$teacher_name = $TeacherData['Teach_name'] ?? ($_SESSION['name'] ?? '');
$teacher_major = $TeacherData['Teach_major'] ?? '';
$teacherId = $TeacherData['Teach_id'] ?? ($_SESSION['user']['Teach_id'] ?? null);

// Data fetching via Model
require_once __DIR__ . '/../models/Timetable.php';
use App\Models\Timetable;

$timetableModel = new Timetable();
$rows = $timetableModel->getTeacherTimetable($teacherId);

// Process data for the view
$subjectTypeColors = [
    'พื้นฐาน' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
    'เพิ่มเติม' => 'bg-sky-100 text-sky-800 border-sky-300 dark:bg-sky-900/30 dark:text-sky-400 dark:border-sky-800',
    'กิจกรรมพัฒนาผู้เรียน' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    'อื่นๆ' => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
];

$timetable = [];
$classRoomsList = [];
foreach ($rows as $row) {
    $classRoomsList[$row['class_room']] = true;
    for ($p = $row['period_start']; $p <= $row['period_end']; $p++) {
        $type = $row['subject_type'] ?? 'อื่นๆ';
        $colorClass = $subjectTypeColors[$type] ?? $subjectTypeColors['อื่นๆ'];
        
        $levelText = isset($row['level']) ? 'ม.' . intval($row['level']) : '';
        $display = [
            'code' => $row['code'],
            'name' => $row['subject_name'],
            'level_room' => ($levelText ?? '') . ($row['class_room'] ? '/' . preg_replace('/^ห้อง\s*/u', '', $row['class_room']) : '')
        ];

        $timetable[$row['day_of_week']][$p][$row['class_room']] = [
            'display' => $display,
            'type' => $type,
            'colorClass' => $colorClass,
            'code' => $row['code'],
            'subject_name' => $row['subject_name'],
            'showRoom' => !empty($row['subject_type']),
            'class_room' => $row['class_room']
        ];
    }
}

$classRooms = array_keys($classRoomsList);
sort($classRooms);
$days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
$maxPeriod = 8;

// Capture the view content
ob_start();
include __DIR__ . '/../views/teacher/timetable.php';
$content = ob_get_clean();

// Include the teacher layout
include __DIR__ . '/../views/layouts/teacher_app.php';
