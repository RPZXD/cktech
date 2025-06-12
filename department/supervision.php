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

require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

$dbUsers = new DatabaseUsers();
$pdo = $dbUsers->getPDO();

$DeptData = $dbUsers->getTeacherByUsername($_SESSION['username']);
$dept_name = $DeptData['Teach_name'] ?? '';
$subject_group = $DeptData['Teach_major'] ?? '';

require_once('header.php');
?>

<style>
/* Custom animations and styling */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.8s ease-out;
}

.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.btn-modern {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: all 0.3s ease;
  transform: translateY(0);
}

.btn-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.btn-success-modern {
  background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
  color: #065f46;
}

.radio-modern {
  position: relative;
  appearance: none;
  width: 20px;
  height: 20px;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  background-color: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.radio-modern:checked {
  border-color: #3b82f6;
  background-color: #3b82f6;
}

.radio-modern:checked::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: white;
}

.modal-backdrop {
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
}

.modal-content {
  animation: fadeInUp 0.5s ease-out;
}

.score-display {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 800;
  font-size: 2rem;
}
</style>

<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gradient-to-br from-blue-50 to-purple-50">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper bg-transparent">

    <div class="content-header animate-fadeInUp">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent flex items-center">
              👁️ การนิเทศการสอน - <?= htmlspecialchars($subject_group) ?>
            </h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid flex justify-center">
        <div class="w-full max-w-7xl">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 card-hover">
            <div class="mb-6">
              <h2 class="text-2xl font-bold mb-3 flex items-center gap-3 bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                👁️ การนิเทศการสอน - กลุ่มสาระ<?= htmlspecialchars($subject_group) ?>
              </h2>
              <p class="text-gray-600 text-lg">ดูและประเมินการนิเทศการสอนของครูในกลุ่มสาระ</p>
            </div>
            
            <div class="overflow-x-auto">
              <table class="min-w-full bg-white/90 backdrop-blur-sm border-0 rounded-2xl shadow-lg overflow-hidden">
                <thead class="bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                  <tr>
                    <th class="py-4 px-4 text-center font-semibold">📅 วันที่นิเทศ</th>
                    <th class="py-4 px-4 text-center font-semibold">👨‍🏫 ผู้รับการนิเทศ</th>
                    <th class="py-4 px-4 text-center font-semibold">📖 วิชา</th>
                    <th class="py-4 px-4 text-center font-semibold">🏫 ชั้น</th>
                    <th class="py-4 px-4 text-center font-semibold">🔢 ครั้งที่</th>
                    <th class="py-4 px-4 text-center font-semibold">📅 ภาคเรียน/ปี</th>
                    <th class="py-4 px-4 text-center font-semibold">📊 คะแนนครู</th>
                    <th class="py-4 px-4 text-center font-semibold">📊 คะแนนหัวหน้า</th>
                    <th class="py-4 px-4 text-center font-semibold">🏆 สถานะ</th>
                    <th class="py-4 px-4 text-center font-semibold">🔍 จัดการ</th>
                  </tr>
                </thead>
                <tbody id="supervisionTableBody">
                  <!-- Data will be loaded here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal สำหรับประเมินการนิเทศ -->
      <div id="modalSupervision" class="fixed inset-0 modal-backdrop flex items-center justify-center z-50 hidden">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-7xl p-8 relative overflow-y-auto max-h-screen border-0">
          <button id="closeModalSupervision" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-3xl transition-all duration-300 hover:rotate-90">&times;</button>
          <h2 id="modalSupervisionTitle" class="text-2xl font-bold mb-6 flex items-center gap-3 bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
            📋 ประเมินการนิเทศการสอน
          </h2>
          
          <form id="formSupervision" class="space-y-8">
            
            <!-- แสดงข้อมูลที่ครูกรอกไว้ -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-2xl border-l-4 border-blue-500 card-hover">
              <h3 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
                📋 ข้อมูลการนิเทศที่ครูบันทึกไว้
              </h3>
              <div id="teacherData" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Teacher data will be populated here -->
              </div>
            </div>

            <!-- แสดงคะแนนและเอกสารที่ครูประเมิน -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-2xl border-l-4 border-green-500 card-hover">
              <h3 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
                📊 การประเมินของครู และเอกสารที่เกี่ยวข้อง
              </h3>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div id="teacherEvaluation">
                  <!-- Teacher's evaluation scores will be shown here -->
                </div>
                <div id="teacherDocuments">
                  <!-- Teacher's uploaded documents will be shown here -->
                </div>
              </div>
            </div>

            <!-- การประเมินของหัวหน้ากลุ่มสาระ -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-2xl border-l-4 border-purple-500 card-hover">
              <h3 class="text-xl font-bold text-purple-700 mb-4 flex items-center gap-2">
                👥 การประเมินของหัวหน้ากลุ่มสาระ
              </h3>
              
              <!-- Department Head Evaluation Questions -->
              <div class="space-y-6">
                <h4 class="font-bold text-purple-600 mb-4 text-lg">แบบประเมินสมรรถนะการจัดการเรียนรู้ของผู้รับการนิเทศ (ประเมินโดยหัวหน้ากลุ่มสาระ)</h4>
                
                <!-- ด้านที่ 1: ความสามารถในการจัดทำแผนการจัดการเรียนรู้ -->
                <div class="mb-8">
                  <h5 class="font-bold text-purple-600 mb-4 text-lg">1. ด้านความสามารถในการจัดทำแผนการจัดการเรียนรู้</h5>
                  <div class="space-y-4">
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">การวางแผนการสอนที่มีประสิทธิภาพ</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_effective" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_effective" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_effective" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_effective" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_effective" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้ถูกต้อง เป็นขั้นตอน และครบองค์ประกอบ</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_correct" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_correct" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_correct" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_correct" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_correct" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้มีกิจกรรมที่ทำให้นักเรียนเกิดการเรียนรู้</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_activities" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_activities" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_activities" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_activities" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_activities" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้มีการจัดหาสื่อที่เหมาะสมกับการเรียนรู้ของนักเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_media" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_media" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_media" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_media" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_media" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้มีการวัดและประเมินผลผู้เรียนได้อย่างเหมาะสม</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_assessment" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_assessment" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_assessment" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_assessment" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_plan_assessment" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ด้านที่ 2: ความสามารถในการจัดการเรียนรู้ -->
                <div class="mb-6">
                  <h5 class="font-bold text-purple-600 mb-4 text-lg">2. ด้านความสามารถในการจัดการเรียนรู้</h5>
                  <div class="space-y-4">
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">ใช้เทคนิคต่าง ๆ ที่ทำให้นักเรียนทุกคนมีส่วนร่วมในชั้นเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_techniques" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_techniques" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_techniques" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_techniques" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_techniques" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">เลือกใช้สื่อ เทคโนโลยีและอุปกรณ์การสอนที่เหมาะสม</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_media" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_media" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_media" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_media" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_media" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีการประเมินนักเรียนระหว่างเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_assessment" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_assessment" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_assessment" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_assessment" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_assessment" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">อธิบายเนื้อหาบทเรียนได้อย่างชัดเจน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_explanation" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_explanation" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_explanation" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_explanation" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_explanation" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีความสามารถในการควบคุมชั้นเรียนเมื่อทำกิจกรรม</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_control" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_control" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_control" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_control" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_control" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีการจัดกิจกรรมการเรียนรู้ที่เน้นการพัฒนาการคิด ได้อภิปราย ซักถาม และแสดงความคิดเห็น</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_thinking" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_thinking" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_thinking" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_thinking" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_thinking" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีการปรับเนื้อหา กิจกรรมในขณะจัดการเรียนรู้เพื่อให้เหมาะสมตามสถานการณ์หรือให้ทันเวลาที่เหลือ</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_adaptation" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_adaptation" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_adaptation" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_adaptation" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_adaptation" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีกิจกรรมการเรียนการสอนที่เชื่อมโยงหรือบูรณาการกับชีวิตประจำวัน สอดแทรกคุณธรรม จริยธรรมระหว่างเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_integration" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_integration" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_integration" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_integration" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_integration" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">ใช้ภาษาพูดและภาษาเขียนได้ถูกต้อง เหมาะสม</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_language" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_language" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_language" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_language" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_teach_language" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ด้านที่ 3: ความสามารถในการประเมินผล -->
                <div class="mb-6">
                  <h5 class="font-bold text-purple-600 mb-4 text-lg">3. ด้านความสามารถในการประเมินผล</h5>
                  <div class="space-y-4">
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">วัดและประเมินผลด้วยวิธีการที่หลากหลาย</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_variety" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_variety" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_variety" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_variety" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_variety" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">วัดและประเมินผลสอดคล้องกับมาตรฐานการเรียนรู้ ตัวชี้วัด และจุดประสงค์การเรียนรู้</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_standards" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_standards" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_standards" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_standards" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_standards" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีเกณฑ์การวัดและประเมินผลที่ชัดเจน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_criteria" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_criteria" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_criteria" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_criteria" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_criteria" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">ให้ข้อมูลย้อนกลับแก่นักเรียนเพื่อการปรับปรุงหรือพัฒนา</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_feedback" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_feedback" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_feedback" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_feedback" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_feedback" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีผลงาน ชิ้นงาน ภาระงาน ซึ่งเป็นหลักฐานการเรียนรู้</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_evidence" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_evidence" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_evidence" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_evidence" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_eval_evidence" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ด้านที่ 4: ความสามารถในการจัดสภาพแวดล้อมในชั้นเรียน -->
                <div class="mb-6">
                  <h5 class="font-bold text-purple-600 mb-4 text-lg">4. ด้านความสามารถในการจัดสภาพแวดล้อมในชั้นเรียน</h5>
                  <div class="space-y-4">
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">จัดสภาพห้องเรียนได้อย่างเหมาะสม และเอื้อต่อการเรียนรู้ของนักเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_classroom" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_classroom" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_classroom" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_classroom" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_classroom" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">สร้างปฏิสัมพันธ์เชิงบวกในชั้นเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_interaction" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_interaction" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_interaction" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_interaction" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_interaction" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">จัดชั้นเรียนให้มีความปลอดภัย ไม่เสี่ยงต่อการเกิดอุบัติเหตุในระหว่างการจัดการเรียนการสอน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_safety" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_safety" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_safety" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_safety" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_safety" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีความสามารถในการควบคุมชั้นเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_management" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_management" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_management" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_management" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_management" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">ชี้แจงกฎกติกาหรือข้อตกลงในการเรียน</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_rules" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_rules" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_rules" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_rules" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_rules" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                      <div class="col-span-4 font-medium">มีการดูแลพฤติกรรมของนักเรียนในชั้นเรียนอย่างใกล้ชิด</div>
                      <div class="col-span-2 flex gap-3">
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_behavior" value="5" class="radio-modern"> 
                          <span class="font-semibold">5</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_behavior" value="4" class="radio-modern"> 
                          <span class="font-semibold">4</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_behavior" value="3" class="radio-modern"> 
                          <span class="font-semibold">3</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_behavior" value="2" class="radio-modern"> 
                          <span class="font-semibold">2</span>
                        </label>
                        <label class="flex items-center space-x-1">
                          <input type="radio" name="dept_env_behavior" value="1" class="radio-modern"> 
                          <span class="font-semibold">1</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- คะแนนของหัวหน้ากลุ่มสาระ -->
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-6 rounded-2xl border-l-4 border-yellow-500">
                  <h4 class="font-bold text-yellow-700 mb-4 text-lg flex items-center gap-2">
                    🏆 คะแนนจากหัวหน้ากลุ่มสาระ
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                      <label class="block font-semibold text-gray-700">คะแนนรวม</label>
                      <input type="number" id="deptScore" name="dept_score" readonly 
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-gray-100 text-center text-2xl font-bold text-purple-600" />
                    </div>
                    <div class="space-y-2">
                      <label class="block font-semibold text-gray-700">ระดับคุณภาพ</label>
                      <input type="text" id="deptQualityLevel" name="dept_quality_level" readonly 
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-gray-100 text-center text-lg font-bold text-green-600" />
                    </div>
                    <div class="flex items-end">
                      <button type="button" id="calculateDeptScore" 
                        class="btn-modern text-white px-6 py-3 rounded-xl w-full font-semibold shadow-lg">
                        🧮 คำนวณคะแนน
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ตอนที่ 3: บันทึกเพิ่มเติมของผู้นิเทศ -->
            <div class="bg-gradient-to-r from-orange-50 to-red-50 p-6 rounded-2xl border-l-4 border-orange-500 card-hover">
              <h3 class="text-xl font-bold text-orange-700 mb-4 flex items-center gap-2">
                📝 ตอนที่ 3 ผู้นิเทศบันทึกเพิ่มเติมการนิเทศการจัดการเรียนรู้
              </h3>
              
              <div class="space-y-6">
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">1. สิ่งที่พบจากการสังเกตการจัดการเรียนรู้ในชั้นเรียนของผู้รับการนิเทศ</label>
                  <textarea name="dept_observation_notes" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">2. การสะท้อนความคิดจากการจัดการเรียนรู้ในชั้นเรียนของผู้รับการนิเทศ</label>
                  <textarea name="dept_reflection_notes" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">3. ความประทับใจหรือจุดเด่นในการจัดการเรียนรู้ครั้งนี้</label>
                  <textarea name="dept_strengths" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">4. สิ่งที่ควรปรับปรุงหรือพัฒนา</label>
                  <textarea name="dept_improvements" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-2">
                    <label class="block font-semibold text-gray-700">ผู้รับการนิเทศ</label>
                    <input type="text" id="superviseeSignature" readonly
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-gray-100 transition-all duration-300" />
                  </div>
                  <div class="space-y-2">
                    <label class="block font-semibold text-gray-700">ผู้นิเทศ (หัวหน้ากลุ่มสาระ)</label>
                    <input type="text" name="dept_supervisor_signature" value="<?= htmlspecialchars($dept_name) ?>"
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80" />
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-4 pt-6">
              <button type="button" id="cancelSupervision" 
                class="px-8 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition-all duration-300 shadow-md">
                ❌ ยกเลิก
              </button>
              <button type="submit" 
                class="btn-success-modern px-8 py-3 rounded-xl font-semibold shadow-lg">
                💾 บันทึกการประเมิน
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>
    <?php require_once('../footer.php');?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Global variables
  let currentSupervisionId = null;
  let dataTable = null;
  const subjectGroup = '<?= htmlspecialchars($subject_group) ?>';

  // Initialize
  initializeDataTable();
  loadSupervisions();

  function initializeDataTable() {
    if ($('.min-w-full').length) {
      dataTable = $('.min-w-full').DataTable({
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between mb-4"<"mb-2 md:mb-0"l><"mb-2 md:mb-0"f>>rtip',
        initComplete: function() {
          $('.dataTables_filter input').addClass('border-2 border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500');
          $('.dataTables_length select').addClass('border-2 border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500');
        }
      });
    }
  }

  async function loadSupervisions() {
    try {
      const response = await fetch(`../controllers/SupervisionController.php?action=list&subject_group=${encodeURIComponent(subjectGroup)}`);
      const data = await response.json();
      
      if (Array.isArray(data)) {
        updateSupervisionTable(data);
      } else {
        console.error('Invalid data format:', data);
      }
    } catch (error) {
      console.error('Error loading supervisions:', error);
      Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลการนิเทศได้', 'error');
    }
  }

  function updateSupervisionTable(supervisions) {
    const tbody = document.getElementById('supervisionTableBody');
    if (!tbody) return;

    // Clear existing data
    if (dataTable) {
      dataTable.clear();
    }

    tbody.innerHTML = '';

    // Filter by teacher's actual subject group from teacher table
    const filteredSupervisions = supervisions.filter(s => s.teacher_subject_group === subjectGroup);

    if (filteredSupervisions.length === 0) {
      // Show message if no supervisions found
      const row = document.createElement('tr');
      row.innerHTML = `
        <td colspan="10" class="py-8 px-4 text-center text-gray-500">
          <div class="flex flex-col items-center gap-3">
            <div class="text-4xl">📋</div>
            <div class="text-lg font-medium">ไม่พบข้อมูลการนิเทศสำหรับกลุ่มสาระ ${subjectGroup}</div>
            <div class="text-sm">ครูในกลุ่มสาระนี้ยังไม่มีการบันทึกการนิเทศ</div>
          </div>
        </td>
      `;
      tbody.appendChild(row);
      
      if (dataTable) {
        dataTable.draw();
      }
      return;
    }

    filteredSupervisions.forEach(supervision => {
      const row = createSupervisionRow(supervision);
      tbody.appendChild(row);
      
      if (dataTable) {
        dataTable.row.add(row);
      }
    });

    if (dataTable) {
      dataTable.draw();
    }
  }

  function createSupervisionRow(supervision) {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300';
    
    const formattedDate = formatDate(supervision.supervision_date);
    const teacherScore = supervision.total_score || 0;
    const deptScore = supervision.dept_score || '-';
    const status = supervision.dept_score ? 
      '<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">ประเมินแล้ว</span>' :
      '<span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-semibold">รอประเมิน</span>';
    
    // Use teacher_full_name if available, fallback to teacher_name
    const displayName = supervision.teacher_full_name || supervision.teacher_name;
    
    row.innerHTML = `
      <td class="py-4 px-4 text-center border-b border-gray-100">${formattedDate}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100 font-medium">${displayName}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">${supervision.subject_name || '-'}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">${supervision.class_level || '-'}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">${supervision.supervision_round || '-'}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">${supervision.term || '-'}/${supervision.pee || '-'}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">
        <span class="score-display text-lg">${teacherScore}</span>
      </td>
      <td class="py-4 px-4 text-center border-b border-gray-100">
        <span class="score-display text-lg text-purple-600">${deptScore}</span>
      </td>
      <td class="py-4 px-4 text-center border-b border-gray-100">
        ${status}
      </td>
      <td class="py-4 px-4 text-center border-b border-gray-100">
        <div class="flex gap-2 justify-center">
          <button onclick="viewSupervision(${supervision.id})" class="btn bg-blue-500 text-white px-3 py-2 rounded-lg text-sm shadow-md hover:bg-blue-600 transition-colors">👁️ ดู</button>
          <button onclick="evaluateSupervision(${supervision.id})" class="btn bg-purple-500 text-white px-3 py-2 rounded-lg text-sm shadow-md hover:bg-purple-600 transition-colors">📊 ประเมิน</button>
        </div>
      </td>
    `;
    
    return row;
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'short', 
      day: 'numeric'
    });
  }

  // Global functions
  window.viewSupervision = async function(id) {
    try {
      const response = await fetch(`../controllers/SupervisionController.php?action=detail&id=${id}`);
      const supervision = await response.json();
      
      if (!supervision || !supervision.id) {
        throw new Error('ไม่พบข้อมูลการนิเทศ');
      }

      showSupervisionDetails(supervision);
    } catch (error) {
      console.error('Error viewing supervision:', error);
      Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลการนิเทศได้', 'error');
    }
  };

  window.evaluateSupervision = async function(id) {
    try {
      const response = await fetch(`../controllers/SupervisionController.php?action=detail&id=${id}`);
      const supervision = await response.json();
      
      if (!supervision || !supervision.id) {
        throw new Error('ไม่พบข้อมูลการนิเทศ');
      }

      openEvaluationModal(supervision);
    } catch (error) {
      console.error('Error loading supervision for evaluation:', error);
      Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลการประเมินได้', 'error');
    }
  };

  // Global function for printing supervision report
  window.printSupervision = async function(id) {
    try {
      const response = await fetch(`../controllers/SupervisionController.php?action=detail&id=${id}`);
      const supervision = await response.json();
      
      if (!supervision || !supervision.id) {
        throw new Error('ไม่พบข้อมูลการนิเทศ');
      }

      // Check if department evaluation exists
      if (!supervision.dept_score || supervision.dept_score <= 0) {
        Swal.fire('ไม่สามารถพิมพ์ได้', 'หัวหน้ากลุ่มสาระยังไม่ได้ประเมิน', 'warning');
        return;
      }

      // Redirect to print page
      window.open(`print_supervision.php?id=${id}`, '_blank');
    } catch (error) {
      console.error('Error loading supervision for print:', error);
      Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลสำหรับพิมพ์ได้', 'error');
    }
  };

  function openEvaluationModal(supervision) {
    currentSupervisionId = supervision.id;
    
    // Populate teacher data
    populateTeacherData(supervision);
    
    // Populate existing department evaluation if any
    populateDepartmentEvaluation(supervision);
    
    // Show modal
    document.getElementById('modalSupervision').classList.remove('hidden');
  }

  function populateTeacherData(supervision) {
    const teacherDataDiv = document.getElementById('teacherData');
    const displayName = supervision.teacher_full_name || supervision.teacher_name;
    const actualSubjectGroup = supervision.teacher_subject_group || supervision.subject_group;
    
    teacherDataDiv.innerHTML = `
      <div class="space-y-2">
        <div><strong>ชื่อ:</strong> ${displayName}</div>
        <div><strong>ตำแหน่ง:</strong> ${supervision.position || '-'}</div>
        <div><strong>วิทยฐานะ:</strong> ${supervision.academic_level || '-'}</div>
        <div><strong>กลุ่มสาระ:</strong> ${actualSubjectGroup || '-'}</div>
      </div>
      <div class="space-y-2">
        <div><strong>รายวิชา:</strong> ${supervision.subject_name || '-'}</div>
        <div><strong>รหัสวิชา:</strong> ${supervision.subject_code || '-'}</div>
        <div><strong>ชั้น:</strong> ${supervision.class_level || '-'}</div>
        <div><strong>วันที่นิเทศ:</strong> ${formatDate(supervision.supervision_date)}</div>
      </div>
    `;

    // Show detailed teacher evaluation with categories
    const teacherEvalDiv = document.getElementById('teacherEvaluation');
    teacherEvalDiv.innerHTML = `
      <h4 class="font-bold text-green-600 mb-2">คะแนนการประเมินของครู</h4>
      
      <!-- Overall Score -->
      <div class="text-center p-4 bg-white rounded-lg border mb-4">
        <div class="text-3xl font-bold text-blue-600">${supervision.total_score}</div>
        <div class="text-lg font-semibold text-green-600">${supervision.quality_level}</div>
      </div>
      
      <!-- Detailed Scores by Category -->
      <div class="space-y-3 text-sm">
        <!-- Category 1: Planning -->
        <div class="bg-blue-50 p-3 rounded-lg">
          <div class="font-semibold text-blue-700 mb-2">1. ความสามารถในการจัดทำแผนการจัดการเรียนรู้</div>
          <ul class="space-y-1 text-gray-700">
            <li>• การวางแผนการสอนที่มีประสิทธิภาพ: <span class="font-semibold">${supervision.plan_effective || 0}</span> คะแนน</li>
            <li>• แผนการจัดการเรียนรู้ถูกต้อง เป็นขั้นตอน: <span class="font-semibold">${supervision.plan_correct || 0}</span> คะแนน</li>
            <li>• มีกิจกรรมที่ทำให้นักเรียนเกิดการเรียนรู้: <span class="font-semibold">${supervision.plan_activities || 0}</span> คะแนน</li>
            <li>• การจัดหาสื่อที่เหมาะสม: <span class="font-semibold">${supervision.plan_media || 0}</span> คะแนน</li>
            <li>• การวัดและประเมินผลผู้เรียน: <span class="font-semibold">${supervision.plan_assessment || 0}</span> คะแนน</li>
          </ul>
          <div class="mt-2 text-right text-blue-600 font-semibold">
            รวม: ${(parseInt(supervision.plan_effective || 0) + parseInt(supervision.plan_correct || 0) + parseInt(supervision.plan_activities || 0) + parseInt(supervision.plan_media || 0) + parseInt(supervision.plan_assessment || 0))} คะแนน
          </div>
        </div>
        
        <!-- Category 2: Teaching -->
        <div class="bg-green-50 p-3 rounded-lg">
          <div class="font-semibold text-green-700 mb-2">2. ความสามารถในการจัดการเรียนรู้</div>
          <ul class="space-y-1 text-gray-700">
            <li>• ใช้เทคนิคที่ทำให้นักเรียนมีส่วนร่วม: <span class="font-semibold">${supervision.teach_techniques || 0}</span> คะแนน</li>
            <li>• เลือกใช้สื่อและเทคโนโลยี: <span class="font-semibold">${supervision.teach_media || 0}</span> คะแนน</li>
            <li>• มีการประเมินนักเรียนระหว่างเรียน: <span class="font-semibold">${supervision.teach_assessment || 0}</span> คะแนน</li>
            <li>• อธิบายเนื้อหาได้ชัดเจน: <span class="font-semibold">${supervision.teach_explanation || 0}</span> คะแนน</li>
            <li>• ความสามารถในการควบคุมชั้นเรียน: <span class="font-semibold">${supervision.teach_control || 0}</span> คะแนน</li>
            <li>• จัดกิจกรรมพัฒนาการคิด: <span class="font-semibold">${supervision.teach_thinking || 0}</span> คะแนน</li>
            <li>• ปรับเนื้อหาตามสถานการณ์: <span class="font-semibold">${supervision.teach_adaptation || 0}</span> คะแนน</li>
            <li>• บูรณาการกับชีวิตประจำวัน: <span class="font-semibold">${supervision.teach_integration || 0}</span> คะแนน</li>
            <li>• ใช้ภาษาได้ถูกต้องเหมาะสม: <span class="font-semibold">${supervision.teach_language || 0}</span> คะแนน</li>
          </ul>
          <div class="mt-2 text-right text-green-600 font-semibold">
            รวม: ${(parseInt(supervision.teach_techniques || 0) + parseInt(supervision.teach_media || 0) + parseInt(supervision.teach_assessment || 0) + parseInt(supervision.teach_explanation || 0) + parseInt(supervision.teach_control || 0) + parseInt(supervision.teach_thinking || 0) + parseInt(supervision.teach_adaptation || 0) + parseInt(supervision.teach_integration || 0) + parseInt(supervision.teach_language || 0))} คะแนน
          </div>
        </div>
        
        <!-- Category 3: Evaluation -->
        <div class="bg-yellow-50 p-3 rounded-lg">
          <div class="font-semibold text-yellow-700 mb-2">3. ความสามารถในการประเมินผล</div>
          <ul class="space-y-1 text-gray-700">
            <li>• วัดและประเมินด้วยวิธีหลากหลาย: <span class="font-semibold">${supervision.eval_variety || 0}</span> คะแนน</li>
            <li>• สอดคล้องกับมาตรฐานการเรียนรู้: <span class="font-semibold">${supervision.eval_standards || 0}</span> คะแนน</li>
            <li>• มีเกณฑ์การประเมินที่ชัดเจน: <span class="font-semibold">${supervision.eval_criteria || 0}</span> คะแนน</li>
            <li>• ให้ข้อมูลย้อนกลับแก่นักเรียน: <span class="font-semibold">${supervision.eval_feedback || 0}</span> คะแนน</li>
            <li>• มีผลงานเป็นหลักฐานการเรียนรู้: <span class="font-semibold">${supervision.eval_evidence || 0}</span> คะแนน</li>
          </ul>
          <div class="mt-2 text-right text-yellow-600 font-semibold">
            รวม: ${(parseInt(supervision.eval_variety || 0) + parseInt(supervision.eval_standards || 0) + parseInt(supervision.eval_criteria || 0) + parseInt(supervision.eval_feedback || 0) + parseInt(supervision.eval_evidence || 0))} คะแนน
          </div>
        </div>
        
        <!-- Category 4: Environment -->
        <div class="bg-purple-50 p-3 rounded-lg">
          <div class="font-semibold text-purple-700 mb-2">4. ความสามารถในการจัดสภาพแวดล้อมในชั้นเรียน</div>
          <ul class="space-y-1 text-gray-700">
            <li>• จัดสภาพห้องเรียนเหมาะสม: <span class="font-semibold">${supervision.env_classroom || 0}</span> คะแนน</li>
            <li>• สร้างปฏิสัมพันธ์เชิงบวก: <span class="font-semibold">${supervision.env_interaction || 0}</span> คะแนน</li>
            <li>• จัดห้องเรียนให้ปลอดภัย: <span class="font-semibold">${supervision.env_safety || 0}</span> คะแนน</li>
            <li>• ความสามารถควบคุมชั้นเรียน: <span class="font-semibold">${supervision.env_management || 0}</span> คะแนน</li>
            <li>• ชี้แจงกฎกติกาในการเรียน: <span class="font-semibold">${supervision.env_rules || 0}</span> คะแนน</li>
            <li>• ดูแลพฤติกรรมนักเรียน: <span class="font-semibold">${supervision.env_behavior || 0}</span> คะแนน</li>
          </ul>
          <div class="mt-2 text-right text-purple-600 font-semibold">
            รวม: ${(parseInt(supervision.env_classroom || 0) + parseInt(supervision.env_interaction || 0) + parseInt(supervision.env_safety || 0) + parseInt(supervision.env_management || 0) + parseInt(supervision.env_rules || 0) + parseInt(supervision.env_behavior || 0))} คะแนน
          </div>
        </div>
      </div>
    `;

    // Show teacher documents
    const teacherDocsDiv = document.getElementById('teacherDocuments');
    let docsHtml = '<h4 class="font-bold text-green-600 mb-2">เอกสารที่ครูอัพโหลด</h4>';
    
    if (supervision.lesson_plan) {
      docsHtml += `<div class="mb-2"><a href="../${supervision.lesson_plan}" target="_blank" class="text-blue-600 hover:underline">📄 แผนการจัดการเรียนรู้ + ใบความรู้ / ใบงาน / ใบกิจกรรม</a></div>`;
    }
    
    if (supervision.supervisor_photos) {
      docsHtml += '<div class="mb-2"><strong>ภาพผู้นิเทศและผู้รับการนิเทศ:</strong></div>';
      const photos = supervision.supervisor_photos.split(',');
      photos.forEach(photo => {
        if (photo.trim()) {
          docsHtml += `<img src="../${photo.trim()}" class="w-150 h-150 object-cover rounded mr-2 mb-2 inline-block cursor-pointer" onclick="showImageModal('../${photo.trim()}')" />`;
        }
      });
    }
    
    if (supervision.classroom_photos) {
      docsHtml += '<div class="mb-2"><strong>ภาพบรรยากาศการเรียน:</strong></div>';
      const photos = supervision.classroom_photos.split(',');
      photos.forEach(photo => {
        if (photo.trim()) {
          docsHtml += `<img src="../${photo.trim()}" class="w-150 h-150 object-cover rounded mr-2 mb-2 inline-block cursor-pointer" onclick="showImageModal('../${photo.trim()}')" />`;
        }
      });
    }
    
    teacherDocsDiv.innerHTML = docsHtml;

    // Set supervisee signature
    document.getElementById('superviseeSignature').value = displayName;
  }

  function populateDepartmentEvaluation(supervision) {
    // Populate existing department scores for all 4 categories
    const deptFields = [
      // Planning
      'dept_plan_effective', 'dept_plan_correct', 'dept_plan_activities', 'dept_plan_media', 'dept_plan_assessment',
      // Teaching  
      'dept_teach_techniques', 'dept_teach_media', 'dept_teach_assessment', 'dept_teach_explanation', 'dept_teach_control',
      'dept_teach_thinking', 'dept_teach_adaptation', 'dept_teach_integration', 'dept_teach_language',
      // Evaluation
      'dept_eval_variety', 'dept_eval_standards', 'dept_eval_criteria', 'dept_eval_feedback', 'dept_eval_evidence',
      // Environment
      'dept_env_classroom', 'dept_env_interaction', 'dept_env_safety', 'dept_env_management', 'dept_env_rules', 'dept_env_behavior'
    ];

    deptFields.forEach(field => {
      if (supervision[field]) {
        const radio = document.querySelector(`input[name="${field}"][value="${supervision[field]}"]`);
        if (radio) radio.checked = true;
      }
    });

    // Populate text areas
    document.querySelector('textarea[name="dept_observation_notes"]').value = supervision.dept_observation_notes || '';
    document.querySelector('textarea[name="dept_reflection_notes"]').value = supervision.dept_reflection_notes || '';
    document.querySelector('textarea[name="dept_strengths"]').value = supervision.dept_strengths || '';
    document.querySelector('textarea[name="dept_improvements"]').value = supervision.dept_improvements || '';

    // Set scores
    document.getElementById('deptScore').value = supervision.dept_score || '';
    document.getElementById('deptQualityLevel').value = supervision.dept_quality_level || '';
  }

  // Calculate department score
  document.getElementById('calculateDeptScore').addEventListener('click', function() {
    const deptFields = [
      // Planning (5 items)
      'dept_plan_effective', 'dept_plan_correct', 'dept_plan_activities', 'dept_plan_media', 'dept_plan_assessment',
      // Teaching (9 items)  
      'dept_teach_techniques', 'dept_teach_media', 'dept_teach_assessment', 'dept_teach_explanation', 'dept_teach_control',
      'dept_teach_thinking', 'dept_teach_adaptation', 'dept_teach_integration', 'dept_teach_language',
      // Evaluation (5 items)
      'dept_eval_variety', 'dept_eval_standards', 'dept_eval_criteria', 'dept_eval_feedback', 'dept_eval_evidence',
      // Environment (6 items)
      'dept_env_classroom', 'dept_env_interaction', 'dept_env_safety', 'dept_env_management', 'dept_env_rules', 'dept_env_behavior'
    ];

    let totalScore = 0;
    let filledGroups = 0;

    deptFields.forEach(fieldName => {
      const checkedRadio = document.querySelector(`input[name="${fieldName}"]:checked`);
      if (checkedRadio) {
        totalScore += parseInt(checkedRadio.value);
        filledGroups++;
      }
    });

    if (filledGroups === 0) {
      Swal.fire({
        title: 'ข้อผิดพลาด',
        text: 'กรุณาให้คะแนนอย่างน้อย 1 รายการ',
        icon: 'warning',
        confirmButtonColor: '#f59e0b'
      });
      return;
    }

    // Determine quality level (same scale as teacher evaluation)
    let qualityLevel = '';
    if (totalScore >= 98) {
      qualityLevel = 'ดีเยี่ยม';
    } else if (totalScore >= 74) {
      qualityLevel = 'ดีมาก';
    } else if (totalScore >= 50) {
      qualityLevel = 'ดี';
    } else if (totalScore >= 26) {
      qualityLevel = 'พอใช้';
    } else {
      qualityLevel = 'ควรปรับปรุง';
    }

    document.getElementById('deptScore').value = totalScore;
    document.getElementById('deptQualityLevel').value = qualityLevel;
  });

  // Form submission
  document.getElementById('formSupervision').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Show loading
    Swal.fire({
      title: '💾 กำลังบันทึกการประเมิน...',
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      const formData = new FormData(this);
      formData.append('id', currentSupervisionId);
      formData.append('evaluator_type', 'department');

      const response = await fetch('../controllers/SupervisionController.php?action=department_evaluate', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      
      if (data.success) {
        Swal.close();
        Swal.fire({
          title: '🎉 สำเร็จ!',
          text: 'บันทึกการประเมินเรียบร้อยแล้ว',
          icon: 'success',
          confirmButtonText: '👍 เยี่ยม',
          confirmButtonColor: '#059669'
        });
        document.getElementById('modalSupervision').classList.add('hidden');
        loadSupervisions(); // Reload the table
      } else {
        throw new Error(data.message || 'เกิดข้อผิดพลาด');
      }
    } catch (error) {
      console.error('Error submitting evaluation:', error);
      Swal.close();
      Swal.fire('ข้อผิดพลาด', error.message || 'ไม่สามารถบันทึกการประเมินได้', 'error');
    }
  });

  // Close modal
  document.getElementById('closeModalSupervision').addEventListener('click', function() {
    document.getElementById('modalSupervision').classList.add('hidden');
  });

  document.getElementById('cancelSupervision').addEventListener('click', function() {
    document.getElementById('modalSupervision').classList.add('hidden');
  });

  function showSupervisionDetails(supervision) {
    const displayName = supervision.teacher_full_name || supervision.teacher_name;
    const actualSubjectGroup = supervision.teacher_subject_group || supervision.subject_group;
    
    let html = `
      <div class="text-left space-y-4">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-lg">
          <h4 class="font-bold text-blue-700 mb-2">ข้อมูลผู้รับการนิเทศ</h4>
          <p><strong>ชื่อ:</strong> ${displayName}</p>
          <p><strong>กลุ่มสาระ:</strong> ${actualSubjectGroup}</p>
          <p><strong>วิชา:</strong> ${supervision.subject_name || '-'}</p>
          <p><strong>ชั้น:</strong> ${supervision.class_level || '-'}</p>
          <p><strong>ครั้งที่:</strong> ${supervision.supervision_round || '-'}</p>
          <p><strong>วันที่นิเทศ:</strong> ${formatDate(supervision.supervision_date)}</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg">
            <h4 class="font-bold text-green-700 mb-2">การประเมินของครู</h4>
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">${supervision.total_score}</div>
              <div class="text-lg font-semibold text-green-600">${supervision.quality_level}</div>
            </div>
          </div>
          
          <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 rounded-lg">
            <h4 class="font-bold text-purple-700 mb-2">การประเมินของหัวหน้ากลุ่มสาระ</h4>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">${supervision.dept_score || '-'}</div>
              <div class="text-lg font-semibold text-purple-600">${supervision.dept_quality_level || 'ยังไม่ประเมิน'}</div>
            </div>
          </div>
        </div>
      </div>
    `;

    Swal.fire({
      title: '📋 รายละเอียดการนิเทศ',
      html: html,
      width: '800px',
      confirmButtonText: '✅ ปิด',
      confirmButtonColor: '#059669'
    });
  }

  // Add function to show image modal
  window.showImageModal = function(imageSrc) {
    Swal.fire({
      imageUrl: imageSrc,
      imageWidth: 600,
      imageHeight: 400,
      imageAlt: 'รูปภาพ',
      showConfirmButton: false,
      showCloseButton: true,
      customClass: {
        image: 'rounded-lg'
      }
    });
  };
});

</script>

<?php require_once('script.php');?>
</body>
</html>