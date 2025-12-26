/**
 * Admin Teacher/User Management JavaScript
 * MVC Pattern - Separated JS for admin user management
 */

// Global variables
window._departments = [];

function renderStatusSwitch(teacher) {
    const statusMap = {
        1: { text: '🟢 ปกติ', color: 'text-green-600', bg: 'bg-green-100' },
        2: { text: '🚚 ย้าย', color: 'text-blue-500', bg: 'bg-blue-100' },
        3: { text: '🎉 เกษียณ', color: 'text-yellow-600', bg: 'bg-yellow-100' },
        4: { text: '🏠 ลาออก', color: 'text-gray-500', bg: 'bg-gray-100' },
        9: { text: '⚰️ เสียชีวิต', color: 'text-red-600', bg: 'bg-red-100' }
    };
    const current = statusMap[teacher.Teach_status] || { text: 'ไม่ทราบ', color: 'text-gray-400', bg: 'bg-gray-100' };
    return `
        <div class="relative inline-block text-left">
            <button type="button" class="status-dropdown-btn status-badge ${current.color} ${current.bg} dark:bg-opacity-20 font-semibold flex items-center gap-1 px-3 py-1.5 rounded-full text-xs" data-id="${teacher.Teach_id}">
                ${current.text}
                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div class="status-dropdown-menu absolute z-20 mt-1 hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg min-w-[140px] right-0" data-id="${teacher.Teach_id}">
                <div class="py-1">
                    <button class="status-option block w-full text-left px-4 py-2 text-green-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-t-lg text-sm" data-status="1">🟢 ปกติ</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-blue-500 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" data-status="2">🚚 ย้าย</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-yellow-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" data-status="3">🎉 เกษียณ</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" data-status="4">🏠 ลาออก</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-b-lg text-sm" data-status="9">⚰️ เสียชีวิต</button>
                </div>
            </div>
        </div>
    `;
}

function renderDepartmentDropdown(teacher) {
    let options = '';
    if (!window._departments) return teacher.Teach_major;
    window._departments.forEach(dep => {
        options += `<button class="dep-option block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" data-value="${dep.name}">${dep.name}</button>`;
    });
    return `
        <div class="relative inline-block text-left">
            <button type="button" class="dep-dropdown-btn font-medium flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm" data-id="${teacher.Teach_id}">
                ${teacher.Teach_major || '--'}
                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div class="dep-dropdown-menu absolute z-20 mt-1 hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg min-w-[160px]" data-id="${teacher.Teach_id}">
                <div class="py-1 max-h-60 overflow-y-auto">${options}</div>
            </div>
        </div>
    `;
}

function renderRoleDropdown(teacher) {
    const roles = [
        { val: 'T', label: '👩‍🏫 ครู' },
        { val: 'HOD', label: '👨‍💼 หัวหน้ากลุ่มสาระ' },
        { val: 'VP', label: '👔 รองผู้บริหาร' },
        { val: 'OF', label: '📋 เจ้าหน้าที่' },
        { val: 'DIR', label: '🏫 ผู้อำนวยการ' },
        { val: 'ADM', label: '🛡️ ผู้ดูแลระบบ' }
    ];
    let options = '';
    roles.forEach(r => {
        options += `<button class="role-option block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" data-value="${r.val}">${r.label}</button>`;
    });
    let current = roles.find(r => r.val === teacher.role_ckteach);
    return `
        <div class="relative inline-block text-left">
            <button type="button" class="role-dropdown-btn font-medium flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm" data-id="${teacher.Teach_id}">
                ${current ? current.label : teacher.role_ckteach}
                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div class="role-dropdown-menu absolute z-20 mt-1 hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg min-w-[180px]" data-id="${teacher.Teach_id}">
                <div class="py-1">${options}</div>
            </div>
        </div>
    `;
}

function getStatusOptions(selected) {
    const statusList = [
        { val: 1, label: '🟢 ปกติ' },
        { val: 2, label: '🚚 ย้าย' },
        { val: 3, label: '🎉 เกษียณ' },
        { val: 4, label: '🏠 ลาออก' },
        { val: 9, label: '⚰️ เสียชีวิต' }
    ];
    return statusList.map(s => `<option value="${s.val}" ${parseInt(selected) === s.val ? 'selected' : ''}>${s.label}</option>`).join('');
}

