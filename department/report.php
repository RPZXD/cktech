<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'หัวหน้ากลุ่มสาระ') {
    header('Location: ../login.php');
    exit;
}
// โหลด config
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];

$department = $_SESSION['user']['Teach_major'];
require_once('header.php');
?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">
    <?php require_once('wrapper.php'); ?>
    <div class="content-wrapper bg-gray-50">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center gap-2">
                            📑 ตรวจสอบรายงานการสอน
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container mx-auto py-6">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="w-full md:w-1/2">
                            <label class="block font-semibold mb-1">👩‍🏫 เลือกครูในกลุ่มสาระของคุณ </label>
                            <select id="teacherSelect" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300 text-center">
                                <option value="">-- เลือกครู --</option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/2 flex justify-end mt-4 md:mt-0">
                            <button id="btnReload" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow flex items-center gap-2">
                                🔄 รีเฟรชข้อมูล
                            </button>
                        </div>
                    </div>
                </div>
                <div id="reportSection" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                            <h2 class="text-lg font-bold text-blue-700 mb-4 flex items-center gap-2">📅 ปฏิทินรายงานการสอน</h2>
                            <div id="calendar" class="w-full" style="min-height: 500px;"></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h2 class="text-lg font-bold text-blue-700 mb-4 flex items-center gap-2">📋 ตารางรายงานการสอน</h2>
                            <div class="overflow-x-auto">
                                <table id="reportTable" class="min-w-full bg-white border border-gray-200 rounded-lg shadow text-sm">
                                    <thead class="bg-blue-100">
                                        <tr>
                                            <th class="py-2 px-3 border-b text-center">📅 วันที่</th>
                                            <th class="py-2 px-3 border-b text-center">📖 วิชา</th>
                                            <th class="py-2 px-3 border-b text-center">🏫 ห้อง</th>
                                            <th class="py-2 px-3 border-b text-center">⏰ คาบ</th>
                                            <th class="py-2 px-3 border-b text-center">📝 แผน/หัวข้อ</th>
                                            <th class="py-2 px-3 border-b text-center">🔍 ดูรายละเอียด</th>
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
                <div id="noDataMsg" class="hidden text-center text-gray-500 mt-8 text-lg">กรุณาเลือกครูเพื่อแสดงข้อมูลรายงาน</div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
const teacherSelect = document.getElementById('teacherSelect');
const reportSection = document.getElementById('reportSection');
const noDataMsg = document.getElementById('noDataMsg');
const btnReload = document.getElementById('btnReload');
let calendar = null;
let dataTable = null;

const department = '<?php echo $department; ?>';

// โหลดรายชื่อครูในกลุ่มสาระของหัวหน้ากลุ่มนี้
function loadTeachersOfDepartmentHead(selectedTeacherId = null) {
    fetch('../controllers/DepartmentController.php?action=listTeachers&department=' + encodeURIComponent(department))
        .then(res => res.json())
        .then(data => {
            teacherSelect.innerHTML = '<option value="">-- เลือกครู --</option>';
            data.forEach(teacher => {
                teacherSelect.innerHTML += `<option value="${teacher.Teach_id}">${teacher.Teach_name}</option>`;
            });
            // ถ้ามีการเลือกครูไว้ ให้เลือกอัตโนมัติ
            if (selectedTeacherId) {
                teacherSelect.value = selectedTeacherId;
            }
        });
}

// ล้างข้อมูลตารางและปฏิทิน
function clearReportDisplay() {
    if (dataTable) {
        dataTable.destroy();
        dataTable = null;
    }
    if (calendar) {
        calendar.destroy();
        calendar = null;
    }
    document.querySelector('#reportTable tbody').innerHTML = '';
    document.getElementById('calendar').innerHTML = '';
    reportSection.classList.add('hidden');
    noDataMsg.classList.remove('hidden');
}

// เมื่อเลือกครู โหลดรายงาน
teacherSelect.addEventListener('change', function() {
    clearReportDisplay();
    const teacherId = this.value;
    if (!teacherId) {
        return;
    }
    loadReportsForTeacher(teacherId);
});

