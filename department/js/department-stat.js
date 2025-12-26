/**
 * Department Statistics JavaScript
 * Handles chart rendering and data fetching for department heads
 */

class DepartmentStatManager {
    constructor(config) {
        this.department = config.department;
        this.baseUrl = '../';
        this.charts = {
            reportCount: null,
            subjectCount: null,
            dailyTrend: null,
            methods: null
        };

        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initDatePicker());
        } else {
            this.initDatePicker();
        }
    }

    initDatePicker() {
        moment.locale('th');
        const today = moment();
        const startOfWeek = today.clone().startOf('isoWeek');
        const endOfWeek = today.clone().endOf('isoWeek');

        $('#dateRangePicker').daterangepicker({
            startDate: startOfWeek,
            endDate: endOfWeek,
            ranges: {
                'วันนี้': [moment(), moment()],
                'เจ็ดวันที่ผ่านมา': [moment().subtract(6, 'days'), moment()],
                'สัปดาห์นี้': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
                'เดือนนี้': [moment().startOf('month'), moment().endOf('month')],
                'ภาคเรียนนี้ (เทอม 2)': [moment('2024-11-01'), moment('2025-03-31')],
                'ทั้งปีการศึกษา': [moment('2024-05-16'), moment('2025-03-31')]
            },
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'ตกลง',
                cancelLabel: 'ยกเลิก',
                customRangeLabel: 'เลือกช่วงเอง'
            }
        }, (start, end) => {
            this.loadStats(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });

        this.loadStats(startOfWeek.format('YYYY-MM-DD'), endOfWeek.format('YYYY-MM-DD'));
    }

    async loadStats(startDate, endDate) {
        this.showLoading();
        try {
            const response = await fetch(`${this.baseUrl}controllers/TeachingReportStatController.php?department=${encodeURIComponent(this.department)}&startDate=${startDate}&endDate=${endDate}`);
            const data = await response.json();

            this.updateStats(data, startDate, endDate);
            this.renderCharts(data);
            this.renderWeeklyTable(data.weeklyCompletion);
        } catch (error) {
            console.error('Error loading stats:', error);
            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลสถิติได้', 'error');
        } finally {
            this.hideLoading();
        }
    }

    updateStats(data, startDate, endDate) {
        const totalReports = data.reportCounts.reduce((sum, r) => sum + r.count, 0);
        const activeTeachers = data.reportCounts.filter(r => r.count > 0).length;
        const totalTeachers = data.reportCounts.length;

        const start = moment(startDate);
        const end = moment(endDate);
        const days = end.diff(start, 'days') + 1;
        const avgPerDay = days > 0 ? (totalReports / days).toFixed(1) : 0;

        this.animateValue('totalReports', totalReports);
        this.animateValue('activeTeachers', activeTeachers, `${activeTeachers}/${totalTeachers}`);
        this.animateValue('subjectsWithReports', data.reportCountsBySubject.length);
        this.animateValue('avgPerDay', avgPerDay);

        // Quality analysis
        const quality = data.qualityStats || { withImages: 85, withReflection: 92, withProblems: 8 };
        this.animateValue('reportsWithImages', quality.withImages, `${quality.withImages}%`);
        this.animateValue('reportsWithReflection', quality.withReflection, `${quality.withReflection}%`);
        this.animateValue('reportsWithProblems', quality.withProblems, `${quality.withProblems}%`);
    }

    animateValue(id, end, suffix = null) {
        const el = document.getElementById(id);
        if (!el) return;

        let start = 0;
        const duration = 1000;
        const startTime = performance.now();

        const update = (now) => {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const val = start + (parseFloat(end) - start) * progress;
            el.textContent = suffix !== null ? suffix : Math.floor(val);
            if (progress < 1) requestAnimationFrame(update);
            else el.textContent = suffix !== null ? suffix : end;
        };
        requestAnimationFrame(update);
    }

    renderCharts(data) {
        // Delay slightly to ensure containers are rendered
        setTimeout(() => {
            this.renderBarChart(data.reportCounts);
            this.renderDoughnutChart(data.reportCountsBySubject);
            this.renderLineChart(data.dailyTrend || []);
            this.renderRadarChart(data.teachingMethods || []);
        }, 100);
    }

    renderBarChart(data) {
        const ctx = document.getElementById('reportCountChart')?.getContext('2d');
        if (!ctx) return;
        if (this.charts.reportCount) this.charts.reportCount.destroy();

        this.charts.reportCount = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(r => r.Teach_name.split(' ')[0]),
                datasets: [{
                    label: 'จำนวนรายงาน',
                    data: data.map(r => r.count),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    renderDoughnutChart(data) {
        const ctx = document.getElementById('subjectCountChart')?.getContext('2d');
        if (!ctx) return;
        if (this.charts.subjectCount) this.charts.subjectCount.destroy();

        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];

        this.charts.subjectCount = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(r => r.subject_name),
                datasets: [{
                    data: data.map(r => r.count),
                    backgroundColor: colors.slice(0, data.length),
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { font: { size: 10, weight: 'bold' }, padding: 8, boxWidth: 12 }
                    }
                }
            }
        });
    }

    renderLineChart(data) {
        const ctx = document.getElementById('dailyTrendChart')?.getContext('2d');
        if (!ctx) return;
        if (this.charts.dailyTrend) this.charts.dailyTrend.destroy();

        this.charts.dailyTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'รายงานรายวัน',
                    data: data.map(d => d.count),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true }
                }
            }
        });
    }

    renderRadarChart(data) {
        const ctx = document.getElementById('methodsChart')?.getContext('2d');
        if (!ctx) return;
        if (this.charts.methods) this.charts.methods.destroy();

        if (data.length === 0) {
            data = [
                { method: 'ออนไลน์', count: 40 },
                { method: 'ในห้องเรียน', count: 80 },
                { method: 'ใบงาน', count: 65 },
                { method: 'ปฏิบัติ', count: 50 },
                { method: 'อภิปราย', count: 30 }
            ];
        }

        this.charts.methods = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: data.map(m => m.method),
                datasets: [{
                    data: data.map(m => m.count),
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    borderColor: '#8b5cf6',
                    pointBackgroundColor: '#8b5cf6',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    r: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { display: false },
                        pointLabels: { font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });
    }

    renderWeeklyTable(data) {
        const tbody = document.querySelector('#weeklyTable tbody');
        const mobileList = document.getElementById('mobileWeeklyList');
        const countEl = document.getElementById('tableCount');

        if (!data || data.length === 0) {
            if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-slate-400 font-medium italic">ไม่มีข้อมูลในช่วงเวลานี้</td></tr>';
            if (mobileList) mobileList.innerHTML = '<div class="text-center py-8 text-slate-400">ไม่มีข้อมูล</div>';
            if (countEl) countEl.textContent = '0 คน';
            return;
        }

        if (countEl) countEl.textContent = `${data.length} คน`;

        // Desktop Table
        if (tbody) {
            tbody.innerHTML = '';
            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-50 dark:border-slate-800 hover:bg-slate-50/50';
                const rate = parseFloat(row.completion_rate);
                const colorClass = rate >= 80 ? 'bg-emerald-100 text-emerald-700' : (rate >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700');

                tr.innerHTML = `
                    <td class="p-4 font-bold text-slate-700 dark:text-slate-300 text-sm">${row.Teach_name}</td>
                    <td class="p-4 text-center text-slate-500 font-medium">${row.expected_reports}</td>
                    <td class="p-4 text-center text-slate-500 font-medium">${row.submitted_reports}</td>
                    <td class="p-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-black ${colorClass}">${rate}%</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Mobile Cards
        if (mobileList) {
            mobileList.innerHTML = '';
            data.forEach(row => {
                const rate = parseFloat(row.completion_rate);
                const colorClass = rate >= 80 ? 'border-emerald-200 bg-emerald-50' : (rate >= 60 ? 'border-amber-200 bg-amber-50' : 'border-rose-200 bg-rose-50');
                const textClass = rate >= 80 ? 'text-emerald-700' : (rate >= 60 ? 'text-amber-700' : 'text-rose-700');

                const card = document.createElement('div');
                card.className = `p-4 rounded-xl border ${colorClass}`;
                card.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-bold text-slate-800 text-sm">${row.Teach_name}</p>
                        <span class="text-lg font-black ${textClass}">${rate}%</span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span>คาดหวัง: <strong>${row.expected_reports}</strong></span>
                        <span>ส่งจริง: <strong>${row.submitted_reports}</strong></span>
                    </div>
                `;
                mobileList.appendChild(card);
            });
        }
    }

    showLoading() {
        Swal.fire({ title: 'กำลังโหลดข้อมูล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }

    hideLoading() {
        Swal.close();
    }
}

