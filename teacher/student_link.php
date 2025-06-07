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
require_once('../classes/DatabaseUsers.php');
$teacherId = $_SESSION['user']['Teach_id'];

$db = new \App\DatabaseTeachingReport();
$dbUser = new \App\DatabaseUsers();
$pdo = $db->getPDO();
$pdoUser = $dbUser->getPDO();

// ดึงข้อมูลกลุ่มสาระของครู
$teacherMajor = '';
$stmtTeacher = $pdoUser->prepare("SELECT Teach_major FROM teacher WHERE Teach_id = ?");
$stmtTeacher->execute([$teacherId]);
$teacher = $stmtTeacher->fetch();
if ($teacher) {
    $teacherMajor = $teacher['Teach_major'];
}

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
                <button id="excelReportBtn" class="ml-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 print:hidden">
                    📊 ส่งออก Excel
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
                <button id="excelAllBtn" class="ml-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 print:hidden">
                    📊 ส่งออก Excel
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // เพิ่มตัวแปร teacher major
    const teacherMajor = <?= json_encode($teacherMajor) ?>;
    
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

            // ย้ายตรงนี้ขึ้นมา
            let likeLabels = Object.keys(likeSubjects).sort((a,b)=>likeSubjects[b]-likeSubjects[a]).slice(0,5);
            let likeData = likeLabels.map(l=>likeSubjects[l]);

            // การวิเคราะห์เพิ่มเติม
            let weightArr = [], heightArr = [];
            let diseaseCount = {}, activityCount = {}, skillCount = {};
            let liveWithCount = {};
            res.data.forEach(row => {
                // น้ำหนัก ส่วนสูง
                if (!isNaN(parseFloat(row.weight))) weightArr.push(parseFloat(row.weight));
                if (!isNaN(parseFloat(row.height))) heightArr.push(parseFloat(row.height));
                
                // โรคประจำตัว
                let disease = (row.disease || '').trim();
                if (disease && disease !== '-' && disease !== 'ไม่มี') {
                    diseaseCount[disease] = (diseaseCount[disease] || 0) + 1;
                }
                
                // กิจกรรมที่ชอบ
                (row.favorite_activity || '').split(',').forEach(act => {
                    act = act.trim();
                    if (act) activityCount[act] = (activityCount[act] || 0) + 1;
                });
                
                // ความสามารถพิเศษ
                (row.special_skill || '').split(',').forEach(skill => {
                    skill = skill.trim();
                    if (skill) skillCount[skill] = (skillCount[skill] || 0) + 1;
                });
                
                // อาศัยกับ
                let liveWith = (row.live_with || '').trim();
                if (liveWith) liveWithCount[liveWith] = (liveWithCount[liveWith] || 0) + 1;
            });
            
            let weightAvg = weightArr.length ? (weightArr.reduce((a,b)=>a+b,0)/weightArr.length).toFixed(1) : '-';
            let heightAvg = heightArr.length ? (heightArr.reduce((a,b)=>a+b,0)/heightArr.length).toFixed(1) : '-';
            
            $('#reportContent').html(`
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">สัดส่วนเพศ</div>
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            ${genderCanvas}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            <div>ชาย: ${male} คน (${((male/totalStudents)*100).toFixed(1)}%)</div>
                            <div>หญิง: ${female} คน (${((female/totalStudents)*100).toFixed(1)}%)</div>
                            ${other > 0 ? `<div>อื่นๆ: ${other} คน (${((other/totalStudents)*100).toFixed(1)}%)</div>` : ''}
                        </div>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">วิชาที่นักเรียนชอบ (Top 5)</div>
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            ${likeSubjectsCanvas}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            ${likeLabels.map((label, i) => `<div>${i+1}. ${label}: ${likeData[i]} คน</div>`).join('')}
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded p-4 text-center">
                        <div class="font-bold text-blue-700">เกรดเฉลี่ย (GPA)</div>
                        <div class="text-3xl font-extrabold text-blue-600">${gpaAvg}</div>
                    </div>
                    <div class="bg-green-50 rounded p-4 text-center">
                        <div class="font-bold text-green-700">ผลการเรียนวิชา${teacherMajor || ''}</div>
                        <div class="text-3xl font-extrabold text-green-600">${comAvg}</div>
                    </div>
                    <div class="bg-yellow-50 rounded p-4 text-center">
                        <div class="font-bold text-yellow-700">จำนวนนักเรียนทั้งหมด</div>
                        <div class="text-3xl font-extrabold text-yellow-600">${totalStudents}</div>
                    </div>
                    <div class="bg-purple-50 rounded p-4 text-center">
                        <div class="font-bold text-purple-700">จำนวนห้องเรียน</div>
                        <div class="text-3xl font-extrabold text-purple-600">${totalRooms}</div>
                    </div>
                </div>
                
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-orange-50 rounded p-4">
                        <div class="font-bold text-orange-700 mb-2">ข้อมูลทางกาย</div>
                        <div class="text-sm">
                            <div>น้ำหนักเฉลี่ย: <span class="font-semibold">${weightAvg} กก.</span></div>
                            <div>ส่วนสูงเฉลี่ย: <span class="font-semibold">${heightAvg} ซม.</span></div>
                        </div>
                    </div>
                    <div class="bg-pink-50 rounded p-4">
                        <div class="font-bold text-pink-700 mb-2">การอยู่อาศัย</div>
                        <div class="text-sm">
                            ${Object.entries(liveWithCount).sort((a,b)=>b[1]-a[1]).slice(0,3).map(([key, val]) => 
                                `<div>${key}: ${val} คน (${((val/totalStudents)*100).toFixed(1)}%)</div>`
                            ).join('')}
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded p-4">
                        <div class="font-bold text-gray-700 mb-2">โรคประจำตัว (Top 5)</div>
                        <div class="text-sm">
                            ${Object.entries(diseaseCount).sort((a,b)=>b[1]-a[1]).slice(0,5).map(([key, val]) => 
                                `<div>• ${key}: ${val} คน</div>`
                            ).join('') || '<div class="text-gray-500">ไม่มีข้อมูล</div>'}
                        </div>
                    </div>
                    <div class="bg-cyan-50 rounded p-4">
                        <div class="font-bold text-cyan-700 mb-2">กิจกรรมที่ชอบ (Top 5)</div>
                        <div class="text-sm">
                            ${Object.entries(activityCount).sort((a,b)=>b[1]-a[1]).slice(0,5).map(([key, val]) => 
                                `<div>• ${key}: ${val} คน</div>`
                            ).join('') || '<div class="text-gray-500">ไม่มีข้อมูล</div>'}
                        </div>
                    </div>
                    <div class="bg-indigo-50 rounded p-4">
                        <div class="font-bold text-indigo-700 mb-2">ความสามารถพิเศษ (Top 5)</div>
                        <div class="text-sm">
                            ${Object.entries(skillCount).sort((a,b)=>b[1]-a[1]).slice(0,5).map(([key, val]) => 
                                `<div>• ${key}: ${val} คน</div>`
                            ).join('') || '<div class="text-gray-500">ไม่มีข้อมูล</div>'}
                        </div>
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

    // Excel export buttons
    $('#excelReportBtn').on('click', function() {
        const subjectId = $('#reportSubject').val();
        if (!subjectId) {
            alert('กรุณาเลือกวิชาก่อน');
            return;
        }
        exportReportToExcel(subjectId);
    });
    
    $('#excelAllBtn').on('click', function() {
        const subjectId = $('#allSubject').val();
        if (!subjectId) {
            alert('กรุณาเลือกวิชาก่อน');
            return;
        }
        exportAllToExcel(subjectId);
    });

    function exportReportToExcel(subjectId) {
        $.getJSON('../controllers/StudentAnalyzeController.php?subject_id=' + subjectId, function(res) {
            if (!res.success || !res.data.length) {
                alert('ไม่พบข้อมูลนักเรียน');
                return;
            }
            
            // สร้างข้อมูลสถิติ
            let male = 0, female = 0, other = 0;
            let likeSubjects = {}, gpaArr = [], comArr = [];
            res.data.forEach(row => {
                if (row.prefix.startsWith('ด.ช.') || row.prefix.startsWith('นาย')) male++;
                else if (row.prefix.startsWith('ด.ญ.') || row.prefix.startsWith('นางสาว') || row.prefix.startsWith('น.ส.') || row.prefix.startsWith('นาง')) female++;
                else other++;
                
                (row.like_subjects || '').split(',').forEach(s => {
                    s = s.trim();
                    if (s) likeSubjects[s] = (likeSubjects[s]||0)+1;
                });
                if (!isNaN(parseFloat(row.gpa))) gpaArr.push(parseFloat(row.gpa));
                if (!isNaN(parseFloat(row.last_com_grade))) comArr.push(parseFloat(row.last_com_grade));
            });
            
            let gpaAvg = gpaArr.length ? (gpaArr.reduce((a,b)=>a+b,0)/gpaArr.length).toFixed(2) : 0;
            let comAvg = comArr.length ? (comArr.reduce((a,b)=>a+b,0)/comArr.length).toFixed(2) : 0;
            
            // สร้าง worksheet สถิติ
            const statsData = [
                ['รายงานสถิตินักเรียน'],
                [''],
                ['ข้อมูลทั่วไป'],
                ['จำนวนนักเรียนทั้งหมด', res.data.length],
                ['นักเรียนชาย', male],
                ['นักเรียนหญิง', female],
                ['เกรดเฉลี่ย (GPA)', gpaAvg],
                [`เกรด${teacherMajor || 'คอมพิวเตอร์'}เฉลี่ย`, comAvg],
                [''],
                ['วิชาที่นักเรียนชอบ (Top 5)'],
                ...Object.entries(likeSubjects).sort((a,b)=>b[1]-a[1]).slice(0,5).map(([subj, count]) => [subj, count])
            ];
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(statsData);
            XLSX.utils.book_append_sheet(wb, ws, 'สถิติ');
            
            // บันทึกไฟล์
            const subjectName = $('#reportSubject option:selected').text().split('(')[0].trim();
            XLSX.writeFile(wb, `รายงานสถิติ_${subjectName}_${new Date().toLocaleDateString('th-TH')}.xlsx`);
        });
    }
    
    function exportAllToExcel(subjectId) {
        $.getJSON('../controllers/StudentAnalyzeController.php?subject_id=' + subjectId, function(res) {
            if (!res.success || !res.data.length) {
                alert('ไม่พบข้อมูลนักเรียน');
                return;
            }
            
            // สร้างข้อมูลสำหรับ Excel
            const headers = [
                'เลขที่', 'คำนำหน้า', 'ชื่อ', 'นามสกุล', 'ห้อง', 'เบอร์โทร', 'น้ำหนัก', 'ส่วนสูง',
                'โรคประจำตัว', 'ชื่อผู้ปกครอง', 'อาศัยกับ', 'ที่อยู่', 'เบอร์ผู้ปกครอง', 
                'กิจกรรมที่ชอบ', 'ความสามารถพิเศษ', 'เกรดเฉลี่ย', `เกรด${teacherMajor || 'คอมพิวเตอร์'}`, 
                'วิชาที่ชอบ', 'วิชาที่ไม่ชอบ'
            ];
            
            const data = [headers];
            res.data.forEach(row => {
                data.push([
                    row.student_no, row.prefix, row.student_firstname, row.student_lastname,
                    row.student_level_room, row.student_phone, row.weight, row.height,
                    row.disease, row.parent_name, row.live_with, row.address, row.parent_phone,
                    row.favorite_activity, row.special_skill, row.gpa, row.last_com_grade,
                    row.like_subjects, row.dislike_subjects
                ]);
            });
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, 'ข้อมูลนักเรียน');
            
            // บันทึกไฟล์
            const subjectName = $('#allSubject option:selected').text().split('(')[0].trim();
            XLSX.writeFile(wb, `ข้อมูลนักเรียน_${subjectName}_${new Date().toLocaleDateString('th-TH')}.xlsx`);
        });
    }

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
