/**
 * Director Report JavaScript
 * Manages report viewing for school administrators
 */

class DirectorReportManager {
    constructor() {
        this.calendar = null;
        this.dataTable = null;
        this.currentReports = [];
        this.baseUrl = '../';

        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.bindEvents();
        this.loadDepartments();
    }

    bindEvents() {
        const deptSelect = document.getElementById('departmentSelect');
        const teacherSelect = document.getElementById('teacherSelect');
        const btnReload = document.getElementById('btnReload');

        deptSelect?.addEventListener('change', (e) => {
            this.clearDisplay();
            if (e.target.value) {
                this.loadTeachers(e.target.value);
            } else {
                teacherSelect.innerHTML = '<option value="">-- เลือกครู --</option>';
                teacherSelect.disabled = true;
            }
        });

        teacherSelect?.addEventListener('change', (e) => {
            if (e.target.value) {
                this.loadReports(e.target.value);
            } else {
                this.clearDisplay();
            }
        });

        btnReload?.addEventListener('click', () => {
            const dept = deptSelect?.value;
            const teacher = teacherSelect?.value;
            if (teacher) {
                this.loadReports(teacher);
            }
        });
    }

    async loadDepartments() {
        try {
            const res = await fetch(`${this.baseUrl}controllers/DepartmentController.php?action=list`);
            const data = await res.json();

            const select = document.getElementById('departmentSelect');
            if (!select) return;

            select.innerHTML = '<option value="">-- เลือกกลุ่มสาระ --</option>';
            data.forEach(d => {
                select.innerHTML += `<option value="${d.name}">${d.name}</option>`;
            });
        } catch (error) {
            console.error('Error loading departments:', error);
        }
    }

    async loadTeachers(department) {
        try {
            const res = await fetch(`${this.baseUrl}controllers/DepartmentController.php?action=listTeachers&department=${encodeURIComponent(department)}`);
            const data = await res.json();

            const select = document.getElementById('teacherSelect');
            if (!select) return;

            select.innerHTML = '<option value="">-- เลือกครู --</option>';
            data.forEach(t => {
                select.innerHTML += `<option value="${t.Teach_id}">${t.Teach_name}</option>`;
            });
            select.disabled = false;
        } catch (error) {
            console.error('Error loading teachers:', error);
        }
    }

