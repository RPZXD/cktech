<?php

require_once __DIR__ . '/../classes/DatabaseUsers.php';

class User
{
    // เพิ่ม mapping สำหรับ role ที่อนุญาต
    private static $allowedUserRoles = [
        'ครู' => ['T', 'ADM', 'VP', 'OF', 'DIR'],
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
                // ถ้า Stu_password ว่าง ให้ return 'change_password'
                if (empty($student['Stu_password'])) {
                    return 'change_password';
                }
                // เปรียบเทียบรหัสผ่าน (plain text)
                if ($password === $student['Stu_password']) {
                    // เพิ่ม role_edoc = 'STU' เพื่อความสอดคล้อง
                    $student['role_ckteach'] = 'STU';
                    return $student;
                }
            }
            return false;
        }

        $user = $db->getTeacherByUsername($username);

        if ($user) {
            // ถ้า password ว่าง ให้ return 'change_password'
            if (empty($user['password'])) {
                return 'change_password';
            }
            if (
                password_verify($password, $user['password']) &&
                self::roleMatch($user['role_ckteach'], $role)
            ) {
                return $user;
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
