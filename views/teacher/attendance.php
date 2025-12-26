<?php
/**
 * Attendance View
 * MVC Pattern - View layer for teacher attendance page
 * Monthly attendance grid with modern UI
 */

// Pass PHP variables to JavaScript
$jsTeacherId = json_encode($teacherId ?? null);
$jsTeacherName = json_encode($teacherName ?? '');
$currentMonth = date('Y-m');
?>

<style>
    /* Aurora Background Effect */
    .attendance-wrapper {
        position: relative;
        isolation: isolate;
    }
    .attendance-wrapper::before {
        content: '';
        position: absolute;
        inset: -40px;
        background: linear-gradient(135deg, rgba(14,165,233,0.3), rgba(139,92,246,0.2));
        filter: blur(60px);
        z-index: -1;
        border-radius: 999px;
        animation: attendanceGlow 10s ease-in-out infinite;
    }
    @keyframes attendanceGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    
    /* Table Styles */
    .attendance-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .attendance-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 800px;
    }
    .attendance-table th,
    .attendance-table td {
        padding: 0.5rem 0.4rem;
        text-align: center;
        font-size: 0.8rem;
        border-bottom: 1px solid rgba(148,163,184,0.2);
    }
    .attendance-table thead th {
        font-weight: 600;
        font-size: 0.75rem;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .attendance-table .name-col {
        text-align: left;
        min-width: 200px;
        position: sticky;
        left: 0;
        z-index: 5;
        background: white;
    }
    .dark .attendance-table .name-col {
        background: #1e293b;
    }
    .attendance-table tbody tr:hover td {
        background: rgba(99, 102, 241, 0.05);
    }
    
    /* Status Cells */
    .status-cell {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .status-present { background: #dcfce7; color: #047857; }
    .status-late { background: #fef3c7; color: #92400e; }
    .status-absent { background: #fee2e2; color: #b91c1c; }
    .status-sick { background: #dbeafe; color: #1d4ed8; }
    .status-personal { background: #e0e7ff; color: #3730a3; }
    .status-activity { background: #fce7f3; color: #9d174d; }
    .status-truant { background: #ffe4e6; color: #9f1239; }
    .status-empty { color: #94a3b8; }
    
    /* Summary Cells */
    .summary-cell {
        font-weight: 700;
        font-size: 0.85rem;
        background: rgba(241,245,249,0.5);
    }
    
    /* Legend Pills */
    .legend-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: white;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .attendance-table th,
        .attendance-table td {
            padding: 0.3rem 0.25rem;
            font-size: 0.7rem;
        }
        .status-cell {
            width: 26px;
            height: 26px;
            font-size: 0.75rem;
        }
        .attendance-table .name-col {
            min-width: 140px;
            font-size: 0.7rem;
        }
    }
    
    /* Loader */
    .loader-spin {
        width: 2rem;
        height: 2rem;
        border: 3px solid rgba(99, 102, 241, 0.2);
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Mobile/Desktop Toggle */
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards { display: flex !important; }
    }
    @media (min-width: 769px) {
        .desktop-table { display: block !important; }
        .mobile-cards { display: none !important; }
    }
    
    /* Student Card */
    .student-card {
        background: white;
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .dark .student-card {
        background: #1e293b;
        border-color: rgba(255,255,255,0.1);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<!-- Page Header -->
<div class="attendance-wrapper mb-4 md:mb-6">
    <div class="glass rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-xl border border-white/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Title -->
            <div class="text-center md:text-left">
                <h1 class="text-xl md:text-2xl lg:text-3xl font-extrabold flex flex-wrap items-center justify-center md:justify-start gap-2 text-slate-900 dark:text-white">
                    <span class="text-2xl md:text-3xl">📚</span>
                    <span class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">
                        สมุดเช็คชื่อรายเดือน
                    </span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-xs md:text-sm mt-1">
                    ดูสถานะการเข้าเรียนของนักเรียนตลอดทั้งเดือน
                </p>
            </div>
            
            <!-- Timestamp -->
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <i class="far fa-clock"></i>
                <span id="filterTimestamp">พร้อมใช้งาน</span>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="glow-card glass rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-xl border border-white/20 mb-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Subject -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600">📖</span>
                รายวิชา
            </label>
            <select id="subjectSelect" 
                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                <option value="">เลือกรายวิชา</option>
            </select>
            <p id="subjectHint" class="text-xs text-gray-500 mt-1">เลือกวิชาเพื่อเริ่มใช้งาน</p>
        </div>
        
        <!-- Class Room -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                <span class="w-6 h-6 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600">🏫</span>
                ห้องเรียน
            </label>
            <select id="classSelect" disabled
                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all disabled:opacity-50">
                <option value="">เลือกวิชาก่อน</option>
            </select>
            <div id="customClassGroup" class="mt-2 hidden">
                <input type="text" id="customClassInput" placeholder="พิมพ์ชื่อห้อง เช่น 2/1"
                    class="w-full px-4 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700" />
            </div>
        </div>
        
        <!-- Month -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                <span class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center text-green-600">📅</span>
                เดือน
            </label>
            <input type="month" id="monthInput" value="<?php echo $currentMonth; ?>"
                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all" />
        </div>
        
        <!-- Teacher -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center text-amber-600">👨‍🏫</span>
                ผู้สอน
            </label>
            <input type="text" value="<?php echo htmlspecialchars($teacherName); ?>" readonly
                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300" />
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-2 mt-4">
        <button id="loadAttendanceBtn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
            <i class="fas fa-sync text-sm"></i>
            <span class="hidden sm:inline">โหลดข้อมูล</span>
            <span class="sm:hidden">โหลด</span>
        </button>
        <button id="resetFilterBtn" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
            <i class="fas fa-undo text-sm"></i>
            <span class="hidden sm:inline">รีเซ็ต</span>
        </button>
        <button id="exportCsvBtn" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
            <i class="fas fa-file-excel text-sm text-green-600"></i>
            <span class="hidden sm:inline">ส่งออก Excel</span>
        </button>
        <button id="printGridBtn" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
            <i class="fas fa-print text-sm text-blue-600"></i>
            <span class="hidden sm:inline">พิมพ์</span>
        </button>
    </div>
</div>

<!-- Data Grid Card -->
<div class="glow-card glass rounded-2xl md:rounded-3xl p-3 md:p-6 shadow-xl border border-white/20 bg-white/90 dark:bg-gray-800/90">
    <!-- Meta Info -->
    <div id="gridMeta" class="flex flex-wrap gap-2 mb-4">
        <span class="legend-pill bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
            <i class="fas fa-info-circle"></i> ยังไม่ได้เลือกข้อมูล
        </span>
    </div>
    
    <!-- Desktop Table -->
    <div id="gridHost" class="desktop-table attendance-table-wrapper">
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <div class="text-4xl mb-3">📊</div>
            <p class="font-semibold">ยังไม่มีข้อมูลสำหรับแสดง</p>
            <p class="text-sm mt-1">เลือกรายวิชา ห้องเรียน และเดือน แล้วกด "โหลดข้อมูล"</p>
        </div>
    </div>
    
    <!-- Mobile Cards -->
    <div id="mobileCardsHost" class="mobile-cards flex-col gap-3" style="display: none;">
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <div class="text-4xl mb-3">📊</div>
            <p class="font-semibold">ยังไม่มีข้อมูลสำหรับแสดง</p>
            <p class="text-sm mt-1">เลือกรายวิชา ห้องเรียน และเดือน แล้วกด "โหลดข้อมูล"</p>
        </div>
    </div>
    
    <!-- Legend -->
    <div id="legendHost" class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"></div>
</div>

<!-- Pass PHP variables to JavaScript -->
<script>
    window.ATTENDANCE_CONFIG = {
        teacherId: <?php echo $jsTeacherId; ?>,
        teacherName: <?php echo $jsTeacherName; ?>,
        currentMonth: '<?php echo $currentMonth; ?>'
    };
</script>

<!-- External JS -->
<script src="js/attendance.js?v=3"></script>
