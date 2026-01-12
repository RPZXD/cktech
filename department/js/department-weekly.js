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

        // Group reports
        const reportMap = {};
        (this.reports || []).forEach(r => {
            if (!reportMap[r.teacher_id]) reportMap[r.teacher_id] = {};
            if (!reportMap[r.teacher_id][r.report_date]) reportMap[r.teacher_id][r.report_date] = [];
            reportMap[r.teacher_id][r.report_date].push(r);
        });

        // Build table rows
        let tableRows = '';
        let teacherIndex = 0;
        (this.teachers || []).forEach(t => {
            teacherIndex++;
            let rowTotal = 0;
            let cellsHtml = '';

            weekDates.forEach(d => {
                const dateStr = this.formatISO(d);
                const dayReports = (reportMap[t.Teach_id] && reportMap[t.Teach_id][dateStr]) ? reportMap[t.Teach_id][dateStr] : [];
                rowTotal += dayReports.length;

                if (dayReports.length > 0) {
                    const badges = dayReports.map(r =>
                        `<span class="period-badge">คาบ ${r.period_start}-${r.period_end}</span>`
                    ).join('');
                    cellsHtml += `<td class="has-report">${badges}</td>`;
                } else {
                    cellsHtml += `<td class="no-report">—</td>`;
                }
            });

            tableRows += `
                <tr>
                    <td class="teacher-cell">
                        <div class="teacher-info">
                            <span class="teacher-num">${teacherIndex}</span>
                            <span class="teacher-name">${t.Teach_name}</span>
                        </div>
                    </td>
                    ${cellsHtml}
                    <td class="total-cell">${rowTotal}</td>
                </tr>
            `;
        });

        // Calculate average per teacher
        const avgPerTeacher = totalTeachers > 0 ? (totalReports / totalTeachers).toFixed(1) : 0;

        const printWindow = window.open('', '', 'width=1200,height=800');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="th">
            <head>
                <meta charset="UTF-8">
                <title>รายงานรายสัปดาห์ - ${this.department}</title>
                <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                <style>
                    :root {
                        --primary: #4f46e5;
                        --primary-light: #818cf8;
                        --success: #10b981;
                        --success-light: #d1fae5;
                        --gray-50: #f8fafc;
                        --gray-100: #f1f5f9;
                        --gray-200: #e2e8f0;
                        --gray-300: #cbd5e1;
                        --gray-400: #94a3b8;
                        --gray-500: #64748b;
                        --gray-600: #475569;
                        --gray-700: #334155;
                        --gray-800: #1e293b;
                    }
                    
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    
                    body { 
                        font-family: 'Sarabun', sans-serif; 
                        background: #fff;
                        color: var(--gray-800);
                        line-height: 1.5;
                    }
                    
                    .page-container {
                        max-width: 1100px;
                        margin: 0 auto;
                        padding: 40px;
                    }
                    
                    /* Header Section */
                    .header {
                        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
                        color: white;
                        padding: 30px 40px;
                        border-radius: 20px;
                        margin-bottom: 30px;
                        position: relative;
                        overflow: hidden;
                    }
                    
                    .header::before {
                        content: '';
                        position: absolute;
                        top: -50%;
                        right: -20%;
                        width: 300px;
                        height: 300px;
                        background: rgba(255,255,255,0.1);
                        border-radius: 50%;
                    }
                    
                    .header::after {
                        content: '';
                        position: absolute;
                        bottom: -30%;
                        left: -10%;
                        width: 200px;
                        height: 200px;
                        background: rgba(255,255,255,0.05);
                        border-radius: 50%;
                    }
                    
                    .header-content {
                        position: relative;
                        z-index: 1;
                    }
                    
                    .header-icon {
                        font-size: 48px;
                        margin-bottom: 10px;
                    }
                    
                    .header h1 {
                        font-size: 26px;
                        font-weight: 800;
                        margin-bottom: 8px;
                        letter-spacing: -0.5px;
                    }
                    
                    .header .dept-name {
                        font-size: 18px;
                        font-weight: 600;
                        opacity: 0.95;
                        margin-bottom: 4px;
                    }
                    
                    .header .date-range {
                        font-size: 14px;
                        opacity: 0.85;
                        font-weight: 400;
                    }
                    
                    /* Stats Section */
                    .stats-grid {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 20px;
                        margin-bottom: 30px;
                    }
                    
                    .stat-card {
                        background: var(--gray-50);
                        border: 2px solid var(--gray-100);
                        border-radius: 16px;
                        padding: 24px;
                        text-align: center;
                        transition: all 0.3s;
                    }
                    
                    .stat-card.primary {
                        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                        border-color: #bfdbfe;
                    }
                    
                    .stat-card.success {
                        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
                        border-color: #a7f3d0;
                    }
                    
                    .stat-card.purple {
                        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
                        border-color: #e9d5ff;
                    }
                    
                    .stat-icon {
                        font-size: 32px;
                        margin-bottom: 8px;
                    }
                    
                    .stat-value {
                        font-size: 28px;
                        font-weight: 800;
                        color: var(--gray-800);
                        line-height: 1;
                        margin-bottom: 6px;
                    }
                    
                    .stat-card.primary .stat-value { color: #2563eb; }
                    .stat-card.success .stat-value { color: #059669; }
                    .stat-card.purple .stat-value { color: #9333ea; }
                    
                    .stat-label {
                        font-size: 13px;
                        font-weight: 600;
                        color: var(--gray-500);
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    
                    /* Table Section */
                    .table-container {
                        background: white;
                        border-radius: 20px;
                        overflow: hidden;
                        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 10px 15px -3px rgba(0,0,0,0.05);
                        border: 2px solid var(--gray-100);
                    }
                    
                    .table-header {
                        background: var(--gray-50);
                        padding: 16px 24px;
                        border-bottom: 2px solid var(--gray-100);
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }
                    
                    .table-header h2 {
                        font-size: 16px;
                        font-weight: 700;
                        color: var(--gray-700);
                    }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 13px;
                    }
                    
                    thead th {
                        background: linear-gradient(180deg, var(--gray-50) 0%, var(--gray-100) 100%);
                        padding: 16px 12px;
                        font-weight: 700;
                        color: var(--gray-600);
                        border-bottom: 2px solid var(--gray-200);
                        text-align: center;
                    }
                    
                    thead th:first-child {
                        text-align: left;
                        padding-left: 24px;
                    }
                    
                    thead th .day-label {
                        display: block;
                        font-size: 14px;
                        font-weight: 700;
                        color: var(--gray-700);
                        margin-bottom: 2px;
                    }
                    
                    thead th .date-label {
                        display: block;
                        font-size: 11px;
                        font-weight: 500;
                        color: var(--gray-400);
                    }
                    
                    tbody tr {
                        border-bottom: 1px solid var(--gray-100);
                        transition: background 0.2s;
                    }
                    
                    tbody tr:nth-child(even) {
                        background: var(--gray-50);
                    }
                    
                    tbody tr:hover {
                        background: #f0f9ff;
                    }
                    
                    tbody td {
                        padding: 14px 12px;
                        text-align: center;
                        vertical-align: middle;
                    }
                    
                    .teacher-cell {
                        text-align: left !important;
                        padding-left: 24px !important;
                    }
                    
                    .teacher-info {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }
                    
                    .teacher-num {
                        width: 28px;
                        height: 28px;
                        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
                        color: white;
                        border-radius: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 12px;
                        font-weight: 700;
                    }
                    
                    .teacher-name {
                        font-weight: 600;
                        color: var(--gray-700);
                    }
                    
                    .has-report {
                        background: rgba(16, 185, 129, 0.05);
                    }
                    
                    .period-badge {
                        display: inline-block;
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white;
                        padding: 4px 10px;
                        border-radius: 20px;
                        font-size: 11px;
                        font-weight: 600;
                        margin: 2px;
                        white-space: nowrap;
                    }
                    
                    .no-report {
                        color: var(--gray-300);
                        font-size: 16px;
                    }
                    
                    .total-cell {
                        font-weight: 800;
                        font-size: 16px;
                        color: var(--primary);
                        background: rgba(79, 70, 229, 0.05);
                    }
                    
                    /* Footer */
                    .footer {
                        margin-top: 30px;
                        padding: 20px;
                        background: var(--gray-50);
                        border-radius: 12px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    
                    .footer-left {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        color: var(--gray-500);
                        font-size: 12px;
                    }
                    
                    .footer-right {
                        font-size: 12px;
                        color: var(--gray-400);
                    }
                    
                    .legend {
                        display: flex;
                        align-items: center;
                        gap: 16px;
                        font-size: 12px;
                        color: var(--gray-500);
                    }
                    
                    .legend-item {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }
                    
                    .legend-dot {
                        width: 12px;
                        height: 12px;
                        border-radius: 50%;
                    }
                    
                    .legend-dot.success { background: var(--success); }
                    .legend-dot.empty { background: var(--gray-200); }
                    
                    @media print {
                        body { 
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .page-container { padding: 20px; }
                        .header { 
                            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%) !important;
                            -webkit-print-color-adjust: exact !important;
                        }
                        .stat-card, .period-badge, .teacher-num, .total-cell, .has-report {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="page-container">
                    <div class="header">
                        <div class="header-content">
                            <h1>📚 สรุปรายงานการจัดการเรียนการสอนรายสัปดาห์</h1>
                            <p class="dept-name">กลุ่มสาระการเรียนรู้${this.department}</p>
                            <p class="date-range">📅 สัปดาห์ที่ ${weekStart} — ${weekEnd}</p>
                        </div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card primary">
                            <div class="stat-value">${totalTeachers}</div>
                            <div class="stat-label">👨‍🏫จำนวนครูในกลุ่มสาระ</div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-value">${totalReports}</div>
                            <div class="stat-label">📝รายงานการสอนทั้งหมด</div>
                        </div>
                        <div class="stat-card purple">
                            <div class="stat-value">${avgPerTeacher}</div>
                            <div class="stat-label">📊เฉลี่ยต่อคน</div>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <div class="table-header">
                            <span style="font-size:20px;">📋</span>
                            <h2>ตารางสรุปรายงานการสอนรายบุคคล</h2>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:220px;">
                                        <span class="day-label">ชื่อ-นามสกุล</span>
                                        <span class="date-label">ครูผู้สอน</span>
                                    </th>
                                    ${weekDates.map(d => `
                                        <th>
                                            <span class="day-label">${this.formatDayName(d)}</span>
                                            <span class="date-label">${this.formatThaiDate(d)}</span>
                                        </th>
                                    `).join('')}
                                    <th style="width:70px;">
                                        <span class="day-label">รวม</span>
                                        <span class="date-label">ทั้งสัปดาห์</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="footer">
                        <div class="legend">
                            <div class="legend-item">
                                <span class="legend-dot success"></span>
                                <span>มีรายงานการสอน</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot empty"></span>
                                <span>ไม่มีรายงาน</span>
                            </div>
                        </div>
                        <div class="footer-right">
                            🖨️ พิมพ์เมื่อ ${new Date().toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })}
                        </div>
                    </div>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 1000);
    }
}
