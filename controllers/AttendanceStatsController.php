<?php
/**
 * Attendance Statistics Controller
 */

namespace App\Controllers;

require_once __DIR__ . '/../models/AttendanceStats.php';

use App\Models\AttendanceStats;

class AttendanceStatsController
{
    private $model;
    private $config;
    private $global;

    public function __construct()
    {
        $this->model = new AttendanceStats();
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
            'pageTitle' => 'สถิติการเช็คชื่อ',
            'overallStats' => $this->model->getOverallStats(),
            'attendanceByStatus' => $this->model->getAttendanceByStatus(),
            'attendanceByMonth' => $this->model->getAttendanceByMonth(6),
            'topAbsentStudents' => $this->model->getTopAbsentStudents(10),
            'global' => $this->global
        ];
    }
}
