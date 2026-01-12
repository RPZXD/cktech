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
        if (!this.department) {
            this.showNoData();
            return;
        }

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

            // Store for print
            this.teachers = teachers;

            // Load reports
            const teacherIds = teachers.map(t => t.Teach_id).join(',');
            const rRes = await fetch(`${this.baseUrl}controllers/TeachingReportController.php?action=listByTeachers&teacher_ids=${encodeURIComponent(teacherIds)}&week_start=${this.formatISO(this.weekDates[0])}&week_end=${this.formatISO(this.weekDates[4])}`);
            const reports = await rRes.json();

            // Store for print
            this.reports = reports;

            this.renderTable(teachers, reports);
            this.renderMobileCards(teachers, reports);
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

    renderMobileCards(teachers, reports) {
        const container = document.getElementById('weeklyMobileCards');
        if (!container) return;

        // Group reports by teacher and date
        const reportMap = {};
        reports.forEach(r => {
            if (!reportMap[r.teacher_id]) reportMap[r.teacher_id] = {};
            if (!reportMap[r.teacher_id][r.report_date]) reportMap[r.teacher_id][r.report_date] = [];
            reportMap[r.teacher_id][r.report_date].push(r);
        });

        container.innerHTML = '';

        teachers.forEach(t => {
            // Count total reports for this teacher
            const teacherReports = reportMap[t.Teach_id] || {};
            const totalReports = Object.values(teacherReports).reduce((sum, arr) => sum + arr.length, 0);

            const card = document.createElement('div');
            card.className = 'glass rounded-3xl p-5 border border-white/20 shadow-lg';

            let cardHtml = `
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-lg font-bold shadow-lg shadow-blue-500/20">
                        ${t.Teach_name.charAt(0)}
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-800 dark:text-white">${t.Teach_name}</h3>
                        <p class="text-xs text-slate-500">รายงานสัปดาห์นี้ <span class="font-bold text-blue-600">${totalReports}</span> รายการ</p>
                    </div>
                </div>
                <div class="space-y-3">
            `;

            // Render each day
            this.weekDates.forEach(d => {
                const dateStr = this.formatISO(d);
                const dayReports = (reportMap[t.Teach_id] && reportMap[t.Teach_id][dateStr]) ? reportMap[t.Teach_id][dateStr] : [];

                cardHtml += `
                    <div class="flex items-start gap-3 p-3 rounded-2xl ${dayReports.length > 0 ? 'bg-emerald-50/50 dark:bg-emerald-900/20' : 'bg-slate-50/50 dark:bg-slate-800/30'}">
                        <div class="text-center min-w-[50px]">
                            <p class="text-[10px] font-black text-slate-400 uppercase">${this.formatDayName(d)}</p>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-300">${d.getDate()}</p>
                        </div>
                        <div class="flex-1 flex flex-wrap gap-1.5">
                `;

                if (dayReports.length > 0) {
                    dayReports.forEach(r => {
                        cardHtml += `
                            <button onclick="window.manager.showDetail(${r.id})" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 dark:bg-emerald-800/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-[10px] font-bold border border-emerald-200 dark:border-emerald-700 hover:bg-emerald-200 active:scale-95 transition-all">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                คาบ ${r.period_start}-${r.period_end}
                            </button>
                        `;
                    });
                } else {
                    cardHtml += `<span class="text-xs text-slate-400 italic">ไม่มีรายงาน</span>`;
                }

                cardHtml += `
                        </div>
                    </div>
                `;
            });

            cardHtml += `
                </div>
            `;

            card.innerHTML = cardHtml;
            container.appendChild(card);
        });

        // Add hint at bottom
        const hint = document.createElement('div');
        hint.className = 'flex items-center gap-2 px-4 py-3 bg-slate-100/50 dark:bg-slate-800/50 rounded-2xl';
        hint.innerHTML = `
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs text-slate-500 font-medium">แตะที่ "คาบสอน" เพื่อดูรายละเอียด</span>
        `;
        container.appendChild(hint);
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
        if (!weekDates || weekDates.length === 0) {
            Swal.fire('ไม่สามารถพิมพ์ได้', 'กรุณาโหลดข้อมูลก่อน', 'warning');
            return;
        }

        const weekStart = this.formatThaiDateLong(weekDates[0]);
        const weekEnd = this.formatThaiDateLong(weekDates[weekDates.length - 1]);
        const totalReports = this.reports ? this.reports.length : 0;
        const totalTeachers = this.teachers ? this.teachers.length : 0;

        // Build print-friendly table
        let tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th style="text-align:left;width:200px;">ชื่อ-นามสกุล ครู</th>
        `;
        weekDates.forEach(d => {
            tableHtml += `<th>${this.formatDayName(d)}<br><small>${this.formatThaiDate(d)}</small></th>`;
        });
        tableHtml += `<th>รวม</th></tr></thead><tbody>`;

        // Group reports
        const reportMap = {};
        (this.reports || []).forEach(r => {
            if (!reportMap[r.teacher_id]) reportMap[r.teacher_id] = {};
            if (!reportMap[r.teacher_id][r.report_date]) reportMap[r.teacher_id][r.report_date] = [];
            reportMap[r.teacher_id][r.report_date].push(r);
        });

        (this.teachers || []).forEach(t => {
            let rowTotal = 0;
            tableHtml += `<tr><td style="text-align:left;font-weight:bold;">${t.Teach_name}</td>`;
            weekDates.forEach(d => {
                const dateStr = this.formatISO(d);
                const dayReports = (reportMap[t.Teach_id] && reportMap[t.Teach_id][dateStr]) ? reportMap[t.Teach_id][dateStr] : [];
                rowTotal += dayReports.length;
                if (dayReports.length > 0) {
                    const periods = dayReports.map(r => `คาบ ${r.period_start}-${r.period_end}`).join(', ');
                    tableHtml += `<td style="background:#ecfdf5;color:#059669;">${periods}</td>`;
                } else {
                    tableHtml += `<td style="color:#cbd5e1;">-</td>`;
                }
            });
            tableHtml += `<td style="font-weight:bold;color:#3b82f6;">${rowTotal}</td></tr>`;
        });
        tableHtml += `</tbody></table>`;

        const printWindow = window.open('', '', 'width=1200,height=800');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="th">
            <head>
                <meta charset="UTF-8">
                <title>รายงานรายสัปดาห์ - ${this.department}</title>
                <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: 'Sarabun', sans-serif; 
                        padding: 30px; 
                        color: #1e293b;
                        background: #fff;
                    }
                    .header { 
                        text-align: center; 
                        margin-bottom: 30px; 
                        padding-bottom: 20px;
                        border-bottom: 3px double #e2e8f0;
                    }
                    .header h1 { 
                        font-size: 22px; 
                        font-weight: 700; 
                        color: #1e40af;
                        margin-bottom: 8px;
                    }
                    .header p { 
                        font-size: 14px; 
                        color: #64748b; 
                        margin: 4px 0;
                    }
                    .header .dept-name {
                        font-size: 16px;
                        font-weight: 600;
                        color: #334155;
                    }
                    .stats {
                        display: flex;
                        justify-content: center;
                        gap: 40px;
                        margin: 20px 0;
                        padding: 15px;
                        background: #f8fafc;
                        border-radius: 8px;
                    }
                    .stat-item {
                        text-align: center;
                    }
                    .stat-value {
                        font-size: 28px;
                        font-weight: 700;
                        color: #3b82f6;
                    }
                    .stat-label {
                        font-size: 12px;
                        color: #64748b;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-top: 20px; 
                        font-size: 13px; 
                    }
                    th, td { 
                        border: 1px solid #e2e8f0; 
                        padding: 10px 8px; 
                        text-align: center; 
                    }
                    th { 
                        background: linear-gradient(to bottom, #f1f5f9, #e2e8f0); 
                        font-weight: 600;
                        color: #475569;
                    }
                    th small {
                        display: block;
                        font-weight: 400;
                        font-size: 11px;
                        color: #64748b;
                    }
                    tr:nth-child(even) { background: #fafafa; }
                    tr:hover { background: #f0f9ff; }
                    .footer {
                        margin-top: 30px;
                        padding-top: 20px;
                        border-top: 1px solid #e2e8f0;
                        text-align: center;
                        font-size: 11px;
                        color: #94a3b8;
                    }
                    @media print { 
                        body { padding: 15px; }
                        .stats { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>📊 สรุปรายงานการจัดการเรียนการสอนรายสัปดาห์</h1>
                    <p class="dept-name">กลุ่มสาระการเรียนรู้${this.department}</p>
                    <p>สัปดาห์ระหว่างวันที่ ${weekStart} - ${weekEnd}</p>
                </div>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">${totalTeachers}</div>
                        <div class="stat-label">จำนวนครู</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${totalReports}</div>
                        <div class="stat-label">รายงานทั้งหมด</div>
                    </div>
                </div>
                ${tableHtml}
                <div class="footer">
                    พิมพ์เมื่อ ${new Date().toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 800);
    }
}
