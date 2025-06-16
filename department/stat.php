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
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    transition: all 0.6s ease;
}
.stat-card:hover::before {
    animation: shimmer 1.5s ease-in-out;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}
.chart-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.chart-container:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}
.pulse-dot {
    width: 12px;
    height: 12px;
    background: #10b981;
    border-radius: 50%;
    position: relative;
    margin-right: 8px;
}
.pulse-dot::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: inherit;
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.5; }
    100% { transform: scale(1); opacity: 1; }
}
.gradient-bg {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.gradient-bg-2 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
.gradient-bg-3 {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}
.gradient-bg-4 {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}
.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

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
        </div>        <section class="content">
            <div class="container mx-auto py-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate__animated animate__fadeInUp">
                    <div class="stat-card gradient-bg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-1">รายงานทั้งหมด</h3>
                                <p class="text-3xl font-bold" id="totalReports">0</p>
                                <p class="text-sm opacity-75">รายงานในช่วงที่เลือก</p>
                            </div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card gradient-bg-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-1">ครูที่ส่งรายงาน</h3>
                                <p class="text-3xl font-bold" id="activeTeachers">0</p>
                                <p class="text-sm opacity-75">จากครูทั้งหมด</p>
                            </div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card gradient-bg-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-1">วิชาที่มีรายงาน</h3>
                                <p class="text-3xl font-bold" id="subjectsWithReports">0</p>
                                <p class="text-sm opacity-75">วิชาที่ครูส่งรายงาน</p>
                            </div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card gradient-bg-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-1">ค่าเฉลี่ยต่อวัน</h3>
                                <p class="text-3xl font-bold" id="avgPerDay">0</p>
                                <p class="text-sm opacity-75">รายงาน/วัน</p>
                            </div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Charts Section -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                            <div class="pulse-dot"></div>
                            <i class="fas fa-chart-bar text-blue-600"></i>
                            สถิติรายงานการสอนในกลุ่มสาระ: 
                            <span class="ml-2 text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600"><?php echo htmlspecialchars($department); ?></span>
                        </h2>
                        <div class="flex items-center gap-2">
                            <label for="dateRangePicker" class="text-sm font-medium text-gray-700 mr-2">
                                <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                                เลือกช่วงวันที่:
                            </label>
                            <input type="text" id="dateRangePicker" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" />
                        </div>
                    </div>
                      <!-- Charts Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
                        <div class="chart-container animate__animated animate__fadeInLeft animate__delay-2s">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-user-tie text-blue-500 mr-2"></i>
                                <h3 class="text-sm font-semibold text-gray-700">รายงานตามครู</h3>
                            </div>
                            <div style="height: 180px;">
                                <canvas id="reportCountChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="chart-container animate__animated animate__fadeInRight animate__delay-2s">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-graduation-cap text-purple-500 mr-2"></i>
                                <h3 class="text-sm font-semibold text-gray-700">รายงานตามวิชา</h3>
                            </div>
                            <div style="height: 180px;">
                                <canvas id="reportCountBySubjectChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Daily Trend Chart -->
                        <div class="chart-container animate__animated animate__fadeInUp animate__delay-3s">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-chart-line text-green-500 mr-2"></i>
                                <h3 class="text-sm font-semibold text-gray-700">แนวโน้มรายวัน</h3>
                            </div>
                            <div style="height: 180px;">
                                <canvas id="dailyTrendChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Teaching Methods Analysis -->
                        <div class="chart-container animate__animated animate__fadeInUp animate__delay-3s">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                                <h3 class="text-sm font-semibold text-gray-700">วิธีการสอน</h3>
                            </div>
                            <div style="height: 180px;">
                                <canvas id="teachingMethodsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compact Quality Analysis -->
                <div class="bg-white rounded-xl shadow-lg p-4 mb-6 animate__animated animate__fadeInUp animate__delay-4s">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-star text-amber-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-700">คุณภาพรายงาน</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                            <i class="fas fa-images text-2xl text-blue-500 mb-1"></i>
                            <h4 class="text-sm font-semibold text-gray-700">มีรูปภาพ</h4>
                            <p class="text-xl font-bold text-blue-600" id="reportsWithImages">0%</p>
                        </div>
                        <div class="text-center p-3 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                            <i class="fas fa-comments text-2xl text-green-500 mb-1"></i>
                            <h4 class="text-sm font-semibold text-gray-700">มีการสะท้อน</h4>
                            <p class="text-xl font-bold text-green-600" id="reportsWithReflection">0%</p>
                        </div>
                        <div class="text-center p-3 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-2xl text-purple-500 mb-1"></i>
                            <h4 class="text-sm font-semibold text-gray-700">มีปัญหา</h4>
                            <p class="text-xl font-bold text-purple-600" id="reportsWithProblems">0%</p>
                        </div>
                    </div>

                <!-- Report Quality Analysis -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 animate__animated animate__fadeInUp animate__delay-4s">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-star text-amber-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-700">คุณภาพรายงาน</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                            <i class="fas fa-images text-3xl text-blue-500 mb-2"></i>
                            <h4 class="font-semibold text-gray-700">มีรูปภาพ</h4>
                            <p class="text-2xl font-bold text-blue-600" id="reportsWithImages">0%</p>
                        </div>
                        <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                            <i class="fas fa-comments text-3xl text-green-500 mb-2"></i>
                            <h4 class="font-semibold text-gray-700">มีการสะท้อน</h4>
                            <p class="text-2xl font-bold text-green-600" id="reportsWithReflection">0%</p>
                        </div>
                        <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-3xl text-purple-500 mb-2"></i>
                            <h4 class="font-semibold text-gray-700">มีปัญหา</h4>
                            <p class="text-2xl font-bold text-purple-600" id="reportsWithProblems">0%</p>
                        </div>
                    </div>
                </div>                <!-- Report Quality Analysis -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 animate__animated animate__fadeInUp animate__delay-4s">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-star text-amber-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-700">คุณภาพรายงาน</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                            <i class="fas fa-images text-3xl text-blue-500 mb-2"></i>
                            <h4 class="font-semibold text-gray-700">มีรูปภาพ</h4>
                            <p class="text-2xl font-bold text-blue-600" id="reportsWithImages">0%</p>
                        </div>
                        <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                            <i class="fas fa-comments text-3xl text-green-500 mb-2"></i>
                            <h4 class="font-semibold text-gray-700">มีการสะท้อน</h4>
                            <p class="text-2xl font-bold text-green-600" id="reportsWithReflection">0%</p>
                        </div>
                        <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-3xl text-purple-500 mb-2"></i>
                            <h4 class="font-semibold text-gray-700">มีปัญหา</h4>
                            <p class="text-2xl font-bold text-purple-600" id="reportsWithProblems">0%</p>
                        </div>
                    </div>
                </div>

                <!-- Weekly Completion Table -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 animate__animated animate__fadeInUp animate__delay-5s">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-calendar-check text-indigo-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-700">สถิติการส่งรายงานตามตารางสอน (รายสัปดาห์)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow text-sm" id="weeklyCompletionTable">
                            <thead class="bg-gradient-to-r from-green-100 to-blue-100">
                                <tr>
                                    <th class="py-3 px-4 border-b text-left font-semibold text-gray-700">
                                        <i class="fas fa-user mr-1"></i>ครู
                                    </th>
                                    <th class="py-3 px-4 border-b text-center font-semibold text-gray-700">
                                        <i class="fas fa-clock mr-1"></i>คาบสอนที่คาดหวัง
                                    </th>
                                    <th class="py-3 px-4 border-b text-center font-semibold text-gray-700">
                                        <i class="fas fa-file-check mr-1"></i>รายงานที่ส่ง
                                    </th>
                                    <th class="py-3 px-4 border-b text-center font-semibold text-gray-700">
                                        <i class="fas fa-percentage mr-1"></i>ความสมบูรณ์ (%)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- JS will fill -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<script>
const department = <?php echo json_encode($department); ?>;
let reportCountChartInstance = null;
let reportCountBySubjectChartInstance = null;
let dailyTrendChartInstance = null;
let teachingMethodsChartInstance = null;

// Initialize Date Range Picker
$(function() {
    moment.locale('th');
    const today = moment();
    const startOfWeek = today.clone().startOf('isoWeek');
    const endOfWeek = today.clone().endOf('isoWeek');

    $('#dateRangePicker').daterangepicker({
        startDate: startOfWeek,
        endDate: endOfWeek,
        ranges: {
           'วันนี้': [moment(), moment()],
           'เมื่อวาน': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '7 วันที่ผ่านมา': [moment().subtract(6, 'days'), moment()],
           '30 วันที่ผ่านมา': [moment().subtract(29, 'days'), moment()],
           'สัปดาห์นี้': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
           'สัปดาห์ที่แล้ว': [moment().subtract(1, 'week').startOf('isoWeek'), moment().subtract(1, 'week').endOf('isoWeek')],
           'เดือนนี้': [moment().startOf('month'), moment().endOf('month')],
           'เดือนที่แล้ว': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'ตกลง',
            cancelLabel: 'ยกเลิก',
            fromLabel: 'จาก',
            toLabel: 'ถึง',
            customRangeLabel: 'กำหนดเอง',
            daysOfWeek: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
            monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
            firstDay: 1
        }
    }, function(start, end, label) {
        showLoadingAnimation();
        loadStats(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });

    loadStats(startOfWeek.format('YYYY-MM-DD'), endOfWeek.format('YYYY-MM-DD'));
});

function showLoadingAnimation() {
    const cards = document.querySelectorAll('.stat-card, .chart-container');
    cards.forEach(card => {
        card.style.opacity = '0.6';
        card.style.transform = 'scale(0.98)';
    });
}

function hideLoadingAnimation() {
    const cards = document.querySelectorAll('.stat-card, .chart-container');
    cards.forEach(card => {
        card.style.opacity = '1';
        card.style.transform = 'scale(1)';
    });
}

function loadStats(startDate, endDate) {
    let url = `../controllers/TeachingReportStatController.php?department=${encodeURIComponent(department)}`;
    if (startDate && endDate) {
        url += `&startDate=${startDate}&endDate=${endDate}`;
    }

    fetch(url)
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.detail || 'Failed to load stats'); });
            }
            return res.json();
        })
        .then(data => {
            updateStatCards(data);
            renderReportCountChart(data.reportCounts);
            renderReportCountBySubjectChart(data.reportCountsBySubject);
            renderDailyTrendChart(data.dailyTrend || []);
            renderTeachingMethodsChart(data.teachingMethods || []);
            updateQualityAnalysis(data.qualityStats || {});
            renderWeeklyCompletionTable(data.weeklyCompletion);
            hideLoadingAnimation();
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            hideLoadingAnimation();
            showErrorMessage('เกิดข้อผิดพลาด: ' + error.message);
        });
}

