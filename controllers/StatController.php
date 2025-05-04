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
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
