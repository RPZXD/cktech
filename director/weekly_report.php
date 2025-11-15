<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ผู้บริหาร') {
    header('Location: ../login.php');
    exit;
}
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
require_once('header.php');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">
    <?php require_once('wrapper.php'); ?>
    <div class="content-wrapper bg-gray-50">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center gap-2">
                            📅 สรุปรายงานการสอนรายสัปดาห์ (กลุ่มสาระ)
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container mx-auto py-6">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="w-full md:w-1/4">
                            <label class="block font-semibold mb-1">🏢 เลือกกลุ่มสาระ</label>
                            <select id="departmentSelect" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300 text-center">
                                <option value="">-- เลือกกลุ่มสาระ --</option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/3">
                            <label class="block font-semibold mb-1">📆 เลือกสัปดาห์</label>
                            <input type="week" id="weekPicker" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300 text-center" />
                        </div>
                        <div class="w-full md:w-1/3 flex justify-end mt-4 md:mt-0">
                            <button id="btnReload" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow flex items-center gap-2">
                                🔄 รีเฟรชข้อมูล
                            </button>
                        </div>
                    </div>
                </div>
                <div id="weeklySection" class="hidden ">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-lg font-bold text-blue-700 mb-4 flex items-center gap-2">📊 ตารางรายงานรายสัปดาห์</h2>
                        <div class="overflow-x-auto">
                            <table id="weeklyTable" class="min-w-full bg-white border border-gray-200 rounded-lg shadow text-sm">
                                <thead class="bg-blue-100">
                                    <tr id="weeklyTableHead">
                                        <!-- JS will fill -->
                                    </tr>
                                </thead>
                                <tbody id="weeklyTableBody">
                                    <!-- JS will fill -->
                                </tbody>
                            </table>
                        </div>
                        <div class="text-gray-500 mt-2 text-sm">* คลิกที่เครื่องหมาย ✅ เพื่อดูรายละเอียดรายงาน</div>
                    </div>
                    <div id="weeklyChartContainer" class="my-6"></div>
                </div>
                <div id="noDataMsg" class="hidden text-center text-gray-500 mt-8 text-lg">กรุณาเลือกกลุ่มสาระเพื่อแสดงข้อมูลรายงาน</div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const departmentSelect = document.getElementById('departmentSelect');
const weekPicker = document.getElementById('weekPicker');
const weeklySection = document.getElementById('weeklySection');
const noDataMsg = document.getElementById('noDataMsg');
const btnReload = document.getElementById('btnReload');
let weekDates = []; // วันในสัปดาห์นี้

function getMonday(d) {
    // คืนวันจันทร์ของสัปดาห์นั้น (ISO week, Thai timezone)
    // ปรับให้เป็นเวลาประเทศไทย (UTC+7)
    d = new Date(d);
    // ปรับ offset เป็น +7 เพื่อให้วันที่ไม่เพี้ยนข้ามวัน
    d.setHours(7, 0, 0, 0);
    var day = d.getDay();
    // day: 0=Sun, 1=Mon, ..., 6=Sat
    var diff = d.getDate() - day + (day === 0 ? -6 : 1);
    d.setDate(diff);
    return d;
}
function getWeekDatesFromMonday(monday) {
    let days = [];
    for (let i = 0; i < 5; i++) { // เฉพาะ จันทร์-ศุกร์
        let d = new Date(monday);
        d.setDate(monday.getDate() + i);
        // ปรับ offset +7 เพื่อให้แสดงวันที่ไทยถูกต้อง
        d.setHours(7, 0, 0, 0);
        days.push(new Date(d));
    }
    return days;
}
function getWeekDates() {
    // ใช้ค่าจาก weekPicker ถ้ามี
    if (weekPicker.value) {
        // weekPicker.value = "YYYY-Www"
        const [year, week] = weekPicker.value.split('-W');
        // ISO week: วันจันทร์ของสัปดาห์ที่ 1 คือสัปดาห์ที่มี Jan 4
        // หา Jan 4 ของปีนั้น
        const jan4 = new Date(Date.UTC(year, 0, 4, 7, 0, 0)); // +7h
        // วันจันทร์ของสัปดาห์ที่ 1
        const jan4Day = jan4.getUTCDay() || 7; // 1=Mon, ..., 7=Sun
        const mondayOfWeek1 = new Date(jan4);
        mondayOfWeek1.setUTCDate(jan4.getUTCDate() - (jan4Day - 1));
        // วันจันทร์ของสัปดาห์ที่ต้องการ
        const monday = new Date(mondayOfWeek1);
        monday.setUTCDate(mondayOfWeek1.getUTCDate() + (parseInt(week, 10) - 1) * 7);
        // ปรับเป็นเขตเวลาไทย
        monday.setHours(7, 0, 0, 0);
        return getWeekDatesFromMonday(monday); // เฉพาะ จันทร์-ศุกร์
    } else {
        const monday = getMonday(new Date());
        return getWeekDatesFromMonday(monday); // เฉพาะ จันทร์-ศุกร์
    }
}
function formatDateISO(d) {
    return d.toISOString().slice(0,10);
}
function formatThaiDateShort(d) {
    const days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
    const months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    // return days[d.getDay()] + ' ' + d.getDate() + ' ' + months[d.getMonth() + 1];
    return days[d.getDay()];
}

