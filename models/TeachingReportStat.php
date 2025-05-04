<?php
namespace App\Models;

use PDO;

require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
require_once __DIR__ . '/../classes/DatabaseUsers.php';

class TeachingReportStat
{
    private $pdo;
    private $pdoUsers;

    public function __construct()
    {
        $db = new \App\DatabaseTeachingReport();
        $this->pdo = $db->getPDO();
        $dbUsers = new \App\DatabaseUsers();
        $this->pdoUsers = $dbUsers->getPDO();
    }

    // 1. จำนวนรายงานแยกตามครูในกลุ่มสาระ
    public function getReportCountsByTeacher($department)
    {
        // ดึงรายชื่อครูในกลุ่มสาระ
        $sqlTeachers = "SELECT Teach_id, Teach_name FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teachers = $stmtT->fetchAll();

        if (empty($teachers)) return [];

        // เตรียม array สำหรับผลลัพธ์
        $result = [];
        foreach ($teachers as $teacher) {
            // นับจำนวนรายงานของแต่ละครูจากฐาน teaching_reports
            $sqlCount = "SELECT COUNT(*) AS count FROM teaching_reports WHERE teacher_id = ?";
            $stmtC = $this->pdo->prepare($sqlCount);
            $stmtC->execute([$teacher['Teach_id']]);
            $count = $stmtC->fetchColumn();
            $result[] = [
                'Teach_id' => $teacher['Teach_id'],
                'Teach_name' => $teacher['Teach_name'],
                'count' => (int)$count
            ];
        }
        return $result;
    }

    // 2. สถิติขาดเรียน/ป่วย/ลากิจ/กิจกรรม รวมทั้งกลุ่มสาระ
    public function getAbsentStats($department)
    {
        // ดึงรายชื่อครูในกลุ่มสาระ
        $sqlTeachers = "SELECT Teach_id FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teacherIds = array_column($stmtT->fetchAll(), 'Teach_id');
        if (empty($teacherIds)) return ['absent'=>0,'sick'=>0,'personal'=>0,'activity'=>0];

        // ดึง report id ของครูในกลุ่มสาระ
        $in = str_repeat('?,', count($teacherIds) - 1) . '?';
        $sqlReports = "SELECT id FROM teaching_reports WHERE teacher_id IN ($in)";
        $stmtR = $this->pdo->prepare($sqlReports);
        $stmtR->execute($teacherIds);
        $reportIds = array_column($stmtR->fetchAll(), 'id');
        if (empty($reportIds)) return ['absent'=>0,'sick'=>0,'personal'=>0,'activity'=>0];

        // ดึงสถิติการขาด/ป่วย/ลากิจ/กิจกรรม
        $in2 = str_repeat('?,', count($reportIds) - 1) . '?';
        $sql = "SELECT status, COUNT(*) AS count FROM teaching_attendance_logs WHERE report_id IN ($in2) GROUP BY status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($reportIds);
        $result = ['absent'=>0,'sick'=>0,'personal'=>0,'activity'=>0];
        foreach ($stmt->fetchAll() as $row) {
            if ($row['status'] === 'ขาดเรียน') $result['absent'] = (int)$row['count'];
            if ($row['status'] === 'ลาป่วย') $result['sick'] = (int)$row['count'];
            if ($row['status'] === 'ลากิจ') $result['personal'] = (int)$row['count'];
            if ($row['status'] === 'เข้าร่วมกิจกรรม') $result['activity'] = (int)$row['count'];
        }
        return $result;
    }

    // 3. สรุปรายเดือน
    public function getMonthlyStats($department)
    {
        // ดึงรายชื่อครูในกลุ่มสาระ
        $sqlTeachers = "SELECT Teach_id FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teacherIds = array_column($stmtT->fetchAll(), 'Teach_id');
        if (empty($teacherIds)) return [];

        // ดึงรายงานทั้งหมดของครูในกลุ่มสาระ
        $in = str_repeat('?,', count($teacherIds) - 1) . '?';
        $sql = "SELECT 
                    MONTH(report_date) AS month,
                    YEAR(report_date) AS year,
                    COUNT(id) AS count
                FROM teaching_reports
                WHERE teacher_id IN ($in)
                GROUP BY year, month
                ORDER BY year DESC, month DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($teacherIds);
        $monthly = $stmt->fetchAll();

        // ดึงสถิติขาด/ป่วย/ลากิจ/กิจกรรม รายเดือน
        $sql2 = "SELECT 
                    MONTH(r.report_date) AS month,
                    YEAR(r.report_date) AS year,
                    l.status,
                    COUNT(*) AS count
                FROM teaching_reports r
                JOIN teaching_attendance_logs l ON l.report_id = r.id
                WHERE r.teacher_id IN ($in)
                GROUP BY year, month, l.status";
        $stmt2 = $this->pdo->prepare($sql2);
        $stmt2->execute($teacherIds);
        $logs = $stmt2->fetchAll();

        // รวมข้อมูล
        $result = [];
        foreach ($monthly as $row) {
            $key = $row['year'].'-'.$row['month'];
            $result[$key] = [
                'month' => $row['month'],
                'year' => $row['year'],
                'count' => $row['count'],
                'absent' => 0,
                'sick' => 0,
                'personal' => 0,
                'activity' => 0
            ];
        }
        foreach ($logs as $log) {
            $key = $log['year'].'-'.$log['month'];
            if (!isset($result[$key])) continue;
            if ($log['status'] === 'ขาดเรียน') $result[$key]['absent'] = (int)$log['count'];
            if ($log['status'] === 'ลาป่วย') $result[$key]['sick'] = (int)$log['count'];
            if ($log['status'] === 'ลากิจ') $result[$key]['personal'] = (int)$log['count'];
            if ($log['status'] === 'เข้าร่วมกิจกรรม') $result[$key]['activity'] = (int)$log['count'];
        }
        // คืนค่าเป็น array
        return array_values($result);
    }
}