function getDepartmentOptions(selected) {
    let html = '<option value="">-- เลือกกลุ่มสาระ --</option>';
    if (!window._departments) return html;
    window._departments.forEach(dep => {
        html += `<option value="${dep.name}" ${selected === dep.name ? 'selected' : ''}>${dep.name}</option>`;
    });
    return html;
}

function getRoleOptions(selected) {
    const roles = [
        { val: 'T', label: '👩‍🏫 ครู' },
        { val: 'HOD', label: '👨‍💼 หัวหน้ากลุ่มสาระ' },
        { val: 'VP', label: '👔 รองผู้บริหาร' },
        { val: 'OF', label: '📋 เจ้าหน้าที่' },
        { val: 'DIR', label: '🏫 ผู้อำนวยการ' },
        { val: 'ADM', label: '🛡️ ผู้ดูแลระบบ' }
    ];
    return roles.map(r => `<option value="${r.val}" ${selected === r.val ? 'selected' : ''}>${r.label}</option>`).join('');
}

function reloadTable() {
    $.getJSON('../controllers/TeacherController.php?action=list', function (data) {
        const major = $('#filter-major').val();
        const role = $('#filter-role').val();
        const status = $('#filter-status').val();
        let tbody = '';
        let filtered = data.filter(function (teacher) {
            let ok = true;
            if (major && teacher.Teach_major !== major) ok = false;
            if (role && teacher.role_ckteach !== role) ok = false;
            if (status && String(teacher.Teach_status) !== String(status)) ok = false;
            return ok;
        });

        filtered.forEach(function (teacher) {
            tbody += `<tr data-id="${teacher.Teach_id}" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 text-center font-mono text-xs text-gray-600 dark:text-gray-400">${teacher.Teach_id}</td>
                <td class="px-4 py-3 text-left editable font-medium text-gray-800 dark:text-gray-200" data-field="Teach_name">${teacher.Teach_name}</td>
                <td class="px-4 py-3 text-center editable hidden sm:table-cell" data-field="Teach_major" data-type="dropdown">${renderDepartmentDropdown(teacher)}</td>
                <td class="px-4 py-3 text-center editable" data-field="role_ckteach" data-type="dropdown">${renderRoleDropdown(teacher)}</td>
                <td class="px-4 py-3 text-center hidden md:table-cell">${renderStatusSwitch(teacher)}</td>
                <td class="px-4 py-3 text-center">
                    <div class="flex flex-wrap gap-1 justify-center">
                        <button class="btn-resetpwd bg-gray-500 hover:bg-gray-600 text-white px-2 py-1 rounded-lg flex items-center gap-1 text-xs transition-all" data-id="${teacher.Teach_id}">
                            <i class="fas fa-key"></i><span class="hidden lg:inline">รีเซ็ต</span>
                        </button>
                        <button class="btn-delete bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-lg flex items-center gap-1 text-xs transition-all" data-id="${teacher.Teach_id}">
                            <i class="fas fa-trash"></i><span class="hidden lg:inline">ลบ</span>
                        </button>
                    </div>
                </td>
            </tr>`;
        });

        if ($.fn.DataTable.isDataTable('#teacherTable')) {
            $('#teacherTable').DataTable().destroy();
        }
        $('#teacherTable tbody').html(tbody);
        $('#teacherTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' },
            order: [[1, 'asc']],
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            autoWidth: false,
            responsive: true
        });
    });
}

