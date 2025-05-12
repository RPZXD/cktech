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

// ดึงรายวิชาของครู
$stmt = $pdo->prepare("SELECT id, name, code, level FROM subjects WHERE created_by = ? ORDER BY code");
$stmt->execute([$teacherId]);
$subjects = $stmt->fetchAll();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/student/analyze.php";

require_once('header.php');
?>


<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper bg-gray-50 min-h-screen p-4">
    <div class="max-w-8xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-bold text-blue-700 mb-4 flex items-center gap-2">🔗 ลิงก์สำหรับนักเรียนกรอกแบบวิเคราะห์ผู้เรียนรายบุคคล</h1>
        <div class="mb-6 text-lg text-gray-700 flex items-center gap-2">
            <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-blue-700 font-semibold shadow-sm">👨‍🏫 <?= htmlspecialchars($_SESSION['user']['Teach_name'] ?? '') ?></span>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <ul class="flex border-b" id="studentTabs">
                <li><button class="tab-btn px-4 py-2 font-semibold text-blue-700 border-b-2 border-blue-700 bg-blue-50" data-tab="tab-link">🔗 ลิงก์สำหรับนักเรียน</button></li>
                <li><button class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:text-blue-700" data-tab="tab-report">📊 รายงานผล</button></li>
                <li><button class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:text-blue-700" data-tab="tab-all">📋 ข้อมูลทั้งหมด</button></li>
            </ul>
        </div>

        <!-- Tab: ลิงก์สำหรับนักเรียน -->
        <div id="tab-link" class="tab-content">
            <?php if (empty($subjects)): ?>
                <div class="text-gray-500 text-center py-10 text-xl">ไม่พบรายวิชาของคุณ</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-blue-100">
                                <th class="border px-3 py-2 text-center">รหัสวิชา</th>
                                <th class="border px-3 py-2 text-center">ชื่อวิชา</th>
                                <th class="border px-3 py-2 text-center">ระดับชั้น</th>
                                <th class="border px-3 py-2 text-center">ลิงก์สำหรับนักเรียน</th>
                                <th class="border px-3 py-2 text-center">คัดลอก</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $sub): ?>
                                <tr class="hover:bg-blue-50">
                                    <td class="border px-3 py-2 text-center"><?= htmlspecialchars($sub['code']) ?></td>
                                    <td class="border px-3 py-2"><?= htmlspecialchars($sub['name']) ?></td>
                                    <td class="border px-3 py-2 text-center"><?= 'ม.' . intval($sub['level']) ?></td>
                                    <td class="border px-3 py-2 text-center">
                                        <a href="<?= $baseUrl . '?subject_id=' . $sub['id'] ?>" target="_blank" class="text-blue-600 underline">
                                            <?= $baseUrl . '?subject_id=' . $sub['id'] ?>
                                        </a>
                                    </td>
                                    <td class="border px-3 py-2 text-center">
                                        <button class="copy-link-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded" data-link="<?= $baseUrl . '?subject_id=' . $sub['id'] ?>">คัดลอก</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 text-gray-600 text-sm">
                    <span class="font-bold text-blue-700">หมายเหตุ:</span> ส่งลิงก์แต่ละวิชาให้นักเรียนในวิชานั้นๆ เพื่อให้นักเรียนเข้ามากรอกแบบวิเคราะห์ผู้เรียนรายบุคคล
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: รายงานผล -->
        <div id="tab-report" class="tab-content hidden">
            <div class="mb-4 flex items-center gap-2">
                <label class="font-semibold">เลือกวิชา:</label>
                <select id="reportSubject" class="border rounded px-2 py-1 ml-2">
                    <option value="">-- เลือกวิชา --</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?> (<?= htmlspecialchars($sub['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button id="printReportBtn" class="ml-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 print:hidden">
                    🖨️ พิมพ์รายงานผล
                </button>
            </div>
            <div id="reportContent" class="mt-4">
                <div class="text-gray-400 text-center">กรุณาเลือกวิชาเพื่อดูรายงาน</div>
            </div>
        </div>

        <!-- Tab: ข้อมูลทั้งหมด -->
        <div id="tab-all" class="tab-content hidden">
            <div class="mb-4 flex items-center gap-2">
                <label class="font-semibold">เลือกวิชา:</label>
                <select id="allSubject" class="border rounded px-2 py-1 ml-2">
                    <option value="">-- เลือกวิชา --</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?> (<?= htmlspecialchars($sub['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchStudent" class="border rounded px-2 py-1 ml-2" placeholder="ค้นหาชื่อ/เลขที่/ห้อง">
                <button id="printAllBtn" class="ml-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 print:hidden">
                    🖨️ พิมพ์ข้อมูลทั้งหมด
                </button>
            </div>
            <div id="allContent" class="mt-4">
                <div class="text-gray-400 text-center">กรุณาเลือกวิชาเพื่อดูข้อมูล</div>
            </div>
        </div>
    </div>
</div>
  <!-- /.content-wrapper -->
    <?php require_once('../footer.php');?>
</div>
<!-- ./wrapper -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switch
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('border-b-2', 'border-blue-700', 'bg-blue-50', 'text-blue-700'));
            btn.classList.add('border-b-2', 'border-blue-700', 'bg-blue-50', 'text-blue-700');
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById(btn.dataset.tab).classList.remove('hidden');
        });
    });

    // Copy link
    document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const link = btn.getAttribute('data-link');
            navigator.clipboard.writeText(link).then(function() {
                btn.textContent = 'คัดลอกแล้ว!';
                setTimeout(() => { btn.textContent = 'คัดลอก'; }, 1200);
            });
        });
    });

    // รายงานผล (กราฟ)
    $('#reportSubject').on('change', function() {
        const subjectId = $(this).val();
        if (!subjectId) {
            $('#reportContent').html('<div class="text-gray-400 text-center">กรุณาเลือกวิชาเพื่อดูรายงาน</div>');
            return;
        }
        $('#reportContent').html('<div class="text-gray-400 text-center">กำลังโหลด...</div>');
        $.getJSON('../controllers/StudentAnalyzeController.php?subject_id=' + subjectId, function(res) {
            if (!res.success || !res.data.length) {
                $('#reportContent').html('<div class="text-gray-400 text-center">ไม่พบข้อมูลนักเรียน</div>');
                return;
            }
            // สถิติ
            let male = 0, female = 0, other = 0;
            let likeSubjects = {};
            let gpaArr = [], comArr = [];
            let roomSet = {};
            res.data.forEach(row => {
                // เพศจากคำนำหน้า
                if (row.prefix.startsWith('ด.ช.') || row.prefix.startsWith('นาย')) male++;
                else if (row.prefix.startsWith('ด.ญ.') || row.prefix.startsWith('นางสาว') || row.prefix.startsWith('น.ส.') || row.prefix.startsWith('นาง')) female++;
                else other++;
                // วิชาที่ชอบ
                (row.like_subjects || '').split(',').forEach(s => {
                    s = s.trim();
                    if (s) likeSubjects[s] = (likeSubjects[s]||0)+1;
                });
                // เกรดเฉลี่ย
                if (!isNaN(parseFloat(row.gpa))) gpaArr.push(parseFloat(row.gpa));
                // เกรดคอม
                if (!isNaN(parseFloat(row.last_com_grade))) comArr.push(parseFloat(row.last_com_grade));
                // ห้องเรียน
                if (row.student_level_room) roomSet[row.student_level_room] = true;
            });
            // กราฟเพศ
            let genderCanvas = '<canvas id="genderChart" height="60"></canvas>';
            // กราฟวิชาที่ชอบ
            let likeSubjectsCanvas = '<canvas id="likeSubjectsChart" height="120"></canvas>';
            // กราฟเกรดเฉลี่ย
            let gpaAvg = gpaArr.length ? (gpaArr.reduce((a,b)=>a+b,0)/gpaArr.length).toFixed(2) : '-';
            let comAvg = comArr.length ? (comArr.reduce((a,b)=>a+b,0)/comArr.length).toFixed(2) : '-';
            let totalStudents = res.data.length;
            let totalRooms = Object.keys(roomSet).length;

            $('#reportContent').html(`
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">สัดส่วนเพศ</div>
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            ${genderCanvas}
                        </div>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">วิชาที่นักเรียนชอบ (Top 5)</div>
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            ${likeSubjectsCanvas}
                        </div>
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-blue-50 rounded p-4 text-center">
                        <div class="font-bold text-blue-700">เกรดเฉลี่ย (GPA)</div>
                        <div class="text-3xl font-extrabold text-blue-600">${gpaAvg}</div>
                    </div>
                    <div class="bg-green-50 rounded p-4 text-center">
                        <div class="font-bold text-green-700">ผลการเรียนวิชาคอมพิวเตอร์</div>
                        <div class="text-3xl font-extrabold text-green-600">${comAvg}</div>
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-yellow-50 rounded p-4 text-center">
                        <div class="font-bold text-yellow-700">จำนวนนักเรียนทั้งหมด</div>
                        <div class="text-3xl font-extrabold text-yellow-600">${totalStudents}</div>
                    </div>
                    <div class="bg-purple-50 rounded p-4 text-center">
                        <div class="font-bold text-purple-700">จำนวนห้องเรียน</div>
                        <div class="text-3xl font-extrabold text-purple-600">${totalRooms}</div>
                    </div>
                </div>
            `);

            // Chart เพศ
            new Chart(document.getElementById('genderChart'), {
                type: 'doughnut',
                data: {
                    labels: ['ชาย', 'หญิง', 'อื่นๆ'],
                    datasets: [{
                        data: [male, female, other],
                        backgroundColor: ['#60a5fa', '#f472b6', '#fbbf24']
                    }]
                },
                options: {responsive: true, plugins: {legend: {position: 'bottom'}}}
            });
            // Chart วิชาที่ชอบ
            let likeLabels = Object.keys(likeSubjects).sort((a,b)=>likeSubjects[b]-likeSubjects[a]).slice(0,5);
            let likeData = likeLabels.map(l=>likeSubjects[l]);
            new Chart(document.getElementById('likeSubjectsChart'), {
                type: 'bar',
                data: {
                    labels: likeLabels,
                    datasets: [{
                        label: 'จำนวน',
                        data: likeData,
                        backgroundColor: '#38bdf8'
                    }]
                },
                options: {responsive: true, plugins: {legend: {display: false}}}
            });
        });
    });

    // ข้อมูลทั้งหมด
    $('#allSubject').on('change', loadAllStudents);
    $('#searchStudent').on('input', loadAllStudents);

    function loadAllStudents() {
        const subjectId = $('#allSubject').val();
        const search = $('#searchStudent').val().toLowerCase();
        if (!subjectId) {
            $('#allContent').html('<div class="text-gray-400 text-center">กรุณาเลือกวิชาเพื่อดูข้อมูล</div>');
            return;
        }
        $('#allContent').html('<div class="text-gray-400 text-center">กำลังโหลด...</div>');
        $.getJSON('../controllers/StudentAnalyzeController.php?subject_id=' + subjectId, function(res) {
            if (!res.success || !res.data.length) {
                $('#allContent').html('<div class="text-gray-400 text-center">ไม่พบข้อมูลนักเรียน</div>');
                return;
            }
            let html = `
                <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden text-xs md:text-sm">
                    <thead>
                        <tr class="bg-blue-100">
                            <th class="border px-2 py-1">เลขที่</th>
                            <th class="border px-2 py-1">ชื่อ-สกุล</th>
                            <th class="border px-2 py-1">ห้อง</th>
                            <th class="border px-2 py-1">คำนำหน้า</th>
                            <th class="border px-2 py-1">เบอร์โทร</th>
                            <th class="border px-2 py-1">น้ำหนัก</th>
                            <th class="border px-2 py-1">ส่วนสูง</th>
                            <th class="border px-2 py-1">โรค</th>
                            <th class="border px-2 py-1">ผู้ปกครอง</th>
                            <th class="border px-2 py-1">อาศัยกับ</th>
                            <th class="border px-2 py-1">ที่อยู่</th>
                            <th class="border px-2 py-1">เบอร์ผู้ปกครอง</th>
                            <th class="border px-2 py-1">กิจกรรม</th>
                            <th class="border px-2 py-1">ความสามารถพิเศษ</th>
                            <th class="border px-2 py-1">GPA</th>
                            <th class="border px-2 py-1">เกรดคอม</th>
                            <th class="border px-2 py-1">วิชาที่ชอบ</th>
                            <th class="border px-2 py-1">วิชาที่ไม่ชอบ</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            res.data.forEach(row => {
                let searchText = [
                    row.student_no, row.student_firstname, row.student_lastname, row.student_level_room
                ].join(' ').toLowerCase();
                if (search && !searchText.includes(search)) return;
                html += `
                    <tr class="hover:bg-blue-50">
                        <td class="border px-2 py-1 text-center">${row.student_no}</td>
                        <td class="border px-2 py-1">${row.prefix} ${row.student_firstname} ${row.student_lastname}</td>
                        <td class="border px-2 py-1 text-center">${row.student_level_room}</td>
                        <td class="border px-2 py-1 text-center">${row.prefix}</td>
                        <td class="border px-2 py-1 text-center">${row.student_phone}</td>
                        <td class="border px-2 py-1 text-center">${row.weight}</td>
                        <td class="border px-2 py-1 text-center">${row.height}</td>
                        <td class="border px-2 py-1">${row.disease}</td>
                        <td class="border px-2 py-1">${row.parent_name}</td>
                        <td class="border px-2 py-1">${row.live_with}</td>
                        <td class="border px-2 py-1">${row.address}</td>
                        <td class="border px-2 py-1 text-center">${row.parent_phone}</td>
                        <td class="border px-2 py-1">${row.favorite_activity}</td>
                        <td class="border px-2 py-1">${row.special_skill}</td>
                        <td class="border px-2 py-1 text-center">${row.gpa}</td>
                        <td class="border px-2 py-1 text-center">${row.last_com_grade}</td>
                        <td class="border px-2 py-1">${row.like_subjects}</td>
                        <td class="border px-2 py-1">${row.dislike_subjects}</td>
                    </tr>
                `;
            });
            html += '</tbody></table></div>';
            $('#allContent').html(html);
        });
    }

    // Print buttons
    $('#printReportBtn').on('click', function() {
        const tab = document.getElementById('reportContent');
        printSection(tab);
    });
    $('#printAllBtn').on('click', function() {
        const tab = document.getElementById('allContent');
        printSection(tab);
    });

    function printSection(section) {
        const printWindow = window.open('', '', 'width=900,height=700');
        printWindow.document.write('<html><head><title>พิมพ์รายงาน</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">');
        printWindow.document.write('<style>body{font-family:sans-serif;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(section.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        setTimeout(() => printWindow.print(), 400);
    }
});
</script>
<?php require_once('script.php'); ?>
</body>
</html>
