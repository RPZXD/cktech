/**
 * Director Statistics JavaScript
 * Manages analytics dashboard for school administrators
 */

class DirectorStatManager {
    constructor() {
        this.baseUrl = '../';
        this.charts = {};
        this.startDate = moment().subtract(30, 'days');
        this.endDate = moment();

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
        this.initDateRangePicker();
        this.bindEvents();
        this.loadAllData();
    }

    initDateRangePicker() {
        const picker = $('#dateRange');
        if (!picker.length) return;

        picker.daterangepicker({
            startDate: this.startDate,
            endDate: this.endDate,
            locale: {
                format: 'D MMM YYYY',
                applyLabel: 'ตกลง',
                cancelLabel: 'ยกเลิก',
                customRangeLabel: 'กำหนดเอง',
                daysOfWeek: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                monthNames: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.']
            },
            ranges: {
                'วันนี้': [moment(), moment()],
                'สัปดาห์นี้': [moment().startOf('week'), moment().endOf('week')],
                'เดือนนี้': [moment().startOf('month'), moment().endOf('month')],
                '30 วันที่ผ่านมา': [moment().subtract(30, 'days'), moment()],
                'ภาคเรียนนี้': [moment().startOf('month').subtract(3, 'months'), moment()]
            }
        }, (start, end) => {
            this.startDate = start;
            this.endDate = end;
        });
    }

    bindEvents() {
        document.getElementById('btnRefresh')?.addEventListener('click', () => this.loadAllData());
    }

    async loadAllData() {
        this.showLoading();
        try {
            await Promise.all([
                this.loadOverviewStats(),
                this.loadDepartmentChart(),
                this.loadWeeklyTrendChart(),
                this.loadQualityChart(),
                this.loadCertTypeChart(),
                this.loadTopTeachers()
            ]);
        } catch (error) {
            console.error('Error loading data:', error);
        } finally {
            this.hideLoading();
        }
    }

    async loadOverviewStats() {
        try {
            const response = await fetch(`${this.baseUrl}controllers/StatController.php?action=directorOverview`);
            const data = await response.json();

            document.getElementById('totalReports').textContent = this.formatNumber(data.totalReports || 0);
            document.getElementById('totalTeachers').textContent = this.formatNumber(data.totalTeachers || 0);
            document.getElementById('totalSupervisions').textContent = this.formatNumber(data.totalSupervisions || 0);
            document.getElementById('totalCertificates').textContent = this.formatNumber(data.totalCertificates || 0);
        } catch (error) {
            console.error('Error loading overview stats:', error);
            document.getElementById('totalReports').textContent = '0';
            document.getElementById('totalTeachers').textContent = '0';
            document.getElementById('totalSupervisions').textContent = '0';
            document.getElementById('totalCertificates').textContent = '0';
        }
    }

    formatNumber(num) {
        return new Intl.NumberFormat('th-TH').format(num);
    }

    async loadDepartmentChart() {
        const ctx = document.getElementById('chartDepartments')?.getContext('2d');
        if (!ctx) return;

        if (this.charts.departments) this.charts.departments.destroy();

        // Demo data - replace with actual API call
        const departments = ['วิทยาศาสตร์', 'คณิตศาสตร์', 'ภาษาไทย', 'ภาษาต่างประเทศ', 'สังคมศึกษา', 'ศิลปะ', 'สุขศึกษา', 'การงานอาชีพ'];
        const values = departments.map(() => Math.floor(Math.random() * 100) + 20);

        this.charts.departments = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: departments,
                datasets: [{
                    label: 'จำนวนรายงาน',
                    data: values,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(244, 63, 94, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(234, 179, 8, 0.8)'
                    ],
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        beginAtZero: true,
                        min: 0,
                        max: 150,
                        grace: 0,
                        ticks: {
                            stepSize: 30
                        }
                    }
                }
            }
        });
    }

    async loadWeeklyTrendChart() {
        const ctx = document.getElementById('chartWeekly')?.getContext('2d');
        if (!ctx) return;

        if (this.charts.weekly) this.charts.weekly.destroy();

        const weeks = ['สัปดาห์ 1', 'สัปดาห์ 2', 'สัปดาห์ 3', 'สัปดาห์ 4'];
        const values = weeks.map(() => Math.floor(Math.random() * 150) + 50);

        this.charts.weekly = new Chart(ctx, {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [{
                    label: 'จำนวนรายงาน',
                    data: values,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'white',
                    pointBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        beginAtZero: true,
                        min: 0,
                        max: 250,
                        grace: 0,
                        ticks: {
                            stepSize: 50
                        }
                    }
                }
            }
        });
    }

    async loadQualityChart() {
        const ctx = document.getElementById('chartQuality')?.getContext('2d');
        if (!ctx) return;

        if (this.charts.quality) this.charts.quality.destroy();

        this.charts.quality = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['ดีมาก', 'ดี', 'พอใช้', 'ต้องปรับปรุง'],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { weight: 'bold', size: 12 }, padding: 20 }
                    }
                }
            }
        });
    }

    async loadCertTypeChart() {
        const ctx = document.getElementById('chartCertTypes')?.getContext('2d');
        if (!ctx) return;

        if (this.charts.certTypes) this.charts.certTypes.destroy();

        this.charts.certTypes = new Chart(ctx, {
            type: 'polarArea',
            data: {
                labels: ['วิชาการ', 'กีฬา', 'ดนตรี', 'ศิลปะ', 'อื่นๆ'],
                datasets: [{
                    data: [35, 25, 15, 15, 10],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(244, 63, 94, 0.7)',
                        'rgba(100, 116, 139, 0.7)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { weight: 'bold', size: 12 }, padding: 15 }
                    }
                }
            }
        });
    }

    async loadTopTeachers() {
        const tbody = document.getElementById('topTeachersBody');
        if (!tbody) return;

        try {
            const start = this.startDate.format('YYYY-MM-DD');
            const end = this.endDate.format('YYYY-MM-DD');
            const response = await fetch(`${this.baseUrl}controllers/StatController.php?action=reportByTeacher&start=${start}&end=${end}`);
            const data = await response.json();

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-slate-400">ไม่พบข้อมูลในช่วงเวลาที่เลือก</td></tr>';
                return;
            }

            // Take top 10 teachers
            const topTeachers = data.slice(0, 10);
            const maxCount = topTeachers[0]?.count || 1;

            tbody.innerHTML = topTeachers.map((t, i) => `
                <tr class="border-b border-slate-50 dark:border-slate-800 hover:bg-slate-50/50">
                    <td class="p-4">
                        <span class="w-8 h-8 inline-flex items-center justify-center ${i === 0 ? 'bg-amber-100 text-amber-600' : i === 1 ? 'bg-slate-200 text-slate-600' : i === 2 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500'} rounded-lg font-black text-sm">
                            ${i + 1}
                        </span>
                    </td>
                    <td class="p-4 font-bold text-slate-800 dark:text-white">${t.teacher || '-'}</td>
                    <td class="p-4 text-slate-500">${t.department || '-'}</td>
                    <td class="p-4 text-center font-black text-indigo-600">${t.count}</td>
                    <td class="p-4 text-center">
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-indigo-500 h-full rounded-full" style="width: ${Math.round((t.count / maxCount) * 100)}%"></div>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Error loading top teachers:', error);
            tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-red-400">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
        }
    }

    showLoading() {
        Swal.fire({ title: 'กำลังโหลดข้อมูล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }

    hideLoading() {
        Swal.close();
    }
}

// Initialize
window.directorStatManager = new DirectorStatManager();
