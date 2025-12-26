<?php
/**
 * Timetable Model
 * MVC Pattern - Model for Teacher Timetable
 */

namespace App\Models;

class Timetable
{
    private $pdo;

    public function __construct()
    {
        require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
        $db = new \App\DatabaseTeachingReport();
        $this->pdo = $db->getPDO();
    }

    /**
     * Get teacher's timetable data
     */
    public function getTeacherTimetable($teacherId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.name AS subject_name, s.code, s.level, sc.class_room, sc.day_of_week, sc.period_start, sc.period_end, s.subject_type
                FROM subjects s
                JOIN subject_classes sc ON s.id = sc.subject_id
                WHERE s.created_by = ? AND s.status = 1
                ORDER BY sc.day_of_week, sc.period_start, sc.class_room
            ");
            $stmt->execute([$teacherId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Timetable Model Error: " . $e->getMessage());
            return [];
        }
    }
}
