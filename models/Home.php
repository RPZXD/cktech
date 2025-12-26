<?php
/**
 * Home Model
 * MVC Pattern - Model for Home page data
 * Uses DatabaseTeachingReport and DatabaseUsers for database connections
 */

namespace App\Models;

class Home
{
    private $pdo;
    private $pdoUsers;

    public function __construct()
    {
        // Load database classes
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
     * Get dashboard statistics
     * @return array
     */
    public function getStatistics(): array
    {
        $stats = [
            'total_teachers' => $this->getTotalTeachers(),
            'total_students' => $this->getTotalStudents(),
            'total_subjects' => $this->getTotalSubjects(),
            'total_departments' => $this->getTotalDepartments(),
        ];
        
        return $stats;
    }

    /**
     * Get total number of teachers
     * @return int
     */
    public function getTotalTeachers(): int
    {
        if (!$this->pdoUsers) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM teacher WHERE Teach_status = 1";
            $stmt = $this->pdoUsers->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Get total number of students
     * @return int
     */
    public function getTotalStudents(): int
    {
        if (!$this->pdoUsers) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM student WHERE Stu_status = 1";
            $stmt = $this->pdoUsers->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Get total number of subjects
     * @return int
     */
    public function getTotalSubjects(): int
    {
        if (!$this->pdo) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM subjects";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Get total number of departments
     * @return int
     */
    public function getTotalDepartments(): int
    {
        if (!$this->pdoUsers) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM department";
            $stmt = $this->pdoUsers->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Get recent activities (e.g., recent teaching reports)
     * @param int $limit
     * @return array
     */
    public function getRecentActivities(int $limit = 5): array
    {
        if (!$this->pdo) return [];
        
        try {
            $sql = "SELECT tr.id, tr.report_date, tr.class_room, tr.period_start, tr.period_end,
                           tr.teacher_id, s.name AS subject_name 
                    FROM teaching_reports tr
                    LEFT JOIN subjects s ON tr.subject_id = s.id
                    ORDER BY tr.created_at DESC
                    LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            $reports = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get teacher names from users database
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
     * Get quick links/menu items for the dashboard
     * @return array
     */
    public function getQuickLinks(): array
    {
        return [
            [
                'title' => 'สถิติรายงานการสอน',
                'description' => 'ดูสถิติการบันทึกการสอน',
                'url' => 'teaching-stats.php',
                'icon' => 'fa-chart-line',
                'color' => 'blue'
            ],
            [
                'title' => 'สถิตินิเทศการสอน',
                'description' => 'ดูสถิติการนิเทศ',
                'url' => 'supervision-stats.php',
                'icon' => 'fa-clipboard-check',
                'color' => 'green'
            ],
            [
                'title' => 'สถิติใบประกาศ',
                'description' => 'ดูสถิติใบประกาศ',
                'url' => 'certificate-stats.php',
                'icon' => 'fa-certificate',
                'color' => 'purple'
            ],
            [
                'title' => 'สถิติการเช็คชื่อ',
                'description' => 'ดูสถิติการมาเรียน',
                'url' => 'attendance-stats.php',
                'icon' => 'fa-user-check',
                'color' => 'orange'
            ],
            [
                'title' => 'เข้าสู่ระบบวครู',
                'description' => 'สำหรับครูผู้สอน',
                'url' => 'login.php',
                'icon' => 'fa-chalkboard-teacher',
                'color' => 'pink'
            ],
            [
                'title' => 'ผู้ดูแลระบบ',
                'description' => 'จัดการระบบ',
                'url' => 'admin/',
                'icon' => 'fa-cog',
                'color' => 'gray'
            ],
        ];
    }

    /**
     * Get teaching statistics for today
     * @return array
     */
    public function getTodayStats(): array
    {
        if (!$this->pdo) return ['reports_today' => 0];
        
        try {
            $today = date('Y-m-d');
            $sql = "SELECT COUNT(*) as total FROM teaching_reports WHERE report_date = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$today]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return ['reports_today' => (int)($result['total'] ?? 0)];
        } catch (\PDOException $e) {
            return ['reports_today' => 0];
        }
    }
}
