<?php
/**
 * CKTech System - Change Password Router
 * MVC Structure
 */

ob_start();
date_default_timezone_set('Asia/Bangkok');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['change_password_user'] ?? null;
if (!$username) {
    header("Location: login.php");
    exit();
}

// Load dependencies
require_once __DIR__ . '/classes/DatabaseUsers.php';

$swalAlert = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmPassword = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validation logic (English letters + Numbers, min 6, no Thai)
    if (strlen($newPassword) < 6 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || preg_match('/[ก-๙]/u', $newPassword)) {
        $swalAlert = [
            'title' => 'รหัสผ่านไม่ปลอดภัย',
            'text' => 'ต้องมีความยาวอย่างน้อย 6 ตัวอักษร ประกอบด้วยตัวอักษรและตัวเลข และห้ามมีภาษาไทย',
            'icon' => 'error'
        ];
    } else if ($newPassword !== $confirmPassword) {
        $swalAlert = [
            'title' => 'รหัสผ่านไม่ตรงกัน',
            'text' => 'กรุณากรอกรหัสผ่านให้ตรงกันทั้งสองช่อง',
            'icon' => 'error'
        ];
    } else {
        // Success -> Update Database
        try {
            $db = new \App\DatabaseUsers();
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Try updating teacher first
            $stmt = $db->query("UPDATE teacher SET password = :pass, Teach_password = '' WHERE Teach_id = :id OR Teach_name = :id", [
                'pass' => $hashedPassword, 
                'id' => $username
            ]);
            
            if ($stmt->rowCount() <= 0) {
                // Try updating student
                $stmt = $db->query("UPDATE student SET Stu_password = :pass WHERE Stu_id = :id OR Stu_name = :id", [
                    'pass' => $hashedPassword, 
                    'id' => $username
                ]);
            }
            
            if ($stmt->rowCount() > 0) {
                // Clear the temporary session
                unset($_SESSION['change_password_user']);
                
                $swalAlert = [
                    'title' => 'เปลี่ยนรหัสผ่านสำเร็จ',
                    'text' => 'กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่',
                    'icon' => 'success',
                    'redirect' => 'login.php'
                ];
            } else {
                $swalAlert = [
                    'title' => 'เกิดข้อผิดพลาด',
                    'text' => 'ไม่พบข้อมูลผู้ใช้งาน หรือรหัสผ่านเดิมตรงกับของใหม่',
                    'icon' => 'warning'
                ];
            }
        } catch (Exception $e) {
            $swalAlert = [
                'title' => 'ข้อผิดพลาดระบบ',
                'text' => $e->getMessage(),
                'icon' => 'error'
            ];
        }
    }
}

// Prepare data for view
$title = 'เปลี่ยนรหัสผ่าน';

// Include view
include __DIR__ . '/views/auth/change_password.php';

ob_end_flush();
