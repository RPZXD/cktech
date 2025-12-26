<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TermPee.php';

class LoginController
{
    public function login($username, $password, $role)
    {
        $user = User::authenticate($username, $password, $role);
        
        if ($user === 'change_password') {
            // redirect ไปหน้าเปลี่ยนรหัสผ่าน
            $_SESSION['change_password_user'] = $username;
            header('Location: change_password.php');
            exit;
        }
        
        if ($user) {
            // Common session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            
            if ($role === 'นักเรียน') {
                // Student session
                $_SESSION['Student_login'] = $user['Stu_id'];
                $_SESSION['student_name'] = $user['Stu_pre'] . $user['Stu_name'] . ' ' . $user['Stu_sur'];
                $_SESSION['user'] = [
                    'Stu_id' => $user['Stu_id'],
                    'Stu_pre' => $user['Stu_pre'],
                    'Stu_name' => $user['Stu_name'],
                    'Stu_sur' => $user['Stu_sur'],
                    'Stu_major' => $user['Stu_major'],
                    'Stu_room' => $user['Stu_room'],
                    'Stu_picture' => $user['Stu_picture'],
                ];
            } else if ($role === 'admin') {
                // Admin session
                $_SESSION['Admin_login'] = $user['Teach_id'];
                $_SESSION['admin_name'] = $user['Teach_name'];
                $_SESSION['Teacher_login'] = $user['Teach_id']; // Also set for compatibility
                $_SESSION['teacher_name'] = $user['Teach_name'];
                $_SESSION['user'] = [
                    'Teach_id' => $user['Teach_id'],
                    'Teach_name' => $user['Teach_name'],
                    'role_edoc' => $user['role_edoc'] ?? $user['role_ckteach'] ?? '',
                    'Teach_photo' => $user['Teach_photo'],
                    'Teach_major' => $user['Teach_major'],
                ];
            } else if ($role === 'เจ้าหน้าที่') {
                // Officer session
                $_SESSION['Officer_login'] = $user['Teach_id'];
                $_SESSION['officer_name'] = $user['Teach_name'];
                $_SESSION['user'] = [
                    'Teach_id' => $user['Teach_id'],
                    'Teach_name' => $user['Teach_name'],
                    'role_edoc' => $user['role_edoc'] ?? $user['role_ckteach'] ?? '',
                    'Teach_photo' => $user['Teach_photo'],
                    'Teach_major' => $user['Teach_major'],
                ];
            } else {
                // Teacher / หัวหน้ากลุ่มสาระ / ผู้บริหาร session
                $_SESSION['Teacher_login'] = $user['Teach_id'];
                $_SESSION['teacher_name'] = $user['Teach_name'];
                $_SESSION['user'] = [
                    'Teach_id' => $user['Teach_id'],
                    'Teach_name' => $user['Teach_name'],
                    'role_edoc' => $user['role_edoc'] ?? $user['role_ckteach'] ?? '',
                    'Teach_photo' => $user['Teach_photo'],
                    'Teach_major' => $user['Teach_major'],
                ];
            }
            
            // เพิ่มเก็บ term pee ลง session
            try {
                $termPee = \TermPee::getCurrent();
                $_SESSION['term'] = $termPee->term;
                $_SESSION['pee'] = $termPee->pee;
            } catch (\Exception $e) {
                // If TermPee fails, set defaults
                $_SESSION['term'] = 2;
                $_SESSION['pee'] = date('Y') + 543;
            }
            
            return 'success';
        } else {
            return "ชื่อผู้ใช้, รหัสผ่าน หรือบทบาทไม่ถูกต้อง 🚫";
        }
    }
}