    async loadReports(teacherId) {
        this.showLoading();
        try {
            const res = await fetch(`${this.baseUrl}controllers/TeachingReportController.php?action=list&teacher_id=${encodeURIComponent(teacherId)}`);
            const reports = await res.json();

            this.currentReports = reports;
            this.updateStats(reports);
            this.renderCalendar(reports);
            this.renderTable(reports);
            this.showReports();
        } catch (error) {
            console.error('Error loading reports:', error);
            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
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

        document.getElementById('statTotal').textContent = reports.length;
        document.getElementById('statMonth').textContent = thisMonth;
        document.getElementById('statSubjects').textContent = subjects;
        document.getElementById('statRooms').textContent = rooms;
        document.getElementById('reportCount').textContent = `${reports.length} รายการ`;
    }

    renderCalendar(reports) {
        const el = document.getElementById('calendar');
        if (!el) return;

        if (this.calendar) this.calendar.destroy();

        this.calendar = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            locale: 'th',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            },
            events: reports.map(r => ({
                id: r.id,
                title: `${r.subject_name || '-'}`.substring(0, 15),
                start: r.report_date,
                backgroundColor: this.getEventColor(r.report_date),
                borderColor: 'transparent',
                extendedProps: r
            })),
            eventClick: (info) => this.showDetail(info.event.extendedProps)
        });

        setTimeout(() => this.calendar.render(), 100);
    }

    getEventColor(date) {
        const d = new Date(date);
        const colors = ['#ef4444', '#eab308', '#ec4899', '#22c55e', '#f97316', '#6366f1', '#a855f7'];
        return colors[d.getDay()] || '#6366f1';
    }

    renderTable(reports) {
        const tbody = document.querySelector('#reportTable tbody');
        if (!tbody) return;

        if (this.dataTable) this.dataTable.destroy();
        tbody.innerHTML = '';

        reports.forEach(r => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-slate-50 dark:border-slate-800 hover:bg-slate-50/50';
            tr.innerHTML = `
                <td class="p-3 font-bold text-slate-700 dark:text-slate-300 text-xs" data-order="${r.report_date}">${this.formatThaiDate(r.report_date)}</td>
                <td class="p-3 text-slate-600 dark:text-slate-400 text-xs">${r.subject_name || '-'}</td>
                <td class="p-3 text-center"><span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-[10px] font-bold">ม.${r.level}/${r.class_room}</span></td>
                <td class="p-3 text-center"><span class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold">${r.period_start}-${r.period_end}</span></td>
                <td class="p-3 text-center">
                    <button class="btn-detail w-8 h-8 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-xs transition-all" data-id="${r.id}">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        this.dataTable = $('#reportTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' },
            order: [[0, 'desc']],
            pageLength: 10,
            dom: '<"mb-4"f>rt<"flex items-center justify-between gap-3 mt-4"ip>',
            drawCallback: () => this.bindDetailButtons()
        });
    }

    bindDetailButtons() {
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.onclick = () => {
                const id = btn.dataset.id;
                const report = this.currentReports.find(r => r.id == id);
                if (report) this.showDetail(report);
            };
        });
    }

    showDetail(report) {
        const content = `
            <div class="text-left space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">วันที่สอน</p>
                        <p class="font-bold text-slate-800">${this.formatThaiDate(report.report_date)}</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">คาบที่สอน</p>
                        <p class="font-bold text-slate-800">${report.period_start} - ${report.period_end}</p>
                    </div>
                </div>
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <p class="text-[10px] font-black text-indigo-500 uppercase mb-1">รายวิชา</p>
                    <p class="font-black text-indigo-700">${report.subject_name || '-'}</p>
                    <p class="text-sm font-bold text-slate-600 mt-1">ม.${report.level}/${report.class_room}</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-bold text-slate-500 mb-0.5">หัวข้อ/แผนการสอน</p>
                        <p class="text-sm text-slate-700">${report.plan_topic || '-'}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 mb-0.5">กิจกรรม</p>
                        <p class="text-sm text-slate-700">${report.activity || '-'}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 mb-0.5">ขาดเรียน</p>
                        <p class="text-sm text-rose-600 font-bold">${report.absent_students || 'ไม่มี'}</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="p-2 bg-emerald-50 rounded-lg text-center">
                        <p class="text-[9px] font-black text-emerald-600 uppercase">K</p>
                        <p class="text-xs text-slate-600">${report.reflection_k || '-'}</p>
                    </div>
                    <div class="p-2 bg-emerald-50 rounded-lg text-center">
                        <p class="text-[9px] font-black text-emerald-600 uppercase">P</p>
                        <p class="text-xs text-slate-600">${report.reflection_p || '-'}</p>
                    </div>
                    <div class="p-2 bg-emerald-50 rounded-lg text-center">
                        <p class="text-[9px] font-black text-emerald-600 uppercase">A</p>
                        <p class="text-xs text-slate-600">${report.reflection_a || '-'}</p>
                    </div>
                </div>
                ${report.image1 || report.image2 ? `
                    <div class="grid grid-cols-2 gap-4">
                        ${report.image1 ? `<img src="${this.baseUrl}${report.image1}" class="w-full h-32 object-cover rounded-xl cursor-pointer" onclick="window.open(this.src)">` : ''}
                        ${report.image2 ? `<img src="${this.baseUrl}${report.image2}" class="w-full h-32 object-cover rounded-xl cursor-pointer" onclick="window.open(this.src)">` : ''}
                    </div>
                ` : ''}
            </div>
        `;

        Swal.fire({
            title: '<div class="flex items-center gap-3 text-indigo-600 font-black"><i class="fas fa-file-alt"></i> รายละเอียดรายงาน</div>',
            html: content,
            width: '700px',
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'rounded-[2rem]' }
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

    clearDisplay() {
        document.getElementById('reportSection')?.classList.add('hidden');
        document.getElementById('noDataMsg')?.classList.remove('hidden');
    }

    showLoading() {
        Swal.fire({ title: 'กำลังโหลดข้อมูล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }

    hideLoading() {
        Swal.close();
    }
}

// Initialize
window.directorReportManager = new DirectorReportManager();
