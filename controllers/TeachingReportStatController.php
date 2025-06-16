<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/TeachingReportStat.php';

use App\Models\TeachingReportStat;

$department = $_GET['department'] ?? '';
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;

try {
    $statModel = new TeachingReportStat();

    // 1. จำนวนรายงานแยกตามครู
    $reportCounts = $statModel->getReportCountsByTeacher($department, $startDate, $endDate);

    // 2. จำนวนรายงานการสอนแยกตามรายวิชา
    $reportCountsBySubject = $statModel->getReportCountsBySubject($department, $startDate, $endDate);

    // 3. สรุปสถิติสำหรับช่วงวันที่ที่เลือก
    $summaryStats = $statModel->getSummaryStatsForDateRange($department, $startDate, $endDate);

    // 4. สถิติการส่งรายงานเทียบกับตารางสอนรายสัปดาห์
    $weeklyCompletion = [];
    if ($startDate && $endDate) {
        $weeklyCompletion = $statModel->getWeeklyReportCompletion($department, $startDate, $endDate);
    }

    // 5. New analytics
    $dailyTrend = $statModel->getDailyTrend($department, $startDate, $endDate);
    $teachingMethods = $statModel->getTeachingMethods($department, $startDate, $endDate);
    $qualityStats = $statModel->getQualityStats($department, $startDate, $endDate);

    echo json_encode([
        'reportCounts' => $reportCounts,
        'reportCountsBySubject' => $reportCountsBySubject,
        'summaryStats' => $summaryStats,
        'weeklyCompletion' => $weeklyCompletion,
        'dailyTrend' => $dailyTrend,
        'teachingMethods' => $teachingMethods,
        'qualityStats' => $qualityStats
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    // Provide a more detailed error message if in a development environment
    $errorMessage = 'Internal Server Error';
    // if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    //     $errorMessage = $e->getMessage() . "\n" . $e->getTraceAsString();
    // }
    echo json_encode(['error' => $errorMessage, 'detail' => $e->getMessage()]);
}
