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
/* Modal open animation */
@keyframes modal-pop {
  0% { transform: translateY(-8px) scale(.96); opacity: 0 }
  100% { transform: translateY(0) scale(1); opacity: 1 }
}
.modal-animate {
  animation: modal-pop .18s cubic-bezier(.2,.9,.2,1) both;
}
.backdrop-blur {
  -webkit-backdrop-filter: blur(6px);
  backdrop-filter: blur(6px);
  /* subtle translucent layer so blur is visible while keeping soft tone */
  background-color: rgba(255,255,255,0.06);
}
</style>

<style>
/* Remove separators between table rows */
#subjectTable tbody td { border-bottom: 0 !important; }
#subjectTable tbody tr { border-bottom: 0 !important; }

/* Filter chip styles (fallback when Tailwind not applied yet) */
.status-chip { cursor: pointer; padding: 6px 10px; border-radius: 8px; background: #f3f4f6; color: #374151; border: 1px solid transparent; }
.status-chip.active { background: #4f46e5; color: white; }
</style>

<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper bg-gray-50">

  <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center gap-3">
              <span class="text-3xl">📚</span>
              <span>จัดการรายวิชา</span>
            </h1>
            <p class="mt-1 text-sm text-gray-500">จัดการข้อมูลรายวิชา เพิ่ม/แก้ไข/ลบ และตั้งค่าคาบสอนอย่างงดงาม ✨</p>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <section class="content">
      <div class="container-fluid flex justify-center">
        <div class="w-full max-w-8xl">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="mb-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
              <div class="flex items-center gap-3">
                <button class="bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg shadow-lg transform transition hover:-translate-y-0.5 flex items-center gap-2" id="btnAddSubject">
                  <span class="text-xl">➕</span>
                  <span class="font-medium">เพิ่มรายวิชา</span>
                </button>
                <div class="relative">
                  <input id="subjectSearch" type="text" placeholder="ค้นหารหัสหรือชื่อวิชา... 🔎" class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-300" />
                </div>
              </div>
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-2" id="filterChipsWrap">
                  <div class="status-chip active" data-val="">ทั้งหมด</div>
                  <div class="status-chip" data-val="เปิดสอน">✅ เปิดสอน</div>
                  <div class="status-chip" data-val="ไม่เปิดสอน">❌ ไม่เปิดสอน</div>
                </div>
                <select id="filterStatus" class="hidden border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                  <option value="">สถานะ: ทั้งหมด</option>
                  <option value="เปิดสอน">✅ เปิดสอน</option>
                  <option value="ไม่เปิดสอน">❌ ไม่เปิดสอน</option>
                </select>
              </div>
            </div>
            <div class="overflow-x-auto rounded-lg shadow-lg bg-white">
              <table class="min-w-full bg-white" id="subjectTable">
                <thead class="bg-gradient-to-r from-blue-500 to-indigo-500 sticky top-0 ">
                  <tr class="text-sm text-white">
                    <th class="py-3 px-4 text-center">🔢 รหัสวิชา</th>
                    <th class="py-3 px-4 text-left">📖 ชื่อวิชา</th>
                    <th class="py-3 px-4 text-center">🏫 ระดับ</th>
                    <th class="py-3 px-4 text-center">🗂️ ประเภท</th>
                    <th class="py-3 px-4 text-center">✅ สถานะ</th>
                    <th class="py-3 px-4 text-center">👤 ผู้สร้าง</th>
                    <th class="py-3 px-4 text-center">⏰ รายละเอียดคาบ</th>
                    <th class="py-3 px-4 text-center">⚙️ จัดการ</th>
                  </tr>
                </thead>
                <tbody class="text-sm">
                  <!-- ข้อมูลจะถูกเติมโดย JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal -->
      <div id="modalAddSubject" class="fixed inset-0 bg-transparent backdrop-blur flex items-center justify-center z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 relative overflow-y-auto max-h-[90vh] ring-1 ring-gray-200" aria-hidden="false">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h2 id="modalTitle" class="text-2xl font-semibold text-gray-800 flex items-center gap-3">🎓 เพิ่ม/แก้ไขรายวิชา</h2>
              <p class="text-sm text-gray-500">กรอกข้อมูลให้ครบถ้วนแล้วกดบันทึก เพื่อเพิ่มรายวิชาใหม่</p>
            </div>
            <div class="flex items-center gap-2">
              <button id="closeModalAddSubject" aria-label="ปิด" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
              </button>
            </div>
          </div>
          <form id="formAddSubject" class="grid grid-cols-1 md:grid-cols-2 gap-4" novalidate>
            <div class="md:col-span-1">
              <label class="block mb-1 font-medium text-gray-700">รหัสวิชา <span class="text-red-500">*</span></label>
              <input id="inputCode" type="text" name="code" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" maxlength="6" placeholder="เช่น ง11101" />
              <p class="text-xs text-gray-400 mt-1">ไม่ต้องเว้นวรรค</p>
            </div>
            <div class="md:col-span-1">
              <label class="block mb-1 font-medium text-gray-700">ชื่อวิชา <span class="text-red-500">*</span></label>
              <input id="inputName" type="text" name="name" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
            </div>
            <div>
              <label class="block mb-1 font-medium text-gray-700">ระดับชั้น <span class="text-red-500">*</span></label>
              <select name="level" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">-- เลือกระดับชั้น --</option>
                <option value="1">มัธยมศึกษาปีที่ 1</option>
                <option value="2">มัธยมศึกษาปีที่ 2</option>
                <option value="3">มัธยมศึกษาปีที่ 3</option>
                <option value="4">มัธยมศึกษาปีที่ 4</option>
                <option value="5">มัธยมศึกษาปีที่ 5</option>
                <option value="6">มัธยมศึกษาปีที่ 6</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 font-medium text-gray-700">ประเภทวิชา</label>
              <select name="subject_type" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">-- เลือกประเภทวิชา --</option>
                <option value="พื้นฐาน">พื้นฐาน</option>
                <option value="เพิ่มเติม">เพิ่มเติม</option>
                <option value="กิจกรรมพัฒนาผู้เรียน">กิจกรรมพัฒนาผู้เรียน</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 font-medium text-gray-700">สถานะ</label>
              <select name="status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="เปิดสอน">✅ เปิดสอน</option>
                <option value="ไม่เปิดสอน">❌ ไม่เปิดสอน</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="block mb-2 font-medium text-gray-700">เลือกห้องเรียน <span class="text-red-500">*</span></label>
              <div class="flex flex-wrap gap-2" id="classRoomCheckboxWrap">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                  <label class="inline-flex items-center gap-2 px-3 py-1 rounded-lg border hover:shadow-sm cursor-pointer text-sm">
                    <input type="checkbox" name="class_room[]" value="ห้อง <?php echo $i; ?>" class="form-checkbox text-indigo-600 class-room-checkbox" />
                    <span>ห้อง <?php echo $i; ?></span>
                  </label>
                <?php endfor; ?>
              </div>
            </div>
            <div id="classRoomDetails" class="md:col-span-2 space-y-4 mt-2">
              <!-- ฟิลด์รายละเอียดห้องแต่ละห้องจะถูกเติมโดย JS -->
            </div>
            <div class="md:col-span-2 flex items-center justify-end gap-3 pt-2">
              <button type="button" id="cancelAddSubject" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">ยกเลิก</button>
              <button type="submit" class="px-4 py-3 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-medium">บันทึก</button>
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
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

<script>
const teacherId = <?php echo isset($_SESSION['user']['Teach_id']) ? 
json_encode($_SESSION['user']['Teach_id']) : 'null'; ?>;

// console.log('Teacher ID:', teacherId); // Log the teacher ID
const days = [
  { key: 'จันทร์', label: 'จันทร์' },
  { key: 'อังคาร', label: 'อังคาร' },
  { key: 'พุธ', label: 'พุธ' },
  { key: 'พฤหัสบดี', label: 'พฤหัสบดี' },
  { key: 'ศุกร์', label: 'ศุกร์' },
  { key: 'เสาร์', label: 'เสาร์' },
  { key: 'อาทิตย์', label: 'อาทิตย์' }
];

// ฟังก์ชันสร้างแถววัน/คาบ สำหรับแต่ละห้อง
function renderClassRoomDayRow(room, idx = 0) {
  return `
    <div class="flex items-end gap-2 mb-2 class-room-day-row" data-room="${room}">
      <div>
        <label class="block mb-1 font-medium">วัน</label>
        <select name="class_days[${room}][]" class="border rounded px-2 py-1 focus:outline-none focus:ring focus:border-blue-300" required>
          <option value="">เลือกวัน</option>
          ${days.map(day => `<option value="${day.key}">${day.label}</option>`).join('')}
        </select>
      </div>
      <div>
        <label class="block mb-1 font-medium">คาบเริ่ม</label>
        <input type="number" name="period_start[${room}][]" min="1" class="w-20 border rounded px-2 py-1 focus:outline-none focus:ring focus:border-blue-300" required placeholder="เริ่ม" />
      </div>
      <div>
        <label class="block mb-1 font-medium">คาบสิ้นสุด</label>
        <input type="number" name="period_end[${room}][]" min="1" class="w-20 border rounded px-2 py-1 focus:outline-none focus:ring focus:border-blue-300" required placeholder="สิ้นสุด" />
      </div>
      <button type="button" class="remove-day-row bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded mb-1" title="ลบแถว">ลบ</button>
    </div>
  `;
}

function renderClassRoomDetail(room) {
  return `
    <div class="border rounded p-3 bg-gray-50 mb-2" data-room="${room}">
      <div class="font-semibold mb-2 text-blue-700">รายละเอียด ${room}</div>
      <div class="class-room-days-list" data-room="${room}">
        ${renderClassRoomDayRow(room, 0)}
      </div>
      <button type="button" class="add-day-row bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded mt-2" data-room="${room}">+ เพิ่มวัน/คาบ</button>
    </div>
  `;
}

function renderClassDetailButton(subjectId) {
  return `<button class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded btn-detail flex items-center gap-1" data-id="${subjectId}">
    👁️ ดูรายละเอียด
  </button>`;
}

function renderClassPeriods(periods) {
  if (!periods || periods.length === 0) return '-';
  return periods.map(p =>
    `${p.class_room} (${p.day_of_week} ${p.period_start}-${p.period_end})`
  ).join('<br>');
}

function renderStatusSwitch(subject) {
  const checked = subject.status === 'เปิดสอน' ? 'checked' : '';
  return `
    <label class="toggle-switch">
      <input type="checkbox" class="status-switch" data-id="${subject.id}" ${checked}>
      <span class="toggle-slider"></span>
    </label>
    <span class="ml-2">${subject.status === 'เปิดสอน' ? '✅ เปิดสอน' : '❌ ไม่เปิดสอน'}</span>
  `;
}


document.addEventListener('DOMContentLoaded', function() {
  loadSubjects();

  function statusBadge(status) {
    if (status === 'เปิดสอน') {
      return '<span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-700">✅ เปิดสอน</span>';
    } else {
      return '<span class="inline-block px-2 py-1 text-xs rounded bg-gray-200 text-gray-600">❌ ไม่เปิดสอน</span>';
    }
  }

  function loadSubjects() {
    fetch('../controllers/SubjectController.php?action=list&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(data => {
        const tbody = document.querySelector('#subjectTable tbody');
        tbody.innerHTML = '';
        console.log('Subjects:', data); // Log the subjects data
        data.forEach(subject => {
          tbody.innerHTML += `
            <tr class="transition transform hover:scale-[1.01] hover:shadow-lg bg-white">
              <td class="py-3 px-4 border-b text-center">${subject.code}</td>
              <td class="py-3 px-4 border-b">${subject.name}</td>
              <td class="py-3 px-4 border-b text-center">${subject.level}</td>
              <td class="py-3 px-4 border-b text-center">${subject.subject_type || ''}</td>
              <td class="py-3 px-4 border-b text-center">${renderStatusSwitch(subject)}</td>
              <td class="py-3 px-4 border-b text-center">${subject.username || '-'}</td>
              <td class="py-3 px-4 border-b text-center">${renderClassDetailButton(subject.id)}</td>
              <td class="py-3 px-4 border-b text-center flex gap-2 justify-center">
                <button class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-lg mr-1 btn-edit flex items-center gap-2" data-id="${subject.id}">
                  ✏️ แก้ไข
                </button>
                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg btn-delete flex items-center gap-2" data-id="${subject.id}">
                  🗑️ ลบ
                </button>
              </td>
            </tr>
          `;
        });

        // Apply client-side filters (search/status) after render
        applyFilters();

        // Event: ดูรายละเอียด
        document.querySelectorAll('.btn-detail').forEach(btn => {
          btn.addEventListener('click', function() {
            const subjectId = btn.getAttribute('data-id');
            showSubjectDetail(subjectId);
          });
        });

        // Event: ลบรายวิชา
        document.querySelectorAll('.btn-delete').forEach(btn => {
          btn.addEventListener('click', function() {
            const subjectId = btn.getAttribute('data-id');
            Swal.fire({
              title: 'ยืนยันการลบ',
              text: 'คุณต้องการลบรายวิชานี้หรือไม่?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'ลบ',
              cancelButtonText: 'ยกเลิก'
            }).then(result => {
              if (result.isConfirmed) {
                fetch('../controllers/SubjectController.php?action=delete', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: subjectId })
                })
                .then(res => res.json())
                .then(result => {
                  if (result.success) {
                    Swal.fire('ลบสำเร็จ', 'ลบรายวิชาเรียบร้อยแล้ว', 'success');
                    if (typeof confetti === 'function') confetti({ particleCount: 40, spread: 60, origin: { y: 0.6 } });
                    loadSubjects();
                  } else {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถลบรายวิชาได้', 'error');
                  }
                })
                .catch(() => {
                  Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                });
              }
            });
          });
        });

        // Event: แก้ไขรายวิชา (แสดง modal พร้อมข้อมูลเดิม)
        document.querySelectorAll('.btn-edit').forEach(btn => {
          btn.addEventListener('click', function() {
            const subjectId = btn.getAttribute('data-id');
            fetch('../controllers/SubjectController.php?action=detail&subjectId=' + encodeURIComponent(subjectId))
              .then(res => res.json())
              .then(data => {
                // ดึงข้อมูล subject หลัก
                const subject = data.subject;
                const classes = data.classes || [];
                if (!subject) {
                  Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลสำหรับแก้ไข', 'error');
                  return;
                }
                // กรอกข้อมูลในฟอร์ม
                form.code.value = subject.code;
                form.name.value = subject.name;
                form.level.value = subject.level;
                form.subject_type.value = subject.subject_type;
                form.status.value = subject.status;

                // --- กรอกข้อมูลห้องเรียนและคาบสอนเดิม ---
                // 1. เคลียร์ checkbox ห้องเรียน
                document.querySelectorAll('.class-room-checkbox').forEach(cb => cb.checked = false);
                // 2. หา unique ห้องเรียนจาก classes
                const uniqueRooms = [...new Set(classes.map(c => c.class_room))];
                // 3. เช็ค checkbox ห้องเรียนที่มีใน classes
                uniqueRooms.forEach(room => {
                  const cb = Array.from(document.querySelectorAll('.class-room-checkbox')).find(cb => cb.value === room);
                  if (cb) cb.checked = true;
                });
                // 4. render ห้องเรียน
                updateClassRoomDetails();
                // 5. ใส่ข้อมูลวัน/คาบในแต่ละห้อง
                uniqueRooms.forEach(room => {
                  const roomClasses = classes.filter(c => c.class_room === room);
                  const list = classRoomDetails.querySelector(`.class-room-days-list[data-room="${room}"]`);
                  if (list) {
                    list.innerHTML = '';
                    roomClasses.forEach((c, idx) => {
                      // render row
                      list.insertAdjacentHTML('beforeend', renderClassRoomDayRow(room, idx));
                    });
                    // ใส่ค่าใน input
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

                  modal.classList.remove('hidden');
                  // animate modal content and backdrop, lock scroll and focus first input
                  if (modal.firstElementChild) modal.firstElementChild.classList.add('modal-animate');
                  modal.classList.add('backdrop-blur');
                  document.body.classList.add('overflow-hidden');
                  setTimeout(() => { document.getElementById('inputCode')?.focus(); }, 120);
                form.setAttribute('data-mode', 'edit');
                form.setAttribute('data-id', subjectId);
              });
          });
        });

        // Event: สถานะ slide switch
        document.querySelectorAll('.status-switch').forEach(sw => {
          sw.addEventListener('change', function() {
            const subjectId = this.getAttribute('data-id');
            const newStatus = this.checked ? 'เปิดสอน' : 'ไม่เปิดสอน';

            fetch('../controllers/SubjectController.php?action=updateStatus', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id: subjectId, status: newStatus })
            })
            .then(res => res.json())
            .then(result => {
              if (result.success) {
                Swal.fire('สำเร็จ', 'อัปเดตสถานะเรียบร้อยแล้ว', 'success');
                if (typeof confetti === 'function') confetti({ particleCount: 20, spread: 40, origin: { y: 0.6 } });
              } else {
                Swal.fire('ผิดพลาด', 'อัปเดตสถานะไม่สำเร็จ', 'error');
                this.checked = !this.checked; // ย้อนกลับถ้าล้มเหลว
              }
              loadSubjects(); // โหลดใหม่ไม่ว่าจะสำเร็จหรือไม่
            })
            .catch(err => {
              console.error(err);
              Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดระหว่างอัปเดต', 'error');
              this.checked = !this.checked;
              loadSubjects();
            });
          });
        });
      });
  }

  // Modal logic
  const modal = document.getElementById('modalAddSubject');
  const btnAdd = document.getElementById('btnAddSubject');
  const btnClose = document.getElementById('closeModalAddSubject');
  const btnCancel = document.getElementById('cancelAddSubject');
  const form = document.getElementById('formAddSubject');

  btnAdd.addEventListener('click', () => {
    modal.classList.remove('hidden');
    if (modal.firstElementChild) modal.firstElementChild.classList.remove('opacity-0');
    if (modal.firstElementChild) modal.firstElementChild.classList.add('modal-animate');
    modal.classList.add('backdrop-blur');
    // lock background scroll and focus first input
    document.body.classList.add('overflow-hidden');
    setTimeout(() => { document.getElementById('inputCode')?.focus(); }, 120);
  });
  btnClose.addEventListener('click', () => {
    // close animation
    if (modal.firstElementChild) modal.firstElementChild.classList.remove('modal-animate');
    modal.classList.remove('backdrop-blur');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    form.reset();
  });
  btnCancel.addEventListener('click', () => {
    if (modal.firstElementChild) modal.firstElementChild.classList.remove('modal-animate');
    modal.classList.remove('backdrop-blur');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    form.reset();
  });

  // (Optional) ปิด modal เมื่อคลิกพื้นหลัง
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      if (modal.firstElementChild) modal.firstElementChild.classList.remove('modal-animate');
      modal.classList.remove('backdrop-blur');
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      form.reset();
    }
  });

  // Close modal with ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      if (modal.firstElementChild) modal.firstElementChild.classList.remove('modal-animate');
      modal.classList.remove('backdrop-blur');
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      form.reset();
    }
  });

  // ห้องเรียน checkbox logic
  const classRoomCheckboxes = document.querySelectorAll('.class-room-checkbox');
  const classRoomDetails = document.getElementById('classRoomDetails');

  function updateClassRoomDetails() {
    classRoomDetails.innerHTML = '';
    document.querySelectorAll('.class-room-checkbox:checked').forEach(cb => {
      const room = cb.value;
      classRoomDetails.innerHTML += renderClassRoomDetail(room);
    });
    bindDayRowEvents();
  }

  classRoomCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateClassRoomDetails);
  });

  // ฟังก์ชัน bind event ให้ปุ่มเพิ่ม/ลบแถววัน/คาบ
  function bindDayRowEvents() {
    // เพิ่มวัน/คาบ
    document.querySelectorAll('.add-day-row').forEach(btn => {
      btn.onclick = function() {
        const room = btn.getAttribute('data-room');
        const list = classRoomDetails.querySelector(`.class-room-days-list[data-room="${room}"]`);
        list.insertAdjacentHTML('beforeend', renderClassRoomDayRow(room));
        bindDayRowEvents();
      };
    });
    // ลบแถว
    document.querySelectorAll('.remove-day-row').forEach(btn => {
      btn.onclick = function() {
        const row = btn.closest('.class-room-day-row');
        if (row.parentNode.childElementCount > 1) {
          row.remove();
        }
      };
    });
  }

  // เรียกครั้งแรกเพื่อ bind event
  bindDayRowEvents();

  // Client-side filtering (search + status)
  function applyFilters() {
    const q = (document.getElementById('subjectSearch')?.value || '').toLowerCase().trim();
    const status = (document.getElementById('filterStatus')?.value || '').trim();
    const rows = document.querySelectorAll('#subjectTable tbody tr');
    rows.forEach(r => {
      const code = (r.children[0]?.textContent || '').toLowerCase();
      const name = (r.children[1]?.textContent || '').toLowerCase();
      const statText = (r.children[4]?.textContent || '').toLowerCase();
      let match = true;
      if (q && !(code.includes(q) || name.includes(q))) match = false;
      if (status && !statText.includes(status.toLowerCase())) match = false;
      r.style.display = match ? '' : 'none';
    });
  }

  document.getElementById('subjectSearch')?.addEventListener('input', applyFilters);
  document.getElementById('filterStatus')?.addEventListener('change', applyFilters);

  // Filter chip behavior
  const chips = document.querySelectorAll('.status-chip');
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      // set active styles
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      // set hidden select value and apply filters
      const val = chip.getAttribute('data-val') || '';
      const sel = document.getElementById('filterStatus');
      if (sel) sel.value = val;
      applyFilters();
    });
  });

  // ====== เพิ่มส่วนนี้สำหรับ submit ฟอร์ม ======
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    // เก็บข้อมูล subject
    const formData = new FormData(form);
    const subjectData = {
      code: formData.get('code'),
      name: formData.get('name'),
      level: formData.get('level'),
      subject_type: formData.get('subject_type'),
      status: formData.get('status')
    };

    // เก็บข้อมูล subject_classes
    const classRooms = [];
    document.querySelectorAll('.class-room-checkbox:checked').forEach(cb => {
      const room = cb.value;
      const days = formData.getAll(`class_days[${room}][]`);
      const periodStarts = formData.getAll(`period_start[${room}][]`);
      const periodEnds = formData.getAll(`period_end[${room}][]`);
      for (let i = 0; i < days.length; i++) {
        classRooms.push({
          class_room: room,
          day_of_week: days[i],
          period_start: periodStarts[i],
          period_end: periodEnds[i]
        });
      }
    });

    // ตรวจสอบโหมด
    const mode = form.getAttribute('data-mode');
    const subjectId = form.getAttribute('data-id');

    let url = '../controllers/SubjectController.php?action=create';
    let method = 'POST';
    let body = JSON.stringify({
      ...subjectData,
      class_rooms: classRooms
    });

    if (mode === 'edit' && subjectId) {
      url = '../controllers/SubjectController.php?action=update';
      body = JSON.stringify({
        id: subjectId,
        ...subjectData,
        class_rooms: classRooms
      });
    }

    fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: body
    })
    .then(res => res.json())
    .then(result => {
      if (result.success) {
        Swal.fire('สำเร็จ', mode === 'edit' ? 'แก้ไขรายวิชาเรียบร้อยแล้ว' : 'เพิ่มรายวิชาเรียบร้อยแล้ว', 'success');
        if (typeof confetti === 'function') confetti({ particleCount: 45, spread: 70, origin: { y: 0.65 } });
        modal.classList.add('hidden');
        if (modal.firstElementChild) modal.firstElementChild.classList.remove('modal-animate');
        modal.classList.remove('backdrop-blur');
        form.reset();
        form.removeAttribute('data-mode');
        form.removeAttribute('data-id');
        loadSubjects();
      } else {
        Swal.fire('ผิดพลาด', mode === 'edit' ? 'ไม่สามารถแก้ไขรายวิชาได้' : 'ไม่สามารถเพิ่มรายวิชาได้', 'error');
      }
    })
    .catch(() => {
      Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    });
  });

});

