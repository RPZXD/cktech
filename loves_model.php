<?php
/**
 * LOVES MODEL Public / Root Entry Point
 * MVC Pattern - แสดงข้อมูลนวัตกรรม LOVES MODEL โรงเรียนพิชัย
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// โหลดการตั้งค่า
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$global = $config['global'] ?? [];
$pageTitle = "LOVES MODEL";

// ดึงเนื้อหา View
ob_start();
include __DIR__ . '/views/teacher/loves_model.php';
$content = ob_get_clean();

// รวม Main Layout ของระบบ
include __DIR__ . '/views/layouts/app.php';
