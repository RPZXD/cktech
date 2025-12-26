/**
 * Director Weekly Report JavaScript
 * Manages weekly aggregation dashboard for school administrators
 */

class DirectorWeeklyManager {
    constructor() {
        this.baseUrl = '../';
        this.currentWeekStart = this.getWeekStart(new Date());
        this.chart = null;

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
        this.loadWeekData();
        this.updateWeekDisplay();
    }

    getWeekStart(date) {
        const d = new Date(date);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        return new Date(d.setDate(diff));
    }

    bindEvents() {
        document.getElementById('prevWeek')?.addEventListener('click', () => {
            this.currentWeekStart.setDate(this.currentWeekStart.getDate() - 7);
            this.updateWeekDisplay();
            this.loadWeekData();
        });

        document.getElementById('nextWeek')?.addEventListener('click', () => {
            this.currentWeekStart.setDate(this.currentWeekStart.getDate() + 7);
            this.updateWeekDisplay();
            this.loadWeekData();
        });

        document.getElementById('departmentFilter')?.addEventListener('change', () => {
            this.loadWeekData();
        });

        document.getElementById('btnPrint')?.addEventListener('click', () => {
            window.print();
        });
    }

    async loadDepartments() {
        try {
            const res = await fetch(`${this.baseUrl}controllers/DepartmentController.php?action=list`);
            const data = await res.json();

            const select = document.getElementById('departmentFilter');
            if (!select) return;

            data.forEach(d => {
                select.innerHTML += `<option value="${d.name}">${d.name}</option>`;
            });
        } catch (error) {
            console.error('Error loading departments:', error);
        }
    }

    updateWeekDisplay() {
        const start = new Date(this.currentWeekStart);
        const end = new Date(start);
        end.setDate(end.getDate() + 4);

        const weekNum = this.getWeekNumber(start);
        const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        document.getElementById('weekDisplay').textContent = `สัปดาห์ที่ ${weekNum}`;
        document.getElementById('weekRange').textContent =
            `${start.getDate()} ${months[start.getMonth()]} - ${end.getDate()} ${months[end.getMonth()]} ${end.getFullYear() + 543}`;
    }

    getWeekNumber(date) {
        const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
        const pastDaysOfYear = (date - firstDayOfYear) / 86400000;
        return Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
    }

    async loadWeekData() {
        // Demo - in production, fetch actual data
        this.updateStats({
            total: Math.floor(Math.random() * 200) + 50,
            teachers: Math.floor(Math.random() * 30) + 10,
            subjects: Math.floor(Math.random() * 15) + 5,
            rooms: Math.floor(Math.random() * 20) + 5
        });

        this.renderGrid([
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10
        ]);

        this.renderChart([
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10,
            Math.floor(Math.random() * 50) + 10
        ]);
    }

    updateStats(stats) {
        document.getElementById('weekTotal').textContent = stats.total;
        document.getElementById('weekTeachers').textContent = stats.teachers;
        document.getElementById('weekSubjects').textContent = stats.subjects;
        document.getElementById('weekRooms').textContent = stats.rooms;
    }

    renderGrid(dayData) {
        const grid = document.getElementById('weeklyGrid');
        if (!grid) return;

        const colors = [
            { bg: 'bg-blue-50', text: 'text-blue-600', badge: 'bg-blue-100' },
            { bg: 'bg-pink-50', text: 'text-pink-600', badge: 'bg-pink-100' },
            { bg: 'bg-emerald-50', text: 'text-emerald-600', badge: 'bg-emerald-100' },
            { bg: 'bg-amber-50', text: 'text-amber-600', badge: 'bg-amber-100' },
            { bg: 'bg-purple-50', text: 'text-purple-600', badge: 'bg-purple-100' }
        ];

        grid.innerHTML = dayData.map((count, i) => `
            <div class="day-cell p-4 ${colors[i].bg}">
                <div class="text-center">
                    <p class="text-3xl font-black ${colors[i].text}">${count}</p>
                    <p class="text-xs font-bold text-slate-500 mt-1">รายการ</p>
                    <div class="mt-3 flex flex-wrap justify-center gap-1">
                        ${Array(Math.min(count, 5)).fill(0).map(() =>
            `<span class="period-badge ${colors[i].badge} ${colors[i].text}">P${Math.floor(Math.random() * 8) + 1}</span>`
        ).join('')}
                        ${count > 5 ? `<span class="period-badge bg-slate-100 text-slate-500">+${count - 5}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }

    renderChart(dayData) {
        const ctx = document.getElementById('weeklyChart')?.getContext('2d');
        if (!ctx) return;

        if (this.chart) this.chart.destroy();

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'],
                datasets: [{
                    label: 'จำนวนรายงาน',
                    data: dayData,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
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
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                }
            }
        });
    }
}

// Initialize
window.directorWeeklyManager = new DirectorWeeklyManager();
