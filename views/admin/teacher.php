<?php
/**
 * Admin User Management View
 * MVC Pattern - View for teacher/user management
 * Enhanced UI/UX with Tailwind CSS - Mobile Responsive
 */
?>

<style>
    .toggle-switch { position: relative; display: inline-block; width: 50px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
    .toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .toggle-slider { background-color: #4ade80; }
    input:checked + .toggle-slider:before { transform: translateX(26px); }
    
    /* Custom scrollbar */
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.5); border-radius: 4px; }
    .table-scroll::-webkit-scrollbar-track { background: rgba(139, 92, 246, 0.1); }
    
    /* Floating animation */
    .float-animation { animation: floating 3s ease-in-out infinite; }
    @keyframes floating { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
    
    /* Status badges */
    .status-badge { transition: all 0.2s ease; }
    .status-badge:hover { transform: scale(1.05); }
</style>

<!-- Page Header -->
<div class="mb-6 md:mb-8">
    <div class="relative glass rounded-2xl md:rounded-3xl p-5 md:p-8 shadow-xl overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute -top-20 -right-20 w-40 h-40 md:w-60 md:h-60 bg-gradient-to-br from-blue-400/20 to-indigo-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-3xl md:text-4xl float-animation">👤</span>
                    <span class="px-3 py-1 bg-blue-500 text-white text-xs font-bold rounded-full uppercase tracking-wider">User Management</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                    จัดการผู้ใช้ระบบ
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm md:text-base">
                    เพิ่ม แก้ไข ลบ ผู้ใช้ทุกระดับในระบบ
                </p>
            </div>
            <div class="flex flex-wrap gap-2 md:gap-3">
                <button id="btnAddTeacher" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium active:scale-95">
                    <i class="fas fa-user-plus mr-2"></i>
                    <span>เพิ่มผู้ใช้ใหม่</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="glass rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg mb-6">
    <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="fas fa-filter text-purple-500"></i> ตัวกรองข้อมูล
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">กลุ่มสาระ</label>
            <select id="filter-major" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">บทบาท</label>
            <select id="filter-role" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
                <option value="T">👩‍🏫 ครู</option>
                <option value="HOD">👨‍💼 หัวหน้ากลุ่มสาระ</option>
                <option value="VP">👔 รองผู้บริหาร</option>
                <option value="OF">📋 เจ้าหน้าที่</option>
                <option value="DIR">🏫 ผู้อำนวยการ</option>
                <option value="ADM">🛡️ ผู้ดูแลระบบ</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">สถานะ</label>
            <select id="filter-status" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                <option value="">-- ทั้งหมด --</option>
                <option value="1">🟢 ปกติ</option>
                <option value="2">🚚 ย้าย</option>
                <option value="3">🎉 เกษียณ</option>
                <option value="4">🏠 ลาออก</option>
                <option value="9">⚰️ เสียชีวิต</option>
            </select>
        </div>
        <div class="flex items-end">
            <button id="filter-clear" class="w-full px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 text-sm font-medium transition-all flex items-center justify-center gap-2">
                <i class="fas fa-times"></i>
                <span>ล้างตัวกรอง</span>
            </button>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-white/50 to-white/30 dark:from-gray-800/50 dark:to-gray-800/30">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fas fa-users text-white text-lg md:text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white">รายชื่อผู้ใช้ในระบบ</h2>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">คลิกชื่อครู กลุ่มสาระ หรือบทบาทเพื่อแก้ไขข้อมูล</p>
            </div>
        </div>
    </div>
    
    <div class="table-scroll overflow-x-auto">
        <table id="teacherTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gradient-to-r from-indigo-500/90 via-purple-500/90 to-pink-500/90">
                <tr>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">
                        <span class="hidden md:inline">🆔</span> รหัสครู
                    </th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">
                        <span class="hidden md:inline">👩‍🏫</span> ชื่อครู
                    </th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden sm:table-cell">
                        🏢 กลุ่มสาระ
                    </th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">
                        <span class="hidden md:inline">🛡️</span> บทบาท
                    </th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap hidden md:table-cell">
                        ✅ สถานะ
                    </th>
                    <th class="px-4 py-4 text-center font-semibold text-white whitespace-nowrap">
                        ⚙️ จัดการ
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                <!-- JS will fill -->
            </tbody>
        </table>
    </div>
    
    <div class="p-4 md:p-6 border-t border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50">
        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
            💡 <span class="font-medium">เคล็ดลับ:</span> คลิกที่ชื่อ กลุ่มสาระ หรือบทบาทเพื่อแก้ไขข้อมูลโดยตรง
        </p>
    </div>
</div>

<!-- External JS for User Management -->
<script src="js/admin-teacher.js"></script>
