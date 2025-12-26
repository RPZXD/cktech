<?php
/**
 * Teaching Statistics Controller
 * MVC Pattern - Controller for Teaching Report Statistics page
 */

namespace App\Controllers;

require_once __DIR__ . '/../models/TeachingStats.php';

use App\Models\TeachingStats;

class TeachingStatsController
{
    private $model;
    private $config;
    private $global;

    public function __construct()
    {
        $this->model = new TeachingStats();
        $this->loadConfig();
    }

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
                'logoLink' => 'logo-phicha.png'
            ];
        }
    }

    public function index(): array
    {
        return [
            'pageTitle' => 'สถิติรายงานการสอน',
            'overallStats' => $this->model->getOverallStats(),
            'reportsByMonth' => $this->model->getReportsByMonth(6),
            'reportsBySubject' => $this->model->getReportsBySubject(10),
            'topTeachers' => $this->model->getTopTeachers(10),
            'recentReports' => $this->model->getRecentReports(10),
            'reportsByDay' => $this->model->getReportsByDayOfWeek(),
            'global' => $this->global
        ];
    }
}
