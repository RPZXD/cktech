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

require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

$dbUsers = new DatabaseUsers();
$pdo = $dbUsers->getPDO();

$TeacherData = $dbUsers->getTeacherByUsername($_SESSION['username']);

$teacher_name = $TeacherData['Teach_name'] ?? '';
$teacher_degree = $TeacherData['Teach_HiDegree'] ?? '';
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

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translateY(0);
  }
  40%, 43% {
    transform: translateY(-10px);
  }
  70% {
    transform: translateY(-5px);
  }
  90% {
    transform: translateY(-2px);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.8s ease-out;
}

.animate-slideInRight {
  animation: slideInRight 0.6s ease-out;
}

.animate-pulse-slow {
  animation: pulse 2s infinite;
}

.animate-bounce-slow {
  animation: bounce 2s infinite;
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

.btn-warning-modern {
  background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
  color: #92400e;
}

.btn-danger-modern {
  background: linear-gradient(135deg, #ff9a8b 0%, #a8e6cf 100%);
  color: #7f1d1d;
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

.file-preview {
  position: relative;
  overflow: hidden;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.file-preview:hover {
  transform: scale(1.05);
}

.file-preview img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.file-remove-btn {
  position: absolute;
  top: 5px;
  right: 5px;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.2s ease;
}

.file-remove-btn:hover {
  background: rgba(239, 68, 68, 1);
  transform: scale(1.1);
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

.quality-badge {
  background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
  padding: 8px 16px;
  border-radius: 25px;
  color: #065f46;
  font-weight: 600;
  display: inline-block;
  animation: bounce 2s infinite;
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
            <h1 class="m-0 text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent flex items-center animate-bounce-slow">
              👁️ การนิเทศการสอน 
            </h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content animate-slideInRight">
      <div class="container-fluid flex justify-center">
        <div class="w-full max-w-6xl">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 card-hover">
            <div class="mb-6 flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold mb-3 flex items-center gap-3 bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                  👁️ การนิเทศการสอน
                </h2>
                <p class="text-gray-600 text-lg">บันทึกการนิเทศการสอนของครูในแต่ละภาคเรียน</p>
              </div>
              <button id="btnAddSupervision" class="btn-modern text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 font-semibold animate-pulse-slow">
                ➕ เพิ่มการนิเทศ
              </button>
            </div>
            
            <div class="overflow-x-auto">
              <table class="min-w-full bg-white/90 backdrop-blur-sm border-0 rounded-2xl shadow-lg overflow-hidden">
                <thead class="bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                  <tr>
                    <th class="py-4 px-4 text-center font-semibold">📅 วันที่นิเทศ</th>
                    <th class="py-4 px-4 text-center font-semibold">👨‍🏫 ผู้รับการนิเทศ</th>
                    <th class="py-4 px-4 text-center font-semibold">📖 วิชา</th>
                    <th class="py-4 px-4 text-center font-semibold">🏫 ชั้น</th>
                    <th class="py-4 px-4 text-center font-semibold">📊 คะแนน</th>
                    <th class="py-4 px-4 text-center font-semibold">🏆 ระดับคุณภาพ</th>
                    <th class="py-4 px-4 text-center font-semibold">🔍 จัดการ</th>
                  </tr>
                </thead>
                <tbody id="supervisionTableBody">
                  <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300">
                    <td class="py-4 px-4 text-center border-b border-gray-100">15 มิ.ย. 2568</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100 font-medium">นายสมชาย ใจดี</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">คณิตศาสตร์</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">ม.1/1</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">
                      <span class="score-display">95</span>
                    </td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">
                      <span class="quality-badge">ดีมาก</span>
                    </td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">
                      <div class="flex gap-2 justify-center">
                        <button class="btn-modern text-white px-3 py-2 rounded-lg text-sm shadow-md">👁️ ดู</button>
                        <button class="btn-warning-modern px-3 py-2 rounded-lg text-sm shadow-md">✏️ แก้ไข</button>
                        <button class="btn-danger-modern px-3 py-2 rounded-lg text-sm shadow-md">🗑️ ลบ</button>
                      </div>
                    </td>
                  </tr>
                  <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300">
                    <td class="py-4 px-4 text-center border-b border-gray-100">10 มิ.ย. 2568</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100 font-medium">นางสาวสมหญิง ขยัน</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">ภาษาไทย</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">ม.2/2</td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">
                      <span class="score-display">105</span>
                    </td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">
                      <span class="quality-badge">ดีเยี่ยม</span>
                    </td>
                    <td class="py-4 px-4 text-center border-b border-gray-100">
                      <div class="flex gap-2 justify-center">
                        <button class="btn-modern text-white px-3 py-2 rounded-lg text-sm shadow-md">👁️ ดู</button>
                        <button class="btn-warning-modern px-3 py-2 rounded-lg text-sm shadow-md">✏️ แก้ไข</button>
                        <button class="btn-danger-modern px-3 py-2 rounded-lg text-sm shadow-md">🗑️ ลบ</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal สำหรับเพิ่ม/แก้ไขการนิเทศ -->
      <div id="modalSupervision" class="fixed inset-0 modal-backdrop flex items-center justify-center z-50 hidden">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-6xl p-8 relative overflow-y-auto max-h-screen border-0">
          <button id="closeModalSupervision" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-3xl transition-all duration-300 hover:rotate-90">&times;</button>
          <h2 id="modalSupervisionTitle" class="text-2xl font-bold mb-6 flex items-center gap-3 bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
            ➕ บันทึกการนิเทศการสอน
          </h2>
          
          <form id="formSupervision" class="space-y-8" enctype="multipart/form-data">
            
            <!-- ตอนที่ 1: ข้อมูลทั่วไปของผู้รับการนิเทศ -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-2xl border-l-4 border-blue-500 card-hover">
              <h3 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
                📋 ตอนที่ 1 ข้อมูลทั่วไปของผู้รับการนิเทศ
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">ชื่อผู้รับการนิเทศ <span class="text-red-500">*</span></label>
                  <input type="text" name="teacher_name" value="<?=htmlspecialchars($teacher_name)?>" required 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">ตำแหน่ง</label>
                  <input type="text" name="position" placeholder="เช่น ครูชำนาญการ" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">วิทยฐานะ</label>
                  <input type="text" name="academic_level" placeholder="เช่น ชำนาญการ" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">กลุ่มสาระการเรียนรู้</label>
                  <select name="subject_group" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80">
                    <option value="">-- เลือกกลุ่มสาระการเรียนรู้ --</option>
                    <option value="ภาษาไทย">ภาษาไทย</option>
                    <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                    <option value="วิทยาศาสตร์และเทคโนโลยี">วิทยาศาสตร์และเทคโนโลยี</option>
                    <option value="สังคมศึกษา ศาสนา และวัฒนธรรม">สังคมศึกษา ศาสนา และวัฒนธรรม</option>
                    <option value="สุขศึกษาและพลศึกษา">สุขศึกษาและพลศึกษา</option>
                    <option value="ศิลปะ">ศิลปะ</option>
                    <option value="การงานอาชีพ">การงานอาชีพ</option>
                    <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                  </select>
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">รายวิชาที่สอน</label>
                  <input type="text" name="subject_name" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">รหัสวิชา</label>
                  <input type="text" name="subject_code" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">ชั้น</label>
                  <input type="text" name="class_level" placeholder="เช่น ม.1/1" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">นิเทศครั้งที่</label>
                  <input type="number" name="supervision_round" min="1" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">วันที่รับการนิเทศ <span class="text-red-500">*</span></label>
                  <input type="date" name="supervision_date" required 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition-all duration-300 bg-white/80" />
                </div>
              </div>
            </div>

            <!-- ตอนที่ 2: แบบประเมินสมรรถนะการจัดการเรียนรู้ -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-2xl border-l-4 border-green-500 card-hover">
              <h3 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
                📊 ตอนที่ 2 แบบประเมินสมรรถนะการจัดการเรียนรู้ของผู้รับการนิเทศ
              </h3>
              
              <!-- ด้านที่ 1: ความสามารถในการจัดทำแผนการจัดการเรียนรู้ -->
              <div class="mb-8">
                <h4 class="font-bold text-green-600 mb-4 text-lg">1. ด้านความสามารถในการจัดทำแผนการจัดการเรียนรู้</h4>
                <div class="space-y-4">
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">การวางแผนการสอนที่มีประสิทธิภาพ</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_effective" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_effective" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_effective" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_effective" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_effective" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้ถูกต้อง เป็นขั้นตอน และครบองค์ประกอบ</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_correct" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_correct" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_correct" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_correct" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_correct" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้มีกิจกรรมที่ทำให้นักเรียนเกิดการเรียนรู้</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_activities" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_activities" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_activities" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_activities" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_activities" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้มีการจัดหาสื่อที่เหมาะสมกับการเรียนรู้ของนักเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_media" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_media" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_media" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_media" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_media" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">แผนการจัดการเรียนรู้มีการวัดและประเมินผลผู้เรียนได้อย่างเหมาะสม</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_assessment" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_assessment" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_assessment" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_assessment" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="plan_assessment" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ด้านที่ 2: ความสามารถในการจัดการเรียนรู้ -->
              <div class="mb-6">
                <h4 class="font-bold text-green-600 mb-4 text-lg">2. ด้านความสามารถในการจัดการเรียนรู้</h4>
                <div class="space-y-4">
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">ใช้เทคนิคต่าง ๆ ที่ทำให้นักเรียนทุกคนมีส่วนร่วมในชั้นเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_techniques" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_techniques" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_techniques" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_techniques" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_techniques" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">เลือกใช้สื่อ เทคโนโลยีและอุปกรณ์การสอนที่เหมาะสม</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_media" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_media" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_media" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_media" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_media" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีการประเมินนักเรียนระหว่างเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_assessment" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_assessment" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_assessment" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_assessment" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_assessment" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">อธิบายเนื้อหาบทเรียนได้อย่างชัดเจน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_explanation" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_explanation" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_explanation" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_explanation" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_explanation" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีความสามารถในการควบคุมชั้นเรียนเมื่อทำกิจกรรม</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_control" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_control" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_control" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_control" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_control" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีการจัดกิจกรรมการเรียนรู้ที่เน้นการพัฒนาการคิด ได้อภิปราย ซักถาม และแสดงความคิดเห็น</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_thinking" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_thinking" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_thinking" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_thinking" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_thinking" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีการปรับเนื้อหา กิจกรรมในขณะจัดการเรียนรู้เพื่อให้เหมาะสมตามสถานการณ์หรือให้ทันเวลาที่เหลือ</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_adaptation" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_adaptation" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_adaptation" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_adaptation" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_adaptation" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีกิจกรรมการเรียนการสอนที่เชื่อมโยงหรือบูรณาการกับชีวิตประจำวัน สอดแทรกคุณธรรม จริยธรรมระหว่างเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_integration" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_integration" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_integration" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_integration" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_integration" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">ใช้ภาษาพูดและภาษาเขียนได้ถูกต้อง เหมาะสม</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_language" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_language" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_language" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_language" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="teach_language" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ด้านที่ 3: ความสามารถในการประเมินผล -->
              <div class="mb-6">
                <h4 class="font-bold text-green-600 mb-4 text-lg">3. ด้านความสามารถในการประเมินผล</h4>
                <div class="space-y-4">
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">วัดและประเมินผลด้วยวิธีการที่หลากหลาย</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_variety" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_variety" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_variety" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_variety" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_variety" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">วัดและประเมินผลสอดคล้องกับมาตรฐานการเรียนรู้ ตัวชี้วัด และจุดประสงค์การเรียนรู้</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_standards" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_standards" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_standards" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_standards" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_standards" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีเกณฑ์การวัดและประเมินผลที่ชัดเจน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_criteria" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_criteria" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_criteria" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_criteria" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_criteria" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">ให้ข้อมูลย้อนกลับแก่นักเรียนเพื่อการปรับปรุงหรือพัฒนา</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_feedback" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_feedback" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_feedback" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_feedback" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_feedback" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีผลงาน ชิ้นงาน ภาระงาน ซึ่งเป็นหลักฐานการเรียนรู้</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_evidence" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_evidence" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_evidence" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_evidence" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="eval_evidence" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ด้านที่ 4: ความสามารถในการจัดสภาพแวดล้อมในชั้นเรียน -->
              <div class="mb-6">
                <h4 class="font-bold text-green-600 mb-4 text-lg">4. ด้านความสามารถในการจัดสภาพแวดล้อมในชั้นเรียน</h4>
                <div class="space-y-4">
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">จัดสภาพห้องเรียนได้อย่างเหมาะสม และเอื้อต่อการเรียนรู้ของนักเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_classroom" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_classroom" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_classroom" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_classroom" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_classroom" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">สร้างปฏิสัมพันธ์เชิงบวกในชั้นเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_interaction" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_interaction" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_interaction" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_interaction" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_interaction" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">จัดชั้นเรียนให้มีความปลอดภัย ไม่เสี่ยงต่อการเกิดอุบัติเหตุในระหว่างการจัดการเรียนการสอน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_safety" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_safety" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_safety" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_safety" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_safety" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีความสามารถในการควบคุมชั้นเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_management" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_management" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_management" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_management" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_management" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">ชี้แจงกฎกติกาหรือข้อตกลงในการเรียน</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_rules" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_rules" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_rules" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_rules" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_rules" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-6 gap-4 items-center bg-white/80 p-4 rounded-xl border-2 border-gray-100 card-hover">
                    <div class="col-span-4 font-medium">มีการดูแลพฤติกรรมของนักเรียนในชั้นเรียนอย่างใกล้ชิด</div>
                    <div class="col-span-2 flex gap-3">
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_behavior" value="5" class="radio-modern"> 
                        <span class="font-semibold">5</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_behavior" value="4" class="radio-modern"> 
                        <span class="font-semibold">4</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_behavior" value="3" class="radio-modern"> 
                        <span class="font-semibold">3</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_behavior" value="2" class="radio-modern"> 
                        <span class="font-semibold">2</span>
                      </label>
                      <label class="flex items-center space-x-1">
                        <input type="radio" name="env_behavior" value="1" class="radio-modern"> 
                        <span class="font-semibold">1</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ผลการประเมิน -->
              <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-6 rounded-2xl border-l-4 border-yellow-500">
                <h4 class="font-bold text-yellow-700 mb-4 text-lg flex items-center gap-2">
                  🏆 ผลการประเมิน
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div class="space-y-2">
                    <label class="block font-semibold text-gray-700">รวมคะแนนทั้งหมด</label>
                    <input type="number" id="totalScore" name="total_score" readonly 
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-gray-100 text-center text-2xl font-bold text-blue-600" />
                  </div>
                  <div class="space-y-2">
                    <label class="block font-semibold text-gray-700">ระดับคุณภาพ</label>
                    <input type="text" id="qualityLevel" name="quality_level" readonly 
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-gray-100 text-center text-lg font-bold text-green-600" />
                  </div>
                  <div class="flex items-end">
                    <button type="button" id="calculateScore" 
                      class="btn-modern text-white px-6 py-3 rounded-xl w-full font-semibold shadow-lg">
                      🧮 คำนวณคะแนน
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- ตอนที่ 3: ผู้นิเทศบันทึกเพิ่มเติม -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-2xl border-l-4 border-purple-500 card-hover">
              <h3 class="text-xl font-bold text-purple-700 mb-4 flex items-center gap-2">
                📝 ตอนที่ 3 ผู้นิเทศบันทึกเพิ่มเติมการนิเทศการจัดการเรียนรู้
              </h3>
              
              <div class="space-y-6">
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">1. สิ่งที่พบจากการสังเกตการจัดการเรียนรู้ในชั้นเรียนของผู้รับการนิเทศ</label>
                  <textarea name="observation_notes" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-purple-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">2. การสะท้อนความคิดจากการจัดการเรียนรู้ในชั้นเรียนของผู้รับการนิเทศ</label>
                  <textarea name="reflection_notes" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-purple-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">3. ความประทับใจหรือจุดเด่นในการจัดการเรียนรู้ครั้งนี้</label>
                  <textarea name="strengths" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-purple-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="space-y-2">
                  <label class="block font-semibold text-gray-700">4. สิ่งที่ควรปรับปรุงหรือพัฒนา</label>
                  <textarea name="improvements" rows="4" 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-purple-500 transition-all duration-300 bg-white/80"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-2">
                    <label class="block font-semibold text-gray-700">ผู้รับการนิเทศ</label>
                    <input type="text" name="supervisee_signature" value="<?=htmlspecialchars($teacher_name)?>" placeholder="ชื่อผู้รับการนิเทศ" 
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-purple-500 transition-all duration-300 bg-white/80" />
                  </div>
                  <div class="space-y-2">
                    <label class="block font-semibold text-gray-700">ผู้นิเทศ</label>
                    <input type="text" name="supervisor_signature" placeholder="ชื่อผู้นิเทศ" 
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-purple-500 transition-all duration-300 bg-white/80" />
                  </div>
                </div>
              </div>
            </div>

            <!-- ตอนที่ 4: อัพโหลดเอกสาร -->
            <div class="bg-gradient-to-r from-orange-50 to-red-50 p-6 rounded-2xl border-l-4 border-orange-500 card-hover">
              <h3 class="text-xl font-bold text-orange-700 mb-4 flex items-center gap-2">
                📁 ตอนที่ 4 อัพโหลดเอกสารที่เกี่ยวข้อง
              </h3>
              
              <div class="space-y-6">
                <div class="space-y-3">
                  <label class="block font-semibold text-gray-700">แผนการจัดการเรียนรู้</label>
                  <input type="file" name="lesson_plan" accept=".pdf,.doc,.docx" 
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-6 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                  <div id="lessonPlanPreview" class="mt-3"></div>
                </div>
                
                <div class="space-y-3">
                  <label class="block font-semibold text-gray-700">ใบความรู้ / ใบงาน / ใบกิจกรรม</label>
                  <input type="file" name="worksheets" accept=".pdf,.doc,.docx,.jpg,.png" multiple 
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-6 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                  <div id="worksheetsPreview" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3"></div>
                </div>
                
                <div class="space-y-3">
                  <label class="block font-semibold text-gray-700">ภาพถ่ายที่เห็นทั้งผู้นิเทศและผู้รับการนิเทศ</label>
                  <input type="file" name="supervisor_photos" accept="image/*" multiple 
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-6 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                  <div id="supervisorPhotosPreview" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3"></div>
                </div>
                
                <div class="space-y-3">
                  <label class="block font-semibold text-gray-700">ภาพบรรยากาศการจัดการเรียนรู้</label>
                  <input type="file" name="classroom_photos" accept="image/*" multiple 
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-6 focus:outline-none focus:border-orange-500 transition-all duration-300 bg-white/80 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                  <div id="classroomPhotosPreview" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3"></div>
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
                💾 บันทึก
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
  // Modal elements
  const modalSupervision = document.getElementById('modalSupervision');
  const btnAddSupervision = document.getElementById('btnAddSupervision');
  const btnCloseSupervision = document.getElementById('closeModalSupervision');
  const btnCancelSupervision = document.getElementById('cancelSupervision');
  const formSupervision = document.getElementById('formSupervision');
  const btnCalculateScore = document.getElementById('calculateScore');
  const modalTitle = document.getElementById('modalSupervisionTitle');

  // Global variables
  let currentSupervisionId = null;
  let isEditMode = false;
  let dataTable = null;

  // Initialize
  initializeDataTable();
  loadSupervisions();

  // File preview handlers
  const fileInputs = {
    'lesson_plan': 'lessonPlanPreview',
    'worksheets': 'worksheetsPreview', 
    'supervisor_photos': 'supervisorPhotosPreview',
    'classroom_photos': 'classroomPhotosPreview'
  };

  Object.keys(fileInputs).forEach(inputName => {
    const input = document.querySelector(`input[name="${inputName}"]`);
    const preview = document.getElementById(fileInputs[inputName]);
    
    if (input && preview) {
      input.addEventListener('change', function() {
        handleFilePreview(this, preview);
      });
    }
  });

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
      const response = await fetch('../controllers/SupervisionController.php?action=list');
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

    supervisions.forEach(supervision => {
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
    
    const qualityBadge = getQualityBadge(supervision.quality_level);
    const formattedDate = formatDate(supervision.supervision_date);
    
    row.innerHTML = `
      <td class="py-4 px-4 text-center border-b border-gray-100">${formattedDate}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100 font-medium">${supervision.teacher_name}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">${supervision.subject_name || '-'}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">${supervision.class_level || '-'}</td>
      <td class="py-4 px-4 text-center border-b border-gray-100">
        <span class="score-display">${supervision.total_score}</span>
      </td>
      <td class="py-4 px-4 text-center border-b border-gray-100">
        ${qualityBadge}
      </td>
      <td class="py-4 px-4 text-center border-b border-gray-100">
        <div class="flex gap-2 justify-center">
          <button onclick="viewSupervision(${supervision.id})" class="btn bg-blue-500 text-white px-3 py-2 rounded-lg text-sm shadow-md">👁️ ดู</button>
          <button onclick="editSupervision(${supervision.id})" class="btn bg-yellow-500 text-white px-3 py-2 rounded-lg text-sm shadow-md">✏️ แก้ไข</button>
          <button onclick="deleteSupervision(${supervision.id})" class="btn bg-rose-500 text-white px-3 py-2 rounded-lg text-sm shadow-md">🗑️ ลบ</button>
        </div>
      </td>
    `;
    
    return row;
  }

  function getQualityBadge(qualityLevel) {
    const badges = {
      'ดีเยี่ยม': '<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold animate-pulse">ดีเยี่ยม</span>',
      'ดีมาก': '<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">ดีมาก</span>',
      'ดี': '<span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-semibold">ดี</span>',
      'พอใช้': '<span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-semibold">พอใช้</span>',
      'ควรปรับปรุง': '<span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">ควรปรับปรุง</span>'
    };
    return badges[qualityLevel] || `<span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">${qualityLevel}</span>`;
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'short', 
      day: 'numeric'
    });
  }

  // Global functions for button actions
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

  window.editSupervision = async function(id) {
    try {
      const response = await fetch(`../controllers/SupervisionController.php?action=detail&id=${id}`);
      const supervision = await response.json();
      
      if (!supervision || !supervision.id) {
        throw new Error('ไม่พบข้อมูลการนิเทศ');
      }

      openEditModal(supervision);
    } catch (error) {
      console.error('Error loading supervision for edit:', error);
      Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลการแก้ไขได้', 'error');
    }
  };

  window.deleteSupervision = async function(id) {
    const result = await Swal.fire({
      title: '⚠️ ยืนยันการลบ',
      text: 'คุณต้องการลบการนิเทศนี้ใช่หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: '🗑️ ลบ',
      cancelButtonText: '❌ ยกเลิก'
    });

    if (result.isConfirmed) {
      try {
        const response = await fetch('../controllers/SupervisionController.php?action=delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id: id })
        });

        const data = await response.json();
        
        if (data.success) {
          Swal.fire('สำเร็จ!', 'ลบการนิเทศเรียบร้อยแล้ว', 'success');
          loadSupervisions(); // Reload data
        } else {
          throw new Error(data.message || 'ไม่สามารถลบได้');
        }
      } catch (error) {
        console.error('Error deleting supervision:', error);
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถลบการนิเทศได้', 'error');
      }
    }
  };

  function showSupervisionDetails(supervision) {
    const qualityColor = getQualityColor(supervision.quality_level);
    
    Swal.fire({
      title: '📋 รายละเอียดการนิเทศ',
      html: `
        <div class="text-left space-y-4">
          <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-lg">
            <h4 class="font-bold text-blue-700 mb-2">ข้อมูลผู้รับการนิเทศ</h4>
            <p><strong>ชื่อ:</strong> ${supervision.teacher_name}</p>
            <p><strong>วิชา:</strong> ${supervision.subject_name || '-'}</p>
            <p><strong>ชั้น:</strong> ${supervision.class_level || '-'}</p>
            <p><strong>วันที่นิเทศ:</strong> ${formatDate(supervision.supervision_date)}</p>
          </div>
          
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg">
            <h4 class="font-bold text-green-700 mb-2">ผลการประเมิน</h4>
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600">${supervision.total_score}</div>
              <div class="text-lg font-semibold" style="color: ${qualityColor}">${supervision.quality_level}</div>
            </div>
          </div>
          
          ${supervision.strengths ? `
          <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-lg">
            <h4 class="font-bold text-yellow-700 mb-2">จุดเด่น</h4>
            <p class="text-sm">${supervision.strengths}</p>
          </div>
          ` : ''}
          
          ${supervision.improvements ? `
          <div class="bg-gradient-to-r from-red-50 to-pink-50 p-4 rounded-lg">
            <h4 class="font-bold text-red-700 mb-2">ข้อเสนอแนะ</h4>
            <p class="text-sm">${supervision.improvements}</p>
          </div>
          ` : ''}
        </div>
      `,
      width: '600px',
      confirmButtonText: '✅ ปิด',
      confirmButtonColor: '#059669'
    });
  }

  function getQualityColor(qualityLevel) {
    const colors = {
      'ดีเยี่ยม': '#059669',
      'ดีมาก': '#0891b2', 
      'ดี': '#7c3aed',
      'พอใช้': '#ea580c',
      'ควรปรับปรุง': '#dc2626'
    };
    return colors[qualityLevel] || '#6b7280';
  }

  function openEditModal(supervision) {
    isEditMode = true;
    currentSupervisionId = supervision.id;
    modalTitle.innerHTML = '✏️ แก้ไขการนิเทศการสอน';
    
    // Populate form with existing data
    populateForm(supervision);
    
    // Load existing files
    loadExistingFiles(supervision);
    
    // Show modal
    modalSupervision.classList.remove('hidden');
    modalSupervision.style.opacity = '0';
    setTimeout(() => {
      modalSupervision.style.opacity = '1';
    }, 10);
  }

  function populateForm(supervision) {
    // Basic information
    document.querySelector('input[name="teacher_name"]').value = supervision.teacher_name || '';
    document.querySelector('input[name="position"]').value = supervision.position || '';
    document.querySelector('input[name="academic_level"]').value = supervision.academic_level || '';
    document.querySelector('select[name="subject_group"]').value = supervision.subject_group || '';
    document.querySelector('input[name="subject_name"]').value = supervision.subject_name || '';
    document.querySelector('input[name="subject_code"]').value = supervision.subject_code || '';
    document.querySelector('input[name="class_level"]').value = supervision.class_level || '';
    document.querySelector('input[name="supervision_round"]').value = supervision.supervision_round || '';
    document.querySelector('input[name="supervision_date"]').value = supervision.supervision_date || '';

    // Rating scores
    const ratingFields = [
      'plan_effective', 'plan_correct', 'plan_activities', 'plan_media', 'plan_assessment',
      'teach_techniques', 'teach_media', 'teach_assessment', 'teach_explanation', 'teach_control',
      'teach_thinking', 'teach_adaptation', 'teach_integration', 'teach_language',
      'eval_variety', 'eval_standards', 'eval_criteria', 'eval_feedback', 'eval_evidence',
      'env_classroom', 'env_interaction', 'env_safety', 'env_management', 'env_rules', 'env_behavior'
    ];

    ratingFields.forEach(field => {
      const value = supervision[field];
      if (value) {
        const radio = document.querySelector(`input[name="${field}"][value="${value}"]`);
        if (radio) radio.checked = true;
      }
    });

    // Scores
    document.getElementById('totalScore').value = supervision.total_score || '';
    document.getElementById('qualityLevel').value = supervision.quality_level || '';

    // Notes
    document.querySelector('textarea[name="observation_notes"]').value = supervision.observation_notes || '';
    document.querySelector('textarea[name="reflection_notes"]').value = supervision.reflection_notes || '';
    document.querySelector('textarea[name="strengths"]').value = supervision.strengths || '';
    document.querySelector('textarea[name="improvements"]').value = supervision.improvements || '';
    document.querySelector('input[name="supervisee_signature"]').value = supervision.supervisee_signature || '';
    document.querySelector('input[name="supervisor_signature"]').value = supervision.supervisor_signature || '';
  }

  async function loadExistingFiles(supervision) {
    const fileTypes = ['lesson_plan', 'worksheets', 'supervisor_photos', 'classroom_photos'];
    
    fileTypes.forEach(fileType => {
      const files = supervision[fileType];
      if (files) {
        const preview = document.getElementById(fileInputs[fileType]);
        displayExistingFiles(files, preview, fileType);
      }
    });
  }

  function displayExistingFiles(filesString, previewContainer, fileType) {
    if (!filesString) return;
    
    const files = filesString.includes(',') ? filesString.split(',') : [filesString];
    
    files.forEach(file => {
      if (file.trim()) {
        const fileItem = createExistingFilePreview(file.trim(), fileType);
        previewContainer.appendChild(fileItem);
      }
    });
  }

  function createExistingFilePreview(filename, fileType) {
    const fileItem = document.createElement('div');
    fileItem.className = 'file-preview bg-white border-2 border-gray-200 rounded-xl overflow-hidden';
    
    const isImage = /\.(jpg|jpeg|png|gif)$/i.test(filename);
    
    if (isImage) {
      const img = document.createElement('img');
      img.src = `../${filename}`;
      img.className = 'w-full h-24 object-cover';
      img.onerror = function() {
        this.parentElement.innerHTML = `
          <div class="w-full h-24 bg-gray-100 flex items-center justify-center text-gray-500">
            <div class="text-center">
              <div class="text-2xl mb-1">🖼️</div>
              <div class="text-xs">ไม่พบไฟล์</div>
            </div>
          </div>
        `;
      };
      fileItem.appendChild(img);
    } else {
      const fileIcon = document.createElement('div');
      fileIcon.className = 'w-full h-24 bg-gray-100 flex items-center justify-center text-gray-500';
      const extension = filename.split('.').pop().toUpperCase();
      fileIcon.innerHTML = `
        <div class="text-center">
          <div class="text-2xl mb-1">📄</div>
          <div class="text-xs">${extension}</div>
        </div>
      `;
      fileItem.appendChild(fileIcon);
    }
    
    const fileName = document.createElement('div');
    fileName.className = 'p-2 text-xs text-gray-600 truncate';
    fileName.textContent = filename.split('/').pop();
    fileItem.appendChild(fileName);
    
    const removeBtn = document.createElement('button');
    removeBtn.className = 'file-remove-btn';
    removeBtn.innerHTML = '×';
    removeBtn.onclick = async (e) => {
      e.preventDefault();
      await removeExistingFile(filename, fileType);
      fileItem.remove();
    };
    fileItem.appendChild(removeBtn);
    
    return fileItem;
  }

  async function removeExistingFile(filename, fileType) {
    try {
      const response = await fetch('../controllers/SupervisionController.php?action=delete_file', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          filename: filename,
          supervision_id: currentSupervisionId,
          file_type: fileType
        })
      });

      const data = await response.json();
      if (!data.success) {
        throw new Error(data.message || 'ไม่สามารถลบไฟล์ได้');
      }
    } catch (error) {
      console.error('Error removing file:', error);
      Swal.fire('ข้อผิดพลาด', 'ไม่สามารถลบไฟล์ได้', 'error');
    }
  }

  function handleFilePreview(input, previewContainer) {
    // Don't clear existing files in edit mode, just add new ones
    if (!isEditMode) {
      previewContainer.innerHTML = '';
    }
    
    if (input.files && input.files.length > 0) {
      Array.from(input.files).forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-preview bg-white border-2 border-gray-200 rounded-xl overflow-hidden border-green-400';
        
        if (file.type.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = URL.createObjectURL(file);
          img.className = 'w-full h-24 object-cover';
          fileItem.appendChild(img);
        } else {
          const fileIcon = document.createElement('div');
          fileIcon.className = 'w-full h-24 bg-gray-100 flex items-center justify-center text-gray-500';
          fileIcon.innerHTML = `<div class="text-center"><div class="text-2xl mb-1">📄</div><div class="text-xs">${file.name.split('.').pop().toUpperCase()}</div></div>`;
          fileItem.appendChild(fileIcon);
        }
        
        const fileName = document.createElement('div');
        fileName.className = 'p-2 text-xs text-gray-600 truncate bg-green-50';
        fileName.innerHTML = `<span class="text-green-600">🆕</span> ${file.name}`;
        fileItem.appendChild(fileName);
        
        const removeBtn = document.createElement('button');
        removeBtn.className = 'file-remove-btn';
        removeBtn.innerHTML = '×';
        removeBtn.onclick = (e) => {
          e.preventDefault();
          fileItem.remove();
          const dt = new DataTransfer();
          Array.from(input.files).forEach((f, i) => {
            if (i !== index) dt.items.add(f);
          });
          input.files = dt.files;
        };
        fileItem.appendChild(removeBtn);
        
        previewContainer.appendChild(fileItem);
      });
    }
  }

  // Modal handlers with animations
  btnAddSupervision.addEventListener('click', () => {
    isEditMode = false;
    currentSupervisionId = null;
    modalTitle.innerHTML = '➕ บันทึกการนิเทศการสอน';
    modalSupervision.classList.remove('hidden');
    modalSupervision.style.opacity = '0';
    setTimeout(() => {
      modalSupervision.style.opacity = '1';
    }, 10);
  });

  function closeModal() {
    modalSupervision.style.opacity = '0';
    setTimeout(() => {
      modalSupervision.classList.add('hidden');
      formSupervision.reset();
      Object.values(fileInputs).forEach(previewId => {
        document.getElementById(previewId).innerHTML = '';
      });
      isEditMode = false;
      currentSupervisionId = null;
    }, 300);
  }

  btnCloseSupervision.addEventListener('click', closeModal);
  btnCancelSupervision.addEventListener('click', closeModal);

  // Enhanced calculate score function
  btnCalculateScore.addEventListener('click', function() {
    const radioGroups = [
      'plan_effective', 'plan_correct', 'plan_activities', 'plan_media', 'plan_assessment',
      'teach_techniques', 'teach_media', 'teach_assessment', 'teach_explanation', 'teach_control',
      'teach_thinking', 'teach_adaptation', 'teach_integration', 'teach_language',
      'eval_variety', 'eval_standards', 'eval_criteria', 'eval_feedback', 'eval_evidence',
      'env_classroom', 'env_interaction', 'env_safety', 'env_management', 'env_rules', 'env_behavior'
    ];

    let totalScore = 0;
    let filledGroups = 0;

    radioGroups.forEach(groupName => {
      const checkedRadio = document.querySelector(`input[name="${groupName}"]:checked`);
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

    // Update total score with animation
    const scoreInput = document.getElementById('totalScore');
    scoreInput.value = '';
    let currentScore = 0;
    const scoreInterval = setInterval(() => {
      currentScore += Math.ceil(totalScore / 20);
      if (currentScore >= totalScore) {
        currentScore = totalScore;
        clearInterval(scoreInterval);
      }
      scoreInput.value = currentScore;
    }, 50);

    // Determine quality level
    let qualityLevel = '';
    let levelColor = '';
    if (totalScore >= 98) {
      qualityLevel = 'ดีเยี่ยม';
      levelColor = '#059669';
    } else if (totalScore >= 74) {
      qualityLevel = 'ดีมาก';
      levelColor = '#0891b2';
    } else if (totalScore >= 50) {
      qualityLevel = 'ดี';
      levelColor = '#7c3aed';
    } else if (totalScore >= 26) {
      qualityLevel = 'พอใช้';
      levelColor = '#ea580c';
    } else {
      qualityLevel = 'ควรปรับปรุง';
      levelColor = '#dc2626';
    }

    setTimeout(() => {
      document.getElementById('qualityLevel').value = qualityLevel;
      document.getElementById('qualityLevel').style.color = levelColor;
    }, 1000);

    Swal.fire({
      title: '🎉 ผลการประเมิน',
      html: `
        <div class="text-left bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-xl">
          <div class="text-center mb-4">
            <div class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">${totalScore}</div>
            <div class="text-lg font-semibold" style="color: ${levelColor}">${qualityLevel}</div>
          </div>
          <hr class="my-4 border-gray-300">
          <p class="text-sm text-gray-600 mb-2"><strong>เกณฑ์การประเมิน:</strong></p>
          <ul class="text-sm text-gray-600 space-y-1">
            <li class="flex items-center"><span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>98-125 คะแนน = ดีเยี่ยม</li>
            <li class="flex items-center"><span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>74-97 คะแนน = ดีมาก</li>
            <li class="flex items-center"><span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>50-73 คะแนน = ดี</li>
            <li class="flex items-center"><span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>26-49 คะแนน = พอใช้</li>
            <li class="flex items-center"><span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>ต่ำกว่า 26 คะแนน = ควรปรับปรุง</li>
          </ul>
        </div>
      `,
      icon: 'success',
      confirmButtonText: '🎯 ตกลง',
      confirmButtonColor: '#059669',
      customClass: {
        popup: 'animate-fadeInUp'
      }
    });
  });

  // Enhanced form submission
  formSupervision.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Show loading with animation
    Swal.fire({
      title: '💾 กำลังบันทึกข้อมูล...',
      html: '<div class="text-center"><div class="animate-pulse">📊 กำลังประมวลผลการนิเทศ</div></div>',
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      const formData = new FormData(formSupervision);
      
      // Add ID for update
      if (isEditMode && currentSupervisionId) {
        formData.append('id', currentSupervisionId);
      }

      const action = isEditMode ? 'update' : 'create';
      const response = await fetch(`../controllers/SupervisionController.php?action=${action}`, {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      
      if (data.success) {
        Swal.close();
        Swal.fire({
          title: '🎉 สำเร็จ!',
          text: isEditMode ? 'แก้ไขการนิเทศเรียบร้อยแล้ว' : 'บันทึกการนิเทศเรียบร้อยแล้ว',
          icon: 'success',
          confirmButtonText: '👍 เยี่ยม',
          confirmButtonColor: '#059669',
          customClass: {
            popup: 'animate-fadeInUp'
          }
        });
        closeModal();
        loadSupervisions(); // Reload the table
      } else {
        throw new Error(data.message || 'เกิดข้อผิดพลาด');
      }
    } catch (error) {
      console.error('Error submitting form:', error);
      Swal.close();
      Swal.fire('ข้อผิดพลาด', error.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
    }
  });

  // Add floating action button effect
  btnAddSupervision.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-3px) scale(1.05)';
  });

  btnAddSupervision.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0) scale(1)';
  });
});
</script>

<?php require_once('script.php');?>
</body>
</html>
