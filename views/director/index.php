<?php
/**
 * Director Dashboard View
 * MVC Pattern - Premium dashboard for school administrators
 */
?>

<style>
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    .float-animation { animation: float 3s ease-in-out infinite; }
    
    .stat-card {
        background: white;
        border-radius: 1.5rem;
        padding: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }
    
    .nav-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s;
        cursor: pointer;
    }
    .nav-card:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
</style>

<div class="space-y-8 pb-8">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 rounded-3xl p-8 lg:p-12 shadow-2xl shadow-indigo-500/30">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-purple-400/20 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        
        <div class="relative flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 rounded-full text-white/90 text-sm font-bold mb-4">
                    <i class="fas fa-crown"></i>
                    <span>Director Dashboard</span>
                </div>
                <h1 class="text-3xl lg:text-4xl font-black text-white mb-3">
                    ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['user']['Teach_name'] ?? 'ผู้บริหาร'); ?>
                </h1>
                <p class="text-indigo-100 text-lg max-w-xl">
                    ระบบบริหารจัดการข้อมูลการสอนของ <?php echo $global['nameschool'] ?? 'โรงเรียน'; ?>
                </p>
            </div>
            <div class="text-center">
                <div class="text-6xl float-animation">👨‍💼</div>
                <div class="mt-3 px-4 py-2 bg-white/20 rounded-2xl">
                    <p class="text-white font-bold text-sm">ปีการศึกษา <?php echo $_SESSION['pee'] ?? date('Y') + 543; ?></p>
                    <p class="text-indigo-200 text-xs">ภาคเรียนที่ <?php echo $_SESSION['term'] ?? '1'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-blue-500/30">
                    📑
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">รายงานทั้งหมด</p>
                    <p id="statReports" class="text-3xl font-black text-slate-800">-</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30">
                    👩‍🏫
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">ครูทั้งหมด</p>
                    <p id="statTeachers" class="text-3xl font-black text-slate-800">-</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-purple-500/30">
                    👁️
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">การนิเทศ</p>
                    <p id="statSupervisions" class="text-3xl font-black text-slate-800">-</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-amber-500/30">
                    🏆
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">เกียรติบัตร</p>
                    <p id="statCertificates" class="text-3xl font-black text-slate-800">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div>
        <h2 class="text-xl font-black text-slate-800 dark:text-white mb-6 flex items-center gap-3">
            <span class="w-1.5 h-7 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></span>
            เมนูการจัดการ
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <a href="report.php" class="nav-card group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        📑
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800">ตรวจสอบรายงานการสอน</h3>
                        <p class="text-xs text-slate-400 font-medium">ดูรายงานของครูทุกกลุ่มสาระ</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg font-bold">ดูรายงาน</span>
                    <i class="fas fa-arrow-right text-slate-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-all"></i>
                </div>
            </a>

            <a href="supervision.php" class="nav-card group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        👁️
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800">การนิเทศการสอน</h3>
                        <p class="text-xs text-slate-400 font-medium">ประเมินและติดตามการสอน</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-lg font-bold">นิเทศ</span>
                    <i class="fas fa-arrow-right text-slate-300 group-hover:text-purple-500 group-hover:translate-x-1 transition-all"></i>
                </div>
            </a>

            <a href="stat.php" class="nav-card group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        📊
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800">สถิติและวิเคราะห์</h3>
                        <p class="text-xs text-slate-400 font-medium">วิเคราะห์ข้อมูลการสอน</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg font-bold">สถิติ</span>
                    <i class="fas fa-arrow-right text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all"></i>
                </div>
            </a>

            <a href="weekly_report.php" class="nav-card group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        📅
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800">รายงานรายสัปดาห์</h3>
                        <p class="text-xs text-slate-400 font-medium">สรุปข้อมูลรายสัปดาห์</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg font-bold">รายสัปดาห์</span>
                    <i class="fas fa-arrow-right text-slate-300 group-hover:text-amber-500 group-hover:translate-x-1 transition-all"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Info Section -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row items-center gap-8">
            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center text-4xl">
                💡
            </div>
            <div class="flex-1 text-center lg:text-left">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-2">ข้อมูลสำหรับผู้บริหาร</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                    คุณสามารถตรวจสอบรายงานการสอน ดูสถิติ และวิเคราะห์ข้อมูลการสอนของครูในกลุ่มสาระต่างๆ 
                    รวมถึงการนิเทศการสอนและติดตามผลการปฏิบัติงานของครูในโรงเรียนได้จากเมนูด้านบน
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Load quick stats from API
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('<?php echo $baseUrl ?? "../"; ?>controllers/StatController.php?action=directorOverview');
        const data = await response.json();
        
        const formatNumber = (num) => new Intl.NumberFormat('th-TH').format(num);
        
        document.getElementById('statReports').textContent = formatNumber(data.totalReports || 0);
        document.getElementById('statTeachers').textContent = formatNumber(data.totalTeachers || 0);
        document.getElementById('statSupervisions').textContent = formatNumber(data.totalSupervisions || 0);
        document.getElementById('statCertificates').textContent = formatNumber(data.totalCertificates || 0);
    } catch (error) {
        console.error('Error loading stats:', error);
        document.getElementById('statReports').textContent = '0';
        document.getElementById('statTeachers').textContent = '0';
        document.getElementById('statSupervisions').textContent = '0';
        document.getElementById('statCertificates').textContent = '0';
    }
});
</script>
