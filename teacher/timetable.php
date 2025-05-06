<?php 
session_start();
// เช็ค session และ role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ครู') {
    header('Location: ../login.php');
    exit;
}
// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
require_once('../classes/DatabaseTeachingReport.php');
$teacherId = $_SESSION['user']['Teach_id'];

$db = new \App\DatabaseTeachingReport();
$pdo = $db->getPDO();

// ดึงข้อมูลตารางสอนของครู
$stmt = $pdo->prepare("
    SELECT s.name AS subject_name, s.code, s.level, sc.class_room, sc.day_of_week, sc.period_start, sc.period_end, s.subject_type
    FROM subjects s
    JOIN subject_classes sc ON s.id = sc.subject_id
    WHERE s.created_by = ?
    ORDER BY sc.day_of_week, sc.period_start, sc.class_room
");
$stmt->execute([$teacherId]);
$rows = $stmt->fetchAll();

// กำหนดสีแต่ละกลุ่มวิชา
$subjectTypeColors = [
    'พื้นฐาน' => 'bg-green-100 text-green-800 border-green-300',
    'เพิ่มเติม' => 'bg-blue-100 text-blue-800 border-blue-300',
    'กิจกรรมพัฒนาผู้เรียน' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'อื่นๆ' => 'bg-gray-100 text-gray-800 border-gray-300'
];

// ดึงข้อมูล subject_type ของแต่ละวิชา
$subjectTypeMap = [];
foreach ($rows as $row) {
    $subjectTypeMap[$row['code']] = $row['subject_type'] ?? 'อื่นๆ';
}

// สร้าง array ตารางสอน [day][period][class_room] = subject
$timetable = [];
foreach ($rows as $row) {
    for ($p = $row['period_start']; $p <= $row['period_end']; $p++) {
        $type = $row['subject_type'] ?? 'อื่นๆ';
        $colorClass = $subjectTypeColors[$type] ?? $subjectTypeColors['อื่นๆ'];
        // เงื่อนไข: ถ้า subject_type ว่าง ไม่ต้องแสดง level กับ room
        if (empty($row['subject_type'])) {
            $display = $row['subject_name'] . " (" . $row['code'] . ")";
        } else {
            $levelText = isset($row['level']) ? 'ม.' . intval($row['level']) : '';
            $display = $row['subject_name'] . " (" . $row['code'] . ")" .
                ($levelText ? " <span class=\"text-xs text-indigo-600\">[$levelText]</span>" : "");
        }
        $timetable[$row['day_of_week']][$p][$row['class_room']] = [
            'display' => $display,
            'type' => $type,
            'colorClass' => $colorClass,
            'code' => $row['code'],
            'subject_name' => $row['subject_name'],
            'showRoom' => !empty($row['subject_type'])
        ];
    }
}

// รายชื่อวัน
$days = ['จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์'];
$maxPeriod = 8; // กำหนดคาบสูงสุดเป็น 8 เสมอ

// หา class_room ทั้งหมด
$classRooms = [];
foreach ($rows as $row) {
    $classRooms[$row['class_room']] = true;
}
$classRooms = array_keys($classRooms);
sort($classRooms);

require_once('header.php');
?>
<style>
/* เพิ่มเงาและลูกเล่นให้ cell ตาราง */
.timetable-cell {
  transition: background 0.2s, color 0.2s;
}
.timetable-cell:not(.empty):hover {
  background: #dbeafe !important;
  color: #1e40af !important;
  font-weight: bold;
  cursor: pointer;
}
.timetable-day {
  background: linear-gradient(90deg, #f0f9ff 0%, #e0e7ff 100%);
}
.timetable-room {
  background: linear-gradient(90deg, #fef9c3 0%, #fef3c7 100%);
}
</style>

<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper bg-gray-50 min-h-screen p-4">
        <div class="max-w-8xl mx-auto bg-white rounded-xl shadow-lg p-6">
            <h1 class="text-3xl font-extrabold text-blue-700 mb-4 flex items-center gap-2">
                🗓️ ตารางสอนของครู
            </h1>
            <div class="mb-6 text-lg text-gray-700 flex items-center gap-2">
                <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-blue-700 font-semibold shadow-sm">👨‍🏫 <?= htmlspecialchars($_SESSION['user']['Teach_name'] ?? '') ?></span>
                <span class="inline-block rounded-full bg-yellow-100 px-3 py-1 text-yellow-700 font-semibold shadow-sm"> <?= htmlspecialchars($_SESSION['user']['Teach_major'] ?? '') ?></span>
                <button onclick="printTimetable()" class="ml-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 print:hidden">
                    🖨️ พิมพ์ตารางสอน
                </button>
            </div>
            <?php if (empty($rows)): ?>
                <div class="text-gray-500 text-center py-10 text-xl">😢 ไม่พบข้อมูลตารางสอน</div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full border border-gray-300 mb-4 rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-200 to-blue-400">
                            <th class="border px-2 py-2 text-center font-bold text-blue-900 timetable-day w-32">📅 วัน</th>
                            <?php
                                $periodTimes = [
                                    1 => '08:30–09:25',
                                    2 => '09:25–10:20',
                                    3 => '10:20–11:15',
                                    4 => '11:15–12:10',
                                    5 => '12:10–13:05',
                                    6 => '13:05–14:00',
                                    7 => '14:00–14:55',
                                    8 => '14:55–15:50'
                                ];
                                for ($p = 1; $p <= $maxPeriod; $p++): 
                            ?>
                                <th class="border px-2 py-2 text-center font-bold text-blue-900 timetable-day">
                                    <span class="inline-block bg-blue-100 rounded-full px-2 py-1 shadow text-base">
                                        ⏰ คาบ <?= $p ?><br>
                                        <span class="text-xs text-gray-600"><?= $periodTimes[$p] ?? '' ?></span>
                                    </span>
                                </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($days as $day): ?>
                            <tr class="hover:bg-blue-50 transition">
                                <td class="border px-2 py-2 text-center font-semibold bg-blue-50 timetable-day">
                                    <?= $day ?>
                                    <?php
                                        $emojis = [
                                            'จันทร์'=>'🌞','อังคาร'=>'🔥','พุธ'=>'🌳','พฤหัสบดี'=>'⚡','ศุกร์'=>'💧'
                                        ];
                                        echo isset($emojis[$day]) ? '<span class="ml-1">'.$emojis[$day].'</span>' : '';
                                    ?>
                                </td>
                                <?php for ($p = 1; $p <= $maxPeriod; $p++): ?>
                                    <?php
                                        $cellContent = [];
                                        $periodTimes = [
                                            1 => '08:30–09:25',
                                            2 => '09:25–10:20',
                                            3 => '10:20–11:15',
                                            4 => '11:15–12:10',
                                            5 => '12:10–13:05',
                                            6 => '13:05–14:00',
                                            7 => '14:00–14:55',
                                            8 => '14:55–15:50'
                                        ];
                                        foreach ($classRooms as $classRoom) {
                                            if (isset($timetable[$day][$p][$classRoom])) {
                                                $cell = $timetable[$day][$p][$classRoom];
                                                $roomHtml = ($cell['showRoom'] ?? false) ? ' <span class="text-xs text-gray-500">[' . htmlspecialchars($classRoom) . ']</span>' : '';
                                                $cellContent[] = '<span class="inline-block '.$cell['colorClass'].' rounded px-2 py-1 shadow-sm animate-pulse mb-1 border" style="border-width:1.5px">'
                                                    . '<br>'
                                                    . '📚 ' . $cell['display']
                                                    . $roomHtml
                                                    . '</span>';
                                            }
                                        }
                                    ?>
                                    <td class="border px-2 py-2 text-center text-sm timetable-cell <?= $cellContent ? '' : 'empty text-gray-300 bg-gray-50' ?>">
                                        <?php
                                            if ($cellContent) {
                                                echo implode('<br>', $cellContent);
                                            } else {
                                                echo '<span>-</span>';
                                            }
                                        ?>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <!-- Legend/คำอธิบายสี -->
                <div class="mt-8">
                    <div class="font-bold text-lg mb-2 flex items-center gap-2">📝 คำอธิบายสีแต่ละกลุ่มวิชา</div>
                    <div class="flex flex-wrap gap-4 mb-4">
                        <?php foreach ($subjectTypeColors as $type => $colorClass): ?>
                            <span class="inline-block px-3 py-1 rounded border <?= $colorClass ?> font-semibold"><?= $type ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="font-bold text-lg mb-2 flex items-center gap-2">📚 รายชื่อวิชาในตาราง</div>
                    <ul class="list-disc pl-6">
                        <?php
                        // แสดงรายชื่อวิชาไม่ซ้ำ พร้อมกลุ่มวิชา
                        $shown = [];
                        foreach ($rows as $row) {
                            $code = $row['code'];
                            if (!isset($shown[$code])) {
                                $type = $row['subject_type'] ?? 'อื่นๆ';
                                $colorClass = $subjectTypeColors[$type] ?? $subjectTypeColors['อื่นๆ'];
                                echo '<li class="mb-1"><span class="inline-block px-2 py-0.5 rounded border '.$colorClass.'">' .
                                    htmlspecialchars($row['subject_name']) . ' (' . htmlspecialchars($code) . ')' .
                                    '</span> <span class="text-xs text-gray-500">[' . $type . ']</span></li>';
                                $shown[$code] = true;
                            }
                        }
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
  <!-- /.content-wrapper -->
    <?php require_once('../footer.php');?>
</div>
<!-- ./wrapper -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
// เพิ่มลูกเล่น highlight cell ตาราง
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.timetable-cell:not(.empty)').forEach(function(cell) {
        cell.addEventListener('mouseenter', function() {
            cell.classList.add('ring', 'ring-blue-400', 'scale-105');
        });
        cell.addEventListener('mouseleave', function() {
            cell.classList.remove('ring', 'ring-blue-400', 'scale-105');
        });
    });
});

// ฟังก์ชันพิมพ์ตารางสอน
function printTimetable() {
    window.print();
}
</script>
<style>
@media print {
    .print\:hidden { display: none !important; }
    body, html {
        background: #fff !important;
    }
    .content-wrapper, .max-w-8xl, .max-w-6xl, .max-w-5xl, .max-w-4xl {
        box-shadow: none !important;
        background: #fff !important;
    }
    .shadow, .shadow-lg, .rounded, .rounded-xl, .rounded-lg, .overflow-x-auto {
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .timetable-cell {
        background: #fff !important;
        color: #000 !important;
        font-weight: normal !important;
        box-shadow: none !important;
    }
    .timetable-day {
        background: #f0f9ff !important;
    }
    .timetable-room {
        background: #fef9c3 !important;
    }
}
</style>
<?php require_once('script.php'); ?>
</body>
</html>
