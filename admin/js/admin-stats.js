/**
 * Admin Statistics JavaScript
 * MVC Pattern - JavaScript for admin statistics and analytics
 * Uses real data from StatController API
 */

// Chart instances
let chartDepartment = null;
let chartTimeline = null;
let chartAttendance = null;

// Colors
const chartColors = {
    blue: 'rgba(59, 130, 246, 0.8)',
    emerald: 'rgba(16, 185, 129, 0.8)',
    purple: 'rgba(139, 92, 246, 0.8)',
    orange: 'rgba(249, 115, 22, 0.8)',
    pink: 'rgba(236, 72, 153, 0.8)',
    cyan: 'rgba(6, 182, 212, 0.8)',
    amber: 'rgba(245, 158, 11, 0.8)',
    red: 'rgba(239, 68, 68, 0.8)',
    indigo: 'rgba(99, 102, 241, 0.8)',
    teal: 'rgba(20, 184, 166, 0.8)'
};

$(document).ready(function () {
    loadStats();
    loadCharts();
    loadTopTeachers();
    loadHeatmap();

    // Refresh button
    $('#btnRefresh').on('click', function () {
        location.reload();
    });

    // Period filter change
    $('#filterPeriod').on('change', function () {
        loadStats();
        loadCharts();
        loadTopTeachers();
        loadHeatmap();
    });
});

function loadStats() {
    // Load summary stats from API
    $.getJSON('../controllers/StatController.php?action=summary', function (data) {
        if (data) {
            $('#statTotalReports').text(numberFormat(data.totalReports || 0));
            $('#statActiveTeachers').text(numberFormat(data.activeTeachers || 0));
            $('#statTotalTeachers').text(numberFormat(data.totalTeachers || 0));
            $('#statAttendanceRate').text((data.attendanceRate || 0) + '%');
            $('#statTodayReports').text(numberFormat(data.todayReports || 0));
            $('#statTodayTarget').text(numberFormat(data.todayTarget || 0));

            // Handle growth indicator
            const growth = parseFloat(data.reportsGrowth) || 0;
            const growthEl = $('#statReportsGrowth');
            const growthParent = growthEl.parent();

            if (growth >= 0) {
                growthParent.removeClass('text-red-500').addClass('text-emerald-500');
                growthParent.find('i').removeClass('fa-arrow-down').addClass('fa-arrow-up');
            } else {
                growthParent.removeClass('text-emerald-500').addClass('text-red-500');
                growthParent.find('i').removeClass('fa-arrow-up').addClass('fa-arrow-down');
            }
            growthEl.text(Math.abs(growth) + '%');
        }
    }).fail(function () {
        console.error('Failed to load summary stats');
    });
}

function loadTopTeachers() {
    // Load top teachers from API
    $.getJSON('../controllers/StatController.php?action=topTeachers', function (data) {
        renderTopTeachers(data);
    }).fail(function () {
        $('#topTeachersList').html('<p class="text-center text-gray-500 py-4">ไม่สามารถโหลดข้อมูลได้</p>');
    });
}

function renderTopTeachers(teachers) {
    if (!Array.isArray(teachers) || teachers.length === 0) {
        $('#topTeachersList').html('<p class="text-center text-gray-500 py-4">ไม่มีข้อมูล</p>');
        return;
    }

    let html = '';
    teachers.slice(0, 10).forEach((teacher, index) => {
        const colors = ['from-yellow-400 to-amber-500', 'from-gray-300 to-gray-400', 'from-orange-400 to-orange-500'];
        const badgeColor = index < 3 ? colors[index] : 'from-gray-200 to-gray-300';
        const textColor = index < 3 ? 'text-white' : 'text-gray-600';

        html += `
            <div class="flex items-center gap-2 md:gap-3 p-2.5 md:p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="w-7 h-7 md:w-8 md:h-8 flex-shrink-0 flex items-center justify-center bg-gradient-to-br ${badgeColor} rounded-full ${textColor} font-bold text-xs md:text-sm">
                    ${index + 1}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-800 dark:text-white truncate text-xs md:text-sm">${teacher.name || teacher.Teach_name || '-'}</p>
                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 truncate">${teacher.department || teacher.Teach_major || '-'}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-[10px] md:text-xs font-bold">${numberFormat(teacher.count || 0)}</span>
                </div>
            </div>
        `;
    });

    $('#topTeachersList').html(html);
}

function loadCharts() {
    // Load chart data from API
    loadDepartmentChart();
    loadTimelineChart();
    loadAttendanceChart();
}

function loadDepartmentChart() {
    $.getJSON('../controllers/StatController.php?action=chartDepartment', function (data) {
        if (data && data.labels && data.data) {
            initDepartmentChart(data.labels, data.data);
        }
    }).fail(function () {
        // Use fallback empty chart
        initDepartmentChart(['ไม่มีข้อมูล'], [0]);
    });
}

function loadTimelineChart() {
    $.getJSON('../controllers/StatController.php?action=chartTimeline&days=14', function (data) {
        if (data && data.labels && data.data) {
            initTimelineChart(data.labels, data.data);
        }
    }).fail(function () {
        initTimelineChart(generateDateLabels(14), new Array(14).fill(0));
    });
}

function loadAttendanceChart() {
    $.getJSON('../controllers/StatController.php?action=chartAttendance', function (data) {
        if (data && data.labels && data.data) {
            initAttendanceChart(data.labels, data.data);
        }
    }).fail(function () {
        initAttendanceChart(['ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'], [0, 0, 0, 0, 0, 0]);
    });
}

