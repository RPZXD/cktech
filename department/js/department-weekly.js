/**
 * Department Weekly Report JavaScript
 * Handles grid rendering, week selection, and report details
 */

class DepartmentWeeklyManager {
    constructor(config) {
        this.department = config.department;
        this.baseUrl = '../';
        this.weekDates = [];
        this.chart = null;

        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setDefaultWeek();
            this.bindEvents();
            this.loadWeeklyReport();
        });
    }

    bindEvents() {
        const weekPicker = document.getElementById('weekPicker');
        if (weekPicker) {
            weekPicker.addEventListener('change', () => this.loadWeeklyReport());
        }

        const btnReload = document.getElementById('btnReload');
        if (btnReload) {
            btnReload.addEventListener('click', () => {
                this.showLoading('กำลังรีเฟรชข้อมูล...');
                this.loadWeeklyReport();
            });
        }
    }

    setDefaultWeek() {
        const weekPicker = document.getElementById('weekPicker');
        if (!weekPicker) return;

        const now = new Date();
        const year = now.getFullYear();
        const onejan = new Date(now.getFullYear(), 0, 1);
        const week = Math.ceil((((now - onejan) / 86400000) + onejan.getDay() + 1) / 7);
        weekPicker.value = `${year}-W${week < 10 ? '0' + week : week}`;
    }

    getWeekDates() {
        const weekPicker = document.getElementById('weekPicker');
        if (weekPicker && weekPicker.value) {
            const [year, week] = weekPicker.value.split('-W');
            const jan4 = new Date(Date.UTC(year, 0, 4, 7, 0, 0));
            const jan4Day = jan4.getUTCDay() || 7;
            const mondayOfWeek1 = new Date(jan4);
            mondayOfWeek1.setUTCDate(jan4.getUTCDate() - (jan4Day - 1));
            const monday = new Date(mondayOfWeek1);
            monday.setUTCDate(mondayOfWeek1.getUTCDate() + (parseInt(week, 10) - 1) * 7);
            monday.setHours(7, 0, 0, 0);
            return this.generateDays(monday);
        } else {
            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1);
            const monday = new Date(now.setDate(diff));
            monday.setHours(7, 0, 0, 0);
            return this.generateDays(monday);
        }
    }

    generateDays(monday) {
        let days = [];
        for (let i = 0; i < 5; i++) {
            let d = new Date(monday);
            d.setDate(monday.getDate() + i);
            days.push(d);
        }
        return days;
    }

    async loadWeeklyReport() {
        if (!this.department) return;

        this.weekDates = this.getWeekDates();
        this.showLoading('กำลังโหลดรายงานรายสัปดาห์...');

        try {
            // Load teachers
            const tRes = await fetch(`${this.baseUrl}controllers/DepartmentController.php?action=listTeachers&department=${encodeURIComponent(this.department)}`);
            const teachers = await tRes.json();

            if (teachers.length === 0) {
                this.showNoData();
                return;
            }

            // Load reports
            const teacherIds = teachers.map(t => t.Teach_id).join(',');
            const rRes = await fetch(`${this.baseUrl}controllers/TeachingReportController.php?action=listByTeachers&teacher_ids=${encodeURIComponent(teacherIds)}&week_start=${this.formatISO(this.weekDates[0])}&week_end=${this.formatISO(this.weekDates[4])}`);
            const reports = await rRes.json();

            this.renderTable(teachers, reports);
            this.renderChart(teachers, reports);

            document.getElementById('weeklySection')?.classList.remove('hidden');
            document.getElementById('noDataMsg')?.classList.add('hidden');
        } catch (error) {
            console.error('Error loading weekly report:', error);
            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลสัปดาห์นี้ได้', 'error');
        } finally {
            this.hideLoading();
        }
    }

    renderTable(teachers, reports) {
        const thead = document.getElementById('weeklyTableHead');
        const tbody = document.getElementById('weeklyTableBody');
        if (!thead || !tbody) return;

        // Group reports by teacher and date
        const reportMap = {};
        reports.forEach(r => {
            if (!reportMap[r.teacher_id]) reportMap[r.teacher_id] = {};
            if (!reportMap[r.teacher_id][r.report_date]) reportMap[r.teacher_id][r.report_date] = [];
            reportMap[r.teacher_id][r.report_date].push(r);
        });

        // Render Head
        let headHtml = '<th class="p-6 text-left w-64">👩‍🏫 ชื่อ-นามสกุล ครู</th>';
        this.weekDates.forEach(d => {
            headHtml += `
                <th class="p-6 text-center">
                    <p class="text-[10px] font-black uppercase text-slate-400 mb-1">${this.formatDayName(d)}</p>
                    <p class="text-xs font-black text-slate-700 dark:text-slate-300">${this.formatThaiDate(d)}</p>
                </th>`;
        });
        thead.innerHTML = headHtml;

        // Render Body
        tbody.innerHTML = '';
        teachers.forEach((t, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors border-b border-slate-100 dark:border-slate-800';

            let rowHtml = `
                <td class="p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold">
                            ${t.Teach_name.charAt(0)}
                        </div>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm whitespace-nowrap">${t.Teach_name}</span>
                    </div>
                </td>`;

            this.weekDates.forEach(d => {
                const dateStr = this.formatISO(d);
                const dayReports = (reportMap[t.Teach_id] && reportMap[t.Teach_id][dateStr]) ? reportMap[t.Teach_id][dateStr] : [];

                rowHtml += '<td class="p-4 text-center">';
                if (dayReports.length > 0) {
                    dayReports.forEach(r => {
                        rowHtml += `
                            <button onclick="window.manager.showDetail(${r.id})" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black border border-emerald-100 hover:bg-emerald-100 transition-all mb-1 mr-1 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                คาบ ${r.period_start}-${r.period_end}
                            </button>`;
                    });
                } else {
                    rowHtml += '<span class="text-slate-300">-</span>';
                }
                rowHtml += '</td>';
            });

            tr.innerHTML = rowHtml;
            tbody.appendChild(tr);
        });
    }

    renderChart(teachers, reports) {
        const ctx = document.getElementById('weeklyChartCanvas')?.getContext('2d');
        if (!ctx) return;
        if (this.chart) this.chart.destroy();

        const data = this.weekDates.map(d => {
            const dateStr = this.formatISO(d);
            return reports.filter(r => r.report_date === dateStr).length;
        });

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.weekDates.map(d => this.formatDayName(d)),
                datasets: [{
                    label: 'จำนวนรายงานรายวัน',
                    data: data,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    async showDetail(id) {
        this.showLoading('กำลังโหลดรายละเอียด...');
        try {
            const res = await fetch(`${this.baseUrl}controllers/TeachingReportController.php?action=detail&id=${id}`);
            const r = await res.json();

            let html = `
                <div class="space-y-6 text-left p-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800">
                            <p class="text-[10px] font-black text-blue-500 uppercase mb-1">วันที่สอน</p>
                            <p class="font-bold text-slate-700 dark:text-slate-200">${this.formatThaiDateLong(new Date(r.report_date))}</p>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                            <p class="text-[10px] font-black text-emerald-500 uppercase mb-1">คาบเรียน</p>
                            <p class="font-bold text-slate-700 dark:text-slate-200">${r.period_start} - ${r.period_end}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">ข้อมูลวิชา</p>
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                                <p class="font-bold text-slate-800 dark:text-white">${r.subject_name || '-'}</p>
                                <p class="text-xs text-slate-500 font-medium">ระดับชั้น ม.${r.level}/${r.class_room}</p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">แผนการจัดการเรียนรู้ / หัวข้อ</p>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 p-4 border border-slate-100 dark:border-slate-800 rounded-2xl italic">"${r.plan_topic || '-'}"</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">กิจกรรมการเรียนรู้</p>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">${r.activity || '-'}</p>
                        </div>
                    </div>

                    ${r.image1 || r.image2 ? `
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">หลักฐานการสอน</p>
                            <div class="flex gap-4">
                                ${r.image1 ? `<img src="${this.baseUrl}${r.image1}" class="w-32 h-32 object-cover rounded-2xl shadow-sm hover:scale-105 transition-transform cursor-pointer" onclick="window.manager.previewImage('${this.baseUrl}${r.image1}')">` : ''}
                                ${r.image2 ? `<img src="${this.baseUrl}${r.image2}" class="w-32 h-32 object-cover rounded-2xl shadow-sm hover:scale-105 transition-transform cursor-pointer" onclick="window.manager.previewImage('${this.baseUrl}${r.image2}')">` : ''}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;

            Swal.fire({
                title: `<p class="text-xl font-black mb-1">รายละเอียดการสอน</p><p class="text-xs font-bold text-slate-400 uppercase tracking-widest">${r.subject_name}</p>`,
                html: html,
                width: '600px',
                showConfirmButton: false,
                showCloseButton: true,
                customClass: { popup: 'rounded-[2.5rem]' }
            });
        } catch (error) {
            console.error('Error fetching detail:', error);
            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดรายละเอียดได้', 'error');
        } finally {
            this.hideLoading();
        }
    }

    previewImage(src) {
        Swal.fire({
            imageUrl: src,
            imageWidth: '100%',
            imageAlt: 'Teaching Evidence',
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'rounded-[2rem] overflow-hidden' }
        });
    }

    formatISO(d) { return d.toISOString().slice(0, 10); }

    formatDayName(d) {
        const days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
        return days[d.getDay()];
    }

    formatThaiDate(d) {
        const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
    }

    formatThaiDateLong(d) {
        const months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
    }

    showLoading(title = 'กำลังโหลด...') {
        Swal.fire({ title: title, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }

    hideLoading() { Swal.close(); }

    showNoData() {
        document.getElementById('weeklySection')?.classList.add('hidden');
        document.getElementById('noDataMsg')?.classList.remove('hidden');
    }

    printWeekly() {
        const weekDates = this.weekDates;
        const weekStart = this.formatThaiDateLong(weekDates[0]);
        const weekEnd = this.formatThaiDateLong(weekDates[weekDates.length - 1]);
        const tableContent = document.getElementById('weeklyTable').outerHTML;

        const printWindow = window.open('', '', 'width=1200,height=800');
        printWindow.document.write(`
            <html>
            <head>
                <title>รายงานรายสัปดาห์ - ${this.department}</title>
                <style>
                    body { font-family: 'Sarabun', sans-serif; padding: 40px; }
                    .header { text-align: center; margin-bottom: 40px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
                    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
                    th { background-color: #f8fafc; font-weight: bold; }
                    .teacher-name { text-align: left; font-weight: bold; }
                    button { display: none; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>สรุปรายงานการจัดการเรียนการสอนรายสัปดาห์</h2>
                    <p>กลุ่มสาระการเรียนรู้${this.department}</p>
                    <p>สัปดาห์ระหว่างวันที่ ${weekStart} ถึง ${weekEnd}</p>
                </div>
                ${tableContent}
            </body>
            </html>
        `);
        printWindow.document.close();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
}
