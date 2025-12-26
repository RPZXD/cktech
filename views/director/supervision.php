<?php
/**
 * Director Supervision View
 * MVC Pattern - Premium supervision dashboard for directors
 */
?>

<style>
    .supervision-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    .supervision-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    
    .quality-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
    }
</style>

<div class="space-y-6 pb-8">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-purple-500/30">
                    👁️
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">การนิเทศการสอน</h1>
                    <p class="text-slate-400 font-medium text-sm">ประเมินและติดตามคุณภาพการสอน</p>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <select id="departmentFilter" class="bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 font-bold text-sm focus:border-purple-500 focus:outline-none transition-colors">
                    <option value="">ทุกกลุ่มสาระ</option>
                </select>
                <select id="statusFilter" class="bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 font-bold text-sm focus:border-purple-500 focus:outline-none transition-colors">
                    <option value="">ทุกสถานะ</option>
                    <option value="pending">รอประเมิน</option>
                    <option value="completed">ประเมินแล้ว</option>
                </select>
                <button id="btnRefresh" class="flex items-center gap-2 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-purple-500/25 active:scale-95 transition-all">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-file-signature text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">นิเทศทั้งหมด</p>
                <p id="statTotal" class="text-2xl font-black text-slate-800 dark:text-white">0</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">รอประเมิน</p>
                <p id="statPending" class="text-2xl font-black text-amber-600">0</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">ประเมินแล้ว</p>
                <p id="statCompleted" class="text-2xl font-black text-emerald-600">0</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-star text-purple-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">คะแนนเฉลี่ย</p>
                <p id="statAvgScore" class="text-2xl font-black text-purple-600">-</p>
            </div>
        </div>
    </div>

    <!-- Supervision List -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"></div>
                <h2 class="text-lg font-black text-slate-800 dark:text-white">รายการนิเทศการสอน</h2>
            </div>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="ค้นหา..." class="bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 pl-10 font-medium text-sm focus:border-purple-500 focus:outline-none transition-colors w-56">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="supervisionTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                        <th class="p-4 text-left rounded-l-lg">วันที่</th>
                        <th class="p-4 text-left">ครูผู้สอน</th>
                        <th class="p-4 text-left">กลุ่มสาระ</th>
                        <th class="p-4 text-center">คะแนน</th>
                        <th class="p-4 text-center">ระดับคุณภาพ</th>
                        <th class="p-4 text-center">สถานะ</th>
                        <th class="p-4 text-center rounded-r-lg">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="supervisionBody">
                    <tr><td colspan="7" class="p-12 text-center text-slate-400 font-medium">กำลังโหลดข้อมูล...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Evaluation Modal -->
<div id="evaluationModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="hideEvaluationModal()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-purple-500 to-violet-600 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h2 class="text-lg font-black text-white">แบบประเมินการนิเทศ</h2>
                </div>
                <button onclick="hideEvaluationModal()" class="text-white/80 hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="evaluationContent" class="p-6 max-h-[70vh] overflow-y-auto">
                <!-- JS will populate -->
            </div>
        </div>
    </div>
</div>

<script src="js/director-supervision.js"></script>

<script>
function hideEvaluationModal() {
    document.getElementById('evaluationModal').classList.add('hidden');
}
</script>