// ปุ่มรีเฟรชข้อมูล
btnReload.addEventListener('click', function() {
    const teacherId = teacherSelect.value;
    clearReportDisplay();
    loadTeachersOfDepartmentHead(teacherId); // โหลดรายชื่อครูใหม่ (และเลือกครูเดิมถ้ามี)
    if (teacherId) {
        // โหลดข้อมูลรายงานใหม่
        setTimeout(() => loadReportsForTeacher(teacherId), 200);
    }
});

// 4. โหลดรายงานการสอนของครู
function loadReportsForTeacher(teacherId) {
    fetch('../controllers/TeachingReportController.php?action=list&teacher_id=' + encodeURIComponent(teacherId))
        .then(res => res.json())
        .then(data => {
            renderCalendar(data);
            renderReportTable(data);
            reportSection.classList.remove('hidden');
            noDataMsg.classList.add('hidden');
        });
}

// 5. Render FullCalendar
function renderCalendar(reports) {
    if (calendar) {
        calendar.destroy();
    }
    const calendarEl = document.getElementById('calendar');
    // ปรับขนาด calendar ให้เต็ม container เสมอ
    setTimeout(() => {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'th',
            height: 'auto',
            contentHeight: 500,
            aspectRatio: 1.8,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: reports.map(report => ({
                id: report.id,
                title: (report.subject_name || '-') + ' (' + (report.class_room || '-') + ')',
                start: report.report_date,
                extendedProps: report
            })),
            eventClick: function(info) {
                const report = info.event.extendedProps;
                showReportDetail(report);
            }
        });
        calendar.render();
    }, 100);
}

// 6. Render DataTable
function renderReportTable(reports) {
    const tbody = document.querySelector('#reportTable tbody');
    tbody.innerHTML = '';
    if (!reports.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-400 py-6">ไม่มีข้อมูลรายงานการสอน</td></tr>`;
        if (dataTable) dataTable.destroy();
        return;
    }
    // ไม่ต้อง sort ใน JS ให้ DataTables จัดการเอง
    reports.forEach(report => {
        tbody.innerHTML += `
            <tr class="hover:bg-blue-50">
                <td class="py-2 px-3 border-b text-center" data-order="${report.report_date}">${formatThaiDate(report.report_date)}</td>
                <td class="py-2 px-3 border-b text-center">${report.subject_name || '-'}</td>
                <td class="py-2 px-3 border-b text-center">ม.${report.level}/${report.class_room}</td>
                <td class="py-2 px-3 border-b text-center">${report.period_start} - ${report.period_end}</td>
                <td class="py-2 px-3 border-b text-center">${report.plan_topic ? report.plan_topic.substring(0, 20) + '...' : '-'}</td>
                <td class="py-2 px-3 border-b text-center">
                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded btn-detail flex items-center gap-1" data-id="${report.id}">
                        👁️ ดู
                    </button>
                </td>
            </tr>
        `;
    });
    if (dataTable) dataTable.destroy();
    dataTable = $('#reportTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        pagingType: 'simple',
        searching: true,
        info: true,
        autoWidth: false
    });

    // bind รายละเอียด
    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            const reportId = btn.getAttribute('data-id');
            const report = reports.find(r => r.id == reportId);
            showReportDetail(report);
        });
    });
}

// Helper: แปลงวันที่เป็นภาษาไทย
function formatThaiDate(dateStr) {
    if (!dateStr) return '-';
    const months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const day = d.getDate();
    const month = months[d.getMonth() + 1];
    const year = d.getFullYear() + 543;
    return `${day} ${month} ${year}`;
}

// Modal รายละเอียดรายงาน
function showReportDetail(report) {
    let html = `<div class="text-lg font-bold mb-2">รายละเอียดรายงานการสอน</div>
        <div class="mb-2 text-left"><span class="font-semibold">📅 วันที่:</span> ${formatThaiDate(report.report_date)}</div>
        <div class="mb-2 text-left"><span class="font-semibold">📖 วิชา:</span> ${report.subject_name || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">🏫 ห้อง:</span> ม.${report.level}/${report.class_room}</div>
        <div class="mb-2 text-left"><span class="font-semibold">⏰ คาบ:</span> ${report.period_start} - ${report.period_end}</div>
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
}

// เริ่มต้น
loadTeachersOfDepartmentHead();
</script>
<?php require_once('script.php'); ?>
</body>
</html>
</html>
