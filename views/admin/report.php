<?php
/**
 * Admin Report View
 * MVC Pattern - View for viewing all teaching reports
 * Enhanced UI/UX with Tailwind CSS - Mobile Responsive
 */
?>

<style>
    /* Floating Animation */
    .float-animation { animation: floating 3s ease-in-out infinite; }
    @keyframes floating { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    
    /* Table scroll */
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.5); border-radius: 4px; }
    
    /* Status badges */
    .report-badge { transition: all 0.2s ease; }
    .report-badge:hover { transform: scale(1.05); }
</style>

<!-- Page Header -->
<div class="mb-6 md:mb-8">
    <div class="relative glass rounded-2xl md:rounded-3xl p-5 md:p-8 shadow-xl overflow-hidden">
        <div class="absolute -top-20 -right-20 w-40 h-40 md:w-60 md:h-60 bg-gradient-to-br from-emerald-400/20 to-green-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-3xl md:text-4xl float-animation">📑</span>
                    <span class="px-3 py-1 bg-emerald-500 text-white text-xs font-bold rounded-full uppercase tracking-wider">Teaching Reports</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                    รายงานการสอนทั้งหมด
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm md:text-base">
                    ตรวจสอบและดูรายงานการสอนของครูทุกคน
                </p>
            </div>
            <div class="flex flex-wrap gap-2 md:gap-3">
                <button id="btnExportExcel" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium active:scale-95">
                    <i class="fas fa-file-excel mr-2"></i>
                    <span>ส่งออก Excel</span>
                </button>
                <button id="btnPrintReport" class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 shadow hover:shadow-lg transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium">
                    <i class="fas fa-print mr-2"></i>
                    <span>พิมพ์</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">รายงานทั้งหมด</p>
                <h3 id="statTotalReports" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-file-alt text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">รายงานวันนี้</p>
                <h3 id="statTodayReports" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-day text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-violet-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">ครูที่ส่ง</p>
                <h3 id="statTeachersSubmitted" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard-teacher text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-amber-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">เฉลี่ยนักเรียนขาด</p>
                <h3 id="statAvgAbsent" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-times text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="glass rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg mb-6">
    <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="fas fa-filter text-emerald-500"></i> ตัวกรองข้อมูล
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ครู</label>
            <select id="filter-teacher" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">กลุ่มสาระ</label>
            <select id="filter-department" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ระดับชั้น</label>
            <select id="filter-level" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
                <option value="ม.1">ม.1</option>
                <option value="ม.2">ม.2</option>
                <option value="ม.3">ม.3</option>
                <option value="ม.4">ม.4</option>
                <option value="ม.5">ม.5</option>
                <option value="ม.6">ม.6</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">วันที่เริ่ม</label>
            <input type="date" id="filter-date-start" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">วันที่สิ้นสุด</label>
            <input type="date" id="filter-date-end" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
        </div>
        <div class="flex items-end gap-2">
            <button id="btn-filter" class="flex-1 px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium transition-all flex items-center justify-center gap-2">
                <i class="fas fa-search"></i>
                <span>ค้นหา</span>
            </button>
            <button id="btn-filter-clear" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 text-sm font-medium transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<!-- Reports Table -->
<div class="glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-white/50 to-white/30 dark:from-gray-800/50 dark:to-gray-800/30">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <i class="fas fa-clipboard-list text-white text-lg md:text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white">รายการรายงานการสอน</h2>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">แสดงรายงานจากครูทุกคน</p>
            </div>
        </div>
    </div>
    
    <div class="table-scroll overflow-x-auto">
        <table id="reportTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gradient-to-r from-emerald-500/90 via-green-500/90 to-teal-500/90">
                <tr>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">📅 วันที่</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">👩‍🏫 ครูผู้สอน</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden sm:table-cell">📖 วิชา</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">🏫 ห้อง</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden md:table-cell">⏰ คาบ</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden lg:table-cell">📝 หัวข้อ</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">🙋‍♂️ ขาด</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">⚙️ ดู</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                <!-- JS will fill -->
            </tbody>
        </table>
    </div>
</div>

<!-- Report Detail Modal -->
<div id="reportDetailModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-black/40 backdrop-blur-sm p-2 md:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-4xl relative border border-gray-200 dark:border-gray-700 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="sticky top-0 z-10 bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 p-4 md:p-6">
            <button id="closeReportModal" class="absolute top-3 right-3 md:top-4 md:right-4 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full text-white text-xl md:text-2xl transition-all">&times;</button>
            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2 md:gap-3 pr-10">
                <span class="text-2xl md:text-3xl">📋</span>
                <span>รายละเอียดรายงานการสอน</span>
            </h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4 md:p-6">
            <div id="reportDetailContent">
                <!-- JS will fill -->
            </div>
        </div>
    </div>
</div>

<!-- External JS -->
<script src="js/admin-report.js"></script>
