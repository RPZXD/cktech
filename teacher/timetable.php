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
    SELECT s.name AS subject_name, s.code, s.level, sc.class_room, sc.day_of_week, sc.period_start, sc.period_end
    FROM subjects s
    JOIN subject_classes sc ON s.id = sc.subject_id
    WHERE s.created_by = ?
    ORDER BY sc.day_of_week, sc.period_start, sc.class_room
");
$stmt->execute([$teacherId]);
$rows = $stmt->fetchAll();

// สร้าง array ตารางสอน [day][period][class_room] = subject
$timetable = [];
foreach ($rows as $row) {
    for ($p = $row['period_start']; $p <= $row['period_end']; $p++) {
        // ดึงระดับชั้นของวิชานี้
        $levelText = '';
        if (isset($row['level'])) {
            $levelText = 'ม.' . intval($row['level']);
        }
        $timetable[$row['day_of_week']][$p][$row['class_room']] =
            $row['subject_name'] . " (" . $row['code'] . ")"
            . ($levelText ? " <span class=\"text-xs text-indigo-600\">[$levelText]</span>" : "");
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
        <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg p-6">
            <h1 class="text-3xl font-extrabold text-blue-700 mb-4 flex items-center gap-2">
                🗓️ ตารางสอนของครู
            </h1>
            <div class="mb-6 text-lg text-gray-700 flex items-center gap-2">
                <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-blue-700 font-semibold shadow-sm">👨‍🏫 <?= htmlspecialchars($_SESSION['user']['Teach_name'] ?? '') ?></span>
                <span class="inline-block rounded-full bg-yellow-100 px-3 py-1 text-yellow-700 font-semibold shadow-sm"> <?= htmlspecialchars($_SESSION['user']['Teach_major'] ?? '') ?></span>
            </div>
            <?php if (empty($rows)): ?>
                <div class="text-gray-500 text-center py-10 text-xl">😢 ไม่พบข้อมูลตารางสอน</div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full border border-gray-300 mb-4 rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-200 to-blue-400">
                            <th class="border px-2 py-2 text-center font-bold text-blue-900 timetable-day w-32">📅 วัน</th>
                            <?php for ($p = 1; $p <= $maxPeriod; $p++): ?>
                                <th class="border px-2 py-2 text-center font-bold text-blue-900 timetable-day">
                                    <span class="inline-block bg-blue-100 rounded-full px-2 py-1 shadow text-base">⏰ คาบ <?= $p ?></span>
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
                                        // รวมทุกห้องในแต่ละคาบ
                                        $cellContent = [];
                                        foreach ($classRooms as $classRoom) {
                                            if (isset($timetable[$day][$p][$classRoom])) {
                                                $cellContent[] = '<span class="inline-block bg-green-100 text-green-800 rounded px-2 py-1 shadow-sm animate-pulse mb-1">'
                                                    . '📚 ' . $timetable[$day][$p][$classRoom]
                                                    . ' <span class="text-xs text-gray-500">[' . htmlspecialchars($classRoom) . ']</span>'
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
</script>
<?php require_once('script.php'); ?>
</body>
</html>
