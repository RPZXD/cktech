<?php
/**
 * Supervision Statistics Controller
 */

namespace App\Controllers;

require_once __DIR__ . '/../models/SupervisionStats.php';

use App\Models\SupervisionStats;

class SupervisionStatsController
{
    private $model;
    private $config;
    private $global;

    public function __construct()
    {
        $this->model = new SupervisionStats();
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../config.json';
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
            $this->global = $this->config['global'] ?? [];
        } else {
            $this->global = ['pageTitle' => 'ระบบ วิชาการ', 'nameschool' => 'โรงเรียน'];
        }
    }

    public function index(): array
    {
        return [
            'pageTitle' => 'สถิตินิเทศการสอน',
            'overallStats' => $this->model->getOverallStats(),
            'supervisionsByMonth' => $this->model->getSupervisionsByMonth(6),
            'recentSupervisions' => $this->model->getRecentSupervisions(10),
            'global' => $this->global
        ];
    }
}