function initDepartmentChart(labels, data) {
    const ctx = document.getElementById('chartDepartment');
    if (!ctx) return;

    if (chartDepartment) {
        chartDepartment.destroy();
    }

    // Responsive legend position
    const getLegendConfig = () => {
        const isMobile = window.innerWidth < 768;
        return {
            position: 'bottom',
            display: true,
            labels: {
                boxWidth: isMobile ? 8 : 12,
                padding: isMobile ? 8 : 12,
                font: {
                    size: isMobile ? 10 : 12,
                    family: "'Mali', sans-serif",
                    weight: 'bold'
                },
                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563',
                usePointStyle: true
            }
        };
    };

    chartDepartment = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: Object.values(chartColors),
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: window.innerWidth < 768 ? '65%' : '75%',
            layout: {
                padding: {
                    top: 10,
                    bottom: 20,
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: getLegendConfig(),
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8
                }
            }
        }
    });
}

function initTimelineChart(labels, data) {
    const ctx = document.getElementById('chartTimeline');
    if (!ctx) return;

    if (chartTimeline) {
        chartTimeline.destroy();
    }

    const isMobile = window.innerWidth < 640;

    chartTimeline = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'จำนวนรายงาน',
                data: data,
                borderColor: chartColors.emerald,
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: isMobile ? 2 : 4,
                pointHoverRadius: isMobile ? 4 : 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    bottom: 10
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.03)' },
                    ticks: {
                        font: { size: isMobile ? 9 : 11 },
                        padding: 10
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: isMobile ? 9 : 11 },
                        maxTicksLimit: isMobile ? 6 : 14,
                        maxRotation: 0,
                        padding: 10
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

function initAttendanceChart(labels, data) {
    const ctx = document.getElementById('chartAttendance');
    if (!ctx) return;

    if (chartAttendance) {
        chartAttendance.destroy();
    }

    // Calculate dynamic min based on data
    const minVal = Math.min(...data.filter(v => v > 0));
    const chartMin = minVal > 0 ? Math.max(0, minVal - 10) : 0;

    const isMobile = window.innerWidth < 640;

    chartAttendance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'อัตราการเข้าเรียน (%)',
                data: data,
                backgroundColor: [
                    chartColors.blue,
                    chartColors.emerald,
                    chartColors.purple,
                    chartColors.orange,
                    chartColors.pink,
                    chartColors.cyan
                ],
                borderRadius: isMobile ? 4 : 8,
                barThickness: isMobile ? 12 : 'flex'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    bottom: 10
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: chartMin,
                    max: 100,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: isMobile ? 9 : 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: isMobile ? 9 : 11 },
                        maxRotation: 0
                    }
                }
            }
        }
    });
}

function loadHeatmap() {
    $.getJSON('../controllers/StatController.php?action=heatmap', function (data) {
        generateHeatmap(data);
    }).fail(function () {
        generateHeatmap(null);
    });
}

function generateHeatmap(data) {
    const days = [
        { key: 2, name: 'จันทร์' },
        { key: 3, name: 'อังคาร' },
        { key: 4, name: 'พุธ' },
        { key: 5, name: 'พฤหัสบดี' },
        { key: 6, name: 'ศุกร์' }
    ];

    // Find max value for normalization
    let maxVal = 1;
    if (data) {
        for (let day = 2; day <= 6; day++) {
            if (data[day]) {
                for (let period = 1; period <= 9; period++) {
                    if (data[day][period] > maxVal) {
                        maxVal = data[day][period];
                    }
                }
            }
        }
    }

    let html = '';
    days.forEach(day => {
        html += `<div class="grid grid-cols-10 gap-2">`;
        html += `<div class="text-[10px] md:text-xs font-black text-gray-400 dark:text-gray-500 flex items-center justify-center uppercase tracking-tighter">${day.name}</div>`;

        for (let period = 1; period <= 9; period++) {
            let count = 0;
            if (data && data[day.key] && data[day.key][period]) {
                count = data[day.key][period];
            }

            // Calculate intensity (0-4) based on count relative to max
            const intensity = count > 0 ? Math.max(1, Math.min(4, Math.ceil((count / maxVal) * 4))) : 0;
            const colors = [
                'bg-gray-100/50 dark:bg-gray-800/30 text-transparent',
                'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400',
                'bg-emerald-200 dark:bg-emerald-700/40 text-emerald-800 dark:text-emerald-200',
                'bg-emerald-500 dark:bg-emerald-500 text-white shadow-sm',
                'bg-emerald-700 dark:bg-emerald-400 text-white shadow-md'
            ];
            const tooltip = `${day.name} คาบ ${period}: ${numberFormat(count)} รายงาน`;
            html += `<div class="h-10 md:h-12 ${colors[intensity]} rounded-xl cursor-pointer hover:opacity-80 hover:scale-[1.02] transition-all flex flex-col items-center justify-center text-[10px] md:text-sm font-black border border-black/5 dark:border-white/5" title="${tooltip}">
                        <span>${count > 0 ? count : ''}</span>
                    </div>`;
        }
        html += `</div>`;
    });

    $('#heatmapGrid').html(html);
}

function generateDateLabels(days) {
    const labels = [];
    const today = new Date();
    for (let i = days - 1; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        labels.push(`${date.getDate()}/${date.getMonth() + 1}`);
    }
    return labels;
}

function numberFormat(num) {
    return new Intl.NumberFormat('th-TH').format(num);
}
