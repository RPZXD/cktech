<?php
/**
 * Admin Certificate Report View
 * MVC Pattern - View for viewing all certificates (admin level)
 * Enhanced UI/UX with Tailwind CSS - Mobile Responsive
 */
?>

<style>
    /* Floating Animation */
    .float-animation {
        animation: floating 3s ease-in-out infinite;
    }

    @keyframes floating {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    /* Table scroll */
    .table-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .table-scroll::-webkit-scrollbar-thumb {
        background: rgba(139, 92, 246, 0.5);
        border-radius: 4px;
    }

    /* Award badges */
    .award-badge {
        transition: all 0.2s ease;
    }

    .award-badge:hover {
        transform: scale(1.05);
    }

    /* Award Colors */
    .award-gold {
        background: linear-gradient(135deg, #fef3c7, #fcd34d);
        color: #92400e;
    }

    .award-silver {
        background: linear-gradient(135deg, #f3f4f6, #d1d5db);
        color: #374151;
    }

    .award-bronze {
        background: linear-gradient(135deg, #fed7aa, #fdba74);
        color: #9a3412;
    }

    .award-special {
        background: linear-gradient(135deg, #dbeafe, #93c5fd);
        color: #1e40af;
    }

    /* Certificate Card */
    .cert-card {
        transition: all 0.3s ease;
    }

    .cert-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.15);
    }

    /* Image Preview */
    .cert-image-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .cert-image-thumb:hover {
        transform: scale(1.1);
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .desktop-table {
            display: none !important;
        }

        .mobile-cards {
            display: flex !important;
        }
    }

    @media (min-width: 769px) {
        .desktop-table {
            display: block !important;
        }

        .mobile-cards {
            display: none !important;
        }
    }
</style>

<!-- Page Header -->
<div class="mb-6 md:mb-8">
    <div class="relative glass rounded-2xl md:rounded-3xl p-5 md:p-8 shadow-xl overflow-hidden">
        <div
            class="absolute -top-20 -right-20 w-40 h-40 md:w-60 md:h-60 bg-gradient-to-br from-indigo-400/20 to-violet-400/20 rounded-full blur-3xl">
        </div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-3xl md:text-4xl float-animation">🏆</span>
                    <span
                        class="px-3 py-1 bg-indigo-500 text-white text-xs font-bold rounded-full uppercase tracking-wider">Certificate
                        Reports</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                    รายงานเกียรติบัตรทั้งหมด
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm md:text-base">
                    ดูและจัดการข้อมูลเกียรติบัตรของนักเรียนจากครูทุกคน
                </p>
            </div>
            <div class="flex flex-wrap gap-2 md:gap-3">
                <button id="btnStatistics"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-purple-500 to-violet-600 rounded-xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium active:scale-95">
                    <i class="fas fa-chart-pie mr-2"></i>
                    <span>สถิติ</span>
                </button>
                <button id="btnExportExcel"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium active:scale-95">
                    <i class="fas fa-file-excel mr-2"></i>
                    <span>ส่งออก Excel</span>
                </button>
                <button id="btnPrintReport"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-rose-500 to-pink-600 rounded-xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium active:scale-95">
                    <i class="fas fa-file-pdf mr-2"></i>
                    <span>พิมพ์รายงาน PDF</span>
                </button>
                <button id="btnPrintWord"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium active:scale-95">
                    <i class="fas fa-file-word mr-2"></i>
                    <span>ส่งออก Word</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div
            class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-violet-500/10 opacity-0 group-hover:opacity-100 transition-opacity">
        </div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">เกียรติบัตรทั้งหมด</p>
                <h3 id="statTotalCerts" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div
                class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-certificate text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div
            class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity">
        </div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">นักเรียนที่ได้รับ</p>
                <h3 id="statTotalStudents" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0
                </h3>
            </div>
            <div
                class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div
            class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-yellow-500/10 opacity-0 group-hover:opacity-100 transition-opacity">
        </div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">ครูที่บันทึก</p>
                <h3 id="statTeachersCount" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0
                </h3>
            </div>
            <div
                class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard-teacher text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div
            class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity">
        </div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">เดือนนี้</p>
                <h3 id="statThisMonth" class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div
                class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl shadow-lg shadow-rose-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-alt text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="glass rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg mb-6">
    <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="fas fa-filter text-indigo-500"></i> ตัวกรองข้อมูล
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 md:gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ค้นหา</label>
            <input type="text" id="filter-search" placeholder="ชื่อนักเรียน/รางวัล..."
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 bg-white dark:bg-gray-700 dark:text-gray-100">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ครูผู้บันทึก</label>
            <select id="filter-teacher"
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ระดับชั้น</label>
            <select id="filter-class"
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 bg-white dark:bg-gray-700 dark:text-gray-100">
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
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ประเภทรางวัล</label>
            <select id="filter-award"
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
                <option value="รางวัลชนะเลิศ">🥇 รางวัลชนะเลิศ</option>
                <option value="รางวัลรองชนะเลิศอันดับ 1">🥈 รองชนะเลิศอันดับ 1</option>
                <option value="รางวัลรองชนะเลิศอันดับ 2">🥉 รองชนะเลิศอันดับ 2</option>
                <option value="รางวัลชมเชย">🏅 รางวัลชมเชย</option>
                <option value="เกียรติบัตร">📜 เกียรติบัตร</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ภาคเรียน</label>
            <select id="filter-term"
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
                <option value="1">1</option>
                <option value="2">2</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">ปีการศึกษา</label>
            <select id="filter-year"
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button id="btn-filter"
                class="flex-1 px-4 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium transition-all flex items-center justify-center gap-2">
                <i class="fas fa-search"></i>
                <span>ค้นหา</span>
            </button>
            <button id="btn-filter-clear"
                class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 text-sm font-medium transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<!-- Desktop Table -->
<div class="desktop-table glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden">
    <div
        class="p-4 md:p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-white/50 to-white/30 dark:from-gray-800/50 dark:to-gray-800/30">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-trophy text-white text-lg md:text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white">รายการเกียรติบัตร</h2>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">แสดงเกียรติบัตรจากครูทุกคน</p>
            </div>
        </div>
    </div>

    <div class="table-scroll overflow-x-auto">
        <table id="certificateTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gradient-to-r from-indigo-500/90 via-violet-500/90 to-purple-500/90">
                <tr>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">📅 วันที่</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">👤 นักเรียน</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden sm:table-cell">🏫
                        ชั้น</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden md:table-cell">🏆
                        ชื่อรางวัล</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">🎖️ ประเภท</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden lg:table-cell">⭐
                        ระดับ</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden xl:table-cell">
                        👨‍🏫 ผู้บันทึก</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">📷 รูป</th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">⚙️ ดู</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                <!-- JS will fill -->
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Cards -->
<div id="mobileCertCards" class="mobile-cards flex-col gap-3" style="display: none;">
    <div class="text-center py-8 text-gray-500">
        <div class="text-4xl mb-2 animate-bounce">🏆</div>
        กำลังโหลดข้อมูล...
    </div>
</div>

<!-- Certificate Detail Modal -->
<div id="certDetailModal"
    class="fixed inset-0 flex items-center justify-center z-50 hidden bg-black/40 backdrop-blur-sm p-2 md:p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-2xl relative border border-gray-200 dark:border-gray-700 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="sticky top-0 z-10 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-500 p-4 md:p-6">
            <button id="closeCertModal"
                class="absolute top-3 right-3 md:top-4 md:right-4 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full text-white text-xl md:text-2xl transition-all">&times;</button>
            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2 md:gap-3 pr-10">
                <span class="text-2xl md:text-3xl">🏆</span>
                <span>รายละเอียดเกียรติบัตร</span>
            </h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4 md:p-6">
            <div id="certDetailContent">
                <!-- JS will fill -->
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imagePreviewModal"
    class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[60] hidden"
    onclick="closeImagePreview()">
    <div class="relative max-w-4xl max-h-[90vh] p-4">
        <button
            class="absolute top-2 right-2 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 rounded-full text-white text-2xl transition-colors"
            onclick="closeImagePreview()">✕</button>
        <img id="previewImage" src="" alt="Preview" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl">
    </div>
</div>

<!-- Print Options Modal -->
<div id="printOptionsModal"
    class="fixed inset-0 flex items-center justify-center z-50 hidden bg-black/40 backdrop-blur-sm p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg relative border border-gray-200 dark:border-gray-700">
        <div id="printModalHeader" class="bg-gradient-to-r from-rose-500 via-pink-500 to-red-500 p-4 rounded-t-2xl">
            <button onclick="closePrintModal()"
                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full text-white text-xl transition-all">&times;</button>
            <h2 id="printModalTitle" class="text-xl font-bold text-white flex items-center gap-2">
                <i id="printModalIcon" class="fas fa-file-pdf"></i>
                <span id="printModalTitleText">เลือกรูปแบบรายงาน PDF</span>
            </h2>
        </div>
        <div class="p-5 space-y-4">
            <!-- Year Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ปีการศึกษา</label>
                <select id="printYear"
                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500">
                    <option value="">-- ทั้งหมด --</option>
                </select>
            </div>

            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ประเภทรายงาน</label>
                <div class="space-y-2">
                    <label
                        class="flex items-center p-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                        <input type="radio" name="reportType" value="all" checked
                            class="w-4 h-4 text-rose-500 focus:ring-rose-500">
                        <div class="ml-3">
                            <span class="font-medium text-gray-800 dark:text-white">📋 รายงานทั้งหมด</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                แสดงรายการเกียรติบัตรทั้งหมดตามปีที่เลือก</p>
                        </div>
                    </label>
                    <label
                        class="flex items-center p-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                        <input type="radio" name="reportType" value="byLevel"
                            class="w-4 h-4 text-rose-500 focus:ring-rose-500">
                        <div class="ml-3">
                            <span class="font-medium text-gray-800 dark:text-white">🌍 แยกตามระดับรางวัล</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">นานาชาติ / ประเทศ / ภาค / จังหวัด /
                                โรงเรียน</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Level Selection (shown when byLevel is selected) -->
            <div id="levelSelection" class="hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">เลือกระดับรางวัล</label>
                <select id="printLevel"
                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500">
                    <option value="">-- ทุกระดับ --</option>
                    <option value="ระดับนานาชาติ">🌍 ระดับนานาชาติ</option>
                    <option value="ระดับประเทศ">🇹🇭 ระดับประเทศ</option>
                    <option value="ระดับภาค">🌏 ระดับภาค</option>
                    <option value="ระดับจังหวัด">🏙️ ระดับจังหวัด</option>
                    <option value="ระดับอำเภอ">🏘️ ระดับอำเภอ</option>
                    <option value="ระดับโรงเรียน">🏫 ระดับโรงเรียน</option>
                </select>
            </div>
        </div>
        <div id="printModalButtons" class="flex gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closePrintModal()"
                class="flex-1 px-4 py-2.5 rounded-xl bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 font-medium transition-colors">
                ยกเลิก
            </button>
            <button id="btnGenerateAction" onclick="generatePDF()"
                class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white font-medium shadow-lg transition-all">
                <i class="fas fa-file-pdf mr-2"></i>สร้าง PDF
            </button>
        </div>
    </div>
</div>

<!-- Statistics Modal -->
<div id="statisticsModal"
    class="fixed inset-0 flex items-center justify-center z-50 hidden bg-black/40 backdrop-blur-sm p-2 md:p-4 overflow-y-auto">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-5xl relative border border-gray-200 dark:border-gray-700 my-4 max-h-[95vh] overflow-hidden flex flex-col">
        <div
            class="sticky top-0 z-10 bg-gradient-to-r from-purple-500 via-violet-500 to-indigo-500 p-4 md:p-5 rounded-t-2xl">
            <button onclick="closeStatisticsModal()"
                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full text-white text-xl transition-all">&times;</button>
            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-chart-pie"></i>
                <span>สถิติเกียรติบัตร</span>
            </h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4 md:p-6">
            <!-- Year Filter for Stats -->
            <div class="mb-6 flex flex-wrap items-center gap-3">
                <label class="font-medium text-gray-700 dark:text-gray-300">ปีการศึกษา:</label>
                <select id="statsYear"
                    class="px-4 py-2 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">
                    <option value="">-- ทั้งหมด --</option>
                </select>
                <button onclick="updateStatistics()"
                    class="px-4 py-2 rounded-xl bg-purple-500 hover:bg-purple-600 text-white font-medium transition-colors">
                    <i class="fas fa-sync-alt mr-1"></i> อัพเดท
                </button>
            </div>

            <!-- Stats Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-4 text-white">
                    <div class="text-3xl font-bold" id="statsTotal">0</div>
                    <div class="text-sm opacity-80">เกียรติบัตรทั้งหมด</div>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-yellow-600 rounded-2xl p-4 text-white">
                    <div class="text-3xl font-bold" id="statsInternational">0</div>
                    <div class="text-sm opacity-80">นานาชาติ</div>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-4 text-white">
                    <div class="text-3xl font-bold" id="statsNational">0</div>
                    <div class="text-sm opacity-80">ประเทศ</div>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-4 text-white">
                    <div class="text-3xl font-bold" id="statsRegional">0</div>
                    <div class="text-sm opacity-80">ภาค/จังหวัด</div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Award Level Pie Chart -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-globe text-purple-500"></i> สัดส่วนตามระดับรางวัล
                    </h3>
                    <div class="aspect-square max-h-[300px] relative">
                        <canvas id="levelChart"></canvas>
                    </div>
                </div>

                <!-- Award Type Pie Chart -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-trophy text-amber-500"></i> สัดส่วนตามประเภทรางวัล
                    </h3>
                    <div class="aspect-square max-h-[300px] relative">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Department Statistics Table -->
            <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4">
                <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-users text-indigo-500"></i> สถิติแยกตามกลุ่มสาระการเรียนรู้
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white">
                                <th class="px-4 py-3 text-left rounded-tl-xl">กลุ่มสาระ</th>
                                <th class="px-4 py-3 text-center">นานาชาติ</th>
                                <th class="px-4 py-3 text-center">ประเทศ</th>
                                <th class="px-4 py-3 text-center">ภาค</th>
                                <th class="px-4 py-3 text-center">จังหวัด</th>
                                <th class="px-4 py-3 text-center">โรงเรียน</th>
                                <th class="px-4 py-3 text-center rounded-tr-xl">รวม</th>
                            </tr>
                        </thead>
                        <tbody id="departmentStatsBody" class="divide-y divide-gray-200 dark:divide-gray-600">
                            <!-- JS will fill -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex gap-3">
            <button onclick="exportStatsPDF()"
                class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-medium shadow-lg transition-all hover:shadow-xl">
                <i class="fas fa-file-pdf mr-2"></i>ส่งออก PDF สถิติ
            </button>
            <button onclick="closeStatisticsModal()"
                class="px-6 py-2.5 rounded-xl bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 font-medium transition-colors">
                ปิด
            </button>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<!-- jsPDF AutoTable Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
<!-- html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<!-- docx.js for Word export -->
<script src="https://cdn.jsdelivr.net/npm/docx@8.5.0/build/index.umd.min.js"></script>
<!-- FileSaver.js for saving Word files -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<!-- Hidden container for PDF rendering with Thai font -->
<div id="pdfRenderContainer"
    style="position: absolute; left: -9999px; top: 0; width: 1000px; font-family: 'Sarabun', sans-serif;"></div>

<!-- Load Sarabun font from Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* PDF render styles - Matching Reference Image Precisely */
    #pdfRenderContainer * {
        font-family: 'Sarabun', sans-serif !important;
        box-sizing: border-box;
    }

    .pdf-page {
        width: 1000px;
        /* Wider for better quality */
        background: white;
        color: black;
        padding: 40px 50px;
    }

    .pdf-header-lines {
        border-top: 1px solid #000;
        border-bottom: 4px double #000;
        padding: 8px 0;
        margin-bottom: 25px;
        text-align: center;
    }

    .pdf-header-title {
        font-size: 26px;
        font-weight: 700;
        color: #000;
    }

    .pdf-section-title {
        background: #f48c51;
        display: inline-block;
        padding: 4px 20px;
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 15px;
        border: 1px solid #d4650d;
    }

    .pdf-table {
        width: 100%;
        border-collapse: collapse;
    }

    .pdf-table th {
        background: #f4ab7d;
        color: #000;
        font-weight: 700;
        padding: 12px 8px;
        text-align: center;
        border: 1px solid #000;
        font-size: 18px;
    }

    .pdf-table td {
        padding: 12px 10px;
        border: 1px solid #000;
        vertical-align: top;
        font-size: 16px;
        line-height: 1.4;
    }

    .pdf-img-cell {
        width: 320px;
        text-align: center;
        padding: 10px !important;
    }

    .pdf-cert-img {
        max-width: 100%;
        max-height: 250px;
        border: 1px solid #ddd;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    .text-center {
        text-align: center;
    }

    .pdf-footer {
        border-top: 4px double #000;
        border-bottom: 1px solid #000;
        padding: 5px 0;
        margin-top: 30px;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
    }
</style>



<script>
    (function () {
        // Global state
        let allCertificates = [];
        let filteredCertificates = [];

        // DOM elements
        const tableBody = document.querySelector('#certificateTable tbody');
        const mobileCards = document.getElementById('mobileCertCards');
        const filterSearch = document.getElementById('filter-search');
        const filterTeacher = document.getElementById('filter-teacher');
        const filterClass = document.getElementById('filter-class');
        const filterAward = document.getElementById('filter-award');
        const filterTerm = document.getElementById('filter-term');
        const filterYear = document.getElementById('filter-year');
        const btnFilter = document.getElementById('btn-filter');
        const btnClear = document.getElementById('btn-filter-clear');

        // Populate year filter
        function populateYears() {
            const now = new Date();
            const buddhist = now.getFullYear() + 543;
            for (let y = buddhist + 1; y >= buddhist - 5; y--) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                filterYear.appendChild(opt);
            }
        }
        populateYears();

        // Load certificates
        async function loadCertificates() {
            try {
                showLoading();
                const resp = await fetch('../controllers/CertificateController.php?action=list');
                const data = await resp.json();

                if (data.success && data.data) {
                    allCertificates = data.data;
                    filteredCertificates = [...allCertificates];
                    populateTeacherFilter();
                    updateStats();
                    renderTable();
                    renderMobileCards();
                } else {
                    showError(data.message || 'ไม่สามารถโหลดข้อมูลได้');
                }
            } catch (err) {
                console.error('Load error:', err);
                showError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            }
        }

        // Populate teacher filter
        function populateTeacherFilter() {
            const teachers = [...new Set(allCertificates.map(c => c.teacher_name).filter(Boolean))];
            teachers.sort();
            filterTeacher.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            teachers.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.textContent = t;
                filterTeacher.appendChild(opt);
            });
        }

        // Update statistics
        function updateStats() {
            const total = filteredCertificates.length;
            const students = new Set(filteredCertificates.map(c => c.student_name)).size;
            const teachers = new Set(filteredCertificates.map(c => c.teacher_id)).size;

            const now = new Date();
            const thisMonth = filteredCertificates.filter(c => {
                const d = new Date(c.created_at);
                return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
            }).length;

            document.getElementById('statTotalCerts').textContent = total.toLocaleString();
            document.getElementById('statTotalStudents').textContent = students.toLocaleString();
            document.getElementById('statTeachersCount').textContent = teachers.toLocaleString();
            document.getElementById('statThisMonth').textContent = thisMonth.toLocaleString();
        }

        // Get award badge class
        function getAwardBadge(type) {
            if (!type) return 'bg-gray-100 text-gray-600';
            if (type.includes('ชนะเลิศ') && !type.includes('รอง')) return 'award-gold';
            if (type.includes('รองชนะเลิศอันดับ 1')) return 'award-silver';
            if (type.includes('รองชนะเลิศอันดับ 2')) return 'award-bronze';
            return 'award-special';
        }

        // Format date
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: '2-digit' });
        }

        // Render table
        function renderTable() {
            if (filteredCertificates.length === 0) {
                tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-12 text-gray-500">
                        <div class="text-5xl mb-4">📭</div>
                        <p class="text-lg font-medium">ไม่พบข้อมูลเกียรติบัตร</p>
                        <p class="text-sm">ลองปรับตัวกรองใหม่อีกครั้ง</p>
                    </td>
                </tr>
            `;
                return;
            }

            tableBody.innerHTML = filteredCertificates.map(cert => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">${formatDate(cert.award_date)}</td>
                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">${cert.student_name || '-'}</td>
                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 hidden sm:table-cell">${cert.student_class || '-'}/${cert.student_room || '-'}</td>
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 hidden md:table-cell">
                    <div class="max-w-xs truncate" title="${cert.award_name || cert.award_detail || '-'}">${cert.award_name || cert.award_detail || '-'}</div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="award-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${getAwardBadge(cert.award_type)}">${cert.award_type || '-'}</span>
                </td>
                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 hidden lg:table-cell">${cert.award_level || '-'}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 hidden xl:table-cell">${cert.teacher_name || '-'}</td>
                <td class="px-4 py-3 text-center">
                    ${cert.certificate_image
                    ? `<img src="../uploads/certificates/${cert.certificate_image}" class="cert-image-thumb" onclick="openImagePreview('../uploads/certificates/${cert.certificate_image}')" alt="รูปเกียรติบัตร" onerror="this.src='../dist/img/logo-phicha.png'">`
                    : '<span class="text-gray-400">-</span>'}
                </td>
                <td class="px-4 py-3 text-center">
                    <button onclick="viewDetail(${cert.id})" class="p-2 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:hover:bg-indigo-800/50 text-indigo-600 dark:text-indigo-400 rounded-lg transition-colors">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        }

        // Render mobile cards
        function renderMobileCards() {
            if (filteredCertificates.length === 0) {
                mobileCards.innerHTML = `
                <div class="glass rounded-2xl p-8 text-center">
                    <div class="text-5xl mb-4">📭</div>
                    <p class="text-gray-600 dark:text-gray-400">ไม่พบข้อมูลเกียรติบัตร</p>
                </div>
            `;
                return;
            }

            mobileCards.innerHTML = filteredCertificates.map(cert => `
            <div class="cert-card glass rounded-2xl p-4 border border-white/20">
                <div class="flex items-start gap-3">
                    ${cert.certificate_image
                    ? `<img src="../uploads/certificates/${cert.certificate_image}" class="w-16 h-16 rounded-xl object-cover" onclick="openImagePreview('../uploads/certificates/${cert.certificate_image}')" onerror="this.src='../dist/img/logo-phicha.png'">`
                    : `<div class="w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-2xl">🏆</div>`}
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 dark:text-white truncate">${cert.student_name || '-'}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">${cert.student_class || '-'}/${cert.student_room || '-'}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-1 ${getAwardBadge(cert.award_type)}">${cert.award_type || '-'}</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">ชื่อรางวัล:</span>
                        <p class="font-medium text-gray-700 dark:text-gray-300 truncate">${cert.award_name || cert.award_detail || '-'}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">วันที่:</span>
                        <p class="font-medium text-gray-700 dark:text-gray-300">${formatDate(cert.award_date)}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">ผู้บันทึก:</span>
                        <p class="font-medium text-gray-700 dark:text-gray-300 truncate">${cert.teacher_name || '-'}</p>
                    </div>
                    <div class="flex items-end justify-end">
                        <button onclick="viewDetail(${cert.id})" class="flex items-center gap-1 px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm transition-colors">
                            <i class="fas fa-eye"></i> ดู
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        }

        // Show loading
        function showLoading() {
            tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-12">
                    <div class="text-4xl mb-3 animate-bounce">🏆</div>
                    <p class="text-gray-500">กำลังโหลดข้อมูล...</p>
                </td>
            </tr>
        `;
            mobileCards.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <div class="text-4xl mb-2 animate-bounce">🏆</div>
                กำลังโหลดข้อมูล...
            </div>
        `;
        }

        // Show error
        function showError(msg) {
            tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-12 text-red-500">
                    <div class="text-4xl mb-3">❌</div>
                    <p>${msg}</p>
                </td>
            </tr>
        `;
        }

        // Apply filters
        function applyFilters() {
            const search = filterSearch.value.toLowerCase().trim();
            const teacher = filterTeacher.value;
            const classVal = filterClass.value;
            const award = filterAward.value;
            const term = filterTerm.value;
            const year = filterYear.value;

            filteredCertificates = allCertificates.filter(c => {
                if (search && !((c.student_name || '').toLowerCase().includes(search) ||
                    (c.award_name || '').toLowerCase().includes(search) ||
                    (c.award_detail || '').toLowerCase().includes(search))) return false;
                if (teacher && c.teacher_name !== teacher) return false;
                if (classVal && c.student_class !== classVal) return false;
                if (award && c.award_type !== award) return false;
                if (term && c.term != term) return false;
                if (year && c.year != year) return false;
                return true;
            });

            updateStats();
            renderTable();
            renderMobileCards();
        }

        // Clear filters
        function clearFilters() {
            filterSearch.value = '';
            filterTeacher.value = '';
            filterClass.value = '';
            filterAward.value = '';
            filterTerm.value = '';
            filterYear.value = '';
            filteredCertificates = [...allCertificates];
            updateStats();
            renderTable();
            renderMobileCards();
        }

        // View detail
        window.viewDetail = async function (id) {
            try {
                const resp = await fetch(`../controllers/CertificateController.php?action=detail&id=${id}`);
                const cert = await resp.json();

                if (cert.id) {
                    const content = document.getElementById('certDetailContent');
                    content.innerHTML = `
                    <div class="space-y-4">
                        ${cert.certificate_image ? `
                            <div class="text-center mb-4">
                                <img src="../uploads/certificates/${cert.certificate_image}" 
                                     class="max-w-full max-h-64 rounded-xl shadow-lg mx-auto cursor-pointer hover:scale-105 transition-transform" 
                                     onclick="openImagePreview('../uploads/certificates/${cert.certificate_image}')"
                                     onerror="this.src='../dist/img/no-image.png'">
                            </div>
                        ` : ''}
                        
                        <div class="bg-gradient-to-r from-indigo-50 to-violet-50 dark:from-indigo-900/30 dark:to-violet-900/30 rounded-xl p-4">
                            <h4 class="font-bold text-indigo-700 dark:text-indigo-400 mb-3">👤 ข้อมูลนักเรียน</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">ชื่อ-นามสกุล:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.student_name || '-'}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">ชั้น/ห้อง:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.student_class || '-'}/${cert.student_room || '-'}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30 rounded-xl p-4">
                            <h4 class="font-bold text-amber-700 dark:text-amber-400 mb-3">🏆 ข้อมูลรางวัล</h4>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">ชื่อรางวัล:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.award_name || '-'}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">ประเภท:</span>
                                        <p><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${getAwardBadge(cert.award_type)}">${cert.award_type || '-'}</span></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">ระดับ:</span>
                                        <p class="font-semibold text-gray-800 dark:text-white">${cert.award_level || '-'}</p>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">หน่วยงาน:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.award_organization || '-'}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">รายละเอียด:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.award_detail || '-'}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/30 dark:to-pink-900/30 rounded-xl p-4">
                            <h4 class="font-bold text-purple-700 dark:text-purple-400 mb-3">📅 ข้อมูลเพิ่มเติม</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">วันที่ได้รับ:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${formatDate(cert.award_date)}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">ภาคเรียน/ปี:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.term || '-'}/${cert.year || '-'}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">ผู้บันทึก:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.teacher_name || '-'}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">วันที่บันทึก:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${formatDate(cert.created_at)}</p>
                                </div>
                            </div>
                            ${cert.note ? `
                                <div class="mt-3">
                                    <span class="text-gray-500 dark:text-gray-400">หมายเหตุ:</span>
                                    <p class="font-semibold text-gray-800 dark:text-white">${cert.note}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;

                    document.getElementById('certDetailModal').classList.remove('hidden');
                }
            } catch (err) {
                console.error('Detail error:', err);
                Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดรายละเอียดได้', 'error');
            }
        };

        // Image preview
        window.openImagePreview = function (src) {
            const modal = document.getElementById('imagePreviewModal');
            const img = document.getElementById('previewImage');
            if (modal && img) {
                img.src = src;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeImagePreview = function () {
            const modal = document.getElementById('imagePreviewModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        };

        // Close detail modal
        document.getElementById('closeCertModal').addEventListener('click', () => {
            document.getElementById('certDetailModal').classList.add('hidden');
        });

        // Export Excel
        document.getElementById('btnExportExcel').addEventListener('click', () => {
            window.open('../controllers/CertificateController.php?action=export&format=csv', '_blank');
        });

        // Open Print Modal (PDF)
        document.getElementById('btnPrintReport').addEventListener('click', () => {
            populatePrintYears();
            // Set modal style for PDF
            document.getElementById('printModalHeader').className = 'bg-gradient-to-r from-rose-500 via-pink-500 to-red-500 p-4 rounded-t-2xl';
            document.getElementById('printModalIcon').className = 'fas fa-file-pdf';
            document.getElementById('printModalTitleText').textContent = 'เลือกรูปแบบรายงาน PDF';
            const btn = document.getElementById('btnGenerateAction');
            btn.className = 'flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white font-medium shadow-lg transition-all';
            btn.innerHTML = '<i class="fas fa-file-pdf mr-2"></i>สร้าง PDF';
            btn.onclick = generatePDF;
            document.getElementById('printOptionsModal').classList.remove('hidden');
        });

        // Open Print Modal (Word)
        document.getElementById('btnPrintWord').addEventListener('click', () => {
            populatePrintYears();
            // Set modal style for Word
            document.getElementById('printModalHeader').className = 'bg-gradient-to-r from-blue-500 via-cyan-500 to-blue-600 p-4 rounded-t-2xl';
            document.getElementById('printModalIcon').className = 'fas fa-file-word';
            document.getElementById('printModalTitleText').textContent = 'เลือกรูปแบบรายงาน Word';
            const btn = document.getElementById('btnGenerateAction');
            btn.className = 'flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white font-medium shadow-lg transition-all';
            btn.innerHTML = '<i class="fas fa-file-word mr-2"></i>สร้าง Word';
            btn.onclick = generateWord;
            document.getElementById('printOptionsModal').classList.remove('hidden');
        });

        // Open Statistics Modal
        document.getElementById('btnStatistics').addEventListener('click', () => {
            populateStatsYears();
            document.getElementById('statisticsModal').classList.remove('hidden');
            setTimeout(() => updateStatistics(), 100);
        });

        // Event listeners
        btnFilter.addEventListener('click', applyFilters);
        btnClear.addEventListener('click', clearFilters);
        filterSearch.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') applyFilters();
        });

        // Report type radio change
        document.querySelectorAll('input[name="reportType"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const levelSelection = document.getElementById('levelSelection');
                if (e.target.value === 'byLevel') {
                    levelSelection.classList.remove('hidden');
                } else {
                    levelSelection.classList.add('hidden');
                }
            });
        });

        // Initialize
        loadCertificates();
    })();

    // ============================================
    // Print Modal Functions
    // ============================================
    function populatePrintYears() {
        const printYear = document.getElementById('printYear');
        const now = new Date();
        const buddhist = now.getFullYear() + 543;
        printYear.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        for (let y = buddhist + 1; y >= buddhist - 5; y--) {
            printYear.innerHTML += `<option value="${y}">${y}</option>`;
        }
    }

    function closePrintModal() {
        document.getElementById('printOptionsModal').classList.add('hidden');
    }

    async function generatePDF() {
        const { jsPDF } = window.jspdf;
        const printYear = document.getElementById('printYear').value;
        const reportType = document.querySelector('input[name="reportType"]:checked').value;
        const printLevel = document.getElementById('printLevel').value;

        // Show loading
        Swal.fire({
            title: 'กำลังสร้าง PDF...',
            html: 'กำลังประมวลผลรูปภาพและจัดรูปแบบ กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            // Fetch certificates
            const resp = await fetch('../controllers/CertificateController.php?action=list');
            const data = await resp.json();

            if (!data.success || !data.data) {
                throw new Error('ไม่สามารถโหลดข้อมูลได้');
            }

            let certs = data.data;

            // Filter by year
            if (printYear) {
                certs = certs.filter(c => c.year == printYear);
            }

            // Filter by level
            if (reportType === 'byLevel' && printLevel) {
                certs = certs.filter(c => c.award_level === printLevel);
            }

            if (certs.length === 0) {
                Swal.fire('ไม่พบข้อมูล', 'ไม่พบเกียรติบัตรตามเงื่อนไขที่เลือก', 'warning');
                return;
            }

            const title = printYear ? `สารสนเทศโรงเรียนพิชัย ปีการศึกษา ${printYear}` : 'สารสนเทศโรงเรียนพิชัย';

            // Fewer items per page when images are included
            const itemsPerPage = 4;
            const totalPages = Math.ceil(certs.length / itemsPerPage);

            const doc = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = doc.internal.pageSize.getWidth();
            const pdfHeight = doc.internal.pageSize.getHeight();

            const container = document.getElementById('pdfRenderContainer');

            for (let page = 0; page < totalPages; page++) {
                const startIdx = page * itemsPerPage;
                const endIdx = Math.min(startIdx + itemsPerPage, certs.length);
                const pageData = certs.slice(startIdx, endIdx);

                // Build rows with images
                let rowsHtml = '';
                for (const [i, cert] of pageData.entries()) {
                    const idx = startIdx + i + 1;
                    const imgSrc = cert.certificate_image
                        ? `../uploads/certificates/${cert.certificate_image}`
                        : '../dist/img/logo-phicha.png';

                    rowsHtml += `
                    <tr>
                        <td class="text-center" style="width: 60px;">${idx}</td>
                        <td style="width: 150px;">${cert.student_name || '-'}</td>
                        <td style="font-size: 14px;">${cert.award_name || cert.award_detail || '-'}</td>
                        <td style="width: 150px;">${cert.award_organization || '-'}</td>
                        <td class="text-center" style="width: 100px;">
                            ${cert.award_date ? new Date(cert.award_date).toLocaleDateString('th-TH', { month: 'long', year: '2-digit' }) : (cert.year || '-')}
                        </td>
                        <td class="pdf-img-cell">
                            <img src="${imgSrc}" class="pdf-cert-img" crossorigin="anonymous">
                        </td>
                    </tr>
                `;
                }

                container.innerHTML = `
                <div class="pdf-page">
                    <div class="pdf-header-lines">
                        <div class="pdf-header-title">${title}</div>
                    </div>
                    <div class="pdf-section-title">รางวัลของนักเรียน</div>
                    <table class="pdf-table">
                        <thead>
                            <tr>
                                <th>ลำดับที่</th>
                                <th>ชื่อ</th>
                                <th>ชื่อรางวัล</th>
                                <th>ได้รับจากหน่วยงาน</th>
                                <th>ปี พ.ศ.</th>
                                <th>รูป</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                    <div class="pdf-footer">
                        <span>โรงเรียนพิชัย สำนักงานเขตพื้นที่การศึกษามัธยมศึกษาพิษณุโลก อุตรดิตถ์</span>
                        <span>หน้า ${page + 1}/${totalPages}</span>
                    </div>
                </div>
            `;

                // Wait for images and fonts
                await new Promise(resolve => {
                    const images = container.getElementsByTagName('img');
                    let loaded = 0;
                    if (images.length === 0) resolve();
                    for (let img of images) {
                        if (img.complete) {
                            loaded++;
                            if (loaded === images.length) resolve();
                        } else {
                            img.onload = () => {
                                loaded++;
                                if (loaded === images.length) resolve();
                            };
                            img.onerror = () => {
                                loaded++;
                                img.src = '../dist/img/logo-phicha.png'; // Fallback again on load error
                                if (loaded === images.length) resolve();
                            };
                        }
                    }
                });
                await document.fonts.ready;

                // Capture with html2canvas (optimized for large images)
                const canvas = await html2canvas(container, {
                    scale: 2.0, // Reduced from 2.5 to save memory
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    imageTimeout: 30000, // Increased timeout for large images
                    allowTaint: false,
                    removeContainer: true
                });

                // Use JPEG with 0.7 compression instead of PNG to drastically reduce memory usage
                const imgData = canvas.toDataURL('image/jpeg', 0.7);
                const imgWidth = pdfWidth - 10;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                if (page > 0) doc.addPage();
                doc.addImage(imgData, 'JPEG', 5, 5, imgWidth, imgHeight);

                // Clean up current page and wait briefly for garbage collection
                container.innerHTML = '';
                await new Promise(resolve => setTimeout(resolve, 150)); 
            }

            const filename = `certificate_report_${printYear || 'all'}_${new Date().getTime()}.pdf`;
            doc.save(filename);

            Swal.fire({
                icon: 'success',
                title: 'สร้าง PDF สำเร็จ!',
                text: `มีข้อมูล ${certs.length} รายการ`,
                timer: 2000,
                showConfirmButton: false
            });

            closePrintModal();
        } catch (err) {
            console.error('PDF Error:', err);
            Swal.fire('ผิดพลาด', 'ไม่สามารถสร้าง PDF ได้เนื่องจากรูปภาพหรือการประมวลผล', 'error');
        }
    }

    // ============================================
    // Word Export Functions
    // ============================================
    async function generateWord() {
        const printYear = document.getElementById('printYear').value;
        const reportType = document.querySelector('input[name="reportType"]:checked').value;
        const printLevel = document.getElementById('printLevel').value;

        Swal.fire({
            title: 'กำลังสร้างไฟล์ Word...',
            html: 'กำลังประมวลผลรูปภาพและจัดรูปแบบ กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            // Fetch certificates
            const resp = await fetch('../controllers/CertificateController.php?action=list');
            const data = await resp.json();

            if (!data.success || !data.data) {
                throw new Error('ไม่สามารถโหลดข้อมูลได้');
            }

            let certs = data.data;

            // Filter by year
            if (printYear) {
                certs = certs.filter(c => c.year == printYear);
            }

            // Filter by level
            if (reportType === 'byLevel' && printLevel) {
                certs = certs.filter(c => c.award_level === printLevel);
            }

            if (certs.length === 0) {
                Swal.fire('ไม่พบข้อมูล', 'ไม่พบเกียรติบัตรตามเงื่อนไขที่เลือก', 'warning');
                return;
            }

            const { Document, Packer, Paragraph, Table, TableRow, TableCell, TextRun, ImageRun,
                WidthType, AlignmentType, BorderStyle, HeadingLevel, PageOrientation,
                VerticalAlign, ShadingType, convertInchesToTwip } = docx;

            const titleText = printYear ? `สารสนเทศโรงเรียนพิชัย ปีการศึกษา ${printYear}` : 'สารสนเทศโรงเรียนพิชัย';

            // Fetch all certificate images as blobs in parallel
            Swal.update({ html: 'กำลังโหลดรูปภาพเกียรติบัตร... (0/' + certs.length + ')' });

            const imagePromises = certs.map(async (cert, idx) => {
                if (cert.certificate_image) {
                    try {
                        const imgResp = await fetch('../uploads/certificates/' + cert.certificate_image);
                        if (imgResp.ok) {
                            const blob = await imgResp.blob();
                            const arrayBuffer = await blob.arrayBuffer();
                            const uint8 = new Uint8Array(arrayBuffer);

                            // Check if image is valid and determine type
                            const isPng = uint8[0] === 0x89 && uint8[1] === 0x50;
                            const isJpg = uint8[0] === 0xFF && uint8[1] === 0xD8;

                            if (isPng || isJpg) {
                                // Get image dimensions
                                const imgEl = new Image();
                                const imgUrl = URL.createObjectURL(blob);
                                await new Promise((resolve, reject) => {
                                    imgEl.onload = resolve;
                                    imgEl.onerror = reject;
                                    imgEl.src = imgUrl;
                                });
                                URL.revokeObjectURL(imgUrl);

                                // Scale: max width 200px, max height 150px (in Word units)
                                let w = imgEl.naturalWidth;
                                let h = imgEl.naturalHeight;
                                const maxW = 200;
                                const maxH = 150;
                                const ratio = Math.min(maxW / w, maxH / h, 1);
                                w = Math.round(w * ratio);
                                h = Math.round(h * ratio);

                                Swal.update({ html: `กำลังโหลดรูปภาพเกียรติบัตร... (${idx + 1}/${certs.length})` });

                                return { data: uint8, width: w, height: h, type: isPng ? 'png' : 'jpg' };
                            }
                        }
                    } catch (e) {
                        console.warn('Failed to load image for cert:', cert.id, e);
                    }
                }
                return null;
            });

            const images = await Promise.all(imagePromises);

            Swal.update({ html: 'กำลังสร้างเอกสาร Word...' });

            // Build table rows
            const headerRow = new TableRow({
                tableHeader: true,
                children: [
                    new TableCell({
                        width: { size: 700, type: WidthType.DXA },
                        shading: { type: ShadingType.SOLID, color: 'F4AB7D' },
                        verticalAlign: VerticalAlign.CENTER,
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: 'ลำดับที่', bold: true, size: 24, font: 'TH Sarabun New' })] })],
                    }),
                    new TableCell({
                        width: { size: 2000, type: WidthType.DXA },
                        shading: { type: ShadingType.SOLID, color: 'F4AB7D' },
                        verticalAlign: VerticalAlign.CENTER,
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: 'ชื่อ', bold: true, size: 24, font: 'TH Sarabun New' })] })],
                    }),
                    new TableCell({
                        width: { size: 3000, type: WidthType.DXA },
                        shading: { type: ShadingType.SOLID, color: 'F4AB7D' },
                        verticalAlign: VerticalAlign.CENTER,
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: 'ชื่อรางวัล', bold: true, size: 24, font: 'TH Sarabun New' })] })],
                    }),
                    new TableCell({
                        width: { size: 2000, type: WidthType.DXA },
                        shading: { type: ShadingType.SOLID, color: 'F4AB7D' },
                        verticalAlign: VerticalAlign.CENTER,
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: 'ได้รับจากหน่วยงาน', bold: true, size: 24, font: 'TH Sarabun New' })] })],
                    }),
                    new TableCell({
                        width: { size: 1200, type: WidthType.DXA },
                        shading: { type: ShadingType.SOLID, color: 'F4AB7D' },
                        verticalAlign: VerticalAlign.CENTER,
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: 'ปี พ.ศ.', bold: true, size: 24, font: 'TH Sarabun New' })] })],
                    }),
                    new TableCell({
                        width: { size: 3000, type: WidthType.DXA },
                        shading: { type: ShadingType.SOLID, color: 'F4AB7D' },
                        verticalAlign: VerticalAlign.CENTER,
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: 'รูป', bold: true, size: 24, font: 'TH Sarabun New' })] })],
                    }),
                ],
            });

            const dataRows = certs.map((cert, idx) => {
                const dateStr = cert.award_date
                    ? new Date(cert.award_date).toLocaleDateString('th-TH', { month: 'long', year: '2-digit' })
                    : (cert.year || '-');

                const imgData = images[idx];
                const imgChildren = [];

                if (imgData) {
                    try {
                        imgChildren.push(new Paragraph({
                            alignment: AlignmentType.CENTER,
                            children: [
                                new ImageRun({
                                    data: imgData.data,
                                    transformation: { width: imgData.width, height: imgData.height },
                                    type: imgData.type === 'png' ? 'png' : 'jpg',
                                })
                            ],
                        }));
                    } catch (e) {
                        imgChildren.push(new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: '-', size: 22, font: 'TH Sarabun New' })] }));
                    }
                } else {
                    imgChildren.push(new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: '-', size: 22, font: 'TH Sarabun New' })] }));
                }

                const borderConfig = {
                    top: { style: BorderStyle.SINGLE, size: 1, color: '000000' },
                    bottom: { style: BorderStyle.SINGLE, size: 1, color: '000000' },
                    left: { style: BorderStyle.SINGLE, size: 1, color: '000000' },
                    right: { style: BorderStyle.SINGLE, size: 1, color: '000000' },
                };

                return new TableRow({
                    children: [
                        new TableCell({
                            borders: borderConfig,
                            verticalAlign: VerticalAlign.CENTER,
                            children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: String(idx + 1), size: 22, font: 'TH Sarabun New' })] })],
                        }),
                        new TableCell({
                            borders: borderConfig,
                            verticalAlign: VerticalAlign.CENTER,
                            children: [new Paragraph({ children: [new TextRun({ text: cert.student_name || '-', size: 22, font: 'TH Sarabun New' })] })],
                        }),
                        new TableCell({
                            borders: borderConfig,
                            verticalAlign: VerticalAlign.CENTER,
                            children: [new Paragraph({ children: [new TextRun({ text: cert.award_name || cert.award_detail || '-', size: 22, font: 'TH Sarabun New' })] })],
                        }),
                        new TableCell({
                            borders: borderConfig,
                            verticalAlign: VerticalAlign.CENTER,
                            children: [new Paragraph({ children: [new TextRun({ text: cert.award_organization || '-', size: 22, font: 'TH Sarabun New' })] })],
                        }),
                        new TableCell({
                            borders: borderConfig,
                            verticalAlign: VerticalAlign.CENTER,
                            children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: dateStr, size: 22, font: 'TH Sarabun New' })] })],
                        }),
                        new TableCell({
                            borders: borderConfig,
                            verticalAlign: VerticalAlign.CENTER,
                            children: imgChildren,
                        }),
                    ],
                });
            });

            const table = new Table({
                width: { size: 100, type: WidthType.PERCENTAGE },
                rows: [headerRow, ...dataRows],
            });

            const doc = new Document({
                sections: [{
                    properties: {
                        page: {
                            size: { orientation: PageOrientation.PORTRAIT },
                            margin: {
                                top: convertInchesToTwip(0.5),
                                bottom: convertInchesToTwip(0.5),
                                left: convertInchesToTwip(0.5),
                                right: convertInchesToTwip(0.5),
                            },
                        },
                    },
                    children: [
                        // Title
                        new Paragraph({
                            alignment: AlignmentType.CENTER,
                            spacing: { after: 200 },
                            border: {
                                top: { style: BorderStyle.SINGLE, size: 1, color: '000000', space: 3 },
                                bottom: { style: BorderStyle.DOUBLE, size: 3, color: '000000', space: 3 },
                            },
                            children: [
                                new TextRun({
                                    text: titleText,
                                    bold: true,
                                    size: 36,
                                    font: 'TH Sarabun New',
                                }),
                            ],
                        }),
                        // Section title
                        new Paragraph({
                            spacing: { after: 200 },
                            children: [
                                new TextRun({
                                    text: '  รางวัลของนักเรียน  ',
                                    bold: true,
                                    size: 28,
                                    font: 'TH Sarabun New',
                                    shading: { type: ShadingType.SOLID, color: 'F48C51' },
                                }),
                            ],
                        }),
                        // Table
                        table,
                        // Footer
                        new Paragraph({
                            spacing: { before: 400 },
                            border: {
                                top: { style: BorderStyle.DOUBLE, size: 3, color: '000000', space: 3 },
                                bottom: { style: BorderStyle.SINGLE, size: 1, color: '000000', space: 3 },
                            },
                            children: [
                                new TextRun({
                                    text: 'โรงเรียนพิชัย สำนักงานเขตพื้นที่การศึกษามัธยมศึกษาพิษณุโลก อุตรดิตถ์',
                                    bold: true,
                                    size: 22,
                                    font: 'TH Sarabun New',
                                }),
                            ],
                        }),
                    ],
                }],
            });

            const blob = await Packer.toBlob(doc);
            const filename = `certificate_report_${printYear || 'all'}_${new Date().getTime()}.docx`;
            saveAs(blob, filename);

            Swal.fire({
                icon: 'success',
                title: 'สร้างไฟล์ Word สำเร็จ!',
                text: `มีข้อมูล ${certs.length} รายการ`,
                timer: 2000,
                showConfirmButton: false
            });

            closePrintModal();
        } catch (err) {
            console.error('Word Error:', err);
            Swal.fire('ผิดพลาด', 'ไม่สามารถสร้างไฟล์ Word ได้: ' + err.message, 'error');
        }
    }




    // ============================================
    // Statistics Modal Functions
    // ============================================
    let levelChart = null;
    let typeChart = null;

    function populateStatsYears() {
        const statsYear = document.getElementById('statsYear');
        const now = new Date();
        const buddhist = now.getFullYear() + 543;
        statsYear.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        for (let y = buddhist + 1; y >= buddhist - 5; y--) {
            statsYear.innerHTML += `<option value="${y}">${y}</option>`;
        }
    }

    function closeStatisticsModal() {
        document.getElementById('statisticsModal').classList.add('hidden');
        if (levelChart) { levelChart.destroy(); levelChart = null; }
        if (typeChart) { typeChart.destroy(); typeChart = null; }
    }

    async function updateStatistics() {
        try {
            const statsYear = document.getElementById('statsYear').value;

            // Fetch all certificates
            const resp = await fetch('../controllers/CertificateController.php?action=list');
            const data = await resp.json();

            if (!data.success || !data.data) return;

            let certs = data.data;

            // Filter by year
            if (statsYear) {
                certs = certs.filter(c => c.year == statsYear);
            }

            // Calculate stats
            const total = certs.length;
            const international = certs.filter(c => (c.award_level || '').includes('นานาชาติ')).length;
            const national = certs.filter(c => (c.award_level || '').includes('ประเทศ')).length;
            const regional = certs.filter(c => (c.award_level || '').includes('ภาค') || (c.award_level || '').includes('จังหวัด')).length;

            document.getElementById('statsTotal').textContent = total;
            document.getElementById('statsInternational').textContent = international;
            document.getElementById('statsNational').textContent = national;
            document.getElementById('statsRegional').textContent = regional;

            // Level breakdown for chart
            const levelCounts = {};
            const levelLabels = ['ระดับนานาชาติ', 'ระดับประเทศ', 'ระดับภาค', 'ระดับจังหวัด', 'ระดับอำเภอ', 'ระดับโรงเรียน', 'อื่นๆ'];
            levelLabels.forEach(l => levelCounts[l] = 0);

            certs.forEach(c => {
                const level = c.award_level || 'อื่นๆ';
                let matched = false;
                for (const l of levelLabels) {
                    if (level.includes(l.replace('ระดับ', ''))) {
                        levelCounts[l]++;
                        matched = true;
                        break;
                    }
                }
                if (!matched) levelCounts['อื่นๆ']++;
            });

            // Type breakdown for chart
            const typeCounts = {};
            certs.forEach(c => {
                const type = c.award_type || 'อื่นๆ';
                typeCounts[type] = (typeCounts[type] || 0) + 1;
            });

            // Update Level Chart
            if (levelChart) levelChart.destroy();
            const levelCtx = document.getElementById('levelChart').getContext('2d');
            levelChart = new Chart(levelCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(levelCounts).filter(k => levelCounts[k] > 0),
                    datasets: [{
                        data: Object.values(levelCounts).filter(v => v > 0),
                        backgroundColor: ['#f59e0b', '#ef4444', '#8b5cf6', '#3b82f6', '#10b981', '#6366f1', '#94a3b8']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 11 } } }
                    }
                }
            });

            // Update Type Chart
            if (typeChart) typeChart.destroy();
            const typeCtx = document.getElementById('typeChart').getContext('2d');
            typeChart = new Chart(typeCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(typeCounts),
                    datasets: [{
                        data: Object.values(typeCounts),
                        backgroundColor: ['#fcd34d', '#d1d5db', '#fdba74', '#93c5fd', '#86efac', '#f9a8d4']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 11 } } }
                    }
                }
            });

            // Update Department Stats Table
            updateDepartmentStats(certs);

        } catch (err) {
            console.error('Stats error:', err);
        }
    }

    function updateDepartmentStats(certs) {
        // Group by teacher (as proxy for department - in real scenario, need teacher's department)
        const deptStats = {};
        const levels = ['นานาชาติ', 'ประเทศ', 'ภาค', 'จังหวัด', 'โรงเรียน'];

        // Group teachers into departments (using teacher_name as proxy)
        const teacherGroups = {};
        certs.forEach(c => {
            const teacher = c.teacher_name || 'ไม่ระบุ';
            if (!teacherGroups[teacher]) {
                teacherGroups[teacher] = { นานาชาติ: 0, ประเทศ: 0, ภาค: 0, จังหวัด: 0, โรงเรียน: 0, total: 0 };
            }

            const level = c.award_level || '';
            let matched = false;
            for (const l of levels) {
                if (level.includes(l)) {
                    teacherGroups[teacher][l]++;
                    matched = true;
                    break;
                }
            }
            teacherGroups[teacher].total++;
        });

        // Render table
        const tbody = document.getElementById('departmentStatsBody');
        tbody.innerHTML = '';

        // Sort by total desc
        const sortedTeachers = Object.entries(teacherGroups).sort((a, b) => b[1].total - a[1].total);

        sortedTeachers.slice(0, 15).forEach(([teacher, stats]) => {
            tbody.innerHTML += `
            <tr class="hover:bg-gray-100 dark:hover:bg-gray-600/50">
                <td class="px-4 py-2 font-medium text-gray-800 dark:text-white">${teacher}</td>
                <td class="px-4 py-2 text-center">${stats['นานาชาติ'] || '-'}</td>
                <td class="px-4 py-2 text-center">${stats['ประเทศ'] || '-'}</td>
                <td class="px-4 py-2 text-center">${stats['ภาค'] || '-'}</td>
                <td class="px-4 py-2 text-center">${stats['จังหวัด'] || '-'}</td>
                <td class="px-4 py-2 text-center">${stats['โรงเรียน'] || '-'}</td>
                <td class="px-4 py-2 text-center font-bold text-indigo-600 dark:text-indigo-400">${stats.total}</td>
            </tr>
        `;
        });

        if (sortedTeachers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">ไม่พบข้อมูล</td></tr>';
        }
    }

    async function exportStatsPDF() {
        const { jsPDF } = window.jspdf;

        Swal.fire({
            title: 'กำลังสร้าง PDF สถิติ...',
            html: 'กำลังประมวลผลกราฟและตารางสรุป กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const statsYear = document.getElementById('statsYear').value;
            const total = document.getElementById('statsTotal').textContent;
            const international = document.getElementById('statsInternational').textContent;
            const national = document.getElementById('statsNational').textContent;
            const regional = document.getElementById('statsRegional').textContent;

            // Capture charts as base64
            const levelCanvas = document.getElementById('levelChart');
            const typeCanvas = document.getElementById('typeChart');
            const levelImg = levelCanvas.toDataURL('image/png', 1.0);
            const typeImg = typeCanvas.toDataURL('image/png', 1.0);

            // Get department table content
            const deptTableBody = document.getElementById('departmentStatsBody').innerHTML;

            const container = document.getElementById('pdfRenderContainer');
            const title = statsYear ? `รายงานสถิติเกียรติบัตร ปีการศึกษา ${statsYear}` : 'รายงานสถิติเกียรติบัตรทั้งหมด';

            container.innerHTML = `
            <div class="pdf-page" style="padding: 40px;">
                <div class="pdf-header-lines">
                    <div class="pdf-header-title">${title}</div>
                </div>
                
                <div class="pdf-section-title">สรุปภาพรวม</div>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">
                    <div style="border: 1px solid #000; padding: 15px; text-align: center; border-radius: 8px;">
                        <div style="font-size: 14px; color: #666;">เกียรติบัตรทั้งหมด</div>
                        <div style="font-size: 24px; font-weight: 700;">${total}</div>
                    </div>
                    <div style="border: 1px solid #000; padding: 15px; text-align: center; border-radius: 8px;">
                        <div style="font-size: 14px; color: #666;">ระดับนานาชาติ</div>
                        <div style="font-size: 24px; font-weight: 700; color: #f59e0b;">${international}</div>
                    </div>
                    <div style="border: 1px solid #000; padding: 15px; text-align: center; border-radius: 8px;">
                        <div style="font-size: 14px; color: #666;">ระดับประเทศ</div>
                        <div style="font-size: 24px; font-weight: 700; color: #ef4444;">${national}</div>
                    </div>
                    <div style="border: 1px solid #000; padding: 15px; text-align: center; border-radius: 8px;">
                        <div style="font-size: 14px; color: #666;">ระดับภาค/จังหวัด</div>
                        <div style="font-size: 24px; font-weight: 700; color: #3b82f6;">${regional}</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 30px; margin-bottom: 30px;">
                    <div style="flex: 1; text-align: center; border: 1px solid #eee; padding: 15px; border-radius: 12px;">
                        <div style="font-weight: 700; margin-bottom: 10px; font-size: 16px;">สัดส่วนตามระดับรางวัล</div>
                        <img src="${levelImg}" style="width: 100%; max-height: 250px; object-fit: contain;">
                    </div>
                    <div style="flex: 1; text-align: center; border: 1px solid #eee; padding: 15px; border-radius: 12px;">
                        <div style="font-weight: 700; margin-bottom: 10px; font-size: 16px;">สัดส่วนตามประเภทรางวัล</div>
                        <img src="${typeImg}" style="width: 100%; max-height: 250px; object-fit: contain;">
                    </div>
                </div>
                
                <div class="pdf-section-title">สถิติแยกตามรายครู (Top 15)</div>
                <table class="pdf-table">
                    <thead>
                        <tr>
                            <th>ชื่อครู/ผู้สอน</th>
                            <th>นานาชาติ</th>
                            <th>ประเทศ</th>
                            <th>ภาค</th>
                            <th>จังหวัด</th>
                            <th>โรงเรียน</th>
                            <th>รวม</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 14px;">
                        ${deptTableBody}
                    </tbody>
                </table>
                
                <div class="pdf-footer" style="margin-top: 40px;">
                    <span>โรงเรียนพิชัย ฝ่ายบริหารงานวิชาการ</span>
                    <span>รายงาน ณ วันที่ ${new Date().toLocaleDateString('th-TH', { day: 'numeric', month: 'long', year: 'numeric' })}</span>
                </div>
            </div>
        `;

            await document.fonts.ready;

            const canvas = await html2canvas(container, {
                scale: 2.5,
                useCORS: true,
                backgroundColor: '#ffffff',
                imageTimeout: 15000
            });

            const doc = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = doc.internal.pageSize.getWidth();
            const imgData = canvas.toDataURL('image/jpeg', 0.9);
            const imgWidth = pdfWidth - 10;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            doc.addImage(imgData, 'JPEG', 5, 5, imgWidth, imgHeight);

            container.innerHTML = '';
            doc.save(`certificate_statistics_${new Date().getTime()}.pdf`);

            Swal.fire({
                icon: 'success',
                title: 'สร้าง PDF สถิติสำเร็จ!',
                timer: 2000,
                showConfirmButton: false
            });

        } catch (err) {
            console.error('Stats PDF Error:', err);
            Swal.fire('ผิดพลาด', 'ไม่สามารถสร้าง PDF สถิติได้', 'error');
        }
    }

</script>