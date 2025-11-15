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
            <h1 class="m-0 text-3xl font-bold text-white flex items-center gap-3 animate-pulse">
              📑 <span class="drop-shadow-lg">รายงานการสอน</span>
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
            
            
            <div class="relative z-10">
              <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                  <p class="uppercase tracking-[0.2em] text-xs text-gray-500 dark:text-gray-400 mb-1">Teaching Experience Hub</p>
                  <h2 class="text-3xl md:text-4xl font-extrabold flex items-center gap-3 text-slate-900 dark:text-white">
                    📊 <span class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 bg-clip-text text-transparent">แดชบอร์ดรายงานการสอน</span>
                  </h2>
                  <p class="text-gray-600 dark:text-gray-400 text-base md:text-lg flex items-center gap-2">
                    <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span></span>
                    อัปเดตข้อมูลเรียลไทม์ พร้อมลูกเล่นเอฟเฟกต์สุดโมเดิร์น ✨
                  </p>
                </div>
                <div class="flex items-center gap-3">
                  <button id="btnAddReport" class="group relative inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-400 via-green-500 to-emerald-600 px-6 py-3 text-white font-semibold shadow-lg shadow-emerald-500/30 transition-all duration-300 hover:shadow-2xl hover:-translate-y-0.5 focus-ring">
                    <span class="text-xl group-hover:rotate-6 transition-transform duration-200">➕</span>
                    เพิ่มรายงานใหม่
                    <span class="absolute inset-0 rounded-full border border-white/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                  </button>
                </div>
              </div>

              <div id="reportStats" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                <div class="stat-sheen glow-card rounded-2xl p-5 bg-green-300 dark:bg-green-500 ">
                  <div class="text-sm font-semibold text-white dark:text-white">📈 รายงานทั้งหมด</div>
                  <div id="statTotalReports" class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">0</div>
                  <p id="statUpdatedAt" class="text-xs text-white dark:text-white mt-2">อัปเดตล่าสุด: -</p>
                </div>
                <div class="stat-sheen glow-card rounded-2xl p-5 bg-emerald-300 dark:bg-emerald-500 ">
                  <div class="text-sm font-semibold text-white dark:text-white">🛡️ เข้าเรียนครบ</div>
                  <div class="text-4xl font-black text-white dark:text-white" id="statPerfectSessions">0</div>
                  <p class="text-xs text-white dark:text-white mt-2">จำนวนคาบที่ไม่มีการขาด ลา หรือกิจกรรม</p>
                </div>
                <div class="stat-sheen glow-card rounded-2xl p-5 bg-orange-300 dark:bg-orange-500 ">
                  <div class="text-sm font-semibold text-white dark:text-white">📉 ค่าเฉลี่ยการขาด</div>
                  <div class="text-4xl font-black text-white dark:text-white" id="statAverageAbsent">0</div>
                  <p class="text-xs text-white dark:text-white mt-2">นับรวมขาด/ลาป่วย/ลากิจ/กิจกรรม ต่อ 1 รายงาน</p>
                </div>
                <div class="stat-sheen glow-card rounded-2xl p-5 bg-purple-300 dark:bg-purple-500 ">
                  <div class="text-sm font-semibold text-white dark:text-white">🆕 รายงานล่าสุด</div>
                  <div class="text-xl font-bold text-white dark:text-white" id="statLatestInfo">ยังไม่มีข้อมูล</div>
                  <p class="text-xs text-white dark:text-white mt-2">แสดงวิชา/ห้อง/คาบล่าสุดที่บันทึก</p>
                </div>
              </div>

              <div class="overflow-x-auto rounded-2xl border border-white/60 dark:border-white/10 backdrop-blur-xl shadow-inner">
                <table class="min-w-full border-collapse overflow-hidden">
                  <thead class="bg-gradient-to-r from-blue-500/90 via-indigo-500/90 to-purple-600/90 ">
                    <tr>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">📅 วันที่</th>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">📖 วิชา</th>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">🏫 ห้อง</th>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">⏰ คาบ</th>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">📝 แผน/หัวข้อ</th>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">👨‍🏫 กิจกรรม</th>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">🙋‍♂️ ขาดเรียน</th>
                      <th class="py-4 px-4 border-b border-white/30 text-center font-semibold tracking-wide">🔍 จัดการ</th>
                    </tr>
                  </thead>
                  <tbody id="reportTableBody" class="divide-y divide-gray-200/70 dark:divide-gray-700/50 ">

                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal สำหรับเพิ่ม/แก้ไขรายงาน -->
      <div id="modalAddReport" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-all duration-300 bg-white/30 dark:bg-black/30 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-5xl p-8 relative overflow-y-auto max-h-screen border border-gray-200 dark:border-gray-700 transform scale-95 animate-in fade-in-0 zoom-in-95">
          <button id="closeModalAddReport" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300 text-3xl hover:scale-110 transition-all duration-200 focus-ring">&times;</button>
          <h2 id="modalReportTitle" class="text-2xl font-bold mb-6 flex items-center gap-3 text-gray-800 dark:text-gray-100">
            ➕ <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">เพิ่มรายงานการสอน</span>
          </h2>
          <form id="formAddReport" class="space-y-6" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">📅 วันที่ <span class="text-red-500">*</span></label>
                <input type="date" name="report_date" id="reportDate" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" />
              </div>
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">📖 ชื่อวิชา <span class="text-red-500">*</span></label>
                <select name="subject_id" id="subjectSelect" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200">
                  <option value="">-- เลือกวิชา --</option>
                  <!-- JS will fill options -->
                </select>
              </div>
            </div>
            <div id="classRoomSelectArea">
              <!-- ห้องเรียนและคาบจะถูกเติมโดย JS -->
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">📋 เลขแผนการสอน</label>
                <input type="text" name="plan_number" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" />
              </div>
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">📝 หัวข้อ/สาระการเรียนรู้</label>
                <input type="text" name="plan_topic" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" />
              </div>
            </div>
            <div>
              <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">👨‍🏫 กิจกรรมการเรียนรู้</label>
              <textarea name="activity" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" rows="3"></textarea>
            </div>
            <div>
              <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">🙋‍♂️ รายชื่อนักเรียนที่ขาดเรียน</label>
              <div id="studentAttendanceArea">
                <!-- JS จะเติมรายชื่อนักเรียนและ checkbox สถานะ -->
                <div class="text-gray-400 dark:text-gray-500 text-sm bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน 🎓</div>
              </div>
              <textarea name="absent_students" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300 hidden"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">💡 สะท้อนคิด (K - ความรู้)</label>
                <textarea name="reflection_k" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" rows="3"></textarea>
              </div>
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">💡 สะท้อนคิด (P - ทักษะ)</label>
                <textarea name="reflection_p" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" rows="3"></textarea>
              </div>
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">💡 สะท้อนคิด (A - เจตคติ)</label>
                <textarea name="reflection_a" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" rows="3"></textarea>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">❗ ปัญหา/อุปสรรค</label>
                <textarea name="problems" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" rows="3"></textarea>
              </div>
              <div>
                <label class="block mb-2 font-semibold text-gray-900 dark:text-gray-600 flex items-center gap-2">📝 ข้อเสนอแนะ</label>
                <textarea name="suggestions" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200" rows="3"></textarea>
              </div>
            </div>
            <div id="roomImageInputsArea" class="mb-4"></div>
            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button type="button" id="cancelAddReport" class="px-6 py-3 rounded-lg bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-gray-100 hover:scale-105 transition-all duration-200">❌ ยกเลิก</button>
              <button type="submit" class="px-6 py-3 rounded-lg bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200">💾 บันทึก</button>
            </div>
          </form>
        </div>
      </div>
      <!-- End Modal -->
      <!-- Modal สำหรับแสดงรายละเอียดการเข้าเรียน -->
      <div id="attendanceModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-white/30 dark:bg-black/30 backdrop-blur-sm">
        <div role="dialog" aria-modal="true" aria-label="รายละเอียดการเข้าเรียน" id="attendanceModalInner" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl p-6 relative modal-fade modal-scroll border border-gray-200 dark:border-gray-700">
          <button id="closeAttendanceModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300 text-3xl hover:scale-110 transition-all duration-200 focus-ring">&times;</button>
          <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 text-gray-800 dark:text-gray-100">
            📋 <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">รายละเอียดการเข้าเรียน</span>
          </h2>
          <div id="attendanceModalContent"></div>
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
document.addEventListener('DOMContentLoaded', function() {
  loadReports();
  loadSubjectsForReport();

  $('body').addClass('sidebar-collapse');

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
      <button class="tt bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-3 py-1 rounded-md shadow-sm transition-all duration-200 btn-report-detail flex items-center justify-center gap-2 text-xs hover:scale-105 transform" data-id="${reportId}" data-title="ดูรายละเอียด">
        <i class="bi bi-eye"></i> <span class="hidden md:inline">ดู</span>
      </button>
      <button class="tt bg-yellow-400 hover:bg-yellow-500 dark:bg-yellow-500 dark:hover:bg-yellow-600 text-white px-3 py-1 rounded-md shadow-sm transition-all duration-200 btn-edit-report flex items-center justify-center gap-2 text-xs hover:scale-105 transform" data-id="${reportId}" data-title="แก้ไข">
        <i class="bi bi-pencil"></i> <span class="hidden md:inline">แก้ไข</span>
      </button>
      <button class="tt bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white px-3 py-1 rounded-md shadow-sm transition-all duration-200 btn-delete-report flex items-center justify-center gap-2 text-xs hover:scale-105 transform" data-id="${reportId}" data-title="ลบ">
        <i class="bi bi-trash"></i> <span class="hidden md:inline">ลบ</span>
      </button>
      <button class="tt bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-800 text-white px-3 py-1 rounded-md shadow-sm transition-all duration-200 btn-print-report flex items-center justify-center gap-2 text-xs hover:scale-105 transform" data-id="${reportId}" data-title="พิมพ์">
        <i class="bi bi-printer"></i> <span class="hidden md:inline">พิมพ์</span>
      </button>
    `;
  }

  function parseAttendanceCount(listString) {
    if (!listString) return 0;
    return listString.split(/[,\n]/).map(item => item.trim()).filter(Boolean).length;
  }

  function updateReportStats(sortedReports = []) {
    const totalReports = sortedReports.length;
    let totalMissEvents = 0;
    let perfectSessions = 0;

    sortedReports.forEach(report => {
      const absent = parseAttendanceCount(report.absent_students);
      const sick = parseAttendanceCount(report.sick_students);
      const personal = parseAttendanceCount(report.personal_students);
      const activity = parseAttendanceCount(report.activity_students);
      const sum = absent + sick + personal + activity;
      totalMissEvents += sum;
      if (sum === 0) perfectSessions += 1;
    });

    const avgMiss = totalReports ? (totalMissEvents / totalReports).toFixed(1) : '0';
    const latest = sortedReports[0] || null;

    const statTotalEl = document.getElementById('statTotalReports');
    const statUpdatedEl = document.getElementById('statUpdatedAt');
    const statPerfectEl = document.getElementById('statPerfectSessions');
    const statAvgEl = document.getElementById('statAverageAbsent');
    const statLatestEl = document.getElementById('statLatestInfo');

    if (statTotalEl) statTotalEl.textContent = totalReports.toString();
    if (statUpdatedEl) statUpdatedEl.textContent = `อัปเดตล่าสุด: ${latest ? formatThaiDate(latest.report_date) : '-'}`;
    if (statPerfectEl) statPerfectEl.textContent = perfectSessions.toString();
    if (statAvgEl) statAvgEl.textContent = avgMiss;
    if (statLatestEl) {
      if (!latest) {
        statLatestEl.textContent = 'ยังไม่มีข้อมูล';
      } else {
        const subject = latest.subject_name || '-';
        const room = latest.level && latest.class_room ? `ม.${latest.level}/${latest.class_room}` : '-';
        statLatestEl.textContent = `${subject} · ${room} · คาบ ${latest.period_start}-${latest.period_end}`;
      }
    }
  }

  function loadReports() {
    fetch('../controllers/TeachingReportController.php?action=list')
      .then(res => res.json())
      .then(data => {
        const tbody = document.getElementById('reportTableBody');
        tbody.innerHTML = '';
        
        if (!data.length) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-400 py-6">ไม่มีข้อมูลรายงานการสอน</td></tr>`;
          updateReportStats([]);
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

        updateReportStats(sortedData);

        sortedData.forEach(report => {
          tbody.innerHTML += `
            <tr class="group hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-300 border-b border-gray-200/70 dark:border-gray-800">
              <td class="py-4 px-4 text-center">
                <div class="font-semibold text-blue-700 dark:text-blue-300 text-base">${formatThaiDate(report.report_date)}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">${getThaiDayOfWeek(report.report_date)}</div>
              </td>
              <td class="py-4 px-4 text-center text-blue-900 dark:text-blue-300 font-medium">
                ${report.subject_name || '-'}
              </td>
              <td class="py-4 px-4 text-center">
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-900/30 text-blue-200 dark:bg-blue-100/80 dark:text-blue-700 shadow-sm">
                  ม.${report.level}/${report.class_room}
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-900/30 text-blue-200 dark:bg-blue-100/80 dark:text-blue-700 shadow-sm">
                  ${report.period_start}-${report.period_end}
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="max-w-xs truncate text-dark dark:text-white" title="${report.plan_topic || '-'}">
                  ${report.plan_topic ? (report.plan_topic.length > 30 ? report.plan_topic.substring(0, 30) + '...' : report.plan_topic) : '-'}
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="max-w-xs truncate text-dark dark:text-white" title="${report.activity || '-'}">
                  ${report.activity ? (report.activity.length > 30 ? report.activity.substring(0, 30) + '...' : report.activity) : '-'}
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <button class="bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white px-3 py-1 rounded-lg shadow-sm hover:shadow-lg transition-all duration-200 btn-show-attendance flex items-center gap-2 text-sm hover:-translate-y-0.5 focus-ring" data-id="${report.id}" data-title="รายละเอียดการเข้าเรียน">
                  <span class="text-sm">📋</span> <span class="hidden sm:inline font-medium">ดู</span>
                </button>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="flex items-center justify-center gap-2 p-1 rounded-lg">
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
            stripe: true,
            autoWidth: false,
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
              { targets: 3, width: '15%' },
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

        document.querySelectorAll('.btn-show-attendance').forEach(btn => {
          btn.addEventListener('click', function() {
            const reportId = btn.getAttribute('data-id');
            showAttendanceDetail(reportId);
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

                    // รอ studentAttendanceArea ถูก render แล้วค่อย restore attendance (set values บน select)
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
                            'เข้าร่วมกิจกรรม': 'activity',
                            'โดดเรียน': 'truant'
                          };
                          attendanceLogs.forEach(log => {
                            const stuId = log.student_id;
                            // รองรับกรณีไม่มี class_room ใน log
                            let room = log.class_room;
                            if (!room) {
                              // หา element ที่มี student_id นี้
                              const el = formReport.querySelector(`[name$="[${stuId}]"]`);
                              if (el) {
                                const match = el.name.match(/^attendance\[(.+?)\]\[\d+\]$/);
                                if (match) room = match[1];
                              }
                            }
                            const status = statusMap[log.status] || 'present';
                            // Set value บน element (select/input)
                            const el = room ? formReport.querySelector(`[name="attendance[${room}][${stuId}]"]`) : null;
                            if (el) {
                              el.value = status;
                              // ปรับสีพื้นหลังเล็กน้อยถ้าเป็น select
                              if (el.classList.contains('attendance-select')) {
                                el.dispatchEvent(new Event('change'));
                              }
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
      classRoomSelectArea.innerHTML = `<div class="text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">ไม่มีห้องเรียนที่สอนในวัน${thaiDay} สำหรับวิชานี้ ❌</div>`;
      return;
    }

    let html = `<label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">🏫 เลือกห้องเรียน <span class="text-red-500">*</span></label>
      <div class="flex flex-wrap gap-3 mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">`;
    Object.keys(roomMap).forEach(room => {
      html += `
        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 p-2 rounded-lg transition-colors duration-200">
          <input type="checkbox" name="class_room[]" value="${room}" class="form-checkbox report-class-room-checkbox text-blue-600 dark:text-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400" />
          <span class="text-gray-800 dark:text-gray-200 font-medium">${room}</span>
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
      // sanitize key for use in input names
      const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
      area.innerHTML += `
        <div class="mb-4 border border-gray-200 dark:border-gray-600 rounded-xl p-4 bg-gray-50 dark:bg-gray-700 shadow-sm">
          <div class="font-bold text-blue-700 dark:text-blue-400 mb-3 text-lg flex items-center gap-2">⏰ ${room}</div>
          <div class="flex flex-wrap gap-3">
            ${periods.map((p, idx) => `
              <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 p-3 rounded-lg transition-colors duration-200 border border-gray-300 dark:border-gray-500">
                <input type="checkbox" name="periods[${key}][]" data-room="${room}" value="${p.period_start}|${p.period_end}|${p.day_of_week}" class="form-checkbox text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400 report-period-checkbox" />
                <span class="text-gray-800 dark:text-gray-200 font-medium">${p.day_of_week} คาบ ${p.period_start}-${p.period_end}</span>
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
      const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
      area.innerHTML += `
        <div class="mb-4 border border-gray-200 dark:border-gray-600 rounded-xl p-4 bg-gray-50 dark:bg-gray-700 shadow-sm">
          <div class="font-bold text-blue-700 dark:text-blue-400 mb-3 text-lg flex items-center gap-2">🖼️ แนบรูปภาพสำหรับห้อง ${room}</div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">📸 แนบรูปภาพ 1</label>
              <input type="file" name="image1_${key}" data-room="${room}" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 transition-all duration-200" />
            </div>
            <div>
              <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">📸 แนบรูปภาพ 2</label>
              <input type="file" name="image2_${key}" data-room="${room}" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 transition-all duration-200" />
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
    { value: 'activity', label: 'กิจกรรม', color: 'bg-purple-400', emoji: '🎉' },
    { value: 'truant', label: 'โดดเรียน', color: 'bg-gray-600', emoji: '🚫' }
    ];

  const attendanceStyleConfig = {
    present: {
      select: ['bg-emerald-50','text-emerald-700','border-emerald-200'],
      pill: ['bg-emerald-100','text-emerald-700'],
      label: '✅ มา'
    },
    absent: {
      select: ['bg-rose-50','text-rose-600','border-rose-200'],
      pill: ['bg-rose-100','text-rose-600'],
      label: '❌ ขาด'
    },
    late: {
      select: ['bg-amber-50','text-amber-600','border-amber-200'],
      pill: ['bg-amber-100','text-amber-600'],
      label: '⏰ สาย'
    },
    sick: {
      select: ['bg-sky-50','text-sky-600','border-sky-200'],
      pill: ['bg-sky-100','text-sky-600'],
      label: '🤒 ลาป่วย'
    },
    personal: {
      select: ['bg-indigo-50','text-indigo-600','border-indigo-200'],
      pill: ['bg-indigo-100','text-indigo-600'],
      label: '📝 ลากิจ'
    },
    activity: {
      select: ['bg-purple-50','text-purple-600','border-purple-200'],
      pill: ['bg-purple-100','text-purple-600'],
      label: '🎉 กิจกรรม'
    }
    ,
    truant: {
      select: ['bg-gray-50','text-gray-800','border-gray-200'],
      pill: ['bg-gray-100','text-gray-800'],
      label: '🚫 โดดเรียน'
    }
  };


  // เรียกข้อมูลนักเรียนเมื่อเลือกห้องและคาบ
  function loadStudentsForAttendance(subjectId, selectedRooms) {
    const area = document.getElementById('studentAttendanceArea');
    area.innerHTML = '';
    if (!subjectId || !selectedRooms.length) {
        area.innerHTML = '<div class="text-gray-400 dark:text-gray-500 text-sm bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน 🎓</div>';
        return;
    }

    const classRoomData = selectedRooms.map(r => ({ class: r.class, room: r.room }));
    fetch('../controllers/StudentController.php?action=list&subject_id=' + encodeURIComponent(subjectId) +
        '&rooms=' + encodeURIComponent(JSON.stringify(selectedRooms)))
        .then(res => res.json())
        .then(data => {
        if (!data.length) {
            area.innerHTML = '<div class="text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-700">ไม่พบนักเรียนในห้องที่เลือก ❌</div>';
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
          html += `<div class="mb-6 glow-card border border-white/60 dark:border-white/10 p-6 rounded-2xl bg-white/85 dark:bg-gray-900/70 backdrop-blur-xl">
          <div class="font-bold text-blue-700 dark:text-blue-300 mb-3 text-lg flex items-center gap-2">🏫 ห้อง ${room}<span class="text-xs font-normal text-slate-500 dark:text-slate-400">${groupByRoom[room].length} คน</span></div>
          <table class="w-full text-sm table-auto border-collapse rounded-2xl overflow-hidden shadow-inner bg-white/90 dark:bg-gray-800/60">
                <thead>
            <tr class="bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-purple-500/10 text-left text-slate-900 dark:text-slate-100">
              <th class="p-4 border border-gray-200/70 dark:border-gray-700/60 font-bold">เลขที่</th>
              <th class="p-4 border border-gray-200/70 dark:border-gray-700/60 font-bold">ชื่อ - สกุล</th>
              <th class="p-4 border border-gray-200/70 dark:border-gray-700/60 font-bold">สถานะเข้าเรียน</th>
                </tr>
                </thead>
                <tbody>`;

            groupByRoom[room].forEach((student, idx) => {
            html += `
            <tr class="border-b border-gray-200/80 dark:border-gray-700/60 hover:bg-indigo-50/60 dark:hover:bg-gray-800/70 transition-colors duration-200">
            <td class="p-4 border border-gray-200/70 dark:border-gray-700/60 text-center text-slate-900 dark:text-white font-semibold">${idx + 1}</td>
            <td class="p-4 border border-gray-200/70 dark:border-gray-700/60 text-slate-900 dark:text-white font-medium">${student.Stu_id} ${student.fullname}</td>
            <td class="p-4 border border-gray-200/70 dark:border-gray-700/60">
              <div class="flex flex-wrap gap-2 items-center">`;

            // สร้าง select แบบเรียบง่ายสำหรับเลือกสถานะการเข้าเรียน
            html += `
              <select name="attendance[${room}][${student.Stu_id}]" class="attendance-select w-44 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white/90 dark:bg-gray-800 text-slate-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-indigo-400/40 transition-all duration-200">
                <option value="present">มา</option>
                <option value="absent">ขาด</option>
                <option value="late">มาสาย</option>
                <option value="sick">ลาป่วย</option>
                <option value="personal">ลากิจ</option>
                <option value="activity">กิจกรรม</option>
                <option value="truant">โดดเรียน</option>
              </select>
              <span class="attendance-pill text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200 transition-all duration-200">✅ มา</span>
                    </div>
                </td>
                </tr>`;
            });

            html += `</tbody></table></div>`;
        });

        area.innerHTML = html;

        // ตั้งค่าเริ่มต้นและจัดการเปลี่ยนสถานะผ่าน select แบบเรียบง่าย
        area.querySelectorAll('.attendance-select').forEach(sel => {
          const pillEl = sel.parentElement.querySelector('.attendance-pill');
          const resetClasses = () => {
            Object.values(attendanceStyleConfig).forEach(cfg => {
              cfg.select.forEach(cls => sel.classList.remove(cls));
              if (pillEl) cfg.pill.forEach(cls => pillEl.classList.remove(cls));
            });
          };
          const applyStyle = (status) => {
            resetClasses();
            const cfg = attendanceStyleConfig[status];
            if (cfg) {
              cfg.select.forEach(cls => sel.classList.add(cls));
              if (pillEl) {
                cfg.pill.forEach(cls => pillEl.classList.add(cls));
                pillEl.textContent = cfg.label;
              }
            } else if (pillEl) {
              pillEl.textContent = '—';
            }
          };

          sel.addEventListener('change', function() {
            applyStyle(sel.value);
          });

          applyStyle(sel.value);
        });
        });
    }
  



  function showReportDetail(reportId) {
    fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
      .then(res => res.json())
      .then(report => {
        // helper: count items in a comma/newline separated list
        const countList = (s) => {
          if (!s) return 0;
          return s.split(/[,\n]/).map(x => x.trim()).filter(Boolean).length;
        };

        const attendanceBreakdown = [
          { label: '❌ ขาดเรียน', value: (typeof report.absent_count !== 'undefined') ? Number(report.absent_count) : countList(report.absent_students), color: 'text-rose-500' },
          { label: '🤒 ลาป่วย', value: (typeof report.sick_count !== 'undefined') ? Number(report.sick_count) : countList(report.sick_students), color: 'text-sky-500' },
          { label: '📝 ลากิจ', value: (typeof report.personal_count !== 'undefined') ? Number(report.personal_count) : countList(report.personal_students), color: 'text-indigo-500' },
          { label: '🎉 กิจกรรม', value: (typeof report.activity_count !== 'undefined') ? Number(report.activity_count) : countList(report.activity_students), color: 'text-purple-500' },
          { label: '🚫 โดดเรียน', value: (typeof report.truant_count !== 'undefined') ? Number(report.truant_count) : countList(report.truant_students), color: 'text-gray-800' }
        ];

        const html = `
          <div class="relative max-w-4xl mx-auto py-12">
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 opacity-30 dark:opacity-20 blur-3xl rounded-3xl" aria-hidden="true"></div>
            
            <div class="relative bg-white/95 dark:bg-gray-900/90 backdrop-blur-2xl rounded-3xl border border-white/40 dark:border-white/10 shadow-2xl dark:shadow-[0_25px_80px_rgba(0,0,0,0.3)] overflow-hidden">
              <div class="p-6 md:p-8 space-y-8 max-h-[85vh] overflow-y-auto">

                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                  <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">รายงานการสอน</p>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                      📑 ${report.subject_name || '-'}
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-400 mt-1">${formatThaiDate(report.report_date)} · คาบ ${report.period_start}-${report.period_end} · ม.${report.level}/${report.class_room}</p>
                  </div>
                  <div class="flex-shrink-0 px-5 py-3 rounded-2xl bg-gradient-to-r from-lime-400 to-green-500 text-white dark:from-lime-500 dark:to-green-600 dark:text-white font-bold flex items-center gap-2 shadow-lg dark:shadow-green-500/20">
                    🌟 แผนที่ ${report.plan_number || 'ไม่ระบุ'}
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="rounded-2xl p-5 bg-slate-50 dark:bg-slate-800/60 border border-white/50 dark:border-white/10 transition-all duration-300 hover:shadow-lg hover:shadow-slate-500/10">
                    <p class="text-sm text-slate-500 dark:text-slate-400">📝 แผน/หัวข้อ</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${report.plan_topic || '-'}</p>
                  </div>
                  <div class="rounded-2xl p-5 bg-slate-100/50 dark:bg-slate-800/30 border border-white/50 dark:border-white/10 transition-all duration-300 hover:shadow-lg hover:shadow-slate-500/10">
                    <p class="text-sm text-slate-500 dark:text-slate-400">👨‍🏫 กิจกรรม</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${report.activity || '-'}</p>
                  </div>
                  <div class="rounded-2xl p-5 bg-gradient-to-br from-indigo-100/70 to-purple-100/70 dark:from-indigo-900/50 dark:to-purple-900/50 border border-indigo-100/50 dark:border-indigo-900/40 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/20">
                    <p class="text-sm text-slate-500 dark:text-slate-300">📋 สถานะการเข้าเรียน</p>
                    <div class="mt-3 space-y-1 text-sm">
                      ${attendanceBreakdown.map(item => `<div class="flex justify-between ${item.color} dark:text-white/90"><span>${item.label}</span><span class="font-semibold">${typeof item.value === 'number' ? item.value : (item.value || 0)}</span></div>`).join('')}
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  ${['K','P','A'].map(key => {
                    const labels = { K: 'ความรู้ (Knowledge)', P: 'ทักษะ (Practice)', A: 'เจตคติ (Attitude)' };
                    const icons = { K: '💡', P: '🚀', A: '❤️' };
                    const value = report[`reflection_${key.toLowerCase()}`] || '-';
                    // Define color themes for each KPA
                    const colors = {
                      K: 'from-blue-50 to-white dark:from-blue-900/50 dark:to-gray-900/20 border-blue-100 dark:border-blue-800/50 hover:shadow-blue-500/20',
                      P: 'from-orange-50 to-white dark:from-orange-900/50 dark:to-gray-900/20 border-orange-100 dark:border-orange-800/50 hover:shadow-orange-500/20',
                      A: 'from-pink-50 to-white dark:from-pink-900/50 dark:to-gray-900/20 border-pink-100 dark:border-pink-800/50 hover:shadow-pink-500/20'
                    };
                    
                    return `
                      <div class="rounded-2xl p-5 bg-gradient-to-br ${colors[key]} border transition-all duration-300 hover:shadow-xl">
                        <p class="text-sm text-slate-500 dark:text-slate-300">${icons[key]} สะท้อนคิด (${key})</p>
                        <p class="mt-2 text-base leading-relaxed text-slate-800 dark:text-slate-100">${value}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">${labels[key]}</p>
                      </div>`;
                  }).join('')}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="rounded-2xl p-5 bg-gradient-to-br from-rose-50/80 to-white/70 dark:from-rose-900/30 dark:to-gray-900/20 border border-rose-100/60 dark:border-rose-500/30 transition-all duration-300 hover:shadow-lg hover:shadow-rose-500/20">
                    <p class="text-sm font-semibold text-rose-600 dark:text-rose-300">❗ ปัญหา / อุปสรรค</p>
                    <p class="mt-3 text-base text-slate-800 dark:text-slate-100">${report.problems || '-'}</p>
                  </div>
                  <div class="rounded-2xl p-5 bg-gradient-to-br from-emerald-50/80 to-white/70 dark:from-emerald-900/30 dark:to-gray-900/20 border border-emerald-100/60 dark:border-emerald-500/30 transition-all duration-300 hover:shadow-lg hover:shadow-emerald-500/20">
                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-300">📝 ข้อเสนอแนะ</p>
                    <p class="mt-3 text-base text-slate-800 dark:text-slate-100">${report.suggestions || '-'}</p>
                  </div>
                </div>

                <div class="space-y-3">
                  <p class="text-sm text-slate-500 dark:text-slate-400">🖼️ รูปภาพประกอบ</p>
                  <div class="flex flex-wrap gap-4">
                    ${report.image1 ? `<img src="../${report.image1}" class="w-40 h-28 object-cover rounded-2xl border border-white/60 drop-shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" />` : `<div class="w-40 h-28 flex items-center justify-center rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 text-sm text-slate-400">ไม่มีรูปภาพ</div>`}
                    ${report.image2 ? `<img src="../${report.image2}" class="w-40 h-28 object-cover rounded-2xl border border-white/60 drop-shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" />` : ''}
                  </div>
                </div>

                <div class="pt-6 border-t border-slate-200 dark:border-slate-700/50">
                  <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    👩‍🏫 ผู้รายงาน: <span class="font-semibold text-slate-700 dark:text-slate-300">${report.teacher_name || report.teacher_id || '-'}</span>
                  </div>
                </div>

              </div>
            </div>
          </div>
        `;

        Swal.fire({
          html: html,
          width: 950,
          showCloseButton: true,
          showConfirmButton: false,
          background: 'transparent',
          padding: 0 // ✨ SWAL CONFIG: เพิ่ม padding: 0
        });
      });
  }

  function showAttendanceDetail(reportId) {
    fetch('../controllers/TeachingReportController.php?action=attendance_log&id=' + encodeURIComponent(reportId))
      .then(res => res.json())
      .then(logs => {
        const groups = { absent: [], sick: [], personal: [], activity: [], truant: [], late: [] };
        if (Array.isArray(logs)) {
          logs.forEach(l => {
            const s = (l.status || '').trim();
            const label = l.student_name || l.fullname || l.Stu_no || l.student_id;
            const entry = label ? `${label}` : `${l.student_id}`;
            if (s === 'ขาดเรียน') groups.absent.push(entry);
            else if (s === 'ลาป่วย') groups.sick.push(entry);
            else if (s === 'ลากิจ') groups.personal.push(entry);
            else if (s === 'เข้าร่วมกิจกรรม') groups.activity.push(entry);
            else if (s === 'โดดเรียน') groups.truant.push(entry);
            else if (s === 'มาสาย') groups.late.push(entry);
          });
        }

        const categories = [
          { title: 'ขาดเรียน', emoji: '❌', accent: 'text-rose-500', list: groups.absent },
          { title: 'ลาป่วย', emoji: '🤒', accent: 'text-sky-500', list: groups.sick },
          { title: 'ลากิจ', emoji: '📝', accent: 'text-indigo-500', list: groups.personal },
          { title: 'กิจกรรม', emoji: '🎉', accent: 'text-purple-500', list: groups.activity },
          { title: 'โดดเรียน', emoji: '🚫', accent: 'text-gray-800', list: groups.truant }
        ];

        const cards = categories.map(cat => `
          <div class="glow-card rounded-2xl p-5 bg-white/80 dark:bg-gray-800/60 border">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 font-semibold ${cat.accent}">${cat.emoji} ${cat.title}</div>
              <div class="text-2xl font-black ${cat.accent}">${cat.list.length}</div>
            </div>
            <div class="mt-3 text-sm max-h-48 overflow-auto pr-1">
              ${cat.list.length ? `<ul class="space-y-1 ml-4 list-disc">${cat.list.map(item => `<li>${item}</li>`).join('')}</ul>` : '<div class="text-slate-400">ไม่มีข้อมูล</div>'}
            </div>
          </div>`).join('');

        const anyIssues = groups.absent.length + groups.sick.length + groups.personal.length + groups.activity.length + groups.truant.length;

        const html = `
          <div class="relative max-w-5xl mx-auto">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 blur-3xl rounded-3xl"></div>
            <div class="relative backdrop-blur-2xl rounded-3xl border p-6 md:p-8 space-y-6 bg-transparent">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Attendance Detail</p>
                  <h3 class="text-2xl font-extrabold">📋 รายชื่อนักเรียน</h3>
                </div>
                <div class="text-sm text-slate-600">รวมเหตุการณ์พิเศษ: <strong class="text-indigo-600">${anyIssues}</strong></div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                ${cards}
              </div>

              <div class="text-right">
                <button class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700" id="closeAttendanceInner">ปิด</button>
              </div>
            </div>
          </div>
        `;

        const contentEl = document.getElementById('attendanceModalContent');
        contentEl.innerHTML = html;
        const modal = document.getElementById('attendanceModal');
        const inner = document.getElementById('attendanceModalInner');
        modal.classList.remove('hidden');
        setTimeout(() => inner.classList.add('show'), 10);
        const closeBtn = document.getElementById('closeAttendanceInner');
        if (closeBtn) closeBtn.addEventListener('click', () => { inner.classList.remove('show'); setTimeout(() => modal.classList.add('hidden'), 220); });
      });
  }

  // Modal logic
  const modalReport = document.getElementById('modalAddReport');
  const btnAddReport = document.getElementById('btnAddReport');
  const btnCloseReport = document.getElementById('closeModalAddReport');
  const btnCancelReport = document.getElementById('cancelAddReport');
  const formReport = document.getElementById('formAddReport');

  // Attendance Modal logic
  const attendanceModal = document.getElementById('attendanceModal');
  const closeAttendanceModal = document.getElementById('closeAttendanceModal');
  closeAttendanceModal.addEventListener('click', () => {
    const inner = document.getElementById('attendanceModalInner');
    if (inner) inner.classList.remove('show');
    setTimeout(() => attendanceModal.classList.add('hidden'), 220);
  });

  // Close modal on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !attendanceModal.classList.contains('hidden')) {
      closeAttendanceModal.click();
    }
  });

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
    const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
    checkedPeriods[room] = Array.from(document.querySelectorAll(`input[name="periods[${key}][]"]:checked`)).map(cb => {
      const [start, end, day] = cb.value.split('|');
      return { period_start: start, period_end: end, day_of_week: day };
    });
  });

  // Validation: require subject, date, rooms and at least one period per room
  if (!subjectId) {
    Swal.close();
    Swal.fire('ผิดพลาด', 'กรุณาเลือกวิชา', 'error');
    return;
  }
  if (!reportDate) {
    Swal.close();
    Swal.fire('ผิดพลาด', 'กรุณาเลือกวันที่', 'error');
    return;
  }
  if (!checkedRooms.length) {
    Swal.close();
    Swal.fire('ผิดพลาด', 'กรุณาเลือกห้องเรียนอย่างน้อย 1 ห้อง', 'error');
    return;
  }
  for (const room of checkedRooms) {
    if (!checkedPeriods[room] || checkedPeriods[room].length === 0) {
      Swal.close();
      Swal.fire('ผิดพลาด', `กรุณาเลือกอย่างน้อย 1 คาบสำหรับ ${room}`, 'error');
      return;
    }
  }

  const attendance = {};
  document.querySelectorAll('[name^="attendance["]').forEach(el => {
    const match = el.name.match(/^attendance\[(.+?)\]\[(.+?)\]$/);
    if (!match) return;
    const room = match[1];
    const stuId = match[2];
    attendance[stuId] = el.value;
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
  document.querySelectorAll('[name^="attendance["]').forEach(input => {
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
      absent: 'ขาดเรียน',
      truant: 'โดดเรียน'
    };
    attendanceLogs.push({ student_id: stuId, status: map[status] || status, class_room: room });
  });

  const uploadImages = () => {
    return new Promise((resolve, reject) => {
      const imagesByRoom = {};
      checkedRooms.forEach(room => {
        const key = room.replace(/\s+/g, '_').replace(/[^A-Za-z0-9_\-]/g, '');
        imagesByRoom[room] = {
          image1: formData.get(`image1_${key}`),
          image2: formData.get(`image2_${key}`)
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
