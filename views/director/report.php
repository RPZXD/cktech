<?php
/**
 * Director Report View
 * MVC Pattern - Premium view for director report monitoring
 */
?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<style>
    /* FullCalendar Premium Styling */
    .fc { 
        --fc-border-color: #e2e8f0;
        --fc-today-bg-color: rgba(99, 102, 241, 0.08);
        font-family: 'Mali', sans-serif; 
    }
    .fc .fc-toolbar-title { font-weight: 900; color: #4f46e5; font-size: 1.1rem; }
    .fc .fc-button-primary { 
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
        border: none !important; border-radius: 10px !important; font-weight: 700 !important; 
        padding: 8px 14px !important; font-size: 12px !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25) !important;
    }
    .fc .fc-button-primary:hover { transform: translateY(-1px); }
    .fc .fc-button-active { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; }
    .fc-daygrid-event { border-radius: 6px !important; padding: 2px 5px !important; font-weight: 700 !important; font-size: 10px !important; border: none !important; }
    .fc .fc-daygrid-day.fc-day-today { background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%) !important; }

    /* DataTables */
    .dataTables_wrapper .dataTables_filter input { border: 2px solid #e2e8f0 !important; border-radius: 10px !important; padding: 8px 14px !important; font-weight: 600 !important; }
    .dataTables_wrapper .dataTables_filter input:focus { border-color: #6366f1 !important; outline: none !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important; border: none !important; color: white !important; border-radius: 8px !important; }
</style>

<div class="space-y-6 pb-8">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/30">
                    📑
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">ตรวจสอบรายงานการสอน</h1>
                    <p class="text-slate-400 font-medium text-sm">ดูรายงานของครูทุกกลุ่มสาระ</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">กลุ่มสาระ</label>
                <select id="departmentSelect" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 font-bold text-sm focus:border-indigo-500 focus:outline-none transition-colors">
                    <option value="">-- เลือกกลุ่มสาระ --</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">ครูผู้สอน</label>
                <select id="teacherSelect" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 font-bold text-sm focus:border-indigo-500 focus:outline-none transition-colors" disabled>
                    <option value="">-- เลือกครู --</option>
                </select>
            </div>
            <div class="flex items-end">
                <button id="btnReload" class="w-full flex items-center justify-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-indigo-500/25 active:scale-95 transition-all">
                    <i class="fas fa-sync-alt"></i> รีเฟรช
                </button>
            </div>
        </div>
    </div>

    <!-- No Data State -->
    <div id="noDataMsg" class="bg-white dark:bg-slate-900 rounded-3xl p-16 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 shadow-lg">
        <div class="w-20 h-20 mx-auto bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-4xl mb-5">
            🏢
        </div>
        <h3 class="text-lg font-black text-slate-500 dark:text-slate-400 mb-2">กรุณาเลือกกลุ่มสาระและครู</h3>
        <p class="text-sm text-slate-400">เลือกกลุ่มสาระจากรายการด้านบนเพื่อดูรายงานการสอน</p>
    </div>

    <!-- Content Section -->
    <div id="reportSection" class="hidden space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">รายงานทั้งหมด</p>
                    <p id="statTotal" class="text-2xl font-black text-slate-800 dark:text-white">0</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-check text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">เดือนนี้</p>
                    <p id="statMonth" class="text-2xl font-black text-emerald-600">0</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-amber-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">รายวิชา</p>
                    <p id="statSubjects" class="text-2xl font-black text-amber-600">0</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-door-open text-purple-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">ห้องเรียน</p>
                    <p id="statRooms" class="text-2xl font-black text-purple-600">0</p>
                </div>
            </div>
        </div>

        <!-- Calendar & Table -->
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            <div class="xl:col-span-3 bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-7 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
                    <h2 class="text-lg font-black text-slate-800 dark:text-white">ปฏิทินรายงานการสอน</h2>
                </div>
                <div id="calendar"></div>
            </div>

            <div class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-7 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"></div>
                        <h2 class="text-lg font-black text-slate-800 dark:text-white">ตารางรายงาน</h2>
                    </div>
                    <span id="reportCount" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">0 รายการ</span>
                </div>
                <div class="overflow-x-auto">
                    <table id="reportTable" class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                                <th class="p-3 text-left rounded-l-lg">วันที่</th>
                                <th class="p-3 text-left">วิชา</th>
                                <th class="p-3 text-center">ห้อง</th>
                                <th class="p-3 text-center">คาบ</th>
                                <th class="p-3 text-center rounded-r-lg">ดู</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="js/director-report.js"></script>
