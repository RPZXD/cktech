<?php
/**
 * Certificate View
 * MVC Pattern - Student certificate management
 */

$jsTeacherId = json_encode($teacherId ?? null);
$jsTeacherName = json_encode($teacher_name ?? '');
?>

<style>
    /* Aurora Effect */
    .certificate-wrapper {
        position: relative;
        isolation: isolate;
    }
    .certificate-wrapper::before {
        content: '';
        position: absolute;
        inset: -40px;
        background: linear-gradient(135deg, rgba(245,158,11,0.3), rgba(249,115,22,0.2));
        filter: blur(60px);
        z-index: -1;
        border-radius: 999px;
        animation: certificateGlow 10s ease-in-out infinite;
    }
    @keyframes certificateGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    
    /* Award Badge Styles */
    .award-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .award-gold { background: linear-gradient(135deg, #fef3c7, #fcd34d); color: #92400e; }
    .award-silver { background: linear-gradient(135deg, #f3f4f6, #d1d5db); color: #374151; }
    .award-bronze { background: linear-gradient(135deg, #fed7aa, #fdba74); color: #9a3412; }
    .award-special { background: linear-gradient(135deg, #dbeafe, #93c5fd); color: #1e40af; }
    
    /* Table Row Hover */
    .table-row-hover {
        transition: all 0.3s ease;
    }
    .table-row-hover:hover {
        background: linear-gradient(90deg, rgba(249,115,22,0.1) 0%, rgba(245,158,11,0.1) 100%);
        transform: translateX(4px);
    }
    
    /* Loading Skeleton */
    .skeleton-line {
        height: 12px;
        width: 100%;
        background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
        background-size: 200% 100%;
        animation: loading 1.4s infinite;
        border-radius: 6px;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    /* Mobile Table Responsiveness */
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards { display: flex !important; }
    }
    @media (min-width: 769px) {
        .desktop-table { display: block !important; }
        .mobile-cards { display: none !important; }
    }
    
    /* Modal Animation */
    .modal-fade {
        animation: modalFadeIn 0.3s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95) translateY(-10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
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
    
    /* Improved Mobile Layout & Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideIn {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
    .modal-content-animate {
        animation: slideIn 0.3s ease-out;
    }
    
    /* Touch-friendly buttons */
    button, .cursor-pointer, label {
        touch-action: manipulation;
    }

    /* Better scrolling for modal on mobile */
    #formAddCertificate {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
        -webkit-overflow-scrolling: touch;
    }
    #formAddCertificate::-webkit-scrollbar {
        width: 6px;
    }
    #formAddCertificate::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 10px;
    }
    
    /* Responsive spacing */
    @media (max-width: 640px) {
        .student-item {
            padding: 1.25rem 0.75rem !important;
        }
        .modal-container {
            padding: 0 !important;
            height: 100% !important;
            display: flex;
            align-items: flex-end; /* Modern bottom sheet style on mobile */
        }
        #modalAddCertificate > div {
            max-height: 100vh !important;
            height: auto !important;
            min-height: 80vh !important;
            border-radius: 1.5rem 1.5rem 0 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        #formAddCertificate {
            max-height: calc(100vh - 120px) !important;
            padding-bottom: 3rem !important;
        }
    }
    
    /* Input clarity for mobile */
    input, select, textarea {
        font-size: 16px !important; /* Prevents auto-zoom on iOS */
    }
</style>

<!-- Page Header -->
<div class="certificate-wrapper mb-4 md:mb-6">
    <div class="glass rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-xl border border-white/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="text-center md:text-left">
                <h1 class="text-xl md:text-2xl lg:text-3xl font-extrabold flex flex-wrap items-center justify-center md:justify-start gap-2 text-slate-900 dark:text-white">
                    <span class="text-2xl md:text-3xl">🏆</span>
                    <span class="bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 bg-clip-text text-transparent">
                        บันทึกเกียรติบัตรนักเรียน
                    </span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-xs md:text-sm mt-1">
                    จัดการและบันทึกผลงานรางวัลของนักเรียน
                </p>
            </div>
            
            <div class="flex flex-wrap items-center justify-center md:justify-end gap-2">
                <button id="
                " class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all text-sm">
                    <span>📊</span>
                    <span>สถิติรางวัล</span>
                </button>
                <button id="btnAddCertificate" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all text-sm">
                    <span class="text-lg">➕</span>
                    <span>เพิ่มเกียรติบัตร</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards (Hidden by default, toggled by btnStats) -->
<div id="statsCards" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-6 hidden">
    <div class="card-hover glass rounded-2xl p-4 md:p-5 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">เกียรติบัตรทั้งหมด</p>
                <h3 id="totalCerts" class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-certificate text-white text-sm md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-2xl p-4 md:p-5 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">นักเรียนที่ได้รับ</p>
                <h3 id="totalStudents" class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-white text-sm md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-2xl p-4 md:p-5 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">รางวัลยอดนิยม</p>
                <h3 id="topAward" class="text-sm md:text-base font-bold text-gray-900 dark:text-white mt-1 truncate">-</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-yellow-500 to-amber-600 rounded-xl shadow-lg shadow-yellow-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-trophy text-white text-sm md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-2xl p-4 md:p-5 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">เดือนนี้</p>
                <h3 id="thisMonth" class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mt-1">0</h3>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-rose-500 to-red-600 rounded-xl shadow-lg shadow-rose-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar text-white text-sm md:text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="flex flex-wrap gap-2 mb-4 md:mb-6">
    <button id="btnExport" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-medium rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm">
        <i class="fas fa-download"></i>
        <span class="hidden sm:inline">ส่งออกข้อมูล</span>
        <span class="sm:hidden">Export</span>
    </button>
    <button id="btnRefresh" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-medium rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm">
        <i class="fas fa-sync-alt"></i>
        <span class="hidden sm:inline">รีเฟรช</span>
    </button>
</div>

<!-- Search and Filter Section -->
<div class="glass rounded-2xl p-4 md:p-6 shadow-lg border border-white/20 mb-4 md:mb-6">
    <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="fas fa-filter text-orange-500"></i>
        ค้นหาและกรองข้อมูล
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 md:gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">ค้นหานักเรียน</label>
            <input type="text" id="searchStudent" placeholder="ชื่อนักเรียน..." 
                class="w-full px-3 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">ระดับชั้น</label>
            <select id="filterClass" class="w-full px-3 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
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
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">ประเภทรางวัล</label>
            <select id="filterAward" class="w-full px-3 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">ทุกประเภท</option>
                <option value="รางวัลชนะเลิศ">รางวัลชนะเลิศ</option>
                <option value="รางวัลรองชนะเลิศอันดับ 1">รางวัลรองชนะเลิศอันดับ 1</option>
                <option value="รางวัลรองชนะเลิศอันดับ 2">รางวัลรองชนะเลิศอันดับ 2</option>
                <option value="รางวัลชมเชย">รางวัลชมเชย</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">ภาคเรียน</label>
            <select id="filterTerm" class="w-full px-3 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">ทุกภาคเรียน</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">ปีการศึกษา</label>
            <select id="filterYear" class="w-full px-3 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">ทุกปี</option>
            </select>
        </div>
        <div class="flex items-end">
            <button id="btnClearFilter" class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors text-sm">
                <i class="fas fa-eraser mr-1"></i>ล้างตัวกรอง
            </button>
        </div>
    </div>
</div>

<!-- Desktop Table -->
<div class="desktop-table glass rounded-2xl md:rounded-3xl p-3 md:p-6 shadow-xl border border-white/20 bg-white/90 dark:bg-gray-800/90 mb-4">
    <div class="overflow-x-auto">
        <table class="w-full" id="certificateTable">
            <thead>
                <tr class="bg-gradient-to-r from-orange-500 to-amber-600 text-white text-xs md:text-sm">
                    <th class="py-3 px-2 md:px-4 rounded-tl-lg text-left whitespace-nowrap">👤 นักเรียน</th>
                    <th class="py-3 px-2 md:px-4 text-left whitespace-nowrap">🏫 ชั้น</th>
                    <th class="py-3 px-2 md:px-4 text-left whitespace-nowrap">🏆 ชื่อรางวัล</th>
                    <th class="py-3 px-2 md:px-4 text-center whitespace-nowrap">⭐ ระดับ</th>
                    <th class="py-3 px-2 md:px-4 text-center whitespace-nowrap">🎖️ ประเภท</th>
                    <th class="py-3 px-2 md:px-4 text-center whitespace-nowrap">📅 วันที่</th>
                    <th class="py-3 px-2 md:px-4 text-center whitespace-nowrap">📷 รูป</th>
                    <th class="py-3 px-2 md:px-4 text-center whitespace-nowrap">📚 ภาค/ปี</th>
                    <th class="py-3 px-2 md:px-4 rounded-tr-lg text-center whitespace-nowrap">⚙️ จัดการ</th>
                </tr>
            </thead>
            <tbody id="certificateTableBody" class="text-sm">
                <tr class="loading-row">
                    <td colspan="9" class="text-center py-8 text-gray-500">
                        <div class="flex flex-col items-center gap-3">
                            <div class="text-4xl animate-bounce">🏆</div>
                            <span>กำลังโหลดข้อมูล...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Cards -->
<div id="mobileCertificateCards" class="mobile-cards flex-col gap-3" style="display: none;">
    <div class="text-center py-8 text-gray-500">
        <div class="text-4xl mb-2 animate-bounce">🏆</div>
        กำลังโหลดข้อมูล...
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modalAddCertificate" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-start justify-center z-50 hidden overflow-y-auto p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl my-4 ring-1 ring-gray-200 dark:ring-gray-700 modal-fade">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10 rounded-t-2xl">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    🏆 <span id="modalTitleText">บันทึกเกียรติบัตรใหม่</span>
                </h2>
            </div>
            <button id="closeModalAddCertificate" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors text-xl">
                ✕
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="formAddCertificate" class="p-4 space-y-5 max-h-[75vh] overflow-y-auto" enctype="multipart/form-data">
            
            <!-- Section 1: Students -->
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 p-4 rounded-xl border-l-4 border-orange-500">
                <h3 class="text-base font-bold text-orange-700 dark:text-orange-400 mb-4 flex items-center gap-2">
                    👤 รายชื่อนักเรียน
                </h3>
                
            <div id="studentsContainer">
                    <div class="student-item bg-white dark:bg-gray-700 p-3 rounded-xl border border-gray-200 dark:border-gray-600 mb-3 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-sm text-gray-700 dark:text-gray-300">👤 นักเรียนคนที่ 1</span>
                            <button type="button" class="remove-student hidden px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-medium transition-colors">
                                <i class="fas fa-trash mr-1"></i>ลบ
                            </button>
                        </div>
                        <!-- Student Search Input -->
                        <div class="mb-3">
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">🔍 ค้นหานักเรียน</label>
                            <div class="relative">
                                <input type="text" class="student-search w-full px-3 py-2 text-sm rounded-lg border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 bg-white dark:bg-gray-600 text-gray-900 dark:text-white" placeholder="พิมพ์ชื่อนักเรียนเพื่อค้นหา..." autocomplete="off">
                                <div class="student-search-results absolute top-full left-0 right-0 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-lg mt-1 max-h-48 overflow-y-auto z-50 hidden shadow-lg"></div>
                            </div>
                            <input type="hidden" name="students[0][student_id]" class="student-id-hidden">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                                <input type="text" name="students[0][name]" required 
                                    class="student-name-input w-full px-3 py-2 text-sm rounded-lg border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white cursor-not-allowed"
                                    placeholder="(เลือกจากค้นหาด้านบน)" readonly>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">ระดับชั้น <span class="text-red-500">*</span></label>
                                <input type="text" name="students[0][class]" required 
                                    class="student-class-input w-full px-3 py-2 text-sm rounded-lg border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white cursor-not-allowed"
                                    placeholder="(อัตโนมัติ)" readonly>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">ห้อง <span class="text-red-500">*</span></label>
                                <input type="text" name="students[0][room]" required 
                                    class="student-room-input w-full px-3 py-2 text-sm rounded-lg border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white cursor-not-allowed"
                                    placeholder="(อัตโนมัติ)" readonly>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">รูปเกียรติบัตร</label>
                            <input type="file" name="students[0][image]" accept="image/*" 
                                class="student-file-input block w-full text-sm text-gray-500 dark:text-gray-400
                                  file:mr-4 file:py-2.5 file:px-4
                                  file:rounded-xl file:border-0
                                  file:text-xs file:font-bold
                                  file:bg-orange-500 file:text-white
                                  file:shadow-md file:cursor-pointer
                                  hover:file:bg-orange-600 transition-all
                                  bg-white dark:bg-gray-700 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-1">
                            <p class="text-xs text-gray-500 mt-1">รองรับไฟล์รูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</p>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="addStudentBtn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition-all active:scale-95 text-sm shadow-lg shadow-green-500/20">
                    <span>➕</span> เพิ่มนักเรียน
                </button>
            </div>
            
            <div class="h-10 sm:hidden"></div> <!-- Extra space for mobile -->
            
            <!-- Section 2: Award Info -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-4 rounded-xl border-l-4 border-blue-500">
                <h3 class="text-base font-bold text-blue-700 dark:text-blue-400 mb-4 flex items-center gap-2">
                    🏆 ข้อมูลรางวัล
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อรางวัล <span class="text-red-500">*</span></label>
                        <input type="text" name="award_name" required 
                            class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="เช่น การแข่งขันคณิตศาสตร์ รายการ...">
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">ระดับรางวัล <span class="text-red-500">*</span></label>
                            <select name="award_level" required 
                                class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
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
                            <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">หน่วยงานที่มอบรางวัล <span class="text-red-500">*</span></label>
                            <input type="text" name="award_organization" required 
                                class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="เช่น สพฐ., มหาวิทยาลัย...">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">ประเภทรางวัล <span class="text-red-500">*</span></label>
                        <select name="award_type" required 
                            class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
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
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">รายละเอียดรางวัล <span class="text-red-500">*</span></label>
                        <textarea name="award_detail" required rows="2" 
                            class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                            placeholder="เช่น การแข่งขันคณิตศาสตร์ระดับโรงเรียน, โครงงานวิทยาศาสตร์"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Section 3: Date and Term -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 p-4 rounded-xl border-l-4 border-purple-500">
                <h3 class="text-base font-bold text-purple-700 dark:text-purple-400 mb-4 flex items-center gap-2">
                    📅 วันที่และภาคเรียน
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">วันที่ได้รับรางวัล <span class="text-red-500">*</span></label>
                        <input type="date" name="award_date" required 
                            class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">ภาคเรียน <span class="text-red-500">*</span></label>
                        <input type="number" min="1" max="3" name="term" id="termInput" required 
                            class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="เช่น 1 หรือ 2">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">ปีการศึกษา <span class="text-red-500">*</span></label>
                        <input type="number" min="2500" max="2700" name="year" id="yearInput" required 
                            class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="เช่น 2567">
                    </div>
                </div>
            </div>
            
            <!-- Section 4: Notes -->
            <div class="bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900/20 dark:to-slate-900/20 p-4 rounded-xl border-l-4 border-gray-400">
                <h3 class="text-base font-bold text-gray-700 dark:text-gray-400 mb-4 flex items-center gap-2">
                    📝 หมายเหตุ
                </h3>
                <textarea name="note" rows="2" 
                    class="w-full px-3 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500/50 focus:border-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                    placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
            </div>
        </form>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700 sticky bottom-0 bg-white dark:bg-gray-800 rounded-b-2xl sm:rounded-none">
            <button type="button" id="cancelAddCertificate" class="flex-1 sm:flex-none px-6 py-3 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold sm:font-medium transition-colors">
                <i class="fas fa-times mr-1"></i> ยกเลิก
            </button>
            <button type="submit" form="formAddCertificate" class="flex-1 sm:flex-none px-6 py-3 sm:py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-bold sm:font-medium shadow-lg hover:shadow-xl transition-all active:scale-95">
                <i class="fas fa-save mr-1"></i> บันทึก
            </button>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imagePreviewModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[60] hidden" onclick="closeImagePreview()">
    <div class="relative max-w-4xl max-h-[90vh] p-4">
        <button class="absolute top-2 right-2 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 rounded-full text-white text-2xl transition-colors" onclick="closeImagePreview()">
            ✕
        </button>
        <img id="previewImage" src="" alt="Preview" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl">
    </div>
</div>

<!-- Config for JS -->
<script>
    window.CERTIFICATE_CONFIG = {
        teacherId: <?php echo $jsTeacherId; ?>,
        teacherName: <?php echo $jsTeacherName; ?>
    };
</script>

<!-- UI Scripts -->
<script>
(function(){
    // Helper selector
    const $ = (s, root=document) => Array.from(root.querySelectorAll(s));
    
    // Modal elements are now managed by CertificateManager/FormHandler
    // Cleanup of redundant listeners to prevent conflicts
    
    // Stats toggle and other UI behaviors are now moved to JS modules
    // to prevent event conflicts and maintain MVC structure.
    
    // Populate term and year selects
    function populateTermYear() {
        const termEl = document.getElementById('filterTerm');
        const yearEl = document.getElementById('filterYear');
        const termInput = document.getElementById('termInput');
        const yearInput = document.getElementById('yearInput');
        
        if(termEl) {
            termEl.innerHTML = '<option value="">ทุกภาคเรียน</option>' + 
                [1,2,3].map(t => `<option value="${t}">${t}</option>`).join('');
        }
        
        const now = new Date();
        const buddhist = now.getFullYear() + 543;
        const years = [];
        for(let y = buddhist+1; y >= buddhist-5; y--) years.push(y);
        
        if(yearEl) {
            yearEl.innerHTML = '<option value="">ทุกปี</option>' + 
                years.map(y => `<option value="${y}">${y}</option>`).join('');
        }
        
        if(termInput) termInput.setAttribute('max', '3');
        if(yearInput) {
            yearInput.setAttribute('min', (buddhist-10).toString());
            yearInput.setAttribute('max', (buddhist+5).toString());
        }
    }
    populateTermYear();
    
    // Image preview
    window.openImagePreview = function(src) {
        const modal = document.getElementById('imagePreviewModal');
        const img = document.getElementById('previewImage');
        if(modal && img) {
            img.src = src;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeImagePreview = function() {
        const modal = document.getElementById('imagePreviewModal');
        if(modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };
    
    // Animate newly added table rows
    const tbody = document.querySelector('#certificateTable tbody');
    if(tbody) {
        const obs = new MutationObserver((mutations) => {
            mutations.forEach(m => {
                m.addedNodes && Array.from(m.addedNodes).forEach((n, i) => {
                    if(n.nodeType === 1 && n.tagName === 'TR') {
                        n.classList.add('animate-fade-in');
                        n.style.animationDelay = `${i*50}ms`;
                        setTimeout(() => n.style.animationDelay = '', 600);
                    }
                });
            });
        });
        obs.observe(tbody, { childList: true, subtree: false });
    }
})();
</script>

<!-- Simple Student Search Script -->
<script>
(function(){
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    
    // Initialize student search for an item
    function initStudentSearch(container) {
        const searchInput = container.querySelector('.student-search');
        const resultsDiv = container.querySelector('.student-search-results');
        const nameInput = container.querySelector('.student-name-input');
        const classInput = container.querySelector('.student-class-input');
        const roomInput = container.querySelector('.student-room-input');
        const idInput = container.querySelector('.student-id-hidden');
        
        if (!searchInput || !resultsDiv) return;
        
        const doSearch = debounce(async function(query) {
            if (query.length < 2) {
                resultsDiv.classList.add('hidden');
                return;
            }
            
            try {
                const resp = await fetch(`../controllers/StudentController.php?action=search&q=${encodeURIComponent(query)}&limit=10`);
                const data = await resp.json();
                
                if (data.results && data.results.length > 0) {
                    resultsDiv.innerHTML = data.results.map(s => `
                        <div class="student-result p-2 hover:bg-orange-50 dark:hover:bg-gray-600 cursor-pointer border-b last:border-0 transition-colors"
                             data-id="${s.id}" data-name="${s.text}" data-class="${s.class}" data-room="${s.room}">
                            <i class="fas fa-user text-orange-500 mr-2"></i>
                            <span>${s.display}</span>
                        </div>
                    `).join('');
                    resultsDiv.classList.remove('hidden');
                    
                    // Add click handlers
                    resultsDiv.querySelectorAll('.student-result').forEach(el => {
                        el.addEventListener('click', () => {
                            if (nameInput) nameInput.value = el.dataset.name;
                            if (classInput) classInput.value = 'ม.' + el.dataset.class;
                            if (roomInput) roomInput.value = el.dataset.room;
                            if (idInput) idInput.value = el.dataset.id;
                            searchInput.value = el.dataset.name;
                            resultsDiv.classList.add('hidden');
                        });
                    });
                } else {
                    resultsDiv.innerHTML = '<div class="p-3 text-gray-500 text-center">❌ ไม่พบนักเรียน</div>';
                    resultsDiv.classList.remove('hidden');
                }
            } catch (err) {
                console.error('Search error:', err);
                resultsDiv.classList.add('hidden');
            }
        }, 300);
        
        searchInput.addEventListener('input', (e) => doSearch(e.target.value));
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.length >= 2) doSearch(searchInput.value);
        });
        
        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                resultsDiv.classList.add('hidden');
            }
        });
    }
    
    // Initialize file input for an item
    function initFileInput(container) {
        // No special JS needed for the native styled input unless we want extra previews
        // but for now let's keep it simple to ensure it works
    }
    
    // Initialize for existing items
    document.querySelectorAll('.student-item').forEach(container => {
        initStudentSearch(container);
        initFileInput(container);
    });
    
    // Override add student button
    const addBtn = document.getElementById('addStudentBtn');
    const container = document.getElementById('studentsContainer');
    
    if (addBtn && container) {
        addBtn.addEventListener('click', () => {
            const items = container.querySelectorAll('.student-item');
            const idx = items.length;
            const template = items[0];
            const clone = template.cloneNode(true);
            
            // Clear values and update names
            clone.querySelectorAll('input, select, textarea').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/students\[\d+\]/, `students[${idx}]`));
                }
                const id = el.getAttribute('id');
                if (id) {
                    el.setAttribute('id', id.replace(/\d+/, idx));
                }
                if (el.type !== 'file') el.value = '';
            });
            
            // Update label for file input
            const fileLabel = clone.querySelector('.file-label');
            if (fileLabel) {
                fileLabel.setAttribute('for', `st_img_${idx}`);
            }
            
            // Reset file input value
            const fileInput = clone.querySelector('.student-file-input');
            if (fileInput) fileInput.value = '';
            
            // Update student number
            const label = clone.querySelector('.font-semibold');
            if (label) label.textContent = `👤 นักเรียนคนที่ ${idx + 1}`;
            
            // Show remove button
            const removeBtn = clone.querySelector('.remove-student');
            if (removeBtn) {
                removeBtn.classList.remove('hidden');
                removeBtn.addEventListener('click', () => {
                    clone.remove();
                    updateLabels();
                });
            }
            
            // Clear search results container
            const resultsDiv = clone.querySelector('.student-search-results');
            if (resultsDiv) resultsDiv.innerHTML = '';
            
            container.appendChild(clone);
            
            // Initialize search and file input for new item
            initStudentSearch(clone);
            initFileInput(clone);
            
            // Scroll to view
            clone.scrollIntoView({behavior: 'smooth', block: 'center'});
        });
    }
    
    function updateLabels() {
        const items = container.querySelectorAll('.student-item');
        items.forEach((el, i) => {
            const label = el.querySelector('.font-semibold');
            if (label) label.textContent = `👤 นักเรียนคนที่ ${i + 1}`;
            const removeBtn = el.querySelector('.remove-student');
            if (removeBtn) removeBtn.classList.toggle('hidden', i === 0);
        });
    }
    
    // Wire initial remove buttons
    document.querySelectorAll('.remove-student').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.student-item')?.remove();
            updateLabels();
        });
    });
})();
</script>

<!-- External JS Modules -->
<script src="js/certificate/certificate-main.js"></script>
<script src="js/certificate/certificate-form.js"></script>
<script src="js/certificate/certificate-table.js"></script>
<script src="js/certificate/certificate-filter.js"></script>
<script src="js/certificate/certificate-stats.js"></script>
<script src="js/certificate/certificate-export.js"></script>
