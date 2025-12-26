<?php
/**
 * Department Dashboard View
 * MVC Pattern - View for department head index page
 */
?>

<!-- Welcome Section -->
<div class="mb-8 slide-up">
    <div class="glass rounded-3xl p-6 md:p-8 shadow-xl border border-white/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-bold mb-3">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    ยินดีต้อนรับเข้าสู่ระบบ
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white leading-tight">
                    🏫 หัวหน้ากลุ่มสาระ 
                    <span class="bg-gradient-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent italic">
                        <?php echo htmlspecialchars($department); ?>
                    </span>
                </h1>
                <p class="mt-4 text-slate-600 dark:text-slate-400 text-lg font-medium">
                    สวัสดีครับคุณครู <span class="text-blue-600 dark:text-blue-400 font-bold"><?php echo htmlspecialchars($teacher_name); ?></span> <br class="hidden sm:block" />
                    วันนี้คุณพร้อมสำหรับการดูแลและติดตามข้อมูลกลุ่มสาระแล้วหรือยังครับ?
                </p>
            </div>
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative bg-white dark:bg-slate-800 p-2 rounded-full shadow-2xl">
                    <img src="../dist/img/<?php echo $global['logoLink'] ?? 'logo-phicha.png'; ?>" alt="School Logo" class="w-24 h-24 md:w-32 md:h-32 rounded-full object-contain">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Selection -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
    <!-- Check Reports -->
    <a href="report.php" class="group relative">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-[2rem] blur opacity-20 group-hover:opacity-40 transition"></div>
        <div class="relative glass h-full rounded-[2rem] p-8 flex flex-col items-center text-center hover:-translate-y-2 transition-all duration-300">
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/50 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                📑
            </div>
            <h3 class="text-xl font-black text-slate-800 dark:text-white mb-3 tracking-tight">ตรวจสอบรายงานการสอน</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                ติดตามการส่งรายงานรายวันของครูในกลุ่มสาระ ตรวจสอบความถูกต้องและรายละเอียดการสอน
            </p>
            <div class="mt-6 flex items-center text-blue-600 font-bold text-sm">
                เข้าใช้งาน <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </div>
    </a>

    <!-- Supervision -->
    <a href="supervision.php" class="group relative">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-violet-500 rounded-[2rem] blur opacity-20 group-hover:opacity-40 transition"></div>
        <div class="relative glass h-full rounded-[2rem] p-8 flex flex-col items-center text-center hover:-translate-y-2 transition-all duration-300">
            <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/50 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                👁️
            </div>
            <h3 class="text-xl font-black text-slate-800 dark:text-white mb-3 tracking-tight">การนิเทศการสอน</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                บันทึกและจัดการข้อมูลการนิเทศการสอน ให้คำแนะนำเพื่อพัฒนาการจัดการเรียนรู้ของครูในสังกัด
            </p>
            <div class="mt-6 flex items-center text-purple-600 font-bold text-sm">
                เข้าใช้งาน <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </div>
    </a>

    <!-- Statistics -->
    <a href="stat.php" class="group relative">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-500 to-rose-500 rounded-[2rem] blur opacity-20 group-hover:opacity-40 transition"></div>
        <div class="relative glass h-full rounded-[2rem] p-8 flex flex-col items-center text-center hover:-translate-y-2 transition-all duration-300">
            <div class="w-16 h-16 bg-pink-100 dark:bg-pink-900/50 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                📊
            </div>
            <h3 class="text-xl font-black text-slate-800 dark:text-white mb-3 tracking-tight">สถิติและวิเคราะห์</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                วิเคราะห์ข้อมูลสรุปผลการปฏิบัติงาน รายงานรายสัปดาห์ และแนวโน้มต่างๆ ในกลุ่มสาระ
            </p>
            <div class="mt-6 flex items-center text-pink-600 font-bold text-sm">
                เข้าใช้งาน <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </div>
    </a>
</div>

<!-- Extra Quick Actions -->
<div class="mb-12">
    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1 glass rounded-3xl p-8 border border-white/20">
            <h3 class="text-xl font-black mb-6 flex items-center gap-2 text-slate-800 dark:text-white">
                <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                ทางลัดระบบงาน
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="certificate.php" class="p-4 rounded-2xl bg-white/50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 hover:border-blue-400 transition-all group">
                    <div class="text-2xl mb-2">🏆</div>
                    <div class="text-xs font-black text-slate-800 dark:text-white truncate">เกียรติบัตรกลุ่มสาระ</div>
                </a>
                <a href="weekly_report.php" class="p-4 rounded-2xl bg-white/50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 hover:border-blue-400 transition-all group">
                    <div class="text-2xl mb-2">📅</div>
                    <div class="text-xs font-black text-slate-800 dark:text-white truncate">รายงานรายสัปดาห์</div>
                </a>
            </div>
        </div>

        <div class="w-full md:w-80 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group">
            <h3 class="text-sm font-black uppercase tracking-[0.2em] opacity-60 mb-8">System Note</h3>
            <p class="text-lg font-black leading-tight italic">
                "การจัดการข้อมูลที่ดี คือจุดเริ่มต้นของการบริหารที่มีประสิทธิภาพ"
            </p>
            <div class="mt-8 pt-8 border-t border-white/10 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black opacity-50 uppercase">Update Status</p>
                    <p class="text-sm font-bold">System Online</p>
                </div>
                <div class="text-3xl opacity-20">🛡️</div>
            </div>
            <div class="absolute top-[-10%] right-[-10%] w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
        </div>
    </div>
</div>
