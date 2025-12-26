/**
 * Subject Management JavaScript
 * MVC Pattern - Handles subject CRUD operations
 */

document.addEventListener('DOMContentLoaded', function () {
    const config = window.SUBJECT_CONFIG || {};
    const teacherId = config.teacherId;

    // Days of week
    const days = [
        { key: 'จันทร์', label: 'จันทร์' },
        { key: 'อังคาร', label: 'อังคาร' },
        { key: 'พุธ', label: 'พุธ' },
        { key: 'พฤหัสบดี', label: 'พฤหัสบดี' },
        { key: 'ศุกร์', label: 'ศุกร์' },
        { key: 'เสาร์', label: 'เสาร์' },
        { key: 'อาทิตย์', label: 'อาทิตย์' }
    ];

    // DOM Elements
    const modal = document.getElementById('modalAddSubject');
    const btnAdd = document.getElementById('btnAddSubject');
    const btnClose = document.getElementById('closeModalAddSubject');
    const btnCancel = document.getElementById('cancelAddSubject');
    const form = document.getElementById('formAddSubject');
    const tableBody = document.getElementById('subjectTableBody');
    const cardsContainer = document.getElementById('subjectCards');
    const searchInput = document.getElementById('subjectSearch');
    const filterChips = document.querySelectorAll('.filter-chip');
    const classRoomDetails = document.getElementById('classRoomDetails');

    // State
    let currentFilter = '';

    // Initialize
    loadSubjects();
    wireEvents();

    function wireEvents() {
        // Add button
        btnAdd.addEventListener('click', () => openModal('add'));

        // Close modal
        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });

        // Filter chips
        filterChips.forEach(chip => {
            chip.addEventListener('click', () => {
                filterChips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                currentFilter = chip.dataset.val || '';
                applyFilters();
            });
        });

        // Search
        searchInput.addEventListener('input', applyFilters);

        // Class room checkboxes
        document.querySelectorAll('.class-room-checkbox').forEach(cb => {
            cb.addEventListener('change', updateClassRoomDetails);
        });

        // Form submit
        form.addEventListener('submit', handleFormSubmit);
    }

    async function loadSubjects() {
        try {
            const response = await fetch('../controllers/SubjectController.php?action=list&teacherId=' + encodeURIComponent(teacherId));
            const data = await response.json();
            renderSubjects(data || []);
        } catch (error) {
            console.error('Error loading subjects:', error);
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
        }
    }

    function renderSubjects(subjects) {
        if (!subjects.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500"><div class="text-4xl mb-2">📚</div>ยังไม่มีรายวิชา กดปุ่ม "เพิ่มรายวิชา" เพื่อเริ่มต้น</td></tr>';
            cardsContainer.innerHTML = '<div class="text-center py-8 text-gray-500"><div class="text-4xl mb-2">📚</div>ยังไม่มีรายวิชา</div>';
            return;
        }

        // Desktop Table
        tableBody.innerHTML = subjects.map(subject => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700" data-status="${subject.status}">
                <td class="py-3 px-4 text-center font-mono text-indigo-600 dark:text-indigo-400">${subject.code}</td>
                <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">${subject.name}</td>
                <td class="py-3 px-4 text-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">ม.${subject.level}</span>
                </td>
                <td class="py-3 px-4 text-center">${subject.subject_type || '-'}</td>
                <td class="py-3 px-4 text-center">
                    ${renderStatusSwitch(subject)}
                </td>
                <td class="py-3 px-4 text-center">
                    <button class="btn-detail px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-medium hover:bg-blue-200 transition-colors" data-id="${subject.id}">
                        👁️ ดูคาบ
                    </button>
                </td>
                <td class="py-3 px-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button class="btn-edit w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 hover:bg-amber-200 transition-colors flex items-center justify-center" data-id="${subject.id}" title="แก้ไข">
                            ✏️
                        </button>
                        <button class="btn-delete w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" data-id="${subject.id}" title="ลบ">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Mobile Cards
        cardsContainer.innerHTML = subjects.map(subject => `
            <div class="glow-card glass rounded-xl p-4 shadow-lg border border-white/20" data-status="${subject.status}">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <span class="text-xs font-mono text-indigo-600 dark:text-indigo-400">${subject.code}</span>
                        <h3 class="font-bold text-gray-900 dark:text-white">${subject.name}</h3>
                    </div>
                    ${renderStatusSwitch(subject)}
                </div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">ม.${subject.level}</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">${subject.subject_type || '-'}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn-detail flex-1 py-2 rounded-lg bg-blue-100 text-blue-600 text-sm font-medium" data-id="${subject.id}">👁️ ดูคาบ</button>
                    <button class="btn-edit w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center" data-id="${subject.id}">✏️</button>
                    <button class="btn-delete w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center" data-id="${subject.id}">🗑️</button>
                </div>
            </div>
        `).join('');

        // Bind events
        bindRowEvents();
        applyFilters();
    }

    function renderStatusSwitch(subject) {
        const checked = subject.status === 'เปิดสอน' ? 'checked' : '';
        return `
            <label class="toggle-switch">
                <input type="checkbox" class="status-switch" data-id="${subject.id}" ${checked}>
                <span class="toggle-slider"></span>
            </label>
        `;
    }

    function bindRowEvents() {
        // Detail buttons
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', () => showSubjectDetail(btn.dataset.id));
        });

        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => openEditModal(btn.dataset.id));
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => deleteSubject(btn.dataset.id));
        });

        // Status switches
        document.querySelectorAll('.status-switch').forEach(sw => {
            sw.addEventListener('change', function () {
                toggleStatus(this.dataset.id, this.checked);
            });
        });
    }

    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#subjectTableBody tr[data-status]');
        const cards = document.querySelectorAll('#subjectCards > div[data-status]');

        [...rows, ...cards].forEach(item => {
            const text = item.textContent.toLowerCase();
            const status = item.dataset.status || '';

            let matchSearch = !query || text.includes(query);
            let matchStatus = !currentFilter || status.includes(currentFilter);

            item.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    function openModal(mode) {
        form.reset();
        form.removeAttribute('data-mode');
        form.removeAttribute('data-id');
        classRoomDetails.innerHTML = '';
        document.querySelectorAll('.class-room-checkbox').forEach(cb => cb.checked = false);

        document.getElementById('modalTitleText').textContent = mode === 'edit' ? 'แก้ไขรายวิชา' : 'เพิ่มรายวิชา';
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        setTimeout(() => document.getElementById('inputCode')?.focus(), 100);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        form.reset();
        form.removeAttribute('data-mode');
        form.removeAttribute('data-id');
    }

    async function openEditModal(subjectId) {
        try {
            const response = await fetch('../controllers/SubjectController.php?action=detail&subjectId=' + encodeURIComponent(subjectId));
            const data = await response.json();

            const subject = data.subject;
            const classes = data.classes || [];

            if (!subject) {
                Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูล', 'error');
                return;
            }

            // Fill form
            form.code.value = subject.code;
            form.name.value = subject.name;
            form.level.value = subject.level;
            form.subject_type.value = subject.subject_type;
            form.status.value = subject.status;

            // Clear checkboxes
            document.querySelectorAll('.class-room-checkbox').forEach(cb => cb.checked = false);

            // Get unique rooms and check them
            const uniqueRooms = [...new Set(classes.map(c => c.class_room))];
            uniqueRooms.forEach(room => {
                const cb = Array.from(document.querySelectorAll('.class-room-checkbox')).find(cb => cb.value === room);
                if (cb) cb.checked = true;
            });

            // Update room details
            updateClassRoomDetails();

            // Fill in day/period data
            setTimeout(() => {
                uniqueRooms.forEach(room => {
                    const roomClasses = classes.filter(c => c.class_room === room);
                    const list = classRoomDetails.querySelector(`.class-room-days-list[data-room="${room}"]`);
                    if (list) {
                        list.innerHTML = '';
                        roomClasses.forEach((c, idx) => {
                            list.insertAdjacentHTML('beforeend', renderClassRoomDayRow(room, idx));
                        });

                        const rows = list.querySelectorAll('.class-room-day-row');
                        roomClasses.forEach((c, idx) => {
                            const row = rows[idx];
                            if (row) {
                                row.querySelector(`select[name="class_days[${room}][]"]`).value = c.day_of_week;
                                row.querySelector(`input[name="period_start[${room}][]"]`).value = c.period_start;
                                row.querySelector(`input[name="period_end[${room}][]"]`).value = c.period_end;
                            }
                        });
                    }
                });
                bindDayRowEvents();
            }, 100);

            form.setAttribute('data-mode', 'edit');
            form.setAttribute('data-id', subjectId);

            document.getElementById('modalTitleText').textContent = 'แก้ไขรายวิชา';
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } catch (error) {
            console.error(error);
            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาด', 'error');
        }
    }

    async function deleteSubject(subjectId) {
        const result = await Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'คุณต้องการลบรายวิชานี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#ef4444'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('../controllers/SubjectController.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: subjectId })
                });
                const data = await response.json();

                if (data.success) {
                    Swal.fire('ลบสำเร็จ', 'ลบรายวิชาเรียบร้อยแล้ว', 'success');
                    loadSubjects();
                } else {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถลบรายวิชาได้', 'error');
                }
            } catch (error) {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาด', 'error');
            }
        }
    }

    async function toggleStatus(subjectId, isOpen) {
        const newStatus = isOpen ? 'เปิดสอน' : 'ไม่เปิดสอน';

        try {
            const response = await fetch('../controllers/SubjectController.php?action=updateStatus', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: subjectId, status: newStatus })
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'อัปเดตสถานะแล้ว',
                    showConfirmButton: false,
                    timer: 1500
                });
                loadSubjects();
            } else {
                Swal.fire('ผิดพลาด', 'อัปเดตสถานะไม่สำเร็จ', 'error');
                loadSubjects();
            }
        } catch (error) {
            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาด', 'error');
            loadSubjects();
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const subjectData = {
            code: formData.get('code'),
            name: formData.get('name'),
            level: formData.get('level'),
            subject_type: formData.get('subject_type'),
            status: formData.get('status')
        };

        // Collect class room data
        const classRooms = [];
        document.querySelectorAll('.class-room-checkbox:checked').forEach(cb => {
            const room = cb.value;
            const daysArr = formData.getAll(`class_days[${room}][]`);
            const startsArr = formData.getAll(`period_start[${room}][]`);
            const endsArr = formData.getAll(`period_end[${room}][]`);

            for (let i = 0; i < daysArr.length; i++) {
                classRooms.push({
                    class_room: room,
                    day_of_week: daysArr[i],
                    period_start: startsArr[i],
                    period_end: endsArr[i]
                });
            }
        });

        const mode = form.getAttribute('data-mode');
        const subjectId = form.getAttribute('data-id');

        let url = '../controllers/SubjectController.php?action=create';
        let body = { ...subjectData, class_rooms: classRooms };

        if (mode === 'edit' && subjectId) {
            url = '../controllers/SubjectController.php?action=update';
            body.id = subjectId;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire('สำเร็จ', mode === 'edit' ? 'แก้ไขรายวิชาแล้ว' : 'เพิ่มรายวิชาแล้ว', 'success');
                closeModal();
                loadSubjects();
            } else {
                Swal.fire('ผิดพลาด', data.error || 'ไม่สามารถบันทึกได้', 'error');
            }
        } catch (error) {
            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาด', 'error');
        }
    }

    function showSubjectDetail(subjectId) {
        fetch('../controllers/SubjectController.php?action=detail&subjectId=' + encodeURIComponent(subjectId))
            .then(res => res.json())
            .then(data => {
                const classes = Array.isArray(data) ? data : (data.classes || []);

                let html = `
                    <div class="text-left">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            ⏰ รายละเอียดคาบสอน
                        </h3>
                `;

                if (classes.length > 0) {
                    html += `
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead>
                                    <tr class="bg-indigo-500 text-white">
                                        <th class="px-3 py-2 text-left rounded-tl-lg">ห้อง</th>
                                        <th class="px-3 py-2 text-center">วัน</th>
                                        <th class="px-3 py-2 text-center">คาบเริ่ม</th>
                                        <th class="px-3 py-2 text-center rounded-tr-lg">คาบสิ้นสุด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${classes.map((row, idx) => `
                                        <tr class="${idx % 2 === 0 ? 'bg-gray-50' : 'bg-white'}">
                                            <td class="px-3 py-2 border-b">${row.class_room}</td>
                                            <td class="px-3 py-2 border-b text-center">${row.day_of_week}</td>
                                            <td class="px-3 py-2 border-b text-center">${row.period_start}</td>
                                            <td class="px-3 py-2 border-b text-center">${row.period_end}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    html += '<p class="text-gray-500">ไม่พบข้อมูลคาบสอน</p>';
                }

                html += '</div>';

                Swal.fire({
                    html: html,
                    width: 500,
                    showCloseButton: true,
                    showConfirmButton: false
                });
            });
    }

    function updateClassRoomDetails() {
        classRoomDetails.innerHTML = '';
        document.querySelectorAll('.class-room-checkbox:checked').forEach(cb => {
            const room = cb.value;
            classRoomDetails.innerHTML += renderClassRoomDetail(room);
        });
        bindDayRowEvents();
    }

    function renderClassRoomDetail(room) {
        return `
            <div class="border-2 border-gray-200 dark:border-gray-600 rounded-xl p-3 bg-gray-50 dark:bg-gray-700/50" data-room="${room}">
                <div class="font-semibold text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-2">
                    🏫 ${room}
                </div>
                <div class="class-room-days-list space-y-2" data-room="${room}">
                    ${renderClassRoomDayRow(room, 0)}
                </div>
                <button type="button" class="add-day-row mt-3 px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm font-medium hover:bg-blue-200 transition-colors" data-room="${room}">
                    + เพิ่มวัน/คาบ
                </button>
            </div>
        `;
    }

    function renderClassRoomDayRow(room, idx = 0) {
        return `
            <div class="flex flex-wrap items-end gap-2 class-room-day-row p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600" data-room="${room}">
                <div class="flex-1 min-w-[100px]">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">วัน</label>
                    <select name="class_days[${room}][]" required
                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">เลือกวัน</option>
                        ${days.map(day => `<option value="${day.key}">${day.label}</option>`).join('')}
                    </select>
                </div>
                <div class="w-20">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">คาบเริ่ม</label>
                    <input type="number" name="period_start[${room}][]" min="1" required placeholder="1"
                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <div class="w-20">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">คาบสิ้นสุด</label>
                    <input type="number" name="period_end[${room}][]" min="1" required placeholder="2"
                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <button type="button" class="remove-day-row w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center mb-0.5" title="ลบ">
                    ✕
                </button>
            </div>
        `;
    }

    function bindDayRowEvents() {
        // Add day row
        document.querySelectorAll('.add-day-row').forEach(btn => {
            btn.onclick = function () {
                const room = btn.dataset.room;
                const list = classRoomDetails.querySelector(`.class-room-days-list[data-room="${room}"]`);
                if (list) {
                    list.insertAdjacentHTML('beforeend', renderClassRoomDayRow(room));
                    bindDayRowEvents();
                }
            };
        });

        // Remove day row
        document.querySelectorAll('.remove-day-row').forEach(btn => {
            btn.onclick = function () {
                const row = btn.closest('.class-room-day-row');
                if (row.parentNode.childElementCount > 1) {
                    row.remove();
                }
            };
        });
    }
});
