<?php
/**
 * Director Weekly Report View
 * MVC Pattern - Premium weekly report dashboard
 */
?>

<style>
    .week-card {
        background: white;
        border-radius: 1.25rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    .week-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    
    .day-cell {
        min-height: 80px;
        background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);
        border-radius: 0.75rem;
        transition: all 0.2s;
    }
    .day-cell:hover { background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%); }
    
    .period-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
    }
    .period-badge:hover { transform: scale(1.05); }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>

<div class="space-y-6 pb-8">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-amber-500/30">
                    📅
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">รายงานรายสัปดาห์</h1>
                    <p class="text-slate-400 font-medium text-sm">สรุปการสอนรายสัปดาห์ของทุกกลุ่มสาระ</p>
                </div>
            </div>
            
            <!-- Week Selector -->
            <div class="flex items-center gap-3">
                <button id="prevWeek" class="w-10 h-10 bg-slate-100 hover:bg-slate-200 rounded-xl flex items-center justify-center transition-colors">
                    <i class="fas fa-chevron-left text-slate-600"></i>
                </button>
                <div class="px-6 py-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl min-w-[200px] text-center">
                    <span id="weekDisplay" class="font-black text-amber-700 dark:text-amber-400">สัปดาห์ที่ 1</span>
                    <p id="weekRange" class="text-xs font-bold text-slate-500 mt-1">1 - 7 มกราคม 2568</p>
                </div>
                <button id="nextWeek" class="w-10 h-10 bg-slate-100 hover:bg-slate-200 rounded-xl flex items-center justify-center transition-colors">
                    <i class="fas fa-chevron-right text-slate-600"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Department Filter -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-sm font-bold text-slate-500">กลุ่มสาระ:</label>
            <select id="departmentFilter" class="flex-1 md:flex-none md:w-64 bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 font-bold text-sm focus:border-amber-500 focus:outline-none transition-colors">
                <option value="">ทุกกลุ่มสาระ</option>
            </select>
            <button id="btnPrint" class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-rose-500/25 active:scale-95 transition-all ml-auto">
                <i class="fas fa-print"></i>
                <span class="hidden sm:inline">พิมพ์</span>
            </button>
        </div>
    </div>

    <!-- Weekly Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-file-alt text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">รายงานสัปดาห์นี้</p>
                <p id="weekTotal" class="text-2xl font-black text-slate-800 dark:text-white">0</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">ครูที่บันทึก</p>
                <p id="weekTeachers" class="text-2xl font-black text-emerald-600">0</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-book text-purple-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">รายวิชา</p>
                <p id="weekSubjects" class="text-2xl font-black text-purple-600">0</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-door-open text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">ห้องเรียน</p>
                <p id="weekRooms" class="text-2xl font-black text-amber-600">0</p>
            </div>
        </div>
    </div>

    <!-- Weekly Grid -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1.5 h-7 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></div>
            <h2 class="text-lg font-black text-slate-800 dark:text-white">ตารางรายสัปดาห์</h2>
        </div>
        
        <!-- Day Headers -->
        <div class="grid grid-cols-5 gap-3 mb-4">
            <div class="text-center py-3 bg-blue-50 rounded-xl"><span class="font-black text-blue-700 text-sm">จันทร์</span></div>
            <div class="text-center py-3 bg-pink-50 rounded-xl"><span class="font-black text-pink-700 text-sm">อังคาร</span></div>
            <div class="text-center py-3 bg-emerald-50 rounded-xl"><span class="font-black text-emerald-700 text-sm">พุธ</span></div>
            <div class="text-center py-3 bg-amber-50 rounded-xl"><span class="font-black text-amber-700 text-sm">พฤหัสบดี</span></div>
            <div class="text-center py-3 bg-purple-50 rounded-xl"><span class="font-black text-purple-700 text-sm">ศุกร์</span></div>
        </div>
        
        <!-- Day Cells -->
        <div id="weeklyGrid" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <!-- JS will populate -->
            <div class="day-cell p-4 flex items-center justify-center text-slate-400">กำลังโหลด...</div>
            <div class="day-cell p-4 flex items-center justify-center text-slate-400">กำลังโหลด...</div>
            <div class="day-cell p-4 flex items-center justify-center text-slate-400">กำลังโหลด...</div>
            <div class="day-cell p-4 flex items-center justify-center text-slate-400">กำลังโหลด...</div>
            <div class="day-cell p-4 flex items-center justify-center text-slate-400">กำลังโหลด...</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1.5 h-7 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
            <h2 class="text-lg font-black text-slate-800 dark:text-white">กราฟรายงานตามวัน</h2>
        </div>
        <div class="chart-container">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/director-weekly.js"></script>
