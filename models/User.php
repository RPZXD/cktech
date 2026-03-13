<?php

require_once __DIR__ . '/../classes/DatabaseUsers.php';

class User
{
    // เพิ่ม mapping สำหรับ role ที่อนุญาต
    private static $allowedUserRoles = [
        'ครู' => ['T', 'ADM', 'VP', 'OF', 'DIR','HOD'],
        'เจ้าหน้าที่' => ['ADM', 'OF'],
        'หัวหน้ากลุ่มสาระ' => ['HOD', 'ADM'],
        'ผู้บริหาร' => ['VP', 'DIR', 'ADM'],
        'admin' => ['ADM'],
        // เพิ่มนักเรียน
        'นักเรียน' => ['STU']
    ];

    public static function authenticate($username, $password, $role)
    {
        $db = new \App\DatabaseUsers();

        if ($role === 'นักเรียน') {
            $student = $db->getStudentByUsername($username);
            if ($student) {
                // ถ้า Stu_password ว่าง (ยังไม่ได้ตั้งรหัสผ่านใหม่) ให้เช็คกับรหัสผ่านเริ่มต้น (Stu_id)
                if (empty($student['Stu_password'])) {
                    if ($password === $student['Stu_id']) {
                        return 'change_password';
                    }
                } else {
                    // เปรียบเทียบรหัสผ่าน (รองรับทั้ง plain text และ hashed)
                    if ($password === $student['Stu_password'] || password_verify($password, $student['Stu_password'])) {
                        $student['role_ckteach'] = 'STU';
                        return $student;
                    }
                }
            }
            return false;
        }

        $user = $db->getTeacherByUsername($username);

        if ($user) {
            // ถ้า password ว่าง (ยังไม่ได้ตั้งรหัสผ่านใหม่) ให้เช็คกับรหัสผ่านเริ่มต้น
            if (empty($user['password'])) {
                // ต้องระบุรหัสผ่านเริ่มต้นให้ถูกต้อง (Teach_id หรือ Teach_password) ถึงจะให้ไปเปลี่ยนรหัสผ่าน
                if ($password === $user['Teach_id'] || (isset($user['Teach_password']) && $password === $user['Teach_password'])) {
                    return 'change_password';
                }
            } else {
                // ถ้ามีรหัสผ่านแบบ hash แล้ว ให้ตรวจสอบด้วย password_verify
                if (
                    password_verify($password, $user['password']) &&
                    self::roleMatch($user['role_ckteach'], $role)
                ) {
                    return $user;
                }
            }
        }
        return false;
    }

    // เพิ่มเมธอด static สำหรับดึงข้อมูลครู
    public static function getTeacherByUsername($username)
    {
        $db = new \App\DatabaseUsers();
        return $db->getTeacherByUsername($username);
    }

    // ตรวจสอบว่า role_edoc ของ user อยู่ในกลุ่ม role ที่เลือก
    private static function roleMatch($role_edoc, $role)
    {
        if (!isset(self::$allowedUserRoles[$role])) {
            return false;
        }
        return in_array($role_edoc, self::$allowedUserRoles[$role]);
    }
}
