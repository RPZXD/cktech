<?php
/**
 * Home Controller
 * MVC Pattern - Controller for Home page
 */

namespace App\Controllers;

require_once __DIR__ . '/../models/Home.php';

use App\Models\Home;

class HomeController
{
    private $homeModel;
    private $config;
    private $global;

    public function __construct()
    {
        $this->homeModel = new Home();
        $this->loadConfig();
    }

    /**
     * Load configuration from JSON file
     */
    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../config.json';
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
            $this->global = $this->config['global'] ?? [];
        } else {
            $this->config = [];
            $this->global = [
                'pageTitle' => 'ระบบ วิชาการ',
                'nameschool' => 'โรงเรียน',
                'nameTitle' => 'VichakanSystem',
                'logoLink' => 'logo-phicha.png'
            ];
        }
    }

    /**
     * Get global configuration
     * @return array
     */
    public function getGlobalConfig(): array
    {
        return $this->global;
    }

    /**
     * Index action - Display home page
     * @return array Data for the view
     */
    public function index(): array
    {
        // Get dashboard statistics
        $statistics = $this->homeModel->getStatistics();
        
        // Get quick links
        $quickLinks = $this->homeModel->getQuickLinks();
        
        // Get recent activities
        $recentActivities = $this->homeModel->getRecentActivities(5);
        
        // Get today's stats
        $todayStats = $this->homeModel->getTodayStats();

        // Get current user info if logged in
        $currentUser = $this->getCurrentUser();

        return [
            'pageTitle' => 'หน้าหลัก',
            'statistics' => $statistics,
            'quickLinks' => $quickLinks,
            'recentActivities' => $recentActivities,
            'todayStats' => $todayStats,
            'currentUser' => $currentUser,
            'global' => $this->global,
            'welcomeMessage' => $this->getWelcomeMessage()
        ];
    }

    /**
     * Get current logged in user info
     * @return array|null
     */
    private function getCurrentUser(): ?array
    {
        if (isset($_SESSION['Teacher_login'])) {
            return [
                'type' => 'teacher',
                'id' => $_SESSION['Teacher_login'],
                'name' => $_SESSION['teacher_name'] ?? 'ครู',
                'role' => 'ครู'
            ];
        }
        
        if (isset($_SESSION['Student_login'])) {
            return [
                'type' => 'student',
                'id' => $_SESSION['Student_login'],
                'name' => $_SESSION['student_name'] ?? 'นักเรียน',
                'role' => 'นักเรียน'
            ];
        }
        
        if (isset($_SESSION['Officer_login'])) {
            return [
                'type' => 'officer',
                'id' => $_SESSION['Officer_login'],
                'name' => $_SESSION['officer_name'] ?? 'เจ้าหน้าที่',
                'role' => 'เจ้าหน้าที่'
            ];
        }
        
        if (isset($_SESSION['Admin_login'])) {
            return [
                'type' => 'admin',
                'id' => $_SESSION['Admin_login'],
                'name' => $_SESSION['admin_name'] ?? 'ผู้ดูแลระบบ',
                'role' => 'ผู้ดูแลระบบ'
            ];
        }
        
        return null;
    }

    /**
     * Get welcome message based on time of day
     * @return string
     */
    private function getWelcomeMessage(): string
    {
        $hour = (int)date('H');
        
        if ($hour >= 5 && $hour < 12) {
            return 'สวัสดีตอนเช้า';
        } elseif ($hour >= 12 && $hour < 17) {
            return 'สวัสดีตอนบ่าย';
        } elseif ($hour >= 17 && $hour < 21) {
            return 'สวัสดีตอนเย็น';
        } else {
            return 'สวัสดีตอนดึก';
        }
    }

    /**
     * Render the view
     * @param array $data Data to pass to the view
     * @return string Rendered HTML content
     */
    public function render(array $data): string
    {
        // Extract data to make it available in view
        extract($data);
        
        // Capture view output
        ob_start();
        include __DIR__ . '/../views/home/index.php';
        return ob_get_clean();
    }
}
