/**
 * Supervision Page JavaScript
 * MVC Pattern - Teaching supervision management
 */

document.addEventListener('DOMContentLoaded', function () {
    const config = window.SUPERVISION_CONFIG || {};

    // Evaluation criteria - must match controller field names
    const evaluationCriteria = {
        section1: [ // ด้านการจัดทำแผน (plan_*) - 5 ข้อ
            { name: 'plan_effective', label: 'การวางแผนการสอนที่มีประสิทธิภาพ' },
            { name: 'plan_correct', label: 'แผนการจัดการเรียนรู้ถูกต้อง เป็นขั้นตอน และครบองค์ประกอบ' },
            { name: 'plan_activities', label: 'แผนการจัดการเรียนรู้มีกิจกรรมที่ทำให้นักเรียนเกิดการเรียนรู้' },
            { name: 'plan_media', label: 'มีการจัดหาสื่อที่เหมาะสมกับการเรียนรู้' },
            { name: 'plan_assessment', label: 'มีการวัดและประเมินผลที่เหมาะสม' }
        ],
        section2: [ // ด้านการจัดการเรียนรู้ (teach_*) - 9 ข้อ
            { name: 'teach_techniques', label: 'ใช้เทคนิคต่างๆ ที่ทำให้นักเรียนมีส่วนร่วม' },
            { name: 'teach_media', label: 'เลือกใช้สื่อ เทคโนโลยีที่เหมาะสม' },
            { name: 'teach_assessment', label: 'มีการประเมินนักเรียนระหว่างเรียน' },
            { name: 'teach_explanation', label: 'อธิบายเนื้อหาได้อย่างชัดเจน' },
            { name: 'teach_control', label: 'มีความสามารถในการควบคุมชั้นเรียน' },
            { name: 'teach_thinking', label: 'มีกิจกรรมที่เน้นการพัฒนาการคิด' },
            { name: 'teach_adaptation', label: 'มีการปรับเนื้อหาตามสถานการณ์' },
            { name: 'teach_integration', label: 'มีการบูรณาการกับชีวิตประจำวัน' },
            { name: 'teach_language', label: 'ใช้ภาษาพูดและเขียนได้ถูกต้อง' }
        ],
        section3: [ // ด้านการประเมินผล (eval_*) - 5 ข้อ
            { name: 'eval_variety', label: 'วัดและประเมินผลด้วยวิธีการที่หลากหลาย' },
            { name: 'eval_standards', label: 'สอดคล้องกับมาตรฐานและตัวชี้วัด' },
            { name: 'eval_criteria', label: 'กำหนดเกณฑ์การประเมินที่ชัดเจน' },
            { name: 'eval_feedback', label: 'ให้ข้อมูลย้อนกลับแก่นักเรียน' },
            { name: 'eval_evidence', label: 'จัดเก็บหลักฐานการประเมินอย่างเป็นระบบ' }
        ],
        section4: [ // ด้านการจัดสภาพแวดล้อม (env_*) - 6 ข้อ
            { name: 'env_classroom', label: 'จัดห้องเรียนเอื้อต่อการเรียนรู้' },
            { name: 'env_interaction', label: 'สร้างปฏิสัมพันธ์ที่ดีกับนักเรียน' },
            { name: 'env_safety', label: 'จัดห้องเรียนให้ปลอดภัย' },
            { name: 'env_management', label: 'บริหารจัดการห้องเรียนได้ดี' },
            { name: 'env_rules', label: 'กำหนดกฎระเบียบที่ชัดเจน' },
            { name: 'env_behavior', label: 'ส่งเสริมพฤติกรรมที่พึงประสงค์' }
        ]
    };

    // DOM Elements
    const modal = document.getElementById('modalSupervision');
    const btnAdd = document.getElementById('btnAddSupervision');
    const btnClose = document.getElementById('closeModalSupervision');
    const btnCancel = document.getElementById('cancelSupervision');
    const form = document.getElementById('formSupervision');
    const tableBody = document.getElementById('supervisionTableBody');
    const mobileCards = document.getElementById('mobileSupervisionCards');
    const totalScoreValue = document.getElementById('totalScoreValue');

    // State
    let isEditMode = false;
    let currentSupervisionId = null;

    // Initialize
    renderEvaluationItems();
    loadSupervisions();
    wireEvents();

    function wireEvents() {
        btnAdd.addEventListener('click', () => openModal('add'));
        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
        form.addEventListener('submit', handleFormSubmit);

        // Score calculation
        form.addEventListener('change', calculateTotalScore);
    }

    function renderEvaluationItems() {
        ['section1', 'section2', 'section3', 'section4'].forEach(section => {
            const container = document.getElementById(`evalSection${section.slice(-1)}`);
            if (!container) return;

            container.innerHTML = evaluationCriteria[section].map(item => `
                <div class="eval-item">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex-1 mr-4">${item.label}</span>
                    <div class="rating-group">
                        ${[5, 4, 3, 2, 1].map(val => `
                            <div class="rating-option">
                                <input type="radio" name="${item.name}" id="${item.name}_${val}" value="${val}">
                                <label for="${item.name}_${val}">${val}</label>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        });
    }

    function calculateTotalScore() {
        let total = 0;
        const allCriteria = [...evaluationCriteria.section1, ...evaluationCriteria.section2, ...evaluationCriteria.section3, ...evaluationCriteria.section4];

        allCriteria.forEach(item => {
            const checked = form.querySelector(`input[name="${item.name}"]:checked`);
            if (checked) {
                total += parseInt(checked.value);
            }
        });

        totalScoreValue.textContent = total;

        // Update color based on score
        if (total >= 98) {
            totalScoreValue.className = 'text-green-600 font-bold';
        } else if (total >= 74) {
            totalScoreValue.className = 'text-blue-600 font-bold';
        } else if (total >= 50) {
            totalScoreValue.className = 'text-purple-600 font-bold';
        } else if (total >= 26) {
            totalScoreValue.className = 'text-amber-600 font-bold';
        } else {
            totalScoreValue.className = 'text-red-600 font-bold';
        }
    }

    function calculateAndGetTotalScore() {
        let total = 0;
        const allCriteria = [...evaluationCriteria.section1, ...evaluationCriteria.section2, ...evaluationCriteria.section3, ...evaluationCriteria.section4];

        allCriteria.forEach(item => {
            const checked = form.querySelector(`input[name="${item.name}"]:checked`);
            if (checked) {
                total += parseInt(checked.value);
            }
        });
        return total;
    }

    function getQualityLevel(score) {
        if (score >= 98) return 'ดีเยี่ยม';
        if (score >= 74) return 'ดีมาก';
        if (score >= 50) return 'ดี';
        if (score >= 26) return 'พอใช้';
        return 'ควรปรับปรุง';
    }

    async function loadSupervisions() {
        try {
            const response = await fetch('../controllers/SupervisionController.php?action=list');
            const data = await response.json();
            renderSupervisions(data || []);
        } catch (error) {
            console.error('Error loading supervisions:', error);
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
        }
    }

    function renderSupervisions(supervisions) {
        if (!supervisions.length) {
            const emptyHtml = `
                <div class="text-center py-8 text-gray-500">
                    <div class="text-4xl mb-2">👁️</div>
                    <p>ยังไม่มีข้อมูลการนิเทศ</p>
                    <p class="text-sm">กดปุ่ม "บันทึกการนิเทศ" เพื่อเริ่มต้น</p>
                </div>
            `;
            tableBody.innerHTML = `<tr><td colspan="8">${emptyHtml}</td></tr>`;
            mobileCards.innerHTML = emptyHtml;
            return;
        }

        // Desktop Table
        tableBody.innerHTML = supervisions.map(s => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700">
                <td class="py-3 px-3 text-center">${formatDate(s.supervision_date)}</td>
                <td class="py-3 px-3 font-medium">${s.teacher_name || '-'}</td>
                <td class="py-3 px-3">${s.subject_name || '-'}</td>
                <td class="py-3 px-3 text-center">${s.class_level || '-'}</td>
                <td class="py-3 px-3 text-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">ครั้งที่ ${s.supervision_round || 1}</span>
                </td>
                <td class="py-3 px-3 text-center">
                    <span class="font-bold text-indigo-600">${s.total_score || 0}</span>
                </td>
                <td class="py-3 px-3 text-center">
                    ${getQualityBadge(s.total_score)}
                </td>
                <td class="py-3 px-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="viewSupervision(${s.id})" class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center" title="ดูื ">👁️</button>
                        <button onclick="editSupervision(${s.id})" class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 hover:bg-amber-200 flex items-center justify-center" title="แก้ไข">✏️</button>
                        <button onclick="deleteSupervision(${s.id})" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center" title="ลบ">🗑️</button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Mobile Cards
        mobileCards.innerHTML = supervisions.map(s => `
            <div class="glow-card glass rounded-xl p-4 shadow-lg border border-white/20">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="font-bold text-gray-900 dark:text-white">${s.teacher_name || '-'}</div>
                        <div class="text-xs text-gray-500">${formatDate(s.supervision_date)}</div>
                    </div>
                    ${getQualityBadge(s.total_score)}
                </div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">${s.subject_name || '-'}</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">ชั้น ${s.class_level || '-'}</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">ครั้งที่ ${s.supervision_round || 1}</span>
                </div>
                <div class="text-center py-2 bg-gray-50 dark:bg-gray-700 rounded-lg mb-3">
                    <span class="text-2xl font-bold text-indigo-600">${s.total_score || 0}</span>
                    <span class="text-sm text-gray-500">/125 คะแนน</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="viewSupervision(${s.id})" class="flex-1 py-2 rounded-lg bg-blue-100 text-blue-600 text-sm font-medium">👁️ ดู</button>
                    <button onclick="editSupervision(${s.id})" class="flex-1 py-2 rounded-lg bg-amber-100 text-amber-600 text-sm font-medium">✏️ แก้ไข</button>
                    <button onclick="deleteSupervision(${s.id})" class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">🗑️</button>
                </div>
            </div>
        `).join('');
    }

    function getQualityBadge(score) {
        score = parseInt(score) || 0;
        if (score >= 98) return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">ดีเยี่ยม</span>';
        if (score >= 74) return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">ดีมาก</span>';
        if (score >= 50) return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">ดี</span>';
        if (score >= 26) return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">พอใช้</span>';
        return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">ควรปรับปรุง</span>';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear() + 543}`;
    }

    function openModal(mode) {
        form.reset();
        isEditMode = false;
        currentSupervisionId = null;
        document.getElementById('modalTitleText').textContent = 'บันทึกการนิเทศ';
        totalScoreValue.textContent = '0';
        totalScoreValue.className = 'text-indigo-600 font-bold';

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        form.reset();
        isEditMode = false;
        currentSupervisionId = null;
    }

    async function handleFormSubmit(e) {
        e.preventDefault();

        // Validate lesson plan for new submissions
        if (!isEditMode) {
            const lessonPlanInput = form.querySelector('input[name="lesson_plan"]');
            if (!lessonPlanInput.files || lessonPlanInput.files.length === 0) {
                Swal.fire('ข้อมูลไม่ครบ', 'กรุณาอัพโหลดแผนการจัดการเรียนรู้ (PDF)', 'warning');
                return;
            }
        }

        Swal.fire({
            title: '💾 กำลังบันทึก...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const formData = new FormData(form);
            if (isEditMode && currentSupervisionId) {
                formData.append('id', currentSupervisionId);
            }

            // Calculate and append total score
            const totalScore = calculateAndGetTotalScore();
            formData.append('total_score', totalScore);
            formData.append('quality_level', getQualityLevel(totalScore));

            const action = isEditMode ? 'update' : 'create';
            const response = await fetch(`../controllers/SupervisionController.php?action=${action}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: '🎉 สำเร็จ!',
                    text: isEditMode ? 'แก้ไขการนิเทศเรียบร้อยแล้ว' : 'บันทึกการนิเทศเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#6366f1'
                });
                closeModal();
                loadSupervisions();
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาด');
            }
        } catch (error) {
            Swal.fire('ข้อผิดพลาด', error.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
        }
    }

    // Global functions
    window.viewSupervision = async function (id) {
        try {
            const response = await fetch(`../controllers/SupervisionController.php?action=detail&id=${id}`);
            const supervision = await response.json();

            if (!supervision || !supervision.id) {
                throw new Error('ไม่พบข้อมูล');
            }

            // Calculate section scores
            const planScore = (parseInt(supervision.plan_effective) || 0) + (parseInt(supervision.plan_correct) || 0) +
                (parseInt(supervision.plan_activities) || 0) + (parseInt(supervision.plan_media) || 0) +
                (parseInt(supervision.plan_assessment) || 0);
            const teachScore = (parseInt(supervision.teach_techniques) || 0) + (parseInt(supervision.teach_media) || 0) +
                (parseInt(supervision.teach_assessment) || 0) + (parseInt(supervision.teach_explanation) || 0) +
                (parseInt(supervision.teach_control) || 0) + (parseInt(supervision.teach_thinking) || 0) +
                (parseInt(supervision.teach_adaptation) || 0) + (parseInt(supervision.teach_integration) || 0) +
                (parseInt(supervision.teach_language) || 0);
            const evalScore = (parseInt(supervision.eval_variety) || 0) + (parseInt(supervision.eval_standards) || 0) +
                (parseInt(supervision.eval_criteria) || 0) + (parseInt(supervision.eval_feedback) || 0) +
                (parseInt(supervision.eval_evidence) || 0);
            const envScore = (parseInt(supervision.env_classroom) || 0) + (parseInt(supervision.env_interaction) || 0) +
                (parseInt(supervision.env_safety) || 0) + (parseInt(supervision.env_management) || 0) +
                (parseInt(supervision.env_rules) || 0) + (parseInt(supervision.env_behavior) || 0);

            const html = `
                <div class="text-left space-y-4">
                    <!-- Header Card -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-4 text-white shadow-lg">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl">👨‍🏫</div>
                            <div>
                                <div class="font-bold text-lg">${supervision.teacher_name || '-'}</div>
                                <div class="text-white/80 text-sm">${supervision.position || 'ครู'} • ${supervision.subject_group || '-'}</div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-3 py-1 bg-white/20 rounded-full text-sm">📖 ${supervision.subject_name || '-'}</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-sm">🏫 ชั้น ${supervision.class_level || '-'}</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-sm">📅 ${formatDate(supervision.supervision_date)}</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-sm">🔢 ครั้งที่ ${supervision.supervision_round || 1}</span>
                        </div>
                    </div>

                    <!-- Score Card -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-4 border border-amber-200">
                        <div class="text-center mb-4">
                            <div class="text-5xl font-black bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent">${supervision.total_score || 0}</div>
                            <div class="text-gray-500">คะแนนเต็ม 125 คะแนน</div>
                            <div class="mt-2">${getQualityBadgeHtml(supervision.total_score)}</div>
                        </div>
                        
                        <!-- Score Breakdown -->
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="bg-white rounded-lg p-2 flex items-center justify-between">
                                <span class="text-gray-600">📋 การจัดทำแผน</span>
                                <span class="font-bold text-green-600">${planScore}/25</span>
                            </div>
                            <div class="bg-white rounded-lg p-2 flex items-center justify-between">
                                <span class="text-gray-600">👨‍🏫 การจัดการเรียนรู้</span>
                                <span class="font-bold text-purple-600">${teachScore}/45</span>
                            </div>
                            <div class="bg-white rounded-lg p-2 flex items-center justify-between">
                                <span class="text-gray-600">📝 การประเมินผล</span>
                                <span class="font-bold text-amber-600">${evalScore}/25</span>
                            </div>
                            <div class="bg-white rounded-lg p-2 flex items-center justify-between">
                                <span class="text-gray-600">🏫 สภาพแวดล้อม</span>
                                <span class="font-bold text-teal-600">${envScore}/30</span>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    ${(supervision.strengths || supervision.improvements || supervision.suggestions) ? `
                    <div class="bg-gray-50 rounded-2xl p-4 space-y-3">
                        ${supervision.strengths ? `
                        <div class="flex gap-2">
                            <span class="text-green-500 text-xl">✨</span>
                            <div>
                                <div class="font-semibold text-gray-700 text-sm">จุดเด่น</div>
                                <div class="text-gray-600">${supervision.strengths}</div>
                            </div>
                        </div>` : ''}
                        ${supervision.improvements ? `
                        <div class="flex gap-2">
                            <span class="text-amber-500 text-xl">📈</span>
                            <div>
                                <div class="font-semibold text-gray-700 text-sm">จุดที่ควรพัฒนา</div>
                                <div class="text-gray-600">${supervision.improvements}</div>
                            </div>
                        </div>` : ''}
                        ${supervision.suggestions ? `
                        <div class="flex gap-2">
                            <span class="text-blue-500 text-xl">💡</span>
                            <div>
                                <div class="font-semibold text-gray-700 text-sm">ข้อเสนอแนะ</div>
                                <div class="text-gray-600">${supervision.suggestions}</div>
                            </div>
                        </div>` : ''}
                    </div>` : ''}

                    <!-- Actions -->
                    <div class="flex gap-2 pt-2">
                        <button onclick="editSupervision(${id}); Swal.close();" class="flex-1 py-2.5 rounded-xl bg-amber-100 text-amber-700 font-medium hover:bg-amber-200 transition-colors">
                            ✏️ แก้ไข
                        </button>
                        <button onclick="Swal.close();" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">
                            ปิด
                        </button>
                    </div>
                </div>
            `;

            Swal.fire({
                html: html,
                width: 480,
                padding: '1rem',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-3xl',
                    closeButton: 'text-gray-400 hover:text-gray-600'
                }
            });
        } catch (error) {
            Swal.fire('ข้อผิดพลาด', error.message, 'error');
        }
    };

    window.editSupervision = async function (id) {
        try {
            const response = await fetch(`../controllers/SupervisionController.php?action=detail&id=${id}`);
            const supervision = await response.json();

            if (!supervision || !supervision.id) {
                throw new Error('ไม่พบข้อมูล');
            }

            // Fill form
            Object.keys(supervision).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input && input.type !== 'file') {
                    if (input.type === 'radio') {
                        const radio = form.querySelector(`input[name="${key}"][value="${supervision[key]}"]`);
                        if (radio) radio.checked = true;
                    } else {
                        input.value = supervision[key];
                    }
                }
            });

            isEditMode = true;
            currentSupervisionId = id;
            document.getElementById('modalTitleText').textContent = 'แก้ไขการนิเทศ';
            calculateTotalScore();

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } catch (error) {
            Swal.fire('ข้อผิดพลาด', error.message, 'error');
        }
    };

    window.deleteSupervision = async function (id) {
        const result = await Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'คุณต้องการลบการนิเทศนี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#ef4444'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`../controllers/SupervisionController.php?action=delete&id=${id}`, {
                    method: 'POST'
                });
                const data = await response.json();

                if (data.success) {
                    Swal.fire('ลบสำเร็จ', '', 'success');
                    loadSupervisions();
                } else {
                    throw new Error(data.message || 'ไม่สามารถลบได้');
                }
            } catch (error) {
                Swal.fire('ข้อผิดพลาด', error.message, 'error');
            }
        }
    };

    function getQualityBadgeHtml(score) {
        score = parseInt(score) || 0;
        if (score >= 98) return '<span class="px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-700">🏆 ดีเยี่ยม</span>';
        if (score >= 74) return '<span class="px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-700">⭐ ดีมาก</span>';
        if (score >= 50) return '<span class="px-4 py-2 rounded-full text-sm font-medium bg-purple-100 text-purple-700">👍 ดี</span>';
        if (score >= 26) return '<span class="px-4 py-2 rounded-full text-sm font-medium bg-amber-100 text-amber-700">📈 พอใช้</span>';
        return '<span class="px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-700">📉 ควรปรับปรุง</span>';
    }
});
