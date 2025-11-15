<?php 
session_start();
// เช็ค session และ role
// Debug helper: append ?debug=1 to view session values (remove when done)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
  echo '<pre>SESSION='; var_export($_SESSION); echo '</pre>';
  exit;
}
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
<style>
  .aurora-wrapper {
    position: relative;
    isolation: isolate;
  }
  .aurora-wrapper::before,
  .aurora-wrapper::after {
    content: '';
    position: absolute;
    inset: -60px;
    border-radius: 999px;
    opacity: 0.35;
    filter: blur(80px);
    z-index: -1;
  }
  .aurora-wrapper::before {
    background: linear-gradient(135deg, rgba(14,165,233,0.55), rgba(59,130,246,0.45));
    animation: floaty 18s ease-in-out infinite;
  }
  .aurora-wrapper::after {
    background: linear-gradient(135deg, rgba(236,72,153,0.5), rgba(249,115,22,0.45));
    animation: floaty 22s ease-in-out infinite reverse;
  }
  @keyframes floaty {
    0% { transform: translate(-15px, -10px) scale(1); }
    50% { transform: translate(20px, 15px) scale(1.06); }
    100% { transform: translate(-15px, -10px) scale(1); }
  }
  .glow-card {
    box-shadow: 0 10px 40px rgba(15, 23, 42, 0.15);
    transition: transform 220ms ease, box-shadow 220ms ease;
  }
  .glow-card:hover {
    transform: translateY(-6px) scale(1.01);
    box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
  }
  .stat-sheen {
    position: relative;
    overflow: hidden;
  }
  .stat-sheen::after {
    content: '';
    position: absolute;
    inset: -100% auto;
    width: 60px;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.6), transparent);
    transform: rotate(25deg);
    animation: sheen 4.5s linear infinite;
  }
  @keyframes sheen {
    0% { left: -100%; }
    100% { left: 140%; }
  }
