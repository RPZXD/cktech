<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once('../models/Teacher.php');
$teacherModel = new \App\Models\Teacher();
$teachers = $teacherModel->getAll();
// โหลด config
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
require_once('header.php');
?>
<style>
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}
.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0;
  right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}
.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .toggle-slider {
  background-color: #4ade80; /* green-400 */
}
input:checked + .toggle-slider:before {
  transform: translateX(26px);
}
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-tailwind@5/tailwind.min.css">
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">
    <?php require_once('wrapper.php'); ?>
    <div class="content-wrapper bg-gray-50">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center">
                        👤 จัดการผู้ใช้ (ครู)
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container mx-auto py-8 flex justify-center">
                <div class="max-w-6xl w-full">
                    <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center text-center border border-blue-100">
                        <div class="w-full flex justify-end mb-4">
                            <button id="btnAddTeacher" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded font-semibold transition flex items-center gap-2">➕ เพิ่มผู้ใช้ใหม่</button>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <!-- Filter controls -->
                            <div class="flex flex-wrap gap-4 mb-4 items-center">
                                <select id="filter-major" class="border border-blue-200 rounded px-2 py-1">
                                    <option value="">-- กลุ่มสาระทั้งหมด --</option>
                                </select>
                                <select id="filter-role" class="border border-blue-200 rounded px-2 py-1">
                                    <option value="">-- บทบาททั้งหมด --</option>
                                    <option value="T">👩‍🏫 ครู</option>
                                    <option value="HOD">👨‍💼 หัวหน้ากลุ่มสาระ</option>
                                    <option value="VP">👔 รองผู้บริหาร</option>
                                    <option value="OF">📋 เจ้าหน้าที่</option>
                                    <option value="DIR">🏫 ผู้อำนวยการ</option>
                                    <option value="ADM">🛡️ ผู้ดูแลระบบ</option>
                                </select>
                                <select id="filter-status" class="border border-blue-200 rounded px-2 py-1">
                                    <option value="">-- สถานะทั้งหมด --</option>
                                    <option value="1">🟢 ปกติ</option>
                                    <option value="2">🚚 ย้าย</option>
                                    <option value="3">🎉 เกษียณ</option>
                                    <option value="4">🏠 ลาออก</option>
                                    <option value="9">⚰️ เสียชีวิต</option>
                                </select>
                                <button id="filter-clear" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">ล้างตัวกรอง</button>
                            </div>
                            <table id="teacherTable" class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-blue-100">
                                <tr>
                                    <th class="px-4 py-2 text-center font-semibold">🆔 รหัสครู</th>
                                    <th class="px-4 py-2 text-center font-semibold">👩‍🏫 ชื่อครู</th>
                                    <th class="px-4 py-2 text-center font-semibold">🏢 กลุ่มสาระ</th>
                                    <th class="px-4 py-2 text-center font-semibold">🛡️ บทบาท</th>
                                    <th class="px-4 py-2 text-center font-semibold">✅ สถานะ</th>
                                    <th class="px-4 py-2 text-center font-semibold">⚙️ จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <!-- JS will fill -->
                            </tbody>
                        </table>
                        </div>
                        <div class="mt-4 text-sm text-gray-500">* สามารถเพิ่ม/แก้ไข/ลบผู้ใช้ได้ในอนาคต</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function renderStatusSwitch(teacher) {
    const statusMap = {
        1: { text: '🟢 ปกติ', color: 'text-green-600' },
        2: { text: '🚚 ย้าย', color: 'text-blue-500' },
        3: { text: '🎉 เกษียณ', color: 'text-yellow-600' },
        4: { text: '🏠 ลาออก', color: 'text-gray-500' },
        9: { text: '⚰️ เสียชีวิต', color: 'text-red-600' }
    };
    const current = statusMap[teacher.Teach_status] || { text: 'ไม่ทราบ', color: 'text-gray-400' };
    return `
        <div class="relative inline-block text-left">
            <button type="button" class="status-dropdown-btn ${current.color} font-semibold flex items-center gap-1" data-id="${teacher.Teach_id}">
                ${current.text}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div class="status-dropdown-menu absolute z-10 mt-1 hidden bg-white border border-gray-200 rounded shadow-lg min-w-[120px]" data-id="${teacher.Teach_id}">
                <div class="py-1">
                    <button class="status-option block w-full text-left px-4 py-2 text-green-600 hover:bg-gray-100" data-status="1">🟢 ปกติ</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-blue-500 hover:bg-gray-100" data-status="2">🚚 ย้าย</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-yellow-600 hover:bg-gray-100" data-status="3">🎉 เกษียณ</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-gray-500 hover:bg-gray-100" data-status="4">🏠 ลาออก</button>
                    <button class="status-option block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100" data-status="9">⚰️ เสียชีวิต</button>
                </div>
            </div>
        </div>
    `;
}
function renderDepartmentDropdown(teacher) {
    let options = '';
    if (!window._departments) return teacher.Teach_major;
    window._departments.forEach(dep => {
        options += `<button class="dep-option block w-full text-left px-4 py-2 hover:bg-gray-100" data-value="${dep.name}">${dep.name}</button>`;
    });
    return `
        <div class="relative inline-block text-left">
            <button type="button" class="dep-dropdown-btn font-semibold flex items-center gap-1" data-id="${teacher.Teach_id}">
                ${teacher.Teach_major || '--'}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div class="dep-dropdown-menu absolute z-10 mt-1 hidden bg-white border border-gray-200 rounded shadow-lg min-w-[120px]" data-id="${teacher.Teach_id}">
                <div class="py-1">${options}</div>
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
        options += `<button class="role-option block w-full text-left px-4 py-2 hover:bg-gray-100" data-value="${r.val}">${r.label}</button>`;
    });
    let current = roles.find(r => r.val === teacher.role_ckteach);
    return `
        <div class="relative inline-block text-left">
            <button type="button" class="role-dropdown-btn font-semibold flex items-center gap-1" data-id="${teacher.Teach_id}">
                ${current ? current.label : teacher.role_ckteach}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div class="role-dropdown-menu absolute z-10 mt-1 hidden bg-white border border-gray-200 rounded shadow-lg min-w-[120px]" data-id="${teacher.Teach_id}">
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
    $.getJSON('../controllers/TeacherController.php?action=list', function(data) {
        // Apply filter
        const major = $('#filter-major').val();
        const role = $('#filter-role').val();
        const status = $('#filter-status').val();
        let tbody = '';
        let filtered = data.filter(function(teacher) {
            let ok = true;
            if (major && teacher.Teach_major !== major) ok = false;
            if (role && teacher.role_ckteach !== role) ok = false;
            if (status && String(teacher.Teach_status) !== String(status)) ok = false;
            return ok;
        });
        filtered.forEach(function(teacher) {
            tbody += `<tr data-id="${teacher.Teach_id}">
                <td class="px-4 py-2 text-center">${teacher.Teach_id}</td>
                <td class="px-4 py-2 text-left editable" data-field="Teach_name">${teacher.Teach_name}</td>
                <td class="px-4 py-2 text-center editable" data-field="Teach_major" data-type="dropdown">${renderDepartmentDropdown(teacher)}</td>
                <td class="px-4 py-2 text-center editable" data-field="role_ckteach" data-type="dropdown">${renderRoleDropdown(teacher)}</td>
                <td class="px-4 py-2 text-center">${renderStatusSwitch(teacher)}</td>
                <td class="px-4 py-2 text-center flex gap-2 justify-center">
                    <button class="btn-resetpwd bg-gray-400 hover:bg-gray-500 text-white px-2 py-1 rounded flex items-center gap-1" data-id="${teacher.Teach_id}">🔑 รีเซ็ตรหัสผ่าน</button>
                    <button class="btn-delete bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded flex items-center gap-1" data-id="${teacher.Teach_id}">🗑️ ลบ</button>
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
            <form id="teacherForm" class="space-y-3 text-left">
                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1 text-blue-700">🆔 รหัสครู</label>
                    <input type="text" id="Teach_id" class=" w-full border border-blue-200 rounded focus:ring-2 focus:ring-blue-400" placeholder="รหัสครู" value="${teacher.Teach_id || ''}" ${type === 'edit' ? 'readonly' : ''} required>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1 text-blue-700">👩‍🏫 ชื่อครู</label>
                    <input type="text" id="Teach_name" class=" w-full border border-blue-200 rounded focus:ring-2 focus:ring-blue-400" placeholder="ชื่อครู" value="${teacher.Teach_name || ''}" required>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1 text-blue-700">🏢 กลุ่มสาระ</label>
                    <select id="Teach_major" class=" w-full border border-blue-200 rounded focus:ring-2 focus:ring-blue-400">${getDepartmentOptions(teacher.Teach_major || '')}</select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1 text-blue-700">🛡️ บทบาท</label>
                    <select id="role_ckteach" class=" w-full border border-blue-200 rounded focus:ring-2 focus:ring-blue-400">${getRoleOptions(teacher.role_ckteach || '')}</select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1 text-blue-700">✅ สถานะ</label>
                    <select id="Teach_status" class=" w-full border border-blue-200 rounded focus:ring-2 focus:ring-blue-400">${getStatusOptions(teacher.Teach_status ?? 1)}</select>
                </div>
            </form>
        `,
        customClass: {
            htmlContainer: 'text-left',
            confirmButton: 'bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700',
            cancelButton: 'bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300'
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
            $('#teacherForm input, #teacherForm select').on('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            if (type === 'edit') {
                $.ajax({
                    url: '../controllers/TeacherController.php?action=update',
                    type: 'POST',
                    data: result.value,
                    success: function(res) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'แก้ไขข้อมูลเรียบร้อยแล้ว',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        if ($.fn.DataTable.isDataTable('#teacherTable')) {
                            $('#teacherTable').DataTable().destroy();
                        }
                        reloadTable();
                    },
                    error: function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'ไม่สามารถแก้ไขข้อมูลได้',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            } else {
                $.ajax({
                    url: '../controllers/TeacherController.php?action=create',
                    type: 'POST',
                    data: result.value,
                    success: function(res) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        if ($.fn.DataTable.isDataTable('#teacherTable')) {
                            $('#teacherTable').DataTable().destroy();
                        }
                        reloadTable();
                    },
                    error: function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'ไม่สามารถเพิ่มผู้ใช้ได้',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        }
    });
}
$(document).ready(function() {
    $.getJSON('../controllers/DepartmentController.php?action=list', function(departments) {
        window._departments = departments;
        // Fill filter-major
        let html = '<option value="">-- กลุ่มสาระทั้งหมด --</option>';
        departments.forEach(dep => {
            html += `<option value="${dep.name}">${dep.name}</option>`;
        });
        $('#filter-major').html(html);
        reloadTable();
    });

    $('#btnAddTeacher').on('click', function() {
        showTeacherModal('create');
    });

    // Filter events
    $('#filter-major, #filter-role, #filter-status').on('change', function() {
        reloadTable();
    });
    $('#filter-clear').on('click', function() {
        $('#filter-major').val('');
        $('#filter-role').val('');
        $('#filter-status').val('');
        reloadTable();
    });

    $('#teacherTable').on('click', 'td.editable:not([data-type])', function() {
        if ($(this).find('input').length > 0) return;
        const td = $(this);
        const oldVal = td.text();
        const field = td.data('field');
        const tr = td.closest('tr');
        const id = tr.data('id');
        td.html(`<input type="text" class="inline-edit-input w-full border border-blue-300 rounded px-1" value="${oldVal}" />`);
        td.find('input').focus().select();

        td.find('input').on('blur keydown', function(e) {
            if (e.type === 'blur' || (e.type === 'keydown' && e.key === 'Enter')) {
                const newVal = $(this).val().trim();
                if (newVal !== oldVal && newVal !== '') {
                    // ดึงค่าปัจจุบันจาก attributes data-* ของ tr
                    const Teach_id = id;
                    // ดึงค่ากลุ่มสาระจาก dropdown ปุ่ม
                    let Teach_major = tr.find('td[data-field="Teach_major"] .dep-dropdown-btn').length
                        ? tr.find('td[data-field="Teach_major"] .dep-dropdown-btn').contents().filter(function() {
                            return this.nodeType === 3;
                        }).text().trim()
                        : '';
                    // ดึงค่าบทบาทจาก dropdown ปุ่ม
                    let role_ckteach = tr.find('td[data-field="role_ckteach"] .role-dropdown-btn').length
                        ? tr.find('td[data-field="role_ckteach"] .role-dropdown-btn').contents().filter(function() {
                            return this.nodeType === 3;
                        }).text().trim()
                        : '';
                    // ดึงค่าสถานะจากปุ่ม
                    let Teach_status = tr.find('.status-dropdown-btn').length
                        ? (() => {
                            const btn = tr.find('.status-dropdown-btn');
                            const statusText = btn.contents().filter(function() {
                                return this.nodeType === 3;
                            }).text().trim();
                            const statusMap = {
                                '🟢 ปกติ': 1,
                                '🚚 ย้าย': 2,
                                '🎉 เกษียณ': 3,
                                '🏠 ลาออก': 4,
                                '⚰️ เสียชีวิต': 9
                            };
                            for (const [k, v] of Object.entries(statusMap)) {
                                if (statusText.startsWith(k)) return v;
                            }
                            return 1;
                        })()
                        : 1;
                    // แปลง label เป็น code สำหรับ role_ckteach
                    const roleMap = {
                        '🛡️ ผู้ดูแลระบบ': 'ADM',
                        '👨‍💼 หัวหน้ากลุ่มสาระ': 'HOD',
                        '👔 รองผู้บริหาร': 'VP',
                        '📋 เจ้าหน้าที่': 'OF',
                        '🏫 ผู้อำนวยการ': 'DIR',
                        '👩‍🏫 ครู': 'T'
                    };
                    if (roleMap[role_ckteach]) role_ckteach = roleMap[role_ckteach];

                    $.ajax({
                        url: '../controllers/TeacherController.php?action=update',
                        type: 'POST',
                        data: {
                            Teach_id: Teach_id,
                            Teach_name: newVal,
                            Teach_major: Teach_major,
                            role_ckteach: role_ckteach,
                            Teach_status: Teach_status
                        },
                        success: function(res) {
                            td.text(newVal);
                            Swal.fire({toast:true,position:'top-end',icon:'success',title:'บันทึกแล้ว',showConfirmButton:false,timer:1200});
                            reloadTable();
                        },
                        error: function() {
                            td.text(oldVal);
                            Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                        }
                    });
                } else {
                    td.text(oldVal);
                }
            }
        });
    });

    $('#teacherTable').on('click', '.dep-dropdown-btn', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        $('.dep-dropdown-menu').not(`[data-id="${id}"]`).addClass('hidden');
        $(`.dep-dropdown-menu[data-id="${id}"]`).toggleClass('hidden');
    });
    $('#teacherTable').on('click', '.dep-option', function(e) {
        e.stopPropagation();
        const menu = $(this).closest('.dep-dropdown-menu');
        const id = menu.data('id');
        const newVal = $(this).data('value');
        $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function(teacher) {
            if (!teacher) return;
            $.ajax({
                url: '../controllers/TeacherController.php?action=update',
                type: 'POST',
                data: {
                    Teach_id: id,
                    Teach_name: teacher.Teach_name,
                    Teach_major: newVal,
                    role_ckteach: teacher.role_ckteach,
                    Teach_status: teacher.Teach_status
                },
                success: function(res) {
                    Swal.fire({toast:true,position:'top-end',icon:'success',title:'บันทึกแล้ว',showConfirmButton:false,timer:1200});
                    reloadTable();
                },
                error: function() {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                }
            });
        });
        $('.dep-dropdown-menu').addClass('hidden');
    });

    $('#teacherTable').on('click', '.role-dropdown-btn', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        $('.role-dropdown-menu').not(`[data-id="${id}"]`).addClass('hidden');
        $(`.role-dropdown-menu[data-id="${id}"]`).toggleClass('hidden');
    });
    $('#teacherTable').on('click', '.role-option', function(e) {
        e.stopPropagation();
        const menu = $(this).closest('.role-dropdown-menu');
        const id = menu.data('id');
        const newVal = $(this).data('value');
        $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function(teacher) {
            if (!teacher) return;
            $.ajax({
                url: '../controllers/TeacherController.php?action=update',
                type: 'POST',
                data: {
                    Teach_id: id,
                    Teach_name: teacher.Teach_name,
                    Teach_major: teacher.Teach_major,
                    role_ckteach: newVal,
                    Teach_status: teacher.Teach_status
                },
                success: function(res) {
                    Swal.fire({toast:true,position:'top-end',icon:'success',title:'บันทึกแล้ว',showConfirmButton:false,timer:1200});
                    reloadTable();
                },
                error: function() {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                }
            });
        });
        $('.role-dropdown-menu').addClass('hidden');
    });

    $('#teacherTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        console.log('delete ID:',id);
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
                    success: function(response) {
                        let res = {};
                        try { res = typeof response === 'object' ? response : JSON.parse(response); } catch {}
                        if (res.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'ลบผู้ใช้เรียบร้อยแล้ว',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            reloadTable();
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาดในการลบผู้ใช้',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาดในการลบผู้ใช้',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        });
    });

    $('#teacherTable').on('click', '.btn-resetpwd', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'รีเซ็ตรหัสผ่าน?',
            text: 'คุณต้องการรีเซ็ตรหัสผ่านของผู้ใช้นี้หรือไม่? (รหัสผ่านจะถูกตั้งค่าใหม่เป็นค่าเริ่มต้น)',
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
                    success: function(response) {
                        let res = {};
                        try { res = typeof response === 'object' ? response : JSON.parse(response); } catch {}
                        if (res.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        });
    });

    $('#teacherTable').on('click', '.status-dropdown-btn', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        $('.status-dropdown-menu').not(`[data-id="${id}"]`).addClass('hidden');
        $(`.status-dropdown-menu[data-id="${id}"]`).toggleClass('hidden');
    });

    $('#teacherTable').on('click', '.status-option', function(e) {
        e.stopPropagation();
        const id = $(this).closest('.status-dropdown-menu').data('id');
        const newStatus = $(this).data('status');
        $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function(teacher) {
            if (!teacher) return;
            $.ajax({
                url: '../controllers/TeacherController.php?action=update',
                type: 'POST',
                data: {
                    Teach_id: id,
                    Teach_name: teacher.Teach_name,
                    Teach_major: teacher.Teach_major,
                    role_ckteach: teacher.role_ckteach,
                    Teach_status: newStatus
                },
                success: function(res) {
                    reloadTable();
                },
                error: function() {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถอัปเดตสถานะได้', 'error');
                }
            });
        });
        $('.status-dropdown-menu').addClass('hidden');
    });

    $(document).on('click', function() {
        $('.status-dropdown-menu').addClass('hidden');
        $('.dep-dropdown-menu').addClass('hidden');
        $('.role-dropdown-menu').addClass('hidden');
    });
});
</script>
<?php require_once('script.php'); ?>
</body>
</html>