function formatThaiDateShort2(d) {
    const months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
}
function formatThaiDateLong(d) {
    // d: Date object
    const months = [
        '', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
}

// โหลดรายชื่อกลุ่มสาระ
function loadDepartments(selectedDepartment = null) {
    fetch('../controllers/DepartmentController.php?action=list')
        .then(res => res.json())
        .then(data => {
            departmentSelect.innerHTML = '<option value="">-- เลือกกลุ่มสาระ --</option>';
            data.forEach(dep => {
                departmentSelect.innerHTML += `<option value="${dep.name}">${dep.name}</option>`;
            });
            if (selectedDepartment) {
                departmentSelect.value = selectedDepartment;
            }
        });
}

// โหลดข้อมูลรายงานรายสัปดาห์
function loadWeeklyReport(department) {
    if (!department) {
        weeklySection.classList.add('hidden');
        noDataMsg.classList.remove('hidden');
        return;
    }
    weekDates = getWeekDates();
    // ดึงรายชื่อครูในกลุ่มสาระ
    $.getJSON('../controllers/DepartmentController.php?action=listTeachers&department=' + encodeURIComponent(department), function(teachers) {
        // ดึงรายงานของครูแต่ละคนในสัปดาห์นี้
        const teacherIds = teachers.map(t => t.Teach_id);
        if (teacherIds.length === 0) {
            $('#weeklyTableHead').html('');
            $('#weeklyTableBody').html('<tr><td colspan="8" class="text-center text-gray-400 py-6">ไม่มีครูในกลุ่มสาระนี้</td></tr>');
            weeklySection.classList.remove('hidden');
            noDataMsg.classList.add('hidden');
            return;
        }
        // ดึงรายงานทั้งหมดของครูในสัปดาห์นี้
        $.getJSON('../controllers/TeachingReportController.php?action=listByTeachers&teacher_ids=' + encodeURIComponent(teacherIds.join(',')) + '&week_start=' + formatDateISO(weekDates[0]) + '&week_end=' + formatDateISO(weekDates[4]), function(reports) {
            // เตรียม map: { [Teach_id]: { [date]: [report, ...] } }
            const reportMap = {};
            reports.forEach(r => {
                if (!reportMap[r.teacher_id]) reportMap[r.teacher_id] = {};
                if (!reportMap[r.teacher_id][r.report_date]) reportMap[r.teacher_id][r.report_date] = [];
                reportMap[r.teacher_id][r.report_date].push(r);
            });
            // สร้างหัวตาราง
            let head = '<th class="py-2 px-3 border-b text-center">👩‍🏫 ครู</th>';
            weekDates.forEach(d => {
                head += `<th class="py-2 px-3 border-b text-center">${formatThaiDateShort(d)}<br><span class="text-xs text-gray-400">${formatThaiDateShort2(d)}</span></th>`;
            });
            $('#weeklyTableHead').html(head);
            // สร้าง body
            let body = '';
            teachers.forEach(teacher => {
                body += `<tr><td class="py-2 px-3 border-b font-semibold">${teacher.Teach_name}</td>`;
                weekDates.forEach(d => {
                    const dateStr = formatDateISO(d);
                    // หา report ทั้งหมดของครูในวันนั้น
                    const reportsOfDay = (reportMap[teacher.Teach_id] && reportMap[teacher.Teach_id][dateStr]) ? reportMap[teacher.Teach_id][dateStr] : [];
                    if (reportsOfDay.length > 0) {
                        body += `<td class="py-2 px-3 border-b text-center">`;
                        reportsOfDay.forEach((report, idx) => {
                            body += `<button class="btn-detail bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 rounded mb-1"
                                data-id="${report.id}"
                                title="คาบ ${report.period_start}-${report.period_end}${reportsOfDay.length > 1 ? ' ('+(idx+1)+')' : ''}">
                                ✅ คาบ ${report.period_start}-${report.period_end}
                            </button><br/>`;
                        });
                        body += `</td>`;
                    } else {
                        body += `<td class="py-2 px-3 border-b text-center text-gray-300">-</td>`;
                    }
                });
                body += '</tr>';
            });
            $('#weeklyTableBody').html(body);

            // ปุ่ม Print
            if ($('#printBtn').length === 0) {
                $('.overflow-x-auto').prepend(`
                    <div class="mb-4 flex justify-end">
                        <button id="printBtn" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded shadow flex items-center gap-2 print:hidden">
                            🖨️ พิมพ์รายงาน
                        </button>
                    </div>
                `);
            }

            // กำหนดหัวกระดาษสำหรับ print
            const weekStart = formatThaiDateLong(weekDates[0]);
            const weekEnd = formatThaiDateLong(weekDates[weekDates.length - 1]);
            const headerText = `
                <div style="text-align:center;font-size:1.1rem;">
                    <div>รายสัปดาห์ระหว่างวันที่ ${weekStart} ถึง ${weekEnd}</div>
                    <div>ของกลุ่มสาระ <span id="print-major">${department}</span></div>
                </div>
                <br/>
            `;

            // ปุ่ม print
            $('#printBtn').off('click').on('click', function () {
                // สร้าง window ใหม่สำหรับ print
                let printWindow = window.open('', '', 'width=900,height=700');

                let style = `
                    <style>
                    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
                    body {
                        font-family: 'Sarabun', sans-serif;
                        background: white;
                        padding: 20px;
                        color: #000;
                        font-size: 0.8rem;
                    }
                    h1, h2 {
                        text-align: center;
                        font-weight: bold;
                        margin-bottom: 1rem;
                        font-size: 1.2rem;
                    }
                    table {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 0.8rem;
                        margin-top: 0.8rem;
                    }
                    th, td {
                        border: 1px solid #888;
                        padding: 6px 8px;
                        text-align: center;
                        vertical-align: top;
                    }
                    th {
                        background-color: #e0e7ff;
                        font-weight: bold;
                    }
                    .font-semibold { font-weight: bold; }
                    .text-gray-300 { color: #bbb; }
                    .text-center { text-align: center; }
                    .mb-1 { margin-bottom: 0.25rem; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mb-4 { margin-bottom: 1rem; }
                    .print\\:hidden { display: none !important; }
                    </style>
                `;

                let tableHtml = '<table>' + $('#weeklyTable').html() + '</table>';

                printWindow.document.write(`
                    <html>
                    <head>
                    <title>รายงานการสอน รายสัปดาห์ระหว่างวันที่ ${weekStart} ถึง ${weekEnd} ของ ของกลุ่มสาระ${department}</title>
                    ${style}
                    </head>
                    <body>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="../dist/img/logo-phicha.png" alt="Logo" style="width: 50px; height: auto;">
                    </div>
                    <h1>รายงานการสอนรายสัปดาห์</h1>
                    ${typeof headerText !== 'undefined' ? headerText : ''}
                    ${tableHtml}
                    </body>
                    </html>
                `);

                printWindow.document.close();
                setTimeout(() => printWindow.print(), 300);
                });

            // Chart
            renderWeeklyChart(teachers, weekDates, reportMap);

            weeklySection.classList.remove('hidden');
            noDataMsg.classList.add('hidden');
        });
    });
}

// Modal รายละเอียดรายงาน
function showReportDetail(reportId) {
    $.getJSON('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId), function(report) {
        let html = `<div class="text-lg font-bold mb-2">รายละเอียดรายงานการสอน</div>
            <div class="mb-2 text-left"><span class="font-semibold">📅 วันที่:</span> ${report.report_date}</div>
            <div class="mb-2 text-left"><span class="font-semibold">⏰ คาบ:</span> ${report.period_start} - ${report.period_end}</div>
            <div class="mb-2 text-left"><span class="font-semibold">📖 วิชา:</span> ${report.subject_name || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">🏫 ห้อง:</span> ม.${report.level}/${report.class_room}</div>
            <div class="mb-2 text-left"><span class="font-semibold">📝 แผน/หัวข้อ:</span> ${report.plan_topic || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">👨‍🏫 กิจกรรม:</span> ${report.activity || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">🙋‍♂️ ขาดเรียน:</span> ${report.absent_students || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">💡 K:</span> ${report.reflection_k || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">💡 P:</span> ${report.reflection_p || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">💡 A:</span> ${report.reflection_a || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">❗ ปัญหา:</span> ${report.problems || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">📝 ข้อเสนอแนะ:</span> ${report.suggestions || '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">🖼️ รูปภาพ 1:</span> ${report.image1 ? `<img src="../${report.image1}" class="inline-block max-h-32 rounded border" />` : '-'}</div>
            <div class="mb-2 text-left"><span class="font-semibold">🖼️ รูปภาพ 2:</span> ${report.image2 ? `<img src="../${report.image2}" class="inline-block max-h-32 rounded border" />` : '-'}</div>
        `;
        Swal.fire({
            html: html,
            width: 700,
            showCloseButton: true,
            showConfirmButton: false
        });
    });
}

