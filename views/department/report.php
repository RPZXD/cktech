<?php
/**
 * Department Report View
 * MVC Pattern - Premium View for department report monitoring
 */
?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<style>
    /* FullCalendar Premium Styling */
    .fc { 
        --fc-border-color: #e2e8f0;
        --fc-today-bg-color: rgba(59, 130, 246, 0.08);
        font-family: 'Mali', sans-serif; 
    }
    .fc .fc-toolbar-title { 
        font-weight: 900; 
        color: #1e40af; 
        font-size: 1.1rem; 
    }
    .fc .fc-button-primary { 
        background: #3b82f6 !important;
        border: none !important; 
        border-radius: 10px !important; 
        font-weight: 700 !important; 
        padding: 8px 14px !important; 
        font-size: 12px !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25) !important;
    }
    .fc .fc-button-primary:hover { 
        background: #2563eb !important;
    }
    .fc .fc-button-active { 
        background: #1d4ed8 !important; 
    }
    .fc .fc-daygrid-day-number {
        font-weight: 700;
        color: #475569;
        padding: 8px !important;
    }
    .fc .fc-col-header-cell-cushion {
        font-weight: 800;
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        padding: 12px 0 !important;
    }
    .fc-daygrid-event { 
        border-radius: 6px !important; 
        padding: 3px 6px !important; 
        font-weight: 700 !important; 
        font-size: 10px !important;
        border: none !important;
        margin: 1px 2px !important;
    }
    .fc .fc-daygrid-day.fc-day-today {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%) !important;
    }
    .fc .fc-scrollgrid {
        border: none !important;
    }
    .fc .fc-scrollgrid td {
        border-color: #f1f5f9 !important;
    }
    
    /* DataTables Custom */
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 8px 14px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #3b82f6 !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 2px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 6px 10px !important;
        font-weight: 600 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        font-weight: 700 !important;
        margin: 0 2px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: white !important;
    }
    .dataTables_wrapper .dataTables_info {
        font-weight: 600;
        color: #64748b;
        font-size: 12px;
    }

    /* Custom Table Styling */
    #reportTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    #reportTable thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 2px solid #e2e8f0;
        font-weight: 800;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        padding: 14px 12px;
        white-space: nowrap;
    }
    #reportTable thead th:first-child { border-radius: 12px 0 0 0; }
    #reportTable thead th:last-child { border-radius: 0 12px 0 0; }
    #reportTable tbody td {
        padding: 14px 12px;
        font-size: 12px;
        color: #475569;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    #reportTable tbody tr:hover {
        background: rgba(59, 130, 246, 0.04);
    }
</style>

<div class="space-y-6 pb-8">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-blue-500/30">
                    📑
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">ตรวจสอบรายงานการสอน</h1>
                    <p class="text-slate-400 font-medium text-sm">ดูรายงานย้อนหลังของครูในกลุ่มสาระ</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1 min-w-0 sm:min-w-[240px]">
                    <select id="teacherSelect" class="w-full appearance-none bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 pr-10 focus:border-blue-500 focus:bg-white focus:outline-none transition-all font-bold text-sm text-slate-700 dark:text-slate-200">
                        <option value="">-- เลือกครูในกลุ่มสาระ --</option>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <i class="fas fa-chevron-down text-sm"></i>
                    </div>
                </div>
                <button id="btnReload" class="flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-5 py-3 rounded-xl transition-all active:scale-95 font-bold text-sm shadow-lg shadow-blue-500/25">
                    <i class="fas fa-sync-alt"></i>
                    <span class="sm:hidden">รีเฟรช</span>
                </button>
            </div>
        </div>
    </div>

    <!-- No Teacher Selected State -->
    <div id="noDataMsg" class="bg-white dark:bg-slate-900 rounded-3xl p-16 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 shadow-lg">
        <div class="w-20 h-20 mx-auto bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center text-4xl mb-5">
            👩‍🏫
        </div>
        <h3 class="text-lg font-black text-slate-500 dark:text-slate-400 mb-2">กรุณาเลือกครูเพื่อแสดงข้อมูล</h3>
        <p class="text-sm text-slate-400 dark:text-slate-500">เลือกครูจากรายการด้านบนเพื่อดูรายงานการสอน</p>
    </div>

    <!-- Main Content -->
    <div id="reportSection" class="hidden space-y-6">
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">รายงานทั้งหมด</p>
                    <p id="statTotal" class="text-2xl font-black text-slate-800 dark:text-white">0</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-check text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">เดือนนี้</p>
                    <p id="statMonth" class="text-2xl font-black text-emerald-600">0</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-amber-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">รายวิชา</p>
                    <p id="statSubjects" class="text-2xl font-black text-amber-600">0</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-door-open text-purple-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">ห้องเรียน</p>
                    <p id="statRooms" class="text-2xl font-black text-purple-600">0</p>
                </div>
            </div>
        </div>

        <!-- Calendar & Table Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            <!-- Calendar Section -->
            <div class="xl:col-span-3 bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-7 bg-gradient-to-b from-blue-500 to-indigo-500 rounded-full"></div>
                    <h2 class="text-lg font-black text-slate-800 dark:text-white">ปฏิทินรายงานการสอน</h2>
                </div>
                <div id="calendar"></div>
            </div>

            <!-- Table Section -->
            <div class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-7 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
                        <h2 class="text-lg font-black text-slate-800 dark:text-white">ตารางสรุปรายงาน</h2>
                    </div>
                    <span id="reportCount" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">0 รายการ</span>
                </div>
                <div class="overflow-x-auto -mx-2 px-2">
                    <table id="reportTable" class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left">วันที่</th>
                                <th class="text-left">วิชา</th>
                                <th class="text-center">ห้อง</th>
                                <th class="text-center">คาบ</th>
                                <th class="text-left">หัวข้อ</th>
                                <th class="text-center">ดู</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS filled -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="js/department-report.js"></script>
<script>
    const manager = new DepartmentReportManager({
        department: '<?php echo $department; ?>'
    });
</script>
