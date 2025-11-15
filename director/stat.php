<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ผู้บริหาร') {
    header('Location: ../login.php');
    exit;
}
// โหลด config
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
require_once('header.php');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.min.css">
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">
    <?php require_once('wrapper.php'); ?>
    <div class="content-wrapper bg-gray-50">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center gap-2">
                            📊 สถิติและวิเคราะห์ข้อมูลการสอน
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container mx-auto py-6">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-lg font-bold text-blue-700 mb-4 flex items-center gap-2">สถิติภาพรวมการสอน</h2>
                    <div id="statSummary" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <!-- JS จะเติมข้อมูล -->
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <canvas id="reportCountChart" height="220"></canvas>
                        </div>
                        <div>
                            <canvas id="subjectPieChart" height="220"></canvas>
                        </div>
                    </div>
                    <div class="mt-8 text-gray-500 text-sm">
                        * หมายเหตุ: ข้อมูลนี้เป็นข้อมูลจริงจากระบบ
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Promise.all([
        fetch('../controllers/StatController.php?action=reportByMonth').then(res => res.json()),
        fetch('../controllers/StatController.php?action=reportByDepartment').then(res => res.json()),
        fetch('../controllers/StatController.php?action=reportCount').then(res => res.json()),
        fetch('../controllers/StatController.php?action=teacherCount').then(res => res.json()),
        fetch('../controllers/StatController.php?action=reportByTeacher').then(res => res.json())
    ]).then(([reportByMonth, reportByDepartment, reportCount, teacherCount, reportByTeacher]) => {
        // เตรียมข้อมูลสำหรับกราฟรายงานต่อเดือน
        const monthNames = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const monthData = Array(12).fill(0);
        reportByMonth.forEach(item => {
            const idx = parseInt(item.month, 10) - 1;
            if (idx >= 0 && idx < 12) monthData[idx] = parseInt(item.count, 10);
        });

        new Chart(document.getElementById('reportCountChart'), {
            type: 'bar',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'จำนวนรายงานการสอน',
                    data: monthData,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // เตรียมข้อมูลสำหรับกราฟกลุ่มสาระ
        const depLabels = reportByDepartment.map(d => d.department || '-');
        const depData = reportByDepartment.map(d => parseInt(d.count, 10));
        new Chart(document.getElementById('subjectPieChart'), {
            type: 'pie',
            data: {
                labels: depLabels,
                datasets: [{
                    data: depData,
                    backgroundColor: [
                        '#3b82f6', '#6366f1', '#f59e42', '#10b981', '#ef4444', '#fbbf24', '#a3e635', '#f472b6', '#f87171', '#facc15'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // สรุปสถิติ
        const avgReport = teacherCount.count > 0 ? (reportCount.count / teacherCount.count).toFixed(2) : 0;
        const topTeachers = reportByTeacher.slice(0, 5).map(t => `<li>${t.teacher} <span class="text-blue-600 font-bold">(${t.count})</span></li>`).join('');
        document.getElementById('statSummary').innerHTML = `
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-700">${reportCount.count}</div>
                <div class="text-gray-600">จำนวนรายงานทั้งหมด</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-700">${teacherCount.count}</div>
                <div class="text-gray-600">จำนวนครูที่มีรายงาน</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-700">${avgReport}</div>
                <div class="text-gray-600">รายงานเฉลี่ยต่อครู</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-base font-semibold text-purple-700 mb-1">Top 5 ครูที่รายงานมากที่สุด</div>
                <ul class="text-sm text-purple-700">${topTeachers}</ul>
            </div>
        `;
    });
});
</script>
<?php require_once('script.php'); ?>
</body>
</html>
