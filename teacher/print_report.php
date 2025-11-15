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
    'เข้าร่วมกิจกรรม' => ['label' => 'กิจกรรม', 'emoji' => '🎉'],
    'โดดเรียน' => ['label' => 'โดดเรียน', 'emoji' => '🚫']
];

// ดึง absent_students, sick_students, personal_students จาก teaching_attendance_logs (แบบเดียวกับในรายงาน)
require_once __DIR__ . '/../classes/DatabaseUsers.php';
$dbUsers = new \App\DatabaseUsers();
$pdoUsers = $dbUsers->getPDO();

$statuses = [
    'ขาดเรียน' => [],
    'ลาป่วย' => [],
    'ลากิจ' => [],
    'เข้าร่วมกิจกรรม' => [],
    'โดดเรียน' => []
];
$sql2 = "SELECT student_id, status FROM teaching_attendance_logs WHERE report_id = ? AND status IN ('ขาดเรียน','ลาป่วย','ลากิจ','เข้าร่วมกิจกรรม','โดดเรียน')";
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
    'เข้าร่วมกิจกรรม' => [],
    'โดดเรียน' => []
];
foreach ($studentsInfo as $info) {
    if (isset($statuses[$info['status']])) {
        $statuses[$info['status']][] = $info['display'];
    }
}
// Helper to render ordered list for print (sorted by Stu_no already)
function buildOrderedList($items) {
    if (empty($items)) return '';
    $html = '<ol class="list-decimal ml-6 space-y-1">';
    foreach ($items as $it) {
        $html .= '<li class="text-sm">' . $it . '</li>';
    }
    $html .= '</ol>';
    return $html;
}

