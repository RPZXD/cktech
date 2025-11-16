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
$teacherId = $_SESSION['user']['Teach_id'] ?? ($_SESSION['username'] ?? null);

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
  .attendance-card {
    background: rgba(255,255,255,0.92);
    border-radius: 18px;
    box-shadow: 0 20px 45px rgba(15,23,42,0.12);
    border: 1px solid rgba(148,163,184,0.25);
  }
  .dark .attendance-card,
  body.dark-mode .attendance-card {
    background: rgba(15,23,42,0.85);
    border-color: rgba(148,163,184,0.35);
  }
  .filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }
  .filter-grid label {
    font-weight: 600;
    color: #0f172a;
    display: flex;
    gap: 0.35rem;
    align-items: center;
  }
  body.dark-mode .filter-grid label {
    color: #e2e8f0;
  }
  .filter-grid select,
  .filter-grid input[type="month"],
  .filter-grid input[type="text"] {
    width: 100%;
    margin-top: 0.45rem;
    border-radius: 0.8rem;
    border: 1px solid rgba(148,163,184,0.6);
    padding: 0.65rem 0.9rem;
    background: rgba(248,250,252,0.8);
  }
  body.dark-mode .filter-grid select,
  body.dark-mode .filter-grid input[type="month"],
  body.dark-mode .filter-grid input[type="text"] {
    background: #0f172a;
    border-color: rgba(148,163,184,0.4);
    color: #e2e8f0;
  }
  .table-shell {
    margin-top: 1.5rem;
    border-radius: 22px;
    background: rgba(255,255,255,0.92);
    padding: 0;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7), 0 18px 35px rgba(15,23,42,0.08);
    overflow: hidden;
  }
  .dark .table-shell,
  body.dark-mode .table-shell {
    background: rgba(15,23,42,0.85);
    box-shadow: 0 18px 35px rgba(0,0,0,0.35);
  }
  .table-scroll {
    overflow-x: auto;
    padding: 1rem;
  }
  .attendance-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 960px;
  }
  .attendance-table th,
  .attendance-table td {
    padding: 0.55rem 0.65rem;
    border-bottom: 1px solid rgba(148,163,184,0.25);
    text-align: center;
    font-size: 0.9rem;
  }
  .attendance-table thead th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: rgba(241,245,249,0.95);
    color: #0f172a;
    position: sticky;
    top: 0;
    z-index: 4;
  }
  .dark .attendance-table thead th,
  body.dark-mode .attendance-table thead th {
    background: rgba(15,23,42,0.95);
    color: #e2e8f0;
  }
  .attendance-table .name-col {
    text-align: left;
    min-width: 260px;
    background: inherit;
  }
  .sticky-col {
    position: sticky;
    left: 0;
    z-index: 5;
  }
  .attendance-table tbody tr:hover td {
    background: rgba(59,130,246,0.06);
  }
  body.dark-mode .attendance-table tbody tr:hover td {
    background: rgba(96,165,250,0.12);
  }
  .status-cell {
    font-size: 1.05rem;
    font-weight: 600;
    border-radius: 8px;
    min-width: 44px;
  }
  .status-present { background: #dcfce7; color: #047857; }
  .status-late { background: #fef3c7; color: #92400e; }
  .status-absent { background: #fee2e2; color: #b91c1c; }
  .status-sick { background: #dbeafe; color: #1d4ed8; }
  .status-personal { background: #e0e7ff; color: #3730a3; }
  .status-activity { background: #fce7f3; color: #9d174d; }
  .status-truant { background: #ffe4e6; color: #9f1239; }
  .status-other { background: #e2e8f0; color: #334155; }
  .status-empty { color: #94a3b8; font-weight: 500; }
  .summary-cell {
    font-weight: 700;
    font-size: 0.95rem;
    color: #0f172a;
  }
  body.dark-mode .summary-cell { color: #f8fafc; }
  .grid-empty,
  .grid-loading {
    padding: 3rem 1rem;
    text-align: center;
    color: #475569;
  }
  body.dark-mode .grid-empty,
  body.dark-mode .grid-loading { color: #cbd5f5; }
  .legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1.25rem;
  }
  .legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.85rem;
    background: rgba(248,250,252,0.9);
    color: #0f172a;
    border: 1px solid rgba(203,213,225,0.7);
  }
  body.dark-mode .legend-item {
    background: rgba(15,23,42,0.85);
    color: #e2e8f0;
    border-color: rgba(100,116,139,0.6);
  }
  .attendance-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    margin-bottom: 1rem;
  }
  .meta-chip {
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    background: rgba(59,130,246,0.1);
    color: #1d4ed8;
    font-weight: 600;
    font-size: 0.85rem;
  }
  body.dark-mode .meta-chip {
    background: rgba(96,165,250,0.2);
    color: #bfdbfe;
  }
  .cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    padding: 0.65rem 1.4rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: transform 120ms ease;
  }
  .cta-btn:hover { transform: translateY(-1px); }
  .cta-primary {
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: #fff;
    box-shadow: 0 10px 25px rgba(59,130,246,0.35);
  }
  .cta-ghost {
    background: transparent;
    border: 1px dashed rgba(59,130,246,0.5);
    color: #2563eb;
  }
  body.dark-mode .cta-ghost {
    border-color: rgba(191,219,254,0.4);
    color: #bfdbfe;
  }
  .loader {
    width: 1.15rem;
    height: 1.15rem;
    border-radius: 999px;
    border: 3px solid rgba(59,130,246,0.25);
    border-top-color: #2563eb;
    animation: spin 0.8s linear infinite;
    display: inline-block;
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
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
      <div class="container-fluid space-y-6">
        <div class="attendance-card p-6 aurora-wrapper">
          <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
              <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Attendance Intelligence</p>
              <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                <span>สมุดเช็คชื่อรายเดือน</span>
                <span class="text-3xl">📚</span>
              </h2>
            </div>
            <div class="text-sm text-slate-500 dark:text-slate-300 flex items-center gap-2">
              <i class="far fa-clock"></i>
              <span id="filterTimestamp">พร้อมใช้งาน</span>
            </div>
          </div>
          <div class="filter-grid">
            <div>
              <label for="subjectSelect"><i class="fas fa-book"></i> รายวิชา</label>
              <select id="subjectSelect">
                <option value="">เลือกรายวิชา</option>
              </select>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-2" id="subjectHint">เลือกรายวิชาเพื่อดึงรายชื่อห้องที่เปิดสอน</p>
            </div>
            <div>
              <label for="classSelect"><i class="fas fa-users"></i> ห้องเรียน</label>
              <select id="classSelect" disabled>
                <option value="">เลือกวิชาเพื่อแสดงห้อง</option>
              </select>
              <div id="customClassGroup" class="mt-2 hidden">
                <input type="text" id="customClassInput" placeholder="พิมพ์ชื่อห้อง เช่น 2/1 หรือ ห้อง 201" />
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">ใช้ตัวเลือกนี้หากรายวิชาไม่ได้บันทึกห้องในตารางสอน</p>
              </div>
            </div>
            <div>
              <label for="monthInput"><i class="far fa-calendar"></i> เดือนที่ต้องการดู</label>
              <input type="month" id="monthInput" value="<?php echo date('Y-m'); ?>" />
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">ระบบจะสแกนข้อมูลตามเดือนที่เลือก</p>
            </div>
            <div>
              <label><i class="fas fa-chalkboard-teacher"></i> ผู้สอน</label>
              <input type="text" value="<?php echo htmlspecialchars($_SESSION['user']['Teach_name'] ?? ($_SESSION['username'] ?? '')); ?>" readonly class="bg-slate-100 dark:bg-slate-900" />
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">ข้อมูลถูกกรองตามบัญชีผู้สอนที่เข้าสู่ระบบ</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-3 mt-6">
            <button class="cta-btn cta-primary" id="loadAttendanceBtn">
              <i class="fas fa-sync"></i>
              โหลดข้อมูล
            </button>
            <button class="cta-btn cta-ghost" id="resetFilterBtn">
              <i class="fas fa-undo"></i>
              รีเซ็ตตัวเลือก
            </button>
            <button class="cta-btn cta-ghost" id="exportCsvBtn" title="Export to CSV">
              <i class="fas fa-file-excel"></i>
              ส่งออก Excel
            </button>
            <button class="cta-btn cta-ghost" id="printGridBtn" title="พิมพ์ตาราง">
              <i class="fas fa-print"></i>
              พิมพ์
            </button>
          </div>
        </div>

        <div class="table-shell">
          <div class="attendance-meta" id="gridMeta">
            <span class="meta-chip">ยังไม่ได้เลือกข้อมูล</span>
          </div>
          <div class="table-scroll" id="gridHost">
            <div class="grid-empty">
              <p class="text-base font-semibold text-slate-600 dark:text-slate-200">ยังไม่มีข้อมูลสำหรับแสดง</p>
              <p class="text-sm text-slate-500 dark:text-slate-400">โปรดเลือกรายวิชา ห้องเรียน และเดือน จากนั้นกด "โหลดข้อมูล"</p>
            </div>
          </div>
          <div class="legend" id="legendHost"></div>
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
  (function(){
    const teacherId = <?php echo json_encode($teacherId); ?>;
    const subjectSelect = document.getElementById('subjectSelect');
    const classSelect = document.getElementById('classSelect');
    const customClassGroup = document.getElementById('customClassGroup');
    const customClassInput = document.getElementById('customClassInput');
    const monthInput = document.getElementById('monthInput');
    const subjectHint = document.getElementById('subjectHint');
    const loadBtn = document.getElementById('loadAttendanceBtn');
    const resetBtn = document.getElementById('resetFilterBtn');
    const exportBtn = document.getElementById('exportCsvBtn');
    const printBtn = document.getElementById('printGridBtn');
    const gridHost = document.getElementById('gridHost');
    const legendHost = document.getElementById('legendHost');
    const gridMeta = document.getElementById('gridMeta');
    const filterTimestamp = document.getElementById('filterTimestamp');

    const state = {
      subjects: [],
      activeSubject: null,
      statusMeta: {},
      summaryColumns: []
    };

    document.addEventListener('DOMContentLoaded', () => {
      updateTimestamp();
      wireEvents();
      loadSubjects();
    });

    function updateTimestamp() {
      if (filterTimestamp) {
        const formatter = new Intl.DateTimeFormat('th-TH', { dateStyle: 'medium', timeStyle: 'short' });
        filterTimestamp.textContent = formatter.format(new Date());
      }
    }

    function wireEvents() {
      subjectSelect.addEventListener('change', handleSubjectChange);
      classSelect.addEventListener('change', handleClassChange);
      loadBtn.addEventListener('click', handleLoad);
      resetBtn.addEventListener('click', resetFilters);
      if (exportBtn) exportBtn.addEventListener('click', () => exportGridToCSV());
      if (printBtn) printBtn.addEventListener('click', () => printGrid());
    }

    async function loadSubjects() {
      subjectHint.textContent = 'กำลังโหลดรายวิชาของคุณ...';
      try {
        const params = new URLSearchParams({ action: 'list', onlyOpen: 1 });
        if (teacherId) params.append('teacherId', teacherId);
        const response = await fetch('../controllers/SubjectController.php?' + params.toString(), { credentials: 'same-origin' });
        const data = await response.json();
        state.subjects = Array.isArray(data) ? data : [];
        renderSubjectOptions();
        subjectHint.textContent = 'เลือกวิชาเพื่อเริ่มใช้งาน';
      } catch (error) {
        console.error(error);
        subjectHint.textContent = 'ไม่สามารถโหลดรายวิชาได้ กรุณาลองใหม่อีกครั้ง';
      }
    }

    function renderSubjectOptions() {
      const options = ['<option value="">เลือกรายวิชา</option>'];
      state.subjects.forEach(sub => {
        options.push(`<option value="${sub.id}">${sub.code ?? ''} ${sub.name}</option>`);
      });
      subjectSelect.innerHTML = options.join('');
    }

    function handleSubjectChange() {
      const subjectId = subjectSelect.value;
      const subject = state.subjects.find(item => String(item.id) === String(subjectId));
      state.activeSubject = subject || null;
      if (!subject) {
        subjectHint.textContent = 'เลือกวิชาเพื่อดึงข้อมูล';
        classSelect.innerHTML = '<option value="">เลือกวิชาเพื่อแสดงห้อง</option>';
        classSelect.disabled = true;
        customClassGroup.classList.add('hidden');
        return;
      }
      subjectHint.textContent = `${subject.code || ''} | ระดับ ${subject.level || '-'} (${subject.subject_type || 'ทั่วไป'})`;
      populateRooms(subject);
    }

    function populateRooms(subject) {
      const rooms = [];
      const seen = new Set();
      if (subject && Array.isArray(subject.class_periods)) {
        subject.class_periods.forEach(period => {
          if (!period || !period.class_room) return;
          const room = String(period.class_room).trim();
          if (!room || seen.has(room)) return;
          seen.add(room);
          rooms.push(room);
        });
      }
      classSelect.disabled = false;
      customClassGroup.classList.add('hidden');
      if (!rooms.length) {
        classSelect.innerHTML = '<option value="__custom__">พิมพ์ห้องเรียนเอง</option>';
        classSelect.value = '__custom__';
        customClassGroup.classList.remove('hidden');
        customClassInput.focus();
        return;
      }
      const opts = ['<option value="">เลือกห้องเรียน</option>'];
      rooms.forEach(room => {
        opts.push(`<option value="${room}">${room}</option>`);
      });
      opts.push('<option value="__custom__">พิมพ์ห้องเรียนเอง…</option>');
      classSelect.innerHTML = opts.join('');
      classSelect.value = rooms[0];
    }

    function handleClassChange() {
      if (classSelect.value === '__custom__') {
        customClassGroup.classList.remove('hidden');
        customClassInput.focus();
      } else {
        customClassGroup.classList.add('hidden');
      }
    }

    function getSelectedClassRoom() {
      if (classSelect.value === '__custom__') {
        return customClassInput.value.trim();
      }
      return classSelect.value.trim();
    }

    function handleLoad() {
      const subjectId = subjectSelect.value;
      const classRoom = getSelectedClassRoom();
      const month = monthInput.value;
      if (!subjectId) {
        toast('กรุณาเลือกรายวิชา');
        return;
      }
      if (!classRoom) {
        toast('กรุณาเลือกหรือระบุห้องเรียน');
        return;
      }
      if (!month) {
        toast('กรุณาเลือกเดือนที่ต้องการดู');
        return;
      }
      fetchAttendanceGrid({ subjectId, classRoom, month });
    }

    async function fetchAttendanceGrid({ subjectId, classRoom, month }) {
      setGridLoading(true, 'กำลังรวบรวมข้อมูลจากรายงานการสอน');
      const params = new URLSearchParams({
        action: 'calendar_grid',
        subject_id: subjectId,
        class_room: classRoom,
        month: month
      });
      if (teacherId) params.append('teacher_id', teacherId);
      try {
        const response = await fetch('../controllers/AttendanceController.php?' + params.toString(), {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        const json = await response.json();
        if (!json.success) throw new Error(json.error || 'ไม่สามารถโหลดข้อมูล');
        const payload = json.data || {};
        state.statusMeta = payload.summary?.status_meta || {};
        state.summaryColumns = payload.summary?.columns || [];
        renderGrid(payload, classRoom);
      } catch (error) {
        console.error(error);
        toast(error.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
        showEmpty(error.message || 'ไม่พบข้อมูล');
      } finally {
        setGridLoading(false);
      }
    }

    function renderGrid(data, fallbackClass) {
      if (!data || !Array.isArray(data.days) || data.days.length === 0) {
        showEmpty('ไม่พบรายงานในเดือนที่เลือก');
        return;
      }
      const students = Array.isArray(data.students) ? data.students : [];
      if (!students.length) {
        showEmpty('ยังไม่มีข้อมูลเช็คชื่อในเดือนนี้');
        return;
      }
      const days = Array.isArray(data.days) ? data.days : [];
      // Keep only days that actually have report/status data. Fallback to all days if none found.
      let daysWithData = days.filter(day => {
        if (day == null) return false;
        if (day.has_report || day.report_count || day.reports_count || day.attendance_count) return true;
        // check if any student has a status for this date
        if (students.some(s => s && s.statuses && s.statuses[day.date])) return true;
        return false;
      });
      if (!daysWithData.length) daysWithData = days.slice();

      const headerDays = daysWithData.map(day => `<th title="${day.weekday_th || day.weekday}" class="day-head">${day.day}<div class="text-[11px] text-slate-500">${day.weekday_th || ''}</div></th>`).join('');
      const summaryCols = (state.summaryColumns || []).map(col => `<th class="summary-cell" title="${col.label}">${col.emoji}</th>`).join('');
      const summaryColspan = state.summaryColumns.length || 0;
      const headSecond = `<tr>${headerDays}${summaryCols}</tr>`;
      const headFirst = `<tr><th rowspan="2" class="sticky-col name-col">ชื่อ - สกุล</th><th colspan="${daysWithData.length}" class="text-xs tracking-wide">วันในเดือน</th>${summaryColspan ? `<th colspan="${summaryColspan}" class="text-xs tracking-wide">สรุปรายบุคคล</th>` : ''}</tr>`;

      const bodyRows = students.map((student, idx) => {
        const statusCells = daysWithData.map(day => {
          const status = student.statuses?.[day.date] || '';
          const visual = getStatusVisual(status);
          return `<td class="status-cell ${visual.className}" title="${visual.label}">${visual.icon}</td>`;
        }).join('');
        const summaryCells = (state.summaryColumns || []).map(col => {
          const value = student.totals?.[col.key] ?? 0;
          return `<td class="summary-cell" title="${col.label}">${value}</td>`;
        }).join('');
        const no = student.student_no ? `(${student.student_no})` : `#${idx + 1}`;
        const name = student.student_name || 'ไม่พบชื่อ';
        return `<tr>
          <td class="sticky-col name-col">
            <div class="font-semibold text-indigo-500 dark:text-indigo-400">${no} ${name}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">ID: ${student.student_id}</div>
          </td>
          ${statusCells}
          ${summaryCells}
        </tr>`;
      }).join('');

      const tableHtml = `<table class="attendance-table"><thead>${headFirst}${headSecond}</thead><tbody>${bodyRows}</tbody></table>`;
      gridHost.innerHTML = tableHtml;
      const monthLabel = data.meta?.month_label || monthInput.value;
      const classLabel = data.meta?.class_room || fallbackClass;
      const reportCount = data.meta?.report_dates?.length || daysWithData.length || 0;
      const studentCount = data.meta?.student_count || students.length;
      gridMeta.innerHTML = `
        <span class="meta-chip"><i class="far fa-calendar-alt mr-1"></i> ${monthLabel}</span>
        <span class="meta-chip"><i class="fas fa-door-open mr-1"></i> ห้อง ${classLabel}</span>
        <span class="meta-chip"><i class="fas fa-user-graduate mr-1"></i> ${studentCount} คน</span>
        <span class="meta-chip"><i class="fas fa-clipboard-check mr-1"></i> ${reportCount} วันบันทึก</span>`;
      renderLegend();
    }

    function renderLegend() {
      const entries = Object.entries(state.statusMeta || {});
      if (!entries.length) {
        legendHost.innerHTML = '<span class="text-sm text-slate-500">ยังไม่มีสถานะให้แสดง</span>';
        return;
      }
      legendHost.innerHTML = entries.map(([key, meta]) => {
        return `<div class="legend-item ${meta.cell_class || ''}"><span>${meta.emoji || '•'}</span><span>${meta.label || key}</span></div>`;
      }).join('');
    }

    function getStatusVisual(status) {
      if (!status) {
        return { icon: '·', label: 'ไม่มีข้อมูล', className: 'status-empty' };
      }
      const meta = state.statusMeta[status];
      if (!meta) {
        return { icon: '•', label: status, className: 'status-other' };
      }
      return {
        icon: meta.emoji || '•',
        label: meta.label || status,
        className: meta.cell_class || 'status-other'
      };
    }

    function setGridLoading(isLoading, message) {
      if (isLoading) {
        gridHost.innerHTML = `<div class="grid-loading flex flex-col items-center gap-3"><span class="loader"></span><span>${message || 'กำลังโหลด...'}</span></div>`;
      }
    }

    function showEmpty(text) {
      gridHost.innerHTML = `<div class="grid-empty"><p class="font-semibold mb-1">${text}</p><p class="text-sm text-slate-500">ลองเปลี่ยนตัวกรองหรือเลือกเดือนอื่น</p></div>`;
      gridMeta.innerHTML = '<span class="meta-chip">ยังไม่พบข้อมูล</span>';
      legendHost.innerHTML = '';
    }

    function resetFilters() {
      subjectSelect.value = '';
      classSelect.innerHTML = '<option value="">เลือกรายวิชา</option>';
      classSelect.disabled = true;
      customClassGroup.classList.add('hidden');
      customClassInput.value = '';
      monthInput.value = "<?php echo date('Y-m'); ?>";
      subjectHint.textContent = 'เลือกรายวิชาเพื่อเริ่มใช้งาน';
      showEmpty('ยังไม่มีการเลือกข้อมูล');
    }

    function toast(message) {
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2300,
          icon: 'info',
          title: message
        });
      } else {
        alert(message);
      }
    }

    // Export the rendered attendance table to CSV (Excel-friendly)
    function exportGridToCSV(filename = null) {
      const table = gridHost.querySelector('table');
      if (!table) {
        toast('ยังไม่มีตารางให้ส่งออก');
        return;
      }
      const rows = Array.from(table.querySelectorAll('thead tr, tbody tr'));
      const csvLines = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => {
          // preserve text, convert inner newlines and trim
          let text = cell.innerText.replace(/\r?\n/g, ' ').trim();
          // escape double quotes
          text = text.replace(/"/g, '""');
          // wrap if necessary
          if (text.indexOf(',') >= 0 || text.indexOf('"') >= 0) {
            return `"${text}"`;
          }
          return text;
        }).join(',');
      });

      const csvContent = csvLines.join('\r\n');
      const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
      const a = document.createElement('a');
      const url = URL.createObjectURL(blob);
      a.href = url;
      const monthLabel = (document.getElementById('monthInput') || {}).value || new Date().toISOString().slice(0,7);
      filename = filename || `attendance_${monthLabel}.csv`;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 500);
    }

    // Print the attendance grid (opens printable window)
    function printGrid() {
      const table = gridHost.querySelector('table');
      if (!table) {
        toast('ยังไม่มีตารางให้พิมพ์');
        return;
      }
      const printWindow = window.open('', '_blank');
      const styles = `
        <style>
          body{ font-family: Arial, Helvetica, sans-serif; padding:20px; }
          table{ border-collapse: collapse; width:100%; }
          th, td{ border:1px solid #ddd; padding:6px 8px; text-align:center; }
          th{ background:#f3f4f6; }
          .name-col { text-align:left; }
        </style>
      `;
      const title = `<h3>สมุดเช็คชื่อรายเดือน</h3><p>ดาวน์โหลด/พิมพ์ ณ ${new Date().toLocaleString('th-TH')}</p>`;
      printWindow.document.open();
      printWindow.document.write(`<!doctype html><html><head><meta charset="utf-8">${styles}</head><body>${title}${table.outerHTML}</body></html>`);
      printWindow.document.close();
      printWindow.focus();
      setTimeout(() => { printWindow.print(); /* don't auto-close to allow user */ }, 400);
    }
  })();
</script>
<?php require_once('script.php');?>
</body>
</html>
