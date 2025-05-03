<?php
namespace App\Models;

use PDO;
use PDOException;
use App\DatabaseTeachingReport;
use App\DatabaseUsers;

class Subject
{
    private $pdo;
    private $dbUsers;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->dbUsers = new DatabaseUsers(); // สำหรับเชื่อมต่อ phichaia_student
    }

    public function getAllByTeacher($teacher_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM subjects WHERE created_by = ?");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    public function getAllByTeacherWithUsername($teacher_id)
    {

        // ดึงข้อมูลรายวิชาทั้งหมดที่สร้างโดยครูคนนี้
        $stmt = $this->pdo->prepare("SELECT * FROM subjects WHERE created_by = ?");
        $stmt->execute([$teacher_id]);
        $subjects = $stmt->fetchAll();


        // ดึงชื่อครูจากฐานข้อมูล phichaia_student.teacher สำหรับแต่ละ subject
        $pdoUsers = $this->dbUsers->getPDO();
        foreach ($subjects as &$subject) {
            $stmtT = $pdoUsers->prepare("SELECT Teach_name FROM teacher WHERE Teach_id = ?");
            $stmtT->execute([$subject['created_by']]);
            $teacher = $stmtT->fetch();
            if ($teacher) {
                $subject['username'] = $teacher['Teach_name'];
            } else {
                $subject['username'] = '-';
            }
        }
        return $subjects;
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO subjects (name, code, level, subject_type, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['code'],
            $data['level'],
            $data['subject_type'],
            $data['status'],
            $data['created_by']
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("UPDATE subjects SET name=?, code=?, level=?, subject_type=?, status=? WHERE id=?");
        return $stmt->execute([
            $data['name'],
            $data['code'],
            $data['level'],
            $data['subject_type'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM subjects WHERE id=?");
        return $stmt->execute([$id]);
    }
}