function renderWeeklyChart(teachers, weekDates, reportMap) {
    // สร้างข้อมูลสำหรับ chart: จำนวนรายงานแต่ละวัน (รวมทุกครู)
    const labels = weekDates.map(d => formatThaiDateShort(d) + ' ' + formatThaiDateLong(d));
    const data = weekDates.map(d => {
        const dateStr = formatDateISO(d);
        let count = 0;
        teachers.forEach(teacher => {
            if (reportMap[teacher.Teach_id] && reportMap[teacher.Teach_id][dateStr]) {
                count += reportMap[teacher.Teach_id][dateStr].length;
            }
        });
        return count;
    });

    // ลบ chart เดิมถ้ามี
    if (window.weeklyChart) {
        window.weeklyChart.destroy();
    }
    const ctxId = 'weeklyChartCanvas';
    $('#weeklyChartContainer').html('<canvas id="'+ctxId+'" height="80"></canvas>');
    const ctx = document.getElementById(ctxId).getContext('2d');
    window.weeklyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'จำนวนรายงานต่อวัน',
                data: data,
                backgroundColor: '#60a5fa'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'จำนวนรายงานการสอนแต่ละวันในสัปดาห์นี้' }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision:0 } }
            }
        }
    });
}

$(document).off('click', '.btn-detail').on('click', '.btn-detail', function() {
    const reportId = $(this).data('id');
    showReportDetail(reportId);
});

departmentSelect.addEventListener('change', function() {
    loadWeeklyReport(this.value);
});
weekPicker.addEventListener('change', function() {
    loadWeeklyReport(departmentSelect.value);
});
btnReload.addEventListener('click', function() {
    loadDepartments();
    setTimeout(() => loadWeeklyReport(departmentSelect.value), 300);
});

// ตั้งค่า weekPicker เป็นสัปดาห์ปัจจุบัน
(function setDefaultWeek() {
    const now = new Date();
    const year = now.getFullYear();
    // week number
    const onejan = new Date(now.getFullYear(),0,1);
    const week = Math.ceil((((now - onejan) / 86400000) + onejan.getDay()+1)/7);
    weekPicker.value = year + '-W' + (week < 10 ? '0' + week : week);
})();

// เริ่มต้น
loadDepartments();
</script>
<?php require_once('script.php'); ?>
</body>
</html>
