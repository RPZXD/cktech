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
    if (isNaN(d)) return dateStr;
    const day = d.getDate();
    const month = months[d.getMonth() + 1];
    const year = d.getFullYear() + 543;
    return `${day} ${month} ${year}`;
  }

  function renderDetailBtn(reportId) {
    return `<button class="my-1 bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded btn-report-detail flex items-center gap-1" data-id="${reportId}">
      👁️ ดู
    </button>
    <button class="my-1 bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded btn-edit-report flex items-center gap-1" data-id="${reportId}">
      ✏️ แก้ไข
    </button>
    <button class="my-1 bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded btn-delete-report flex items-center gap-1" data-id="${reportId}">
      🗑️ ลบ
    </button>
    <button class="my-1 bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded btn-print-report flex items-center gap-1" data-id="${reportId}">
      🖨️ พิมพ์
    </button>`;
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
        data.forEach(report => {
          tbody.innerHTML += `
            <tr class="hover:bg-blue-50">
              <td class="py-2 px-3 border-b text-center">${formatThaiDate(report.report_date)}</td>
              <td class="py-2 px-3 border-b text-center">${report.subject_name || '-'}</td>
              <td class="py-2 px-3 border-b text-center">${report.class_room}</td>
              <td class="py-2 px-3 border-b text-center">${report.period_start} - ${report.period_end}</td>
              <td class="py-2 px-3 border-b text-center">${report.plan_topic ? report.plan_topic.substring(0, 20) + '...' : '-'}</td>
              <td class="py-2 px-3 border-b text-center">${report.activity ? report.activity.substring(0, 20) + '...' : '-'}</td>
              <td class="py-2 px-3 border-b text-left text-sm">
                ${report.absent_students ? `
                  <div>
                    <div class="font-semibold text-red-600 ">❌ ขาดเรียน</div>
                    <div class="text-gray-700">${report.absent_students.replace(/,\s*/g, '<br>')}</div>
                  </div>` : ''}

                ${report.sick_students ? `
                  <div>
                    <div class="font-semibold text-blue-500 ">🤒 ป่วย</div>
                    <div class="text-gray-700">${report.sick_students.replace(/,\s*/g, '<br>')}</div>
                  </div>` : ''}

                ${report.personal_students ? `
                  <div>
                    <div class="font-semibold text-indigo-500 ">📝 ลากิจ</div>
                    <div class="text-gray-700">${report.personal_students.replace(/,\s*/g, '<br>')}</div>
                  </div>` : ''}

                ${report.activity_students ? `
                  <div>
                    <div class="font-semibold text-purple-500 ">🎉 กิจกรรม</div>
                    <div class="text-gray-700">${report.activity_students.replace(/,\s*/g, '<br>')}</div>
                  </div>` : ''}
              </td>
              <td class="py-2 px-3 border-b text-center">${renderDetailBtn(report.id)}</td>
            </tr>
          `;
        });

        // DataTables: apply เฉพาะครั้งแรก
        if (!$.fn.DataTable.isDataTable('.min-w-full')) {
          $('.min-w-full').DataTable({
            language: {
              url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
            },
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            pagingType: 'simple',
            searching: true,
            info: true,
            autoWidth: false,
            columnDefs: [
              { targets: 0, width: '10%' },
              { targets: 1, width: '20%' },
              { targets: 2, width: '5%' },
              { targets: 3, width: '5%' },
              { targets: 4, width: '15%' },
              { targets: 5, width: '15%' },
              { targets: 6, width: '25%' },
              { targets: 7, width: '10%' }
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
            window.open('../print/teaching_report.php?id=' + encodeURIComponent(reportId), '_blank');
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
                  classRoomSelectArea.dispatchEvent(new Event('change'));
                  setTimeout(() => {
                    // คาบ
                    document.querySelectorAll(`input[name="periods[${report.class_room}][]"]`).forEach(cb => {
                      if (cb.value.startsWith(report.period_start + '|')) cb.checked = true;
                    });
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
              });
          });
        });
      });
  }

  // Helper: แปลงวันที่ (YYYY-MM-DD) เป็นชื่อวันภาษาไทย
  function getThaiDayOfWeek(dateStr) {
    const days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
    const d = new Date(dateStr);
    return days[d.getDay()];
  }

  // ห้องเรียนและคาบตามวิชา
  const subjectSelect = document.getElementById('subjectSelect');
  const reportDateInput = document.getElementById('reportDate');
  const classRoomSelectArea = document.getElementById('classRoomSelectArea');
  let subjectClassRooms = {}; // {subject_id: [{class_room, period_start, period_end, day_of_week}, ...]}

  function loadSubjectsForReport() {
    fetch('../controllers/SubjectController.php?action=list&teacherId=' + encodeURIComponent(<?php echo json_encode($_SESSION['username']); ?>))
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
                data-stu="${student.Stu_id}" data-status="${st.value}" title="${st.label}">
                <span>${st.emoji}</span> <span>${st.label}</span>
                </button>`
            ).join('');

            html += `<input type="hidden" name="attendance[${student.Stu_id}]" value="present">
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
            parent.querySelector(`input[name="attendance[${stuId}]"]`).value = status;
            });
        });
        });
    }



  function showReportDetail(reportId) {
    fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
      .then(res => res.json())
      .then(report => {
        let html = `<div class="text-lg font-bold mb-2">รายละเอียดรายงานการสอน</div>
        <div class="mb-2 text-left"><span class="font-semibold">📅 วันที่:</span> ${report.report_date}</div>
        <div class="mb-2 text-left"><span class="font-semibold">📖 วิชา:</span> ${report.subject_name || '-'}</div>
        <div class="mb-2 text-left"><span class="font-semibold">🏫 ห้อง:</span> ${report.class_room}</div>
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

  btnAddReport.addEventListener('click', () => {
    editMode = false;
    editReportId = null;
    document.getElementById('modalReportTitle').innerHTML = '➕ เพิ่มรายงานการสอน';
    modalReport.classList.remove('hidden');
    formReport.reset();
  });

  btnCloseReport.addEventListener('click', () => {
    modalReport.classList.add('hidden');
    formReport.reset();
  });
  btnCancelReport.addEventListener('click', () => {
    modalReport.classList.add('hidden');
    formReport.reset();
  });

  modalReport.addEventListener('click', (e) => {
    if (e.target === modalReport) {
      modalReport.classList.add('hidden');
      formReport.reset();
    }
  });

  // ====== เพิ่ม event สำหรับ submit form เพื่อบันทึกข้อมูล ======
  formReport.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(formReport);

    console.log('Form Data:', Object.fromEntries(formData.entries()));

    // 1. เก็บข้อมูลห้อง/คาบที่เลือก
    const subjectId = formData.get('subject_id');
    const reportDate = formData.get('report_date');
    const checkedRooms = Array.from(document.querySelectorAll('.report-class-room-checkbox:checked')).map(cb => cb.value);

    // 2. เก็บข้อมูลคาบที่เลือกในแต่ละห้อง
    const checkedPeriods = {};
    checkedRooms.forEach(room => {
      checkedPeriods[room] = Array.from(document.querySelectorAll(`input[name="periods[${room}][]"]:checked`)).map(cb => {
        const [start, end, day] = cb.value.split('|');
        return { period_start: start, period_end: end, day_of_week: day };
      });
    });

    // 3. เก็บข้อมูลเช็คชื่อ
    const attendance = {};
    document.querySelectorAll('input[name^="attendance["]').forEach(input => {
      const stuId = input.name.match(/attendance\[(.+)\]/)[1];
      attendance[stuId] = input.value;
    });

    // 4. เตรียมข้อมูลแยกตามห้อง/คาบ (1 ห้อง 1 row)
    let rows = [];
    checkedRooms.forEach(room => {
      (checkedPeriods[room] || []).forEach(period => {
        rows.push({
          report_date: reportDate,
          subject_id: subjectId,
          class_room: room.replace('ห้อง ', ''),
          period_start: period.period_start,
          period_end: period.period_end,
          plan_number: formData.get('plan_number'),
          plan_topic: formData.get('plan_topic'),
          activity: formData.get('activity'),
          absent_students: '', // ไม่ใช้แล้ว
          reflection_k: formData.get('reflection_k'),
          reflection_p: formData.get('reflection_p'),
          reflection_a: formData.get('reflection_a'),
          problems: formData.get('problems'),
          suggestions: formData.get('suggestions'),
          image1: null, // รูปภาพจะจัดการแยก
          image2: null,
          teacher_id: <?php echo json_encode($_SESSION['username']); ?>,
          created_at: null // จะถูกเซ็ตอัตโนมัติในฐานข้อมูล
        });
      });
    });

    // 5. เตรียมข้อมูลเช็คชื่อ (attendance_logs)
    let attendanceLogs = [];
    Object.keys(attendance).forEach(stuId => {
      let status = attendance[stuId];
      if (status === 'present') status = 'มาเรียน';
      else if (status === 'late') status = 'มาสาย';
      else if (status === 'sick') status = 'ลาป่วย';
      else if (status === 'personal') status = 'ลากิจ';
      else if (status === 'activity') status = 'เข้าร่วมกิจกรรม';
      else if (status === 'absent') status = 'ขาดเรียน';
      attendanceLogs.push({ student_id: stuId, status });
    });

    // 6. อัปโหลดรูปภาพ (ถ้ามี) - อัปโหลดแยกตามห้อง
    const uploadImages = () => {
      return new Promise((resolve, reject) => {
        // เก็บไฟล์รูปภาพแยกห้อง
        const imagesByRoom = {};
        checkedRooms.forEach(room => {
          const img1 = formData.get(`image1_${room}`); // อาจเป็น null
          const img2 = formData.get(`image2_${room}`);
          imagesByRoom[room] = { image1: img1, image2: img2 };
        });

        // อัปโหลดทีละห้อง (Promise.all)
        const uploadPromises = checkedRooms.map(room => {
          const files = imagesByRoom[room];
          const isValidFile = file => file && file instanceof File && file.size > 0;
          if (!isValidFile(files.image1) && !isValidFile(files.image2)) {
            return Promise.resolve({ room, image1: '', image2: '' });
          }
          const uploadData = new FormData();
          if (isValidFile(files.image1)) uploadData.append('image1', files.image1);
          if (isValidFile(files.image2)) uploadData.append('image2', files.image2);
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

        Promise.all(uploadPromises).then(results => {
          // คืนค่าเป็น {room: {image1, image2}, ...}
          const imagesMap = {};
          results.forEach(r => {
            imagesMap[r.room] = { image1: r.image1, image2: r.image2 };
          });
          resolve(imagesMap);
        }).catch(reject);
      });
    };

    uploadImages().then(imagesMap => {
      // 7. ส่งข้อมูลไป controller
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
        if (result.success) {
          Swal.fire('สำเร็จ', editMode ? 'แก้ไขรายงานเรียบร้อยแล้ว' : 'บันทึกรายงานการสอนเรียบร้อยแล้ว', 'success');
          modalReport.classList.add('hidden');
          formReport.reset();
          loadReports();
        } else {
          Swal.fire('ผิดพลาด', editMode ? 'ไม่สามารถแก้ไขรายงานได้' : 'ไม่สามารถบันทึกรายงานได้', 'error');
        }
        editMode = false;
        editReportId = null;
      })
      .catch(() => {
        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
      });
    });
  });
});
</script>
<?php require_once('script.php');?>
</body>
</html>
