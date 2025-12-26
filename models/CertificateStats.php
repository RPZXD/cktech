<?php
/**
 * Certificate Statistics Model
 * MVC Pattern - Model for Certificate Statistics
 */

namespace App\Models;

class CertificateStats
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
            $tables = $this->pdo->query("SHOW TABLES LIKE 'certificates'")->fetchAll();
            if (empty($tables)) {
                return $this->getEmptyStats();
            }
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM certificates");
            $total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            $thisMonth = date('Y-m');
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM certificates WHERE created_at LIKE ?");
            $stmt->execute([$thisMonth . '%']);
            $monthly = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT teacher_id) as total FROM certificates");
            $teachers = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            
            return [
                'total_certificates' => $total,
                'monthly_certificates' => $monthly,
                'teachers_with_cert' => $teachers,
                'avg_per_teacher' => $teachers > 0 ? round($total / $teachers, 1) : 0
            ];
        } catch (\PDOException $e) {
            return $this->getEmptyStats();
        }
    }

    private function getEmptyStats(): array
    {
        return [
            'total_certificates' => 0,
            'monthly_certificates' => 0,
            'teachers_with_cert' => 0,
            'avg_per_teacher' => 0
        ];
    }

    public function getCertificatesByMonth(int $months = 6): array
    {
        if (!$this->pdo) return [];
        
        try {
            $tables = $this->pdo->query("SHOW TABLES LIKE 'certificates'")->fetchAll();
            if (empty($tables)) return [];
            
            $data = [];
            $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM certificates WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
                $stmt->execute([$month]);
                $count = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
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

    public function getRecentCertificates(int $limit = 10): array
    {
        if (!$this->pdo) return [];
        
        try {
            $tables = $this->pdo->query("SHOW TABLES LIKE 'certificates'")->fetchAll();
            if (empty($tables)) return [];
            
            $sql = "SELECT * FROM certificates ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }
}
