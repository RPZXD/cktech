<?php
/**
 * Teaching Report View
 * MVC Pattern - View for teaching report page
 * Enhanced UI/UX with Tailwind CSS - Mobile Responsive
 */
?>

<style>
    /* Aurora Background Effect */
    .aurora-wrapper { position: relative; isolation: isolate; }
    .aurora-wrapper::before, .aurora-wrapper::after { 
        content: ''; position: absolute; inset: -60px; border-radius: 999px; 
        opacity: 0.35; filter: blur(80px); z-index: -1; pointer-events: none;
    }
    .aurora-wrapper::before { background: linear-gradient(135deg, rgba(14,165,233,0.55), rgba(59,130,246,0.45)); animation: floaty 18s ease-in-out infinite; }
    .aurora-wrapper::after { background: linear-gradient(135deg, rgba(236,72,153,0.5), rgba(249,115,22,0.45)); animation: floaty 22s ease-in-out infinite reverse; }
    @keyframes floaty { 0% { transform: translate(-15px, -10px) scale(1); } 50% { transform: translate(20px, 15px) scale(1.06); } 100% { transform: translate(-15px, -10px) scale(1); } }
    
    /* Glow Card Effect */
    .glow-card { 
        box-shadow: 0 10px 40px rgba(15, 23, 42, 0.15); 
        transition: transform 280ms cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 280ms ease; 
    }
    .glow-card:hover { 
        transform: translateY(-8px) scale(1.02); 
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25); 
    }
    
    /* Sheen Animation */
    .stat-sheen { position: relative; overflow: hidden; }
    .stat-sheen::after { 
        content: ''; position: absolute; top: 0; left: -100%; width: 60px; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,0.5), transparent); 
        transform: skewX(-25deg); animation: sheen 4s linear infinite; 
    }
    @keyframes sheen { 0% { left: -100%; } 100% { left: 200%; } }
    
    /* Floating animation */
    .float-animation { animation: floating 3s ease-in-out infinite; }
    @keyframes floating { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    
    /* Table Responsive */
    .table-container { -webkit-overflow-scrolling: touch; }
    .table-container::-webkit-scrollbar { height: 6px; }
    .table-container::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 3px; }
    
    /* Mobile Card View */
    @media (max-width: 768px) {
        .mobile-card-view { display: block !important; }
        .desktop-table-view { display: none !important; }
    }
    @media (min-width: 769px) {
        .mobile-card-view { display: none !important; }
        .desktop-table-view { display: block !important; }
        .fab-mobile { display: none !important; }
    }
    
    /* Pulse dot */
    .pulse-dot { animation: pulse-glow 2s ease-in-out infinite; }
    @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7); } 50% { box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); } }
    
    /* Modal animations */
    .modal-enter { animation: modalEnter 0.3s ease-out; }
    @keyframes modalEnter { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    
    /* Mobile Stats Scroll */
    .stats-scroll-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    @media (min-width: 769px) {
        .stats-scroll-container { gap: 1rem; }
    }
    
    /* FAB Button */
    .fab-mobile {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 40;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        animation: fabPulse 2s ease-in-out infinite;
    }
    .fab-mobile:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(16, 185, 129, 0.5);
    }
    .fab-mobile:active {
        transform: scale(0.95);
    }
    @keyframes fabPulse {
        0%, 100% { box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 4px 30px rgba(16, 185, 129, 0.6); }
    }
    
    /* Better Touch Feedback */
    .touch-feedback {
        transition: all 0.15s ease;
    }
    .touch-feedback:active {
        transform: scale(0.95);
        opacity: 0.9;
    }
</style>

