<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/TeachingReportStat.php';

use App\Models\TeachingReportStat;

$department = $_GET['department'] ?? '';

try {
    $statModel = new TeachingReportStat();

    // 1. จำนวนรายงานแยกตามครู
    $reportCounts = $statModel->getReportCountsByTeacher($department);

    // 2. สถิติขาดเรียน/ป่วย/ลากิจ/กิจกรรม รวมทั้งกลุ่มสาระ
    $absentStats = $statModel->getAbsentStats($department);

    // 3. สรุปรายเดือน
    $monthlyStats = $statModel->getMonthlyStats($department);

    echo json_encode([
        'reportCounts' => $reportCounts,
        'absentStats' => $absentStats,
        'monthlyStats' => $monthlyStats
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
