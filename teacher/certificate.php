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

/* Modal specific styles */
#modalAddCertificate {
  z-index: 9999 !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
}

#modalAddCertificate .bg-white {
  max-height: 90vh !important;
  overflow-y: auto !important;
  margin: 2rem !important;
  z-index: 10000 !important;
  position: relative !important;
}

/* ป้องกันการ scroll ของ body เมื่อ modal เปิด */
body.modal-open {
  overflow: hidden !important;
  padding-right: 17px !important; /* ป้องกันการกระต่าย layout */
}

/* ซ่อน scrollbar ของ modal content */
#modalAddCertificate .bg-white::-webkit-scrollbar {
  width: 8px;
}

#modalAddCertificate .bg-white::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

#modalAddCertificate .bg-white::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

#modalAddCertificate .bg-white::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
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
      </div>      <!-- Enhanced Modal -->
      <div id="modalAddCertificate" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] hidden backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-8 relative overflow-y-auto max-h-[90vh] animate-modal-in" onclick="event.stopPropagation();">
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

<!-- Load JavaScript Modules -->
<script>
  // Set global teacher ID for JavaScript modules
  window.teacherId = <?php echo isset($_SESSION['user']['Teach_id']) ? json_encode($_SESSION['user']['Teach_id']) : 'null'; ?>;

  
</script>

<!-- Load Certificate Management JavaScript Modules -->
<script src="js/certificate/certificate-main.js"></script>
<script src="js/certificate/certificate-form.js"></script>
<script src="js/certificate/certificate-table.js"></script>
<script src="js/certificate/certificate-filter.js"></script>
<script src="js/certificate/certificate-stats.js"></script>
<script src="js/certificate/certificate-export.js"></script>

<!-- External Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<?php require_once('script.php'); ?>
</body>
</html>
