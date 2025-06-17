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
<!-- Enhanced CSS -->
<style>
.animate-fade-in {
  animation: fadeIn 0.8s ease-out;
}

.animate-modal-in {
  animation: modalIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.9) translateY(-20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.table-row-hover:hover {
  background: linear-gradient(90deg, rgba(59,130,246,0.1) 0%, rgba(99,102,241,0.1) 100%);
  transform: translateX(4px);
  transition: all 0.3s ease;
}

.loading-skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.award-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.award-gold { background-color: #fef3c7; color: #92400e; }
.award-silver { background-color: #e5e7eb; color: #374151; }
.award-bronze { background-color: #fed7aa; color: #9a3412; }
.award-other { background-color: #dbeafe; color: #1e40af; }
</style>
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gradient-to-br from-blue-50 to-indigo-100">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper -->
  <div class="content-wrapper bg-transparent">

    <!-- Content Header -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-3xl font-bold text-blue-800 flex items-center animate-fade-in">
              🏆 บันทึกเกียรติบัตรนักเรียน 
              <span class="ml-3 text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full animate-pulse">
                ระบบจัดการรางวัล
              </span>
            </h1>
          </div>
          <div class="col-sm-6">
            <div class="float-right">
              <button id="btnStats" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                📊 สถิติรางวัล
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        
        <!-- Statistics Cards -->
        <div id="statsCards" class="row mb-4 hidden">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-to-r from-blue-400 to-blue-600 text-white rounded-lg shadow-lg">
              <div class="inner p-4">
                <h3 id="totalCerts" class="text-2xl font-bold">0</h3>
                <p class="text-blue-100">เกียรติบัตรทั้งหมด</p>
              </div>
              <div class="icon">
                <i class="fas fa-certificate text-blue-200"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-to-r from-green-400 to-green-600 text-white rounded-lg shadow-lg">
              <div class="inner p-4">
                <h3 id="totalStudents" class="text-2xl font-bold">0</h3>
                <p class="text-green-100">นักเรียนที่ได้รับรางวัล</p>
              </div>
              <div class="icon">
                <i class="fas fa-users text-green-200"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-to-r from-yellow-400 to-yellow-600 text-white rounded-lg shadow-lg">
              <div class="inner p-4">
                <h3 id="topAward" class="text-lg font-bold">-</h3>
                <p class="text-yellow-100">รางวัลยอดนิยม</p>
              </div>
              <div class="icon">
                <i class="fas fa-trophy text-yellow-200"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-to-r from-red-400 to-red-600 text-white rounded-lg shadow-lg">
              <div class="inner p-4">
                <h3 id="thisMonth" class="text-2xl font-bold">0</h3>
                <p class="text-red-100">เดือนนี้</p>
              </div>
              <div class="icon">
                <i class="fas fa-calendar text-red-200"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Card -->
        <div class="w-full">
          <div class="bg-white rounded-xl shadow-xl p-6 backdrop-blur-sm bg-opacity-95">
            
            <!-- Action Buttons -->
            <div class="mb-6 flex flex-wrap gap-3">
              <button class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" id="btnAddCertificate">
                <i class="fas fa-plus"></i>
                เพิ่มเกียรติบัตร
              </button>
              <button class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" id="btnExport">
                <i class="fas fa-download"></i>
                ส่งออกข้อมูล
              </button>
              <button class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" id="btnRefresh">
                <i class="fas fa-sync-alt"></i>
                รีเฟรช
              </button>
            </div>

            <!-- Search and Filter -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ค้นหานักเรียน</label>
                  <input type="text" id="searchStudent" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="ชื่อนักเรียน...">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ระดับชั้น</label>
                  <select id="filterClass" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกระดับชั้น</option>
                    <option value="ม.1">ม.1</option>
                    <option value="ม.2">ม.2</option>
                    <option value="ม.3">ม.3</option>
                    <option value="ม.4">ม.4</option>
                    <option value="ม.5">ม.5</option>
                    <option value="ม.6">ม.6</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ประเภทรางวัล</label>
                  <select id="filterAward" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกประเภท</option>
                    <option value="รางวัลชนะเลิศ">รางวัลชนะเลิศ</option>
                    <option value="รางวัลรองชนะเลิศอันดับ 1">รางวัลรองชนะเลิศอันดับ 1</option>
                    <option value="รางวัลรองชนะเลิศอันดับ 2">รางวัลรองชนะเลิศอันดับ 2</option>
                    <option value="รางวัลชมเชย">รางวัลชมเชย</option>
                  </select>
                </div>
                <!-- เพิ่มตัวกรองภาคเรียน -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ภาคเรียนที่</label>
                  <select id="filterTerm" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกภาคเรียน</option>
                    <!-- ตัวเลือกจะถูกเติมโดย JS -->
                  </select>
                </div>
                <!-- เพิ่มตัวกรองปีการศึกษา -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ปีการศึกษา</label>
                  <select id="filterYear" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกปี</option>
                    <!-- ตัวเลือกจะถูกเติมโดย JS -->
                  </select>
                </div>
                <div class="flex items-end">
                  <button id="btnClearFilter" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-eraser mr-2"></i>ล้างตัวกรอง
                  </button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg shadow-lg">
              <table class="min-w-full bg-white border border-gray-200" id="certificateTable">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                  <tr>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-user mr-2"></i>ชื่อนักเรียน
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-school mr-2"></i>ชั้น/ห้อง
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-award mr-2"></i>ชื่อรางวัล
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-star mr-2"></i>ระดับรางวัล
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-building mr-2"></i>หน่วยงาน
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-trophy mr-2"></i>ประเภทรางวัล
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-list mr-2"></i>รายละเอียด
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-calendar mr-2"></i>วันที่ได้รับ
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-image mr-2"></i>รูปเกียรติ
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-graduation-cap mr-2"></i>ภาค/ปี
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-user-tie mr-2"></i>ผู้บันทึก
                    </th>
                    <th class="py-4 px-4 border-b text-center font-semibold">
                      <i class="fas fa-cog mr-2"></i>จัดการ
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Loading skeleton -->
                  <tr class="loading-row">
                    <td colspan="12" class="py-8 text-center">
                      <div class="flex justify-center items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="ml-3 text-gray-600">กำลังโหลดข้อมูล...</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Modal -->
      <div id="modalAddCertificate" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-8 relative overflow-y-auto max-h-screen animate-modal-in">
          <button id="closeModalAddCertificate" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-3xl transition-colors hover:rotate-90 transform duration-300">&times;</button>
          
          <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 text-blue-800">
            <i class="fas fa-trophy text-yellow-500"></i>
            บันทึกเกียรติบัตรใหม่
          </h2>
          
          <form id="formAddCertificate" class="space-y-6">
            <!-- รายชื่อนักเรียน -->
            <div>
              <label class="block mb-2 font-medium">รายชื่อนักเรียน <span class="text-red-500">*</span></label>
              <div id="studentsContainer">
                <div class="student-item bg-gray-50 p-3 rounded border mb-2">
                  <div class="flex justify-between items-center mb-2">
                    <span class="font-medium text-sm">👤 นักเรียนคนที่ 1</span>
                    <button type="button" class="remove-student hidden bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">ลบ</button>
                  </div>                  <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <div>
                      <label class="block mb-1 text-sm">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                      <input type="text" name="students[0][name]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" placeholder="ชื่อ-นามสกุล" />
                    </div>
                    <div>
                      <label class="block mb-1 text-sm">ระดับชั้น <span class="text-red-500">*</span></label>
                      <select name="students[0][class]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300">
                        <option value="">-- เลือกชั้น --</option>
                        <option value="ม.1">ม.1</option>
                        <option value="ม.2">ม.2</option>
                        <option value="ม.3">ม.3</option>
                        <option value="ม.4">ม.4</option>
                        <option value="ม.5">ม.5</option>
                        <option value="ม.6">ม.6</option>
                      </select>
                    </div>
                    <div>
                      <label class="block mb-1 text-sm">ห้อง <span class="text-red-500">*</span></label>
                      <input type="text" name="students[0][room]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น 1, 2, 3" />
                    </div>
                  </div>
                  <div class="mt-2">
                    <label class="block mb-1 text-sm">รูปเกียรติบัตร</label>
                    <input type="file" name="students[0][image]" accept="image/*" class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" />
                    <p class="text-xs text-gray-500 mt-1">รองรับไฟล์รูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</p>
                  </div>
                </div>
              </div>
              <button type="button" id="addStudentBtn" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm flex items-center gap-1">
                ➕ เพิ่มนักเรียน
              </button>
            </div>
            <div>
              <label class="block mb-2 font-medium">ชื่อรางวัล <span class="text-red-500">*</span></label>
              <input type="text" name="award_name" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น การแข่งขันคณิตศาสตร์ รายการ..." />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1 font-medium">ระดับรางวัล <span class="text-red-500">*</span></label>
                <select name="award_level" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                  <option value="">-- เลือกระดับรางวัล --</option>
                  <option value="ระดับโรงเรียน">🏫 ระดับโรงเรียน</option>
                  <option value="ระดับอำเภอ">🏘️ ระดับอำเภอ</option>
                  <option value="ระดับจังหวัด">🏙️ ระดับจังหวัด</option>
                  <option value="ระดับภาค">🌏 ระดับภาค</option>
                  <option value="ระดับประเทศ">🇹🇭 ระดับประเทศ</option>
                  <option value="ระดับนานาชาติ">🌍 ระดับนานาชาติ</option>
                </select>
              </div>
              <div>
                <label class="block mb-1 font-medium">หน่วยงานที่มอบรางวัล <span class="text-red-500">*</span></label>
                <input type="text" name="award_organization" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น สพฐ., มหาวิทยาลัย..." />
              </div>
            </div>
            <div>
              <label class="block mb-1 font-medium">ประเภทรางวัล <span class="text-red-500">*</span></label>
              <select name="award_type" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                <option value="">-- เลือกประเภทรางวัล --</option>
                <option value="รางวัลชนะเลิศ">🥇 รางวัลชนะเลิศ</option>
                <option value="รางวัลรองชนะเลิศอันดับ 1">🥈 รางวัลรองชนะเลิศอันดับ 1</option>
                <option value="รางวัลรองชนะเลิศอันดับ 2">🥉 รางวัลรองชนะเลิศอันดับ 2</option>
                <option value="รางวัลชมเชย">🏅 รางวัลชมเชย</option>
                <option value="เกียรติบัตร">📜 เกียรติบัตร</option>
                <option value="รางวัลพิเศษ">⭐ รางวัลพิเศษ</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 font-medium">รายละเอียดรางวัล <span class="text-red-500">*</span></label>
              <textarea name="award_detail" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" rows="3" placeholder="เช่น การแข่งขันคณิตศาสตร์ระดับโรงเรียน, โครงงานวิทยาศาสตร์"></textarea>
            </div>            <div>
              <label class="block mb-1 font-medium">วันที่ได้รับรางวัล <span class="text-red-500">*</span></label>
              <input type="date" name="award_date" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
            <!-- Add: Term and Year fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1 font-medium">ภาคเรียน <span class="text-red-500">*</span></label>
                <input type="number" min="1" max="3" name="term" id="termInput" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น 1 หรือ 2" />
              </div>
              <div>
                <label class="block mb-1 font-medium">ปีการศึกษา <span class="text-red-500">*</span></label>
                <input type="number" min="2500" max="2700" name="year" id="yearInput" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น 2567" />
              </div>
            </div>
            <div>
              <label class="block mb-1 font-medium">หมายเหตุ</label>
              <textarea name="note" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" rows="2" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-6 border-t">
              <button type="button" id="cancelAddCertificate" class="px-6 py-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition-colors">
                <i class="fas fa-times mr-2"></i>ยกเลิก
              </button>
              <button type="submit" class="px-6 py-3 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white transition-all duration-300 hover:scale-105">
                <i class="fas fa-save mr-2"></i>บันทึก
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>

  <?php require_once('../footer.php');?>
</div>
<script>
  // Enhanced JavaScript with animations and better UX
const teacherId = <?php echo isset($_SESSION['user']['Teach_id']) ? json_encode($_SESSION['user']['Teach_id']) : 'null'; ?>;

document.addEventListener('DOMContentLoaded', function() {
  let studentCount = 1;
  let certificatesData = [];
  let currentTermInfo = null;

  // Initialize
  initStudentManagement();
  loadCurrentTermInfo();
  loadCertificates();
  loadStatistics();
  initFilters();
  initEventHandlers();

  function loadCurrentTermInfo() {
    fetch('../controllers/CertificateController.php?action=termInfo')
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          currentTermInfo = result.data;
          displayCurrentTermInfo();
        }
      })
      .catch(err => console.error('Error loading term info:', err));
  }

  function displayCurrentTermInfo() {
    if (currentTermInfo) {
      // Add term info to header
      const termInfoElement = document.createElement('div');
      termInfoElement.className = 'text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-full';
      termInfoElement.innerHTML = `📚 ภาคเรียนที่ ${currentTermInfo.term} ปีการศึกษา ${currentTermInfo.year}`;
      
      const headerElement = document.querySelector('.content-header h1');
      if (headerElement) {
        headerElement.appendChild(termInfoElement);
      }

      // Set form values if inputs exist
      const termInput = document.getElementById('termInput');
      const yearInput = document.getElementById('yearInput');
      if (termInput && yearInput) {
        termInput.value = currentTermInfo.term;
        yearInput.value = currentTermInfo.year;
      }
    }
  }

  // ฟังก์ชันสำหรับจัดการการเพิ่ม/ลบนักเรียน
  function initStudentManagement() {
    const addStudentBtn = document.getElementById('addStudentBtn');
    const studentsContainer = document.getElementById('studentsContainer');

    addStudentBtn.addEventListener('click', function() {
      const studentItem = document.createElement('div');
      studentItem.className = 'student-item bg-gray-50 p-3 rounded border mb-2';      studentItem.innerHTML = `
        <div class="flex justify-between items-center mb-2">
          <span class="font-medium text-sm">👤 นักเรียนคนที่ ${studentCount + 1}</span>
          <button type="button" class="remove-student bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">ลบ</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
          <div>
            <label class="block mb-1 text-sm">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
            <input type="text" name="students[${studentCount}][name]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" placeholder="ชื่อ-นามสกุล" />
          </div>
          <div>
            <label class="block mb-1 text-sm">ระดับชั้น <span class="text-red-500">*</span></label>
            <select name="students[${studentCount}][class]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300">
              <option value="">-- เลือกชั้น --</option>
              <option value="ม.1">ม.1</option>
              <option value="ม.2">ม.2</option>
              <option value="ม.3">ม.3</option>
              <option value="ม.4">ม.4</option>
              <option value="ม.5">ม.5</option>
              <option value="ม.6">ม.6</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 text-sm">ห้อง <span class="text-red-500">*</span></label>
            <input type="text" name="students[${studentCount}][room]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น 1, 2, 3" />
          </div>
        </div>
        <div class="mt-2">
          <label class="block mb-1 text-sm">รูปเกียรติบัตร</label>
          <input type="file" name="students[${studentCount}][image]" accept="image/*" class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" />
          <p class="text-xs text-gray-500 mt-1">รองรับไฟล์รูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</p>
        </div>
      `;
      
      studentsContainer.appendChild(studentItem);
      studentCount++;
      updateRemoveButtons();
    });

    // Event delegation สำหรับปุ่มลบ
    studentsContainer.addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-student')) {
        e.target.closest('.student-item').remove();
        updateRemoveButtons();
        updateStudentNumbers();
      }
    });

    function updateRemoveButtons() {
      const studentItems = studentsContainer.querySelectorAll('.student-item');
      studentItems.forEach((item, index) => {
        const removeBtn = item.querySelector('.remove-student');
        if (studentItems.length === 1) {
          removeBtn.classList.add('hidden');
        } else {
          removeBtn.classList.remove('hidden');
        }
      });
    }

    function updateStudentNumbers() {
      const studentItems = studentsContainer.querySelectorAll('.student-item');
      studentItems.forEach((item, index) => {
        const label = item.querySelector('span');
        label.textContent = `👤 นักเรียนคนที่ ${index + 1}`;
      });
    }

    updateRemoveButtons();
  }

  // Load certificates with enhanced UX
  function loadCertificates() {
    showLoadingState();
    
    fetch('../controllers/CertificateController.php?action=list&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        console.log('List response:', result); // Debug log
        
        if (result.success) {
          certificatesData = Array.isArray(result.data) ? result.data : [];
        } else {
          console.error('List error:', result.message);
          certificatesData = [];
        }
        
        renderCertificateTable(certificatesData);
        hideLoadingState();
      })
      .catch(err => {
        console.error('Error loading certificates:', err);
        hideLoadingState();
        showErrorState();
      });
  }

  function renderCertificateTable(certificates) {
    const tbody = document.querySelector('#certificateTable tbody');
    tbody.innerHTML = '';
    
    if (certificates.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center py-8">
            <div class="flex flex-col items-center">
              <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
              <p class="text-gray-500 text-lg">ยังไม่มีข้อมูลเกียรติบัตร</p>
              <p class="text-gray-400">เริ่มต้นบันทึกเกียรติบัตรแรกของคุณ</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    certificates.forEach((cert, index) => {
      const imageColumn = cert.certificate_image 
        ? `<button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm view-image transition-all duration-300 hover:scale-105" data-image="${cert.certificate_image}">
             <i class="fas fa-eye mr-1"></i>ดูรูป
           </button>`
        : '<span class="text-gray-400 text-sm"><i class="fas fa-image mr-1"></i>ไม่มีรูป</span>';

      const awardBadge = getAwardBadge(cert.award_type);
      
      const termYearInfo = cert.term && cert.year 
        ? `<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">${cert.term}/${cert.year}</span>`
        : '<span class="text-gray-400 text-xs">-</span>';
      
      tbody.innerHTML += `
        <tr class="table-row-hover border-b hover:shadow-md transition-all duration-300" style="animation-delay: ${index * 50}ms">
          <td class="py-4 px-4 border-b font-medium">${cert.student_name}</td>
          <td class="py-4 px-4 border-b text-center">
            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
              ${cert.student_class}/${cert.student_room}
            </span>
          </td>
          <td class="py-4 px-4 border-b">${cert.award_name || '-'}</td>
          <td class="py-4 px-4 border-b text-center">
            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
              ${cert.award_level || '-'}
            </span>
          </td>
          <td class="py-4 px-4 border-b">${cert.award_organization || '-'}</td>
          <td class="py-4 px-4 border-b text-center">${awardBadge}</td>
          <td class="py-4 px-4 border-b max-w-xs truncate" title="${cert.award_detail}">
            ${cert.award_detail}
          </td>
          <td class="py-4 px-4 border-b text-center">
            <span class="text-gray-600">
              <i class="fas fa-calendar-alt mr-1"></i>
              ${formatDate(cert.award_date)}
            </span>
          </td>
          <td class="py-4 px-4 border-b text-center">${imageColumn}</td>
          <td class="py-4 px-4 border-b text-center">${termYearInfo}</td>
          <td class="py-4 px-4 border-b text-center">${cert.teacher_name || '-'}</td>
          <td class="py-4 px-4 border-b text-center">
            <div class="flex gap-2 justify-center">
              <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-lg btn-edit transition-all duration-300 hover:scale-105" data-id="${cert.id}" title="แก้ไข">
                <i class="fas fa-edit"></i>
              </button>
              <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg btn-delete transition-all duration-300 hover:scale-105" data-id="${cert.id}" title="ลบ">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });

    bindTableEvents();
  }

  function getAwardBadge(awardType) {
    if (awardType.includes('ชนะเลิศ')) {
      return `<span class="award-badge award-gold"><i class="fas fa-trophy mr-1"></i>${awardType}</span>`;
    } else if (awardType.includes('รองชนะเลิศ')) {
      return `<span class="award-badge award-silver"><i class="fas fa-medal mr-1"></i>${awardType}</span>`;
    } else if (awardType.includes('ชมเชย')) {
      return `<span class="award-badge award-bronze"><i class="fas fa-award mr-1"></i>${awardType}</span>`;
    } else {
      return `<span class="award-badge award-other"><i class="fas fa-certificate mr-1"></i>${awardType}</span>`;
    }
  }

  function loadStatistics() {
    fetch('../controllers/CertificateController.php?action=statistics&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          updateStatsDisplay(result.data);
          loadTopStudents();
          loadRecentCertificates();
        }
      })
      .catch(err => console.error('Error loading statistics:', err));
  }

  function updateStatsDisplay(data) {
    // ป้องกัน null/undefined
    document.getElementById('totalCerts').textContent = data.total_certificates ?? 0;
    document.getElementById('totalStudents').textContent = data.total_students ?? 0;
    document.getElementById('topAward').textContent = data.top_award ?? '-';
    document.getElementById('thisMonth').textContent = data.this_month ?? 0;
  }

  function loadTopStudents() {
    fetch('../controllers/CertificateController.php?action=topStudents&teacherId=' + encodeURIComponent(teacherId) + '&limit=5')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayTopStudents(result.data);
        }
      })
      .catch(err => console.error('Error loading top students:', err));
  }

  function loadRecentCertificates() {
    fetch('../controllers/CertificateController.php?action=recent&teacherId=' + encodeURIComponent(teacherId) + '&limit=3')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayRecentCertificates(result.data);
        }
      })
      .catch(err => console.error('Error loading recent certificates:', err));
  }

  function displayTopStudents(students) {
    // This could be displayed in a separate modal or section
    console.log('Top students:', students);
  }

  function displayRecentCertificates(certificates) {
    // This could be displayed in a separate section
    console.log('Recent certificates:', certificates);
  }

  // Enhanced event handlers with better UX
  function bindTableEvents() {
    // View image with enhanced modal
    document.querySelectorAll('.view-image').forEach(btn => {
      btn.addEventListener('click', function() {
        const imageName = btn.getAttribute('data-image');
        Swal.fire({
          title: 'รูปเกียรติบัตร',
          imageUrl: `../uploads/certificates/${imageName}`,
          imageWidth: 600,
          imageHeight: 400,
          imageAlt: 'เกียรติบัตร',
          showCloseButton: true,
          showConfirmButton: false,
          customClass: {
            image: 'rounded-lg shadow-lg'
          }
        });
      });
    });

    // Enhanced delete confirmation
    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', function() {
        const certId = btn.getAttribute('data-id');
        Swal.fire({
          title: 'ยืนยันการลบ',
          text: 'คุณต้องการลบข้อมูลเกียรติบัตรนี้หรือไม่?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'ลบ',
          cancelButtonText: 'ยกเลิก',
          confirmButtonColor: '#ef4444',
          customClass: {
            confirmButton: 'hover:scale-105 transition-transform',
            cancelButton: 'hover:scale-105 transition-transform'
          }
        }).then(result => {
          if (result.isConfirmed) {
            deleteCertificate(certId);
          }
        });
      });
    });

    // Edit certificate handlers
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', function() {
        const certId = btn.getAttribute('data-id');
        editCertificate(certId);
      });
    });
  }

  function deleteCertificate(certId) {
    fetch('../controllers/CertificateController.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: certId })
    })
    .then(res => res.json())
    .then(result => {
      if (result.success) {
        Swal.fire({
          title: 'ลบสำเร็จ',
          text: result.message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
        loadCertificates();
        loadStatistics();
      } else {
        Swal.fire('ผิดพลาด', result.message, 'error');
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    });
  }

  // Initialize additional event handlers
  function initEventHandlers() {
    // Statistics toggle
    const btnStats = document.getElementById('btnStats');
    const statsCards = document.getElementById('statsCards');
    
    btnStats.addEventListener('click', function() {
      if (statsCards.classList.contains('hidden')) {
        statsCards.classList.remove('hidden');
        statsCards.style.animation = 'fadeIn 0.5s ease-out';
        btnStats.innerHTML = '<i class="fas fa-chart-line mr-2"></i>ซ่อนสถิติ';
      } else {
        statsCards.classList.add('hidden');
        btnStats.innerHTML = '<i class="fas fa-chart-bar mr-2"></i>สถิติรางวัล';
      }
    });

    // Export button
    const btnExport = document.getElementById('btnExport');
    btnExport.addEventListener('click', function() {
      exportCertificates();
    });

    // Refresh button
    const btnRefresh = document.getElementById('btnRefresh');
    btnRefresh.addEventListener('click', function() {
      // Add spinning animation
      const icon = btnRefresh.querySelector('i');
      icon.classList.add('fa-spin');
      
      Promise.all([loadCertificates(), loadStatistics()])
        .finally(() => {
          setTimeout(() => {
            icon.classList.remove('fa-spin');
          }, 500);
        });
    });
  }

  function exportCertificates() {
    Swal.fire({
      title: 'ส่งออกข้อมูล',
      text: 'เลือกรูปแบบที่ต้องการส่งออก',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Excel (.xlsx)',
      cancelButtonText: 'PDF',
      showDenyButton: true,
      denyButtonText: 'CSV',
      customClass: {
        confirmButton: 'btn btn-success',
        cancelButton: 'btn btn-danger', 
        denyButton: 'btn btn-info'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        exportToFormat('excel');
      } else if (result.isDenied) {
        exportToFormat('csv');
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        exportToFormat('pdf');
      }
    });
  }

  function exportToFormat(format) {
    // Show loading
    Swal.fire({
      title: 'กำลังส่งออกข้อมูล...',
      text: `กำลังสร้างไฟล์ ${format.toUpperCase()}`,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Create export URL
    const exportUrl = `../controllers/CertificateController.php?action=export&format=${format}&teacherId=${encodeURIComponent(teacherId)}`;
    
    // Use fetch to check for errors before downloading
    fetch(exportUrl, {
      method: 'GET',
      headers: {
        'Accept': format === 'json' ? 'application/json' : 
                 format === 'csv' ? 'text/csv' : 
                 format === 'excel' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 
                 'application/pdf'
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      // Check if response is JSON (error response)
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return response.json().then(data => {
          throw new Error(data.message || 'เกิดข้อผิดพลาดในการส่งออกข้อมูล');
        });
      }
      
      // If successful, trigger download
      Swal.close();
      downloadFile(exportUrl);
      
      Swal.fire({
        title: 'ส่งออกสำเร็จ',
        text: `ไฟล์ ${format.toUpperCase()} ถูกส่งออกเรียบร้อยแล้ว`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    })
    .catch(error => {
      Swal.close();
      console.error('Export error:', error);
      Swal.fire({
        title: 'เกิดข้อผิดพลาด',
        text: error.message || 'ไม่สามารถส่งออกข้อมูลได้',
        icon: 'error'
      });
    });
  }

  function downloadFile(url) {
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  function exportToCSV() {
    exportToFormat('csv');
  }

  function exportToExcel() {
    exportToFormat('excel');
  }

  function exportToPDF() {
    exportToFormat('pdf');
  }

  // Modal logic
  const modal = document.getElementById('modalAddCertificate');
  const btnAdd = document.getElementById('btnAddCertificate');
  const btnClose = document.getElementById('closeModalAddCertificate');
  const btnCancel = document.getElementById('cancelAddCertificate');
  const form = document.getElementById('formAddCertificate');

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

  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.add('hidden');
      form.reset();
    }
  });  // Form submit
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(form);
    const mode = form.getAttribute('data-mode');
    
    // เก็บข้อมูลนักเรียนทั้งหมด
    const students = [];
    const studentItems = document.querySelectorAll('.student-item');
    
    studentItems.forEach((item, index) => {
      const nameInput = item.querySelector(`input[name="students[${index}][name]"]`);
      const classSelect = item.querySelector(`select[name="students[${index}][class]"]`);
      const roomInput = item.querySelector(`input[name="students[${index}][room]"]`);
      const imageInput = item.querySelector(`input[name="students[${index}][image]"]`);
      
      if (nameInput && classSelect && roomInput && nameInput.value && classSelect.value && roomInput.value) {
        students.push({
          name: nameInput.value,
          class: classSelect.value,
          room: roomInput.value,
          image: imageInput && imageInput.files[0] ? imageInput.files[0] : null
        });
      }
    });

    // ตรวจสอบข้อมูลนักเรียน
    if (mode === 'edit') {
      // โหมดแก้ไข: ต้องมีนักเรียน 1 คน
      if (students.length !== 1) {
        Swal.fire('ผิดพลาด', 'กรุณากรอกข้อมูลนักเรียน 1 คน', 'error');
        return;
      }
    } else {
      // โหมดเพิ่มใหม่: ต้องมีอย่างน้อย 1 คน
      if (students.length === 0) {
        Swal.fire('ผิดพลาด', 'กรุณากรอกข้อมูลนักเรียนอย่างน้อย 1 คน', 'error');
        return;
      }
    }

    // ข้อมูลรางวัลที่ใช้ร่วมกัน
    const certificateData = {
      students: students,
      award_name: formData.get('award_name'),
      award_level: formData.get('award_level'),
      award_organization: formData.get('award_organization'),
      award_type: formData.get('award_type'),
      award_detail: formData.get('award_detail'),
      award_date: formData.get('award_date'),
      note: formData.get('note'),
      term: formData.get('term'),
      year: formData.get('year')
    };

    // อัพโหลดรูปภาพสำหรับแต่ละนักเรียน
    uploadStudentImages(students)
      .then(studentsWithImages => {
        certificateData.students = studentsWithImages;
        saveCertificate(certificateData);
      })
      .catch(err => {
        console.error(err);
        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการอัพโหลดรูป', 'error');
      });
  });

  // ฟังก์ชันอัพโหลดรูปภาพสำหรับแต่ละนักเรียน
  async function uploadStudentImages(students) {
    const studentsWithImages = [];
    
    for (let i = 0; i < students.length; i++) {
      const student = students[i];
      let imageFilename = null;
      
      if (student.image && student.image.size > 0) {
        try {
          const uploadData = new FormData();
          uploadData.append('certificate_image', student.image);
          
          const response = await fetch('../controllers/CertificateController.php?action=upload', {
            method: 'POST',
            body: uploadData
          });
          
          const uploadResult = await response.json();
          
          if (uploadResult.success) {
            imageFilename = uploadResult.filename;
          } else {
            throw new Error(`ไม่สามารถอัพโหลดรูปของ ${student.name}: ${uploadResult.message}`);
          }
        } catch (error) {
          throw new Error(`เกิดข้อผิดพลาดในการอัพโหลดรูปของ ${student.name}: ${error.message}`);
        }
      }
      
      studentsWithImages.push({
        name: student.name,
        class: student.class,
        room: student.room,
        certificate_image: imageFilename
      });
    }
    
    return studentsWithImages;
  }
  function saveCertificate(certificateData) {
    const mode = form.getAttribute('data-mode');
    const certId = form.getAttribute('data-id');
    
    let url = '../controllers/CertificateController.php?action=create';
    let method = 'POST';
    
    if (mode === 'edit' && certId) {
      url = '../controllers/CertificateController.php?action=update';
      certificateData.id = certId;
    }

    // เพิ่มฟิลด์ใหม่ในข้อมูลที่ส่ง
    const formData = new FormData(form);
    certificateData.award_name = formData.get('award_name');
    certificateData.award_level = formData.get('award_level');
    certificateData.award_organization = formData.get('award_organization');

    // Show loading state
    Swal.fire({
      title: 'กำลังบันทึก...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(certificateData)
    })
    .then(result => result.json())
    .then(result => {
      Swal.close();
      if (result.success) {
        let successMessage = mode === 'edit' ? 'แก้ไขเกียรติบัตรเรียบร้อยแล้ว' : result.message;
        
        // Add term info to success message for new certificates
        if (mode !== 'edit' && result.term_info) {
          successMessage += `<br><small class="text-gray-600">บันทึกในภาคเรียนที่ ${result.term_info.term} ปีการศึกษา ${result.term_info.year}</small>`;
        }
        
        Swal.fire({
          title: 'สำเร็จ',
          html: successMessage,
          icon: 'success',
          timer: 3000,
          showConfirmButton: false
        });
        modal.classList.add('hidden');
        resetForm();
        loadCertificates();
        loadStatistics();
      } else {
        Swal.fire('ผิดพลาด', result.message, 'error');
      }
    })
    .catch(err => {
      Swal.close();
      console.error(err);
      Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    });
  }
  function editCertificate(certId) {
    fetch('../controllers/CertificateController.php?action=detail&id=' + encodeURIComponent(certId))
      .then(res => res.json())
      .then(cert => {
        if (cert.success === false) {
          Swal.fire('ผิดพลาด', cert.message, 'error');
          return;
        }

        // เปลี่ยนหัวข้อ modal
        document.querySelector('#modalAddCertificate h2').innerHTML = '✏️ แก้ไขเกียรติบัตร';
        
        // ซ่อนปุ่มเพิ่มนักเรียนในโหมดแก้ไข
        document.getElementById('addStudentBtn').style.display = 'none';
        
        // ซ่อนปุ่มลบนักเรียนคนแรก
        const firstStudentRemoveBtn = document.querySelector('.student-item .remove-student');
        if (firstStudentRemoveBtn) {
          firstStudentRemoveBtn.style.display = 'none';
        }
        
        // ลบนักเรียนคนอื่นๆ ก่อน (ในกรณีที่มี)
        const otherStudents = document.querySelectorAll('.student-item:not(:first-child)');
        otherStudents.forEach(item => item.remove());
        
        // ใส่ข้อมูลในฟอร์ม
        const firstStudent = document.querySelector('.student-item');
        firstStudent.querySelector('input[name="students[0][name]"]').value = cert.student_name;
        firstStudent.querySelector('select[name="students[0][class]"]').value = cert.student_class;
        firstStudent.querySelector('input[name="students[0][room]"]').value = cert.student_room;
        
        form.award_name.value = cert.award_name || '';
        form.award_level.value = cert.award_level || '';
        form.award_organization.value = cert.award_organization || '';
        form.award_type.value = cert.award_type;
        form.award_detail.value = cert.award_detail;
        form.award_date.value = cert.award_date;
        form.note.value = cert.note || '';

        // ตั้งค่าโหมดแก้ไข
        form.setAttribute('data-mode', 'edit');
        form.setAttribute('data-id', certId);
        
        // แสดงข้อมูลภาคเรียน/ปีการศึกษา
        if (cert.term) document.getElementById('termInput').value = cert.term;
        if (cert.year) document.getElementById('yearInput').value = cert.year;

        // แสดง modal
        modal.classList.remove('hidden');
      })
      .catch(err => {
        console.error(err);
        Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
      });
  }

  // ฟังก์ชันรีเซ็ตฟอร์ม
  function resetForm() {
    form.reset();
    form.removeAttribute('data-mode');
    form.removeAttribute('data-id');
    
    // รีเซ็ตหัวข้อ modal
    document.querySelector('#modalAddCertificate h2').innerHTML = '🏆 บันทึกเกียรติบัตรใหม่';
    
    // แสดงปุ่มเพิ่มนักเรียนอีกครั้ง
    document.getElementById('addStudentBtn').style.display = 'flex';
    
    // รีเซ็ตรายชื่อนักเรียนให้เหลือคนเดียว
    const studentsContainer = document.getElementById('studentsContainer');
    const firstStudent = studentsContainer.querySelector('.student-item');
    firstStudent.querySelector('input[name="students[0][name]"]').value = '';
    firstStudent.querySelector('select[name="students[0][class]"]').value = '';
    firstStudent.querySelector('input[name="students[0][room]"]').value = '';
    
    // ลบนักเรียนคนอื่นๆ
    const otherStudents = studentsContainer.querySelectorAll('.student-item:not(:first-child)');
    otherStudents.forEach(item => item.remove());
    
    // แสดงปุ่มลบของนักเรียนคนแรกอีกครั้ง (แต่ยังคงซ่อนไว้ถ้ามีแค่คนเดียว)
    const firstStudentRemoveBtn = firstStudent.querySelector('.remove-student');
    if (firstStudentRemoveBtn) {
      firstStudentRemoveBtn.style.display = 'none'; // ซ่อนไว้เพราะมีแค่คนเดียว
    }
    
    // รีเซ็ตภาคเรียน/ปีการศึกษา
    if (currentTermInfo) {
      document.getElementById('termInput').value = currentTermInfo.term;
      document.getElementById('yearInput').value = currentTermInfo.year;
    }

    studentCount = 1;
  }

  // Enhanced search functionality
  function initFilters() {
    const searchInput = document.getElementById('searchStudent');
    const filterClass = document.getElementById('filterClass');
    const filterAward = document.getElementById('filterAward');
    const filterTerm = document.getElementById('filterTerm');
    const filterYear = document.getElementById('filterYear');
    const btnClearFilter = document.getElementById('btnClearFilter');

    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          loadCertificates();
        }
      }, 500);
    });

    // โหลดตัวเลือกภาคเรียน/ปีการศึกษา
    loadAvailableTermsAndYears();

    filterClass.addEventListener('change', applyFilters);
    filterAward.addEventListener('change', applyFilters);
    filterTerm.addEventListener('change', applyFilters);
    filterYear.addEventListener('change', applyFilters);

    btnClearFilter.addEventListener('click', function() {
      searchInput.value = '';
      filterClass.value = '';
      filterAward.value = '';
      filterTerm.value = '';
      filterYear.value = '';
      loadCertificates();
    });
  }

  function loadAvailableTermsAndYears() {
    fetch('../controllers/CertificateController.php?action=availableTerms&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const terms = new Set();
          const years = new Set();
          result.data.forEach(item => {
            if (item.term) terms.add(item.term);
            if (item.year) years.add(item.year);
          });

          // เติม dropdown ภาคเรียน
          const filterTerm = document.getElementById('filterTerm');
          filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
          Array.from(terms).sort().forEach(term => {
            filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
          });

          // เติม dropdown ปีการศึกษา
          const filterYear = document.getElementById('filterYear');
          filterYear.innerHTML = '<option value="">ทุกปี</option>';
          Array.from(years).sort((a, b) => b - a).forEach(year => {
            filterYear.innerHTML += `<option value="${year}">${year}</option>`;
          });
        }
      });
  }

  function performSearch(searchTerm) {
    fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(teacherId)}`)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
          
          // Show search results info
          if (result.count === 0) {
            Swal.fire({
              title: 'ไม่พบผลลัพธ์',
              text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
              icon: 'info',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Search error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการค้นหา', 'error');
      });
  }

  // ปรับ applyFilters ให้ส่ง term/year ไป backend
  function applyFilters() {
    const filterClass = document.getElementById('filterClass').value;
    const filterAward = document.getElementById('filterAward').value;
    const filterTerm = document.getElementById('filterTerm').value;
    const filterYear = document.getElementById('filterYear').value;
    let url = `../controllers/CertificateController.php?action=search&teacherId=${encodeURIComponent(teacherId)}`;
    if (filterClass) url += `&classFilter=${encodeURIComponent(filterClass)}`;
    if (filterAward) url += `&awardFilter=${encodeURIComponent(filterAward)}`;
    if (filterTerm) url += `&termFilter=${encodeURIComponent(filterTerm)}`;
    if (filterYear) url += `&yearFilter=${encodeURIComponent(filterYear)}`;
    fetch(url)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Filter error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการกรองข้อมูล', 'error');
      });
  }

  // Enhanced statistics with additional info
  function loadStatistics() {
    fetch('../controllers/CertificateController.php?action=statistics&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          updateStatsDisplay(result.data);
          loadTopStudents();
          loadRecentCertificates();
        }
      })
      .catch(err => console.error('Error loading statistics:', err));
  }

  function loadTopStudents() {
    fetch('../controllers/CertificateController.php?action=topStudents&teacherId=' + encodeURIComponent(teacherId) + '&limit=5')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayTopStudents(result.data);
        }
      })
      .catch(err => console.error('Error loading top students:', err));
  }

  function loadRecentCertificates() {
    fetch('../controllers/CertificateController.php?action=recent&teacherId=' + encodeURIComponent(teacherId) + '&limit=3')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayRecentCertificates(result.data);
        }
      })
      .catch(err => console.error('Error loading recent certificates:', err));
  }

  function displayTopStudents(students) {
    // This could be displayed in a separate modal or section
    console.log('Top students:', students);
  }

  function displayRecentCertificates(certificates) {
    // This could be displayed in a separate section
    console.log('Recent certificates:', certificates);
  }

  // Enhanced error handling
  function showErrorState() {
    const tbody = document.querySelector('#certificateTable tbody');
    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="text-center py-8">
          <div class="flex flex-col items-center">
            <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
            <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
            <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
              <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  // Filter and search functionality
  function initFilters() {
    const searchInput = document.getElementById('searchStudent');
    const filterClass = document.getElementById('filterClass');
    const filterAward = document.getElementById('filterAward');
    const filterTerm = document.getElementById('filterTerm');
    const filterYear = document.getElementById('filterYear');
    const btnClearFilter = document.getElementById('btnClearFilter');

    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          loadCertificates();
        }
      }, 500);
    });

    // โหลดตัวเลือกภาคเรียน/ปีการศึกษา
    loadAvailableTermsAndYears();

    filterClass.addEventListener('change', applyFilters);
    filterAward.addEventListener('change', applyFilters);
    filterTerm.addEventListener('change', applyFilters);
    filterYear.addEventListener('change', applyFilters);

    btnClearFilter.addEventListener('click', function() {
      searchInput.value = '';
      filterClass.value = '';
      filterAward.value = '';
      filterTerm.value = '';
      filterYear.value = '';
      loadCertificates();
    });
  }

  function loadAvailableTermsAndYears() {
    fetch('../controllers/CertificateController.php?action=availableTerms&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const terms = new Set();
          const years = new Set();
          result.data.forEach(item => {
            if (item.term) terms.add(item.term);
            if (item.year) years.add(item.year);
          });

          // เติม dropdown ภาคเรียน
          const filterTerm = document.getElementById('filterTerm');
          filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
          Array.from(terms).sort().forEach(term => {
            filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
          });

          // เติม dropdown ปีการศึกษา
          const filterYear = document.getElementById('filterYear');
          filterYear.innerHTML = '<option value="">ทุกปี</option>';
          Array.from(years).sort((a, b) => b - a).forEach(year => {
            filterYear.innerHTML += `<option value="${year}">${year}</option>`;
          });
        }
      });
  }

  function performSearch(searchTerm) {
    fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(teacherId)}`)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
          
          // Show search results info
          if (result.count === 0) {
            Swal.fire({
              title: 'ไม่พบผลลัพธ์',
              text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
              icon: 'info',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Search error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการค้นหา', 'error');
      });
  }

  // ปรับ applyFilters ให้ส่ง term/year ไป backend
  function applyFilters() {
    const filterClass = document.getElementById('filterClass').value;
    const filterAward = document.getElementById('filterAward').value;
    const filterTerm = document.getElementById('filterTerm').value;
    const filterYear = document.getElementById('filterYear').value;
    let url = `../controllers/CertificateController.php?action=search&teacherId=${encodeURIComponent(teacherId)}`;
    if (filterClass) url += `&classFilter=${encodeURIComponent(filterClass)}`;
    if (filterAward) url += `&awardFilter=${encodeURIComponent(filterAward)}`;
    if (filterTerm) url += `&termFilter=${encodeURIComponent(filterTerm)}`;
    if (filterYear) url += `&yearFilter=${encodeURIComponent(filterYear)}`;
    fetch(url)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Filter error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการกรองข้อมูล', 'error');
      });
  }

  // Enhanced statistics with additional info
  function loadStatistics() {
    fetch('../controllers/CertificateController.php?action=statistics&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          updateStatsDisplay(result.data);
          loadTopStudents();
          loadRecentCertificates();
        }
      })
      .catch(err => console.error('Error loading statistics:', err));
  }

  function loadTopStudents() {
    fetch('../controllers/CertificateController.php?action=topStudents&teacherId=' + encodeURIComponent(teacherId) + '&limit=5')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayTopStudents(result.data);
        }
      })
      .catch(err => console.error('Error loading top students:', err));
  }

  function loadRecentCertificates() {
    fetch('../controllers/CertificateController.php?action=recent&teacherId=' + encodeURIComponent(teacherId) + '&limit=3')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayRecentCertificates(result.data);
        }
      })
      .catch(err => console.error('Error loading recent certificates:', err));
  }

  function displayTopStudents(students) {
    // This could be displayed in a separate modal or section
    console.log('Top students:', students);
  }

  function displayRecentCertificates(certificates) {
    // This could be displayed in a separate section
    console.log('Recent certificates:', certificates);
  }

  // Enhanced error handling
  function showErrorState() {
    const tbody = document.querySelector('#certificateTable tbody');
    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="text-center py-8">
          <div class="flex flex-col items-center">
            <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
            <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
            <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
              <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  // Filter and search functionality
  function initFilters() {
    const searchInput = document.getElementById('searchStudent');
    const filterClass = document.getElementById('filterClass');
    const filterAward = document.getElementById('filterAward');
    const filterTerm = document.getElementById('filterTerm');
    const filterYear = document.getElementById('filterYear');
    const btnClearFilter = document.getElementById('btnClearFilter');

    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          loadCertificates();
        }
      }, 500);
    });

    // โหลดตัวเลือกภาคเรียน/ปีการศึกษา
    loadAvailableTermsAndYears();

    filterClass.addEventListener('change', applyFilters);
    filterAward.addEventListener('change', applyFilters);
    filterTerm.addEventListener('change', applyFilters);
    filterYear.addEventListener('change', applyFilters);

    btnClearFilter.addEventListener('click', function() {
      searchInput.value = '';
      filterClass.value = '';
      filterAward.value = '';
      filterTerm.value = '';
      filterYear.value = '';
      loadCertificates();
    });
  }

  function loadAvailableTermsAndYears() {
    fetch('../controllers/CertificateController.php?action=availableTerms&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const terms = new Set();
          const years = new Set();
          result.data.forEach(item => {
            if (item.term) terms.add(item.term);
            if (item.year) years.add(item.year);
          });

          // เติม dropdown ภาคเรียน
          const filterTerm = document.getElementById('filterTerm');
          filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
          Array.from(terms).sort().forEach(term => {
            filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
          });

          // เติม dropdown ปีการศึกษา
          const filterYear = document.getElementById('filterYear');
          filterYear.innerHTML = '<option value="">ทุกปี</option>';
          Array.from(years).sort((a, b) => b - a).forEach(year => {
            filterYear.innerHTML += `<option value="${year}">${year}</option>`;
          });
        }
      });
  }

  function performSearch(searchTerm) {
    fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(teacherId)}`)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
          
          // Show search results info
          if (result.count === 0) {
            Swal.fire({
              title: 'ไม่พบผลลัพธ์',
              text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
              icon: 'info',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Search error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการค้นหา', 'error');
      });
  }

  // ปรับ applyFilters ให้ส่ง term/year ไป backend
  function applyFilters() {
    const filterClass = document.getElementById('filterClass').value;
    const filterAward = document.getElementById('filterAward').value;
    const filterTerm = document.getElementById('filterTerm').value;
    const filterYear = document.getElementById('filterYear').value;
    let url = `../controllers/CertificateController.php?action=search&teacherId=${encodeURIComponent(teacherId)}`;
    if (filterClass) url += `&classFilter=${encodeURIComponent(filterClass)}`;
    if (filterAward) url += `&awardFilter=${encodeURIComponent(filterAward)}`;
    if (filterTerm) url += `&termFilter=${encodeURIComponent(filterTerm)}`;
    if (filterYear) url += `&yearFilter=${encodeURIComponent(filterYear)}`;
    fetch(url)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Filter error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการกรองข้อมูล', 'error');
      });
  }

  // Enhanced statistics with additional info
  function loadStatistics() {
    fetch('../controllers/CertificateController.php?action=statistics&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          updateStatsDisplay(result.data);
          loadTopStudents();
          loadRecentCertificates();
        }
      })
      .catch(err => console.error('Error loading statistics:', err));
  }

  function loadTopStudents() {
    fetch('../controllers/CertificateController.php?action=topStudents&teacherId=' + encodeURIComponent(teacherId) + '&limit=5')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayTopStudents(result.data);
        }
      })
      .catch(err => console.error('Error loading top students:', err));
  }

  function loadRecentCertificates() {
    fetch('../controllers/CertificateController.php?action=recent&teacherId=' + encodeURIComponent(teacherId) + '&limit=3')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayRecentCertificates(result.data);
        }
      })
      .catch(err => console.error('Error loading recent certificates:', err));
  }

  function displayTopStudents(students) {
    // This could be displayed in a separate modal or section
    console.log('Top students:', students);
  }

  function displayRecentCertificates(certificates) {
    // This could be displayed in a separate section
    console.log('Recent certificates:', certificates);
  }

  // Enhanced error handling
  function showErrorState() {
    const tbody = document.querySelector('#certificateTable tbody');
    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="text-center py-8">
          <div class="flex flex-col items-center">
            <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
            <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
            <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
              <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  // Filter and search functionality
  function initFilters() {
    const searchInput = document.getElementById('searchStudent');
    const filterClass = document.getElementById('filterClass');
    const filterAward = document.getElementById('filterAward');
    const filterTerm = document.getElementById('filterTerm');
    const filterYear = document.getElementById('filterYear');
    const btnClearFilter = document.getElementById('btnClearFilter');

    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          loadCertificates();
        }
      }, 500);
    });

    // โหลดตัวเลือกภาคเรียน/ปีการศึกษา
    loadAvailableTermsAndYears();

    filterClass.addEventListener('change', applyFilters);
    filterAward.addEventListener('change', applyFilters);
    filterTerm.addEventListener('change', applyFilters);
    filterYear.addEventListener('change', applyFilters);

    btnClearFilter.addEventListener('click', function() {
      searchInput.value = '';
      filterClass.value = '';
      filterAward.value = '';
      filterTerm.value = '';
      filterYear.value = '';
      loadCertificates();
    });
  }

  function loadAvailableTermsAndYears() {
    fetch('../controllers/CertificateController.php?action=availableTerms&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const terms = new Set();
          const years = new Set();
          result.data.forEach(item => {
            if (item.term) terms.add(item.term);
            if (item.year) years.add(item.year);
          });

          // เติม dropdown ภาคเรียน
          const filterTerm = document.getElementById('filterTerm');
          filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
          Array.from(terms).sort().forEach(term => {
            filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
          });

          // เติม dropdown ปีการศึกษา
          const filterYear = document.getElementById('filterYear');
          filterYear.innerHTML = '<option value="">ทุกปี</option>';
          Array.from(years).sort((a, b) => b - a).forEach(year => {
            filterYear.innerHTML += `<option value="${year}">${year}</option>`;
          });
        }
      });
  }

  function performSearch(searchTerm) {
    fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(teacherId)}`)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
          
          // Show search results info
          if (result.count === 0) {
            Swal.fire({
              title: 'ไม่พบผลลัพธ์',
              text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
              icon: 'info',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Search error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการค้นหา', 'error');
      });
  }

  // ปรับ applyFilters ให้ส่ง term/year ไป backend
  function applyFilters() {
    const filterClass = document.getElementById('filterClass').value;
    const filterAward = document.getElementById('filterAward').value;
    const filterTerm = document.getElementById('filterTerm').value;
    const filterYear = document.getElementById('filterYear').value;
    let url = `../controllers/CertificateController.php?action=search&teacherId=${encodeURIComponent(teacherId)}`;
    if (filterClass) url += `&classFilter=${encodeURIComponent(filterClass)}`;
    if (filterAward) url += `&awardFilter=${encodeURIComponent(filterAward)}`;
    if (filterTerm) url += `&termFilter=${encodeURIComponent(filterTerm)}`;
    if (filterYear) url += `&yearFilter=${encodeURIComponent(filterYear)}`;
    fetch(url)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Filter error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการกรองข้อมูล', 'error');
      });
  }

  // Enhanced statistics with additional info
  function loadStatistics() {
    fetch('../controllers/CertificateController.php?action=statistics&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          updateStatsDisplay(result.data);
          loadTopStudents();
          loadRecentCertificates();
        }
      })
      .catch(err => console.error('Error loading statistics:', err));
  }

  function loadTopStudents() {
    fetch('../controllers/CertificateController.php?action=topStudents&teacherId=' + encodeURIComponent(teacherId) + '&limit=5')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayTopStudents(result.data);
        }
      })
      .catch(err => console.error('Error loading top students:', err));
  }

  function loadRecentCertificates() {
    fetch('../controllers/CertificateController.php?action=recent&teacherId=' + encodeURIComponent(teacherId) + '&limit=3')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayRecentCertificates(result.data);
        }
      })
      .catch(err => console.error('Error loading recent certificates:', err));
  }

  function displayTopStudents(students) {
    // This could be displayed in a separate modal or section
    console.log('Top students:', students);
  }

  function displayRecentCertificates(certificates) {
    // This could be displayed in a separate section
    console.log('Recent certificates:', certificates);
  }

  // Enhanced error handling
  function showErrorState() {
    const tbody = document.querySelector('#certificateTable tbody');
    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="text-center py-8">
          <div class="flex flex-col items-center">
            <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
            <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
            <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
              <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  // Filter and search functionality
  function initFilters() {
    const searchInput = document.getElementById('searchStudent');
    const filterClass = document.getElementById('filterClass');
    const filterAward = document.getElementById('filterAward');
    const filterTerm = document.getElementById('filterTerm');
    const filterYear = document.getElementById('filterYear');
    const btnClearFilter = document.getElementById('btnClearFilter');

    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          loadCertificates();
        }
      }, 500);
    });

    // โหลดตัวเลือกภาคเรียน/ปีการศึกษา
    loadAvailableTermsAndYears();

    filterClass.addEventListener('change', applyFilters);
    filterAward.addEventListener('change', applyFilters);
    filterTerm.addEventListener('change', applyFilters);
    filterYear.addEventListener('change', applyFilters);

    btnClearFilter.addEventListener('click', function() {
      searchInput.value = '';
      filterClass.value = '';
      filterAward.value = '';
      filterTerm.value = '';
      filterYear.value = '';
      loadCertificates();
    });
  }

  function loadAvailableTermsAndYears() {
    fetch('../controllers/CertificateController.php?action=availableTerms&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const terms = new Set();
          const years = new Set();
          result.data.forEach(item => {
            if (item.term) terms.add(item.term);
            if (item.year) years.add(item.year);
          });

          // เติม dropdown ภาคเรียน
          const filterTerm = document.getElementById('filterTerm');
          filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
          Array.from(terms).sort().forEach(term => {
            filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
          });

          // เติม dropdown ปีการศึกษา
          const filterYear = document.getElementById('filterYear');
          filterYear.innerHTML = '<option value="">ทุกปี</option>';
          Array.from(years).sort((a, b) => b - a).forEach(year => {
            filterYear.innerHTML += `<option value="${year}">${year}</option>`;
          });
        }
      });
  }

  function performSearch(searchTerm) {
    fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(teacherId)}`)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
          
          // Show search results info
          if (result.count === 0) {
            Swal.fire({
              title: 'ไม่พบผลลัพธ์',
              text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
              icon: 'info',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Search error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการค้นหา', 'error');
      });
  }

  // ปรับ applyFilters ให้ส่ง term/year ไป backend
  function applyFilters() {
    const filterClass = document.getElementById('filterClass').value;
    const filterAward = document.getElementById('filterAward').value;
    const filterTerm = document.getElementById('filterTerm').value;
    const filterYear = document.getElementById('filterYear').value;
    let url = `../controllers/CertificateController.php?action=search&teacherId=${encodeURIComponent(teacherId)}`;
    if (filterClass) url += `&classFilter=${encodeURIComponent(filterClass)}`;
    if (filterAward) url += `&awardFilter=${encodeURIComponent(filterAward)}`;
    if (filterTerm) url += `&termFilter=${encodeURIComponent(filterTerm)}`;
    if (filterYear) url += `&yearFilter=${encodeURIComponent(filterYear)}`;
    fetch(url)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Filter error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการกรองข้อมูล', 'error');
      });
  }

  // Enhanced statistics with additional info
  function loadStatistics() {
    fetch('../controllers/CertificateController.php?action=statistics&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          updateStatsDisplay(result.data);
          loadTopStudents();
          loadRecentCertificates();
        }
      })
      .catch(err => console.error('Error loading statistics:', err));
  }

  function loadTopStudents() {
    fetch('../controllers/CertificateController.php?action=topStudents&teacherId=' + encodeURIComponent(teacherId) + '&limit=5')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayTopStudents(result.data);
        }
      })
      .catch(err => console.error('Error loading top students:', err));
  }

  function loadRecentCertificates() {
    fetch('../controllers/CertificateController.php?action=recent&teacherId=' + encodeURIComponent(teacherId) + '&limit=3')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayRecentCertificates(result.data);
        }
      })
      .catch(err => console.error('Error loading recent certificates:', err));
  }

  function displayTopStudents(students) {
    // This could be displayed in a separate modal or section
    console.log('Top students:', students);
  }

  function displayRecentCertificates(certificates) {
    // This could be displayed in a separate section
    console.log('Recent certificates:', certificates);
  }

  // Enhanced error handling
  function showErrorState() {
    const tbody = document.querySelector('#certificateTable tbody');
    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="text-center py-8">
          <div class="flex flex-col items-center">
            <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
            <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
            <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
              <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  // Filter and search functionality
  function initFilters() {
    const searchInput = document.getElementById('searchStudent');
    const filterClass = document.getElementById('filterClass');
    const filterAward = document.getElementById('filterAward');
    const filterTerm = document.getElementById('filterTerm');
    const filterYear = document.getElementById('filterYear');
    const btnClearFilter = document.getElementById('btnClearFilter');

    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          loadCertificates();
        }
      }, 500);
    });

    // โหลดตัวเลือกภาคเรียน/ปีการศึกษา
    loadAvailableTermsAndYears();

    filterClass.addEventListener('change', applyFilters);
    filterAward.addEventListener('change', applyFilters);
    filterTerm.addEventListener('change', applyFilters);
    filterYear.addEventListener('change', applyFilters);

    btnClearFilter.addEventListener('click', function() {
      searchInput.value = '';
      filterClass.value = '';
      filterAward.value = '';
      filterTerm.value = '';
      filterYear.value = '';
      loadCertificates();
    });
  }

  function loadAvailableTermsAndYears() {
    fetch('../controllers/CertificateController.php?action=availableTerms&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const terms = new Set();
          const years = new Set();
          result.data.forEach(item => {
            if (item.term) terms.add(item.term);
            if (item.year) years.add(item.year);
          });

          // เติม dropdown ภาคเรียน
          const filterTerm = document.getElementById('filterTerm');
          filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
          Array.from(terms).sort().forEach(term => {
            filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
          });

          // เติม dropdown ปีการศึกษา
          const filterYear = document.getElementById('filterYear');
          filterYear.innerHTML = '<option value="">ทุกปี</option>';
          Array.from(years).sort((a, b) => b - a).forEach(year => {
            filterYear.innerHTML += `<option value="${year}">${year}</option>`;
          });
        }
      });
  }

  function performSearch(searchTerm) {
    fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(teacherId)}`)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
          
          // Show search results info
          if (result.count === 0) {
            Swal.fire({
              title: 'ไม่พบผลลัพธ์',
              text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
              icon: 'info',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Search error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการค้นหา', 'error');
      });
  }

  // ปรับ applyFilters ให้ส่ง term/year ไป backend
  function applyFilters() {
    const filterClass = document.getElementById('filterClass').value;
    const filterAward = document.getElementById('filterAward').value;
    const filterTerm = document.getElementById('filterTerm').value;
    const filterYear = document.getElementById('filterYear').value;
    let url = `../controllers/CertificateController.php?action=search&teacherId=${encodeURIComponent(teacherId)}`;
    if (filterClass) url += `&classFilter=${encodeURIComponent(filterClass)}`;
    if (filterAward) url += `&awardFilter=${encodeURIComponent(filterAward)}`;
    if (filterTerm) url += `&termFilter=${encodeURIComponent(filterTerm)}`;
    if (filterYear) url += `&yearFilter=${encodeURIComponent(filterYear)}`;
    fetch(url)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Filter error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการกรองข้อมูล', 'error');
      });
  }

  // Enhanced statistics with additional info
  function loadStatistics() {
    fetch('../controllers/CertificateController.php?action=statistics&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          updateStatsDisplay(result.data);
          loadTopStudents();
          loadRecentCertificates();
        }
      })
      .catch(err => console.error('Error loading statistics:', err));
  }

  function loadTopStudents() {
    fetch('../controllers/CertificateController.php?action=topStudents&teacherId=' + encodeURIComponent(teacherId) + '&limit=5')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayTopStudents(result.data);
        }
      })
      .catch(err => console.error('Error loading top students:', err));
  }

  function loadRecentCertificates() {
    fetch('../controllers/CertificateController.php?action=recent&teacherId=' + encodeURIComponent(teacherId) + '&limit=3')
      .then(res => res.json())
      .then(result => {
        if (result.success && result.data.length > 0) {
          displayRecentCertificates(result.data);
        }
      })
      .catch(err => console.error('Error loading recent certificates:', err));
  }

  function displayTopStudents(students) {
    // This could be displayed in a separate modal or section
    console.log('Top students:', students);
  }

  function displayRecentCertificates(certificates) {
    // This could be displayed in a separate section
    console.log('Recent certificates:', certificates);
  }

  // Enhanced error handling
  function showErrorState() {
    const tbody = document.querySelector('#certificateTable tbody');
    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="text-center py-8">
          <div class="flex flex-col items-center">
            <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
            <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
            <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
              <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  // Filter and search functionality
  function initFilters() {
    const searchInput = document.getElementById('searchStudent');
    const filterClass = document.getElementById('filterClass');
    const filterAward = document.getElementById('filterAward');
    const filterTerm = document.getElementById('filterTerm');
    const filterYear = document.getElementById('filterYear');
    const btnClearFilter = document.getElementById('btnClearFilter');

    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          loadCertificates();
        }
      }, 500);
    });

    // โหลดตัวเลือกภาคเรียน/ปีการศึกษา
    loadAvailableTermsAndYears();

    filterClass.addEventListener('change', applyFilters);
    filterAward.addEventListener('change', applyFilters);
    filterTerm.addEventListener('change', applyFilters);
    filterYear.addEventListener('change', applyFilters);

    btnClearFilter.addEventListener('click', function() {
      searchInput.value = '';
      filterClass.value = '';
      filterAward.value = '';
      filterTerm.value = '';
      filterYear.value = '';
      loadCertificates();
    });
  }

  function loadAvailableTermsAndYears() {
    fetch('../controllers/CertificateController.php?action=availableTerms&teacherId=' + encodeURIComponent(teacherId))
      .then(res => res.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const terms = new Set();
          const years = new Set();
          result.data.forEach(item => {
            if (item.term) terms.add(item.term);
            if (item.year) years.add(item.year);
          });

          // เติม dropdown ภาคเรียน
          const filterTerm = document.getElementById('filterTerm');
          filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
          Array.from(terms).sort().forEach(term => {
            filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
          });

          // เติม dropdown ปีการศึกษา
          const filterYear = document.getElementById('filterYear');
          filterYear.innerHTML = '<option value="">ทุกปี</option>';
          Array.from(years).sort((a, b) => b - a).forEach(year => {
            filterYear.innerHTML += `<option value="${year}">${year}</option>`;
          });
        }
      });
  }

  function performSearch(searchTerm) {
    fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(teacherId)}`)
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          certificatesData = result.data;
          renderCertificateTable(certificatesData);
          
          // Show search results info
          if (result.count === 0) {
            Swal.fire({
              title: 'ไม่พบผลลัพธ์',
              text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
              icon: 'info',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire('ข้อผิดพลาด', result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Search error:', err);
        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการค้นหา', 'error');
      });
  }
});


function showLoadingState() {
  const tbody = document.querySelector('#certificateTable tbody');
  tbody.innerHTML = `
    <tr class="loading-row">
      <td colspan="12" class="py-8 text-center">
        <div class="flex justify-center items-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-3 text-gray-600">กำลังโหลดข้อมูล...</span>
        </div>
      </td>
    </tr>
  `;
}

function hideLoadingState() {
  const loadingRow = document.querySelector('.loading-row');
  if (loadingRow) {
    loadingRow.remove();
  }
}

function showErrorState() {
  const tbody = document.querySelector('#certificateTable tbody');
  tbody.innerHTML = `
    <tr>
      <td colspan="12" class="text-center py-8">
        <div class="flex flex-col items-center">
          <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
          <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
          <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
          <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
          </button>
        </div>
      </td>
    </tr>
  `;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="js/cerificate.js"></script>
<?php require_once('script.php'); ?>
</body>
</html>
