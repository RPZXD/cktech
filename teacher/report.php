<?php 
session_start();
// เช็ค session และ role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ครู') {
    header('Location: ../login.php');
    exit;
}
// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];

require_once('header.php');
?>
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper bg-gray-50">

  <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center">
              📑 รายงานการสอน
            </h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <section class="content">
      <div class="container-fluid flex justify-center">
        <div class="w-full max-w-8xl">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="mb-4 flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold mb-2 flex items-center gap-2">📑 รายงานการสอน</h2>
                <p class="text-gray-600">แสดงรายการรายงานการสอนของครู</p>
              </div>
              <button id="btnAddReport" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow flex items-center gap-2">
                ➕ เพิ่มรายงาน
              </button>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                <thead class="bg-blue-100">
                  <tr>
                    <th class="py-2 px-3 border-b text-center">📅 วันที่</th>
                    <th class="py-2 px-3 border-b text-center">📖 วิชา</th>
                    <th class="py-2 px-3 border-b text-center">🏫 ห้อง</th>
                    <th class="py-2 px-3 border-b text-center">⏰ คาบ</th>
                    <th class="py-2 px-3 border-b text-center">📝 แผน/หัวข้อ</th>
                    <th class="py-2 px-3 border-b text-center">👨‍🏫 กิจกรรม</th>
                    <th class="py-2 px-3 border-b text-center">🙋‍♂️ ขาดเรียน</th>
                    <th class="py-2 px-3 border-b text-center">🔍 ดูรายละเอียด</th>
                  </tr>
                </thead>
                <tbody id="reportTableBody">

                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal สำหรับเพิ่ม/แก้ไขรายงาน -->
      <div id="modalAddReport" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 relative overflow-y-auto max-h-screen">
          <button id="closeModalAddReport" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
          <h2 id="modalReportTitle" class="text-xl font-bold mb-4 flex items-center gap-2">➕ เพิ่มรายงานการสอน</h2>
          <form id="formAddReport" class="space-y-3" enctype="multipart/form-data">
            <div>
              <label class="block mb-1 font-medium">วันที่ <span class="text-red-500">*</span></label>
              <input type="date" name="report_date" id="reportDate" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
            <div>
              <label class="block mb-1 font-medium">ชื่อวิชา <span class="text-red-500">*</span></label>
              <select name="subject_id" id="subjectSelect" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                <option value="">-- เลือกวิชา --</option>
                <!-- JS will fill options -->
              </select>
            </div>
            <div id="classRoomSelectArea">
              <!-- ห้องเรียนและคาบจะถูกเติมโดย JS -->
            </div>
            <div>
              <label class="block mb-1 font-medium">เลขแผนการสอน</label>
              <input type="text" name="plan_number" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
            <div>
              <label class="block mb-1 font-medium">หัวข้อ/สาระการเรียนรู้</label>
              <textarea name="plan_topic" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>
            <div>
              <label class="block mb-1 font-medium">กิจกรรมการเรียนรู้</label>
              <textarea name="activity" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>
            <div>
              <label class="block mb-1 font-medium">รายชื่อนักเรียนที่ขาดเรียน</label>
              <div id="studentAttendanceArea">
                <!-- JS จะเติมรายชื่อนักเรียนและ checkbox สถานะ -->
                <div class="text-gray-400 text-sm">เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน</div>
              </div>
              <textarea name="absent_students" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300 hidden"></textarea>
            </div>
            <div>
              <label class="block mb-1 font-medium">สะท้อนคิด (K - ความรู้)</label>
              <textarea name="reflection_k" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>
            <div>
              <label class="block mb-1 font-medium">สะท้อนคิด (P - ทักษะ)</label>
              <textarea name="reflection_p" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>
            <div>
              <label class="block mb-1 font-medium">สะท้อนคิด (A - เจตคติ)</label>
              <textarea name="reflection_a" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>
            <div>
              <label class="block mb-1 font-medium">ปัญหา/อุปสรรค</label>
              <textarea name="problems" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>
            <div>
              <label class="block mb-1 font-medium">ข้อเสนอแนะ</label>
              <textarea name="suggestions" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>
            <div id="roomImageInputsArea" class="mb-2"></div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" id="cancelAddReport" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">ยกเลิก</button>
              <button type="submit" class="px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white">บันทึก</button>
            </div>
          </form>
        </div>
      </div>
      <!-- End Modal -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
    <?php require_once('../footer.php');?>
