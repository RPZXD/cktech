<?php
namespace App\Models;

use PDO;
require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

class Student
{
    private $pdo;

    public function __construct()
    {
        $dbUsers = new DatabaseUsers();
        $this->pdo = $dbUsers->getPDO();
    }

    /**
     * ดึงรายชื่อนักเรียนตามห้องเรียน (array ของชื่อห้อง)
     * @param array $rooms เช่น ['ห้อง 1', 'ห้อง 2']
     * @return array
     */
    public function getStudentsByRooms($rooms)
    {
        if (empty($rooms)) return [];
        // สมมติว่าชื่อห้องตรงกับฟิลด์ class_room ในตาราง student
        $in = str_repeat('?,', count($rooms) - 1) . '?';
        $sql = "SELECT Stu_id, CONCAT(Stu_pre,Stu_name, ' ', Stu_sur) AS fullname FROM student WHERE Stu_major IN ($in) ORDER BY Stu_room, Stu_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($rooms);
        return $stmt->fetchAll();
    }

    /**
     * ดึงรายชื่อนักเรียนตามระดับชั้นและห้องเรียน (array ของ ['class' => ..., 'room' => ...])
     * @param array $classRooms เช่น [['class' => '1', 'room' => '1'], ...]
     * @return array
     */
    public function getStudentsByClassAndRooms($classRooms)
    {
        if (empty($classRooms)) return [];
        $where = [];
        $params = [];
        foreach ($classRooms as $cr) {
            // ปรับชื่อฟิลด์ให้ตรงกับฐานข้อมูลจริง
            // สมมติใช้ Stu_level (หรือ Stu_major) แทน Stu_class และ Stu_room
            $where[] = '(Stu_major = ? AND Stu_room = ?)';
            $params[] = $cr['class'];
            $params[] = $cr['room'];
        }
        $sql = "SELECT Stu_id, Stu_major, Stu_room, CONCAT(Stu_pre,Stu_name, ' ', Stu_sur) AS fullname 
                FROM student 
                WHERE (" . implode(' OR ', $where) . ") AND Stu_status = '1'
                ORDER BY Stu_major, Stu_room, Stu_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
