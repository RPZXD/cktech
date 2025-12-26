<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/Stat.php';

use App\Models\Stat;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $statModel = new Stat();

    // รับช่วงวันที่จาก query string หรือใช้ค่า default (ปีการศึกษาตามปฏิทินไทย: 1 พ.ค. - 31 มี.ค. ปีถัดไป)
    session_start();
    if (isset($_GET['start']) || isset($_POST['start']) || isset($_GET['end']) || isset($_POST['end'])) {
        $startDate = $_GET['start'] ?? $_POST['start'];
        $endDate = $_GET['end'] ?? $_POST['end'];
    } else {
        $year = date('Y');
        $month = date('n');
        if ($month >= 5) {
            // พ.ค. - ธ.ค. => ปีการศึกษานี้
            $startDate = "$year-05-01";
            $endDate = ($year + 1) . "-03-31";
        } else {
            // ม.ค. - เม.ย. => ปีการศึกษาที่แล้ว
            $startDate = ($year - 1) . "-05-01";
            $endDate = "$year-03-31";
        }
    }

    switch ($action) {
        case 'summary':
            // Get summary stats for admin dashboard
            require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
            require_once __DIR__ . '/../classes/DatabaseUsers.php';
            
            $dbReport = new \App\DatabaseTeachingReport();
            $dbUsers = new \App\DatabaseUsers();
            $pdoReport = $dbReport->getPDO();
            $pdoUsers = $dbUsers->getPDO();
            
            // Total reports
            $totalReports = $statModel->getReportCount();
            
            // Total teachers
            $totalTeachers = $statModel->getTeacherCount();
            
            // Active teachers (teachers who submitted reports)
            $stmtActive = $pdoReport->query("SELECT COUNT(DISTINCT teacher_id) as cnt FROM teaching_reports");
            $activeTeachers = $stmtActive->fetch()['cnt'] ?? 0;
            
            // Today's reports
            $today = date('Y-m-d');
            $stmtToday = $pdoReport->prepare("SELECT COUNT(*) as cnt FROM teaching_reports WHERE report_date = ?");
            $stmtToday->execute([$today]);
            $todayReports = $stmtToday->fetch()['cnt'] ?? 0;
            
            // Attendance rate (based on attendance logs - มาเรียน vs total)
            $stmtAttendance = $pdoReport->query("
                SELECT 
                    SUM(CASE WHEN status = 'มาเรียน' THEN 1 ELSE 0 END) as present,
                    COUNT(*) as total
                FROM teaching_attendance_logs
            ");
            $attendanceData = $stmtAttendance->fetch();
            $attendanceRate = $attendanceData['total'] > 0 
                ? round(($attendanceData['present'] / $attendanceData['total']) * 100, 1) 
                : 0;
            
            // Reports growth compared to last month
            $thisMonth = date('Y-m-01');
            $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
            $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));
            
            $stmtThisMonth = $pdoReport->prepare("SELECT COUNT(*) as cnt FROM teaching_reports WHERE report_date >= ?");
            $stmtThisMonth->execute([$thisMonth]);
            $thisMonthCount = $stmtThisMonth->fetch()['cnt'] ?? 0;
            
            $stmtLastMonth = $pdoReport->prepare("SELECT COUNT(*) as cnt FROM teaching_reports WHERE report_date BETWEEN ? AND ?");
            $stmtLastMonth->execute([$lastMonthStart, $lastMonthEnd]);
            $lastMonthCount = $stmtLastMonth->fetch()['cnt'] ?? 0;
            
            $reportsGrowth = $lastMonthCount > 0 
                ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) 
                : 0;
            
            // Today's target (based on active teachers)
            $todayTarget = $activeTeachers;
            
            echo json_encode([
                'totalReports' => $totalReports,
                'activeTeachers' => $activeTeachers,
                'totalTeachers' => $totalTeachers,
                'attendanceRate' => $attendanceRate,
                'todayReports' => $todayReports,
                'todayTarget' => $todayTarget,
                'reportsGrowth' => $reportsGrowth
            ]);
            break;
            
        case 'topTeachers':
            // Get top teachers by report count
            $teachers = $statModel->getReportCountByTeacher($startDate, $endDate);
            $result = [];
            foreach (array_slice($teachers, 0, 10) as $t) {
                $result[] = [
                    'name' => $t['teacher'],
                    'department' => $t['department'],
                    'count' => $t['count']
                ];
            }
            echo json_encode($result);
            break;
            
        case 'chartDepartment':
            // Get report count by department for pie chart
            $data = $statModel->getReportCountByDepartment($startDate, $endDate);
            $labels = [];
            $counts = [];
            foreach ($data as $d) {
                $labels[] = $d['department'];
                $counts[] = $d['count'];
            }
            echo json_encode(['labels' => $labels, 'data' => $counts]);
            break;
            
        case 'chartTimeline':
            // Get daily report count for last N days
            require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
            $dbReport = new \App\DatabaseTeachingReport();
            $pdoReport = $dbReport->getPDO();
            
            $days = isset($_GET['days']) ? intval($_GET['days']) : 14;
            $startDateTimeline = date('Y-m-d', strtotime("-{$days} days"));
            
            $stmt = $pdoReport->prepare("
                SELECT report_date, COUNT(*) as cnt 
                FROM teaching_reports 
                WHERE report_date >= ? 
                GROUP BY report_date 
                ORDER BY report_date ASC
            ");
            $stmt->execute([$startDateTimeline]);
            $rows = $stmt->fetchAll();
            
            // Fill in missing dates with 0
            $labels = [];
            $data = [];
            $dateMap = [];
            foreach ($rows as $r) {
                $dateMap[$r['report_date']] = $r['cnt'];
            }
            
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $labels[] = date('d/m', strtotime($date));
                $data[] = $dateMap[$date] ?? 0;
            }
            
            echo json_encode(['labels' => $labels, 'data' => $data]);
            break;
            
        case 'chartAttendance':
            // Get attendance rate by level
            require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
            $dbReport = new \App\DatabaseTeachingReport();
            $pdoReport = $dbReport->getPDO();
            
            $levels = ['ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'];
            $rates = [];
            
            foreach ($levels as $level) {
                $stmt = $pdoReport->prepare("
                    SELECT 
                        SUM(CASE WHEN al.status = 'มาเรียน' OR al.status = 'มาสาย' THEN 1 ELSE 0 END) as present,
                        COUNT(al.id) as total
                    FROM teaching_attendance_logs al
                    JOIN teaching_reports r ON al.report_id = r.id
                    JOIN subjects s ON r.subject_id = s.id
                    WHERE s.level = ?
                ");
                $stmt->execute([$level]);
                $row = $stmt->fetch();
                
                if ($row && $row['total'] > 0) {
                    $rates[] = round(($row['present'] / $row['total']) * 100, 1);
                } else {
                    $rates[] = 0;
                }
            }
            
            echo json_encode(['labels' => $levels, 'data' => $rates]);
            break;
            
        case 'heatmap':
            // Get heatmap data (reports by day of week and period)
            require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
            $dbReport = new \App\DatabaseTeachingReport();
            $pdoReport = $dbReport->getPDO();
            
            $stmt = $pdoReport->prepare("
                SELECT 
                    DAYOFWEEK(report_date) as dow,
                    period_start as period,
                    COUNT(*) as cnt
                FROM teaching_reports
                WHERE report_date >= ?
                GROUP BY DAYOFWEEK(report_date), period_start
                ORDER BY dow, period
            ");
            $stmt->execute([$startDate]);
            $rows = $stmt->fetchAll();
            
            // Build 5 days x 9 periods grid (Mon-Fri, periods 1-9)
            $heatmap = [];
            for ($day = 2; $day <= 6; $day++) { // 2=Monday, 6=Friday
                $heatmap[$day] = [];
                for ($period = 1; $period <= 9; $period++) {
                    $heatmap[$day][$period] = 0;
                }
            }
            
            foreach ($rows as $r) {
                $dow = $r['dow'];
                $period = $r['period'];
                if ($dow >= 2 && $dow <= 6 && $period >= 1 && $period <= 9) {
                    $heatmap[$dow][$period] = $r['cnt'];
                }
            }
            
            echo json_encode($heatmap);
            break;
            
        case 'reportByMonth':
            echo json_encode($statModel->getReportCountByMonth($startDate, $endDate));
            break;
        case 'reportByDepartment':
            echo json_encode($statModel->getReportCountByDepartment($startDate, $endDate));
            break;
        case 'teacherCount':
            echo json_encode(['count' => $statModel->getTeacherCount()]);
            break;
        case 'reportCount':
            echo json_encode(['count' => $statModel->getReportCount()]);
            break;
        case 'reportByTeacher':
            echo json_encode($statModel->getReportCountByTeacher($startDate, $endDate));
            break;
        case 'directorOverview':
            require_once __DIR__ . '/../models/SupervisionStats.php';
            require_once __DIR__ . '/../models/CertificateStats.php';
            
            $supervisionModel = new \App\Models\SupervisionStats();
            $certificateModel = new \App\Models\CertificateStats();
            
            $supervisionStats = $supervisionModel->getOverallStats();
            $certificateStats = $certificateModel->getOverallStats();
            
            echo json_encode([
                'totalReports' => $statModel->getReportCount(),
                'totalTeachers' => $statModel->getTeacherCount(),
                'totalSupervisions' => $supervisionStats['total_supervisions'] ?? 0,
                'totalCertificates' => $certificateStats['total_certificates'] ?? 0
            ]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
