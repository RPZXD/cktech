<?php
/**
 * Teaching Statistics Model
 * MVC Pattern - Model for Teaching Report Statistics
 */

namespace App\Models;

class TeachingStats
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

    /**
     * Get overall statistics
     */
    public function getOverallStats(): array
    {
        if (!$this->pdo) return $this->getEmptyStats();
        
        try {
            // Total reports
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM teaching_reports");
            $totalReports = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // This month reports
            $thisMonth = date('Y-m');
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM teaching_reports WHERE report_date LIKE ?");
            $stmt->execute([$thisMonth . '%']);
            $monthlyReports = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Today reports
            $today = date('Y-m-d');
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM teaching_reports WHERE report_date = ?");
            $stmt->execute([$today]);
            $todayReports = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Unique teachers who submitted
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT teacher_id) as total FROM teaching_reports");
            $activeTeachers = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            return [
                'total_reports' => $totalReports,
                'monthly_reports' => $monthlyReports,
                'today_reports' => $todayReports,
                'active_teachers' => $activeTeachers
            ];
        } catch (\PDOException $e) {
            return $this->getEmptyStats();
        }
    }

    private function getEmptyStats(): array
    {
        return [
            'total_reports' => 0,
            'monthly_reports' => 0,
            'today_reports' => 0,
            'active_teachers' => 0
        ];
    }

    /**
     * Get reports by month for chart (last 6 months)
     */
    public function getReportsByMonth(int $months = 6): array
    {
        if (!$this->pdo) return [];
        
        try {
            $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM teaching_reports WHERE DATE_FORMAT(report_date, '%Y-%m') = ?");
                $stmt->execute([$month]);
                $count = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
                
                // Thai month names
                $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                $monthNum = (int)date('n', strtotime($month . '-01'));
                
                $data[] = [
                    'month' => $thaiMonths[$monthNum],
                    'count' => $count
                ];
            }
            return $data;
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get reports by subject
     */
    public function getReportsBySubject(int $limit = 10): array
    {
        if (!$this->pdo) return [];
        
        try {
            $sql = "SELECT s.name as subject_name, COUNT(tr.id) as count 
                    FROM teaching_reports tr
                    LEFT JOIN subjects s ON tr.subject_id = s.id
                    GROUP BY tr.subject_id, s.name
                    ORDER BY count DESC
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get top teachers by report count
     */
    public function getTopTeachers(int $limit = 10): array
    {
        if (!$this->pdo || !$this->pdoUsers) return [];
        
        try {
            // Get teacher report counts
            $sql = "SELECT teacher_id, COUNT(*) as count 
                    FROM teaching_reports 
                    GROUP BY teacher_id 
                    ORDER BY count DESC 
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            $reports = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get teacher names
            $result = [];
            foreach ($reports as $row) {
                $stmtTeacher = $this->pdoUsers->prepare("SELECT CONCAT(Teach_pre, Teach_name, ' ', Teach_sur) AS name FROM teacher WHERE Teach_id = ?");
                $stmtTeacher->execute([$row['teacher_id']]);
                $teacher = $stmtTeacher->fetch(\PDO::FETCH_ASSOC);
                
                $result[] = [
                    'teacher_id' => $row['teacher_id'],
                    'teacher_name' => $teacher['name'] ?? 'ไม่ระบุ',
                    'count' => (int)$row['count']
                ];
            }
            return $result;
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get recent reports
     */
    public function getRecentReports(int $limit = 10): array
    {
        if (!$this->pdo) return [];
        
        try {
            $sql = "SELECT tr.id, tr.report_date, tr.class_room, tr.period_start, tr.period_end,
                           tr.teacher_id, s.name AS subject_name 
                    FROM teaching_reports tr
                    LEFT JOIN subjects s ON tr.subject_id = s.id
                    ORDER BY tr.created_at DESC
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            $reports = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get teacher names
            if ($this->pdoUsers && !empty($reports)) {
                foreach ($reports as &$report) {
                    try {
                        $stmtTeacher = $this->pdoUsers->prepare("SELECT CONCAT(Teach_pre, Teach_name, ' ', Teach_sur) AS teacher_name FROM teacher WHERE Teach_id = ?");
                        $stmtTeacher->execute([$report['teacher_id']]);
                        $teacher = $stmtTeacher->fetch(\PDO::FETCH_ASSOC);
                        $report['teacher_name'] = $teacher['teacher_name'] ?? 'ไม่ระบุ';
                    } catch (\PDOException $e) {
                        $report['teacher_name'] = 'ไม่ระบุ';
                    }
                }
            }
            
            return $reports;
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get reports by day of week
     */
    public function getReportsByDayOfWeek(): array
    {
        if (!$this->pdo) return [];
        
        try {
            $sql = "SELECT DAYOFWEEK(report_date) as day_num, COUNT(*) as count 
                    FROM teaching_reports 
                    GROUP BY DAYOFWEEK(report_date)
                    ORDER BY day_num";
            $stmt = $this->pdo->query($sql);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $thaiDays = ['', 'อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
            $data = [];
            
            foreach ($results as $row) {
                $data[] = [
                    'day' => $thaiDays[(int)$row['day_num']] ?? '',
                    'count' => (int)$row['count']
                ];
            }
            
            return $data;
        } catch (\PDOException $e) {
            return [];
        }
    }
}
