<?php
// หน้าแสดงรายงานการสอนสำหรับพิมพ์
if (!isset($_GET['id'])) {
    echo "ไม่พบข้อมูล";
    exit;
}
$id = $_GET['id'];

require_once __DIR__ . '/../models/TeachingReport.php';
use App\Models\TeachingReport;

$reportModel = new TeachingReport();
$report = $reportModel->getById($id);

// เพิ่ม: เชื่อมต่อฐานข้อมูล teaching_report โดยตรง (ไม่ใช้ $reportModel->pdo)
require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
$dbTeaching = new \App\DatabaseTeachingReport();
$pdoTeaching = $dbTeaching->getPDO();

// กำหนด mapping สำหรับ label ภาษาไทย
$statusLabelMap = [
    'ขาดเรียน' => ['label' => 'ขาด', 'emoji' => '❌'],
    'ลาป่วย' => ['label' => 'ป่วย', 'emoji' => '🤒'],
    'ลากิจ' => ['label' => 'ลากิจ', 'emoji' => '📝'],
    'มาเรียน' => ['label' => 'มา', 'emoji' => '✅'],
    'มาสาย' => ['label' => 'สาย', 'emoji' => '⏰'],
    'เข้าร่วมกิจกรรม' => ['label' => 'กิจกรรม', 'emoji' => '🎉']
];

// ดึง absent_students, sick_students, personal_students จาก teaching_attendance_logs (แบบเดียวกับในรายงาน)
require_once __DIR__ . '/../classes/DatabaseUsers.php';
$dbUsers = new \App\DatabaseUsers();
$pdoUsers = $dbUsers->getPDO();

$statuses = [
    'ขาดเรียน' => [],
    'ลาป่วย' => [],
    'ลากิจ' => [],
    'เข้าร่วมกิจกรรม' => []
];
$sql2 = "SELECT student_id, status FROM teaching_attendance_logs WHERE report_id = ? AND status IN ('ขาดเรียน','ลาป่วย','ลากิจ','เข้าร่วมกิจกรรม')";
$stmt2 = $pdoTeaching->prepare($sql2);
$stmt2->execute([$id]);
$logs = $stmt2->fetchAll();