</div>
<!-- ./wrapper -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script>
document.addEventListener('DOMContentLoaded', function() {
  loadReports();
  loadSubjectsForReport();

  // ฟังก์ชันแปลงวันที่เป็นภาษาไทย
  function formatThaiDate(dateStr) {
    if (!dateStr) return '-';
    const months = [
      '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
      'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const day = d.getDate();
    const month = months[d.getMonth() + 1];
    const year = d.getFullYear() + 543;
    return `${day} ${month} ${year}`;
  }

  function renderDetailBtn(reportId) {
    return `
      <button class="w-full mb-1 bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded transition-colors duration-200 btn-report-detail flex items-center justify-center gap-1 text-xs" data-id="${reportId}">
        👁️ ดู
      </button>
      <button class="w-full mb-1 bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded transition-colors duration-200 btn-edit-report flex items-center justify-center gap-1 text-xs" data-id="${reportId}">
        ✏️ แก้ไข
      </button>
      <button class="w-full mb-1 bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded transition-colors duration-200 btn-delete-report flex items-center justify-center gap-1 text-xs" data-id="${reportId}">
        🗑️ ลบ
      </button>
      <button class="w-full mb-1 bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded transition-colors duration-200 btn-print-report flex items-center justify-center gap-1 text-xs" data-id="${reportId}">
        🖨️ พิมพ์
      </button>
    `;
  }

  function loadReports() {
    fetch('../controllers/TeachingReportController.php?action=list')
      .then(res => res.json())
      .then(data => {
        const tbody = document.getElementById('reportTableBody');
        tbody.innerHTML = '';
        
        if (!data.length) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-400 py-6">ไม่มีข้อมูลรายงานการสอน</td></tr>`;
          // รีเซ็ต DataTable ถ้ามี
          if ($.fn.DataTable.isDataTable('#reportTableBody')) {
            $('#reportTableBody').DataTable().destroy();
          }
          return;
        }

        // เรียงข้อมูลตามวันที่จากล่าสุดไปเก่าสุด
        const sortedData = data.sort((a, b) => {
          const dateA = new Date(a.report_date);
          const dateB = new Date(b.report_date);
          return dateB - dateA; // เรียงจากใหม่ไปเก่า
        });

        sortedData.forEach(report => {
          tbody.innerHTML += `
            <tr class="hover:bg-blue-50 transition-colors duration-200">
              <td class="py-3 px-3 border-b text-center">
                <div class="font-semibold text-blue-700">${formatThaiDate(report.report_date)}</div>
                <div class="text-xs text-gray-500">${getThaiDayOfWeek(report.report_date)}</div>
              </td>
              <td class="py-3 px-3 border-b text-center">
                <div class="font-medium text-gray-800">${report.subject_name || '-'}</div>
              </td>
              <td class="py-3 px-3 border-b text-center">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm font-medium">
                  ม.${report.level}/${report.class_room}
                </span>
              </td>
              <td class="py-3 px-3 border-b text-center">
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-medium">
                  ${report.period_start} - ${report.period_end}
                </span>
              </td>
              <td class="py-3 px-3 border-b text-center">
                <div class="max-w-xs truncate" title="${report.plan_topic || '-'}">
                  ${report.plan_topic ? (report.plan_topic.length > 30 ? report.plan_topic.substring(0, 30) + '...' : report.plan_topic) : '-'}
                </div>
              </td>
              <td class="py-3 px-3 border-b text-center">
                <div class="max-w-xs truncate" title="${report.activity || '-'}">
                  ${report.activity ? (report.activity.length > 30 ? report.activity.substring(0, 30) + '...' : report.activity) : '-'}
                </div>
              </td>
              <td class="py-3 px-3 border-b text-left text-sm">
                <div class="space-y-1">
                  ${report.absent_students ? `
                    <div class="flex items-start gap-1">
                      <span class="text-red-600 font-semibold text-xs">❌ ขาดเรียน:</span>
                      <div class="text-gray-700 text-xs">${report.absent_students.replace(/,\s*/g, ', ')}</div>
                    </div>` : ''}

                  ${report.sick_students ? `
                    <div class="flex items-start gap-1">
                      <span class="text-blue-500 font-semibold text-xs">🤒 ป่วย:</span>
                      <div class="text-gray-700 text-xs">${report.sick_students.replace(/,\s*/g, ', ')}</div>
                    </div>` : ''}

                  ${report.personal_students ? `
                    <div class="flex items-start gap-1">
                      <span class="text-indigo-500 font-semibold text-xs">📝 ลากิจ:</span>
                      <div class="text-gray-700 text-xs">${report.personal_students.replace(/,\s*/g, ', ')}</div>
                    </div>` : ''}

                  ${report.activity_students ? `
                    <div class="flex items-start gap-1">
                      <span class="text-purple-500 font-semibold text-xs">🎉 กิจกรรม:</span>
                      <div class="text-gray-700 text-xs">${report.activity_students.replace(/,\s*/g, ', ')}</div>
                    </div>` : ''}
                    
                  ${!report.absent_students && !report.sick_students && !report.personal_students && !report.activity_students ? 
                    '<span class="text-green-600 text-xs font-medium">✅ เข้าเรียนครบ</span>' : ''}
                </div>
              </td>
              <td class="py-3 px-3 border-b text-center">
                <div class="flex flex-col gap-1">
                  ${renderDetailBtn(report.id)}
                </div>
              </td>
            </tr>
          `;
        });

        // DataTables: apply เฉพาะครั้งแรก
        if (!$.fn.DataTable.isDataTable('.min-w-full')) {
          $('.min-w-full').DataTable({
            language: {
              url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
            },
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            pagingType: 'simple',
            searching: true,
            info: true,
            autoWidth: false,
            order: [[0, 'desc']], // เรียงตามคอลัมน์แรก (วันที่) จากใหม่ไปเก่า
            columnDefs: [
              { targets: 0, width: '12%', type: 'date' },
              { targets: 1, width: '18%' },
              { targets: 2, width: '8%' },
              { targets: 3, width: '8%' },
              { targets: 4, width: '15%' },
              { targets: 5, width: '15%' },
              { targets: 6, width: '18%' },
              { targets: 7, width: '12%', orderable: false }
            ]
          });
        }

        document.querySelectorAll('.btn-report-detail').forEach(btn => {
          btn.addEventListener('click', function() {
            const reportId = btn.getAttribute('data-id');
            showReportDetail(reportId);
          });
        });

        // Event: ลบรายงาน
        document.querySelectorAll('.btn-delete-report').forEach(btn => {
          btn.addEventListener('click', function() {
            const reportId = btn.getAttribute('data-id');
            Swal.fire({
              title: 'ยืนยันการลบ',
              text: 'คุณต้องการลบรายงานนี้หรือไม่?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'ลบ',
              cancelButtonText: 'ยกเลิก'
            }).then(result => {
              if (result.isConfirmed) {
                fetch('../controllers/TeachingReportController.php?action=delete', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: reportId })
                })
                .then(res => res.json())
                .then(result => {
                  if (result.success) {
                    Swal.fire('ลบสำเร็จ', 'ลบรายงานเรียบร้อยแล้ว', 'success');
                    loadReports();
                  } else {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถลบรายงานได้', 'error');
                  }
                })
                .catch(() => {
                  Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                });
              }
            });
          });
        });

         // Event: พิมพ์รายงาน
         document.querySelectorAll('.btn-print-report').forEach(btn => {
          btn.addEventListener('click', function() {
            const reportId = btn.getAttribute('data-id');
            window.open('../teacher/print_report.php?id=' + encodeURIComponent(reportId), '_blank');
          });
        });


        // Event: แก้ไขรายงาน (แสดง modal พร้อมข้อมูลเดิม)
        document.querySelectorAll('.btn-edit-report').forEach(btn => {
          btn.addEventListener('click', function() {
            const reportId = btn.getAttribute('data-id');
            fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
              .then(res => res.json())
              .then(report => {
                editMode = true;
                editReportId = reportId;
                document.getElementById('modalReportTitle').innerHTML = '✏️ แก้ไขรายงานการสอน';
                modalReport.classList.remove('hidden');
                // เติมข้อมูลลงฟอร์ม
                formReport.report_date.value = report.report_date;
                formReport.subject_id.value = report.subject_id;
                // trigger change เพื่อโหลดห้อง/คาบ
                formReport.subject_id.dispatchEvent(new Event('change'));
                setTimeout(() => {
                  // ห้อง
                  document.querySelectorAll('.report-class-room-checkbox').forEach(cb => {
                    cb.checked = (cb.value.replace('ห้อง ', '') === report.class_room);
                  });
                  // trigger change เพื่อโหลดคาบ
                  document.querySelectorAll('.report-class-room-checkbox').forEach(cb => {
                    if (cb.checked) {
                      cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                  });
                  setTimeout(() => {
                    // คาบ
                    document.querySelectorAll(`input[name^="periods["]`).forEach(cb => {
                      const [start, end] = cb.value.split('|');
                      if (start === String(report.period_start) && end === String(report.period_end)) {
                        cb.checked = true;
                      }
                    });
                    // ====== Restore attendance status after student table is rendered ======
                    // ดึง class (level) จาก option ที่เลือกใน subjectSelect
                    const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
                    const classValue = selectedOption ? (selectedOption.getAttribute('data-class') || '') : '';
                    const checkedRooms = Array.from(classRoomSelectArea.querySelectorAll('.report-class-room-checkbox:checked')).map(cb => cb.value);
                    const classRoomArr = checkedRooms.map(room => ({
                      class: classValue,
                      room: room.replace('ห้อง ', '')
                    }));
                    // เรียก student table ใหม่ (จะ render studentAttendanceArea)
                    loadStudentsForAttendance(report.subject_id, classRoomArr);

                    // รอ studentAttendanceArea ถูก render แล้วค่อย restore attendance
                    setTimeout(() => {
                      fetch('../controllers/TeachingReportController.php?action=attendance_log&id=' + encodeURIComponent(reportId))
                        .then(res => res.json())
                        .then(attendanceLogs => {
                          if (!Array.isArray(attendanceLogs)) return;
                          const statusMap = {
                            'มาเรียน': 'present',
                            'ขาดเรียน': 'absent',
                            'มาสาย': 'late',
                            'ลาป่วย': 'sick',
                            'ลากิจ': 'personal',
                            'เข้าร่วมกิจกรรม': 'activity'
                          };
                          attendanceLogs.forEach(log => {
                            const stuId = log.student_id;
                            // รองรับกรณีไม่มี class_room ใน log
                            let room = log.class_room;
                            if (!room) {
                              // หา room จาก input ที่มี student_id นี้
                              const input = formReport.querySelector(`input[name^="attendance["][name$="[${stuId}]"]`);
                              if (input) {
                                const match = input.name.match(/^attendance\[(.+?)\]\[\d+\]$/);
                                if (match) room = match[1];
                              }
                            }
                            const status = statusMap[log.status] || 'present';
                            // Set hidden input value
                            const input = room ? formReport.querySelector(`input[name="attendance[${room}][${stuId}]"]`) : null;
                            if (input) input.value = status;
                            // Highlight the correct button
                            const btn = room ? formReport.querySelector(`.attendance-btn[data-stu="${stuId}"][data-room="${room}"][data-status="${status}"]`) : null;
                            if (btn) {
                              // Remove highlight from all buttons for this student/room
                              const parent = btn.parentNode;
                              parent.querySelectorAll('.attendance-btn').forEach(b => {
                                b.classList.remove(
                                  'ring-2',
                                  'ring-green-600',
                                  'ring-red-600',
                                  'ring-yellow-500',
                                  'ring-blue-600',
                                  'ring-indigo-600',
                                  'ring-purple-600',
                                  'opacity-100'
                                );
                              });
                              // Add highlight to the correct button
                              let ringColor = '';
                              if (status === 'present') ringColor = 'ring-green-600';
                              else if (status === 'absent') ringColor = 'ring-red-600';
                              else if (status === 'late') ringColor = 'ring-yellow-500';
                              else if (status === 'sick') ringColor = 'ring-blue-600';
                              else if (status === 'personal') ringColor = 'ring-indigo-600';
                              else if (status === 'activity') ringColor = 'ring-purple-600';
                              btn.classList.add('ring-2', ringColor, 'opacity-100');
                            }
                          });
                        });
                    }, 350); // รอ studentAttendanceArea render เสร็จ (ปรับเวลาได้)
                    // ====== End restore attendance ======
                  }, 200);
                }, 200);
                formReport.plan_number.value = report.plan_number || '';
                formReport.plan_topic.value = report.plan_topic || '';
                formReport.activity.value = report.activity || '';
                formReport.reflection_k.value = report.reflection_k || '';
                formReport.reflection_p.value = report.reflection_p || '';
                formReport.reflection_a.value = report.reflection_a || '';
                formReport.problems.value = report.problems || '';
                formReport.suggestions.value = report.suggestions || '';
                // หมายเหตุ: ไม่เติมรูปภาพเดิม
                // เก็บค่าฟอร์มล่าสุด
                lastFormData = {};
                Array.from(formReport.elements).forEach(el => {
                  if (el.name) lastFormData[el.name] = el.value;
                });
              });
          });
        });
      });
  }

  // Helper: แปลงวันที่ (YYYY-MM-DD) เป็นชื่อวันภาษาไทย
  function getThaiDayOfWeek(dateStr) {
    const days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '-';
    return days[d.getDay()];
  }

  // ห้องเรียนและคาบตามวิชา
  const subjectSelect = document.getElementById('subjectSelect');
  const reportDateInput = document.getElementById('reportDate');
  const classRoomSelectArea = document.getElementById('classRoomSelectArea');
  let subjectClassRooms = {}; // {subject_id: [{class_room, period_start, period_end, day_of_week}, ...]}

  function loadSubjectsForReport() {
    // เรียกเฉพาะวิชาที่เปิดสอนสำหรับ dropdown
    fetch('../controllers/SubjectController.php?action=list&teacherId=' + encodeURIComponent(<?php echo json_encode($_SESSION['user']['Teach_id']); ?>) + '&onlyOpen=1')
      .then(res => res.json())
      .then(data => {
        const select = document.getElementById('subjectSelect');
        if (!select) return;
        select.innerHTML = `<option value="">-- เลือกวิชา --</option>`;
        data.forEach(subject => {
          // ใส่ data-class เป็น level ของวิชา
          select.innerHTML += `<option value="${subject.id}" data-class="${subject.level}">${subject.name}</option>`;
          // เก็บ class_rooms สำหรับแต่ละวิชา
          if (subject.class_periods) {
            subjectClassRooms[subject.id] = subject.class_periods;
          }
        });
      });
  }

  function renderClassRoomCheckboxes(subjectId, reportDate) {
    classRoomSelectArea.innerHTML = '';
    if (!subjectId || !subjectClassRooms[subjectId] || !reportDate) return;
    const thaiDay = getThaiDayOfWeek(reportDate);
    // filter เฉพาะคาบที่ตรงกับวัน
    const rooms = subjectClassRooms[subjectId].filter(r => r.day_of_week === thaiDay);
    // group by class_room
    const roomMap = {};
    rooms.forEach(r => {
      if (!roomMap[r.class_room]) roomMap[r.class_room] = [];
      roomMap[r.class_room].push(r);
    });

    if (Object.keys(roomMap).length === 0) {
      classRoomSelectArea.innerHTML = `<div class="text-red-500">ไม่มีห้องเรียนที่สอนในวัน${thaiDay} สำหรับวิชานี้</div>`;
      return;
    }

    let html = `<label class="block mb-1 font-medium">เลือกห้องเรียน <span class="text-red-500">*</span></label>
      <div class="flex flex-wrap gap-2 mb-2">`;
    Object.keys(roomMap).forEach(room => {
      html += `
        <label class="flex items-center gap-1">
          <input type="checkbox" name="class_room[]" value="${room}" class="form-checkbox text-blue-600 report-class-room-checkbox" />
          <span>${room}</span>
        </label>
      `;
    });
    html += `</div>
      <div id="reportClassPeriodsArea"></div>
    `;
    classRoomSelectArea.innerHTML = html;
  }

  function renderClassPeriodsInputs(subjectId, selectedRooms, reportDate) {
    const area = document.getElementById('reportClassPeriodsArea');
    area.innerHTML = '';
    if (!subjectId || !subjectClassRooms[subjectId] || !selectedRooms.length || !reportDate) return;
    const thaiDay = getThaiDayOfWeek(reportDate);
    const rooms = subjectClassRooms[subjectId].filter(r => r.day_of_week === thaiDay);
    selectedRooms.forEach(room => {
      // ห้องนี้มีคาบอะไรบ้างในวันนั้น
      const periods = rooms.filter(r => r.class_room === room);
      area.innerHTML += `
        <div class="mb-2 border rounded p-2 bg-gray-50">
          <div class="font-semibold text-blue-700 mb-1">${room}</div>
          <div class="flex flex-wrap gap-2">
            ${periods.map((p, idx) => `
              <label class="flex items-center gap-1">
                <input type="checkbox" name="periods[${room}][]" value="${p.period_start}|${p.period_end}|${p.day_of_week}" class="form-checkbox text-green-600 report-period-checkbox" />
                <span>${p.day_of_week} คาบ ${p.period_start}-${p.period_end}</span>
              </label>
            `).join('')}
          </div>
        </div>
      `;
    });
  }

  // เพิ่ม: Render ช่องอัปโหลดรูปภาพแยกตามห้อง
  function renderRoomImageInputs(checkedRooms) {
    const area = document.getElementById('roomImageInputsArea');
    if (!area) return;
    area.innerHTML = '';
    checkedRooms.forEach(room => {
      area.innerHTML += `
        <div class="mb-2 border rounded p-2 bg-gray-50">
          <div class="font-semibold text-blue-700 mb-1">แนบรูปภาพสำหรับห้อง ${room}</div>
          <div class="flex gap-2">
            <div class="w-1/2">
              <label class="block mb-1 font-medium">แนบรูปภาพ 1</label>
              <input type="file" name="image1_${room}" accept="image/*" class="w-full border rounded px-3 py-2" />
            </div>
            <div class="w-1/2">
              <label class="block mb-1 font-medium">แนบรูปภาพ 2</label>
              <input type="file" name="image2_${room}" accept="image/*" class="w-full border rounded px-3 py-2" />
            </div>
          </div>
        </div>
      `;
    });
  }

  subjectSelect.addEventListener('change', function() {
    renderClassRoomCheckboxes(this.value, reportDateInput.value);
  });
  reportDateInput.addEventListener('change', function() {
    renderClassRoomCheckboxes(subjectSelect.value, this.value);
  });

  // เมื่อเลือกห้องเรียน ให้แสดงคาบที่เลือกได้
  classRoomSelectArea.addEventListener('change', function(e) {
    if (e.target.classList.contains('report-class-room-checkbox')) {
      const subjectId = subjectSelect.value;
      const reportDate = reportDateInput.value;
      const checkedRooms = Array.from(classRoomSelectArea.querySelectorAll('.report-class-room-checkbox:checked')).map(cb => cb.value);
      renderClassPeriodsInputs(subjectId, checkedRooms, reportDate);
      renderRoomImageInputs(checkedRooms);

      // ดึง class (level) จาก option ที่เลือกใน subjectSelect
      const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
      const classValue = selectedOption.getAttribute('data-class') || '';
      const classRoomArr = checkedRooms.map(room => ({
        class: classValue,
        room: room.replace('ห้อง ', '')
      }));
      loadStudentsForAttendance(subjectId, classRoomArr);
    }
  });

  // สถานะการเข้าเรียน (ย่อ)
  const attendanceStatus = [
    { value: 'present', label: 'มา', color: 'bg-green-500', emoji: '✅' },
    { value: 'absent', label: 'ขาด', color: 'bg-red-500', emoji: '❌' },
    { value: 'late', label: 'สาย', color: 'bg-yellow-400', emoji: '⏰' },
    { value: 'sick', label: 'ป่วย', color: 'bg-blue-400', emoji: '🤒' },
    { value: 'personal', label: 'ลากิจ', color: 'bg-indigo-400', emoji: '📝' },
    { value: 'activity', label: 'กิจกรรม', color: 'bg-purple-400', emoji: '🎉' }
    ];


  // เรียกข้อมูลนักเรียนเมื่อเลือกห้องและคาบ
  function loadStudentsForAttendance(subjectId, selectedRooms) {
    const area = document.getElementById('studentAttendanceArea');
    area.innerHTML = '';
    if (!subjectId || !selectedRooms.length) {
        area.innerHTML = '<div class="text-gray-400 text-sm">เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน</div>';
        return;
    }

    const classRoomData = selectedRooms.map(r => ({ class: r.class, room: r.room }));
    fetch('../controllers/StudentController.php?action=list&subject_id=' + encodeURIComponent(subjectId) +
        '&rooms=' + encodeURIComponent(JSON.stringify(selectedRooms)))
        .then(res => res.json())
        .then(data => {
        if (!data.length) {
            area.innerHTML = '<div class="text-red-500">ไม่พบนักเรียนในห้องที่เลือก</div>';
            return;
        }

        // แยกนักเรียนตามห้อง
        const groupByRoom = {};
        data.forEach(stu => {
            if (!groupByRoom[stu.Stu_room]) groupByRoom[stu.Stu_room] = [];
            groupByRoom[stu.Stu_room].push(stu);
        });

        // สร้าง HTML ตารางแสดงนักเรียน
        let html = '';
        Object.keys(groupByRoom).forEach(room => {
            html += `<div class="mb-6 border p-4 rounded-xl bg-white shadow">
            <div class="font-bold text-blue-700 mb-3 text-lg">🏫 ห้อง ${room}</div>
            <table class="w-full text-sm table-auto border-collapse">
                <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2 border">เลขที่</th>
                    <th class="p-2 border">ชื่อ - สกุล</th>
                    <th class="p-2 border">สถานะเข้าเรียน</th>
                </tr>
                </thead>
                <tbody>`;

            groupByRoom[room].forEach((student, idx) => {
            html += `
                <tr class="border-b">
                <td class="p-2 border text-center">${idx + 1}</td>
                <td class="p-2 border">${student.Stu_id} ${student.fullname}</td>
                <td class="p-2 border">
                    <div class="flex flex-wrap gap-1">`;

            html += attendanceStatus.map(st =>
                `<button type="button"
                class="attendance-btn px-3 py-1 rounded ${st.color} text-white font-semibold flex items-center gap-1 opacity-80 hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:ring-${st.color.replace('bg-', '')}"
                data-stu="${student.Stu_id}" data-status="${st.value}" data-room="${room}" title="${st.label}">
                <span>${st.emoji}</span> <span>${st.label}</span>
                </button>`
            ).join('');

            // เปลี่ยน name ให้มีห้องด้วย
            html += `<input type="hidden" name="attendance[${room}][${student.Stu_id}]" value="present">
                    </div>
                </td>
                </tr>`;
            });

            html += `</tbody></table></div>`;
        });

        area.innerHTML = html;

        // ตั้งค่าเริ่มต้นและจัดการคลิกปุ่มสถานะ
        area.querySelectorAll('.attendance-btn').forEach(btn => {
            const stuId = btn.getAttribute('data-stu');
            const status = btn.getAttribute('data-status');
            const room = btn.getAttribute('data-room');

            if (status === 'present') {
            btn.classList.add('ring-2', 'ring-green-600', 'opacity-100');
            }

            btn.addEventListener('click', function () {
            const parent = btn.parentNode;
            parent.querySelectorAll('.attendance-btn').forEach(b => {
                b.classList.remove(
                'ring-2',
                'ring-green-700',
                'ring-red-700',
                'ring-yellow-600',
                'ring-blue-700',
                'ring-indigo-700',
                'ring-purple-700',
                'opacity-100'
                );
            });

            let ringColor = '';
            if (status === 'present') ringColor = 'ring-green-600';
            else if (status === 'absent') ringColor = 'ring-red-600';
            else if (status === 'late') ringColor = 'ring-yellow-500';
            else if (status === 'sick') ringColor = 'ring-blue-600';
            else if (status === 'personal') ringColor = 'ring-indigo-600';
            else if (status === 'activity') ringColor = 'ring-purple-600';

            btn.classList.add('ring-2', ringColor, 'opacity-100');
            parent.querySelector(`input[name="attendance[${room}][${stuId}]"]`).value = status;
            });
        });
        });
    }
  



  function showReportDetail(reportId) {
    fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
      .then(res => res.json())
      .then(report => {
        let html = `<div class="text-lg font-bold mb-2">รายละเอียดรายงานการสอน</div>
        <div class="mb-2 text-left"><span class="font-semibold">📅 วันที่:</span> ${formatThaiDate(report.report_date)}</div>
        <div class="mb-2 text-left"><span class="font-semibold">📖 วิชา:</span> ${report.subject_name || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">🏫 ห้อง:</span> ม.${report.level}/${report.class_room}</div>
        <div class="mb-2 text-left"><span class="font-semibold">⏰ คาบ:</span> ${report.period_start} - ${report.period_end}</div>
        <div class="mb-2 text-left"><span class="font-semibold">📝 แผน/หัวข้อ:</span> ${report.plan_topic || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">👨‍🏫 กิจกรรม:</span> ${report.activity || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">🙋‍♂️ ขาดเรียน:</span> ${report.absent_students || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">💡 K:</span> ${report.reflection_k || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">💡 P:</span> ${report.reflection_p || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">💡 A:</span> ${report.reflection_a || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">❗ ปัญหา:</span> ${report.problems || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">📝 ข้อเสนอแนะ:</span> ${report.suggestions || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">🖼️ รูปภาพ 1:</span> ${report.image1 ? `<img src="../${report.image1}" class="inline-block max-h-32 rounded border" />` : '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">🖼️ รูปภาพ 2:</span> ${report.image2 ? `<img src="../${report.image2}" class="inline-block max-h-32 rounded border" />` : '-'}</div>
        `;
        Swal.fire({
          html: html,
          width: 700,
          showCloseButton: true,
          showConfirmButton: false
        });
      });
  }

  // Modal logic
  const modalReport = document.getElementById('modalAddReport');
  const btnAddReport = document.getElementById('btnAddReport');
  const btnCloseReport = document.getElementById('closeModalAddReport');
  const btnCancelReport = document.getElementById('cancelAddReport');
  const formReport = document.getElementById('formAddReport');

  // เพิ่มตัวแปร modal mode
  let editMode = false;
  let editReportId = null;
  let lastFormData = null; // เก็บค่าฟอร์มล่าสุด

  btnAddReport.addEventListener('click', () => {
    editMode = false;
    editReportId = null;
    document.getElementById('modalReportTitle').innerHTML = '➕ เพิ่มรายงานการสอน';
    modalReport.classList.remove('hidden');
    // ถ้ามี lastFormData ให้เติมค่ากลับ
    if (lastFormData) {
      for (const [key, value] of Object.entries(lastFormData)) {
        if (formReport.elements[key]) formReport.elements[key].value = value;
      }
    }
  });

  btnCloseReport.addEventListener('click', () => {
    modalReport.classList.add('hidden');
    formReport.reset();
    lastFormData = null;
  });
  btnCancelReport.addEventListener('click', () => {
    modalReport.classList.add('hidden');
    formReport.reset();
    lastFormData = null;
  });

  // ปิด modal เฉพาะปุ่ม ไม่ปิดเมื่อคลิกพื้นหลัง
  // modalReport.addEventListener('click', (e) => {
  //   if (e.target === modalReport) {
  //     modalReport.classList.add('hidden');
  //     formReport.reset();
  //   }
  // });

  // เก็บค่าฟอร์มล่าสุดทุกครั้งที่เปลี่ยน
  formReport.addEventListener('input', function() {
    lastFormData = {};
    Array.from(formReport.elements).forEach(el => {
      if (el.name) lastFormData[el.name] = el.value;
    });
  });

  formReport.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(formReport);

  // === แสดงสถานะ Loading ===
  Swal.fire({
    title: 'กำลังบันทึกข้อมูล...',
    text: 'กรุณารอสักครู่',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  const subjectId = formData.get('subject_id');
  const reportDate = formData.get('report_date');
  const checkedRooms = Array.from(document.querySelectorAll('.report-class-room-checkbox:checked')).map(cb => cb.value);

  const checkedPeriods = {};
  checkedRooms.forEach(room => {
    checkedPeriods[room] = Array.from(document.querySelectorAll(`input[name="periods[${room}][]"]:checked`)).map(cb => {
      const [start, end, day] = cb.value.split('|');
      return { period_start: start, period_end: end, day_of_week: day };
    });
  });

  const attendance = {};
  document.querySelectorAll('input[name^="attendance["]').forEach(input => {
    const stuId = input.name.match(/attendance\[(.+)\]/)[1];
    attendance[stuId] = input.value;
  });

  let rows = [];
  checkedRooms.forEach(room => {
    (checkedPeriods[room] || []).forEach(period => {
      const classRoom = (room.replace('ห้อง ', '') + '').trim();
      rows.push({
        report_date: reportDate,
        subject_id: subjectId,
        class_room: classRoom,
        period_start: period.period_start.trim(),
        period_end: period.period_end.trim(),
        plan_number: formData.get('plan_number'),
        plan_topic: formData.get('plan_topic'),
        activity: formData.get('activity'),
        absent_students: '',
        reflection_k: formData.get('reflection_k'),
        reflection_p: formData.get('reflection_p'),
        reflection_a: formData.get('reflection_a'),
        problems: formData.get('problems'),
        suggestions: formData.get('suggestions'),
        image1: null,
        image2: null,
        teacher_id: <?php echo json_encode($_SESSION['username']); ?>,
        created_at: null
      });
    });
  });

  // ดึงข้อมูลการเช็คชื่อแบบแยกห้อง
  let attendanceLogs = [];
  document.querySelectorAll('input[name^="attendance["]').forEach(input => {
    // name="attendance[room][stuId]"
    const match = input.name.match(/^attendance\[(.+?)\]\[(.+?)\]$/);
    if (!match) return;
    const room = match[1];
    const stuId = match[2];
    let status = input.value;
    const map = {
      present: 'มาเรียน',
      late: 'มาสาย',
      sick: 'ลาป่วย',
      personal: 'ลากิจ',
      activity: 'เข้าร่วมกิจกรรม',
      absent: 'ขาดเรียน'
    };
    attendanceLogs.push({ student_id: stuId, status: map[status] || status, class_room: room });
  });

  const uploadImages = () => {
    return new Promise((resolve, reject) => {
      const imagesByRoom = {};
      checkedRooms.forEach(room => {
        imagesByRoom[room] = {
          image1: formData.get(`image1_${room}`),
          image2: formData.get(`image2_${room}`)
        };
      });

      const uploadPromises = checkedRooms.map(room => {
        const files = imagesByRoom[room];
        const isValid = file => file && file instanceof File && file.size > 0;
        if (!isValid(files.image1) && !isValid(files.image2)) {
          return Promise.resolve({ room, image1: '', image2: '' });
        }
        const uploadData = new FormData();
        if (isValid(files.image1)) uploadData.append('image1', files.image1);
        if (isValid(files.image2)) uploadData.append('image2', files.image2);

        return fetch('../controllers/TeachingReportController.php?action=upload_images', {
          method: 'POST',
          body: uploadData
        })
        .then(res => res.json())
        .then(result => ({
          room,
          image1: result.image1 ? 'uploads/' + result.image1 : '',
          image2: result.image2 ? 'uploads/' + result.image2 : ''
        }));
      });

      Promise.all(uploadPromises)
        .then(results => {
          const imagesMap = {};
          results.forEach(r => imagesMap[r.room] = { image1: r.image1, image2: r.image2 });
          resolve(imagesMap);
        })
        .catch(reject);
    });
  };

  uploadImages().then(imagesMap => {
    let url = '../controllers/TeachingReportController.php?action=create';
    let method = 'POST';
    let body = {
      rows: rows.map(row => {
        let roomKey = row.class_room;
        if (!imagesMap[roomKey] && imagesMap['ห้อง ' + roomKey]) {
          roomKey = 'ห้อง ' + roomKey;
        }
        return {
          ...row,
          image1: imagesMap[roomKey]?.image1 || null,
          image2: imagesMap[roomKey]?.image2 || null
        };
      }),
      attendance_logs: attendanceLogs
    };
    if (editMode && editReportId) {
      url = '../controllers/TeachingReportController.php?action=update';
      body.id = editReportId;
    }

    fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    })
    .then(res => res.json())
    .then(result => {
      // หลังบันทึกเสร็จ
      Swal.close(); // ✅ ปิด loading
      if (result.success) {
        Swal.fire('สำเร็จ', editMode ? 'แก้ไขรายงานเรียบร้อยแล้ว' : 'บันทึกรายงานเรียบร้อยแล้ว', 'success');
        modalReport.classList.add('hidden');
        formReport.reset();
        lastFormData = null;
        loadReports();
      } else {
        Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกรายงานได้', 'error');
      }
      editMode = false;
      editReportId = null;
    })
    .catch(() => {
      Swal.close(); // ✅ ปิด loading
      Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    });
  });
});

});
</script>
<?php require_once('script.php');?>
</body>
</html>
