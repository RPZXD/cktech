<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'หัวหน้ากลุ่มสาระ') {
    header('Location: ../login.php');
    exit;
}
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
$department = $_SESSION['user']['Teach_major'];
require_once('header.php');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">
    <?php require_once('wrapper.php'); ?>
    <div class="content-wrapper bg-gray-50">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center gap-2">
                            📊 สถิติและวิเคราะห์ข้อมูลรายงานการสอน
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container mx-auto py-6">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-lg font-bold text-blue-700 mb-4 flex items-center gap-2">👩‍🏫 สถิติรายงานการสอนในกลุ่มสาระ: <span class="ml-2 text-indigo-600"><?php echo htmlspecialchars($department); ?></span></h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <canvas id="reportCountChart" height="220"></canvas>
                        </div>
                        <div>
                            <canvas id="absentStatChart" height="220"></canvas>
                        </div>
                    </div>
                    <div class="mt-8">
                        <h3 class="text-md font-bold text-blue-700 mb-2 flex items-center gap-2">📅 สรุปรายงานการสอนรายเดือน</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow text-sm" id="statTable">
                                <thead class="bg-blue-100">
                                    <tr>
                                        <th class="py-2 px-3 border-b text-center">เดือน</th>
                                        <th class="py-2 px-3 border-b text-center">จำนวนรายงาน</th>
                                        <th class="py-2 px-3 border-b text-center">ขาดเรียน</th>
                                        <th class="py-2 px-3 border-b text-center">ลาป่วย</th>
                                        <th class="py-2 px-3 border-b text-center">ลากิจ</th>
                                        <th class="py-2 px-3 border-b text-center">กิจกรรม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- JS will fill -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<script>
const department = <?php echo json_encode($department); ?>;

// ดึงข้อมูลสถิติจาก backend
function loadStats() {
    fetch('../controllers/TeachingReportStatController.php?department=' + encodeURIComponent(department))
        .then(res => res.json())
        .then(data => {
            renderReportCountChart(data.reportCounts);
            renderAbsentStatChart(data.absentStats);
            renderStatTable(data.monthlyStats);
        });
}

// Chart: จำนวนรายงานการสอนแยกตามครู
function renderReportCountChart(reportCounts) {
    const ctx = document.getElementById('reportCountChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: reportCounts.map(r => r.Teach_name),
            datasets: [{
                label: 'จำนวนรายงาน',
                data: reportCounts.map(r => r.count),
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { title: { display: true, text: 'ครู' } },
                y: { title: { display: true, text: 'จำนวนรายงาน' }, beginAtZero: true }
            }
        }
    });
}

// Chart: สถิติขาดเรียน/ป่วย/ลากิจ/กิจกรรม
function renderAbsentStatChart(absentStats) {
    const ctx = document.getElementById('absentStatChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['ขาดเรียน', 'ลาป่วย', 'ลากิจ', 'กิจกรรม'],
            datasets: [{
                label: 'จำนวนครั้ง',
                data: [
                    absentStats.absent || 0,
                    absentStats.sick || 0,
                    absentStats.personal || 0,
                    absentStats.activity || 0
                ],
                backgroundColor: ['#ef4444', '#60a5fa', '#a78bfa', '#f59e42']
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { title: { display: false } },
                y: { title: { display: true, text: 'จำนวนครั้ง' }, beginAtZero: true }
            }
        }
    });
}

// ตารางสรุปรายเดือน
function renderStatTable(monthlyStats) {
    const tbody = document.querySelector('#statTable tbody');
    tbody.innerHTML = '';
    if (!monthlyStats.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-gray-400 py-6">ไม่มีข้อมูล</td></tr>`;
        return;
    }
    const thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    monthlyStats.forEach(row => {
        tbody.innerHTML += `
            <tr>
                <td class="py-2 px-3 border-b text-center">${thMonths[parseInt(row.month,10)]} ${parseInt(row.year,10)+543}</td>
                <td class="py-2 px-3 border-b text-center">${row.count}</td>
                <td class="py-2 px-3 border-b text-center">${row.absent}</td>
                <td class="py-2 px-3 border-b text-center">${row.sick}</td>
                <td class="py-2 px-3 border-b text-center">${row.personal}</td>
                <td class="py-2 px-3 border-b text-center">${row.activity}</td>
            </tr>
        `;
    });
}

document.addEventListener('DOMContentLoaded', loadStats);
</script>
<?php require_once('script.php'); ?>
</body>
</html>
