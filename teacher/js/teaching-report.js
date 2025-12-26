/**
 * Teaching Report JavaScript
 * MVC Pattern - Separated JS for Teaching Report page
 */

document.addEventListener('DOMContentLoaded', function () {
    // NOTE: loadReports() and loadSubjectsForReport() are called at the END of this handler
    // to ensure all functions are defined before being called (no hoisting for window.x = function)

    // ฟังก์ชันแปลงวันที่เป็นภาษาไทย
    function formatThaiDate(dateStr) {
        if (!dateStr) return '-';
        const months = [
            '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
            'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
        ];
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        const day = d.getDate();
        const month = months[d.getMonth() + 1];
        const year = d.getFullYear() + 543;
        return `${day} ${month} ${year}`;
    }

    // Helper: แปลงวันที่ (YYYY-MM-DD) เป็นชื่อวันภาษาไทย
    function getThaiDayOfWeek(dateStr) {
        const days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '-';
        return days[d.getDay()];
    }

    function renderDetailBtn(reportId) {
        return `
            <div class="grid grid-cols-2 gap-4 ">
                <button class="bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white w-9 h-9 rounded-lg shadow-sm transition-all duration-200 btn-report-detail flex items-center justify-center hover:scale-110 hover:shadow-md" data-id="${reportId}" title="ดูรายละเอียด">
                    <i class="fas fa-eye text-sm"></i>
                </button>
                <button class="bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white w-9 h-9 rounded-lg shadow-sm transition-all duration-200 btn-edit-report flex items-center justify-center hover:scale-110 hover:shadow-md" data-id="${reportId}" title="แก้ไข">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <button class="bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white w-9 h-9 rounded-lg shadow-sm transition-all duration-200 btn-delete-report flex items-center justify-center hover:scale-110 hover:shadow-md" data-id="${reportId}" title="ลบ">
                    <i class="fas fa-trash text-sm"></i>
                </button>
                <button class="bg-gradient-to-br from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white w-9 h-9 rounded-lg shadow-sm transition-all duration-200 btn-print-report flex items-center justify-center hover:scale-110 hover:shadow-md" data-id="${reportId}" title="พิมพ์">
                    <i class="fas fa-print text-sm"></i>
                </button>
            </div>
        `;
    }

    function parseAttendanceCount(listString) {
        if (!listString) return 0;
        return listString.split(/[,\n]/).map(item => item.trim()).filter(Boolean).length;
    }

    function updateReportStats(sortedReports = []) {
        const totalReports = sortedReports.length;
        let totalMissEvents = 0;
        let perfectSessions = 0;

        sortedReports.forEach(report => {
            const absent = parseAttendanceCount(report.absent_students);
            const sick = parseAttendanceCount(report.sick_students);
            const personal = parseAttendanceCount(report.personal_students);
            const activity = parseAttendanceCount(report.activity_students);
            const sum = absent + sick + personal + activity;
            totalMissEvents += sum;
            if (sum === 0) perfectSessions += 1;
        });

        const avgMiss = totalReports ? (totalMissEvents / totalReports).toFixed(1) : '0';
        const latest = sortedReports[0] || null;

        const statTotalEl = document.getElementById('statTotalReports');
        const statUpdatedEl = document.getElementById('statUpdatedAt');
        const statPerfectEl = document.getElementById('statPerfectSessions');
        const statAvgEl = document.getElementById('statAverageAbsent');
        const statLatestEl = document.getElementById('statLatestInfo');

        if (statTotalEl) statTotalEl.textContent = totalReports.toString();
        if (statUpdatedEl) statUpdatedEl.textContent = `อัปเดตล่าสุด: ${latest ? formatThaiDate(latest.report_date) : '-'}`;
        if (statPerfectEl) statPerfectEl.textContent = perfectSessions.toString();
        if (statAvgEl) statAvgEl.textContent = avgMiss;
        if (statLatestEl) {
            if (!latest) {
                statLatestEl.textContent = 'ยังไม่มีข้อมูล';
            } else {
                const subject = latest.subject_name || '-';
                const room = latest.level && latest.class_room ? `ม.${latest.level}/${latest.class_room}` : '-';
                statLatestEl.textContent = `${subject} · ${room} · คาบ ${latest.period_start}-${latest.period_end}`;
            }
        }
    }

    window.loadReports = function () {
        fetch('../controllers/TeachingReportController.php?action=list')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('reportTableBody');
                const mobileContainer = document.getElementById('mobileReportCards');
                tbody.innerHTML = '';

                if (!data.length) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-400 py-6">ไม่มีข้อมูลรายงานการสอน</td></tr>`;
                    if (mobileContainer) {
                        mobileContainer.innerHTML = `<div class="text-center text-gray-400 py-8">
                            <i class="fas fa-clipboard text-4xl mb-3 opacity-50"></i>
                            <p>ไม่มีข้อมูลรายงานการสอน</p>
                        </div>`;
                    }
                    updateReportStats([]);
                    if ($.fn.DataTable.isDataTable('#reportTableBody')) {
                        $('#reportTableBody').DataTable().destroy();
                    }
                    return;
                }

                const sortedData = data.sort((a, b) => new Date(b.report_date) - new Date(a.report_date));
                updateReportStats(sortedData);

                // Render mobile cards with improved touch targets
                if (mobileContainer) {
                    mobileContainer.innerHTML = sortedData.map(report => `
                        <div class="glow-card bg-white/90 dark:bg-gray-800/90 rounded-2xl p-4 border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-all duration-300">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">${getThaiDayOfWeek(report.report_date)}</div>
                                    <div class="text-lg font-bold text-red-700 dark:text-red-300">${formatThaiDate(report.report_date)}</div>
                                </div>
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">ม.${report.level}/${report.class_room}</span>
                            </div>
                            <div class="mb-3">
                                <div class="text-indigo-900 dark:text-indigo-300 font-semibold text-base">${report.subject_name || '-'}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                                    <span>⏰</span> คาบ ${report.period_start}-${report.period_end}
                                </div>
                            </div>
                            ${report.plan_topic ? `<div class="text-sm text-gray-700 dark:text-gray-300 mb-2 line-clamp-2 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg"><span class="font-medium text-indigo-600 dark:text-indigo-400">หัวข้อ:</span> ${report.plan_topic}</div>` : ''}
                            ${report.activity ? `<div class="text-sm text-gray-700 dark:text-gray-300 mb-3 line-clamp-2 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg"><span class="font-medium text-green-600 dark:text-green-400">กิจกรรม:</span> ${report.activity}</div>` : ''}
                            <div class="grid grid-cols-4 gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <button class="touch-feedback btn-report-detail flex flex-col items-center justify-center gap-1 bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white p-3 rounded-xl shadow-md min-h-[52px] transition-all" data-id="${report.id}" aria-label="ดูรายละเอียด">
                                    <i class="fas fa-eye text-lg"></i>
                                    <span class="text-[10px] font-medium">ดู</span>
                                </button>
                                <button class="touch-feedback btn-edit-report flex flex-col items-center justify-center gap-1 bg-gradient-to-br from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white p-3 rounded-xl shadow-md min-h-[52px] transition-all" data-id="${report.id}" aria-label="แก้ไข">
                                    <i class="fas fa-edit text-lg"></i>
                                    <span class="text-[10px] font-medium">แก้ไข</span>
                                </button>
                                <button class="touch-feedback btn-delete-report flex flex-col items-center justify-center gap-1 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white p-3 rounded-xl shadow-md min-h-[52px] transition-all" data-id="${report.id}" aria-label="ลบ">
                                    <i class="fas fa-trash text-lg"></i>
                                    <span class="text-[10px] font-medium">ลบ</span>
                                </button>
                                <button class="touch-feedback btn-print-report flex flex-col items-center justify-center gap-1 bg-gradient-to-br from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white p-3 rounded-xl shadow-md min-h-[52px] transition-all" data-id="${report.id}" aria-label="พิมพ์">
                                    <i class="fas fa-print text-lg"></i>
                                    <span class="text-[10px] font-medium">พิมพ์</span>
                                </button>
                            </div>
                        </div>
                    `).join('');
                }

                sortedData.forEach(report => {
                    tbody.innerHTML += `
                        <tr class="group transition-all duration-300 border-b border-gray-200/70 dark:border-gray-800">
                            <td class="text-center">
                                <div class="font-semibold text-red-700 dark:text-red-300 text-base">${formatThaiDate(report.report_date)}</div>
                                <div class="text-xs text-indigo-500 dark:text-indigo-400">${getThaiDayOfWeek(report.report_date)}</div>
                            </td>
                            <td class="text-center text-indigo-900 dark:text-indigo-500 font-medium">${report.subject_name || '-'}</td>
                            <td class="text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-900 dark:bg-blue-500 text-white shadow-sm">ม.${report.level}/${report.class_room}</span>
                            </td>
                            <td class="text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-900 dark:bg-blue-500 text-white shadow-sm">${report.period_start}-${report.period_end}</span>
                            </td>
                            <td class="text-center">
                                <div class="max-w-xs truncate text-dark" title="${report.plan_topic || '-'}">
                                    ${report.plan_topic ? (report.plan_topic.length > 30 ? report.plan_topic.substring(0, 30) + '...' : report.plan_topic) : '-'}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="max-w-xs truncate text-dark" title="${report.activity || '-'}">
                                    ${report.activity ? (report.activity.length > 30 ? report.activity.substring(0, 30) + '...' : report.activity) : '-'}
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white px-3 py-1 rounded-lg shadow-sm hover:shadow-lg transition-all duration-200 btn-show-attendance flex items-center gap-2 text-sm hover:-translate-y-0.5" data-id="${report.id}">
                                    <span class="text-sm">📋</span> <span class="hidden sm:inline font-medium">ดู</span>
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2 p-1 rounded-lg">${renderDetailBtn(report.id)}</div>
                            </td>
                        </tr>
                    `;
                });

                // Initialize DataTables
                if (!$.fn.DataTable.isDataTable('.min-w-full')) {
                    $('.min-w-full').DataTable({
                        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' },
                        stripe: true,
                        autoWidth: false,
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        pagingType: 'simple',
                        searching: true,
                        info: true,
                        order: [[0, 'desc']],
                        columnDefs: [
                            { targets: 0, width: '12%', type: 'date' },
                            { targets: 1, width: '15%' },
                            { targets: 2, width: '8%' },
                            { targets: 3, width: '8%' },
                            { targets: 4, width: '15%' },
                            { targets: 5, width: '15%' },
                            { targets: 6, width: '8%' },
                            { targets: 7, width: '12%', orderable: false }
                        ]
                    });
                }

                // Bind event handlers for both desktop table and mobile cards
                bindReportEventHandlers();
            });
    };

    function bindReportEventHandlers() {
        // View details
        document.querySelectorAll('.btn-report-detail').forEach(btn => {
            btn.addEventListener('click', function () {
                showReportDetail(btn.getAttribute('data-id'));
            });
        });

        // Show attendance
        document.querySelectorAll('.btn-show-attendance').forEach(btn => {
            btn.addEventListener('click', function () {
                showAttendanceDetail(btn.getAttribute('data-id'));
            });
        });

        // Delete report
        document.querySelectorAll('.btn-delete-report').forEach(btn => {
            btn.addEventListener('click', function () {
                const reportId = btn.getAttribute('data-id');
                Swal.fire({
                    title: 'ยืนยันการลบ',
                    text: 'คุณต้องการลบรายงานนี้หรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก'
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('../controllers/TeachingReportController.php?action=delete', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: reportId })
                        })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) {
                                    Swal.fire('ลบสำเร็จ', 'ลบรายงานเรียบร้อยแล้ว', 'success');
                                    loadReports();
                                } else {
                                    Swal.fire('ผิดพลาด', 'ไม่สามารถลบรายงานได้', 'error');
                                }
                            });
                    }
                });
            });
        });

        // Print report
        document.querySelectorAll('.btn-print-report').forEach(btn => {
            btn.addEventListener('click', function () {
                window.open('../teacher/print_report.php?id=' + encodeURIComponent(btn.getAttribute('data-id')), '_blank');
            });
        });

        // Edit report
        document.querySelectorAll('.btn-edit-report').forEach(btn => {
            btn.addEventListener('click', function () {
                const reportId = btn.getAttribute('data-id');
                openEditReportModal(reportId);
            });
        });
    }

    // Subject and classroom handling
    const subjectSelect = document.getElementById('subjectSelect');
    const reportDateInput = document.getElementById('reportDate');
    const classRoomSelectArea = document.getElementById('classRoomSelectArea');
    let subjectClassRooms = {};

    window.loadSubjectsForReport = function () {
        const teacherId = window.TEACHER_ID || null;
        const params = new URLSearchParams({ action: 'list', onlyOpen: 1 });
        if (teacherId) params.append('teacherId', teacherId);

        fetch('../controllers/SubjectController.php?' + params.toString(), { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('subjectSelect');
                if (!select) return;
                select.innerHTML = `<option value="">-- เลือกวิชา --</option>`;
                subjectClassRooms = {};
                (Array.isArray(data) ? data : []).forEach(subject => {
                    subjectClassRooms[String(subject.id)] = Array.isArray(subject.class_periods) ? subject.class_periods : [];
                    const code = subject.code ? (subject.code + ' ') : '';
                    const name = subject.name || subject.subject_name || '';
                    const level = subject.level || '';
                    const opt = document.createElement('option');
                    opt.value = subject.id;
                    opt.textContent = `${code}${name}`;
                    if (level) opt.setAttribute('data-class', level);
                    select.appendChild(opt);
                });
            });
    };

    function renderClassRoomCheckboxes(subjectId, reportDate) {
        classRoomSelectArea.innerHTML = '';
        if (!subjectId || !subjectClassRooms[subjectId] || !reportDate) return;
        const thaiDay = getThaiDayOfWeek(reportDate);
        const rooms = subjectClassRooms[subjectId].filter(r => r.day_of_week === thaiDay);
        const roomMap = {};
        rooms.forEach(r => {
            if (!roomMap[r.class_room]) roomMap[r.class_room] = [];
            roomMap[r.class_room].push(r);
        });

        if (Object.keys(roomMap).length === 0) {
            classRoomSelectArea.innerHTML = `<div class="text-red-500 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">ไม่มีห้องเรียนที่สอนในวัน${thaiDay} สำหรับวิชานี้ ❌</div>`;
            return;
        }

        let html = `<label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">🏫 เลือกห้องเรียน <span class="text-red-500">*</span></label>
            <div class="flex flex-wrap gap-3 mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">`;
        Object.keys(roomMap).forEach(room => {
            html += `
                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 p-2 rounded-lg transition-colors duration-200">
                    <input type="checkbox" name="class_room[]" value="${room}" class="form-checkbox report-class-room-checkbox text-blue-600 focus:ring-blue-500" />
                    <span class="text-gray-800 dark:text-gray-200 font-medium">${room}</span>
                </label>
            `;
        });
        html += `</div><div id="reportClassPeriodsArea"></div>`;
        classRoomSelectArea.innerHTML = html;
    }

    function renderClassPeriodsInputs(subjectId, selectedRooms, reportDate) {
        const area = document.getElementById('reportClassPeriodsArea');
        area.innerHTML = '';
        if (!subjectId || !subjectClassRooms[subjectId] || !selectedRooms.length || !reportDate) return;
        const thaiDay = getThaiDayOfWeek(reportDate);
        const rooms = subjectClassRooms[subjectId].filter(r => r.day_of_week === thaiDay);

        selectedRooms.forEach(room => {
            const periods = rooms.filter(r => r.class_room === room);
            const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
            area.innerHTML += `
                <div class="mb-4 border border-gray-200 dark:border-gray-600 rounded-xl p-4 bg-gray-50 dark:bg-gray-700 shadow-sm">
                    <div class="font-bold text-blue-700 dark:text-blue-400 mb-3 text-lg flex items-center gap-2">⏰ ${room}</div>
                    <div class="flex flex-wrap gap-3">
                        ${periods.map(p => `
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 p-3 rounded-lg transition-colors duration-200 border border-gray-300 dark:border-gray-500">
                                <input type="checkbox" name="periods[${key}][]" data-room="${room}" value="${p.period_start}|${p.period_end}|${p.day_of_week}" class="form-checkbox text-green-600 focus:ring-green-500 report-period-checkbox" />
                                <span class="text-gray-800 dark:text-gray-200 font-medium">${p.day_of_week} คาบ ${p.period_start}-${p.period_end}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        });
    }

    function renderRoomImageInputs(checkedRooms) {
        const area = document.getElementById('roomImageInputsArea');
        if (!area) return;
        area.innerHTML = '';
        checkedRooms.forEach(room => {
            const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
            area.innerHTML += `
                <div class="mb-4 border border-gray-200 dark:border-gray-600 rounded-xl p-4 bg-gray-50 dark:bg-gray-700 shadow-sm">
                    <div class="font-bold text-blue-700 dark:text-blue-400 mb-3 text-lg flex items-center gap-2">🖼️ แนบรูปภาพสำหรับห้อง ${room}</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">📸 แนบรูปภาพ 1</label>
                            <input type="file" name="image1_${key}" data-room="${room}" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 transition-all duration-200" />
                        </div>
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">📸 แนบรูปภาพ 2</label>
                            <input type="file" name="image2_${key}" data-room="${room}" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 transition-all duration-200" />
                        </div>
                    </div>
                </div>
            `;
        });
    }

    // Event listeners for subject and date changes
    if (subjectSelect) {
        subjectSelect.addEventListener('change', function () {
            renderClassRoomCheckboxes(this.value, reportDateInput.value);
        });
    }

    if (reportDateInput) {
        reportDateInput.addEventListener('change', function () {
            renderClassRoomCheckboxes(subjectSelect.value, this.value);
        });
    }

    // Classroom checkbox change handler
    if (classRoomSelectArea) {
        classRoomSelectArea.addEventListener('change', function (e) {
            if (e.target.classList.contains('report-class-room-checkbox')) {
                const subjectId = subjectSelect.value;
                const reportDate = reportDateInput.value;
                const checkedRooms = Array.from(classRoomSelectArea.querySelectorAll('.report-class-room-checkbox:checked')).map(cb => cb.value);
                renderClassPeriodsInputs(subjectId, checkedRooms, reportDate);
                renderRoomImageInputs(checkedRooms);

                const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
                const classValue = selectedOption.getAttribute('data-class') || '';
                const classRoomArr = checkedRooms.map(room => ({
                    class: classValue,
                    room: room.replace('ห้อง ', '')
                }));
                loadStudentsForAttendance(subjectId, classRoomArr);
            }
        });
    }

    // Attendance status configuration
    const attendanceStyleConfig = {
        present: { select: ['bg-emerald-50', 'text-emerald-700', 'border-emerald-200'], pill: ['bg-emerald-100', 'text-emerald-700'], label: '✅ มา' },
        absent: { select: ['bg-rose-50', 'text-rose-600', 'border-rose-200'], pill: ['bg-rose-100', 'text-rose-600'], label: '❌ ขาด' },
        late: { select: ['bg-amber-50', 'text-amber-600', 'border-amber-200'], pill: ['bg-amber-100', 'text-amber-600'], label: '⏰ สาย' },
        sick: { select: ['bg-sky-50', 'text-sky-600', 'border-sky-200'], pill: ['bg-sky-100', 'text-sky-600'], label: '🤒 ลาป่วย' },
        personal: { select: ['bg-indigo-50', 'text-indigo-600', 'border-indigo-200'], pill: ['bg-indigo-100', 'text-indigo-600'], label: '📝 ลากิจ' },
        activity: { select: ['bg-purple-50', 'text-purple-600', 'border-purple-200'], pill: ['bg-purple-100', 'text-purple-600'], label: '🎉 กิจกรรม' },
        truant: { select: ['bg-gray-50', 'text-gray-800', 'border-gray-200'], pill: ['bg-gray-100', 'text-gray-800'], label: '🚫 โดดเรียน' }
    };

    window.loadStudentsForAttendance = function (subjectId, selectedRooms) {
        const area = document.getElementById('studentAttendanceArea');
        area.innerHTML = '';
        if (!subjectId || !selectedRooms.length) {
            area.innerHTML = '<div class="text-gray-400 dark:text-gray-500 text-sm bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน 🎓</div>';
            return;
        }

        fetch('../controllers/StudentController.php?action=list&subject_id=' + encodeURIComponent(subjectId) + '&rooms=' + encodeURIComponent(JSON.stringify(selectedRooms)))
            .then(res => res.json())
            .then(data => {
                if (!data.length) {
                    area.innerHTML = '<div class="text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-700">ไม่พบนักเรียนในห้องที่เลือก ❌</div>';
                    return;
                }

                const groupByRoom = {};
                data.forEach(stu => {
                    if (!groupByRoom[stu.Stu_room]) groupByRoom[stu.Stu_room] = [];
                    groupByRoom[stu.Stu_room].push(stu);
                });

                let html = '';
                Object.keys(groupByRoom).forEach(room => {
                    html += `<div class="mb-6 glow-card border border-white/60 dark:border-white/10 p-6 rounded-2xl bg-white/85 dark:bg-gray-900/70 backdrop-blur-xl">
                        <div class="font-bold text-blue-700 dark:text-blue-300 mb-3 text-lg flex items-center gap-2">🏫 ห้อง ${room}<span class="text-xs font-normal text-slate-500 dark:text-slate-400">${groupByRoom[room].length} คน</span></div>
                        <table class="w-full text-sm table-auto border-collapse rounded-2xl overflow-hidden shadow-inner bg-white/90 dark:bg-gray-800/60">
                            <thead>
                                <tr class="bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-purple-500/10 text-left text-slate-900 dark:text-slate-100">
                                    <th class="p-4 border border-gray-200/70 dark:border-gray-700/60 font-bold">เลขที่</th>
                                    <th class="p-4 border border-gray-200/70 dark:border-gray-700/60 font-bold">ชื่อ - สกุล</th>
                                    <th class="p-4 border border-gray-200/70 dark:border-gray-700/60 font-bold">สถานะเข้าเรียน</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    groupByRoom[room].forEach((student, idx) => {
                        html += `
                            <tr class="border-b border-gray-200/80 dark:border-gray-700/60 hover:bg-indigo-50/60 dark:hover:bg-gray-800/70 transition-colors duration-200">
                                <td class="p-4 border border-gray-200/70 dark:border-gray-700/60 text-center text-slate-900 dark:text-white font-semibold">${idx + 1}</td>
                                <td class="p-4 border border-gray-200/70 dark:border-gray-700/60 text-slate-900 dark:text-white font-medium">${student.Stu_id} ${student.fullname}</td>
                                <td class="p-4 border border-gray-200/70 dark:border-gray-700/60">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <select name="attendance[${room}][${student.Stu_id}]" class="attendance-select w-44 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white/90 dark:bg-gray-800 text-slate-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-indigo-400/40 transition-all duration-200">
                                            <option value="present">มา</option>
                                            <option value="absent">ขาด</option>
                                            <option value="late">มาสาย</option>
                                            <option value="sick">ลาป่วย</option>
                                            <option value="personal">ลากิจ</option>
                                            <option value="activity">กิจกรรม</option>
                                            <option value="truant">โดดเรียน</option>
                                        </select>
                                        <span class="attendance-pill text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200 transition-all duration-200">✅ มา</span>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    html += `</tbody></table></div>`;
                });

                area.innerHTML = html;

                // Apply attendance select styles
                area.querySelectorAll('.attendance-select').forEach(sel => {
                    const pillEl = sel.parentElement.querySelector('.attendance-pill');
                    const resetClasses = () => {
                        Object.values(attendanceStyleConfig).forEach(cfg => {
                            cfg.select.forEach(cls => sel.classList.remove(cls));
                            if (pillEl) cfg.pill.forEach(cls => pillEl.classList.remove(cls));
                        });
                    };
                    const applyStyle = (status) => {
                        resetClasses();
                        const cfg = attendanceStyleConfig[status];
                        if (cfg) {
                            cfg.select.forEach(cls => sel.classList.add(cls));
                            if (pillEl) {
                                cfg.pill.forEach(cls => pillEl.classList.add(cls));
                                pillEl.textContent = cfg.label;
                            }
                        } else if (pillEl) {
                            pillEl.textContent = '—';
                        }
                    };

                    sel.addEventListener('change', function () { applyStyle(sel.value); });
                    applyStyle(sel.value);
                });
            });
    };

    // Show report detail modal
    window.showReportDetail = function (reportId) {
        fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
            .then(res => res.json())
            .then(report => {
                const countList = (s) => {
                    if (!s) return 0;
                    return s.split(/[,\n]/).map(x => x.trim()).filter(Boolean).length;
                };

                const attendanceBreakdown = [
                    { label: '❌ ขาดเรียน', value: (typeof report.absent_count !== 'undefined') ? Number(report.absent_count) : countList(report.absent_students), color: 'text-rose-500' },
                    { label: '🤒 ลาป่วย', value: (typeof report.sick_count !== 'undefined') ? Number(report.sick_count) : countList(report.sick_students), color: 'text-sky-500' },
                    { label: '📝 ลากิจ', value: (typeof report.personal_count !== 'undefined') ? Number(report.personal_count) : countList(report.personal_students), color: 'text-indigo-500' },
                    { label: '🎉 กิจกรรม', value: (typeof report.activity_count !== 'undefined') ? Number(report.activity_count) : countList(report.activity_students), color: 'text-purple-500' },
                    { label: '🚫 โดดเรียน', value: (typeof report.truant_count !== 'undefined') ? Number(report.truant_count) : countList(report.truant_students), color: 'text-gray-800' }
                ];

                const html = `
                    <div class="relative max-w-4xl mx-auto py-4 md:py-8">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 opacity-30 dark:opacity-20 blur-3xl rounded-3xl"></div>
                        <div class="relative bg-white/95 dark:bg-gray-900/90 backdrop-blur-2xl rounded-2xl md:rounded-3xl border border-white/40 dark:border-white/10 shadow-2xl overflow-hidden">
                            <div class="p-4 md:p-8 space-y-4 md:space-y-6 max-h-[80vh] overflow-y-auto">
                                
                                <!-- Header -->
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">รายงานการสอน</p>
                                        <h3 class="text-xl md:text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">📑 ${report.subject_name || '-'}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            📅 ${formatThaiDate(report.report_date)} · ⏰ คาบ ${report.period_start}-${report.period_end} · 🏫 ม.${report.level}/${report.class_room}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 px-4 py-2 rounded-xl bg-gradient-to-r from-lime-400 to-green-500 text-white font-bold flex items-center gap-2 shadow-lg text-sm">
                                        📋 แผนที่ ${report.plan_number || '-'}
                                    </div>
                                </div>
                                
                                <!-- Topic & Activity -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="rounded-xl p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800">
                                        <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide">📝 หัวข้อ/สาระการเรียนรู้</p>
                                        <p class="mt-2 text-sm md:text-base font-medium text-slate-800 dark:text-white">${report.plan_topic || '-'}</p>
                                    </div>
                                    <div class="rounded-xl p-4 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">
                                        <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">👨‍🏫 กิจกรรมการเรียนรู้</p>
                                        <p class="mt-2 text-sm md:text-base font-medium text-slate-800 dark:text-white">${report.activity || '-'}</p>
                                    </div>
                                </div>
                                
                                <!-- KPA Reflections -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="rounded-xl p-4 bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-800">
                                        <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide flex items-center gap-1">💡 ความรู้ (K)</p>
                                        <p class="mt-2 text-sm text-slate-700 dark:text-gray-300">${report.reflection_k || '-'}</p>
                                    </div>
                                    <div class="rounded-xl p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800">
                                        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide flex items-center gap-1">⚡ กระบวนการ (P)</p>
                                        <p class="mt-2 text-sm text-slate-700 dark:text-gray-300">${report.reflection_p || '-'}</p>
                                    </div>
                                    <div class="rounded-xl p-4 bg-pink-50 dark:bg-pink-900/30 border border-pink-100 dark:border-pink-800">
                                        <p class="text-xs font-semibold text-pink-600 dark:text-pink-400 uppercase tracking-wide flex items-center gap-1">❤️ เจตคติ (A)</p>
                                        <p class="mt-2 text-sm text-slate-700 dark:text-gray-300">${report.reflection_a || '-'}</p>
                                    </div>
                                </div>
                                
                                <!-- Problems & Suggestions -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="rounded-xl p-4 bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800">
                                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">⚠️ ปัญหา/อุปสรรค</p>
                                        <p class="mt-2 text-sm text-slate-700 dark:text-gray-300">${report.problems || '-'}</p>
                                    </div>
                                    <div class="rounded-xl p-4 bg-teal-50 dark:bg-teal-900/30 border border-teal-100 dark:border-teal-800">
                                        <p class="text-xs font-semibold text-teal-600 dark:text-teal-400 uppercase tracking-wide">💬 ข้อเสนอแนะ</p>
                                        <p class="mt-2 text-sm text-slate-700 dark:text-gray-300">${report.suggestions || '-'}</p>
                                    </div>
                                </div>
                                
                                <!-- Attendance -->
                                <div class="rounded-xl p-4 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-700/50 border border-slate-200 dark:border-slate-600">
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-3">📊 สถานะการเข้าเรียน</p>
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                                        ${attendanceBreakdown.map(item => `
                                            <div class="text-center p-2 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                                                <div class="text-xl font-black ${item.color}">${typeof item.value === 'number' ? item.value : (item.value || 0)}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">${item.label}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                
                                <!-- Images -->
                                ${(report.image1 || report.image2) ? `
                                <div class="rounded-xl p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-3">🖼️ รูปภาพประกอบ</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        ${report.image1 ? `<img src="../${report.image1}" class="w-full rounded-lg shadow-md" alt="รูปที่ 1">` : ''}
                                        ${report.image2 ? `<img src="../${report.image2}" class="w-full rounded-lg shadow-md" alt="รูปที่ 2">` : ''}
                                    </div>
                                </div>
                                ` : ''}
                                
                            </div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    html: html,
                    width: 900,
                    showCloseButton: true,
                    showConfirmButton: false,
                    background: 'transparent',
                    padding: 0
                });
            });
    };

    // Show attendance detail modal
    window.showAttendanceDetail = function (reportId) {
        fetch('../controllers/TeachingReportController.php?action=attendance_log&id=' + encodeURIComponent(reportId))
            .then(res => res.json())
            .then(logs => {
                const groups = { absent: [], sick: [], personal: [], activity: [], truant: [], late: [] };
                if (Array.isArray(logs)) {
                    logs.forEach(l => {
                        const s = (l.status || '').trim();
                        const label = l.student_name || l.fullname || l.Stu_no || l.student_id;
                        const entry = label ? `${label}` : `${l.student_id}`;
                        if (s === 'ขาดเรียน') groups.absent.push(entry);
                        else if (s === 'ลาป่วย') groups.sick.push(entry);
                        else if (s === 'ลากิจ') groups.personal.push(entry);
                        else if (s === 'เข้าร่วมกิจกรรม') groups.activity.push(entry);
                        else if (s === 'โดดเรียน') groups.truant.push(entry);
                        else if (s === 'มาสาย') groups.late.push(entry);
                    });
                }

                const categories = [
                    { title: 'ขาดเรียน', emoji: '❌', accent: 'rose', list: groups.absent },
                    { title: 'ลาป่วย', emoji: '🤒', accent: 'sky', list: groups.sick },
                    { title: 'ลากิจ', emoji: '📝', accent: 'indigo', list: groups.personal },
                    { title: 'กิจกรรม', emoji: '🎉', accent: 'purple', list: groups.activity },
                    { title: 'โดดเรียน', emoji: '🚫', accent: 'gray', list: groups.truant }
                ];

                const cards = categories.map(cat => `
                    <div class="glow-card rounded-2xl p-5 bg-gradient-to-r from-${cat.accent}-500/10 via-${cat.accent}-500/20 to-${cat.accent}-500/10 border">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 font-semibold text-${cat.accent}-500">${cat.emoji} ${cat.title}</div>
                            <div class="text-2xl font-black text-${cat.accent}-500">${cat.list.length}</div>
                        </div>
                        <div class="mt-3 text-sm max-h-48 overflow-auto pr-1">
                            ${cat.list.length ? `<ul class="space-y-1 ml-4 list-disc">${cat.list.map(item => `<li>${item}</li>`).join('')}</ul>` : '<div class="text-slate-400">ไม่มีข้อมูล</div>'}
                        </div>
                    </div>`).join('');

                const anyIssues = groups.absent.length + groups.sick.length + groups.personal.length + groups.activity.length + groups.truant.length;

                const html = `
                    <div class="relative max-w-5xl mx-auto">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 blur-3xl rounded-3xl"></div>
                        <div class="relative backdrop-blur-2xl rounded-3xl border p-6 md:p-8 space-y-6 bg-transparent">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Attendance Detail</p>
                                    <h3 class="text-2xl font-extrabold">📋 รายชื่อนักเรียน</h3>
                                </div>
                                <div class="text-sm text-slate-600">ยอดทั้งหมด : <strong class="text-indigo-600">${anyIssues}</strong></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">${cards}</div>
                        </div>
                    </div>
                `;

                const contentEl = document.getElementById('attendanceModalContent');
                contentEl.innerHTML = html;
                const modal = document.getElementById('attendanceModal');
                const inner = document.getElementById('attendanceModalInner');
                modal.classList.remove('hidden');
                setTimeout(() => inner.classList.add('show'), 10);
            });
    };

    // Modal logic
    const modalReport = document.getElementById('modalAddReport');
    const btnAddReport = document.getElementById('btnAddReport');
    const btnCloseReport = document.getElementById('closeModalAddReport');
    const btnCancelReport = document.getElementById('cancelAddReport');
    const formReport = document.getElementById('formAddReport');
    const attendanceModal = document.getElementById('attendanceModal');
    const closeAttendanceModal = document.getElementById('closeAttendanceModal');

    let editMode = false;
    let editReportId = null;
    let lastFormData = null;

    if (btnAddReport) {
        btnAddReport.addEventListener('click', () => {
            editMode = false;
            editReportId = null;
            document.getElementById('modalReportTitle').innerHTML = '➕ เพิ่มรายงานการสอน';

            // Reset form and all areas
            formReport.reset();
            classRoomSelectArea.innerHTML = '';
            document.getElementById('studentAttendanceArea').innerHTML = `
                <div class="text-gray-400 dark:text-gray-500 text-sm bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 text-center">
                    <i class="fas fa-users text-2xl mb-2"></i>
                    <p>เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน 🎓</p>
                </div>
            `;
            document.getElementById('roomImageInputsArea').innerHTML = '';

            modalReport.classList.remove('hidden');
            lastFormData = null;
        });
    }

    // FAB button for mobile
    const fabAddReport = document.getElementById('fabAddReport');
    if (fabAddReport) {
        fabAddReport.addEventListener('click', () => {
            if (btnAddReport) btnAddReport.click();
        });
    }

    if (btnCloseReport) {
        btnCloseReport.addEventListener('click', () => {
            modalReport.classList.add('hidden');
            formReport.reset();
            classRoomSelectArea.innerHTML = '';
            document.getElementById('studentAttendanceArea').innerHTML = '';
            document.getElementById('roomImageInputsArea').innerHTML = '';
            lastFormData = null;
            editMode = false;
            editReportId = null;
        });
    }

    if (btnCancelReport) {
        btnCancelReport.addEventListener('click', () => {
            modalReport.classList.add('hidden');
            formReport.reset();
            classRoomSelectArea.innerHTML = '';
            document.getElementById('studentAttendanceArea').innerHTML = '';
            document.getElementById('roomImageInputsArea').innerHTML = '';
            lastFormData = null;
            editMode = false;
            editReportId = null;
        });
    }

    if (closeAttendanceModal) {
        closeAttendanceModal.addEventListener('click', () => {
            const inner = document.getElementById('attendanceModalInner');
            if (inner) inner.classList.remove('show');
            setTimeout(() => attendanceModal.classList.add('hidden'), 220);
        });
    }

    // Close attendance modal on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && attendanceModal && !attendanceModal.classList.contains('hidden')) {
            closeAttendanceModal.click();
        }
    });

    // Store form data on input
    if (formReport) {
        formReport.addEventListener('input', function () {
            lastFormData = {};
            Array.from(formReport.elements).forEach(el => {
                if (el.name) lastFormData[el.name] = el.value;
            });
        });

        // Form submission
        formReport.addEventListener('submit', function (e) {
            e.preventDefault();
            submitReportForm();
        });
    }

    function submitReportForm() {
        const formData = new FormData(formReport);

        Swal.fire({
            title: 'กำลังบันทึกข้อมูล...',
            text: 'กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const subjectId = formData.get('subject_id');
        const reportDate = formData.get('report_date');
        const checkedRooms = Array.from(document.querySelectorAll('.report-class-room-checkbox:checked')).map(cb => cb.value);

        const checkedPeriods = {};
        checkedRooms.forEach(room => {
            const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
            checkedPeriods[room] = Array.from(document.querySelectorAll(`input[name="periods[${key}][]"]:checked`)).map(cb => {
                const [start, end, day] = cb.value.split('|');
                return { period_start: start, period_end: end, day_of_week: day };
            });
        });

        // Validation
        if (!subjectId) { Swal.close(); Swal.fire('ผิดพลาด', 'กรุณาเลือกวิชา', 'error'); return; }
        if (!reportDate) { Swal.close(); Swal.fire('ผิดพลาด', 'กรุณาเลือกวันที่', 'error'); return; }
        if (!checkedRooms.length) { Swal.close(); Swal.fire('ผิดพลาด', 'กรุณาเลือกห้องเรียนอย่างน้อย 1 ห้อง', 'error'); return; }
        for (const room of checkedRooms) {
            if (!checkedPeriods[room] || checkedPeriods[room].length === 0) {
                Swal.close();
                Swal.fire('ผิดพลาด', `กรุณาเลือกอย่างน้อย 1 คาบสำหรับ ${room}`, 'error');
                return;
            }
        }

        let attendanceLogs = [];
        document.querySelectorAll('[name^="attendance["]').forEach(input => {
            const match = input.name.match(/^attendance\[(.+?)\]\[(.+?)\]$/);
            if (!match) return;
            const room = match[1];
            const stuId = match[2];
            let status = input.value;
            const map = {
                present: 'มาเรียน', late: 'มาสาย', sick: 'ลาป่วย',
                personal: 'ลากิจ', activity: 'เข้าร่วมกิจกรรม',
                absent: 'ขาดเรียน', truant: 'โดดเรียน'
            };
            attendanceLogs.push({ student_id: stuId, status: map[status] || status, class_room: room });
        });

        let rows = [];
        checkedRooms.forEach(room => {
            (checkedPeriods[room] || []).forEach(period => {
                const classRoom = (room.replace('ห้อง ', '') + '').trim();
                rows.push({
                    report_date: reportDate,
                    subject_id: subjectId,
                    class_room: classRoom,
                    period_start: period.period_start.trim(),
                    period_end: period.period_end.trim(),
                    plan_number: formData.get('plan_number'),
                    plan_topic: formData.get('plan_topic'),
                    activity: formData.get('activity'),
                    absent_students: '',
                    reflection_k: formData.get('reflection_k'),
                    reflection_p: formData.get('reflection_p'),
                    reflection_a: formData.get('reflection_a'),
                    problems: formData.get('problems'),
                    suggestions: formData.get('suggestions'),
                    image1: null,
                    image2: null,
                    teacher_id: window.TEACHER_ID || '',
                    created_at: null
                });
            });
        });

        // Upload images and save
        uploadImagesAndSave(formData, checkedRooms, rows, attendanceLogs);
    }

    function uploadImagesAndSave(formData, checkedRooms, rows, attendanceLogs) {
        const uploadImages = () => {
            return new Promise((resolve, reject) => {
                const imagesByRoom = {};
                checkedRooms.forEach(room => {
                    const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
                    imagesByRoom[room] = {
                        image1: formData.get(`image1_${key}`),
                        image2: formData.get(`image2_${key}`)
                    };
                });

                const uploadPromises = checkedRooms.map(room => {
                    const files = imagesByRoom[room];
                    const isValid = file => file && file instanceof File && file.size > 0;
                    if (!isValid(files.image1) && !isValid(files.image2)) {
                        return Promise.resolve({ room, image1: '', image2: '' });
                    }
                    const uploadData = new FormData();
                    if (isValid(files.image1)) uploadData.append('image1', files.image1);
                    if (isValid(files.image2)) uploadData.append('image2', files.image2);

                    return fetch('../controllers/TeachingReportController.php?action=upload_images', {
                        method: 'POST',
                        body: uploadData
                    })
                        .then(res => res.json())
                        .then(result => ({
                            room,
                            image1: result.image1 ? 'uploads/' + result.image1 : '',
                            image2: result.image2 ? 'uploads/' + result.image2 : ''
                        }));
                });

                Promise.all(uploadPromises)
                    .then(results => {
                        const imagesMap = {};
                        results.forEach(r => imagesMap[r.room] = { image1: r.image1, image2: r.image2 });
                        resolve(imagesMap);
                    })
                    .catch(reject);
            });
        };

        uploadImages().then(imagesMap => {
            let url = '../controllers/TeachingReportController.php?action=create';
            let method = 'POST';
            let body = {
                rows: rows.map(row => {
                    let roomKey = row.class_room;
                    if (!imagesMap[roomKey] && imagesMap['ห้อง ' + roomKey]) {
                        roomKey = 'ห้อง ' + roomKey;
                    }
                    return {
                        ...row,
                        image1: imagesMap[roomKey]?.image1 || null,
                        image2: imagesMap[roomKey]?.image2 || null
                    };
                }),
                attendance_logs: attendanceLogs
            };
            if (editMode && editReportId) {
                url = '../controllers/TeachingReportController.php?action=update';
                body.id = editReportId;
            }

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            })
                .then(res => res.json())
                .then(result => {
                    Swal.close();
                    if (result.success) {
                        Swal.fire('สำเร็จ', editMode ? 'แก้ไขรายงานเรียบร้อยแล้ว' : 'บันทึกรายงานเรียบร้อยแล้ว', 'success');
                        modalReport.classList.add('hidden');
                        formReport.reset();
                        lastFormData = null;
                        loadReports();
                    } else {
                        Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกรายงานได้', 'error');
                    }
                    editMode = false;
                    editReportId = null;
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                });
        });
    }

    // Edit report modal
    window.openEditReportModal = function (reportId) {
        fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
            .then(res => res.json())
            .then(report => {
                editMode = true;
                editReportId = reportId;
                document.getElementById('modalReportTitle').innerHTML = '✏️ แก้ไขรายงานการสอน';

                // Reset form first
                formReport.reset();
                classRoomSelectArea.innerHTML = '';
                document.getElementById('studentAttendanceArea').innerHTML = '';
                document.getElementById('roomImageInputsArea').innerHTML = '';

                modalReport.classList.remove('hidden');

                // Fill form with existing data
                formReport.report_date.value = report.report_date;
                formReport.subject_id.value = report.subject_id;

                // Trigger subject change to load class rooms
                formReport.subject_id.dispatchEvent(new Event('change'));

                // Wait for class rooms to load, then select the correct one and load attendance
                setTimeout(() => {
                    const classRoom = report.class_room;
                    document.querySelectorAll('.report-class-room-checkbox').forEach(cb => {
                        const cbRoom = cb.value.replace('ห้อง ', '');
                        cb.checked = (cbRoom === classRoom || cb.value === classRoom || cbRoom === classRoom.replace('ห้อง ', ''));
                    });

                    // Trigger change event to load periods
                    document.querySelectorAll('.report-class-room-checkbox').forEach(cb => {
                        if (cb.checked) {
                            cb.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });

                    // Wait for periods to load, then select correct period
                    setTimeout(() => {
                        document.querySelectorAll(`input[name^="periods["]`).forEach(cb => {
                            const [start, end] = cb.value.split('|');
                            if (start === String(report.period_start) && end === String(report.period_end)) {
                                cb.checked = true;
                            }
                        });

                        // After attendance area is loaded, fetch and populate attendance data
                        setTimeout(() => {
                            fetch('../controllers/TeachingReportController.php?action=attendance_log&id=' + encodeURIComponent(reportId))
                                .then(res => res.json())
                                .then(logs => {
                                    if (Array.isArray(logs) && logs.length > 0) {
                                        // Map status from Thai to JS value
                                        const statusMap = {
                                            'มาเรียน': 'present',
                                            'ขาดเรียน': 'absent',
                                            'มาสาย': 'late',
                                            'ลาป่วย': 'sick',
                                            'ลากิจ': 'personal',
                                            'เข้าร่วมกิจกรรม': 'activity',
                                            'โดดเรียน': 'truant'
                                        };

                                        logs.forEach(log => {
                                            const studentId = log.student_id;
                                            const status = statusMap[log.status] || 'present';

                                            // Find the select element for this student
                                            document.querySelectorAll(`[name^="attendance["]`).forEach(sel => {
                                                const match = sel.name.match(/^attendance\[.+?\]\[(.+?)\]$/);
                                                if (match && match[1] === String(studentId)) {
                                                    sel.value = status;
                                                    sel.dispatchEvent(new Event('change'));
                                                }
                                            });
                                        });
                                    }
                                });
                        }, 500);
                    }, 300);
                }, 300);

                // Fill other form fields
                formReport.plan_number.value = report.plan_number || '';
                formReport.plan_topic.value = report.plan_topic || '';
                formReport.activity.value = report.activity || '';
                formReport.reflection_k.value = report.reflection_k || '';
                formReport.reflection_p.value = report.reflection_p || '';
                formReport.reflection_a.value = report.reflection_a || '';
                formReport.problems.value = report.problems || '';
                formReport.suggestions.value = report.suggestions || '';

                lastFormData = {};
                Array.from(formReport.elements).forEach(el => {
                    if (el.name) lastFormData[el.name] = el.value;
                });
            });
    };

    // Initialize: call functions after all definitions are complete
    loadReports();
    loadSubjectsForReport();
});
