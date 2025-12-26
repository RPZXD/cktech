<?php
/**
 * Director Statistics View
 * MVC Pattern - Premium statistics dashboard for directors
 */
?>

<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<style>
    .chart-card {
        background: white;
        border-radius: 1.5rem;
        padding: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }
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
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30">
                    📊
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">สถิติและวิเคราะห์ข้อมูล</h1>
                    <p class="text-slate-400 font-medium text-sm">ภาพรวมข้อมูลการสอนทั้งโรงเรียน</p>
                </div>
            </div>
            
            <!-- Date Range Picker -->
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="dateRange" class="bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 pr-10 font-bold text-sm focus:border-emerald-500 focus:outline-none transition-colors cursor-pointer" readonly>
                    <i class="fas fa-calendar-alt absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                </div>
                <button id="btnRefresh" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-3 rounded-xl font-bold text-sm shadow-lg shadow-emerald-500/25 active:scale-95 transition-all">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">รายงานทั้งหมด</p>
                    <p id="totalReports" class="text-2xl font-black text-slate-800 dark:text-white">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">ครูผู้สอน</p>
                    <p id="totalTeachers" class="text-2xl font-black text-emerald-600">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-eye text-purple-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">การนิเทศ</p>
                    <p id="totalSupervisions" class="text-2xl font-black text-purple-600">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-trophy text-amber-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">เกียรติบัตร</p>
                    <p id="totalCertificates" class="text-2xl font-black text-amber-600">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="chart-card">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-7 bg-gradient-to-b from-blue-500 to-indigo-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white">รายงานแยกตามกลุ่มสาระ</h3>
            </div>
            <div class="chart-container">
                <canvas id="chartDepartments"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-7 bg-gradient-to-b from-emerald-500 to-teal-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white">แนวโน้มการบันทึกรายสัปดาห์</h3>
            </div>
            <div class="chart-container">
                <canvas id="chartWeekly"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-7 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white">คุณภาพการนิเทศ</h3>
            </div>
            <div class="chart-container">
                <canvas id="chartQuality"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-7 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white">เกียรติบัตรแยกประเภท</h3>
            </div>
            <div class="chart-container">
                <canvas id="chartCertTypes"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Teachers Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white text-lg">ครูที่บันทึกมากที่สุด</h3>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="topTeachersTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                        <th class="p-4 text-left rounded-l-lg">อันดับ</th>
                        <th class="p-4 text-left">ชื่อครู</th>
                        <th class="p-4 text-left">กลุ่มสาระ</th>
                        <th class="p-4 text-center">จำนวนรายงาน</th>
                        <th class="p-4 text-center rounded-r-lg">เปอร์เซ็นต์</th>
                    </tr>
                </thead>
                <tbody id="topTeachersBody">
                    <tr><td colspan="5" class="p-8 text-center text-slate-400">กำลังโหลดข้อมูล...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/locale/th.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/director-stat.js"></script>