function showTeacherModal(type, teacher = {}) {
    Swal.fire({
        title: type === 'edit' ? '✏️ แก้ไขข้อมูลครู' : '➕ เพิ่มผู้ใช้ใหม่',
        html: `
            <form id="teacherForm" class="space-y-4 text-left">
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">🆔 รหัสครู</label>
                    <input type="text" id="Teach_id" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500" placeholder="รหัสครู" value="${teacher.Teach_id || ''}" ${type === 'edit' ? 'readonly' : ''} required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">👩‍🏫 ชื่อครู</label>
                    <input type="text" id="Teach_name" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500" placeholder="ชื่อครู" value="${teacher.Teach_name || ''}" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">🏢 กลุ่มสาระ</label>
                    <select id="Teach_major" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">${getDepartmentOptions(teacher.Teach_major || '')}</select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">🛡️ บทบาท</label>
                    <select id="role_ckteach" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">${getRoleOptions(teacher.role_ckteach || '')}</select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">✅ สถานะ</label>
                    <select id="Teach_status" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">${getStatusOptions(teacher.Teach_status ?? 1)}</select>
                </div>
            </form>
        `,
        customClass: {
            popup: 'rounded-2xl',
            htmlContainer: 'text-left',
            confirmButton: 'bg-purple-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-purple-700 transition-all',
            cancelButton: 'bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl font-semibold hover:bg-gray-300 transition-all'
        },
        showCancelButton: true,
        confirmButtonText: type === 'edit' ? 'บันทึก' : 'เพิ่ม',
        cancelButtonText: 'ยกเลิก',
        focusConfirm: false,
        preConfirm: () => {
            return {
                Teach_id: $('#Teach_id').val().trim(),
                Teach_name: $('#Teach_name').val().trim(),
                Teach_major: $('#Teach_major').val().trim(),
                role_ckteach: $('#role_ckteach').val(),
                Teach_status: $('#Teach_status').val()
            };
        },
        didOpen: () => {
            $('#teacherForm input, #teacherForm select').on('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            const action = type === 'edit' ? 'update' : 'create';
            const successMsg = type === 'edit' ? 'แก้ไขข้อมูลเรียบร้อยแล้ว' : 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว';

            $.ajax({
                url: `../controllers/TeacherController.php?action=${action}`,
                type: 'POST',
                data: result.value,
                success: function (res) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: successMsg, showConfirmButton: false, timer: 1500 });
                    if ($.fn.DataTable.isDataTable('#teacherTable')) {
                        $('#teacherTable').DataTable().destroy();
                    }
                    reloadTable();
                },
                error: function () {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'เกิดข้อผิดพลาด', showConfirmButton: false, timer: 1500 });
                }
            });
        }
    });
}