<!-- Page Header with Aurora Effect -->
<div class="aurora-wrapper mb-4 md:mb-8">
    <div class="glass rounded-2xl md:rounded-3xl p-3 md:p-8 shadow-xl border border-white/20">
        <div class="flex flex-col gap-2 md:gap-4">
            <!-- Title Section - Compact on Mobile -->
            <div class="text-center md:text-left">
                <div class="hidden md:inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 rounded-full mb-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full pulse-dot"></span>
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-300 uppercase tracking-wider">Teaching Experience Hub</span>
                </div>
                <h1 class="text-lg sm:text-xl md:text-3xl lg:text-4xl font-extrabold flex flex-wrap items-center justify-center md:justify-start gap-2 md:gap-3 text-slate-900 dark:text-white">
                    <span class="text-xl md:text-3xl float-animation">📊</span>
                    <span class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 bg-clip-text text-transparent">
                        <span class="hidden sm:inline">แดชบอร์ด</span>รายงานการสอน
                    </span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-xs md:text-base flex items-center justify-center md:justify-start gap-2 mt-1 md:mt-2">
                    <span class="relative flex h-2 w-2 md:h-2.5 md:w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 md:h-2.5 md:w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs md:text-sm">อัปเดตเรียลไทม์ ✨</span>
                </p>
            </div>
            
            <!-- Action Button - Hidden on Mobile (use FAB instead) -->
            <div class="hidden md:flex justify-end">
                <button id="btnAddReport" class="group relative inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-emerald-400 via-green-500 to-emerald-600 px-6 py-3.5 text-white font-semibold shadow-lg shadow-emerald-500/30 transition-all duration-300 hover:shadow-2xl hover:-translate-y-0.5 active:scale-95">
                    <span class="text-xl group-hover:rotate-12 transition-transform duration-200">➕</span>
                    <span class="text-base">เพิ่มรายงานใหม่</span>
                    <span class="absolute inset-0 rounded-full border-2 border-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards - 2 Column Grid -->
<div id="reportStats" class="stats-scroll-container mb-4 md:mb-8">
    <!-- Total Reports -->
    <div class="stat-sheen glow-card glass rounded-xl md:rounded-2xl p-4 md:p-5 bg-gradient-to-br from-green-400 via-green-500 to-emerald-600 relative overflow-hidden">
        <div class="absolute -top-4 -right-4 w-16 h-16 md:w-20 md:h-20 bg-white/10 rounded-full blur-xl"></div>
        <div class="relative">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl md:text-2xl">📈</span>
                <span class="text-xs md:text-sm font-semibold text-white/90">รายงานทั้งหมด</span>
            </div>
            <div id="statTotalReports" class="text-3xl md:text-4xl font-black text-white tracking-tight">0</div>
            <p id="statUpdatedAt" class="text-[10px] md:text-xs text-white/70 mt-1 md:mt-2 truncate">อัปเดตล่าสุด: -</p>
        </div>
    </div>
    
    <!-- Perfect Sessions -->
    <div class="stat-sheen glow-card glass rounded-xl md:rounded-2xl p-4 md:p-5 bg-gradient-to-br from-teal-400 via-teal-500 to-cyan-600 relative overflow-hidden">
        <div class="absolute -top-4 -right-4 w-16 h-16 md:w-20 md:h-20 bg-white/10 rounded-full blur-xl"></div>
        <div class="relative">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl md:text-2xl">🛡️</span>
                <span class="text-xs md:text-sm font-semibold text-white/90">เข้าเรียนครบ</span>
            </div>
            <div class="text-3xl md:text-4xl font-black text-white" id="statPerfectSessions">0</div>
            <p class="text-[10px] md:text-xs text-white/70 mt-1 md:mt-2">คาบที่ไม่มีขาด</p>
        </div>
    </div>
</div>

<!-- Reports Section -->
<div class="glass rounded-2xl md:rounded-3xl shadow-xl border border-white/20 overflow-hidden">
    <!-- Section Header -->
    <div class="p-4 md:p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-white/50 to-white/30 dark:from-gray-800/50 dark:to-gray-800/30">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-clipboard-list text-white text-lg md:text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white">รายการรายงานการสอน</h2>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">บันทึกการสอนทั้งหมดของคุณ</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs font-semibold rounded-full">
                    <i class="fas fa-sync-alt mr-1"></i> Auto-Refresh
                </span>
            </div>
        </div>
    </div>
    
    <!-- Desktop Table View -->
    <div class="desktop-table-view table-container overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-indigo-500/90 via-purple-500/90 to-pink-500/90">
                <tr>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap">
                        <span class="hidden md:inline">📅</span> วันที่
                    </th>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap">
                        <span class="hidden md:inline">📖</span> วิชา
                    </th>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap">
                        <span class="hidden md:inline">🏫</span> ห้อง
                    </th>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap">
                        <span class="hidden md:inline">⏰</span> คาบ
                    </th>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap hidden lg:table-cell">
                        📝 แผน/หัวข้อ
                    </th>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap hidden xl:table-cell">
                        👨‍🏫 กิจกรรม
                    </th>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap">
                        <span class="hidden md:inline">🙋‍♂️</span> ขาด
                    </th>
                    <th class="py-4 px-3 md:px-4 text-center text-white font-semibold text-xs md:text-sm whitespace-nowrap">
                        🔍 จัดการ
                    </th>
                </tr>
            </thead>
            <tbody id="reportTableBody" class="divide-y divide-gray-200/70 dark:divide-gray-700/50 bg-white/50 dark:bg-gray-800/50">
                <!-- Data will be loaded by JS -->
            </tbody>
        </table>
    </div>
    
    <!-- Mobile Card View -->
    <div id="mobileReportCards" class="mobile-card-view p-4 space-y-4">
        <!-- Cards will be loaded by JS -->
        <div class="text-center text-gray-400 py-8">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>กำลังโหลดข้อมูล...</p>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Report -->