$absent = $statuses['ขาดเรียน'] ? '<div class="mb-2">' . buildOrderedList($statuses['ขาดเรียน']) . '</div>' : '';
$sick = $statuses['ลาป่วย'] ? '<div class="mb-2">' . buildOrderedList($statuses['ลาป่วย']) . '</div>' : '';
$personal = $statuses['ลากิจ'] ? '<div class="mb-2">' . buildOrderedList($statuses['ลากิจ']) . '</div>' : '';
$activity = $statuses['เข้าร่วมกิจกรรม'] ? '<div class="mb-2">' . buildOrderedList($statuses['เข้าร่วมกิจกรรม']) . '</div>' : '';
$truant = $statuses['โดดเรียน'] ? '<div class="mb-2">' . buildOrderedList($statuses['โดดเรียน']) . '</div>' : '';

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
        /* Remove default printer margins and ensure backgrounds print where supported */
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @media print {
            html, body { background: #fff; font-family: 'Sarabun', 'Tahoma', 'sans-serif'; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-block { page-break-inside: avoid; }
            /* Make print container use full page when printing (reserve a right margin of 0.5cm) */
            .print-block { width: calc(100vw - 1.5cm); max-width: calc(100vw - 1.5cm); margin: 0 1.5cm 0 0; padding: 18px; border: none; box-shadow: none !important; }
            /* Ensure header background/gradients are preserved when possible */
            .print-block, .print-block * { -webkit-print-color-adjust: exact; color-adjust: exact; print-color-adjust: exact; }
        }
        .print-block { page-break-inside: avoid; }
        .line-space { margin-bottom: 1.1em; }
        .section-title { font-size: 1.1rem; font-weight: 600; color: #1e40af; margin-bottom: 0.5em; }
        .attendance-block span { display: inline-block; margin-bottom: 0.2em; }
        .attendance-label { min-width: 90px; display: inline-block; }
        .signature-area { margin-top: 2.5rem; text-align: right; }
        .signature-box { display: inline-block; text-align: center; width: 320px; }
        .img-preview { max-height: 7rem; max-width: 10rem; object-fit: contain; border-radius: 0.5rem; border: 1px solid #e5e7eb; background: #f9fafb; }
        .img-preview-large { max-height: 10rem; max-width: 12rem; object-fit: contain; border-radius: 0.5rem; border: 1px solid #e5e7eb; background: #f9fafb; }
        .flex-wrap { flex-wrap: wrap; }
        .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .max-h-report { max-height: 96vh; overflow: hidden; }
        .print-header { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
        .school-name { font-weight:700; font-size:1.05rem; color:#0f172a; }
        .print-meta { text-align:right; font-size:0.95rem; color:#374151; }
    </style>
</head>
<body class="p-6 text-base bg-white max-h-report">
    <div class="no-print" style="text-align:center;padding:8px;background:#fff7ed;border:1px solid #fee2b3;margin-bottom:8px;border-radius:8px;font-size:0.95rem;color:#92400e;">
        หมายเหตุ: หากสีพื้นหลัง/กราฟิกไม่แสดงในหน้าต่างการพิมพ์ ให้ทำเครื่องหมายที่ "Background graphics" (หรือ "Print backgrounds") ในตัวเลือกการพิมพ์ของเบราว์เซอร์
    </div>
    <?php
        // counts for attendance summary
        $count_absent = count($statuses['ขาดเรียน']);
        $count_sick = count($statuses['ลาป่วย']);
        $count_personal = count($statuses['ลากิจ']);
        $count_activity = count($statuses['เข้าร่วมกิจกรรม']);
        $count_truant = isset($statuses['โดดเรียน']) ? count($statuses['โดดเรียน']) : 0;
        $count_total = $count_absent + $count_sick + $count_personal + $count_activity + $count_truant;
    ?>

    <div class="max-w-4xl mx-auto print-block bg-white rounded-xl shadow-lg border border-gray-200 px-6 py-6" style="box-shadow:0 4px 18px rgba(2,6,23,0.08);">
        <div style="background: linear-gradient(90deg, #ef4444, #f59e0b);padding:18px;border-radius:12px;color:#fff;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;">
            <div style="display:flex;gap:12px;align-items:center;">
                
                <div style="width:56px;height:56px;background:rgba(255,255,255,0.12);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px; box-shadow: 0 0 15px rgba(255, 255, 255, 0.7);">
                    <img src="../dist/img/logo-phicha.png" alt="">
                </div>
                
                <div>
                    <div style="font-weight:700;font-size:1.15rem;">โรงเรียนพิชัย</div>
                    <div style="opacity:0.95">📘 รายงานการสอน </div>
                </div>
            </div>
            
            <div style="text-align:right;font-size:0.95rem;">
                <div>ผู้สอน: <strong><?= htmlspecialchars($teacherName ?: '-') ?></strong></div>
                <div>วันที่พิมพ์: <strong><?= formatThaiDate(date('Y-m-d')) ?></strong></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;align-items:start;">
            <!-- Left: Main content -->
            <div>
                <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid #eef2ff;margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <div style="font-weight:700;font-size:1rem;color:#111827;">📖 วิชา: <?= htmlspecialchars($report['subject_name'] ?? '-') ?></div>
                        <div style="font-size:0.95rem;color:#6b7280;">ม.<?= htmlspecialchars($report['level'].'/' .$report['class_room']) ?> • <?= formatThaiDate($report['report_date']) ?></div>
                    </div>
                    <div style="color:#374151;">📝 หัวข้อ/แผน: <strong><?= htmlspecialchars($report['plan_topic'] ?? '-') ?></strong></div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div style="background:linear-gradient(180deg,#f8fafc,#eef2ff);padding:12px;border-radius:10px;border:1px solid #e6ecff;">
                        <div style="font-weight:700;color:#1e40af;margin-bottom:6px;">👨‍🏫 กิจกรรมการเรียนรู้</div>
                        <div style="color:#374151;"><?= nl2br(htmlspecialchars($report['activity'] ?? '-')) ?></div>
                    </div>
                    <div style="background:linear-gradient(180deg,#fff7ed,#fff1f2);padding:12px;border-radius:10px;border:1px solid #ffe7e6;">
                        <div style="font-weight:700;color:#b45309;margin-bottom:6px;">💡 สะท้อนผลการสอน (K/P/A)</div>
                        <div style="color:#374151;"><strong>K:</strong> <?= htmlspecialchars($report['reflection_k'] ?? '-') ?><br><strong>P:</strong> <?= htmlspecialchars($report['reflection_p'] ?? '-') ?><br><strong>A:</strong> <?= htmlspecialchars($report['reflection_a'] ?? '-') ?></div>
                    </div>
                </div>

                <div style="background:#ffffff;padding:12px;border-radius:10px;border:1px solid #eef2ff;margin-bottom:12px;">
                    <div style="font-weight:700;color:#111827;margin-bottom:6px;">❗ ปัญหา / ข้อเสนอแนะ</div>
                    <div style="color:#374151;"><?= nl2br(htmlspecialchars(($report['problems'] ?? '-') . "\n\n" . ($report['suggestions'] ?? '-'))) ?></div>
                </div>

                <div style="background:#fff;padding:12px;border-radius:10px;border:1px solid #eef2ff;">
                    <div style="font-weight:700;color:#111827;margin-bottom:8px;">📸 รูปภาพประกอบการสอน</div>
                    <div style="display:flex;gap:12px;align-items:center;justify-content:flex-start;flex-wrap:wrap;">
                        <?php if ($report['image1']): ?>
                            <img src="<?= htmlspecialchars('../' . $report['image1']) ?>" class="img-preview-large" alt="รูปภาพ 1">
                        <?php endif; ?>
                        <?php if ($report['image2']): ?>
                            <img src="<?= htmlspecialchars('../' . $report['image2']) ?>" class="img-preview-large" alt="รูปภาพ 2">
                        <?php endif; ?>
                        <?php if (!$report['image1'] && !$report['image2']): ?>
                            <div class="text-gray-400">-</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Summary / Infographic cards -->
            <div>
                <div style="background:linear-gradient(180deg,#ffffff,#f8fafc);padding:12px;border-radius:10px;border:1px solid #eef2ff;margin-bottom:12px;">
                    <div style="font-weight:700;color:#111827;margin-bottom:8px;">📊 สรุปการเข้าเรียน</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:10px;border-radius:8px;border:1px solid #eef2ff;">
                            <div style="display:flex;align-items:center;gap:8px;"><div style="font-size:18px;">❌</div><div>ขาดเรียน</div></div>
                            <div style="font-weight:700;color:#dc2626;"><?= $count_absent ?></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:10px;border-radius:8px;border:1px solid #eef2ff;">
                            <div style="display:flex;align-items:center;gap:8px;"><div style="font-size:18px;">🤒</div><div>ป่วย</div></div>
                            <div style="font-weight:700;color:#2563eb;"><?= $count_sick ?></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:10px;border-radius:8px;border:1px solid #eef2ff;">
                            <div style="display:flex;align-items:center;gap:8px;"><div style="font-size:18px;">📝</div><div>ลากิจ</div></div>
                            <div style="font-weight:700;color:#7c3aed;"><?= $count_personal ?></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:10px;border-radius:8px;border:1px solid #eef2ff;">
                            <div style="display:flex;align-items:center;gap:8px;"><div style="font-size:18px;">🎉</div><div>กิจกรรม</div></div>
                            <div style="font-weight:700;color:#6b21a8;"><?= $count_activity ?></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:10px;border-radius:8px;border:1px solid #eef2ff;">
                            <div style="display:flex;align-items:center;gap:8px;"><div style="font-size:18px;">🚫</div><div>โดดเรียน</div></div>
                            <div style="font-weight:700;color:#374151;"><?= $count_truant ?></div>
                        </div>
                        <div style="margin-top:6px;padding:8px;background:linear-gradient(90deg,#eef2ff,#f8fafc);border-radius:8px;text-align:center;font-weight:700;">รวม: <?= $count_total ?></div>
                    </div>
                </div>

                <div style="background:#fff;padding:12px;border-radius:10px;border:1px solid #eef2ff;">
                    <div style="font-weight:700;color:#111827;margin-bottom:8px;">รายละเอียดผู้สอน</div>
                    <div style="color:#374151;">ชื่อ: <strong><?= htmlspecialchars($teacherName ?: '-') ?></strong></div>
                    <div style="color:#374151;">วิชา: <strong><?= htmlspecialchars($report['subject_name'] ?? '-') ?></strong></div>
                    <div style="color:#374151;">คาบ: <strong><?= htmlspecialchars($report['period_start']) ?> - <?= htmlspecialchars($report['period_end']) ?></strong></div>
                </div>
            </div>
        </div>
        <div style="margin-top:18px;text-align:right;">
            <div class="signature-box">ลงชื่อ..............................................<br>(<?= htmlspecialchars($teacherName) ?>)</div>
        </div>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
