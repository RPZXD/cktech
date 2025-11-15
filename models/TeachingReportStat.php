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

    // 1. จำนวนรายงานแยกตามครูในกลุ่มสาระ (ปรับปรุงให้รับช่วงวันที่)
    public function getReportCountsByTeacher($department, $startDate = null, $endDate = null)
    {
        // ดึงรายชื่อครูในกลุ่มสาระ
        $sqlTeachers = "SELECT Teach_id, Teach_name FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teachers = $stmtT->fetchAll();

        if (empty($teachers)) return [];

        $result = [];
        foreach ($teachers as $teacher) {
            $sqlCount = "SELECT COUNT(*) AS count FROM teaching_reports WHERE teacher_id = :teacher_id";
            $params = ['teacher_id' => $teacher['Teach_id']];
            if ($startDate && $endDate) {
                $sqlCount .= " AND report_date BETWEEN :startDate AND :endDate";
                $params['startDate'] = $startDate;
                $params['endDate'] = $endDate;
            }
            $stmtC = $this->pdo->prepare($sqlCount);
            $stmtC->execute($params);
            $count = $stmtC->fetchColumn();
            $result[] = [
                'Teach_id' => $teacher['Teach_id'],
                'Teach_name' => $teacher['Teach_name'],
                'count' => (int)$count
            ];
        }
        return $result;
    }


    // New method: จำนวนรายงานการสอนแยกตามรายวิชา
    public function getReportCountsBySubject($department, $startDate = null, $endDate = null)
    {
        // First, get teacher IDs for the department
        $sqlTeachers = "SELECT Teach_id FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teacherIds = array_column($stmtT->fetchAll(PDO::FETCH_ASSOC), 'Teach_id');

        if (empty($teacherIds)) {
            return [];
        }

        $teacherIdPlaceholders = implode(',', array_fill(0, count($teacherIds), '?'));

        $sql = "SELECT s.name AS subject_name, COUNT(tr.id) AS count
                FROM teaching_reports tr
                JOIN subjects s ON tr.subject_id = s.id
                WHERE tr.teacher_id IN ($teacherIdPlaceholders)";
        
        $params = $teacherIds;

        if ($startDate && $endDate) {
            $sql .= " AND tr.report_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $sql .= " GROUP BY s.name ORDER BY count DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. สรุปสถิติสำหรับช่วงวันที่ที่เลือก (แทนที่ getMonthlyStats)
    public function getSummaryStatsForDateRange($department, $startDate = null, $endDate = null)
    {
        $sqlTeachers = "SELECT Teach_id FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teacherIds = array_column($stmtT->fetchAll(), 'Teach_id');

        if (empty($teacherIds)) {
            return ['count' => 0, 'absent' => 0, 'sick' => 0, 'personal' => 0, 'activity' => 0];
        }

        $in = str_repeat('?,', count($teacherIds) - 1) . '?';
        
        $baseParams = $teacherIds;
        $reportsInRangeBaseSQL = "FROM teaching_reports WHERE teacher_id IN ($in)";
        
        if ($startDate && $endDate) {
            $reportsInRangeBaseSQL .= " AND report_date BETWEEN ? AND ?";
            $baseParams[] = $startDate;
            $baseParams[] = $endDate;
        }
        
        $sqlCountParams = $baseParams; // Create a copy for this query
        $sqlCount = "SELECT COUNT(id) AS total_reports " . $reportsInRangeBaseSQL;
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($sqlCountParams);
        $totalReports = (int)$stmtCount->fetchColumn();

        $sqlReportIdsParams = $baseParams; // Create a copy for this query
        $sqlReportIds = "SELECT id " . $reportsInRangeBaseSQL;
        $stmtReportIds = $this->pdo->prepare($sqlReportIds);
        $stmtReportIds->execute($sqlReportIdsParams);
        $reportIds = array_column($stmtReportIds->fetchAll(), 'id');

        $attendanceResult = ['absent' => 0, 'sick' => 0, 'personal' => 0, 'activity' => 0];
        if (!empty($reportIds)) {
            $in2 = str_repeat('?,', count($reportIds) - 1) . '?';
            $sqlLogs = "SELECT status, COUNT(*) AS count FROM teaching_attendance_logs WHERE report_id IN ($in2) GROUP BY status";
            $stmtLogs = $this->pdo->prepare($sqlLogs);
            $stmtLogs->execute($reportIds);
            foreach ($stmtLogs->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if ($row['status'] === 'ขาดเรียน') $attendanceResult['absent'] = (int)$row['count'];
                if ($row['status'] === 'ลาป่วย') $attendanceResult['sick'] = (int)$row['count'];
                if ($row['status'] === 'ลากิจ') $attendanceResult['personal'] = (int)$row['count'];
                if ($row['status'] === 'เข้าร่วมกิจกรรม') $attendanceResult['activity'] = (int)$row['count'];
            }
        }

        return [
            'count' => $totalReports,
            'absent' => $attendanceResult['absent'],
            'sick' => $attendanceResult['sick'],
            'personal' => $attendanceResult['personal'],
            'activity' => $attendanceResult['activity']
        ];
    }

    // 4. สถิติการส่งรายงานเทียบกับตารางสอนรายสัปดาห์
    public function getWeeklyReportCompletion($department, $startDate, $endDate)
    {
        $sqlTeachers = "SELECT Teach_id, Teach_name FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teachers = $stmtT->fetchAll(PDO::FETCH_ASSOC);

        if (empty($teachers)) return [];

        $result = [];
        // Map Thai day names to PHP day of week (0 for Sunday, 1 for Monday, ..., 6 for Saturday)
        $daysOfWeekMap = ['อาทิตย์' => 0, 'จันทร์' => 1, 'อังคาร' => 2, 'พุธ' => 3, 'พฤหัสบดี' => 4, 'ศุกร์' => 5, 'เสาร์' => 6];

        foreach ($teachers as $teacher) {
            $teacherId = $teacher['Teach_id'];
            $expectedSessions = 0;

            // Get subjects this teacher has reported on (as a proxy for subjects they teach)
            $stmtSubjectsTaught = $this->pdo->prepare("SELECT DISTINCT subject_id FROM teaching_reports WHERE teacher_id = :teacher_id");
            $stmtSubjectsTaught->execute(['teacher_id' => $teacherId]);
            $taughtSubjectIds = $stmtSubjectsTaught->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($taughtSubjectIds)) {
                $placeholders = implode(',', array_fill(0, count($taughtSubjectIds), '?'));
                // Assuming subject_classes is in the same database ($this->pdo)
                $sqlScheduledClasses = "SELECT day_of_week FROM subject_classes WHERE subject_id IN ($placeholders)";
                $stmtScheduledClasses = $this->pdo->prepare($sqlScheduledClasses);
                $stmtScheduledClasses->execute($taughtSubjectIds);
                $scheduledClassesInfo = $stmtScheduledClasses->fetchAll(PDO::FETCH_ASSOC);

                // Calculate expected sessions within the date range
                try {
                    $currentIterDate = new \DateTime($startDate);
                    $endIterDate = new \DateTime($endDate);
                    // Loop through each day in the selected range
                    while ($currentIterDate <= $endIterDate) {
                        $phpDayOfWeek = (int)$currentIterDate->format('w'); // 0 (Sunday) to 6 (Saturday)
                        foreach ($scheduledClassesInfo as $classInfo) {
                            $dbDayName = $classInfo['day_of_week'];
                            if (isset($daysOfWeekMap[$dbDayName]) && $daysOfWeekMap[$dbDayName] == $phpDayOfWeek) {
                                $expectedSessions++;
                            }
                        }
                        $currentIterDate->modify('+1 day');
                    }
                } catch (\Exception $e) {
                    // Handle DateTime creation errors, e.g. invalid date format
                    // For now, we'll let expectedSessions be 0 if dates are problematic
                }
            }
            
            // Count actual reports submitted by this teacher in the date range
            $sqlActualReports = "SELECT COUNT(DISTINCT id) AS count 
                                 FROM teaching_reports 
                                 WHERE teacher_id = :teacher_id 
                                   AND report_date BETWEEN :startDate AND :endDate";
            $stmtActualReports = $this->pdo->prepare($sqlActualReports);
            $stmtActualReports->execute([
                'teacher_id' => $teacherId,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            $actualReportsCount = (int)$stmtActualReports->fetchColumn();

            $completionRate = ($expectedSessions > 0) ? round(($actualReportsCount / $expectedSessions) * 100, 2) : 0;

            $result[] = [
                'Teach_name' => $teacher['Teach_name'],
                'expected_reports' => $expectedSessions,
                'submitted_reports' => $actualReportsCount,
                'completion_rate' => $completionRate
            ];
        }
        return $result;
    }

    // New method: Daily trend analysis
    public function getDailyTrend($department, $startDate, $endDate)
    {
        $sqlTeachers = "SELECT Teach_id FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teacherIds = array_column($stmtT->fetchAll(PDO::FETCH_ASSOC), 'Teach_id');

        if (empty($teacherIds)) {
            return [];
        }

        $teacherIdPlaceholders = implode(',', array_fill(0, count($teacherIds), '?'));

        $sql = "SELECT DATE_FORMAT(report_date, '%d/%m') as date, COUNT(*) as count
                FROM teaching_reports 
                WHERE teacher_id IN ($teacherIdPlaceholders)";
        
        $params = $teacherIds;

        if ($startDate && $endDate) {
            $sql .= " AND report_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $sql .= " GROUP BY report_date ORDER BY report_date";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // New method: Teaching methods analysis (based on activity field)
    public function getTeachingMethods($department, $startDate = null, $endDate = null)
    {
        $sqlTeachers = "SELECT Teach_id FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teacherIds = array_column($stmtT->fetchAll(PDO::FETCH_ASSOC), 'Teach_id');

        if (empty($teacherIds)) {
            return [];
        }

        $teacherIdPlaceholders = implode(',', array_fill(0, count($teacherIds), '?'));

        // Simple keyword-based analysis of activities
        $sql = "SELECT 
                    CASE 
                        WHEN activity LIKE '%บรรยาย%' OR activity LIKE '%อธิบาย%' THEN 'การบรรยาย'
                        WHEN activity LIKE '%กลุ่ม%' OR activity LIKE '%ร่วมมือ%' THEN 'กิจกรรมกลุ่ม'
                        WHEN activity LIKE '%ทดลอง%' OR activity LIKE '%ปฏิบัติ%' THEN 'การทดลอง'
                        WHEN activity LIKE '%อภิปราย%' OR activity LIKE '%토론%' THEN 'การอภิปราย'
                        WHEN activity LIKE '%โครงงาน%' OR activity LIKE '%project%' THEN 'โครงงาน'
                        ELSE 'อื่นๆ'
                    END as method,
                    COUNT(*) as count
                FROM teaching_reports 
                WHERE teacher_id IN ($teacherIdPlaceholders) 
                AND activity IS NOT NULL AND activity != ''";
        
        $params = $teacherIds;

        if ($startDate && $endDate) {
            $sql .= " AND report_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $sql .= " GROUP BY method ORDER BY count DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // New method: Report quality analysis
    public function getQualityStats($department, $startDate = null, $endDate = null)
    {
        $sqlTeachers = "SELECT Teach_id FROM teacher WHERE Teach_major = :department AND Teach_status = 1";
        $stmtT = $this->pdoUsers->prepare($sqlTeachers);
        $stmtT->execute(['department' => $department]);
        $teacherIds = array_column($stmtT->fetchAll(PDO::FETCH_ASSOC), 'Teach_id');

        if (empty($teacherIds)) {
            return ['withImages' => 0, 'withReflection' => 0, 'withProblems' => 0];
        }

        $teacherIdPlaceholders = implode(',', array_fill(0, count($teacherIds), '?'));

        $sql = "SELECT 
                    COUNT(*) as total_reports,
                    SUM(CASE WHEN (image1 IS NOT NULL AND image1 != '') OR (image2 IS NOT NULL AND image2 != '') THEN 1 ELSE 0 END) as with_images,
                    SUM(CASE WHEN (reflection_k IS NOT NULL AND reflection_k != '') OR (reflection_p IS NOT NULL AND reflection_p != '') OR (reflection_a IS NOT NULL AND reflection_a != '') THEN 1 ELSE 0 END) as with_reflection,
                    SUM(CASE WHEN problems IS NOT NULL AND problems != '' THEN 1 ELSE 0 END) as with_problems
                FROM teaching_reports 
                WHERE teacher_id IN ($teacherIdPlaceholders)";
        
        $params = $teacherIds;

        if ($startDate && $endDate) {
            $sql .= " AND report_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['total_reports'] == 0) {
            return ['withImages' => 0, 'withReflection' => 0, 'withProblems' => 0];
        }

        return [
            'withImages' => round(($result['with_images'] / $result['total_reports']) * 100, 1),
            'withReflection' => round(($result['with_reflection'] / $result['total_reports']) * 100, 1),
            'withProblems' => round(($result['with_problems'] / $result['total_reports']) * 100, 1)
        ];
    }
}