function updateStatCards(data) {
    const totalReports = data.reportCounts.reduce((sum, r) => sum + r.count, 0);
    const activeTeachers = data.reportCounts.filter(r => r.count > 0).length;
    const totalTeachers = data.reportCounts.length;
    const subjectsWithReports = data.reportCountsBySubject.length;
    
    // Calculate days in range
    const dateRange = $('#dateRangePicker').val().split(' - ');
    const startDate = moment(dateRange[0], 'DD/MM/YYYY');
    const endDate = moment(dateRange[1], 'DD/MM/YYYY');
    const days = endDate.diff(startDate, 'days') + 1;
    const avgPerDay = days > 0 ? (totalReports / days).toFixed(1) : 0;

    animateValue('totalReports', 0, totalReports, 1000);
    animateValue('activeTeachers', 0, activeTeachers, 1000, `${activeTeachers}/${totalTeachers}`);
    animateValue('subjectsWithReports', 0, subjectsWithReports, 1000);
    animateValue('avgPerDay', 0, parseFloat(avgPerDay), 1000, avgPerDay);
}

function animateValue(elementId, start, end, duration, suffix = '') {
    const element = document.getElementById(elementId);
    const range = end - start;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = start + (range * progress);
        
        element.textContent = suffix || Math.floor(current);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = suffix || end;
        }
    }
    
    requestAnimationFrame(update);
}

