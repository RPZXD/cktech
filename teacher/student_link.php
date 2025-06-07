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
    const teacherName = <?= json_encode($_SESSION['user']['Teach_name'] ?? '') ?>;
    
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
            
            // เพิ่มตัวแปรสำหรับจัดกลุ่มเกรด
            let gpaExcellent = 0, gpaAverage = 0, gpaWeak = 0;
            let comExcellent = 0, comAverage = 0, comWeak = 0;
            
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
                
                // เกรดเฉลี่ย และจัดกลุ่ม
                if (!isNaN(parseFloat(row.gpa))) {
                    const gpa = parseFloat(row.gpa);
                    gpaArr.push(gpa);
                    if (gpa >= 3.0) gpaExcellent++;
                    else if (gpa >= 2.0) gpaAverage++;
                    else gpaWeak++;
                }
                
                // เกรดคอม และจัดกลุ่ม
                if (!isNaN(parseFloat(row.last_com_grade))) {
                    const com = parseFloat(row.last_com_grade);
                    comArr.push(com);
                    if (com >= 3.0) comExcellent++;
                    else if (com >= 2.0) comAverage++;
                    else comWeak++;
                }
                
                // ห้องเรียน
                if (row.student_level_room) roomSet[row.student_level_room] = true;
            });
            
            // กราฟเพศ
            let genderCanvas = '<canvas id="genderChart" height="60"></canvas>';
            // กราฟวิชาที่ชอบ
            let likeSubjectsCanvas = '<canvas id="likeSubjectsChart" height="120"></canvas>';
            // กราฟ GPA
            let gpaCanvas = '<canvas id="gpaChart" height="120"></canvas>';
            // กราฟเกรดคอม
            let comCanvas = '<canvas id="comChart" height="120"></canvas>';
            
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
                <div class="grid grid-cols-2 gap-8">
                    <!-- Chart 1: สัดส่วนเพศ -->
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">สัดส่วนเพศ</div>
                        <div class="flex-1 flex items-center justify-center h-[220px]">
                            ${genderCanvas}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            <div>ชาย: ${male} คน (${((male/totalStudents)*100).toFixed(1)}%)</div>
                            <div>หญิง: ${female} คน (${((female/totalStudents)*100).toFixed(1)}%)</div>
                            ${other > 0 ? `<div>อื่นๆ: ${other} คน (${((other/totalStudents)*100).toFixed(1)}%)</div>` : ''}
                        </div>
                    </div>
                    <!-- Chart 2: วิชาที่นักเรียนชอบ (Top 5) -->
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">วิชาที่นักเรียนชอบ (Top 5)</div>
                        <div class="flex-1 flex items-center justify-center h-[220px]">
                            ${likeSubjectsCanvas}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            ${likeLabels.map((label, i) => `<div>${i+1}. ${label}: ${likeData[i]} คน</div>`).join('')}
                        </div>
                    </div>
                </div>

                <!-- แถวใหม่สำหรับกราฟเกรด -->
                <div class="mt-8 grid grid-cols-2 gap-8">
                    <!-- Chart 3: การแจกแจงเกรดเฉลี่ย (GPA) -->
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">การแจกแจงเกรดเฉลี่ย (GPA)</div>
                        <div class="flex-1 flex items-center justify-center h-[220px]">
                            ${gpaCanvas}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            <div class="text-green-600">เก่ง (3.00+): ${gpaExcellent} คน (${gpaArr.length ? ((gpaExcellent/gpaArr.length)*100).toFixed(1) : 0}%)</div>
                            <div class="text-yellow-600">กลาง (2.00-2.99): ${gpaAverage} คน (${gpaArr.length ? ((gpaAverage/gpaArr.length)*100).toFixed(1) : 0}%)</div>
                            <div class="text-red-600">อ่อน (0.00-1.99): ${gpaWeak} คน (${gpaArr.length ? ((gpaWeak/gpaArr.length)*100).toFixed(1) : 0}%)</div>
                        </div>
                    </div>
                    <!-- Chart 4: การแจกแจงเกรดวิชา${teacherMajor || ''} -->
                    <div class="flex flex-col h-full">
                        <div class="font-bold mb-2">การแจกแจงเกรดวิชา${teacherMajor || ''}</div>
                        <div class="flex-1 flex items-center justify-center h-[220px]">
                            ${comCanvas}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            <div class="text-green-600">เก่ง (3.00+): ${comExcellent} คน (${comArr.length ? ((comExcellent/comArr.length)*100).toFixed(1) : 0}%)</div>
                            <div class="text-yellow-600">กลาง (2.00-2.99): ${comAverage} คน (${comArr.length ? ((comAverage/comArr.length)*100).toFixed(1) : 0}%)</div>
                            <div class="text-red-600">อ่อน (0.00-1.99): ${comWeak} คน (${comArr.length ? ((comWeak/comArr.length)*100).toFixed(1) : 0}%)</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded p-4 text-center">
                        <div class="font-bold text-blue-700">เกรดเฉลี่ย (GPA)</div>
                        <div class="text-3xl font-extrabold text-blue-600">${gpaAvg}</div>
                    </div>
                    <div class="bg-green-50 rounded p-4 text-center">
                        <div class="font-bold text-green-700">เกรดเฉลี่ย${teacherMajor || ''}</div>
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
                
                <div class="mt-8 grid grid-cols-2 gap-8">
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

                <div class="mt-8 grid grid-cols-3 gap-6">
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
            
            // Chart GPA
            new Chart(document.getElementById('gpaChart'), {
                type: 'bar',
                data: {
                    labels: ['เก่ง (3.00+)', 'กลาง (2.00-2.99)', 'อ่อน (0.00-1.99)'],
                    datasets: [{
                        label: 'จำนวนนักเรียน',
                        data: [gpaExcellent, gpaAverage, gpaWeak],
                        backgroundColor: ['#22c55e', '#eab308', '#ef4444']
                    }]
                },
                options: {
                    responsive: true, 
                    plugins: {legend: {display: false}},
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            
            // Chart เกรดคอม
            new Chart(document.getElementById('comChart'), {
                type: 'bar',
                data: {
                    labels: ['เก่ง (3.00+)', 'กลาง (2.00-2.99)', 'อ่อน (0.00-1.99)'],
                    datasets: [{
                        label: 'จำนวนนักเรียน',
                        data: [comExcellent, comAverage, comWeak],
                        backgroundColor: ['#22c55e', '#eab308', '#ef4444']
                    }]
                },
                options: {
                    responsive: true, 
                    plugins: {legend: {display: false}},
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
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
        const subjectId = $('#reportSubject').val();
        if (!subjectId) {
            alert('กรุณาเลือกวิชาก่อน');
            return;
        }
        const subjectOption = $('#reportSubject option:selected');
        const subjectText = subjectOption.text();
        const subjectName = subjectText.split('(')[0].trim();
        const subjectCode = subjectText.match(/\(([^)]+)\)/)?.[1] || '';
        
        // หาระดับชั้นจากรายวิชา
        const subjects = <?= json_encode($subjects) ?>;
        const selectedSubject = subjects.find(s => s.id == subjectId);
        const level = selectedSubject ? selectedSubject.level : '';
        
        const tab = document.getElementById('reportContent');
        printReportSection(tab, subjectName, subjectCode, level, 'รายงานสถิติ');
    });
    
    $('#printAllBtn').on('click', function() {
        const subjectId = $('#allSubject').val();
        if (!subjectId) {
            alert('กรุณาเลือกวิชาก่อน');
            return;
        }
        const subjectOption = $('#allSubject option:selected');
        const subjectText = subjectOption.text();
        const subjectName = subjectText.split('(')[0].trim();
        const subjectCode = subjectText.match(/\(([^)]+)\)/)?.[1] || '';
        
        // หาระดับชั้นจากรายวิชา
        const subjects = <?= json_encode($subjects) ?>;
        const selectedSubject = subjects.find(s => s.id == subjectId);
        const level = selectedSubject ? selectedSubject.level : '';
        
        const tab = document.getElementById('allContent');
        printReportSection(tab, subjectName, subjectCode, level, 'ข้อมูลนักเรียนทั้งหมด');
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

    function printReportSection(section, subjectName, subjectCode, level, reportType) {
        // แปลง charts เป็นรูปภาพก่อนพิมพ์
        const charts = section.querySelectorAll('canvas');
        const chartImages = [];
        
        // แปลง canvas เป็น image
        charts.forEach((canvas, index) => {
            if (canvas && canvas.getContext) {
                const dataURL = canvas.toDataURL('image/png');
                chartImages.push({
                    id: canvas.id,
                    dataURL: dataURL,
                    width: canvas.offsetWidth,
                    height: canvas.offsetHeight
                });
            }
        });
        
        // Clone section และแทนที่ canvas ด้วย img
        const clonedSection = section.cloneNode(true);
        const clonedCharts = clonedSection.querySelectorAll('canvas');
        
        clonedCharts.forEach((canvas, index) => {
            if (chartImages[index]) {
                const img = document.createElement('img');
                img.src = chartImages[index].dataURL;
                img.style.width = '100%';
                img.style.height = 'auto';
                img.style.maxWidth = '300px';
                img.style.maxHeight = '200px';
                canvas.parentNode.replaceChild(img, canvas);
            }
        });
        
        const printWindow = window.open('', '', 'width=900,height=700');
        const headerHTML = `
            <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px;">
                    <img src="../assets/images/logo.png" alt="โลโก้โรงเรียน" style="width: 60px; height: 60px;" onerror="this.style.display='none'">
                    <div>
                        <h1 style="font-size: 20px; font-weight: bold; margin: 0; color: #1e40af;">โรงเรียนพิชัย</h1>
                        <p style="font-size: 14px; margin: 3px 0; color: #374151;">แบบวิเคราะห์ผู้เรียนรายบุคคล</p>
                    </div>
                </div>
                <div style="text-align: left; max-width: 500px; margin: 0 auto;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                        <div><strong>วิชา:</strong> ${subjectName}</div>
                        <div><strong>รหัสวิชา:</strong> ${subjectCode}</div>
                        <div><strong>ระดับชั้น:</strong> มัธยมศึกษาปีที่ ${level}</div>
                        <div><strong>ครูผู้สอน:</strong> ${teacherName}</div>
                    </div>
                    <div style="margin-top: 8px; font-size: 12px;">
                        <strong>กลุ่มสาระการเรียนรู้:</strong> ${teacherMajor || 'ไม่ระบุ'}
                    </div>
                    <div style="margin-top: 8px; text-align: center;">
                        <strong style="font-size: 14px; color: #1e40af;">${reportType}</strong>
                    </div>
                </div>
            </div>
        `;
        
        printWindow.document.write(`
            <html>
            <head>
                <title>พิมพ์รายงาน - ${reportType}</title>
                <style>
                    body { 
                        font-family: 'Sarabun', sans-serif; 
                        margin: 15px;
                        line-height: 1.3;
                        font-size: 12px;
                    }
                    @page { 
                        margin: 1cm; 
                        size: A4;
                    }
                    @media print {
                        .no-print, button { 
                            display: none !important; 
                        }
                        .page-break {
                            page-break-before: always;
                        }
                        .avoid-break {
                            page-break-inside: avoid;
                        }
                    }
                    table { 
                        border-collapse: collapse; 
                        width: 100%; 
                        font-size: 10px;
                    }
                    th, td { 
                        border: 1px solid #333; 
                        padding: 3px 4px; 
                        text-align: left;
                    }
                    th { 
                        background-color: #f3f4f6; 
                        font-weight: bold;
                        text-align: center;
                    }
                    .text-center { 
                        text-align: center; 
                    }
                    img {
                        max-width: 100%;
                        height: auto;
                        display: block;
                        margin: 0 auto;
                    }
                    
                    /* Grid Layout for Charts */
                    .grid {
                        display: grid;
                    }
                    .grid-cols-1 {
                        grid-template-columns: 1fr;
                    }
                    .grid-cols-2 {
                        grid-template-columns: 1fr 1fr;
                    }
                    .grid-cols-3 {
                        grid-template-columns: 1fr 1fr 1fr;
                    }
                    .grid-cols-4 {
                        grid-template-columns: 1fr 1fr 1fr 1fr;
                    }
                    .gap-4 {
                        gap: 10px;
                    }
                    .gap-6 {
                        gap: 15px;
                    }
                    .gap-8 {
                        gap: 20px;
                    }
                    
                    /* Chart specific styles */
                    .chart-container {
                        page-break-inside: avoid;
                        margin-bottom: 15px;
                        text-align: center;
                    }
                    .chart-row {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 15px;
                        page-break-inside: avoid;
                    }
                    .chart-item {
                        text-align: center;
                    }
                    .chart-item h3 {
                        font-size: 14px;
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                    .chart-item img {
                        max-width: 100%;
                        max-height: 180px;
                        width: auto;
                        height: auto;
                    }
                    .chart-details {
                        font-size: 11px;
                        margin-top: 8px;
                        text-align: left;
                    }
                    
                    /* Stats Grid */
                    .stats-grid {
                        display: grid;
                        grid-template-columns: repeat(4, 1fr);
                        gap: 10px;
                        margin: 15px 0;
                    }
                    .stats-card {
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        padding: 10px;
                        text-align: center;
                    }
                    .stats-title {
                        font-size: 11px;
                        font-weight: bold;
                        margin-bottom: 5px;
                    }
                    .stats-value {
                        font-size: 18px;
                        font-weight: bold;
                    }
                    
                    /* Other sections */
                    .info-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 15px;
                        margin: 15px 0;
                    }
                    .info-card {
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        padding: 10px;
                    }
                    .info-title {
                        font-size: 12px;
                        font-weight: bold;
                        margin-bottom: 8px;
                    }
                    .info-content {
                        font-size: 11px;
                    }
                    
                    .bottom-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr 1fr;
                        gap: 10px;
                        margin: 15px 0;
                    }
                    
                    /* Responsive adjustments */
                    .rounded { border-radius: 4px; }
                    .p-4 { padding: 10px; }
                    .mb-2 { margin-bottom: 8px; }
                    .mt-8 { margin-top: 15px; }
                    .text-sm { font-size: 11px; }
                    .font-bold { font-weight: 700; }
                    .font-semibold { font-weight: 600; }
                    
                    /* Background colors */
                    .bg-blue-50 { background-color: #eff6ff; }
                    .bg-green-50 { background-color: #f0fdf4; }
                    .bg-yellow-50 { background-color: #fefce8; }
                    .bg-purple-50 { background-color: #faf5ff; }
                    .bg-orange-50 { background-color: #fff7ed; }
                    .bg-pink-50 { background-color: #fdf2f8; }
                    .bg-gray-50 { background-color: #f9fafb; }
                    .bg-cyan-50 { background-color: #ecfeff; }
                    .bg-indigo-50 { background-color: #eef2ff; }
                    
                    /* Text colors */
                    .text-blue-700 { color: #1d4ed8; }
                    .text-green-700 { color: #15803d; }
                    .text-yellow-700 { color: #a16207; }
                    .text-purple-700 { color: #7c3aed; }
                    .text-orange-700 { color: #c2410c; }
                    .text-pink-700 { color: #be185d; }
                    .text-gray-700 { color: #374151; }
                    .text-cyan-700 { color: #0e7490; }
                    .text-indigo-700 { color: #4338ca; }
                    .text-blue-600 { color: #2563eb; }
                    .text-green-600 { color: #16a34a; }
                    .text-yellow-600 { color: #ca8a04; }
                    .text-purple-600 { color: #9333ea; }
                    
                    .text-3xl { font-size: 18px; line-height: 1.2; }
                    .font-extrabold { font-weight: 800; }
                </style>
            </head>
            <body>
                ${headerHTML}
                <div style="margin-top: 15px;">
                    ${clonedSection.innerHTML}
                </div>
                <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 8px;">
                    <p>รายงานนี้สร้างโดยระบบรายงานการสอน โรงเรียนพิชัย</p>
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        
        // รอให้โหลดเสร็จแล้วพิมพ์
        setTimeout(() => {
            printWindow.print();
        }, 1000);
    }

    function printSection(section) {
        // ใช้ฟังก์ชันเดิมสำหรับกรณีที่ไม่ได้กำหนดข้อมูลวิชา
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

    // ...existing code...
});
</script>
<?php require_once('script.php'); ?>
</body>
</html>
