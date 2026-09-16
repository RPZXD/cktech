<?php
/**
 * LOVES MODEL Page Entry Point
 * แสดงข้อมูลนวัตกรรม LOVES MODEL โรงเรียนพิชัย
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
$allowedRoles = ['ครู', 'หัวหน้ากลุ่มสาระ', 'ผู้บริหาร', 'admin', 'แอนมิน'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../login.php');
    exit;
}

// โหลดการตั้งค่า
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$global = $config['global'] ?? [];
$pageTitle = "LOVES MODEL";

// ดึงเนื้อหา View
ob_start();
include __DIR__ . '/../views/teacher/loves_model.php';
$content = ob_get_clean();

// รวม Layout ของ Teacher
include __DIR__ . '/../views/layouts/teacher_app.php';
