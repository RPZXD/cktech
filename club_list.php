<?php 

// Read configuration from JSON file
$config = json_decode(file_get_contents('config.json'), true);
$global = $config['global'];

require_once('header.php');

?>
<body class="hold-transition sidebar-mini layout-fixed light-mode">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h5 class="m-0">รายการชุมนุม</h5>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">

            <div class="card">
                <div class="card-header bg-blue-600 text-white font-semibold text-lg">
                    รายการชุมนุม
                </div>
                <div class="card-body">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <div class="flex items-center space-x-2 mb-2 md:mb-0">
                            <label for="grade-filter" class="block text-gray-700 font-medium">ระดับชั้น:</label>
                            <select id="grade-filter" class="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">ทั้งหมด</option>
                                <option value="ม.1">ม.1</option>
                                <option value="ม.2">ม.2</option>
                                <option value="ม.3">ม.3</option>
                                <option value="ม.4">ม.4</option>
                                <option value="ม.5">ม.5</option>
                                <option value="ม.6">ม.6</option>
                            </select>
                        </div>
                        
                    </div>
                </div>
            </div>


            <div class="card">  
                
           
            <div class="card-body">
                <p class="text-gray-700 mb-4">กรุณาเลือกชุมนุมที่ต้องการดูรายละเอียด</p>
                <div class="overflow-x-auto">
                    <!-- DataTables CSS -->
                    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
                    <table id="club-table" class="min-w-full bg-white border border-gray-200 rounded shadow display">
                        <thead>
                            <tr class="bg-blue-500 text-white font-semibold">
                                <th class="py-2 px-4 text-center border-b">รหัสชุมนุม</th>
                                <th class="py-2 px-4 text-center border-b">ชื่อชุมนุม</th>
                                <th class="py-2 px-4 text-center border-b">รายละเอียด</th>
                                <th class="py-2 px-4 text-center border-b">ครูที่ปรึกษาชุมนุม</th>
                                <th class="py-2 px-4 text-center border-b">ระดับชั้นที่เปิด</th>
                                <th class="py-2 px-4 text-center border-b">จำนวนที่รับสมัคร</th>
                                <th class="py-2 px-4 text-center border-b">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="club-table-body">
                            <!-- DataTables will populate here -->
                        </tbody>
                    </table>
                </div>

                </script>
            </div>
        </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
    <?php require_once('footer.php');?>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {


    var table = $('#club-table').DataTable({
        "ajax": "controllers/ClubController.php?action=list",
        "columns": [
            { "data": "club_id", "visible": false }, // Hide the club_id column
            { "data": "club_name", className: "text-center" },
            { "data": "description" },
            { "data": "advisor_teacher_name" },
            { "data": "grade_levels", className: "text-center" },
            { 
                "data": null,
                "render": function(data, type, row) {
                    var current = row.current_members_count ? parseInt(row.current_members_count) : 0;
                    var max = row.max_members ? parseInt(row.max_members) : 0;
                    var percent = (max > 0) ? Math.round((current / max) * 100) : 0;
                    if (percent > 100) percent = 100;
                    var progressBar = `
                        <div style="min-width:100px">
                            <div style="background:#e5e7eb;border-radius:4px;height:16px;overflow:hidden;">
                                <div style="background:#2563eb;width:${percent}%;height:100%;transition:width 0.3s;" title="${current} / ${max}"></div>
                            </div>
                            <div style="font-size:0.85em;color:#444;text-align:right;">${current} / ${max}</div>
                        </div>
                    `;
                    return progressBar;
                },
                "className": "text-center"
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    var html = '';
                    var currentUser = "<?php echo $_SESSION['username']; ?>";
                    if (row.advisor_teacher == currentUser) {
                        html += '<button class="edit-btn bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded mr-1" data-id="'+row.club_id+'">แก้ไข</button>';
                        html += '<button class="delete-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded" data-id="'+row.club_id+'">ลบ</button>';
                    } else {
                        html = '<span class="text-gray-600 font-semibold">-</span>';
                    }
                    return html;
                },
                "className": "text-center"
            },
        ],
        "columnDefs": [
            { "targets": 0, "visible": false }, // Hide the club_id column
            { "targets": 1, "width": "20%" },
            { "targets": 2, "width": "30%" },
            { "targets": 3, "width": "20%" },
            { "targets": 4, "width": "10%" },
            { "targets": 5, "width": "10%" },
            { "targets": 6, "width": "10%" }
        ],
        "language": {
            "lengthMenu": "แสดง _MENU_ รายการ",
            "zeroRecords": "ไม่พบข้อมูล",
            "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
            "infoEmpty": "ไม่มีข้อมูล",
            "infoFiltered": "(กรองจาก _MAX_ รายการทั้งหมด)",
            "search": "ค้นหา:",
            "paginate": {
                "first": "แรก",
                "last": "สุดท้าย",
                "next": "ถัดไป",
                "previous": "ก่อนหน้า"
            }
        },
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "order": [[0, "asc"]],
        "responsive": true,
        "autoWidth": false,
        "processing": true,
    });

    $('#grade-filter').on('change', function() {
        var grade = $(this).val();
        table.column(4).search(grade).draw();
    });

    // เพิ่ม event สำหรับปุ่มแก้ไข
    $('#club-table').on('click', '.edit-btn', function() {
        var clubId = $(this).data('id');
        var table = $('#club-table').DataTable();
        var rowData = null;
        table.rows().every(function() {
            var d = this.data();
            if (d.club_id == clubId) {
                rowData = d;
                return false; // break
            }
        });
        if (rowData) {
            $('#edit_club_id').val(rowData.club_id);
            $('#edit_club_name').val(rowData.club_name);
            $('#edit_description').val(rowData.description);
            $('#edit-grade-levels-checkboxes input[type="checkbox"]').prop('checked', false);
            if (rowData.grade_levels) {
                var grades = rowData.grade_levels.split(',');
                grades.forEach(function(g) {
                    $('#edit-grade-levels-checkboxes input[type="checkbox"][value="'+g.trim()+'"]').prop('checked', true);
                });
            }
            $('#edit_max_members').val(rowData.max_members);
            $('#edit-club-modal').removeClass('hidden');
        }
    });
    $('#close-edit-modal-btn, #cancel-edit-modal-btn').on('click', function() {
        $('#edit-club-modal').addClass('hidden');
    });

    // ฟอร์มแก้ไขชุมนุม: serialize grade_levels[] เป็น string
    $('#edit-club-form').on('submit', function(e) {
        e.preventDefault();
        var grades = [];
        $('#edit-grade-levels-checkboxes input[name="grade_levels[]"]:checked').each(function() {
            grades.push($(this).val());
        });
        var formData = $(this).serializeArray().filter(function(item) {
            return item.name !== 'grade_levels[]';
        });
        formData.push({name: 'grade_levels', value: grades.join(',')});
        $.ajax({
            url: 'controllers/ClubController.php?action=update',
            method: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#edit-club-modal').addClass('hidden');
                    $('#club-table').DataTable().ajax.reload();
                    Swal.fire('สำเร็จ', 'แก้ไขข้อมูลชุมนุมเรียบร้อยแล้ว', 'success');
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', res.message || 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล', 'error');
                }
            },
            error: function() {
                Swal.fire('เกิดข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
            }
        });
    });

    
});
</script>

<?php require_once('script.php');?>
</body>
</html>
