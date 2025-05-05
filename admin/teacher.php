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
    const checked = teacher.Teach_status == 1 ? 'checked' : '';
    return `
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="toggle-status" data-id="${teacher.Teach_id}" ${checked}>
            <span class="ml-2 ${current.color} font-semibold">
                ${current.text}
            </span>
        </label>
    `;
}
function renderRole(role) {
    if (role === 'ADM') return '🛡️ ผู้ดูแลระบบ';
    if (role === 'HOD') return '👨‍💼 หัวหน้ากลุ่มสาระ';
    if (role === 'VP') return '👔 รองผู้บริหาร';
    if (role === 'OF') return '📋 เจ้าหน้าที่';
    if (role === 'DIR') return '🏫 ผู้อำนวยการ';
    if (role === 'T') return '👩‍🏫 ครู';
    return role;
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
function getStatusOptions(selected) {
    return `
        <option value="1" ${selected == 1 ? 'selected' : ''}>🟢 ปกติ</option>
        <option value="2" ${selected == 2 ? 'selected' : ''}>🚚 ย้าย</option>
        <option value="3" ${selected == 3 ? 'selected' : ''}>🎉 เกษียณ</option>
        <option value="4" ${selected == 4 ? 'selected' : ''}>🏠 ลาออก</option>
        <option value="9" ${selected == 9 ? 'selected' : ''}>⚰️ เสียชีวิต</option>
    `;
}
function getDepartmentOptions(selected) {
    let html = '<option value="">-- เลือกกลุ่มสาระ --</option>';
    if (!window._departments) return html;
    window._departments.forEach(dep => {
        html += `<option value="${dep.name}" ${selected === dep.name ? 'selected' : ''}>${dep.name}</option>`;
    });
    return html;
}
function reloadTable() {
    $.getJSON('../controllers/TeacherController.php?action=list', function(data) {
        let tbody = '';
        data.forEach(function(teacher) {
            tbody += `<tr>
                <td class="px-4 py-2 text-center">${teacher.Teach_id}</td>
                <td class="px-4 py-2 text-left">${teacher.Teach_name}</td>
                <td class="px-4 py-2 text-center">${teacher.Teach_major}</td>
                <td class="px-4 py-2 text-center">${renderRole(teacher.role_ckteach)}</td>
                <td class="px-4 py-2 text-center">${renderStatusSwitch(teacher)}</td>
                <td class="px-4 py-2 text-center flex gap-2 justify-center">
                    <button class="btn-edit bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded flex items-center gap-1" data-id="${teacher.Teach_id}">✏️ แก้ไข</button>
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
                        Swal.fire('สำเร็จ', 'แก้ไขข้อมูลเรียบร้อยแล้ว', 'success');
                        if ($.fn.DataTable.isDataTable('#teacherTable')) {
                            $('#teacherTable').DataTable().destroy();
                        }
                        reloadTable();
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'ไม่สามารถแก้ไขข้อมูลได้', 'error');
                    }
                });
            } else {
                $.ajax({
                    url: '../controllers/TeacherController.php?action=create',
                    type: 'POST',
                    data: result.value,
                    success: function(res) {
                        Swal.fire('สำเร็จ', 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว', 'success');
                        if ($.fn.DataTable.isDataTable('#teacherTable')) {
                            $('#teacherTable').DataTable().destroy();
                        }
                        reloadTable();
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'ไม่สามารถเพิ่มผู้ใช้ได้', 'error');
                    }
                });
            }
        }
    });
}
$(document).ready(function() {
    $.getJSON('../controllers/DepartmentController.php?action=list', function(departments) {
        window._departments = departments;
        reloadTable();
    });

    $('#btnAddTeacher').on('click', function() {
        showTeacherModal('create');
    });

    $('#teacherTable').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.getJSON('../controllers/TeacherController.php?action=get&id=' + encodeURIComponent(id), function(teacher) {
            showTeacherModal('edit', teacher);
        });
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
                            Swal.fire('สำเร็จ', 'ลบผู้ใช้เรียบร้อยแล้ว', 'success');
                            reloadTable();
                        } else {
                            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการลบผู้ใช้', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการลบผู้ใช้', 'error');
                    }
                });
            }
        });
    });

    $('#teacherTable').on('change', '.toggle-status', function() {
        const id = $(this).data('id');
        const newStatus = $(this).is(':checked') ? '1' : '4';
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
                    const statusMap = {
                        1: { text: '🟢 ปกติ', color: 'text-green-600' },
                        2: { text: '🚚 ย้าย', color: 'text-blue-500' },
                        3: { text: '🎉 เกษียณ', color: 'text-yellow-600' },
                        4: { text: '🏠 ลาออก', color: 'text-gray-500' },
                        9: { text: '⚰️ เสียชีวิต', color: 'text-red-600' }
                    };
                    const current = statusMap[newStatus] || { text: 'ไม่ทราบ', color: 'text-gray-400' };
                    const label = $(document).find(`.toggle-status[data-id="${id}"]`).next('span');
                    label.removeClass('text-green-600 text-blue-500 text-yellow-600 text-gray-500 text-red-600 text-gray-400');
                    label.addClass(current.color).text(current.text);
                },
                error: function() {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถอัปเดตสถานะได้', 'error');
                    $(document).find(`.toggle-status[data-id="${id}"]`).prop('checked', !$(this).is(':checked'));
                }
            });
        });
    });
});
</script>
<?php require_once('script.php'); ?>
</body>
</html>
``` 
