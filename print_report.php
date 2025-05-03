<?php
// หน้าแสดงรายงานการสอนสำหรับพิมพ์
if (!isset($_GET['id'])) {
    echo "ไม่พบข้อมูล";
    exit;
}
$id = $_GET['id'];

require_once __DIR__ . '/models/TeachingReport.php';
use App\Models\TeachingReport;

$reportModel = new TeachingReport();
$report = $reportModel->getById($id);

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

// ดึง absent/sick/personal/activity students ถ้ามี
$absent = isset($report['absent_students']) ? $report['absent_students'] : '';
$sick = isset($report['sick_students']) ? $report['sick_students'] : '';
$personal = isset($report['personal_students']) ? $report['personal_students'] : '';
$activity = isset($report['activity_students']) ? $report['activity_students'] : '';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์รายงานการสอน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print { body { background: #fff; } }
        .print-block { page-break-inside: avoid; }
    </style>
</head>
<body class="p-8 text-sm bg-white">
    <div class="max-w-3xl mx-auto border rounded-lg shadow p-6 space-y-4 print-block">
        <h1 class="text-2xl font-bold text-blue-700 text-center mb-4">📘 รายงานการสอน</h1>
        <div><span class="font-semibold">📅 วันที่:</span> <?= formatThaiDate($report['report_date']) ?></div>
        <div><span class="font-semibold">📖 วิชา:</span> <?= htmlspecialchars($report['subject_name'] ?? '-') ?></div>
        <div><span class="font-semibold">🏫 ห้อง:</span> <?= htmlspecialchars($report['class_room']) ?></div>
        <div><span class="font-semibold">⏰ คาบ:</span> <?= htmlspecialchars($report['period_start']) ?> - <?= htmlspecialchars($report['period_end']) ?></div>
        <div><span class="font-semibold">📝 แผน / หัวข้อ:</span> <?= htmlspecialchars($report['plan_topic'] ?? '-') ?></div>
        <div><span class="font-semibold">👨‍🏫 กิจกรรมการเรียนรู้:</span>
            <div class="ml-4"><?= nl2br(htmlspecialchars($report['activity'] ?? '-')) ?></div>
        </div>
        <div>
            <div class="font-semibold">🙋‍♂️ สรุปการเข้าชั้นเรียน:</div>
            <div class="ml-4 space-y-1">
                <div><span class="font-medium">❌ ขาดเรียน:</span> <?= $absent ?: '<span class="text-gray-500">-</span>' ?></div>
                <div><span class="font-medium">🤒 ป่วย:</span> <?= $sick ?: '<span class="text-gray-500">-</span>' ?></div>
                <div><span class="font-medium">📝 ลากิจ:</span> <?= $personal ?: '<span class="text-gray-500">-</span>' ?></div>
                <div><span class="font-medium">🎉 กิจกรรม:</span> <?= $activity ?: '<span class="text-gray-500">-</span>' ?></div>
            </div>
        </div>
        <div><span class="font-semibold">💡 สะท้อนผลการสอน:</span></div>
        <div class="ml-4">
            <div><strong>K:</strong> <?= htmlspecialchars($report['reflection_k'] ?? '-') ?></div>
            <div><strong>P:</strong> <?= htmlspecialchars($report['reflection_p'] ?? '-') ?></div>
            <div><strong>A:</strong> <?= htmlspecialchars($report['reflection_a'] ?? '-') ?></div>
        </div>
        <div><span class="font-semibold">❗ ปัญหาที่พบ:</span> <div class="ml-4"><?= nl2br(htmlspecialchars($report['problems'] ?? '-')) ?></div></div>
        <div><span class="font-semibold">📝 ข้อเสนอแนะ:</span> <div class="ml-4"><?= nl2br(htmlspecialchars($report['suggestions'] ?? '-')) ?></div></div>
        <div class="flex gap-4 mt-4">
            <div>
                <div class="font-semibold">🖼️ รูปภาพ 1:</div>
                <?= $report['image1'] ? '<img src="' . htmlspecialchars('../' . $report['image1']) . '" class="mt-1 border rounded max-h-40">' : '<div class="text-gray-500">-</div>' ?>
            </div>
            <div>
                <div class="font-semibold">🖼️ รูปภาพ 2:</div>
                <?= $report['image2'] ? '<img src="' . htmlspecialchars('../' . $report['image2']) . '" class="mt-1 border rounded max-h-40">' : '<div class="text-gray-500">-</div>' ?>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
