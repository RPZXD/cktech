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
        const termFilter = document.getElementById('termFilterSelect');
        let url = '../controllers/TeachingReportController.php?action=list';
        if (termFilter && termFilter.value) {
            const parts = termFilter.value.split('/');
            if (parts.length === 2) {
                url += `&term=${encodeURIComponent(parts[0])}&pee=${encodeURIComponent(parts[1])}`;
            }
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('reportTableBody');
                const mobileContainer = document.getElementById('mobileReportCards');

                // Destroy DataTable before changing HTML to avoid Datatable errors
                if ($.fn.DataTable.isDataTable('.min-w-full')) {
                    $('.min-w-full').DataTable().destroy();
                }

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

    window.loadTermsFilter = function () {
        fetch('../controllers/TeachingReportController.php?action=getTerms')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('termFilterSelect');
                if (!select) return;
                
                select.innerHTML = '';
                
                const terms = data.terms || [];
                const currentTerm = data.current_term;
                const currentPee = data.current_pee;
                
                // We should make sure the current active term is in the list
                if (currentTerm && currentPee) {
                    const hasCurrentTerm = terms.some(t => t.term == currentTerm && t.pee == currentPee);
                    if (!hasCurrentTerm) {
                        // Prepend current active term if it doesn't have any reports yet
                        terms.unshift({ term: currentTerm, pee: currentPee });
                    }
                }
                
                terms.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = `${t.term}/${t.pee}`;
                    opt.textContent = `ภาคเรียนที่ ${t.term}/${t.pee}`;
                    if (t.term == currentTerm && t.pee == currentPee) {
                        opt.selected = true;
                    }
                    select.appendChild(opt);
                });
                
                // Add an option to see all reports
                const optAll = document.createElement('option');
                optAll.value = '';
                optAll.textContent = 'ดูทั้งหมด';
                select.appendChild(optAll);
                
                // After populating, load reports for the selected option (which defaults to current term)
                loadReports();
            })
            .catch(err => {
                console.error('Failed to load terms:', err);
                // Fallback to load reports directly
                loadReports();
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
    const termFilterSelect = document.getElementById('termFilterSelect');
    let subjectClassRooms = {};

    window.loadSubjectsForReport = function () {
        const teacherId = window.TEACHER_ID || null;
        const params = new URLSearchParams({ action: 'list', onlyOpen: 1 });
        if (teacherId) params.append('teacherId', teacherId);

        fetch('../controllers/SubjectController.php?' + params.toString(), { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('subjectSelect');
                const batchSelect = document.getElementById('batchSubjectSelect');
                if (select) select.innerHTML = `<option value="">-- เลือกวิชา --</option>`;
                if (batchSelect) batchSelect.innerHTML = `<option value="">-- เลือกวิชา --</option>`;
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
                    if (select) select.appendChild(opt);
                    if (batchSelect) {
                        const batchOpt = opt.cloneNode(true);
                        batchSelect.appendChild(batchOpt);
                    }
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

    if (termFilterSelect) {
        termFilterSelect.addEventListener('change', function () {
            loadReports();
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
                loadStudentsForAttendance(subjectId, classRoomArr, reportDate);
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

    window.loadStudentsForAttendance = function (subjectId, selectedRooms, reportDate = '') {
        const area = document.getElementById('studentAttendanceArea');
        area.innerHTML = '';
        if (!subjectId || !selectedRooms.length) {
            area.innerHTML = '<div class="text-gray-400 dark:text-gray-500 text-sm bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน 🎓</div>';
            return;
        }

        let url = '../controllers/StudentController.php?action=list&subject_id=' + encodeURIComponent(subjectId) + '&rooms=' + encodeURIComponent(JSON.stringify(selectedRooms));
        if (reportDate) {
            url += '&date=' + encodeURIComponent(reportDate);
        }

        fetch(url)
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
                        let defaultVal = 'present';
                        const careStatus = String(student.care_attendance_status || '');
                        if (careStatus === '1') defaultVal = 'present';
                        else if (careStatus === '2') defaultVal = 'absent';
                        else if (careStatus === '3') defaultVal = 'present'; // มาสายจากระบบดูแล ให้นับเป็นมาเรียน
                        else if (careStatus === '4') defaultVal = 'sick';
                        else if (careStatus === '5') defaultVal = 'personal';
                        else if (careStatus === '6') defaultVal = 'activity';

                        html += `
                            <tr class="border-b border-gray-200/80 dark:border-gray-700/60 hover:bg-indigo-50/60 dark:hover:bg-gray-800/70 transition-colors duration-200">
                                <td class="p-4 border border-gray-200/70 dark:border-gray-700/60 text-center text-slate-900 dark:text-white font-semibold">${idx + 1}</td>
                                <td class="p-4 border border-gray-200/70 dark:border-gray-700/60 text-slate-900 dark:text-white font-medium">${student.Stu_id} ${student.fullname}</td>
                                <td class="p-4 border border-gray-200/70 dark:border-gray-700/60">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <select name="attendance[${room}][${student.Stu_id}]" class="attendance-select w-44 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white/90 dark:bg-gray-800 text-slate-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-indigo-400/40 transition-all duration-200">
                                            <option value="present" ${defaultVal === 'present' ? 'selected' : ''}>มา</option>
                                            <option value="absent" ${defaultVal === 'absent' ? 'selected' : ''}>ขาด</option>
                                            <option value="late" ${defaultVal === 'late' ? 'selected' : ''}>มาสาย</option>
                                            <option value="sick" ${defaultVal === 'sick' ? 'selected' : ''}>ลาป่วย</option>
                                            <option value="personal" ${defaultVal === 'personal' ? 'selected' : ''}>ลากิจ</option>
                                            <option value="activity" ${defaultVal === 'activity' ? 'selected' : ''}>กิจกรรม</option>
                                            <option value="truant" ${defaultVal === 'truant' ? 'selected' : ''}>โดดเรียน</option>
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

    // --- Gemini AI Helper Integration ---
    const btnGeminiSettings = document.getElementById('btnGeminiSettings');
    const btnGeminiGenerate = document.getElementById('btnGeminiGenerate');

    if (btnGeminiSettings) {
        btnGeminiSettings.addEventListener('click', function () {
            // Fetch current masked key
            fetch('../controllers/GeminiController.php?action=get_key')
                .then(res => res.json())
                .then(res => {
                    let placeholderText = 'วาง Gemini API Key ของคุณที่นี่ (AIzaSy...)';
                    if (res.success && res.has_key) {
                        placeholderText = 'มี Key บันทึกอยู่แล้ว: ' + res.masked_key;
                    }

                    Swal.fire({
                        title: '🔑 ตั้งค่า Gemini API Key ส่วนตัว',
                        html: `
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-left">
                                เพื่อประหยัด Token และแยกการใช้งานของครูแต่ละท่าน คุณครูสามารถสมัครและรับ API Key ฟรีได้จาก Google AI Studio
                                <br>
                                <a href="https://aistudio.google.com/" target="_blank" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline inline-flex items-center gap-1 mt-2">
                                    🌐 คลิกที่นี่เพื่อขอรับ Key ฟรีจาก Google AI Studio
                                </a>
                            </p>
                            <input type="password" id="geminiApiKeyInput" class="swal2-input w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="${placeholderText}">
                            <p class="text-xs text-rose-500 mt-2 text-left">* หากปล่อยว่างและกดยืนยัน จะเป็นการลบ Key ที่บันทึกไว้ออก</p>
                        `,
                        showCancelButton: true,
                        confirmButtonText: '💾 บันทึก',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#4f46e5',
                        preConfirm: () => {
                            const keyVal = document.getElementById('geminiApiKeyInput').value;
                            return keyVal;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.showLoading();
                            fetch('../controllers/GeminiController.php?action=save_key', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ gemini_api_key: result.value })
                            })
                            .then(res => res.json())
                            .then(saveRes => {
                                if (saveRes.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'บันทึก Key สำเร็จ!',
                                        text: 'คุณสามารถใช้ฟีเจอร์ช่วยเขียนรายงานด้วย AI ได้ทันที',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'เกิดข้อผิดพลาด',
                                        text: saveRes.error || 'ไม่สามารถบันทึก Key ได้'
                                    });
                                }
                            })
                            .catch(err => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด',
                                    text: 'การเชื่อมต่อเครือข่ายล้มเหลว'
                                });
                            });
                        }
                    });
                });
        });
    }

    if (btnGeminiGenerate) {
        btnGeminiGenerate.addEventListener('click', function () {
            const planTopicInput = document.getElementById('planTopic');
            const planTopic = planTopicInput ? planTopicInput.value.trim() : '';
            const subjectSelect = document.querySelector('[name="subject_id"]');
            
            if (!subjectSelect || !subjectSelect.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'คำเตือน',
                    text: 'กรุณาเลือกวิชาที่สอนก่อนใช้บริการ AI'
                });
                return;
            }

            if (!planTopic) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกหัวข้อก่อน',
                    text: 'กรุณาระบุ "หัวข้อ/สาระการเรียนรู้" เพื่อให้ AI ใช้เป็นข้อมูลในการวิเคราะห์และแนะนำเนื้อหา'
                });
                return;
            }

            const subjectName = subjectSelect.options[subjectSelect.selectedIndex].text;

            Swal.fire({
                title: '🔮 กำลังส่งให้ Gemini AI วิเคราะห์...',
                html: '<span class="text-sm text-gray-500">ระบบกำลังช่วยคุณเขียนกิจกรรมและสรุปการสะท้อนคิด KPA กรุณารอสักครู่ (ประมาณ 3-5 วินาที)...</span>',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../controllers/GeminiController.php?action=generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    subject_name: subjectName,
                    plan_topic: planTopic
                })
            })
            .then(res => res.json())
            .then(res => {
                Swal.close();
                if (res.success) {
                    const data = res.data;
                    
                    // Fields to auto-fill
                    const fields = {
                        activity: data.activity || '',
                        reflection_k: data.reflection_k || '',
                        reflection_p: data.reflection_p || '',
                        reflection_a: data.reflection_a || '',
                        problems: data.problems || '',
                        suggestions: data.suggestions || ''
                    };

                    // Populate fields with dynamic highlight
                    Object.keys(fields).forEach(key => {
                        const inputEl = document.querySelector(`[name="${key}"]`);
                        if (inputEl) {
                            inputEl.value = fields[key];
                            
                            // Visual highlight transition
                            inputEl.classList.add('border-indigo-500', 'ring-4', 'ring-indigo-500/20');
                            setTimeout(() => {
                                inputEl.classList.remove('border-indigo-500', 'ring-4', 'ring-indigo-500/20');
                            }, 2000);
                        }
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'วิเคราะห์สำเร็จ! ✨',
                        text: 'ระบบกรอกข้อมูลกิจกรรมการเรียนรู้, ผลการสะท้อนคิด KPA, ปัญหา และข้อเสนอแนะ ให้เรียบร้อยแล้ว!',
                        timer: 3000,
                        showConfirmButton: true,
                        confirmButtonColor: '#4f46e5'
                    });

                } else if (res.needs_key) {
                    Swal.fire({
                        icon: 'info',
                        title: '🔑 ต้องตั้งค่า API Key ก่อนใช้บริการ',
                        text: res.error || 'กรุณาตั้งค่า Gemini API Key ส่วนตัวก่อนเพื่อใช้งานฟีเจอร์นี้',
                        showCancelButton: true,
                        confirmButtonText: '🛠️ ตั้งค่าตอนนี้',
                        cancelButtonText: 'ไว้ทีหลัง',
                        confirmButtonColor: '#4f46e5'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (btnGeminiSettings) btnGeminiSettings.click();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการวิเคราะห์',
                        text: res.error || 'โปรดตรวจสอบความถูกต้องของ API Key หรือข้อตกลงและลองอีกครั้ง'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'เชื่อมต่อ AI ล้มเหลว',
                    text: 'กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตของคุณ'
                });
            });
        });
    }

    // --- Batch Retroactive Report Logic ---
    const btnBatchReport = document.getElementById('btnBatchReport');
    const modalBatchReport = document.getElementById('modalBatchReport');
    const closeModalBatchReport = document.getElementById('closeModalBatchReport');
    const cancelBatchReport = document.getElementById('cancelBatchReport');
    const btnPreviewBatch = document.getElementById('btnPreviewBatch');
    const btnSubmitBatch = document.getElementById('btnSubmitBatch');
    const batchSubjectSelect = document.getElementById('batchSubjectSelect');
    const batchStartDate = document.getElementById('batchStartDate');
    const batchEndDate = document.getElementById('batchEndDate');
    const batchPreviewArea = document.getElementById('batchPreviewArea');
    const batchSummaryCount = document.getElementById('batchSummaryCount');

    let calculatedBatchSessions = [];

    // Helper: format YYYY-MM-DD
    function toISODate(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    if (btnBatchReport) {
        btnBatchReport.addEventListener('click', function () {
            const today = new Date();
            const todayStr = toISODate(today);

            // Default start date: 30 days ago or start of current semester
            const pastDate = new Date();
            pastDate.setDate(today.getDate() - 30);
            const pastStr = toISODate(pastDate);

            if (batchStartDate) batchStartDate.value = pastStr;
            if (batchEndDate) batchEndDate.value = todayStr;
            if (batchPreviewArea) batchPreviewArea.innerHTML = '';
            if (batchSummaryCount) batchSummaryCount.textContent = '';
            calculatedBatchSessions = [];

            if (modalBatchReport) modalBatchReport.classList.remove('hidden');
        });
    }

    if (closeModalBatchReport) {
        closeModalBatchReport.addEventListener('click', () => {
            if (modalBatchReport) modalBatchReport.classList.add('hidden');
        });
    }

    if (cancelBatchReport) {
        cancelBatchReport.addEventListener('click', () => {
            if (modalBatchReport) modalBatchReport.classList.add('hidden');
        });
    }

    // Function to calculate and preview sessions
    async function calculateBatchSessions() {
        const subjectId = batchSubjectSelect.value;
        const startDateVal = batchStartDate.value;
        const endDateVal = batchEndDate.value;

        if (!subjectId) {
            Swal.fire('แจ้งเตือน', 'กรุณาเลือกวิชาที่ต้องการสร้างรายงาน', 'warning');
            return null;
        }
        if (!startDateVal || !endDateVal) {
            Swal.fire('แจ้งเตือน', 'กรุณาระบุช่วงวันที่ให้ครบถ้วน', 'warning');
            return null;
        }
        if (startDateVal > endDateVal) {
            Swal.fire('แจ้งเตือน', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด', 'warning');
            return null;
        }

        const classPeriods = subjectClassRooms[subjectId] || [];
        if (!classPeriods.length) {
            Swal.fire('ไม่พบตารางสอน', 'วิชานี้ยังไม่มีการตั้งค่าตารางสอนและคาบเรียนในระบบ', 'warning');
            return null;
        }

        // Show loading indicator
        batchPreviewArea.innerHTML = `
            <div class="text-center py-6 text-indigo-600 dark:text-indigo-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p class="text-sm font-medium">กำลังคำนวณคาบสอนและตรวจสอบรายงานเดิม...</p>
            </div>
        `;

        try {
            // Fetch existing reports to avoid duplicate sessions on the same date/period/room
            const existingRes = await fetch('../controllers/TeachingReportController.php?action=list');
            const existingReports = await existingRes.json();
            const existingMap = new Set();
            (Array.isArray(existingReports) ? existingReports : []).forEach(r => {
                if (String(r.subject_id) === String(subjectId)) {
                    const roomStr = String(r.class_room || '').trim();
                    const periodStr = String(r.period_start || '').trim();
                    const key = `${r.report_date}_${roomStr}_${periodStr}`;
                    existingMap.add(key);
                }
            });

            // Map Thai day string to Day Index (0: อาทิตย์, 1: จันทร์, ..., 6: เสาร์)
            const thaiDayMap = { 'อาทิตย์': 0, 'จันทร์': 1, 'อังคาร': 2, 'พุธ': 3, 'พฤหัสบดี': 4, 'ศุกร์': 5, 'เสาร์': 6 };

            const dayPeriodsMap = {};
            classPeriods.forEach(p => {
                const dayIdx = thaiDayMap[p.day_of_week];
                if (dayIdx !== undefined) {
                    if (!dayPeriodsMap[dayIdx]) dayPeriodsMap[dayIdx] = [];
                    dayPeriodsMap[dayIdx].push(p);
                }
            });

            // Generate dates between startDate and endDate
            const start = new Date(startDateVal);
            const end = new Date(endDateVal);
            const sessionsToCreate = [];
            const skippedSessions = [];

            for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                const currentDayIdx = d.getDay();
                const matchedPeriods = dayPeriodsMap[currentDayIdx];

                if (matchedPeriods && matchedPeriods.length > 0) {
                    const dateStr = toISODate(d);
                    matchedPeriods.forEach(p => {
                        const roomClean = String(p.class_room || '').replace('ห้อง ', '').trim();
                        const periodStartClean = String(p.period_start || '').trim();
                        const checkKey = `${dateStr}_${roomClean}_${periodStartClean}`;

                        const sessionItem = {
                            date: dateStr,
                            dayName: p.day_of_week,
                            room: roomClean,
                            period_start: periodStartClean,
                            period_end: String(p.period_end || '').trim()
                        };

                        if (existingMap.has(checkKey)) {
                            skippedSessions.push(sessionItem);
                        } else {
                            sessionsToCreate.push(sessionItem);
                        }
                    });
                }
            }

            calculatedBatchSessions = sessionsToCreate;

            // Render Preview
            if (sessionsToCreate.length === 0) {
                batchPreviewArea.innerHTML = `
                    <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 text-amber-800 dark:text-amber-200 text-sm">
                        <div class="font-bold flex items-center gap-2 mb-1">
                            <span>⚠️</span> ไม่พบคาบสอนที่ต้องสร้างใหม่
                        </div>
                        <p>${skippedSessions.length > 0 ? `พบรายงานที่มีอยู่แล้วทั้งหมด ${skippedSessions.length} คาบ ในช่วงวันดังกล่าว` : 'ไม่มีคาบสอนตามตารางในวันและช่วงเวลาที่เลือก'}</p>
                    </div>
                `;
                batchSummaryCount.textContent = 'ไม่มีคาบสอนที่ต้องสร้าง';
            } else {
                let html = `
                    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 mb-3">
                        <div class="font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                            <span>✅</span> พบคาบสอนที่พร้อมสร้างรายงานจำนวน ${sessionsToCreate.length} คาบ
                            ${skippedSessions.length > 0 ? `<span class="text-xs font-normal text-gray-500 dark:text-gray-400">(ข้ามรายงานเดิมที่มีแล้ว ${skippedSessions.length} คาบ)</span>` : ''}
                        </div>
                    </div>
                    <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1 border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-gray-50/50 dark:bg-gray-800/50 text-xs">
                `;

                sessionsToCreate.forEach((s, idx) => {
                    html += `
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">#${idx + 1}</span>
                                <span class="font-medium text-slate-800 dark:text-gray-200">วัน${s.dayName} ที่ ${formatThaiDate(s.date)}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-semibold">ม.${s.room}</span>
                                <span class="px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-semibold">คาบ ${s.period_start}-${s.period_end}</span>
                            </div>
                        </div>
                    `;
                });

                html += `</div>`;
                batchPreviewArea.innerHTML = html;
                batchSummaryCount.textContent = `พร้อมสร้าง ${sessionsToCreate.length} คาบ`;
            }

            return sessionsToCreate;
        } catch (err) {
            console.error(err);
            batchPreviewArea.innerHTML = `
                <div class="p-4 rounded-xl bg-red-50 text-red-600 text-sm">
                    เกิดข้อผิดพลาดในการตรวจสอบคาบสอน กรุณาลองใหม่อีกครั้ง
                </div>
            `;
            return null;
        }
    }

    if (btnPreviewBatch) {
        btnPreviewBatch.addEventListener('click', () => {
            calculateBatchSessions();
        });
    }

    // Helper: Get ISO Week Key (e.g. 2026-W35)
    function getWeekKey(dateStr) {
        const d = new Date(dateStr);
        const dayNum = d.getUTCDay() || 7;
        d.setUTCDate(d.getUTCDate() + 4 - dayNum);
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        return `${d.getUTCFullYear()}-W${String(weekNo).padStart(2, '0')}`;
    }

    let batchAiCurriculumPlan = [];

    const btnBatchAiGenerate = document.getElementById('btnBatchAiGenerate');
    if (btnBatchAiGenerate) {
        btnBatchAiGenerate.addEventListener('click', async () => {
            const subjectId = batchSubjectSelect.value;
            const subjectText = batchSubjectSelect.options[batchSubjectSelect.selectedIndex]?.text || '';
            if (!subjectId) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกวิชาก่อนใช้งาน AI', 'warning');
                return;
            }

            const sessions = await calculateBatchSessions();
            if (!sessions || sessions.length === 0) {
                Swal.fire('ไม่มีคาบสอน', 'กรุณาเลือกช่วงวันที่ที่มีคาบสอน', 'info');
                return;
            }

            // Count unique weeks
            const uniqueWeeks = Array.from(new Set(sessions.map(s => getWeekKey(s.date))));
            const weeksCount = uniqueWeeks.length;

            Swal.fire({
                title: '✨ AI กำลังวิเคราะห์และจัดทำหลักสูตร...',
                html: `กำลังจัดโครงสร้างเนื้อหาสำหรับ <strong>วิชา ${subjectText}</strong><br>จำนวน <strong>${weeksCount} สัปดาห์</strong> ไม่ซ้ำกัน`,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch('../controllers/GeminiController.php?action=generate_curriculum_batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        subject_name: subjectText,
                        weeks_count: weeksCount
                    })
                });

                const data = await res.json();
                if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                    batchAiCurriculumPlan = data.data;
                    const sample = batchAiCurriculumPlan[0];
                    if (document.getElementById('batchPlanTopic')) document.getElementById('batchPlanTopic').value = sample.plan_topic || '';
                    if (document.getElementById('batchActivity')) document.getElementById('batchActivity').value = sample.activity || '';
                    if (document.querySelector('[name="batch_reflection_k"]')) document.querySelector('[name="batch_reflection_k"]').value = sample.reflection_k || '';
                    if (document.querySelector('[name="batch_reflection_p"]')) document.querySelector('[name="batch_reflection_p"]').value = sample.reflection_p || '';
                    if (document.querySelector('[name="batch_reflection_a"]')) document.querySelector('[name="batch_reflection_a"]').value = sample.reflection_a || '';

                    Swal.fire({
                        icon: 'success',
                        title: '✨ จัดทำแผนสำเร็จ!',
                        html: `AI ได้จัดทำแผนการสอน <strong>${batchAiCurriculumPlan.length} สัปดาห์</strong> ไม่ซ้ำกันเรียบร้อยแล้ว<br><span class="text-xs text-gray-500">คาบในสัปดาห์เดียวกัน (แม้คนละห้อง) จะใช้เนื้อหาตรงกัน ส่วนต่างสัปดาห์เนื้อหาจะไม่ซ้ำกัน</span>`,
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    if (data.needs_key) {
                        Swal.fire({
                            icon: 'info',
                            title: '🔑 ต้องตั้งค่า API Key',
                            text: data.error || 'กรุณาตั้งค่า Gemini API Key ก่อน',
                            showCancelButton: true,
                            confirmButtonText: 'ตั้งค่า API Key'
                        }).then(r => {
                            if (r.isConfirmed) {
                                const btnGeminiSettings = document.getElementById('btnGeminiSettings');
                                if (btnGeminiSettings) btnGeminiSettings.click();
                            }
                        });
                    } else {
                        Swal.fire('ข้อผิดพลาด', data.error || 'ไม่สามารถสร้างแผนจาก AI ได้', 'error');
                    }
                }
            } catch (err) {
                console.error(err);
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อ AI ได้ กรุณาลองใหม่อีกครั้ง', 'error');
            }
        });
    }

    // Submit batch creation with weekly distinct contents
    if (btnSubmitBatch) {
        btnSubmitBatch.addEventListener('click', async () => {
            const sessions = await calculateBatchSessions();
            if (!sessions || sessions.length === 0) {
                Swal.fire('ไม่มีข้อมูล', 'ไม่มีคาบสอนที่ต้องสร้างรายงาน', 'info');
                return;
            }

            const subjectId = batchSubjectSelect.value;
            const subjectText = batchSubjectSelect.options[batchSubjectSelect.selectedIndex]?.text || '';
            const fallbackPlanTopic = document.getElementById('batchPlanTopic')?.value || 'การจัดการเรียนรู้ตามหลักสูตร';
            const fallbackActivity = document.getElementById('batchActivity')?.value || 'จัดกิจกรรมการเรียนรู้ บรรยาย และฝึกปฏิบัติ';
            const fallbackReflectionK = document.querySelector('[name="batch_reflection_k"]')?.value || 'ผู้เรียนมีความรู้ความเข้าใจตามเนื้อหา';
            const fallbackReflectionP = document.querySelector('[name="batch_reflection_p"]')?.value || 'ผู้เรียนได้ฝึกปฏิบัติตามกิจกรรม';
            const fallbackReflectionA = document.querySelector('[name="batch_reflection_a"]')?.value || 'ผู้เรียนมีความกระตือรือร้นและตั้งใจเรียน';

            // Group sessions by ISO week key (e.g. "2026-W30") to map same week -> same content, different week -> different content
            const weekKeys = Array.from(new Set(sessions.map(s => getWeekKey(s.date)))).sort();
            const weekIndexMap = {};
            weekKeys.forEach((wk, idx) => {
                weekIndexMap[wk] = idx + 1; // 1-based week index
            });

            const confirmRes = await Swal.fire({
                title: '⚡ ยืนยันสร้างรายงานย้อนหลัง?',
                html: `คุณกำลังจะสร้างรายงานการสอนทั้งหมด <strong>${sessions.length} คาบ</strong> (ครอบคลุม ${weekKeys.length} สัปดาห์)<br><span class="text-xs text-gray-500">สัปดาห์เดียวกันเนื้อหาจะตรงกัน ส่วนต่างสัปดาห์เนื้อหาจะไม่ซ้ำกัน</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '🚀 เริ่มสร้างรายงานทันที',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#4f46e5'
            });

            if (!confirmRes.isConfirmed) return;

            // If user hasn't generated with AI, fetch AI curriculum automatically on submit for highest quality
            if (!batchAiCurriculumPlan || batchAiCurriculumPlan.length < weekKeys.length) {
                Swal.fire({
                    title: '✨ AI กำลังสร้างเนื้อหารายสัปดาห์ให้สอดคล้องกับวิชา...',
                    html: `กำลังจัดเตรียมเนื้อหา ${weekKeys.length} สัปดาห์ สำหรับวิชา <strong>${subjectText}</strong>`,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const aiRes = await fetch('../controllers/GeminiController.php?action=generate_curriculum_batch', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            subject_name: subjectText,
                            weeks_count: weekKeys.length
                        })
                    });
                    const aiData = await aiRes.json();
                    if (aiData.success && Array.isArray(aiData.data)) {
                        batchAiCurriculumPlan = aiData.data;
                    }
                } catch (e) {
                    console.warn('AI curriculum fallback error:', e);
                }
            }

            Swal.fire({
                title: 'กำลังสร้างรายงานย้อนหลัง...',
                html: `กรุณารอสักครู่ กำลังบันทึกข้อมูล 0 / ${sessions.length} รายการ`,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Map each session to its week's unique topic and activity
            const rowsToCreate = sessions.map(s => {
                const wk = getWeekKey(s.date);
                const weekNum = weekIndexMap[wk] || 1;
                const aiItem = batchAiCurriculumPlan && batchAiCurriculumPlan[weekNum - 1];

                const planTopic = aiItem?.plan_topic || (fallbackPlanTopic ? `${fallbackPlanTopic} (สัปดาห์ที่ ${weekNum})` : `หน่วยการเรียนรู้ที่ ${weekNum}`);
                const activity = aiItem?.activity || (fallbackActivity ? `${fallbackActivity} ประจำสัปดาห์ที่ ${weekNum}` : `จัดกิจกรรมการเรียนการสอนสัปดาห์ที่ ${weekNum}`);
                const reflectionK = aiItem?.reflection_k || fallbackReflectionK;
                const reflectionP = aiItem?.reflection_p || fallbackReflectionP;
                const reflectionA = aiItem?.reflection_a || fallbackReflectionA;

                return {
                    report_date: s.date,
                    subject_id: subjectId,
                    class_room: s.room,
                    period_start: String(s.period_start).trim(),
                    period_end: String(s.period_end).trim(),
                    plan_number: String(weekNum),
                    plan_topic: planTopic,
                    activity: activity,
                    absent_students: '',
                    reflection_k: reflectionK,
                    reflection_p: reflectionP,
                    reflection_a: reflectionA,
                    problems: '',
                    suggestions: '',
                    image1: null,
                    image2: null,
                    teacher_id: window.TEACHER_ID || '',
                    created_at: null
                };
            });

            try {
                // Submit in batches of 20
                const chunkSize = 20;
                let successCount = 0;

                for (let i = 0; i < rowsToCreate.length; i += chunkSize) {
                    const chunk = rowsToCreate.slice(i, i + chunkSize);
                    const res = await fetch('../controllers/TeachingReportController.php?action=create', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            rows: chunk,
                            attendance_logs: []
                        })
                    });
                    const result = await res.json();
                    if (result.success) {
                        successCount += chunk.length;
                        Swal.update({
                            html: `กำลังบันทึกข้อมูล ${successCount} / ${rowsToCreate.length} รายการ`
                        });
                    } else {
                        throw new Error(result.error || 'เกิดข้อผิดพลาดในการบันทึกบางรายการ');
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: '🎉 สร้างรายงานสำเร็จ!',
                    text: `บันทึกรายงานการสอนย้อนหลังเรียบร้อยแล้วทั้งหมด ${successCount} คาบ (จัดสรรเนื้อหาไม่ซ้ำกันตามสัปดาห์)`,
                    confirmButtonColor: '#10b981'
                });

                if (modalBatchReport) modalBatchReport.classList.add('hidden');
                loadReports();
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: err.message || 'ไม่สามารถบันทึกข้อมูลได้ครบถ้วน'
                });
            }
        });
    }

    // Initialize: call functions after all definitions are complete
    loadTermsFilter();
    loadSubjectsForReport();
});