// Modal แสดงรายละเอียดคาบสอน
function showSubjectDetail(subjectId) {
  fetch('../controllers/SubjectController.php?action=detail&subjectId=' + encodeURIComponent(subjectId))
    .then(res => res.json())
    .then(data => {
      let html = `<div class="text-lg font-bold mb-2">รายละเอียดคาบสอน</div>`;
      let classes = Array.isArray(data) ? data : (data.classes || []);
      if (classes.length > 0) {
        html += `<table class="w-full border-collapse border border-gray-200 mb-4 rounded shadow text-base">
          <thead class="bg-blue-500 text-white text-center "><tr>
            <th class="border px-2 py-1">ห้อง</th>
            <th class="border px-2 py-1">วัน</th>
            <th class="border px-2 py-1">คาบเริ่ม</th>
            <th class="border px-2 py-1">คาบสิ้นสุด</th>
          </tr></thead>
          <tbody>
          ${classes.map(row => `
            <tr>
              <td class="border px-2 py-1">${row.class_room}</td>
              <td class="border px-2 py-1">${row.day_of_week}</td>
              <td class="border px-2 py-1">${row.period_start}</td>
              <td class="border px-2 py-1">${row.period_end}</td>
            </tr>
          `).join('')}
          </tbody>
        </table>`;
      } else {
        html += `<div class="text-gray-500">ไม่พบข้อมูลคาบสอน</div>`;
      }
      Swal.fire({
        html: html,
        width: 600,
        showCloseButton: true,
        showConfirmButton: false
      });
    });
}
</script>
<?php require_once('script.php'); ?>
</body>
</html>
