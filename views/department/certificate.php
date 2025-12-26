<?php
/**
 * Department Certificate View
 * MVC Pattern - Premium View for department certificate reports
 */
?>

<style>
    /* Custom Table Styling */
    #departmentCertificateTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    #departmentCertificateTable thead th {
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
    #departmentCertificateTable thead th:first-child { border-radius: 12px 0 0 0; }
    #departmentCertificateTable thead th:last-child { border-radius: 0 12px 0 0; }
    #departmentCertificateTable tbody td {
        padding: 14px 12px;
        font-size: 12px;
        color: #475569;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    #departmentCertificateTable tbody tr:hover {
        background: rgba(251, 191, 36, 0.05);
    }

    /* Mobile Card System */
    .cert-mobile-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        transition: all 0.2s;
    }
    .cert-mobile-card:active { transform: scale(0.98); }

    /* Teacher Summary Card */
    .teacher-summary-card {
        background: white;
        border-radius: 1.5rem;
        padding: 1.5rem;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        transition: all 0.3s;
    }
    .teacher-summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.1);
    }

    /* Animation */
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-slide-up { animation: slideUp 0.4s ease-out forwards; }
</style>

<div class="space-y-6 pb-8">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-amber-500/30">
                    🏆
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">รายงานเกียรติบัตรกลุ่มสาระ</h1>
                    <p class="text-slate-400 font-medium text-sm">ภาพรวมรางวัลและเกียรติบัตรในสังกัด</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <button id="btnExportDepartment" class="flex items-center gap-2 px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/25 active:scale-95 transition-all text-sm">
                    <i class="fas fa-file-csv"></i>
                    <span class="hidden sm:inline">ส่งออก CSV</span>
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-5 border border-blue-100 dark:border-blue-900/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-certificate text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">เกียรติบัตรทั้งหมด</p>
                        <p id="deptTotalCerts" class="text-2xl font-black text-blue-700 dark:text-blue-400">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-900/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-800/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chalkboard-teacher text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">ครูผู้บันทึก</p>
                        <p id="deptTotalTeachers" class="text-2xl font-black text-emerald-700 dark:text-emerald-400">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-2xl p-5 border border-purple-100 dark:border-purple-900/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-800/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-star text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-purple-500 uppercase tracking-wider">ครูดีเด่น</p>
                        <p id="deptTopTeacher" class="text-sm font-black text-purple-700 dark:text-purple-400 truncate max-w-[120px]">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-rose-50 dark:bg-rose-900/20 rounded-2xl p-5 border border-rose-100 dark:border-rose-900/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-100 dark:bg-rose-800/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-rose-600"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider">เดือนนี้</p>
                        <p id="deptThisMonth" class="text-2xl font-black text-rose-700 dark:text-rose-400">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & View Switcher -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 lg:p-5 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2 lg:gap-3">
                <select id="filterTeacher" class="flex-1 min-w-[140px] bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-bold focus:outline-none focus:border-amber-500 transition-colors">
                    <option value="">ครูทุกคน</option>
                </select>
                <select id="filterAwardType" class="flex-1 min-w-[120px] bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-bold focus:outline-none focus:border-amber-500 transition-colors">
                    <option value="">ทุกประเภท</option>
                </select>
                <select id="filterDeptTerm" class="w-24 bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-bold focus:outline-none focus:border-amber-500 transition-colors">
                    <option value="">เทอม</option>
                </select>
                <select id="filterDeptYear" class="w-28 bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-bold focus:outline-none focus:border-amber-500 transition-colors">
                    <option value="">ปีการศึกษา</option>
                </select>
                <button id="btnClearDeptFilter" class="p-2.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all" title="ล้างตัวกรอง">
                    <i class="fas fa-times-circle text-lg"></i>
                </button>
            </div>

            <!-- View Switcher -->
            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl self-center lg:self-auto">
                <button id="btnTableView" class="px-4 py-2 rounded-lg text-xs font-black transition-all bg-white dark:bg-slate-700 text-amber-600 shadow-sm flex items-center gap-1.5">
                    <i class="fas fa-table"></i>
                    <span class="hidden sm:inline">ตาราง</span>
                </button>
                <button id="btnSummaryView" class="px-4 py-2 rounded-lg text-xs font-black transition-all text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 flex items-center gap-1.5">
                    <i class="fas fa-id-card"></i>
                    <span class="hidden sm:inline">สรุปครู</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table View -->
    <div id="tableView" class="bg-white dark:bg-slate-900 rounded-2xl lg:rounded-3xl shadow-lg border border-slate-100 dark:border-slate-800 overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table id="departmentCertificateTable" class="w-full">
                <thead>
                    <tr>
                        <th class="text-left">ชื่อครู</th>
                        <th class="text-left">นักเรียน</th>
                        <th class="text-center">ชั้น/ห้อง</th>
                        <th class="text-left">รางวัล</th>
                        <th class="text-center">ระดับ</th>
                        <th class="text-center">ประเภท</th>
                        <th class="text-center">วันที่</th>
                        <th class="text-center">เทอม/ปี</th>
                        <th class="text-center w-16">ดู</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="loading-row">
                        <td colspan="9" class="p-16 text-center">
                            <div class="inline-flex items-center gap-3 px-5 py-3 bg-amber-50 text-amber-600 rounded-xl font-bold text-sm">
                                <i class="fas fa-circle-notch animate-spin"></i> กำลังโหลดข้อมูล...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List -->
        <div id="mobileCertList" class="lg:hidden p-4 space-y-3 max-h-[70vh] overflow-y-auto">
            <div class="text-center py-12">
                <i class="fas fa-circle-notch animate-spin text-amber-500 text-2xl"></i>
                <p class="text-slate-400 font-bold mt-3">กำลังโหลด...</p>
            </div>
        </div>
    </div>

    <!-- Summary View (Teacher Cards) -->
    <div id="summaryView" class="hidden">
        <div id="teacherSummaryContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            <!-- JS Populated -->
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="modalCertificateDetail" class="fixed inset-0 z-[60] overflow-y-auto hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="window.departmentCertManager.closeDetailModal()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-slide-up">
            <div class="p-5 bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-award"></i>
                    </div>
                    <h2 class="text-lg font-black text-white">รายละเอียดเกียรติบัตร</h2>
                </div>
                <button onclick="window.departmentCertManager.closeDetailModal()" class="text-white/80 hover:text-white transition-colors text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="certificateDetailContent" class="p-6">
                <!-- JS Populated content -->
            </div>
        </div>
    </div>
</div>

<script>
    window.departmentName = <?php echo json_encode($department); ?>;
    window.userRole = 'department';
</script>
<script src="js/department-certificate.js"></script>
<script>
    // Instantiate the manager after the class is loaded
    document.addEventListener('DOMContentLoaded', function() {
        window.departmentCertManager = new DepartmentCertificateManager({
            department: window.departmentName
        });
    });
</script>

