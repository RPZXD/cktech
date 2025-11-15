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
    WHERE s.created_by = ? AND s.status = 1
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
            $display = [
                'code' => $row['code'],
                'name' => $row['subject_name'],
                'level_room' => ''
            ];
        } else {
            $levelText = isset($row['level']) ? 'ม.' . intval($row['level']) : '';
            $display = [
                'code' => $row['code'],
                'name' => $row['subject_name'],
                'level_room' => ($levelText ?? '') . ($row['class_room'] ? '/' . preg_replace('/^ห้อง\s*/u', '', $row['class_room']) : '')
            ];
        }
        $timetable[$row['day_of_week']][$p][$row['class_room']] = [
            'display' => $display,
            'type' => $type,
            'colorClass' => $colorClass,
            'code' => $row['code'],
            'subject_name' => $row['subject_name'],
            'showRoom' => !empty($row['subject_type']),
            'class_room' => $row['class_room']
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
/* Modern UI additions */
.card-lg { border-radius: 1rem; box-shadow: 0 18px 40px rgba(2,6,23,0.06); }
.btn-gradient { background-image: linear-gradient(90deg,#2563eb,#7c3aed); color: white; }
.subject-chip { display:inline-flex; flex-direction:column; align-items:center; gap:0.18rem; padding:0.35rem 0.6rem; border-radius: 0.6rem; font-weight:600; font-size:0.9rem; line-height:1; text-align:center }
.subject-chip .code { font-weight:800; font-size:0.92rem; line-height:1 }
.subject-chip .name { max-width: 180px; white-space: normal; }
.legend-chip { display:inline-flex; align-items:center; gap:0.5rem; padding:0.4rem 0.75rem; border-radius: 9999px; font-weight:600; font-size:0.9rem }
.fade-in { animation: fadeInUp .5s ease both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity:1; transform: translateY(0);} }
.ripple { position:relative; overflow:hidden; }
.ripple:after { content:''; position:absolute; width:100px; height:100px; background:rgba(255,255,255,0.25); display:block; transform:scale(0); border-radius:50%; opacity:0; pointer-events:none; transition: transform .6s, opacity .6s; }
.ripple.active:after { transform:scale(6); opacity:1; transition: transform .6s, opacity .9s; }
.tooltip { position:relative; }
.tooltip[data-title]:hover::after { content: attr(data-title); position:absolute; bottom:calc(100% + 8px); left:50%; transform:translateX(-50%); background:rgba(2,6,23,0.85); color:#fff; padding:6px 8px; border-radius:6px; font-size:12px; white-space:nowrap; z-index:60; }
/* Popover panel theme (ใช้สีธีมโรงเรียน ถ้ามี) */
.popover-panel { pointer-events: none; box-shadow: 0 14px 40px rgba(2,6,23,0.12); border-radius: 0.75rem; padding: 0.65rem 0.9rem; color: #083344; font-size: 0.92rem; }
.popover-panel .title { font-weight:700; color: #07213a; }
.popover-panel .meta { color: #475569; font-size:0.85rem; margin-top:6px; }
.popover-panel.accent { border-left: 4px solid <?php echo isset($global['primaryColor']) ? $global['primaryColor'] : '#2563eb'; ?>; background: linear-gradient(180deg, #ffffff 0%, rgba(37,99,235,0.03) 100%); }
/* Fit-to-viewport helper: smooth scale when needed to avoid vertical scrolling */
.table-fit-wrapper { transition: height 220ms ease, transform 220ms ease; will-change: transform, height; }
.table-fit-wrapper table { transition: none; transform-origin: top left; }
/* Action buttons (print / export) */
.action-btn { display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 0.85rem; border-radius:9999px; font-weight:600; box-shadow:0 8px 20px rgba(2,6,23,0.08); border: none; cursor: pointer; transition: transform .14s ease, box-shadow .14s ease; }
.action-btn:hover { transform: translateY(-3px); box-shadow:0 18px 40px rgba(2,6,23,0.12); }
.action-icon { font-size:1rem; line-height:1 }
.action-label { font-size:0.95rem }
.btn-indigo { background: linear-gradient(90deg,#4f46e5,#2563eb); color: white }
.btn-violet { background: linear-gradient(90deg,#7c3aed,#6d28d9); color: white }
.btn-green { background: linear-gradient(90deg,#10b981,#059669); color: white }
.btn-yellow { background: linear-gradient(90deg,#f59e0b,#f97316); color: white }
/* Ensure action buttons are always visible above content */
.page-actions { position: absolute; right: 18px; top: 18px; z-index: 220; display:flex; gap:0.5rem; }
.action-btn { position: relative; z-index: 230; }
/* Ensure parent container allows absolute positioning */
.max-w-8xl { position: relative; }
/* Mobile: convert actions into a fixed floating stack (right-bottom) for guaranteed visibility */
@media (max-width: 768px) {
    .page-actions { position: fixed !important; right: 14px; bottom: 14px; top: auto; flex-direction: column-reverse; background: transparent; padding: 0; gap: 10px; }
    .page-actions .action-btn { padding: 0.55rem; border-radius: 0.75rem; box-shadow: 0 10px 30px rgba(2,6,23,0.12); }
    .action-label { display: none; }
}
/* Compact levels for small screens: reduce font-size and padding progressively */
.compact-1 table, .compact-1 th, .compact-1 td { font-size: 0.95rem; }
.compact-1 td { padding: 0.45rem 0.5rem; }
.compact-1 .subject-chip { padding: 0.28rem 0.45rem; font-size:0.85rem }
.compact-2 table, .compact-2 th, .compact-2 td { font-size: 0.9rem; }
.compact-2 td { padding: 0.4rem 0.45rem; }
.compact-2 .subject-chip { padding: 0.24rem 0.4rem; font-size:0.82rem }
.compact-3 table, .compact-3 th, .compact-3 td { font-size: 0.82rem; }
.compact-3 td { padding: 0.32rem 0.36rem; }
.compact-3 .subject-chip { padding: 0.18rem 0.32rem; font-size:0.78rem }
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
                <div class="mb-6 text-lg text-gray-700 flex items-center gap-4">
                <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-blue-700 font-semibold shadow-sm">👨‍🏫 <?= htmlspecialchars($_SESSION['user']['Teach_name'] ?? '') ?></span>
                <span class="inline-block rounded-full bg-yellow-100 px-3 py-1 text-yellow-700 font-semibold shadow-sm"> <?= htmlspecialchars($_SESSION['user']['Teach_major'] ?? '') ?></span>

                <div class="ml-auto flex items-center gap-2 page-actions">
                    <button id="btnPrint" onclick="printTimetable()" class="action-btn btn-indigo" data-title="พิมพ์ตารางสอน"> <span class="action-icon">🖨️</span><span class="action-label">พิมพ์</span></button>
                    <button id="btnExportPDF" class="action-btn btn-violet" data-title="ดาวน์โหลดเป็น PDF"> <span class="action-icon">📄</span><span class="action-label">PDF</span></button>
                    <button id="btnExportCSV" class="action-btn btn-green" data-title="ส่งออกเป็น CSV"> <span class="action-icon">📥</span><span class="action-label">CSV</span></button>
                    <button id="btnExportXLSX" class="action-btn btn-yellow" data-title="ดาวน์โหลดเป็น Excel (XLSX)"> <span class="action-icon">📊</span><span class="action-label">XLSX</span></button>
                </div>
                </div>
            <?php if (empty($rows)): ?>
                <div class="text-gray-500 text-center py-10 text-xl">😢 ไม่พบข้อมูลตารางสอน</div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-lg shadow table-fit-wrapper">
                <table class="min-w-full border border-gray-300 mb-4 rounded-lg overflow-hidden bg-white" id="timetableTable">
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
                                    <span class="text-base">
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
                                        foreach ($classRooms as $classRoom) {
                                            if (isset($timetable[$day][$p][$classRoom])) {
                                                $cell = $timetable[$day][$p][$classRoom];
                                                $display = $cell['display'];
                                                $levelRoom = '';
                                                if ($cell['showRoom'] && $display['level_room']) {
                                                    $levelRoom = htmlspecialchars($display['level_room']);
                                                }
                                                // Build a modern chip per class entry
                                                // include data attributes for popover and export
                                                $codeAttr = htmlspecialchars($display['code']);
                                                $nameAttr = htmlspecialchars($display['name']);
                                                $levelRoomAttr = $levelRoom ? htmlspecialchars($display['level_room']) : '';
                                                $typeAttr = htmlspecialchars($cell['type']);
                                                $classRoomAttr = htmlspecialchars($cell['class_room']);
                                                $chip = '<div class="subject-chip ripple ' . $cell['colorClass'] . ' border" style="border-width:1.25px;" '
                                                    . 'data-code="' . $codeAttr . '" '
                                                    . 'data-name="' . $nameAttr . '" '
                                                    . 'data-levelroom="' . $levelRoomAttr . '" '
                                                    . 'data-type="' . $typeAttr . '" '
                                                    . 'data-classroom="' . $classRoomAttr . '">'
                                                    . '<div class="code">'. $codeAttr .'</div>'
                                                    . '<div class="name text-sm" style="max-width:220px; white-space:normal;">'. $nameAttr .'</div>'
                                                    . ($levelRoom ? '<div class="text-xs text-indigo-600 mt-1">(' . $levelRoom . ')</div>' : '')
                                                    . '</div>';
                                                $cellContent[] = $chip;
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
<!-- html2pdf for client-side PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<!-- SheetJS (XLSX) for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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
<!-- UI/UX Enhancements: ripple, copy-on-click, tooltips, fade-in rows -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    $('body').addClass('sidebar-collapse');
    // Ripple effect on clickable chips
    document.querySelectorAll('.ripple').forEach(function(el){
        el.addEventListener('click', function(e){
            el.classList.remove('active');
            void el.offsetWidth; // reflow
            el.classList.add('active');
            setTimeout(()=> el.classList.remove('active'), 700);
        });
    });

    // Copy subject info on chip click (shows toast)
    document.querySelectorAll('.subject-chip').forEach(function(chip){
        chip.addEventListener('click', function(){
            const code = chip.querySelector('.code')?.textContent || '';
            const name = chip.querySelector('.name')?.textContent || '';
            const txt = code + ' - ' + name;
            if(navigator.clipboard){
                navigator.clipboard.writeText(txt).then(()=>{
                    Swal.fire({ toast:true, position:'top-end', icon:'success', title:'คัดลอก: '+txt, showConfirmButton:false, timer:1200 });
                });
            }
        });
    });

    // Fade-in rows when in view
    const rows = document.querySelectorAll('table tbody tr');
    const obs = new IntersectionObserver((entries)=>{
        entries.forEach(ent=>{
            if(ent.isIntersecting){ ent.target.classList.add('fade-in'); obs.unobserve(ent.target); }
        });
    },{threshold:0.08});
    rows.forEach(r=>obs.observe(r));

    // Tooltip data-title already styled by CSS; ensure attribute exists for print button
    document.querySelectorAll('.tooltip').forEach(el=>{ if(!el.getAttribute('data-title') && el.getAttribute('title')) el.setAttribute('data-title', el.getAttribute('title')); });
    // Popover element (themed)
    const pop = document.createElement('div'); pop.className = 'popover-panel accent hidden';
    pop.style.position = 'fixed'; pop.style.minWidth = '220px'; pop.style.pointerEvents = 'none'; document.body.appendChild(pop);
    let popTimeout;
    document.querySelectorAll('.subject-chip').forEach(function(chip){
        chip.addEventListener('mouseenter', function(e){
            clearTimeout(popTimeout);
            const code = chip.getAttribute('data-code') || '';
            const name = chip.getAttribute('data-name') || '';
            const lvl = chip.getAttribute('data-levelroom') || '';
            const type = chip.getAttribute('data-type') || '';
            const room = chip.getAttribute('data-classroom') || '';
            pop.innerHTML = `<div class="font-semibold">${code} — ${name}</div><div class="text-xs text-gray-500 mt-1">${type} ${lvl ? ' • '+lvl : ''}${room ? ' • ห้อง '+room : ''}</div>`;
            pop.style.left = (e.pageX + 12) + 'px';
            pop.style.top = (e.pageY + 12) + 'px';
            pop.classList.remove('hidden');
        });
        chip.addEventListener('mousemove', function(e){ pop.style.left = (e.pageX + 12) + 'px'; pop.style.top = (e.pageY + 12) + 'px'; });
        chip.addEventListener('mouseleave', function(){ popTimeout = setTimeout(()=> pop.classList.add('hidden'), 120); });
    });

    // Export CSV logic
    document.getElementById('btnExportCSV')?.addEventListener('click', function(){
        const rows = [];
        const table = document.querySelector('table');
        if(!table) return;
        const tbodyRows = table.querySelectorAll('tbody tr');
        tbodyRows.forEach(function(tr){
            const tds = tr.querySelectorAll('td');
            if(tds.length < 2) return;
            const day = tds[0].innerText.trim();
            for(let i=1;i<tds.length;i++){
                const period = i; // since first td is day
                const cell = tds[i];
                const chips = cell.querySelectorAll('.subject-chip');
                if(chips.length === 0){
                    rows.push([day, period, '', '-', '', '', '']);
                } else {
                    chips.forEach(function(ch){
                        const code = ch.getAttribute('data-code') || '';
                        const name = ch.getAttribute('data-name') || '';
                        const lvl = ch.getAttribute('data-levelroom') || '';
                        const type = ch.getAttribute('data-type') || '';
                        const room = ch.getAttribute('data-classroom') || '';
                        rows.push([day, period, code, name, lvl, room, type]);
                    });
                }
            }
        });
        // Build CSV
        const header = ['วัน','คาบ','รหัสวิชา','ชื่อวิชา','ระดับ/ห้อง','ห้อง','กลุ่มวิชา'];
        const csv = [header].concat(rows).map(r=> r.map(c=> '"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href = url; a.download = 'timetable_export_'+(new Date().toISOString().slice(0,10))+'.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    });

    // Export PDF logic using html2pdf (improved: dynamic orientation, better scale, clean clone, progress UI)
    document.getElementById('btnExportPDF')?.addEventListener('click', function(){
        const sourceEl = document.querySelector('.overflow-x-auto') || document.querySelector('.content-wrapper');
        if(!sourceEl) return;
        const teacherName = <?= json_encode($_SESSION['user']['Teach_name'] ?? '') ?> || 'teacher';

        Swal.fire({ title: 'กำลังสร้าง PDF...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        // Clone the content to avoid mutating the live page and strip interactive bits
        const clone = sourceEl.cloneNode(true);
        // Remove interactive controls that shouldn't appear in PDF
        clone.querySelectorAll('.page-actions, .ripple, .popover-panel, .action-btn').forEach(e=> e.remove());
        // Ensure table expands to available width for better layout
        clone.style.maxWidth = '100%';
        clone.style.boxShadow = 'none';
        clone.style.background = '#ffffff';

        // Create a wrapper that mimics print margins
        const wrapper = document.createElement('div');
        wrapper.className = 'pdf-export-wrapper';
        wrapper.style.background = '#fff';
        wrapper.style.padding = '10mm';
        wrapper.appendChild(clone);
        // Hide it off-screen but keep it renderable
        wrapper.style.position = 'fixed';
        wrapper.style.left = '0';
        wrapper.style.top = '-10000px';
        document.body.appendChild(wrapper);

        // Choose orientation based on content width
        const contentWidth = clone.scrollWidth || clone.offsetWidth || sourceEl.offsetWidth;
        const orientation = contentWidth > 900 ? 'landscape' : 'portrait';

        // Choose a reasonable html2canvas scale for crisp output (cap to avoid huge memory)
        const devicePR = window.devicePixelRatio || 1;
        const scale = Math.min(2.5, Math.max(1.2, devicePR * 1.25));

        const filenameSafe = ('timetable_' + (new Date().toISOString().slice(0,10)) + '_' + teacherName).replace(/[^a-z0-9\-_.\u0E00-\u0E7F]/ig, '_');

        const opt = {
            margin: 10, // mm
            filename: filenameSafe + '.pdf',
            image: { type: 'jpeg', quality: 0.97 },
            html2canvas: { scale: scale, useCORS: true, logging: false, allowTaint: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: orientation },
            pagebreak: { mode: ['css', 'legacy'] }
        };

        // Generate PDF then cleanup
        try {
            html2pdf().set(opt).from(wrapper).save().then(() => {
                document.body.removeChild(wrapper);
                Swal.close();
            }).catch((err)=>{
                document.body.removeChild(wrapper);
                Swal.close();
                console.error('PDF export error:', err);
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถสร้าง PDF ได้' });
            });
        } catch (e) {
            document.body.removeChild(wrapper);
            Swal.close();
            console.error(e);
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถสร้าง PDF ได้' });
        }
    });
    // Export XLSX (SheetJS)
    document.getElementById('btnExportXLSX')?.addEventListener('click', function(){
        const rows = [];
        const table = document.querySelector('table');
        if(!table) return;
        const tbodyRows = table.querySelectorAll('tbody tr');
        tbodyRows.forEach(function(tr){
            const tds = tr.querySelectorAll('td');
            if(tds.length < 2) return;
            const day = tds[0].innerText.trim();
            for(let i=1;i<tds.length;i++){
                const period = i;
                const cell = tds[i];
                const chips = cell.querySelectorAll('.subject-chip');
                if(chips.length === 0){
                    rows.push([day, period, '', '-', '', '', '']);
                } else {
                    chips.forEach(function(ch){
                        const code = ch.getAttribute('data-code') || '';
                        const name = ch.getAttribute('data-name') || '';
                        const lvl = ch.getAttribute('data-levelroom') || '';
                        const type = ch.getAttribute('data-type') || '';
                        const room = ch.getAttribute('data-classroom') || '';
                        rows.push([day, period, code, name, lvl, room, type]);
                    });
                }
            }
        });
        const header = ['วัน','คาบ','รหัสวิชา','ชื่อวิชา','ระดับ/ห้อง','ห้อง','กลุ่มวิชา'];
        const aoa = [header].concat(rows);
        // Create workbook and sheet
        const ws = XLSX.utils.aoa_to_sheet(aoa);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Timetable');
        const filename = 'timetable_'+(new Date().toISOString().slice(0,10))+'.xlsx';
        XLSX.writeFile(wb, filename);
    });
    // Fit table to viewport height to avoid vertical scrolling on small screens
    function fitTableToViewport(){
        const wrapper = document.querySelector('.table-fit-wrapper');
        const table = document.getElementById('timetableTable');
        if(!wrapper || !table) return;
        // Only apply compacting on small screens (mobile)
        const mobileBreakpoint = 768; // px
        // remove any compact classes first
        wrapper.classList.remove('compact-1','compact-2','compact-3');
        wrapper.style.height = '';
        wrapper.style.overflowY = '';
        if(window.innerWidth > mobileBreakpoint){
            return; // do nothing on larger screens
        }

        const wrapperRect = wrapper.getBoundingClientRect();
        const available = window.innerHeight - wrapperRect.top - 80; // reserve some space for header/footer
        if(available <= 120) return;

        // Try progressive compact levels until table fits
        const levels = ['compact-1','compact-2','compact-3'];
        for(let i=0;i<levels.length;i++){
            const lvl = levels[i];
            wrapper.classList.add(lvl);
            // allow reflow
            const tableRect = table.getBoundingClientRect();
            if(tableRect.height <= available) {
                // fits now
                wrapper.style.height = tableRect.height + 'px';
                wrapper.style.overflowY = 'hidden';
                return;
            }
            // else try next level
            wrapper.classList.remove(lvl);
        }

        // if still doesn't fit after all compactions, apply the tightest and clamp wrapper height
        wrapper.classList.add('compact-3');
        wrapper.style.height = available + 'px';
        wrapper.style.overflowY = 'hidden';
    }
    fitTableToViewport();
    // respond to resize and orientation change
    let fitTimer = null;
    window.addEventListener('resize', ()=>{ clearTimeout(fitTimer); fitTimer = setTimeout(fitTableToViewport, 180); });
    window.addEventListener('orientationchange', ()=> setTimeout(fitTableToViewport, 250));
});
</script>
<style>
@page { size: A4 portrait; margin: 10mm; }
@media print {
    /* hide interactive controls */
    .print\:hidden, .page-actions { display: none !important; }
    body, html {
        background: #fff !important;
    }
    .content-wrapper, .max-w-8xl, .max-w-6xl, .max-w-5xl, .max-w-4xl {
        box-shadow: none !important;
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .shadow, .shadow-lg, .rounded, .rounded-xl, .rounded-lg, .overflow-x-auto {
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    /* Ensure table uses full portrait width and wrap content to fit one page */
    .overflow-x-auto { overflow: visible !important; }
    table { width: 100% !important; border-collapse: collapse !important; table-layout: fixed !important; }
    th, td { page-break-inside: avoid; word-break: break-word; white-space: normal; }
    /* Reduce sizes for print to help fit on a single portrait page */
    table, th, td { font-size: 10px !important; }
    .timetable-cell {
        background: #fff !important;
        color: #000 !important;
        font-weight: normal !important;
        box-shadow: none !important;
        font-size: 10px !important;
        padding: 4px !important;
        line-height: 1.0 !important;
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
