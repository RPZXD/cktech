/**
 * Admin Report JavaScript
 * MVC Pattern - JavaScript for admin report viewing
 */

$(document).ready(function () {
    // Load initial data
    loadReports();
    loadFilters();

    // Filter button click
    $('#btn-filter').on('click', function () {
        loadReports();
    });

    // Clear filter
    $('#btn-filter-clear').on('click', function () {
        $('#filter-teacher').val('');
        $('#filter-department').val('');
        $('#filter-level').val('');
        $('#filter-date-start').val('');
        $('#filter-date-end').val('');
        loadReports();
    });

    // Close modal
    $('#closeReportModal').on('click', function () {
        $('#reportDetailModal').addClass('hidden');
    });

    // View report detail
    $('#reportTable').on('click', '.btn-view-report', function () {
        const id = $(this).data('id');
        viewReportDetail(id);
    });

    // Export Excel
    $('#btnExportExcel').on('click', function () {
        Swal.fire({
            icon: 'info',
            title: 'กำลังพัฒนา',
            text: 'ฟังก์ชันส่งออก Excel กำลังอยู่ระหว่างการพัฒนา',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Print
    $('#btnPrintReport').on('click', function () {
        window.print();
    });
});

function loadFilters() {
    // Load teachers
    $.getJSON('../controllers/TeacherController.php?action=list', function (data) {
        let html = '<option value="">-- ทั้งหมด --</option>';
        data.forEach(teacher => {
            html += `<option value="${teacher.Teach_id}">${teacher.Teach_name}</option>`;
        });
        $('#filter-teacher').html(html);
    });

    // Load departments
    $.getJSON('../controllers/DepartmentController.php?action=list', function (data) {
        let html = '<option value="">-- ทั้งหมด --</option>';
        data.forEach(dep => {
            html += `<option value="${dep.name}">${dep.name}</option>`;
        });
        $('#filter-department').html(html);
    });
}

function loadReports() {
    const params = {
        teacher: $('#filter-teacher').val(),
        department: $('#filter-department').val(),
        level: $('#filter-level').val(),
        dateStart: $('#filter-date-start').val(),
        dateEnd: $('#filter-date-end').val()
    };

    $.getJSON('../controllers/ReportController.php?action=listAll', params, function (data) {
        renderReportTable(data);
        updateStats(data);
    }).fail(function () {
        // Fallback: try alternate endpoint
        $.getJSON('../controllers/TeachingReportController.php?action=listAll', params, function (data) {
            renderReportTable(data);
            updateStats(data);
        }).fail(function () {
            $('#reportTable tbody').html(`
                <tr>
                    <td colspan="8" class="text-center py-8 text-gray-500">
                        <i class="fas fa-info-circle text-2xl mb-2"></i>
                        <p>ไม่สามารถโหลดข้อมูลได้ หรือยังไม่มีข้อมูล</p>
                    </td>
                </tr>
            `);
        });
    });
}

function renderReportTable(data) {
    if (!Array.isArray(data) || data.length === 0) {
        $('#reportTable tbody').html(`
            <tr>
                <td colspan="8" class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-2xl mb-2"></i>
                    <p>ไม่พบข้อมูลรายงาน</p>
                </td>
            </tr>
        `);
        return;
    }

    if ($.fn.DataTable.isDataTable('#reportTable')) {
        $('#reportTable').DataTable().destroy();
    }

    let tbody = '';
    data.forEach(report => {
        const absentCount = report.absent_count || report.count_absent || 0;
        tbody += `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 text-xs">${formatDate(report.report_date || report.Teport_date)}</td>
                <td class="px-4 py-3 text-left font-medium text-gray-800 dark:text-gray-200">${report.teacher_name || report.Teach_name || '-'}</td>
                <td class="px-4 py-3 text-center hidden sm:table-cell text-gray-600 dark:text-gray-400">${report.subject_name || report.Sub_name || '-'}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs">${report.class_room || report.Teport_class || '-'}</span>
                </td>
                <td class="px-4 py-3 text-center hidden md:table-cell text-gray-600 dark:text-gray-400">${report.period || report.Teport_time || '-'}</td>
                <td class="px-4 py-3 text-left hidden lg:table-cell text-gray-600 dark:text-gray-400 truncate max-w-xs">${report.topic || report.Teport_activity || '-'}</td>
                <td class="px-4 py-3 text-center">
                    ${absentCount > 0
                ? `<span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full text-xs font-semibold">${absentCount}</span>`
                : `<span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-xs">0</span>`
            }
                </td>
                <td class="px-4 py-3 text-center">
                    <button class="btn-view-report bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1 mx-auto" data-id="${report.id || report.Teport_id}">
                        <i class="fas fa-eye"></i>
                        <span class="hidden sm:inline">ดู</span>
                    </button>
                </td>
            </tr>
        `;
    });

    $('#reportTable tbody').html(tbody);
    $('#reportTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' },
        order: [[0, 'desc']],
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        autoWidth: false,
        responsive: true
    });
}

function updateStats(data) {
    if (!Array.isArray(data)) {
        $('#statTotalReports').text('0');
        $('#statTodayReports').text('0');
        $('#statTeachersSubmitted').text('0');
        $('#statAvgAbsent').text('0');
        return;
    }

    $('#statTotalReports').text(data.length);

    // Today's reports
    const today = new Date().toISOString().split('T')[0];
    const todayReports = data.filter(r => (r.report_date || r.Teport_date) === today);
    $('#statTodayReports').text(todayReports.length);

    // Unique teachers
    const teachers = new Set(data.map(r => r.teacher_id || r.Teach_id));
    $('#statTeachersSubmitted').text(teachers.size);

    // Average absent
    const totalAbsent = data.reduce((sum, r) => sum + (parseInt(r.absent_count || r.count_absent) || 0), 0);
    const avgAbsent = data.length > 0 ? (totalAbsent / data.length).toFixed(1) : 0;
    $('#statAvgAbsent').text(avgAbsent);
}

function viewReportDetail(id) {
    $.getJSON('../controllers/TeachingReportController.php?action=detail&id=' + id, function (report) {
        if (!report) {
            Swal.fire('ไม่พบข้อมูล', 'ไม่สามารถโหลดรายละเอียดรายงานได้', 'error');
            return;
        }

        let html = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400">วันที่</p>
                        <p class="font-semibold text-gray-800 dark:text-white">${formatDate(report.report_date || report.Teport_date)}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400">ครูผู้สอน</p>
                        <p class="font-semibold text-gray-800 dark:text-white">${report.teacher_name || report.Teach_name || '-'}</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                        <p class="text-xs text-blue-600 dark:text-blue-400">วิชา</p>
                        <p class="font-semibold text-gray-800 dark:text-white">${report.subject_name || report.Sub_name || '-'}</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4">
                        <p class="text-xs text-purple-600 dark:text-purple-400">ห้อง</p>
                        <p class="font-semibold text-gray-800 dark:text-white">${report.class_room || report.Teport_class || '-'}</p>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4">
                        <p class="text-xs text-orange-600 dark:text-orange-400">คาบ</p>
                        <p class="font-semibold text-gray-800 dark:text-white">${report.period || report.Teport_time || '-'}</p>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">กิจกรรมการเรียนรู้</p>
                    <p class="text-gray-800 dark:text-white">${report.activity || report.Teport_activity || '-'}</p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4">
                    <p class="text-xs text-red-600 dark:text-red-400 mb-2">นักเรียนที่ขาดเรียน</p>
                    <p class="text-gray-800 dark:text-white">${report.absent_students || report.Teport_absent || 'ไม่มี'}</p>
                </div>
            </div>
        `;

        $('#reportDetailContent').html(html);
        $('#reportDetailModal').removeClass('hidden');
    }).fail(function () {
        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดรายละเอียดรายงานได้', 'error');
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    const thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    return `${date.getDate()} ${thaiMonths[date.getMonth()]} ${date.getFullYear() + 543}`;
}
