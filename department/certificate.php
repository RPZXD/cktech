<?php 
session_start();
// เช็ค session และ role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'หัวหน้ากลุ่มสาระ') {
    header('Location: ../login.php');
    exit;
}
// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];

$department = $_SESSION['user']['Teach_major'];
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

.teacher-filter {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px;
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.teacher-filter:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.teacher-filter.active {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}

.certificate-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border-left: 4px solid #3b82f6;
}

.certificate-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
}

/* Modal overlay styles to ensure it appears above everything */
#modalCertificateDetail {
  z-index: 9999 !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
}

#modalCertificateDetail .bg-white {
  position: relative;
  z-index: 10000 !important;
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
              🏆 รายงานเกียรติบัตรกลุ่มสาระ 
              <span class="ml-3 text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full animate-pulse">
                ภาพรวมรางวัล
              </span>
            </h1>
          </div>
          <div class="col-sm-6">
            <div class="float-right">
              <button id="btnExportDepartment" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                📊 ส่งออกรายงาน
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        
        <!-- Department Statistics Cards -->
        <div class="row mb-4">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-to-r from-blue-400 to-blue-600 text-white rounded-lg shadow-lg">
              <div class="inner p-4">
                <h3 id="deptTotalCerts" class="text-2xl font-bold">0</h3>
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
                <h3 id="deptTotalTeachers" class="text-2xl font-bold">0</h3>
                <p class="text-green-100">ครูในกลุ่มสาระ</p>
              </div>
              <div class="icon">
                <i class="fas fa-users text-green-200"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-to-r from-yellow-400 to-yellow-600 text-white rounded-lg shadow-lg">
              <div class="inner p-4">
                <h3 id="deptTopTeacher" class="text-lg font-bold">-</h3>
                <p class="text-yellow-100">ครูดีเด่น</p>
              </div>
              <div class="icon">
                <i class="fas fa-star text-yellow-200"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-to-r from-red-400 to-red-600 text-white rounded-lg shadow-lg">
              <div class="inner p-4">
                <h3 id="deptThisMonth" class="text-2xl font-bold">0</h3>
                <p class="text-red-100">เดือนนี้</p>
              </div>
              <div class="icon">
                <i class="fas fa-calendar text-red-200"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters and Controls -->
        <div class="w-full">
          <div class="bg-white rounded-xl shadow-xl p-6 backdrop-blur-sm bg-opacity-95">
            
            <!-- Filter Controls -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ครูในกลุ่มสาระ</label>
                  <select id="filterTeacher" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ครูทั้งหมด</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ประเภทรางวัล</label>
                  <select id="filterAwardType" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกประเภท</option>
                    <option value="รางวัลชนะเลิศ">รางวัลชนะเลิศ</option>
                    <option value="รางวัลรองชนะเลิศอันดับ 1">รางวัลรองชนะเลิศอันดับ 1</option>
                    <option value="รางวัลรองชนะเลิศอันดับ 2">รางวัลรองชนะเลิศอันดับ 2</option>
                    <option value="รางวัลชมเชย">รางวัลชมเชย</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ภาคเรียน</label>
                  <select id="filterDeptTerm" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกภาคเรียน</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ปีการศึกษา</label>
                  <select id="filterDeptYear" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกปี</option>
                  </select>
                </div>
                <div class="flex items-end">
                  <button id="btnClearDeptFilter" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-eraser mr-2"></i>ล้างตัวกรอง
                  </button>
                </div>
              </div>
            </div>

            <!-- View Toggle -->
            <div class="mb-6">
              <div class="flex gap-3">
                <button id="btnTableView" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition-all duration-300 active">
                  <i class="fas fa-table mr-2"></i>ตารางรายละเอียด
                </button>
                <button id="btnSummaryView" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg shadow transition-all duration-300">
                  <i class="fas fa-chart-bar mr-2"></i>สรุปแยกครู
                </button>
              </div>
            </div>

            <!-- Table View -->
            <div id="tableView" class="view-section">
              <div class="overflow-x-auto rounded-lg shadow-lg">
                <table class="min-w-full bg-white border border-gray-200" id="departmentCertificateTable">
                  <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                      <th class="py-4 px-4 border-b text-center font-semibold">
                        <i class="fas fa-user-tie mr-2"></i>ชื่อครู
                      </th>
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
                        <i class="fas fa-trophy mr-2"></i>ประเภทรางวัล
                      </th>
                      <th class="py-4 px-4 border-b text-center font-semibold">
                        <i class="fas fa-calendar mr-2"></i>วันที่ได้รับ
                      </th>
                      <th class="py-4 px-4 border-b text-center font-semibold">
                        <i class="fas fa-graduation-cap mr-2"></i>ภาค/ปี
                      </th>
                      <th class="py-4 px-4 border-b text-center font-semibold">
                        <i class="fas fa-eye mr-2"></i>ดูรายละเอียด
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Loading skeleton -->
                    <tr class="loading-row">
                      <td colspan="9" class="py-8 text-center">
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

            <!-- Summary View -->
            <div id="summaryView" class="view-section hidden">
              <div id="teacherSummaryContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Teacher summary cards will be populated here -->
              </div>
            </div>
          </div>
        </div>
      </div>      <!-- Certificate Detail Modal -->
      <div id="modalCertificateDetail" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden backdrop-blur-sm" style="z-index: 9999;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl p-8 relative overflow-y-auto animate-modal-in" style="max-height: 90vh; margin: 20px;">
          <button id="closeCertificateDetail" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-3xl transition-colors hover:rotate-90 transform duration-300" style="z-index: 10000;">&times;</button>
          
          <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 text-blue-800">
            <i class="fas fa-certificate text-yellow-500"></i>
            รายละเอียดเกียรติบัตร
          </h2>
          
          <div id="certificateDetailContent" class="space-y-4">
            <!-- Certificate details will be populated here -->
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php require_once('../footer.php');?>
</div>

<!-- Load JavaScript Modules -->
<script>
  // Set global department for JavaScript modules
  window.departmentName = <?php echo json_encode($department); ?>;
  window.userRole = 'department';
</script>

<!-- Load Department Certificate Management JavaScript -->
<script src="js/department-certificate.js"></script>

<!-- External Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<?php require_once('script.php'); ?>
</body>
</html>
