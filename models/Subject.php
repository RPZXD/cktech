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
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO subjects (name, code, level, subject_type, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['code'],
                $data['level'],
                $data['subject_type'],
                $data['status'],
                $data['created_by']
            ]);

            $subjectId = $this->pdo->lastInsertId();

            // บันทึก subject_classes แยกแต่ละแถว
            if (!empty($data['class_rooms'])) {
                $stmtClasses = $this->pdo->prepare("INSERT INTO subject_classes (subject_id, class_room, period_start, period_end, day_of_week) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['class_rooms'] as $row) {
                    $stmtClasses->execute([
                        $subjectId,
                        $row['class_room'],
                        $row['period_start'],
                        $row['period_end'],
                        $row['day_of_week']
                    ]);
                }
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function update($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // อัปเดต subject หลัก
            $stmt = $this->pdo->prepare("UPDATE subjects SET name=?, code=?, level=?, subject_type=?, status=? WHERE id=?");
            $stmt->execute([
                $data['name'],
                $data['code'],
                $data['level'],
                $data['subject_type'],
                $data['status'],
                $id
            ]);

            // ลบ subject_classes เดิม
            $stmtDel = $this->pdo->prepare("DELETE FROM subject_classes WHERE subject_id=?");
            $stmtDel->execute([$id]);

            // เพิ่ม subject_classes ใหม่
            if (!empty($data['class_rooms'])) {
                $stmtClasses = $this->pdo->prepare("INSERT INTO subject_classes (subject_id, class_room, period_start, period_end, day_of_week) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['class_rooms'] as $row) {
                    $stmtClasses->execute([
                        $id,
                        $row['class_room'],
                        $row['period_start'],
                        $row['period_end'],
                        $row['day_of_week']
                    ]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->pdo->beginTransaction();

            // ลบ subject_classes ก่อน (child)
            $stmt1 = $this->pdo->prepare("DELETE FROM subject_classes WHERE subject_id=?");
            $stmt1->execute([$id]);

            // ลบ subject (parent)
            $stmt2 = $this->pdo->prepare("DELETE FROM subjects WHERE id=?");
            $result = $stmt2->execute([$id]);

            $this->pdo->commit();
            return $result;
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }
}
