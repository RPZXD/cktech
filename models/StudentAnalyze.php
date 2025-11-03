<?php
namespace App\Models;

use PDO;

class StudentAnalyze
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO student_analyze (
            subject_id, student_level_room, student_no, prefix, student_firstname, student_lastname, student_phone,
            weight, height, disease, parent_name, live_with, address, parent_phone, favorite_activity, special_skill,
            gpa, last_com_grade, like_subjects, dislike_subjects, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([
            $data['subject_id'],
            $data['student_level_room'],
            $data['student_no'],
            $data['prefix'],
            $data['student_firstname'],
            $data['student_lastname'],
            $data['student_phone'],
            $data['weight'],
            $data['height'],
            $data['disease'],
            $data['parent_name'],
            $data['live_with'],
            $data['address'],
            $data['parent_phone'],
            $data['favorite_activity'],
            $data['special_skill'],
            $data['gpa'],
            $data['last_com_grade'],
            $data['like_subjects'],
            $data['dislike_subjects']
        ]);
    }

    public function getBySubject($subject_id)
    {
        // เลือก * (รวมถึง id)
        $stmt = $this->pdo->prepare("SELECT * FROM student_analyze WHERE subject_id = ? ORDER BY student_level_room, student_no");
        $stmt->execute([$subject_id]);
        return $stmt->fetchAll();
    }

    // ⭐️ ADDED: ฟังก์ชันสำหรับลบข้อมูล
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM student_analyze WHERE id = ?");
        return $stmt->execute([$id]);
    }
}