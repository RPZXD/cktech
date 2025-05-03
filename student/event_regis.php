<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'นักเรียน') {
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];
// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];

require_once('header.php');
?>
<body class="hold-transition sidebar-mini layout-fixed light-mode">
<div class="wrapper">
    <?php require_once('wrapper.php');?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">เข้าร่วมกิจกรรม</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-blue-600 text-white font-semibold text-lg">
                        รายการกิจกรรมเปิดลงทะเบียน
                    </div>
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
                            <table id="event-table" class="min-w-full bg-white border border-gray-200 rounded shadow display">
                                <thead>
                                    <tr class="bg-blue-100 text-blue-900">
                                        <th class="px-4 py-2 text-left">ชื่อกิจกรรม</th>
                                        <th class="px-4 py-2 text-left">วันที่จัด</th>
                                        <th class="px-4 py-2 text-left">ชั่วโมง</th>
                                        <th class="px-4 py-2 text-left">ประเภท</th>
                                        <th class="px-4 py-2 text-left">จำนวนสูงสุด</th>
                                        <th class="px-4 py-2 text-left">สมัคร</th>
                                    </tr>
                                </thead>
                                <tbody id="event-table-body">
                                    <!-- ข้อมูลกิจกรรมจะถูกเติมโดย JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php');?>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('../controllers/EventController.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('event-table-body');
            tbody.innerHTML = '';
            if (data.success && Array.isArray(data.events)) {
                // ดึง code ของแต่ละกิจกรรม (async)
                const fetchCodes = data.events.map(ev =>
                    fetch(`../controllers/EventController.php?activity_id=${ev.id}`)
                        .then(res => res.json())
                        .then(codeRes => ({
                            ...ev,
                            code: codeRes.code
                        }))
                );
                Promise.all(fetchCodes).then(eventsWithCode => {
                    // กรองเฉพาะกิจกรรมที่มี code (เปิดลงทะเบียน)
                    const filtered = eventsWithCode.filter(ev => ev.code);
                    if (filtered.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-gray-500 py-4">ไม่พบกิจกรรมที่เปิดลงทะเบียน</td></tr>`;
                    } else {
                        // ขอข้อมูลกิจกรรมที่นักเรียนลงทะเบียนแล้ว
                        fetch('../controllers/EventController.php?student_registered=true')
                            .then(res => res.json())
                            .then(regData => {
                                // regData = { registered: [activity_id, ...] }
                                let registeredIds = [];
                                if (Array.isArray(regData.registered)) {
                                    registeredIds = regData.registered.map(id => parseInt(id, 10));
                                }
                                 console.log('regData',  regData, 'registered', registeredIds);
                                // console.log(registeredIds);
                                filtered.forEach(ev => {
                                    const thaiDate = new Date(ev.event_date).toLocaleDateString('th-TH', {
                                        year: 'numeric', month: 'long', day: 'numeric'
                                    });

                                    // console.log(ev);
                                    // ตรวจสอบจาก student_activity_logs ว่าลงทะเบียนแล้วหรือยัง
                                    const registered = registeredIds.includes(parseInt(ev.id, 10));
                                    // console.log(registeredIds, ev.id, registered);
                                    tbody.innerHTML += `
                                        <tr>
                                            <td class="px-4 py-2">${ev.title}</td>
                                            <td class="px-4 py-2">${thaiDate}</td>
                                            <td class="px-4 py-2">${ev.hours}</td>
                                            <td class="px-4 py-2">${ev.category}</td>
                                            <td class="px-4 py-2">${ev.max_students}</td>
                                            <td class="px-4 py-2">
                                                ${
                                                    // ถ้าลงทะเบียนแล้ว แสดงข้อความแทนปุ่ม
                                                    registered
                                                    ? '<span class="text-green-600 font-semibold">ลงทะเบียนแล้ว</span>'
                                                    : `<button class="register-btn bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded" data-id="${ev.id}">ลงทะเบียน</button>`
                                                }
                                            </td>
                                        </tr>
                                    `;
                                });

                                // clear DataTable ก่อนสร้างใหม่
                                if ($.fn.DataTable.isDataTable('#event-table')) {
                                    $('#event-table').DataTable().clear().destroy();
                                }
                                $('#event-table').DataTable({
                                    language: {
                                        search: "ค้นหา:",
                                        lengthMenu: "แสดง _MENU_ รายการ",
                                        info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                                        paginate: {
                                            first: "หน้าแรก",
                                            last: "หน้าสุดท้าย",
                                            next: "ถัดไป",
                                            previous: "ก่อนหน้า"
                                        },
                                        zeroRecords: "ไม่พบข้อมูล",
                                        infoEmpty: "ไม่มีข้อมูลให้แสดง",
                                        infoFiltered: "(กรองจาก _MAX_ รายการทั้งหมด)"
                                    }
                                });

                                // สมัครกิจกรรม
                                document.querySelectorAll('.register-btn').forEach(btn => {
                                    btn.onclick = function() {
                                        Swal.fire({
                                            title: 'กรอกรหัสกิจกรรม',
                                            input: 'text',
                                            inputLabel: 'กรุณากรอก code สำหรับกิจกรรมนี้',
                                            inputPlaceholder: 'กรอกรหัสกิจกรรมที่ได้รับ',
                                            showCancelButton: true,
                                            confirmButtonText: 'ยืนยัน',
                                            cancelButtonText: 'ยกเลิก',
                                            inputValidator: (value) => {
                                                if (!value) {
                                                    return 'กรุณากรอกรหัสกิจกรรม';
                                                }
                                            }
                                        }).then((result) => {
                                            if (result.isConfirmed && result.value) {
                                                const code = result.value.trim();
                                                // ส่งข้อมูลไป backend
                                                fetch('../controllers/EventController.php', {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json' },
                                                    body: JSON.stringify({
                                                        action: 'register_activity',
                                                        activity_id: btn.dataset.id,
                                                        code: code
                                                    })
                                                })
                                                .then(res => res.json())
                                                .then(res => {
                                                    if (res.success) {
                                                        Swal.fire('สำเร็จ', 'ลงทะเบียนกิจกรรมเรียบร้อย', 'success').then(() => {
                                                            location.reload();
                                                        });
                                                    } else {
                                                        Swal.fire('ผิดพลาด', res.message || 'ไม่สามารถลงทะเบียนได้', 'error');
                                                    }
                                                })
                                                .catch(() => Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์', 'error'));
                                            }
                                        });
                                    };
                                });
                            });
                    }
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-gray-500 py-4">ไม่พบกิจกรรมที่เปิดลงทะเบียน</td></tr>`;
            }
        });
});
</script>
<?php require_once('script.php');?>
</body>
</html>
