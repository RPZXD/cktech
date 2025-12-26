/**
 * Director Supervision JavaScript
 * Manages supervision evaluations for school administrators
 */

class DirectorSupervisionManager {
    constructor() {
        this.baseUrl = '../';
        this.supervisions = [];

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
        this.loadSupervisions();
    }

    bindEvents() {
        document.getElementById('departmentFilter')?.addEventListener('change', () => this.filterAndRender());
        document.getElementById('statusFilter')?.addEventListener('change', () => this.filterAndRender());
        document.getElementById('btnRefresh')?.addEventListener('click', () => this.loadSupervisions());
        document.getElementById('searchInput')?.addEventListener('input', (e) => this.searchSupervisions(e.target.value));
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

    async loadSupervisions() {
        this.showLoading();
        try {
            const res = await fetch(`${this.baseUrl}controllers/SupervisionController.php?action=list`);
            const data = await res.json();

            this.supervisions = data.data || data || [];
            this.updateStats();
            this.renderTable();
        } catch (error) {
            console.error('Error loading supervisions:', error);
            this.renderEmpty();
        } finally {
            this.hideLoading();
        }
    }

    updateStats() {
        const total = this.supervisions.length;
        const completed = this.supervisions.filter(s => s.total_score && parseFloat(s.total_score) > 0).length;
        const pending = total - completed;

        const scores = this.supervisions
            .filter(s => s.total_score && parseFloat(s.total_score) > 0)
            .map(s => parseFloat(s.total_score));
        const avgScore = scores.length > 0 ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : '-';

        document.getElementById('statTotal').textContent = total;
        document.getElementById('statPending').textContent = pending;
        document.getElementById('statCompleted').textContent = completed;
        document.getElementById('statAvgScore').textContent = avgScore;
    }

    filterAndRender() {
        this.renderTable();
    }

    searchSupervisions(query) {
        this.renderTable(query.toLowerCase());
    }

    getFilteredData(searchQuery = '') {
        const dept = document.getElementById('departmentFilter')?.value || '';
        const status = document.getElementById('statusFilter')?.value || '';

        return this.supervisions.filter(s => {
            const matchDept = !dept || s.department === dept;
            const isCompleted = s.total_score && parseFloat(s.total_score) > 0;
            const matchStatus = !status ||
                (status === 'completed' && isCompleted) ||
                (status === 'pending' && !isCompleted);
            const matchSearch = !searchQuery ||
                (s.teacher_name && s.teacher_name.toLowerCase().includes(searchQuery)) ||
                (s.department && s.department.toLowerCase().includes(searchQuery));

            return matchDept && matchStatus && matchSearch;
        });
    }

    renderTable(searchQuery = '') {
        const tbody = document.getElementById('supervisionBody');
        if (!tbody) return;

        const filtered = this.getFilteredData(searchQuery);

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="p-12 text-center text-slate-400">ไม่พบข้อมูลการนิเทศ</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(s => {
            const isCompleted = s.total_score && parseFloat(s.total_score) > 0;
            const score = isCompleted ? parseFloat(s.total_score).toFixed(1) : '-';
            const quality = this.getQualityLevel(parseFloat(s.total_score) || 0);

            return `
                <tr class="border-b border-slate-50 dark:border-slate-800 hover:bg-slate-50/50">
                    <td class="p-4 font-bold text-slate-700 dark:text-slate-300 text-xs">${this.formatThaiDate(s.supervision_date || s.created_at)}</td>
                    <td class="p-4">
                        <p class="font-bold text-slate-800 dark:text-white">${s.teacher_name || '-'}</p>
                    </td>
                    <td class="p-4 text-slate-500">${s.department || '-'}</td>
                    <td class="p-4 text-center font-black ${isCompleted ? 'text-purple-600' : 'text-slate-400'}">${score}</td>
                    <td class="p-4 text-center">
                        ${isCompleted ? `<span class="quality-badge ${quality.class}">${quality.text}</span>` : '<span class="text-slate-400">-</span>'}
                    </td>
                    <td class="p-4 text-center">
                        ${isCompleted
                    ? '<span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold"><i class="fas fa-check-circle"></i> ประเมินแล้ว</span>'
                    : '<span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold"><i class="fas fa-clock"></i> รอประเมิน</span>'
                }
                    </td>
                    <td class="p-4 text-center">
                        <button onclick="window.directorSupervisionManager.showDetail(${s.id})" class="w-8 h-8 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-xs transition-all">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    getQualityLevel(score) {
        if (score >= 90) return { text: 'ดีมาก', class: 'bg-emerald-100 text-emerald-700' };
        if (score >= 80) return { text: 'ดี', class: 'bg-blue-100 text-blue-700' };
        if (score >= 70) return { text: 'พอใช้', class: 'bg-amber-100 text-amber-700' };
        if (score >= 60) return { text: 'ปรับปรุง', class: 'bg-orange-100 text-orange-700' };
        return { text: 'ต้องพัฒนา', class: 'bg-rose-100 text-rose-700' };
    }

    showDetail(id) {
        const supervision = this.supervisions.find(s => s.id == id);
        if (!supervision) return;

        const modal = document.getElementById('evaluationModal');
        const content = document.getElementById('evaluationContent');

        if (!modal || !content) return;

        const score = supervision.total_score ? parseFloat(supervision.total_score).toFixed(1) : '-';
        const quality = this.getQualityLevel(parseFloat(supervision.total_score) || 0);

        content.innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">ครูผู้รับการนิเทศ</p>
                        <p class="font-black text-slate-800">${supervision.teacher_name || '-'}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">กลุ่มสาระ</p>
                        <p class="font-bold text-slate-700">${supervision.department || '-'}</p>
                    </div>
                </div>
                
                <div class="p-4 bg-purple-50 rounded-xl border border-purple-100 text-center">
                    <p class="text-[10px] font-black text-purple-500 uppercase mb-2">คะแนนรวม</p>
                    <p class="text-4xl font-black text-purple-700">${score}</p>
                    <p class="mt-2"><span class="quality-badge ${quality.class}">${quality.text}</span></p>
                </div>
                
                <div class="space-y-3">
                    <div class="p-3 bg-slate-50 rounded-lg flex justify-between items-center">
                        <span class="font-bold text-slate-600">ด้านการวางแผน</span>
                        <span class="font-black text-purple-600">${supervision.score_planning || '-'}</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg flex justify-between items-center">
                        <span class="font-bold text-slate-600">ด้านการจัดการเรียนรู้</span>
                        <span class="font-black text-purple-600">${supervision.score_teaching || '-'}</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg flex justify-between items-center">
                        <span class="font-bold text-slate-600">ด้านการวัดและประเมินผล</span>
                        <span class="font-black text-purple-600">${supervision.score_evaluation || '-'}</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg flex justify-between items-center">
                        <span class="font-bold text-slate-600">ด้านบรรยากาศการเรียนรู้</span>
                        <span class="font-black text-purple-600">${supervision.score_atmosphere || '-'}</span>
                    </div>
                </div>
                
                ${supervision.suggestions ? `
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                        <p class="text-[10px] font-black text-amber-500 uppercase mb-2">ข้อเสนอแนะ</p>
                        <p class="text-sm text-slate-700">${supervision.suggestions}</p>
                    </div>
                ` : ''}
            </div>
        `;

        modal.classList.remove('hidden');
    }

    renderEmpty() {
        const tbody = document.getElementById('supervisionBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" class="p-12 text-center text-slate-400">ไม่พบข้อมูลการนิเทศ</td></tr>`;
        }
    }

    formatThaiDate(dateStr) {
        if (!dateStr) return '-';
        const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
    }

    showLoading() {
        Swal.fire({ title: 'กำลังโหลดข้อมูล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }

    hideLoading() {
        Swal.close();
    }
}

// Initialize
window.directorSupervisionManager = new DirectorSupervisionManager();
