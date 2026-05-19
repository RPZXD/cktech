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
    public function getStudentsByClassAndRooms($classRooms, $date = null)
    {
        if (empty($classRooms)) return [];
        $where = [];
        $params = [];
        foreach ($classRooms as $cr) {
            $where[] = '(s.Stu_major = ? AND s.Stu_room = ?)';
            $params[] = $cr['class'];
            $params[] = $cr['room'];
        }
        
        if (!empty($date)) {
            $sql = "SELECT s.Stu_id, s.Stu_major, s.Stu_room, CONCAT(s.Stu_pre, s.Stu_name, ' ', s.Stu_sur) AS fullname,
                           a.attendance_status AS care_attendance_status, a.reason AS care_attendance_reason
                    FROM student s 
                    LEFT JOIN student_attendance a ON s.Stu_id = a.student_id AND a.attendance_date = ?
                    WHERE (" . implode(' OR ', $where) . ") AND s.Stu_status = '1'
                    ORDER BY s.Stu_major, s.Stu_room, s.Stu_no ASC";
            array_unshift($params, $date);
        } else {
            $sql = "SELECT s.Stu_id, s.Stu_major, s.Stu_room, CONCAT(s.Stu_pre, s.Stu_name, ' ', s.Stu_sur) AS fullname 
                    FROM student s
                    WHERE (" . implode(' OR ', $where) . ") AND s.Stu_status = '1'
                    ORDER BY s.Stu_major, s.Stu_room, s.Stu_no ASC";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * ค้นหานักเรียนสำหรับ Select2 AJAX
     * @param string $search คำค้นหา
     * @param string $class ระดับชั้น (optional)
     * @param int $limit จำนวนผลลัพธ์สูงสุด
     * @return array
     */
    public function searchStudents($search = '', $class = '', $limit = 20)
    {
        $params = [];
        $where = ["Stu_status = '1'"];
        
        // Search by name
        if (!empty($search)) {
            $where[] = "(CONCAT(Stu_pre, Stu_name, ' ', Stu_sur) LIKE ? OR Stu_id LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        // Filter by class level
        if (!empty($class)) {
            $where[] = "Stu_major = ?";
            $params[] = $class;
        }
        
        // Use intval to ensure limit is integer
        $limit = intval($limit);
        if ($limit <= 0) $limit = 20;
        
        $sql = "SELECT Stu_id, Stu_major, Stu_room, 
                       CONCAT(Stu_pre, Stu_name, ' ', Stu_sur) AS fullname,
                       Stu_no
                FROM student 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY Stu_major, Stu_room, Stu_no ASC
                LIMIT {$limit}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

