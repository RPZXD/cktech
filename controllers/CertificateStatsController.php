<?php
/**
 * Certificate Statistics Controller
 */

namespace App\Controllers;

require_once __DIR__ . '/../models/CertificateStats.php';

use App\Models\CertificateStats;

class CertificateStatsController
{
    private $model;
    private $config;
    private $global;

    public function __construct()
    {
        $this->model = new CertificateStats();
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
            'pageTitle' => 'สถิติใบประกาศ',
            'overallStats' => $this->model->getOverallStats(),
            'certificatesByMonth' => $this->model->getCertificatesByMonth(6),
            'recentCertificates' => $this->model->getRecentCertificates(10),
            'global' => $this->global
        ];
    }
}