<div id="modalAddReport" class="fixed inset-0 flex items-start md:items-center justify-center z-50 hidden transition-all duration-300 bg-black/40 backdrop-blur-sm p-0 pt-0 md:p-4 overflow-y-auto">
    <div class="modal-enter bg-white dark:bg-gray-800 rounded-none md:rounded-3xl shadow-2xl w-full md:max-w-5xl relative border-0 md:border border-gray-200 dark:border-gray-700 min-h-screen md:min-h-0 md:max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-4 md:p-6">
            <button id="closeModalAddReport" class="absolute top-3 right-3 md:top-4 md:right-4 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full text-white text-xl md:text-2xl transition-all">&times;</button>
            <h2 id="modalReportTitle" class="text-xl md:text-2xl font-bold text-white flex items-center gap-2 md:gap-3 pr-10">
                <span class="text-2xl md:text-3xl">➕</span>
                <span>เพิ่มรายงานการสอน</span>
            </h2>
            <p class="text-white/70 text-sm mt-1 hidden md:block">กรอกข้อมูลรายงานการสอนของคุณ</p>
        </div>
        
        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
            <form id="formAddReport" class="space-y-4 md:space-y-6" enctype="multipart/form-data">
                <!-- Date & Subject -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div class="group">
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                            <span class="w-7 h-7 md:w-8 md:h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">📅</span>
                            วันที่ <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="report_date" id="reportDate" required 
                            class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200 hover:border-blue-300" />
                    </div>
                    <div class="group">
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                            <span class="w-7 h-7 md:w-8 md:h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600 dark:text-purple-400">📖</span>
                            ชื่อวิชา <span class="text-red-500">*</span>
                        </label>
                        <select name="subject_id" id="subjectSelect" required 
                            class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200 hover:border-purple-300">
                            <option value="">-- เลือกวิชา --</option>
                        </select>
                    </div>
                </div>
                
                <!-- Room Selection Area -->
                <div id="classRoomSelectArea" class="transition-all duration-300"></div>
                
                <!-- Plan Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div class="group">
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                            <span class="w-7 h-7 md:w-8 md:h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center text-green-600 dark:text-green-400">📋</span>
                            เลขแผนการสอน
                        </label>
                        <input type="text" name="plan_number" placeholder="เช่น 1, 2, 3..."
                            class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200 hover:border-green-300" />
                    </div>
                    <div class="group">
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                            <span class="w-7 h-7 md:w-8 md:h-8 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center text-amber-600 dark:text-amber-400">📝</span>
                            หัวข้อ/สาระการเรียนรู้
                        </label>
                        <input type="text" name="plan_topic" placeholder="ระบุหัวข้อที่สอน"
                            class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200 hover:border-amber-300" />
                    </div>
                </div>
                
                <!-- Activity -->
                <div class="group">
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                        <span class="w-7 h-7 md:w-8 md:h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400">👨‍🏫</span>
                        กิจกรรมการเรียนรู้
                    </label>
                    <textarea name="activity" rows="2" placeholder="อธิบายกิจกรรมที่จัดในคาบเรียน"
                        class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200 hover:border-indigo-300 resize-none"></textarea>
                </div>
                
                <!-- Student Attendance -->
                <div class="group">
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                        <span class="w-7 h-7 md:w-8 md:h-8 bg-rose-100 dark:bg-rose-900/30 rounded-lg flex items-center justify-center text-rose-600 dark:text-rose-400">🙋‍♂️</span>
                        รายชื่อนักเรียน
                    </label>
                    <div id="studentAttendanceArea" class="rounded-xl overflow-hidden">
                        <div class="text-gray-400 dark:text-gray-500 text-sm bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 text-center">
                            <i class="fas fa-users text-2xl mb-2"></i>
                            <p>เลือกห้องเรียนและคาบก่อนเพื่อแสดงรายชื่อนักเรียน 🎓</p>
                        </div>
                    </div>
                    <textarea name="absent_students" class="hidden"></textarea>
                </div>
                
                <!-- KPA Reflections -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700/50 dark:to-gray-700/50 rounded-xl p-4 md:p-5">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <span class="text-xl">💡</span> สะท้อนคิด (KPA)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-2 font-medium text-blue-700 dark:text-blue-300 text-sm flex items-center gap-1">
                                <span class="w-5 h-5 bg-blue-500 text-white rounded-full text-xs flex items-center justify-center font-bold">K</span>
                                ความรู้ (Knowledge)
                            </label>
                            <textarea name="reflection_k" rows="3" placeholder="ผู้เรียนได้ความรู้อะไรบ้าง"
                                class="w-full border-2 border-blue-200 dark:border-blue-800 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-purple-700 dark:text-purple-300 text-sm flex items-center gap-1">
                                <span class="w-5 h-5 bg-purple-500 text-white rounded-full text-xs flex items-center justify-center font-bold">P</span>
                                ทักษะ (Practice)
                            </label>
                            <textarea name="reflection_p" rows="3" placeholder="ผู้เรียนได้ฝึกทักษะอะไร"
                                class="w-full border-2 border-purple-200 dark:border-purple-800 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-pink-700 dark:text-pink-300 text-sm flex items-center gap-1">
                                <span class="w-5 h-5 bg-pink-500 text-white rounded-full text-xs flex items-center justify-center font-bold">A</span>
                                เจตคติ (Attitude)
                            </label>
                            <textarea name="reflection_a" rows="3" placeholder="ผู้เรียนมีเจตคติอย่างไร"
                                class="w-full border-2 border-pink-200 dark:border-pink-800 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-pink-500/20 focus:border-pink-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Problems & Suggestions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div class="group">
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                            <span class="w-7 h-7 md:w-8 md:h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 dark:text-red-400">❗</span>
                            ปัญหา/อุปสรรค
                        </label>
                        <textarea name="problems" rows="2" placeholder="ระบุปัญหาที่พบในการสอน (ถ้ามี)"
                            class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200 hover:border-red-300 resize-none"></textarea>
                    </div>
                    <div class="group">
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 text-sm md:text-base flex items-center gap-2">
                            <span class="w-7 h-7 md:w-8 md:h-8 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center text-teal-600 dark:text-teal-400">💬</span>
                            ข้อเสนอแนะ
                        </label>
                        <textarea name="suggestions" rows="2" placeholder="ข้อเสนอแนะในการปรับปรุง"
                            class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-all duration-200 hover:border-teal-300 resize-none"></textarea>
                    </div>
                </div>
                
                <!-- Image Uploads -->
                <div id="roomImageInputsArea"></div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 md:p-5 flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" id="cancelAddReport" class="w-full sm:w-auto px-5 py-2.5 md:px-6 md:py-3 rounded-xl bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-100 font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fas fa-times"></i>
                <span>ยกเลิก</span>
            </button>
            <button type="submit" form="formAddReport" class="w-full sm:w-auto px-5 py-2.5 md:px-6 md:py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                <span>บันทึก</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal: Attendance Details -->
