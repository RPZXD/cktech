<?php
namespace App\Models;

require_once __DIR__ . '/../classes/DatabaseUsers.php';

class Department
{
    private $dbUsers;

    public function __construct()
    {
        $this->dbUsers = new \App\DatabaseUsers();
    }

    // คืนค่ารายชื่อกลุ่มสาระ (Teach_major) ที่ไม่ว่างและมีครูสถานะปกติ และไม่ใช่ชื่อที่ไม่ต้องการ
    public function getAllDepartments()
    {
        // รายชื่อที่ไม่ต้องการ
        $exclude = [
            'admin',
            'เจ้าหน้าที่งานการเงิน',
            'เจ้าหน้าที่ธุรการ',
            'เจ้าหน้าที่บริหารงานทั่วไป',
            'เจ้าหน้าที่โสตทัศนศึกษา',
            'แม่บ้าน',
            'นักการ',
            'พนักงานขับรถ',
            'นักการภารโรง',
            'ผู้อำนวยการ',
            'รองผู้อำนวยการ'
        ];
        $placeholders = implode(',', array_fill(0, count($exclude), '?'));
        $sql = "SELECT Teach_major AS name 
                FROM teacher 
                WHERE Teach_major IS NOT NULL 
                  AND Teach_major != '' 
                  AND Teach_status = 1
                  AND Teach_major NOT IN ($placeholders)
                GROUP BY Teach_major 
                ORDER BY Teach_major";
        $stmt = $this->dbUsers->getPDO()->prepare($sql);
        $stmt->execute($exclude);
        return $stmt->fetchAll();
    }

    // คืนค่ารายชื่อครูในกลุ่มสาระ
    public function getTeachersByDepartment($department_name)
    {
        $sql = "SELECT Teach_id, Teach_name 
                FROM teacher 
                WHERE Teach_major = :department_name AND Teach_status = 1
                ORDER BY Teach_name";
        $stmt = $this->dbUsers->getPDO()->prepare($sql);
        $stmt->execute(['department_name' => $department_name]);
        return $stmt->fetchAll();
    }
}
