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
        $sqlTeacher = "SELECT Teach_id, Teach_major FROM teacher WHERE Teach_status = 1";
        $teachers = $this->dbUsers->query($sqlTeacher)->fetchAll();

        // เตรียม mapping Teach_id => Teach_major
        $teachMajorMap = [];
        foreach ($teachers as $t) {
            $teachMajorMap[$t['Teach_id']] = $t['Teach_major'];
        }

        // ดึงรายงานจาก teaching_reports
        $sqlReport = "SELECT teacher_id FROM teaching_reports WHERE created_at BETWEEN :start AND :end";
        $reports = $this->dbReport->query($sqlReport, ['start' => $startDate, 'end' => $endDate])->fetchAll();

        // นับจำนวนรายงานแยกตามกลุ่มสาระ
        $departmentCounts = [];
        foreach ($reports as $r) {
            $major = $teachMajorMap[$r['teacher_id']] ?? null;
            if ($major) {
                if (!isset($departmentCounts[$major])) $departmentCounts[$major] = 0;
                $departmentCounts[$major]++;
            }
        }

        // จัดรูปแบบผลลัพธ์
        $result = [];
        foreach ($departmentCounts as $department => $count) {
            $result[] = ['department' => $department, 'count' => $count];
        }
        // เรียงลำดับมากไปน้อย
        usort($result, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        return $result;
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
        $sqlTeacher = "SELECT Teach_id, Teach_name FROM teacher WHERE Teach_status = 1";
        $teachers = $this->dbUsers->query($sqlTeacher)->fetchAll();

        // เตรียม mapping Teach_id => Teach_name
        $teachNameMap = [];
        foreach ($teachers as $t) {
            $teachNameMap[$t['Teach_id']] = $t['Teach_name'];
        }

        // ดึงรายงานจาก teaching_reports
        $sqlReport = "SELECT teacher_id FROM teaching_reports WHERE created_at BETWEEN :start AND :end";
        $reports = $this->dbReport->query($sqlReport, ['start' => $startDate, 'end' => $endDate])->fetchAll();

        // นับจำนวนรายงานแยกตามครู
        $teacherCounts = [];
        foreach ($reports as $r) {
            $name = $teachNameMap[$r['teacher_id']] ?? null;
            if ($name) {
                if (!isset($teacherCounts[$name])) $teacherCounts[$name] = 0;
                $teacherCounts[$name]++;
            }
        }

        // จัดรูปแบบผลลัพธ์
        $result = [];
        foreach ($teacherCounts as $teacher => $count) {
            $result[] = ['teacher' => $teacher, 'count' => $count];
        }
        // เรียงลำดับมากไปน้อย
        usort($result, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        return $result;
    }
}
