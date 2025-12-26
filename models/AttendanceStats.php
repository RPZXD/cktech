<?php
/**
 * Attendance Statistics Model
 * MVC Pattern - Model for Attendance/Check-in Statistics
 */

namespace App\Models;

class AttendanceStats
{
    private $pdo;
    private $pdoUsers;

    public function __construct()
    {
        require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
        require_once __DIR__ . '/../classes/DatabaseUsers.php';
        
        try {
            $dbTeaching = new \App\DatabaseTeachingReport();
            $this->pdo = $dbTeaching->getPDO();
        } catch (\Exception $e) {
            $this->pdo = null;
        }
        
        try {
            $dbUsers = new \App\DatabaseUsers();
            $this->pdoUsers = $dbUsers->getPDO();
        } catch (\Exception $e) {
            $this->pdoUsers = null;
        }
    }

    public function getOverallStats(): array
    {
        if (!$this->pdo) return $this->getEmptyStats();
        
        try {
            $tables = $this->pdo->query("SHOW TABLES LIKE 'teaching_attendance_logs'")->fetchAll();
            if (empty($tables)) {
                return $this->getEmptyStats();
            }
            
            // Total attendance logs
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM teaching_attendance_logs");
            $total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Present count
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM teaching_attendance_logs WHERE status = 'มาเรียน'");
            $present = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Absent count
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM teaching_attendance_logs WHERE status = 'ขาดเรียน'");
            $absent = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Late count
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM teaching_attendance_logs WHERE status = 'มาสาย'");
            $late = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Sick leave
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM teaching_attendance_logs WHERE status = 'ลาป่วย'");
            $sick = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Personal leave
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM teaching_attendance_logs WHERE status = 'ลากิจ'");
            $personal = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Attendance rate
            $attendanceRate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            
            return [
                'total_records' => $total,
                'present_count' => $present,
                'absent_count' => $absent,
                'late_count' => $late,
                'sick_count' => $sick,
                'personal_count' => $personal,
                'attendance_rate' => $attendanceRate
            ];
        } catch (\PDOException $e) {
            return $this->getEmptyStats();
        }
    }

    private function getEmptyStats(): array
    {
        return [
            'total_records' => 0,
            'present_count' => 0,
            'absent_count' => 0,
            'late_count' => 0,
            'sick_count' => 0,
            'personal_count' => 0,
            'attendance_rate' => 0
        ];
    }

    public function getAttendanceByStatus(): array
    {
        if (!$this->pdo) return [];
        
        try {
            $tables = $this->pdo->query("SHOW TABLES LIKE 'teaching_attendance_logs'")->fetchAll();
            if (empty($tables)) return [];
            
            $sql = "SELECT status, COUNT(*) as count FROM teaching_attendance_logs GROUP BY status ORDER BY count DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getAttendanceByMonth(int $months = 6): array
    {
        if (!$this->pdo) return [];
        
        try {
            $tables = $this->pdo->query("SHOW TABLES LIKE 'teaching_attendance_logs'")->fetchAll();
            if (empty($tables)) return [];
            
            $data = [];
            $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                
                // Get present count for the month
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as total 
                    FROM teaching_attendance_logs tal
                    JOIN teaching_reports tr ON tal.report_id = tr.id
                    WHERE DATE_FORMAT(tr.report_date, '%Y-%m') = ?
                    AND tal.status = 'มาเรียน'
                ");
                $stmt->execute([$month]);
                $present = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
                
                // Get absent count for the month
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as total 
                    FROM teaching_attendance_logs tal
                    JOIN teaching_reports tr ON tal.report_id = tr.id
                    WHERE DATE_FORMAT(tr.report_date, '%Y-%m') = ?
                    AND tal.status = 'ขาดเรียน'
                ");
                $stmt->execute([$month]);
                $absent = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
                
                $monthNum = (int)date('n', strtotime($month . '-01'));
                
                $data[] = [
                    'month' => $thaiMonths[$monthNum],
                    'present' => $present,
                    'absent' => $absent
                ];
            }
            return $data;
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getTopAbsentStudents(int $limit = 10): array
    {
        if (!$this->pdo || !$this->pdoUsers) return [];
        
        try {
            $tables = $this->pdo->query("SHOW TABLES LIKE 'teaching_attendance_logs'")->fetchAll();
            if (empty($tables)) return [];
            
            $sql = "SELECT student_id, COUNT(*) as count 
                    FROM teaching_attendance_logs 
                    WHERE status = 'ขาดเรียน'
                    GROUP BY student_id 
                    ORDER BY count DESC 
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get student names
            $data = [];
            foreach ($results as $row) {
                $stmtStu = $this->pdoUsers->prepare("SELECT CONCAT(Stu_pre, Stu_name, ' ', Stu_sur) AS name, Stu_room FROM student WHERE Stu_id = ?");
                $stmtStu->execute([$row['student_id']]);
                $student = $stmtStu->fetch(\PDO::FETCH_ASSOC);
                
                $data[] = [
                    'student_id' => $row['student_id'],
                    'student_name' => $student['name'] ?? 'ไม่ระบุ',
                    'room' => $student['Stu_room'] ?? '-',
                    'count' => (int)$row['count']
                ];
            }
            return $data;
        } catch (\PDOException $e) {
            return [];
        }
    }
}
