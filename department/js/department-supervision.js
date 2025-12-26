/**
 * Department Supervision JavaScript
 * Handles supervision management for department heads
 */

class DepartmentSupervisionManager {
    constructor(config) {
        this.department = config.department;
        this.baseUrl = '../';
        this.currentSupervisionId = null;
        this.supervisions = [];

        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.bindEvents();
            this.loadSupervisions();
        });
    }

    bindEvents() {
        // Filters
        ['filterYear', 'filterMonth', 'filterRound'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', () => this.filterData());
        });

        // Search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterData());
        }

        // Calculate score in modal
        const btnCalc = document.getElementById('calculateDeptScore');
        if (btnCalc) {
            btnCalc.addEventListener('click', () => this.calculateDeptScore());
        }

        // Form submission
        const form = document.getElementById('formSupervision');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Modal close
        const closeModals = ['closeModalSupervision', 'cancelSupervision'];
        closeModals.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', () => this.closeModal());
        });
    }

    async loadSupervisions() {
        this.showLoading('กำลังโหลดข้อมูลการนิเทศ...');
        try {
            const response = await fetch(`${this.baseUrl}controllers/SupervisionController.php?action=list_by_department&department=${encodeURIComponent(this.department)}`);
            this.supervisions = await response.json();
            this.renderTable(this.supervisions);
            this.updateStats(this.supervisions);
        } catch (error) {
            console.error('Error loading supervisions:', error);
            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลการนิเทศได้', 'error');
        } finally {
            this.hideLoading();
        }
    }

    renderTable(data) {
        const tbody = document.getElementById('supervisionTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="p-12 text-center text-slate-400 font-bold italic">ไม่พบข้อมูลการนิเทศที่ค้นหา</td></tr>';
            return;
        }

        data.forEach((s, index) => {
            const displayName = s.teacher_full_name || s.teacher_name;
            const isEvaluated = s.dept_score !== null && s.dept_score !== '';

            const tr = document.createElement('tr');
            tr.className = 'group hover:bg-blue-50/50 transition-all border-b border-slate-100 dark:border-slate-800';
            tr.innerHTML = `
                <td class="p-4 text-center text-xs font-bold text-slate-400">${index + 1}</td>
                <td class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                            ${displayName.charAt(0)}
                        </div>
                        <div>
                            <p class="font-black text-slate-800 dark:text-white">${displayName}</p>
                            <p class="text-xs text-slate-400">${s.subject_name || '-'}</p>
                        </div>
                    </div>
                </td>
                <td class="p-4 text-center">
                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-xs font-black text-slate-600 dark:text-slate-400">
                        ม.${s.class_level || '-'}
                    </span>
                </td>
                <td class="p-4 text-center">
                    <span class="text-xs font-bold text-slate-500">${this.formatThaiDate(s.supervision_date)}</span>
                </td>
                <td class="p-4 text-center">
                    <div class="flex flex-col items-center">
                        <span class="text-lg font-black text-blue-600">${s.total_score || '0'}</span>
                        <span class="text-[10px] uppercase font-black text-slate-300">Teacher Score</span>
                    </div>
                </td>
                <td class="p-4 text-center">
                    ${isEvaluated ? `
                        <div class="flex flex-col items-center">
                            <span class="text-lg font-black text-purple-600">${s.dept_score}</span>
                            <span class="text-[10px] uppercase font-black text-purple-300">Evaluated</span>
                        </div>
                    ` : `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-600 rounded-xl text-[10px] font-black uppercase ring-1 ring-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Pending
                        </span>
                    `}
                </td>
                <td class="p-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="window.manager.openEvaluateModal(${s.id})" class="p-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-500/20 active:scale-95 transition-all group/btn" title="ประเมิน">
                            <i class="fas fa-edit group-hover/btn:rotate-12 transition-transform"></i>
                        </button>
                        <button onclick="window.manager.printSupervision(${s.id})" class="p-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-500/20 active:scale-95 transition-all" title="พิมพ์">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    updateStats(data) {
        const total = data.length;
        const evaluated = data.filter(s => s.dept_score !== null && s.dept_score !== '').length;
        const pending = total - evaluated;
        const avgScore = evaluated > 0 ? (data.reduce((acc, s) => acc + (parseFloat(s.dept_score) || 0), 0) / evaluated).toFixed(1) : 0;

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        setVal('statTotal', total);
        setVal('statEvaluated', evaluated);
        setVal('statPending', pending);
        setVal('statAvgScore', avgScore);
    }

    filterData() {
        const year = document.getElementById('filterYear')?.value;
        const month = document.getElementById('filterMonth')?.value;
        const round = document.getElementById('filterRound')?.value;
        const search = document.getElementById('searchInput')?.value.toLowerCase();

        const filtered = this.supervisions.filter(s => {
            const matchYear = !year || s.academic_year === year;
            const matchMonth = !month || (s.supervision_date && s.supervision_date.split('-')[1] === month.padStart(2, '0'));
            const matchRound = !round || s.supervision_round == round;
            const matchSearch = !search ||
                (s.teacher_full_name && s.teacher_full_name.toLowerCase().includes(search)) ||
                (s.teacher_name && s.teacher_name.toLowerCase().includes(search)) ||
                (s.subject_name && s.subject_name.toLowerCase().includes(search));

            return matchYear && matchMonth && matchRound && matchSearch;
        });

        this.renderTable(filtered);
    }

    openEvaluateModal(id) {
        const s = this.supervisions.find(item => item.id == id);
        if (!s) return;

        this.currentSupervisionId = id;
        this.resetModal();
        this.populateTeacherInfo(s);
        this.populateDepartmentEvaluation(s);

        document.getElementById('modalSupervision')?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    closeModal() {
        document.getElementById('modalSupervision')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    resetModal() {
        const form = document.getElementById('formSupervision');
        if (form) form.reset();

        // Reset custom radio selections if any or hidden fields
        document.getElementById('deptScore').value = '';
        document.getElementById('deptQualityLevel').value = '';
    }

    populateTeacherInfo(s) {
        const displayName = s.teacher_full_name || s.teacher_name;
        document.getElementById('modalTeacherName').textContent = displayName;
        document.getElementById('modalSubjectInfo').textContent = `${s.subject_name || '-'} (ม.${s.class_level || '-'})`;
        document.getElementById('modalDateInfo').textContent = this.formatThaiDate(s.supervision_date);

        // Detailed scores from teacher self-eval if needed
        const teacherScoreDiv = document.getElementById('teacherEvaluationSummary');
        if (teacherScoreDiv) {
            teacherScoreDiv.innerHTML = `
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100">
                    <div class="text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Planning</p>
                        <p class="font-bold text-blue-600">${s.plan_score || 0}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Teaching</p>
                        <p class="font-bold text-emerald-600">${s.teach_score || 0}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Evaluation</p>
                        <p class="font-bold text-purple-600">${s.eval_score || 0}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Environment</p>
                        <p class="font-bold text-orange-600">${s.env_score || 0}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between px-2">
                    <span class="text-sm font-bold text-slate-500 italic">คุณภาพ: <span class="text-slate-800 dark:text-white">${s.quality_level || '-'}</span></span>
                    <span class="text-sm font-black text-blue-600">รวม ${s.total_score || 0} คะแนน</span>
                </div>
            `;
        }

        // Teacher Documents
        const docsDiv = document.getElementById('teacherDocuments');
        if (docsDiv) {
            let html = '<div class="space-y-3">';
            if (s.lesson_plan) {
                html += `<a href="${this.baseUrl}${s.lesson_plan}" target="_blank" class="flex items-center gap-3 p-3 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 transition-colors">
                            <i class="fas fa-file-pdf text-xl"></i>
                            <span class="text-xs font-bold">แผนการจัดการเรียนรู้ / เอกสารประกอบ</span>
                         </a>`;
            }
            if (s.supervisor_photos) {
                html += '<div><p class="text-[10px] font-black text-slate-400 uppercase mb-2">ภาพการนิเทศ</p><div class="flex flex-wrap gap-2">';
                s.supervisor_photos.split(',').forEach(p => {
                    if (p.trim()) html += `<img src="${this.baseUrl}${p.trim()}" class="w-16 h-16 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity" onclick="window.manager.showImage('${this.baseUrl}${p.trim()}')">`;
                });
                html += '</div></div>';
            }
            html += '</div>';
            docsDiv.innerHTML = html;
        }
    }

    populateDepartmentEvaluation(s) {
        const deptFields = [
            'dept_plan_effective', 'dept_plan_correct', 'dept_plan_activities', 'dept_plan_media', 'dept_plan_assessment',
            'dept_teach_techniques', 'dept_teach_media', 'dept_teach_assessment', 'dept_teach_explanation', 'dept_teach_control',
            'dept_teach_thinking', 'dept_teach_adaptation', 'dept_teach_integration', 'dept_teach_language',
            'dept_eval_variety', 'dept_eval_standards', 'dept_eval_criteria', 'dept_eval_feedback', 'dept_eval_evidence',
            'dept_env_classroom', 'dept_env_interaction', 'dept_env_safety', 'dept_env_management', 'dept_env_rules', 'dept_env_behavior'
        ];

        deptFields.forEach(field => {
            if (s[field]) {
                const radio = document.querySelector(`input[name="${field}"][value="${s[field]}"]`);
                if (radio) radio.checked = true;
            }
        });

        const textFields = ['dept_observation_notes', 'dept_strengths', 'dept_suggestion'];
        textFields.forEach(field => {
            const el = document.querySelector(`textarea[name="${field}"]`);
            if (el) el.value = s[field] || '';
        });

        document.getElementById('deptScore').value = s.dept_score || '';
        document.getElementById('deptQualityLevel').value = s.dept_quality_level || '';
    }

    calculateDeptScore() {
        const deptFields = [
            'dept_plan_effective', 'dept_plan_correct', 'dept_plan_activities', 'dept_plan_media', 'dept_plan_assessment',
            'dept_teach_techniques', 'dept_teach_media', 'dept_teach_assessment', 'dept_teach_explanation', 'dept_teach_control',
            'dept_teach_thinking', 'dept_teach_adaptation', 'dept_teach_integration', 'dept_teach_language',
            'dept_eval_variety', 'dept_eval_standards', 'dept_eval_criteria', 'dept_eval_feedback', 'dept_eval_evidence',
            'dept_env_classroom', 'dept_env_interaction', 'dept_env_safety', 'dept_env_management', 'dept_env_rules', 'dept_env_behavior'
        ];

        let totalScore = 0;
        let filledCount = 0;

        deptFields.forEach(name => {
            const checked = document.querySelector(`input[name="${name}"]:checked`);
            if (checked) {
                totalScore += parseInt(checked.value);
                filledCount++;
            }
        });

        if (filledCount < deptFields.length) {
            Swal.fire('ประเมินไม่ครบ', `กรุณาให้คะแนนให้ครบทุกข้อ (ประเมินแล้ว ${filledCount}/${deptFields.length})`, 'warning');
            return;
        }

        let quality = '';
        if (totalScore >= 98) quality = 'ดีเยี่ยม';
        else if (totalScore >= 74) quality = 'ดีมาก';
        else if (totalScore >= 50) quality = 'ดี';
        else if (totalScore >= 26) quality = 'พอใช้';
        else quality = 'ควรปรับปรุง';

        document.getElementById('deptScore').value = totalScore;
        document.getElementById('deptQualityLevel').value = quality;

        Swal.fire({
            title: 'คำนวณสำเร็จ',
            text: `คะแนนรวม: ${totalScore} (${quality})`,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (!document.getElementById('deptScore').value) {
            this.calculateDeptScore();
            if (!document.getElementById('deptScore').value) return;
        }

        this.showLoading('กำลังบันทึกการประเมิน...');

        try {
            const formData = new FormData(e.target);
            formData.append('id', this.currentSupervisionId);
            formData.append('evaluator_type', 'department');

            const response = await fetch(`${this.baseUrl}controllers/SupervisionController.php?action=department_evaluate`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                Swal.fire('สำเร็จ', 'บันทึกการประเมินเรียบร้อยแล้ว', 'success');
                this.closeModal();
                this.loadSupervisions();
            } else {
                throw new Error(result.message || 'เกิดข้อผิดพลาดในการบันทึก');
            }
        } catch (error) {
            console.error('Error submitting evaluation:', error);
            Swal.fire('ข้อผิดพลาด', error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    printSupervision(id) {
        window.open(`print_supervision.php?id=${id}`, '_blank');
    }

    showImage(src) {
        Swal.fire({
            imageUrl: src,
            imageWidth: '100%',
            imageAlt: 'Room Image',
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'rounded-3xl overflow-hidden' }
        });
    }

    formatThaiDate(dateStr) {
        if (!dateStr) return '-';
        const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
    }

    showLoading(title = 'กำลังโหลด...') {
        Swal.fire({
            title: title,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }

    hideLoading() {
        Swal.close();
    }
}
