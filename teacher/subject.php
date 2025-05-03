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
              📚 จัดการรายวิชา  <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>
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
            <div class="mb-3">
              <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center gap-2" id="btnAddSubject">
                ➕ เพิ่มรายวิชา
              </button>
            </div>
            <div class="overflow-x-auto rounded shadow">
              <table class="min-w-full bg-white border border-gray-200" id="subjectTable">
                <thead class="bg-blue-100">
                  <tr>
                    <th class="py-2 px-3 border-b text-center">🔢 รหัสวิชา</th>
                    <th class="py-2 px-3 border-b text-center">📖 ชื่อวิชา</th>
                    <th class="py-2 px-3 border-b text-center">🏫 ระดับชั้น</th>
                    <th class="py-2 px-3 border-b text-center">🗂️ ประเภท</th>
                    <th class="py-2 px-3 border-b text-center">✅ สถานะ</th>
                    <th class="py-2 px-3 border-b text-center">👤 ผู้สร้าง</th>
                    <th class="py-2 px-3 border-b text-center">⚙️ จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- ข้อมูลจะถูกเติมโดย JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal -->
      <div id="modalAddSubject" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
          <button id="closeModalAddSubject" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">➕ เพิ่มรายวิชาใหม่</h2>
          <form id="formAddSubject" class="space-y-3">
            <div>
              <label class="block mb-1 font-medium">รหัสวิชา <span class="text-red-500">* (โปรดกรอกชื่อวิชา)</span></label>
              <input type="text" name="code" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
            <div>
              <label class="block mb-1 font-medium">ชื่อวิชา <span class="text-red-500">* (โปรดกรอกรหัสวิชา ไม่ต้องเว้นวรรค เช่น ง11101)</span></label>
              <input type="text" name="name" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
            <div>
              <label class="block mb-1 font-medium">ระดับชั้น <span class="text-red-500">* (ระดับชั้นของวิชา)</span></label>
              <select name="level" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
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
              <label class="block mb-1 font-medium">ประเภทวิชา <span class="text-red-500">* (โปรดเลือกประเภทวิชา)</span></label>
              <select name="subject_type" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                <option value="">-- เลือกประเภทวิชา --</option>
                <option value="พื้นฐาน">พื้นฐาน</option>
                <option value="เพิ่มเติม">เพิ่มเติม</option>
                <option value="กิจกรรมพัฒนาผู้เรียน">กิจกรรมพัฒนาผู้เรียน</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 font-medium">สถานะ</label>
              <select name="status" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                <option value="เปิดสอน">✅ เปิดสอน</option>
                <option value="ไม่เปิดสอน">❌ ไม่เปิดสอน</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 font-medium">ห้องเรียน <span class="text-red-500">*</span></label>
              <input type="text" name="class_room" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น ห้อง 1, ห้อง 2" />
            </div>
            <div class="flex gap-2">
              <div class="w-1/2">
                <label class="block mb-1 font-medium">คาบเริ่ม <span class="text-red-500">*</span></label>
                <input type="number" name="period_start" min="1" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น 1" />
              </div>
              <div class="w-1/2">
                <label class="block mb-1 font-medium">คาบสิ้นสุด <span class="text-red-500">*</span></label>
                <input type="number" name="period_end" min="1" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น 2" />
              </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" id="cancelAddSubject" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">ยกเลิก</button>
              <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">บันทึก</button>
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

<script>

const teacherId = <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>;

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
            <tr class="hover:bg-blue-50">
              <td class="py-2 px-3 border-b text-center">${subject.code}</td>
              <td class="py-2 px-3 border-b">${subject.name}</td>
              <td class="py-2 px-3 border-b text-center">${subject.level}</td>
              <td class="py-2 px-3 border-b text-center">${subject.subject_type || ''}</td>
              <td class="py-2 px-3 border-b text-center">${statusBadge(subject.status)}</td>
              <td class="py-2 px-3 border-b text-center">${subject.username || '-'}</td>
              <td class="py-2 px-3 border-b text-center">
                <button class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded mr-1 btn-edit flex items-center gap-1" data-id="${subject.id}">
                  ✏️ แก้ไข
                </button>
                <button class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded btn-delete flex items-center gap-1" data-id="${subject.id}">
                  🗑️ ลบ
                </button>
              </td>
            </tr>
          `;
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
  });
  btnClose.addEventListener('click', () => {
    modal.classList.add('hidden');
    form.reset();
  });
  btnCancel.addEventListener('click', () => {
    modal.classList.add('hidden');
    form.reset();
  });

  // (Optional) ปิด modal เมื่อคลิกพื้นหลัง
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.add('hidden');
      form.reset();
    }
  });

  // TODO: เพิ่ม event สำหรับ submit form เพื่อบันทึกข้อมูล

});
</script>
<?php require_once('script.php');?>
</body>
</html>
