<?php
/**
 * Department Weekly Report View
 * MVC Pattern - View for weekly teaching summary in department
 */
?>

<div class="space-y-6 pb-12">
    <!-- Header Card -->
    <div class="glass rounded-[2.5rem] p-8 shadow-xl border border-white/20 slide-up">
        <div class="flex flex-col lg:flex-row justify-between gap-8 items-center text-center lg:text-left">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-indigo-500/20">
                    📅
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-slate-800 dark:text-white">สรุปรายงานการสอนรายสัปดาห์</h1>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">ภาพรวมการจัดการเรียนรู้ของกลุ่มสาระ<?php echo htmlspecialchars($department); ?></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4">
                <div class="flex flex-col items-center lg:items-end gap-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">เลือกสัปดาห์</label>
                    <input type="week" id="weekPicker" class="bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl px-6 py-2.5 focus:border-blue-500 transition-all font-bold text-slate-700 dark:text-slate-200 shadow-sm cursor-pointer">
                </div>
                <div class="flex gap-2 mt-4 lg:mt-5">
                    <button id="btnReload" class="p-3 bg-blue-500 hover:bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-500/20 active:scale-95 transition-all" title="รีเฟรชข้อมูล">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button onclick="window.manager.printWeekly()" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-2xl shadow-lg active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-print"></i> พิมพ์รายงาน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Table -->
    <div id="weeklySection" class="hidden space-y-8 fade-in">
        <div class="glass rounded-[2.5rem] border border-white/20 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table id="weeklyTable" class="w-full text-sm">
                    <thead>
                        <tr id="weeklyTableHead" class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-black tracking-widest">
                            <!-- JS Filled -->
                        </tr>
                    </thead>
                    <tbody id="weeklyTableBody" class="divide-y divide-slate-50 dark:divide-slate-800">
                        <!-- JS Filled -->
                    </tbody>
                </table>
            </div>
            <div class="p-6 bg-slate-50/50 dark:bg-slate-800/30 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-400 italic font-medium">คลิกที่ "คาบสอน" เพื่อดูรายละเอียดบันทึกการสอนรายคาบ</span>
            </div>
        </div>

        <!-- Weekly Stats Chart -->
        <div class="glass rounded-[2.5rem] p-8 border border-white/20 shadow-xl">
            <h3 class="text-xl font-black mb-8 flex items-center gap-3">
                <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
                วิเคราะห์ความหนาแน่นรายวัน
            </h3>
            <div class="h-[300px]">
                <canvas id="weeklyChartCanvas"></canvas>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="noDataMsg" class="hidden glass rounded-[2.5rem] p-20 text-center fade-in">
        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">📭</div>
        <h2 class="text-2xl font-black text-slate-400">ไม่พบข้อมูลรายงานในสัปดาห์นี้</h2>
        <p class="text-slate-500 mt-2 font-medium">กรุณาเลือกสัปดาห์อื่นหรือตรวจสอบภายหลัง</p>
    </div>
</div>

<script src="js/department-weekly.js"></script>
<script>
    window.manager = new DepartmentWeeklyManager({
        department: '<?php echo $department; ?>'
    });
</script>