$studentsInfo = [];
foreach ($logs as $log) {
    $stuId = $log['student_id'];
    $stmtStu = $pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id = ?");
    $stmtStu->execute([$stuId]);
    $stu = $stmtStu->fetch();
    $stu_no = $stu && isset($stu['Stu_no']) ? (int)$stu['Stu_no'] : 0;
    $label = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['label'] : $log['status'];
    $emoji = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['emoji'] : '';
    $display = $stu
        ? (
            '<span style="display:inline-block;margin-right:4px;">[' . ($stu['Stu_no'] ?? '-') . '][' . $stu['Stu_id'] . ']' . htmlspecialchars($stu['fullname']) . '</span> <span style="font-weight:bold;">' . $emoji . ' ' . $label . '</span>'
        )
        : '<span>' . $stuId . '</span> <span style="font-weight:bold;">' . $emoji . ' ' . $label . '</span>';
    $studentsInfo[] = [
        'stu_no' => $stu_no,
        'status' => $log['status'],
        'display' => $display
    ];
}
usort($studentsInfo, function($a, $b) {
    return $a['stu_no'] <=> $b['stu_no'];
});
$statuses = [
    'ขาดเรียน' => [],
    'ลาป่วย' => [],
    'ลากิจ' => [],
    'เข้าร่วมกิจกรรม' => []
];
foreach ($studentsInfo as $info) {
    if (isset($statuses[$info['status']])) {
        $statuses[$info['status']][] = $info['display'];
    }
}
$absent = $statuses['ขาดเรียน'] ? '<div class="flex flex-wrap gap-2">'.implode('', array_map(function($s){return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ขาดเรียน'])).'</div>' : '';
$sick = $statuses['ลาป่วย'] ? '<div class="flex flex-wrap gap-2">'.implode('', array_map(function($s){return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลาป่วย'])).'</div>' : '';
$personal = $statuses['ลากิจ'] ? '<div class="flex flex-wrap gap-2">'.implode('', array_map(function($s){return '<span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลากิจ'])).'</div>' : '';
$activity = $statuses['เข้าร่วมกิจกรรม'] ? '<div class="flex flex-wrap gap-2">'.implode('', array_map(function($s){return '<span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['เข้าร่วมกิจกรรม'])).'</div>' : '';

if (!$report) {
    echo "ไม่พบข้อมูลรายงาน";
    exit;
}

// ฟังก์ชันแปลงวันที่เป็นไทย
function formatThaiDate($dateStr) {
    if (!$dateStr) return '-';
    $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $d = strtotime($dateStr);
    if (!$d) return $dateStr;
    $day = date('j', $d);
    $month = $months[(int)date('n', $d)];
    $year = date('Y', $d) + 543;
    return "{$day} {$month} {$year}";
}

// ดึงชื่อครูผู้สอน
$teacherName = '';
if (!empty($report['teacher_id'])) {
    $stmtT = $pdoUsers->prepare("SELECT Teach_name FROM teacher WHERE Teach_id = ?");
    $stmtT->execute([$report['teacher_id']]);
    $rowT = $stmtT->fetch();
    if ($rowT && !empty($rowT['Teach_name'])) {
        $teacherName = $rowT['Teach_name'];
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์รายงานการสอน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            font-family: 'Sarabun', 'Tahoma', 'sans-serif';
            background: #fff;
        }
        @media print {
            html, body { background: #fff; font-family: 'Sarabun', 'Tahoma', 'sans-serif'; }
            .no-print { display: none !important; }
            .print-block { page-break-inside: avoid; }
        }
        .print-block { page-break-inside: avoid; }
        .line-space { margin-bottom: 1.1em; }
        .section-title { font-size: 1.1rem; font-weight: 600; color: #1e40af; margin-bottom: 0.5em; }
        .attendance-block span { display: inline-block; margin-bottom: 0.2em; }
        .attendance-label { min-width: 90px; display: inline-block; }
        .signature-area { margin-top: 2.5rem; text-align: right; }
        .signature-box { display: inline-block; text-align: center; width: 320px; }
        .img-preview { max-height: 7rem; max-width: 10rem; object-fit: contain; border-radius: 0.5rem; border: 1px solid #e5e7eb; background: #f9fafb; }
        .flex-wrap { flex-wrap: wrap; }
        .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .max-h-report { max-height: 96vh; overflow: hidden; }
    </style>
</head>
<body class="p-6 text-base bg-white max-h-report">
    <div class="max-w-3xl mx-auto print-block bg-white rounded-lg shadow border border-gray-200 px-8 py-6" style="box-shadow:0 2px 8px #0001;">
        <h1 class="text-xl font-bold text-blue-700 text-center mb-4">📘 รายงานการสอน</h1>
        <div class="flex flex-row justify-between items-start mb-2 gap-4">
            <div class="w-1/2 text-left">
                <div class="line-space"><span class="font-semibold">📖 วิชา:</span> <span class="truncate"><?= htmlspecialchars($report['subject_name'] ?? '-') ?></span></div>
                <div class="line-space"><span class="font-semibold">🏫 ระดับชั้น:</span> ม.<?= htmlspecialchars($report['class_room']) ?></div>
                <div class="line-space"><span class="font-semibold">📝 แผน / หัวข้อ:</span> <span class="truncate"><?= htmlspecialchars($report['plan_topic'] ?? '-') ?></span></div>
            </div>
            <div class="w-1/2 text-left">
                <div class="line-space"><span class="font-semibold">📅 วันที่:</span> <?= formatThaiDate($report['report_date']) ?></div>
                <div class="line-space"><span class="font-semibold">⏰ คาบ:</span> <?= htmlspecialchars($report['period_start']) ?> - <?= htmlspecialchars($report['period_end']) ?></div>
            </div>
        </div>
        <div class="line-space">
            <div class="section-title">👨‍🏫 กิจกรรมการเรียนรู้</div>
            <div class="ml-4 text-gray-800"><?= nl2br(htmlspecialchars($report['activity'] ?? '-')) ?></div>
        </div>
        <div class="line-space">
            <div class="section-title">🙋‍♂️ สรุปการเข้าชั้นเรียน</div>
            <div class="ml-4 attendance-block">
                <div class="mb-1 flex items-start gap-2">
                    <span class="attendance-label font-medium text-red-700">❌ ขาดเรียน:</span>
                    <span><?= $absent ?: '<span class="text-gray-400">-</span>' ?></span>
                </div>
                <div class="mb-1 flex items-start gap-2">
                    <span class="attendance-label font-medium text-blue-700">🤒 ป่วย:</span>
                    <span><?= $sick ?: '<span class="text-gray-400">-</span>' ?></span>
                </div>
                <div class="mb-1 flex items-start gap-2">
                    <span class="attendance-label font-medium text-indigo-700">📝 ลากิจ:</span>
                    <span><?= $personal ?: '<span class="text-gray-400">-</span>' ?></span>
                </div>
                <div class="mb-1 flex items-start gap-2">
                    <span class="attendance-label font-medium text-purple-700">🎉 กิจกรรม:</span>
                    <span><?= $activity ?: '<span class="text-gray-400">-</span>' ?></span>
                </div>
            </div>
        </div>
        <div class="line-space">
            <div class="section-title">💡 สะท้อนผลการสอน</div>
            <div class="ml-4">
                <div class="mb-1"><strong>K:</strong> <?= htmlspecialchars($report['reflection_k'] ?? '-') ?></div>
                <div class="mb-1"><strong>P:</strong> <?= htmlspecialchars($report['reflection_p'] ?? '-') ?></div>
                <div class="mb-1"><strong>A:</strong> <?= htmlspecialchars($report['reflection_a'] ?? '-') ?></div>
            </div>
        </div>
        <div class="line-space">
            <div class="section-title">❗ ปัญหาที่พบ</div>
            <div class="ml-4"><?= nl2br(htmlspecialchars($report['problems'] ?? '-')) ?></div>
        </div>
        <div class="line-space">
            <div class="section-title">📝 ข้อเสนอแนะ</div>
            <div class="ml-4"><?= nl2br(htmlspecialchars($report['suggestions'] ?? '-')) ?></div>
        </div>
        <div class="section-title mb-2">📸 รูปภาพประกอบการสอน</div>
        <div class="flex flex-row gap-8 mb-8 items-center justify-center">
            <?php if ($report['image1']): ?>
                <img src="<?= htmlspecialchars('../' . $report['image1']) ?>" class="img-preview" alt="รูปภาพ 1">
            <?php endif; ?>
            <?php if ($report['image2']): ?>
                <img src="<?= htmlspecialchars('../' . $report['image2']) ?>" class="img-preview" alt="รูปภาพ 2">
            <?php endif; ?>
            <?php if (!$report['image1'] && !$report['image2']): ?>
                <div class="text-gray-400">-</div>
            <?php endif; ?>
        </div>
        <div class="signature-area">
            <div class="signature-box">
                ลงชื่อ......................................................<br>
                (<?= htmlspecialchars($teacherName) ?>)<br>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