<div id="attendanceModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-black/40 backdrop-blur-sm p-2 md:p-4">
    <div role="dialog" aria-modal="true" aria-label="รายละเอียดการเข้าเรียน" id="attendanceModalInner" class="modal-enter bg-white dark:bg-gray-800 rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-4xl relative border border-gray-200 dark:border-gray-700 max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 p-4 md:p-6">
            <button id="closeAttendanceModal" class="absolute top-3 right-3 md:top-4 md:right-4 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full text-white text-xl md:text-2xl transition-all">&times;</button>
            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2 md:gap-3 pr-10">
                <span class="text-2xl md:text-3xl">📋</span>
                <span>รายละเอียดการเข้าเรียน</span>
            </h2>
        </div>
        
        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6">
            <div id="attendanceModalContent"></div>
        </div>
    </div>
</div>

<!-- Pass PHP variables to JavaScript -->
<script>
    window.TEACHER_ID = <?php echo isset($_SESSION['user']['Teach_id']) ? json_encode($_SESSION['user']['Teach_id']) : 'null'; ?>;
    window.TEACHER_USERNAME = <?php echo json_encode($_SESSION['username'] ?? ''); ?>;
</script>

<!-- Floating Action Button (Mobile Only) -->
<button id="fabAddReport" class="fab-mobile md:hidden" aria-label="เพิ่มรายงานใหม่">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
    </svg>
</button>

<!-- External JS -->
<script src="js/teaching-report.js?v=5"></script>