</style>
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Content Header (Page header) -->

  <div class="content-header p-6 rounded-lg shadow-lg mb-6">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-3xl font-bold bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 bg-clip-text text-transparent flex items-center gap-3 animate-pulse">
              📑 <span class="drop-shadow-lg"></span>
            </h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <section class="content">
      <div class="container-fluid">
        <div class="w-full max-w-6xl mx-auto">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
              <div>
                <label class="block mb-2 font-semibold text-gray-700">เลือกวิชา</label>
                <select id="subjectSelect" class="w-full border rounded-lg p-2">
                  <option value="">-- เลือกวิชา --</option>
                </select>
              </div>
              <div>
                <label class="block mb-2 font-semibold text-gray-700">วันที่</label>
                <input type="date" id="attendanceDate" class="w-full border rounded-lg p-2" />
              </div>
              <div class="flex gap-2">
                <button id="btnLoadRooms" class="mt-6 px-4 py-2 bg-blue-600 text-white rounded-lg">โหลดห้อง</button>
                <button id="btnSaveAttendance" class="mt-6 px-4 py-2 bg-green-600 text-white rounded-lg">บันทึก</button>
                <button id="btnClear" class="mt-6 px-4 py-2 bg-gray-200 rounded-lg">ล้าง</button>
              </div>
            </div>

            <div id="classRoomSelectArea" class="mb-6"></div>

            <div id="studentAttendanceArea"></div>
          </div>
        </div>
      </div>

      <!-- End Attendance Modal -->
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
<!-- Tailwind Browser: runtime utility for using Tailwind classes client-side -->
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script>
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const subjectSelect = document.getElementById('subjectSelect');
  const btnLoadRooms = document.getElementById('btnLoadRooms');
  const btnClear = document.getElementById('btnClear');
  const classRoomSelectArea = document.getElementById('classRoomSelectArea');
  const studentAttendanceArea = document.getElementById('studentAttendanceArea');
  const attendanceDateInput = document.getElementById('attendanceDate');

  // สถานะการเข้าเรียน (ย่อ) - นำมาจาก report.php
  const attendanceStatus = [
    { value: 'present', label: 'มา', color: 'bg-green-500', emoji: '✅' },
    { value: 'absent', label: 'ขาด', color: 'bg-red-500', emoji: '❌' },
    { value: 'late', label: 'สาย', color: 'bg-yellow-400', emoji: '⏰' },
    { value: 'sick', label: 'ป่วย', color: 'bg-blue-400', emoji: '🤒' },
    { value: 'personal', label: 'ลากิจ', color: 'bg-indigo-400', emoji: '📝' },
    { value: 'activity', label: 'กิจกรรม', color: 'bg-purple-400', emoji: '🎉' },
    { value: 'truant', label: 'โดดเรียน', color: 'bg-gray-600', emoji: '🚫' }
  ];

  const attendanceStyleConfig = {
    present: { select: ['bg-emerald-50','text-emerald-700','border-emerald-200'], pill: ['bg-emerald-100','text-emerald-700'], label: '✅ มา' },
    absent: { select: ['bg-rose-50','text-rose-600','border-rose-200'], pill: ['bg-rose-100','text-rose-600'], label: '❌ ขาด' },
    late:   { select: ['bg-amber-50','text-amber-600','border-amber-200'], pill: ['bg-amber-100','text-amber-600'], label: '⏰ สาย' },
    sick:   { select: ['bg-sky-50','text-sky-600','border-sky-200'], pill: ['bg-sky-100','text-sky-600'], label: '🤒 ลาป่วย' },
    personal:{ select: ['bg-indigo-50','text-indigo-600','border-indigo-200'], pill: ['bg-indigo-100','text-indigo-600'], label: '📝 ลากิจ' },
    activity:{ select: ['bg-purple-50','text-purple-600','border-purple-200'], pill: ['bg-purple-100','text-purple-600'], label: '🎉 กิจกรรม' },
    truant: { select: ['bg-gray-50','text-gray-800','border-gray-200'], pill: ['bg-gray-100','text-gray-800'], label: '🚫 โดดเรียน' }
  };

  // โหลดวิชาของครู
  function loadSubjectsForAttendance() {
    fetch('../controllers/SubjectController.php?action=list&teacherId=' + encodeURIComponent(<?php echo json_encode($_SESSION['user']['Teach_id']); ?>) + '&onlyOpen=1')
      .then(res => res.json())
      .then(data => {
        subjectSelect.innerHTML = '<option value="">-- เลือกวิชา --</option>';
        data.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.id || s.Subject_id || '';
          opt.textContent = s.Subject_name || s.name || s.subject_name || s.Subject || s.title || opt.value;
          subjectSelect.appendChild(opt);
        });
      })
      .catch(err => console.warn('ไม่สามารถโหลดวิชาได้', err));
  }

  btnLoadRooms.addEventListener('click', () => {
    const subjectId = subjectSelect.value;
    if (!subjectId) {
      Swal.fire('ผิดพลาด', 'กรุณาเลือกวิชาก่อน', 'error');
      return;
    }
    // ดึงนักเรียนทั้งหมดสำหรับวิชานี้ เพื่อหา list ห้อง
    fetch('../controllers/StudentController.php?action=list&subject_id=' + encodeURIComponent(subjectId))
      .then(res => res.json())
      .then(data => {
        if (!Array.isArray(data) || data.length === 0) {
          classRoomSelectArea.innerHTML = '<div class="text-red-500">ไม่พบนักเรียนสำหรับวิชานี้</div>';
          return;
        }
        const rooms = Array.from(new Set(data.map(s => s.Stu_room).filter(Boolean)));
        if (!rooms.length) {
          classRoomSelectArea.innerHTML = '<div class="text-red-500">ไม่พบข้อมูลห้อง</div>';
          return;
        }
        let html = '<label class="block mb-2 font-semibold">เลือกห้อง</label><div class="flex flex-wrap gap-2">';
        rooms.forEach(r => {
          const id = 'room_' + r.replace(/\s+/g, '_');
          html += `<label class="flex items-center gap-2 p-2 border rounded-md cursor-pointer"><input type="checkbox" class="report-class-room-checkbox" value="${r}" id="${id}"> <span>${r}</span></label>`;
        });
        html += '</div>';
        classRoomSelectArea.innerHTML = html;

        // add change listener
        classRoomSelectArea.querySelectorAll('.report-class-room-checkbox').forEach(cb => cb.addEventListener('change', () => {
          const checkedRooms = Array.from(classRoomSelectArea.querySelectorAll('.report-class-room-checkbox:checked')).map(x => x.value);
          loadStudentsForAttendance(subjectId, checkedRooms, attendanceDateInput.value);
        }));
      })
      .catch(err => {
        console.warn(err);
        classRoomSelectArea.innerHTML = '<div class="text-red-500">เกิดข้อผิดพลาดในการดึงข้อมูล</div>';
      });
  });

  btnClear.addEventListener('click', () => {
    subjectSelect.value = '';
    attendanceDateInput.value = '';
    classRoomSelectArea.innerHTML = '';
    studentAttendanceArea.innerHTML = '';
  });

  function applyStyleToRow(selectEl) {
    const room = selectEl.dataset.room;
    const pill = selectEl.parentElement.querySelector('.attendance-pill');
    const cfg = attendanceStyleConfig[selectEl.value] || attendanceStyleConfig.present;
    // reset classes
    if (pill) {
      pill.textContent = cfg.label || '';
      pill.className = 'attendance-pill inline-block px-2 py-1 text-xs rounded-full ' + cfg.pill.join(' ');
    }
    selectEl.className = 'attendance-select border rounded px-2 py-1 ' + (cfg.select || []).join(' ');
  }

  // ดึงนักเรียนตามห้องที่เลือก และ render ตารางสถานะ
  function loadStudentsForAttendance(subjectId, selectedRooms, date) {
    studentAttendanceArea.innerHTML = '';
    if (!subjectId || !selectedRooms || selectedRooms.length === 0) {
      studentAttendanceArea.innerHTML = '<div class="text-gray-500">เลือกห้องเพื่อแสดงรายชื่อนักเรียน</div>';
      return;
    }

    // show loading
    studentAttendanceArea.innerHTML = '<div class="text-gray-500">กำลังโหลดข้อมูล...</div>';

    // If no date provided, skip attendance endpoint and fetch students directly
    if (!date) {
      fetch('../controllers/StudentController.php?action=list&subject_id=' + encodeURIComponent(subjectId) + '&rooms=' + encodeURIComponent(JSON.stringify(selectedRooms)))
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (!Array.isArray(data) || data.length === 0) {
            studentAttendanceArea.innerHTML = '<div class="text-red-500">ไม่พบนักเรียนในห้องที่เลือก</div>';
            return;
          }
          const groupByRoom = {};
          data.forEach(stu => {
            if (!groupByRoom[stu.Stu_room]) groupByRoom[stu.Stu_room] = [];
            groupByRoom[stu.Stu_room].push(stu);
          });
          const studentsByRoom = {};
          Object.keys(groupByRoom).forEach(room => {
            studentsByRoom[room] = groupByRoom[room].map(stu => ({
              student_id: stu.Stu_id || stu.id || null,
              student_no: stu.Stu_no || null,
              student_name: stu.Stu_name || stu.name || stu.Stu_fullname || '',
              status: null,
              class_room: room
            }));
          });
          renderFromAttendancePayload(studentsByRoom);
        })
        .catch(err => {
          console.warn('student fallback error', err);
          studentAttendanceArea.innerHTML = '<div class="text-red-500">เกิดข้อผิดพลาดในการดึงข้อมูลนักเรียน</div>';
        });
      return;
    }

    // Try attendance endpoint first (returns grouped students if available)
    const teacherId = <?php echo json_encode($_SESSION['user']['Teach_id']); ?>;
    const url = '../controllers/AttendanceController.php?action=attendance_by_date&subject_id=' + encodeURIComponent(subjectId) + '&date=' + encodeURIComponent(date) + '&rooms=' + encodeURIComponent(JSON.stringify(selectedRooms)) + '&teacher_id=' + encodeURIComponent(teacherId);

    fetch(url)
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(res => {
        if (res && res.success && res.data && res.data.studentsByRoom && Object.keys(res.data.studentsByRoom).length > 0) {
          renderFromAttendancePayload(res.data.studentsByRoom);
          return null; // signal that we've rendered
        }
        // fallback to StudentController if attendance endpoint has no student list
        return fetch('../controllers/StudentController.php?action=list&subject_id=' + encodeURIComponent(subjectId) + '&rooms=' + encodeURIComponent(JSON.stringify(selectedRooms)));
      })
      .then(fallbackRes => {
        if (!fallbackRes) return; // already rendered from attendance payload
        return fallbackRes.ok ? fallbackRes.json() : Promise.reject();
      })
      .then(data => {
        if (!data) return;
        if (!Array.isArray(data) || data.length === 0) {
          studentAttendanceArea.innerHTML = '<div class="text-red-500">ไม่พบนักเรียนในห้องที่เลือก</div>';
          return;
        }
        // group by Stu_room
        const groupByRoom = {};
        data.forEach(stu => {
          if (!groupByRoom[stu.Stu_room]) groupByRoom[stu.Stu_room] = [];
          groupByRoom[stu.Stu_room].push(stu);
        });

        // convert to the same structure used by renderFromAttendancePayload
        const studentsByRoom = {};
        Object.keys(groupByRoom).forEach(room => {
          studentsByRoom[room] = groupByRoom[room].map(stu => ({
            student_id: stu.Stu_id || stu.id || null,
            student_no: stu.Stu_no || null,
            student_name: stu.Stu_name || stu.name || stu.Stu_fullname || '',
            status: null,
            class_room: room
          }));
        });
        renderFromAttendancePayload(studentsByRoom);
      })
      .catch(err => {
        console.warn('attendance load error', err);
        studentAttendanceArea.innerHTML = '<div class="text-red-500">เกิดข้อผิดพลาดในการดึงข้อมูลนักเรียน</div>';
      });
  }

  function renderFromAttendancePayload(studentsByRoom) {
    let html = '';
    Object.keys(studentsByRoom).forEach(room => {
      const list = studentsByRoom[room] || [];
      html += `<div class="mb-6 glow-card border p-4 rounded-lg bg-gray-50"><div class="font-bold mb-2">🏫 ห้อง ${room} <span class="text-sm text-gray-500">(${list.length} คน)</span></div>`;
      html += '<table class="w-full text-sm table-auto border-collapse"><thead><tr class="text-left"><th class="py-2">รหัส</th><th class="py-2">ชื่อ-สกุล</th><th class="py-2">สถานะ</th></tr></thead><tbody>';
        list.forEach(stu => {
        const sidAttr = stu.student_id ? stu.student_id : ('no_' + (stu.student_no || ''));
        html += `<tr class="border-t"><td class="py-2">${stu.student_no || ''}</td><td class="py-2">${stu.student_name || ''}</td><td class="py-2"><div class="flex items-center gap-3">`;
        const reportAttr = stu.report_id ? stu.report_id : '';
        html += `<select data-room="${room}" data-stu="${sidAttr}" data-report="${reportAttr}" name="attendance[${room}][${sidAttr}]" class="attendance-select border rounded px-2 py-1">`;
        attendanceStatus.forEach(st => {
          html += `<option value="${st.value}">${st.emoji} ${st.label}</option>`;
        });
        html += `</select>`;
        html += `<span class="attendance-pill inline-block px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700">✅ มา</span>`;
        html += `</div></td></tr>`;
      });
      html += '</tbody></table></div>';
    });

    studentAttendanceArea.innerHTML = html;

    // Apply statuses if provided by studentsByRoom entries (status may be Thai words)
    const mapping = { 'มาเรียน':'present','มาสาย':'late','ลาป่วย':'sick','ลากิจ':'personal','เข้าร่วมกิจกรรม':'activity','ขาดเรียน':'absent','โดดเรียน':'truant' };
    studentAttendanceArea.querySelectorAll('.attendance-select').forEach(sel => {
      const sid = sel.dataset.stu;
      // find provided status in studentsByRoom
      let foundStatus = null;
      Object.keys(studentsByRoom).forEach(r => {
        (studentsByRoom[r] || []).forEach(s => {
          const key = s.student_id ? String(s.student_id) : ('no_' + (s.student_no || ''));
          if (key === sid && s.status) foundStatus = s.status;
        });
      });
      if (foundStatus) {
        const key = mapping[foundStatus] || foundStatus || 'present';
        sel.value = key;
      }
      applyStyleToRow(sel);
      sel.addEventListener('change', () => applyStyleToRow(sel));
    });
  }

  // Save attendance handler
  document.addEventListener('click', function(e){
    const btn = e.target.closest && e.target.closest('#btnSaveAttendance');
    if (!btn) return;
    const subjectId = subjectSelect.value;
    const date = attendanceDateInput.value;
    if (!subjectId) { Swal.fire('ผิดพลาด', 'กรุณาเลือกวิชาก่อน', 'error'); return; }
    if (!date) { Swal.fire('ผิดพลาด', 'กรุณาเลือกวันที่ก่อนบันทึก', 'error'); return; }

    const selects = Array.from(document.querySelectorAll('.attendance-select'));
    if (!selects.length) { Swal.fire('ผิดพลาด', 'ไม่มีข้อมูลนักเรียนให้บันทึก', 'error'); return; }

    const rows = [];
    selects.forEach(s => {
      const room = s.dataset.room || '';
      const stu = s.dataset.stu || '';
      const reportId = s.dataset.report || null;
      const status = s.value || null;
      if (!stu) return;
      if (stu.startsWith('no_')) {
        rows.push({ student_no: stu.replace(/^no_/, ''), status: status, class_room: room, report_id: reportId });
      } else {
        rows.push({ student_id: stu, status: status, class_room: room, report_id: reportId });
      }
    });

    // POST to save endpoint
    btn.disabled = true;
    btn.textContent = 'กำลังบันทึก...';
    const payload = { subject_id: subjectId, date: date, teacher_id: <?php echo json_encode($_SESSION['user']['Teach_id']); ?>, rows: rows };
    fetch('../controllers/AttendanceController.php?action=save_attendance', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
    })
    .then(r => r.ok ? r.json() : Promise.reject())
    .then(res => {
      if (res && res.success) {
        Swal.fire('บันทึกสำเร็จ', 'บันทึกสถานะการเข้าเรียนแล้ว', 'success');
      } else {
        Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึก: ' + (res.error || 'Unknown'), 'error');
      }
    })
    .catch(err => {
      console.error('save error', err);
      Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดขณะบันทึก', 'error');
    })
    .finally(() => { btn.disabled = false; btn.textContent = 'บันทึก'; });
  });
  // init
  loadSubjectsForAttendance();
});
</script>
</script>
<?php require_once('script.php');?>
</body>
</html>