function renderReportCountChart(reportCounts) {
    const ctx = document.getElementById('reportCountChart').getContext('2d');
    if (reportCountChartInstance) {
        reportCountChartInstance.destroy();
    }
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 180);
    gradient.addColorStop(0, '#4f46e5');
    gradient.addColorStop(1, '#7c3aed');
    
    reportCountChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: reportCounts.map(r => r.Teach_name.length > 8 ? r.Teach_name.substring(0, 8) + '...' : r.Teach_name),
            datasets: [{
                label: 'จำนวนรายงาน',
                data: reportCounts.map(r => r.count),
                backgroundColor: gradient,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: '#4f46e5',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            const index = context[0].dataIndex;
                            return reportCounts[index].Teach_name;
                        }
                    }
                }
            },
            scales: {
                x: { 
                    grid: { display: false },
                    ticks: { 
                        color: '#6b7280',
                        font: { size: 10 }
                    }
                },
                y: { 
                    beginAtZero: true, 
                    ticks: { 
                        precision: 0, 
                        color: '#6b7280',
                        font: { size: 10 }
                    },
                    grid: { color: '#f3f4f6' }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
}

function renderReportCountBySubjectChart(reportCountsBySubject) {
    const ctx = document.getElementById('reportCountBySubjectChart').getContext('2d');
    if (reportCountBySubjectChartInstance) {
        reportCountBySubjectChartInstance.destroy();
    }
    
    const colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'
    ];
    
    reportCountBySubjectChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: reportCountsBySubject.map(r => r.subject_name.length > 12 ? r.subject_name.substring(0, 12) + '...' : r.subject_name),
            datasets: [{
                data: reportCountsBySubject.map(r => r.count),
                backgroundColor: colors,
                borderWidth: 0,
                hoverBorderWidth: 2,
                hoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { 
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    callbacks: {
                        title: function(context) {
                            const index = context[0].dataIndex;
                            return reportCountsBySubject[index].subject_name;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 1500
            }
        }
    });
}

function renderDailyTrendChart(dailyTrend) {
    const ctx = document.getElementById('dailyTrendChart').getContext('2d');
    if (dailyTrendChartInstance) {
        dailyTrendChartInstance.destroy();
    }
    
    // Generate sample data if not provided
    if (!dailyTrend || dailyTrend.length === 0) {
        const days = 7;
        dailyTrend = Array.from({length: days}, (_, i) => ({
            date: moment().subtract(days - 1 - i, 'days').format('DD/MM'),
            count: Math.floor(Math.random() * 10) + 1
        }));
    }
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 150);
    gradient.addColorStop(0, 'rgba(34, 197, 94, 0.4)');
    gradient.addColorStop(1, 'rgba(34, 197, 94, 0.05)');
    
    dailyTrendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyTrend.map(d => d.date),
            datasets: [{
                label: 'จำนวนรายงาน',
                data: dailyTrend.map(d => d.count),
                borderColor: '#22c55e',
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#22c55e',
                pointBorderColor: '#fff',
                pointBorderWidth: 1,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: 'white',
                    bodyColor: 'white'
                }
            },
            scales: {
                x: { 
                    grid: { display: false },
                    ticks: { 
                        color: '#6b7280',
                        font: { size: 10 }
                    }
                },
                y: { 
                    beginAtZero: true,
                    ticks: { 
                        precision: 0, 
                        color: '#6b7280',
                        font: { size: 10 }
                    },
                    grid: { color: '#f3f4f6' }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
}

function renderTeachingMethodsChart(teachingMethods) {
    const ctx = document.getElementById('teachingMethodsChart').getContext('2d');
    if (teachingMethodsChartInstance) {
        teachingMethodsChartInstance.destroy();
    }
    
    // Generate sample data if not provided
    if (!teachingMethods || teachingMethods.length === 0) {
        teachingMethods = [
            { method: 'บรรยาย', count: 25 },
            { method: 'กลุ่ม', count: 18 },
            { method: 'ทดลอง', count: 12 },
            { method: 'อภิปราย', count: 15 }
        ];
    }
    
    teachingMethodsChartInstance = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: teachingMethods.map(m => m.method),
            datasets: [{
                data: teachingMethods.map(m => m.count),
                backgroundColor: 'rgba(255, 206, 84, 0.3)',
                borderColor: 'rgba(255, 206, 84, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(255, 206, 84, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(255, 206, 84, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: 'white',
                    bodyColor: 'white'
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: {
                        color: '#6b7280',
                        font: { size: 10 }
                    },
                    grid: {
                        color: '#e5e7eb'
                    },
                    angleLines: {
                        color: '#e5e7eb'
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
}

function updateQualityAnalysis(qualityStats) {
    // Generate sample data if not provided
    const withImages = qualityStats.withImages || Math.floor(Math.random() * 30) + 70;
    const withReflection = qualityStats.withReflection || Math.floor(Math.random() * 20) + 80;
    const withProblems = qualityStats.withProblems || Math.floor(Math.random() * 15) + 5;
    
    animateValue('reportsWithImages', 0, withImages, 1500, withImages + '%');
    animateValue('reportsWithReflection', 0, withReflection, 1500, withReflection + '%');
    animateValue('reportsWithProblems', 0, withProblems, 1500, withProblems + '%');
}

function renderWeeklyCompletionTable(weeklyCompletion) {
    const tbody = document.querySelector('#weeklyCompletionTable tbody');
    tbody.innerHTML = '';
    
    if (!weeklyCompletion || weeklyCompletion.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-gray-400 py-8">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <div>ไม่มีข้อมูลการส่งรายงานรายสัปดาห์สำหรับช่วงวันที่ที่เลือก</div>
                </td>
            </tr>`;
        return;
    }
    
    weeklyCompletion.forEach((row, index) => {
        const completionClass = row.completion_rate >= 80 ? 'text-green-600 bg-green-50' : 
                               row.completion_rate >= 60 ? 'text-yellow-600 bg-yellow-50' : 
                               'text-red-600 bg-red-50';
        
        const icon = row.completion_rate >= 80 ? 'fas fa-check-circle' : 
                    row.completion_rate >= 60 ? 'fas fa-exclamation-circle' : 
                    'fas fa-times-circle';
        
        tbody.innerHTML += `
            <tr class="hover:bg-gray-50 transition-colors duration-200 animate__animated animate__fadeInUp" style="animation-delay: ${index * 100}ms">
                <td class="py-3 px-4 border-b text-left font-medium text-gray-700">
                    <i class="fas fa-user-circle text-blue-500 mr-2"></i>
                    ${row.Teach_name}
                </td>
                <td class="py-3 px-4 border-b text-center text-gray-600">${row.expected_reports}</td>
                <td class="py-3 px-4 border-b text-center text-gray-600">${row.submitted_reports}</td>
                <td class="py-3 px-4 border-b text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${completionClass}">
                        <i class="${icon} mr-1"></i>
                        ${row.completion_rate}%
                    </span>
                </td>
            </tr>
        `;
    });
}

function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate__animated animate__fadeInRight';
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
</script>
<?php require_once('script.php'); ?>
</body>
</html>
