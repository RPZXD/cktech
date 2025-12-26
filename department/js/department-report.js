/**
 * Department Report JavaScript
 * Manages report listing, calendar, and teacher selection for department heads
 * Enhanced for mobile responsiveness
 */

class DepartmentReportManager {
    constructor(config) {
        this.department = config.department;
        this.calendar = null;
        this.mobileCalendar = null;
        this.dataTable = null;
        this.baseUrl = '../';
        this.currentReports = [];

        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.bindEvents();
            this.loadTeachers();
        });
    }

    bindEvents() {
        const teacherSelect = document.getElementById('teacherSelect');
        const btnReload = document.getElementById('btnReload');

        if (teacherSelect) {
            teacherSelect.addEventListener('change', (e) => {
                const teacherId = e.target.value;
                if (teacherId) {
                    this.loadReports(teacherId);
                } else {
                    this.hideReports();
                }
            });
        }

        if (btnReload) {
            btnReload.addEventListener('click', () => {
                const teacherId = teacherSelect.value;
                this.loadTeachers(teacherId);
                if (teacherId) {
                    this.loadReports(teacherId);
                }
            });
        }
    }

    async loadTeachers(selectedId = null) {
        try {
            const response = await fetch(`${this.baseUrl}controllers/DepartmentController.php?action=listTeachers&department=${encodeURIComponent(this.department)}`);
            const teachers = await response.json();

            const select = document.getElementById('teacherSelect');
            if (!select) return;

            select.innerHTML = '<option value="">-- เลือกครู --</option>';
            teachers.forEach(t => {
                const option = document.createElement('option');
                option.value = t.Teach_id;
                option.textContent = t.Teach_name;
                select.appendChild(option);
            });

            if (selectedId) select.value = selectedId;
        } catch (error) {
            console.error('Error loading teachers:', error);
        }
    }

    async loadReports(teacherId) {
        this.showLoading();
        try {
            const response = await fetch(`${this.baseUrl}controllers/TeachingReportController.php?action=list&teacher_id=${encodeURIComponent(teacherId)}`);
            const reports = await response.json();

            this.currentReports = reports;
            this.renderCalendar(reports);
            this.renderTable(reports);
            this.renderMobileCards(reports);
            this.updateStats(reports);
            this.showReports();

            // Render mobile calendar if container is visible
            if (!document.getElementById('mobileCalendarContainer')?.classList.contains('hidden')) {
                this.renderMobileCalendar();
            }
        } catch (error) {
            console.error('Error loading reports:', error);
            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลรายงานได้', 'error');
        } finally {
            this.hideLoading();
        }
    }

    updateStats(reports) {
        const now = new Date();
        const thisMonth = reports.filter(r => {
            const d = new Date(r.report_date);
            return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        }).length;

        const subjects = new Set(reports.map(r => r.subject_name)).size;
        const rooms = new Set(reports.map(r => `${r.level}-${r.class_room}`)).size;

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        setVal('statTotal', reports.length);
        setVal('statMonth', thisMonth);
        setVal('statSubjects', subjects);
        setVal('statRooms', rooms);

        const countEl = document.getElementById('reportCount');
        if (countEl) countEl.textContent = `${reports.length} รายการ`;
    }

    renderMobileCards(reports) {
        const container = document.getElementById('mobileReportList');
        if (!container) return;

        container.innerHTML = '';

        if (reports.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="text-4xl mb-3 opacity-30">📭</div>
                    <p class="text-slate-400 font-bold">ไม่พบรายงานการสอน</p>
                </div>
            `;
            return;
        }

        reports.slice(0, 20).forEach((r, i) => {
            const card = document.createElement('div');
            card.className = 'mobile-report-card';
            card.style.animationDelay = `${i * 0.05}s`;
            card.innerHTML = `
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-slate-800 dark:text-white text-sm truncate">${r.subject_name || 'ไม่ระบุวิชา'}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">ม.${r.level}/${r.class_room} • คาบ ${r.period_start}-${r.period_end}</p>
                    </div>
                    <div class="shrink-0 px-2 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black">
                        ${this.formatThaiDateShort(r.report_date)}
                    </div>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 mb-3">${r.plan_topic || 'ไม่มีรายละเอียด'}</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        ${r.image1 ? '<span class="w-2 h-2 rounded-full bg-emerald-400"></span><span class="text-[10px] font-bold text-emerald-600">มีรูปภาพ</span>' : ''}
                    </div>
                    <button class="btn-mobile-detail px-3 py-1.5 bg-blue-500 text-white rounded-xl text-[10px] font-bold flex items-center gap-1.5 active:scale-95 transition-transform" data-id="${r.id}">
                        <i class="fas fa-eye"></i> ดูเพิ่มเติม
                    </button>
                </div>
            `;
            card.querySelector('.btn-mobile-detail').addEventListener('click', () => this.showDetail(r));
            container.appendChild(card);
        });

        if (reports.length > 20) {
            container.innerHTML += `
                <div class="text-center py-4">
                    <p class="text-xs font-bold text-slate-400">แสดง 20 รายการล่าสุดจาก ${reports.length} รายการ</p>
                </div>
            `;
        }
    }

    renderMobileCalendar() {
        const calendarEl = document.getElementById('mobileCalendar');
        if (!calendarEl || !this.currentReports) return;

        if (this.mobileCalendar) this.mobileCalendar.destroy();

        this.mobileCalendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'th',
            height: 'auto',
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            events: this.currentReports.map(r => ({
                id: r.id,
                title: r.subject_name ? r.subject_name.substring(0, 10) : '-',
                start: r.report_date,
                backgroundColor: this.getEventColor(r.report_date),
                borderColor: 'transparent',
                extendedProps: r
            })),
            eventClick: (info) => this.showDetail(info.event.extendedProps)
        });

        this.mobileCalendar.render();
    }

    formatThaiDateShort(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return `${d.getDate()}/${d.getMonth() + 1}`;
    }

    renderCalendar(reports) {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        if (this.calendar) this.calendar.destroy();

        this.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'th',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            themeSystem: 'standard',
            events: reports.map(r => ({
                id: r.id,
                title: `${r.subject_name || '-'} (${r.class_room || '-'})`,
                start: r.report_date,
                backgroundColor: this.getEventColor(r.report_date),
                borderColor: 'transparent',
                extendedProps: r
            })),
            eventClick: (info) => {
                this.showDetail(info.event.extendedProps);
            }
        });

        setTimeout(() => this.calendar.render(), 100);
    }

    getEventColor(date) {
        const d = new Date(date);
        const day = d.getDay();
        const colors = [
            '#ef4444', // Sun
            '#eab308', // Mon
            '#ec4899', // Tue
            '#22c55e', // Wed
            '#f97316', // Thu
            '#0ea5e9', // Fri
            '#a855f7'  // Sat
        ];
        return colors[day] || '#3b82f6';
    }

    renderTable(reports) {
        const tableBody = document.querySelector('#reportTable tbody');
        if (!tableBody) return;

        if (this.dataTable) this.dataTable.destroy();
        tableBody.innerHTML = '';

        reports.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td data-order="${r.report_date}">
                    <span class="font-bold text-slate-700 dark:text-slate-300">${this.formatThaiDate(r.report_date)}</span>
                </td>
                <td>
                    <span class="font-semibold text-slate-600 dark:text-slate-400">${r.subject_name || '-'}</span>
                </td>
                <td class="text-center">
                    <span class="inline-block px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg text-xs font-bold">
                        ม.${r.level}/${r.class_room}
                    </span>
                </td>
                <td class="text-center">
                    <span class="inline-block px-2 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold">
                        ${r.period_start}-${r.period_end}
                    </span>
                </td>
                <td>
                    <span class="text-slate-500 text-xs">${r.plan_topic ? (r.plan_topic.length > 20 ? r.plan_topic.substring(0, 20) + '...' : r.plan_topic) : '-'}</span>
                </td>
                <td class="text-center">
                    <button class="btn-detail w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs transition-all shadow-md hover:shadow-lg active:scale-95" data-id="${r.id}" title="ดูรายละเอียด">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        this.dataTable = $('#reportTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' },
            order: [[0, 'desc']],
            pageLength: 10,
            responsive: true,
            dom: '<"mb-4"f>rt<"flex flex-col sm:flex-row items-center justify-between gap-3 mt-4"ip>',
            drawCallback: () => {
                this.bindDetailButtons(reports);
            }
        });

        // Update count
        const countEl = document.getElementById('reportCount');
        if (countEl) countEl.textContent = `${reports.length} รายการ`;
    }

    bindDetailButtons(reports) {
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const report = reports.find(r => r.id == id);
                if (report) this.showDetail(report);
            });
        });
    }

    showDetail(report) {
        let content = `
            <div class="text-left space-y-4 max-h-[70vh] overflow-y-auto px-2">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">วันที่สอน</p>
                        <p class="font-bold text-slate-800 dark:text-white">${this.formatThaiDate(report.report_date)}</p>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">คาบที่สอน</p>
                        <p class="font-bold text-slate-800 dark:text-white">${report.period_start} - ${report.period_end}</p>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-900/30">
                    <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">รายวิชาและกลุ่มเป้าหมาย</p>
                    <p class="font-black text-blue-700 dark:text-blue-400">${report.subject_name || '-'}</p>
                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400 mt-1">ระดับชั้น ม.${report.level}/${report.class_room}</p>
                </div>

                <div>
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 border-b border-slate-100 pb-1">รายละเอียดการจัดการเรียนรู้</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-0.5">แผนการสอน/หัวข้อ</p>
                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">${report.plan_topic || '-'}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-0.5">กิจกรรมการเรียนรู้</p>
                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">${report.activity || '-'}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-0.5">นักเรียนที่ขาดเรียน</p>
                            <p class="text-sm text-rose-600 font-bold">${report.absent_students || 'ไม่มี'}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black text-emerald-500 uppercase tracking-widest mb-2 border-b border-emerald-50 pb-1">ผลการจัดการเรียนรู้ (KPA)</h4>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="p-3 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-xl">
                            <span class="text-[10px] font-black text-emerald-600 uppercase mr-2">Knowledge (K):</span>
                            <span class="text-sm text-slate-700 dark:text-slate-300">${report.reflection_k || '-'}</span>
                        </div>
                        <div class="p-3 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-xl">
                            <span class="text-[10px] font-black text-emerald-600 uppercase mr-2">Process (P):</span>
                            <span class="text-sm text-slate-700 dark:text-slate-300">${report.reflection_p || '-'}</span>
                        </div>
                        <div class="p-3 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-xl">
                            <span class="text-[10px] font-black text-emerald-600 uppercase mr-2">Attitude (A):</span>
                            <span class="text-sm text-slate-700 dark:text-slate-300">${report.reflection_a || '-'}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    ${report.image1 ? `
                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase">รูปประกอบ 1</p>
                            <img src="${this.baseUrl}${report.image1}" class="w-full h-40 object-cover rounded-2xl shadow-md cursor-pointer" onclick="window.open(this.src)">
                        </div>
                    ` : ''}
                    ${report.image2 ? `
                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase">รูปประกอบ 2</p>
                            <img src="${this.baseUrl}${report.image2}" class="w-full h-40 object-cover rounded-2xl shadow-md cursor-pointer" onclick="window.open(this.src)">
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        Swal.fire({
            title: `<div class="flex items-center gap-3 text-blue-600 font-black"><i class="fas fa-file-alt"></i> รายละเอียดรายงาน</div>`,
            html: content,
            width: '800px',
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                popup: 'rounded-[2rem] border-none shadow-2xl overflow-hidden',
                title: 'text-left px-8 pt-8 border-b pb-4 bg-slate-50',
                htmlContainer: 'px-8 py-6'
            },
            showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' }
        });
    }

    formatThaiDate(dateStr) {
        if (!dateStr) return '-';
        const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
    }

    showReports() {
        document.getElementById('reportSection')?.classList.remove('hidden');
        document.getElementById('noDataMsg')?.classList.add('hidden');
    }

    hideReports() {
        document.getElementById('reportSection')?.classList.add('hidden');
        document.getElementById('noDataMsg')?.classList.remove('hidden');
    }

    showLoading() {
        Swal.fire({
            title: 'กำลังโหลดข้อมูล...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }

    hideLoading() {
        Swal.close();
    }
}
