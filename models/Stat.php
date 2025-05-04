<?php
namespace App\Models;

require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
require_once __DIR__ . '/../classes/DatabaseUsers.php';

class Stat
{
    private $dbReport;
    private $dbUsers;

    public function __construct()
    {
        $this->dbReport = new \App\DatabaseTeachingReport();
        $this->dbUsers = new \App\DatabaseUsers();
    }

    // จำนวนรายงานการสอนแต่ละเดือน (ในช่วงวันที่)
    public function getReportCountByMonth($startDate, $endDate)
    {
        $sql = "SELECT MONTH(report_date) AS month, COUNT(*) AS count
                FROM teaching_reports
                WHERE created_at BETWEEN :start AND :end
                GROUP BY MONTH(report_date)
                ORDER BY month";
        $stmt = $this->dbReport->query($sql, ['start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll();
    }

    // จำนวนรายงานแยกตามกลุ่มสาระ (ในช่วงวันที่)
    public function getReportCountByDepartment($startDate, $endDate)
    {
        // ดึง teacher จาก DatabaseUsers
        $sql = "SELECT t.Teach_major AS department, COUNT(r.id) AS count
                FROM teaching_reports r
                JOIN phichaia_student.teacher t ON r.teacher_id = t.Teach_id
                WHERE r.created_at BETWEEN :start AND :end
                GROUP BY t.Teach_major
                ORDER BY count DESC";
        $stmt = $this->dbReport->query($sql, ['start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll();
    }

    // จำนวนครูทั้งหมด
    public function getTeacherCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM teacher WHERE Teach_status = 1";
        $stmt = $this->dbUsers->query($sql);
        return $stmt->fetch()['count'];
    }

    // จำนวนรายงานทั้งหมด
    public function getReportCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM teaching_reports";
        $stmt = $this->dbReport->query($sql);
        return $stmt->fetch()['count'];
    }

    // จำนวนรายงานแยกตามครู (ในช่วงวันที่)
    public function getReportCountByTeacher($startDate, $endDate)
    {
        // ดึง teacher จาก DatabaseUsers
        $sql = "SELECT t.Teach_name AS teacher, COUNT(r.id) AS count
                FROM teaching_reports r
                JOIN phichaia_student.teacher t ON r.teacher_id = t.Teach_id
                WHERE r.created_at BETWEEN :start AND :end
                GROUP BY t.Teach_id
                ORDER BY count DESC";
        $stmt = $this->dbReport->query($sql, ['start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll();
    }
}