$(document).ready(function () {
    // Load departments
    $.getJSON('../controllers/DepartmentController.php?action=list', function (departments) {
        window._departments = departments;
        let html = '<option value="">-- ทั้งหมด --</option>';
        departments.forEach(dep => {
            html += `<option value="${dep.name}">${dep.name}</option>`;
        });
        $('#filter-major').html(html);
        reloadTable();
    });

    // Add teacher button
    $('#btnAddTeacher').on('click', function () {
        showTeacherModal('create');
    });

    // Filter events
    $('#filter-major, #filter-role, #filter-status').on('change', function () {
        reloadTable();
    });

    $('#filter-clear').on('click', function () {
        $('#filter-major').val('');
        $('#filter-role').val('');
        $('#filter-status').val('');
        reloadTable();
    });

    // Inline edit for name
    $('#teacherTable').on('click', 'td.editable:not([data-type])', function () {
        if ($(this).find('input').length > 0) return;
        const td = $(this);
        const oldVal = td.text();
        const field = td.data('field');
        const tr = td.closest('tr');
        const id = tr.data('id');
        td.html(`<input type="text" class="inline-edit-input w-full border-2 border-purple-300 rounded-lg px-2 py-1 text-sm" value="${oldVal}" />`);
        td.find('input').focus().select();

        td.find('input').on('blur keydown', function (e) {
            if (e.type === 'blur' || (e.type === 'keydown' && e.key === 'Enter')) {
                const newVal = $(this).val().trim();
                if (newVal !== oldVal && newVal !== '') {
                    $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function (teacher) {
                        if (!teacher) { td.text(oldVal); return; }
                        $.ajax({
                            url: '../controllers/TeacherController.php?action=update',
                            type: 'POST',
                            data: { ...teacher, Teach_name: newVal },
                            success: function (res) {
                                td.text(newVal);
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'บันทึกแล้ว', showConfirmButton: false, timer: 1200 });
                                reloadTable();
                            },
                            error: function () {
                                td.text(oldVal);
                                Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                            }
                        });
                    });
                } else {
                    td.text(oldVal);
                }
            }
        });
    });

    // Department dropdown
    $('#teacherTable').on('click', '.dep-dropdown-btn', function (e) {
        e.stopPropagation();
        const id = $(this).data('id');
        $('.dep-dropdown-menu, .role-dropdown-menu, .status-dropdown-menu').addClass('hidden');
        $(`.dep-dropdown-menu[data-id="${id}"]`).toggleClass('hidden');
    });

    $('#teacherTable').on('click', '.dep-option', function (e) {
        e.stopPropagation();
        const id = $(this).closest('.dep-dropdown-menu').data('id');
        const newVal = $(this).data('value');
        $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function (teacher) {
            if (!teacher) return;
            $.ajax({
                url: '../controllers/TeacherController.php?action=update',
                type: 'POST',
                data: { ...teacher, Teach_major: newVal },
                success: function (res) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'บันทึกแล้ว', showConfirmButton: false, timer: 1200 });
                    reloadTable();
                }
            });
        });
        $('.dep-dropdown-menu').addClass('hidden');
    });

    // Role dropdown
    $('#teacherTable').on('click', '.role-dropdown-btn', function (e) {
        e.stopPropagation();
        const id = $(this).data('id');
        $('.dep-dropdown-menu, .role-dropdown-menu, .status-dropdown-menu').addClass('hidden');
        $(`.role-dropdown-menu[data-id="${id}"]`).toggleClass('hidden');
    });

    $('#teacherTable').on('click', '.role-option', function (e) {
        e.stopPropagation();
        const id = $(this).closest('.role-dropdown-menu').data('id');
        const newVal = $(this).data('value');
        $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function (teacher) {
            if (!teacher) return;
            $.ajax({
                url: '../controllers/TeacherController.php?action=update',
                type: 'POST',
                data: { ...teacher, role_ckteach: newVal },
                success: function (res) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'บันทึกแล้ว', showConfirmButton: false, timer: 1200 });
                    reloadTable();
                }
            });
        });
        $('.role-dropdown-menu').addClass('hidden');
    });

    // Status dropdown
    $('#teacherTable').on('click', '.status-dropdown-btn', function (e) {
        e.stopPropagation();
        const id = $(this).data('id');
        $('.dep-dropdown-menu, .role-dropdown-menu, .status-dropdown-menu').addClass('hidden');
        $(`.status-dropdown-menu[data-id="${id}"]`).toggleClass('hidden');
    });

    $('#teacherTable').on('click', '.status-option', function (e) {
        e.stopPropagation();
        const id = $(this).closest('.status-dropdown-menu').data('id');
        const newStatus = $(this).data('status');
        $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function (teacher) {
            if (!teacher) return;
            $.ajax({
                url: '../controllers/TeacherController.php?action=update',
                type: 'POST',
                data: { ...teacher, Teach_status: newStatus },
                success: function (res) {
                    reloadTable();
                }
            });
        });
        $('.status-dropdown-menu').addClass('hidden');
    });

    // Delete button
    $('#teacherTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'ลบผู้ใช้?',
            text: 'คุณแน่ใจว่าต้องการลบผู้ใช้นี้หรือไม่',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../controllers/TeacherController.php?action=delete',
                    type: 'POST',
                    data: { Teach_id: id },
                    success: function (response) {
                        let res = {};
                        try { res = typeof response === 'object' ? response : JSON.parse(response); } catch { }
                        if (res.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'ลบผู้ใช้เรียบร้อยแล้ว', showConfirmButton: false, timer: 1500 });
                            reloadTable();
                        } else {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'เกิดข้อผิดพลาดในการลบผู้ใช้', showConfirmButton: false, timer: 1500 });
                        }
                    }
                });
            }
        });
    });

    // Reset password button
    $('#teacherTable').on('click', '.btn-resetpwd', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'รีเซ็ตรหัสผ่าน?',
            text: 'คุณต้องการรีเซ็ตรหัสผ่านของผู้ใช้นี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e42',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'รีเซ็ต',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../controllers/TeacherController.php?action=resetpwd',
                    type: 'POST',
                    data: { Teach_id: id },
                    success: function (response) {
                        let res = {};
                        try { res = typeof response === 'object' ? response : JSON.parse(response); } catch { }
                        if (res.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว', showConfirmButton: false, timer: 1500 });
                        } else {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน', showConfirmButton: false, timer: 1500 });
                        }
                    }
                });
            }
        });
    });

    // Close dropdowns on document click
    $(document).on('click', function () {
        $('.status-dropdown-menu, .dep-dropdown-menu, .role-dropdown-menu').addClass('hidden');
    });
});
