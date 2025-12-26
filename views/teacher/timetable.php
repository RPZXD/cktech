<?php
/**
 * Timetable View
 * MVC Pattern - Teacher timetable display
 * Enhanced UI with Glassmorphism and Mobile Responsiveness
 */
?>

<style>
    /* Aurora Effect Background */
    .timetable-wrapper {
        position: relative;
        isolation: isolate;
    }
    .timetable-wrapper::before {
        content: '';
        position: absolute;
        inset: -40px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(59, 130, 246, 0.1));
        filter: blur(60px);
        z-index: -1;
        border-radius: 999px;
        animation: timetableGlow 10s ease-in-out infinite;
    }
    @keyframes timetableGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }

    /* Timetable Cell Styles */
    .timetable-cell {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .timetable-cell:not(.empty):hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10;
    }

    /* Subject Card Styles */
    .subject-card {
        padding: 0.5rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(4px);
    }

    /* Mobile Day Strip */
    @media (max-width: 768px) {
        .mobile-day-selector::-webkit-scrollbar {
            display: none;
        }
        .mobile-day-selector {
            -ms-overflow-style: none;
            scrollbar-width: none;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
    
    /* Animation for Switching Days on Mobile */
    .mobile-day-content.active {
        animation: fadeInSlide 0.4s ease-out forwards;
    }
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="timetable-wrapper space-y-6">
    <!-- Page Header (already provided by teacher_app template partially, but we customize it) -->
    <div class="glass rounded-3xl p-6 shadow-xl border border-white/20 mb-6 slide-up">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold flex items-center gap-3 text-slate-900 dark:text-white">
                    <span class="text-3xl md:text-4xl animate-bounce-slow">🗓️</span>
                    <span class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent">
                        ตารางสอนประจำสัปดาห์
                    </span>
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-bold shadow-sm ring-1 ring-emerald-200 dark:ring-emerald-800">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($teacher_name) ?>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-xs font-bold shadow-sm ring-1 ring-blue-200 dark:ring-blue-800">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($teacher_major) ?>
                    </span>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold rounded-xl shadow-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all active:scale-95 group">
                    <i class="fas fa-print group-hover:text-emerald-500 transition-colors"></i> 
                    <span class="hidden sm:inline">พิมพ์</span>
                </button>
                <button id="btnExportPDF" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-bold rounded-xl shadow-lg shadow-rose-500/20 transition-all active:scale-95">
                    <i class="fas fa-file-pdf"></i>
                    <span class="hidden sm:inline">PDF</span>
                    <span class="sm:hidden text-xs">PDF</span>
                </button>
                <button id="btnExportCSV" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                    <i class="fas fa-file-csv"></i>
                    <span class="hidden sm:inline">CSV</span>
                    <span class="sm:hidden text-xs">CSV</span>
                </button>
                <button id="btnExportExcel" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/20 transition-all active:scale-95">
                    <i class="fas fa-file-excel"></i>
                    <span class="hidden sm:inline">Excel</span>
                    <span class="sm:hidden text-xs">Excel</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <?php if (empty($rows)): ?>
        <div class="glass rounded-3xl p-12 text-center border border-white/20 animate-fade-in shadow-xl">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">ไม่พบข้อมูลตารางสอน</h3>
            <p class="text-slate-600 dark:text-slate-400">คุณยังไม่ได้เพิ่มข้อมูลรายวิชาหรือตารางสอนในระบบ</p>
            <a href="subject.php" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-emerald-500 text-white rounded-2xl font-bold hover:bg-emerald-600 transition-all shadow-lg hover:shadow-emerald-500/30">
                <i class="fas fa-plus"></i> เพิ่มรายวิชาใหม่
            </a>
        </div>
    <?php else: ?>

    <!-- Desktop View (Horizontal Table) -->
    <div class="hidden md:block glass rounded-3xl shadow-2xl border border-white/20 overflow-hidden fade-in">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse" id="timetableMainTable">
                <thead>
                    <tr class="bg-gradient-to-r from-emerald-600/90 to-teal-600/90 text-white">
                        <th class="p-6 text-center font-bold border-b border-white/10 w-32 bg-emerald-700/50">
                            <i class="fas fa-calendar-alt mb-1 block opacity-80"></i> วัน
                        </th>
                        <?php
                        $periodTimes = [
                            1 => '08:30–09:25', 2 => '09:25–10:20', 3 => '10:20–11:15',
                            4 => '11:15–12:10', 5 => '12:10–13:05', 6 => '13:05–14:00',
                            7 => '14:00–14:55', 8 => '14:55–15:50'
                        ];
                        for ($p = 1; $p <= $maxPeriod; $p++): ?>
                        <th class="p-4 text-center border-b border-white/10 min-w-[150px]">
                            <div class="text-xs font-bold uppercase tracking-wider opacity-80">คาบ <?= $p ?></div>
                            <div class="text-[10px] font-medium mt-1 inline-flex items-center gap-1 bg-white/10 px-2 py-0.5 rounded-full">
                                <i class="far fa-clock"></i> <?= $periodTimes[$p] ?>
                            </div>
                        </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php 
                    $dayEmojis = ['จันทร์'=>'🌞','อังคาร'=>'🔥','พุธ'=>'🌳','พฤหัสบดี'=>'⚡','ศุกร์'=>'💧'];
                    $dayColors = [
                        'จันทร์' => 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-l-yellow-500',
                        'อังคาร' => 'bg-pink-500/10 text-pink-700 dark:text-pink-400 border-l-pink-500',
                        'พุธ' => 'bg-green-500/10 text-green-700 dark:text-green-400 border-l-green-500',
                        'พฤหัสบดี' => 'bg-orange-500/10 text-orange-700 dark:text-orange-400 border-l-orange-500',
                        'ศุกร์' => 'bg-sky-500/10 text-sky-700 dark:text-sky-400 border-l-sky-500'
                    ];
                    foreach ($days as $day): ?>
                    <tr class="hover:bg-white/40 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="p-6 text-center font-bold border-r border-slate-100 dark:border-slate-700 <?= $dayColors[$day] ?? '' ?> border-l-8 bg-slate-50/20 dark:bg-slate-800/20">
                            <div class="text-xs uppercase tracking-tight opacity-60 mb-1"><?= $day ?></div>
                            <div class="text-2xl"><?= $dayEmojis[$day] ?? '' ?></div>
                        </td>
                        <?php for ($p = 1; $p <= $maxPeriod; $p++): ?>
                        <td class="p-2 min-h-[120px]">
                            <?php 
                            $cellEntries = [];
                            foreach ($classRooms as $room) {
                                if (isset($timetable[$day][$p][$room])) {
                                    $cellEntries[] = $timetable[$day][$p][$room];
                                }
                            }
                            
                            if (empty($cellEntries)): ?>
                                <div class="flex items-center justify-center h-full text-slate-200 dark:text-slate-700 py-6">
                                    <i class="fas fa-ellipsis-h"></i>
                                </div>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php foreach ($cellEntries as $entry): ?>
                                    <div class="timetable-cell subject-card <?= $entry['colorClass'] ?> shadow-sm ring-1 ring-black/5 hover:scale-[1.03] active:scale-95 cursor-default relative overflow-hidden group">
                                        <div class="font-black text-[11px] leading-tight mb-0.5"><?= htmlspecialchars($entry['code']) ?></div>
                                        <div class="text-[11px] font-bold text-slate-900/80 dark:text-white/80 line-clamp-2 min-h-[2em]" title="<?= htmlspecialchars($entry['subject_name']) ?>">
                                            <?= htmlspecialchars($entry['subject_name']) ?>
                                        </div>
                                        <?php if ($entry['showRoom']): ?>
                                        <div class="text-[9px] mt-2 font-black opacity-70 flex items-center gap-1">
                                            <i class="fas fa-door-open text-[8px]"></i> 
                                            <?= htmlspecialchars($entry['display']['level_room']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <!-- Subtle decoration -->
                                        <i class="fas fa-graduation-cap absolute -right-1 -bottom-1 opacity-5 text-xl group-hover:opacity-10 transition-opacity"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile View (Daily Vertical Flip Cards) -->
    <div class="md:hidden space-y-4">
        <!-- Modern Day Tabs -->
        <div class="mobile-day-selector p-2 bg-white/40 dark:bg-slate-800/40 rounded-3xl border border-white/20 shadow-lg mb-6 sticky top-[72px] z-20 backdrop-blur-xl">
            <div class="flex gap-2">
                <?php foreach ($days as $index => $day): ?>
                <button onclick="switchMobileDay('<?= $day ?>')" id="tab-<?= $day ?>"
                    class="flex-shrink-0 flex flex-col items-center justify-center w-16 h-16 rounded-2xl font-black text-xs transition-all shadow-md active:scale-90 border border-white/30
                    <?= $index === 0 ? 'bg-emerald-500 text-white ring-4 ring-emerald-500/20' : 'bg-white dark:bg-slate-700 text-slate-500 dark:text-slate-400' ?>">
                    <span class="text-lg opacity-80"><?= substr($dayEmojis[$day], 0, 4) ?></span>
                    <span class="mt-1"><?= mb_substr($day, 0, 1) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cards Container -->
        <div id="mobileCardsContainer">
            <?php foreach ($days as $dayIndex => $day): ?>
            <div data-day="<?= $day ?>" class="mobile-day-content <?= $dayIndex === 0 ? 'active' : 'hidden' ?> space-y-4">
                <div class="flex items-center justify-between px-3">
                    <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                        <span class="p-2 bg-white dark:bg-slate-800 rounded-xl shadow-md border border-white/20"><?= $dayEmojis[$day] ?></span>
                        วัน<?= $day ?>
                    </h2>
                    <?php 
                    $dayTotalCount = 0;
                    for($p=1; $p<=$maxPeriod; $p++) {
                        foreach($classRooms as $r) if(isset($timetable[$day][$p][$r])) $dayTotalCount++;
                    }
                    ?>
                    <div class="px-4 py-1.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full text-xs font-black">
                         <?= $dayTotalCount ?> คาบสอน
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <?php 
                    $hasAnyEntry = false;
                    for ($p = 1; $p <= $maxPeriod; $p++): 
                        $pEntries = [];
                        foreach ($classRooms as $room) if (isset($timetable[$day][$p][$room])) $pEntries[] = $timetable[$day][$p][$room];
                        if (!empty($pEntries)):
                            $hasAnyEntry = true;
                            foreach ($pEntries as $entry):
                    ?>
                    <div class="glass p-5 rounded-3xl shadow-xl border-l-8 <?= str_replace('border-', 'border-l-', $entry['colorClass']) ?> relative overflow-hidden active:scale-[0.97] transition-all">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 bg-black/5 dark:bg-white/5 rounded-lg text-[10px] font-black text-slate-500 uppercase flex items-center gap-1">
                                        <i class="far fa-clock"></i> <?= $periodTimes[$p] ?>
                                    </span>
                                    <span class="text-[10px] font-black text-emerald-500 uppercase">คาบ <?= $p ?></span>
                                </div>
                                <h3 class="font-black text-lg text-slate-900 dark:text-white leading-tight mb-1">
                                    <?= htmlspecialchars($entry['code']) ?>
                                </h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400 font-bold leading-snug">
                                    <?= htmlspecialchars($entry['subject_name']) ?>
                                </p>
                                
                                <div class="flex items-center gap-4 mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/50">
                                    <?php if ($entry['showRoom']): ?>
                                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-500">
                                        <i class="fas fa-door-open scale-90 opacity-60"></i>
                                        <span><?= htmlspecialchars($entry['display']['level_room']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-500">
                                        <i class="fas fa-layer-group scale-90 opacity-60"></i>
                                        <span><?= htmlspecialchars($entry['type']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute right-[-10px] bottom-[-10px] opacity-[0.03] transform rotate-[15deg] pointer-events-none">
                            <i class="fas fa-graduation-cap text-8xl"></i>
                        </div>
                    </div>
                    <?php endforeach; endif; endfor; ?>
                    
                    <?php if (!$hasAnyEntry): ?>
                    <div class="flex flex-col items-center justify-center py-20 text-center glass rounded-[40px] border-dashed border-2 border-slate-100 dark:border-slate-800 opacity-80">
                        <div class="text-7xl mb-6 grayscale animate-bounce-slow">🍦</div>
                        <h3 class="text-xl font-black text-slate-400 dark:text-slate-500 mb-1 italic">ไม่มีคาบสอนวันนี้</h3>
                        <p class="text-xs text-slate-300 dark:text-slate-600 uppercase font-black tracking-widest leading-loose">Relax & Re-charge Yourself!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottom Legend & Helpful Tips -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in delay-200">
        <!-- Legend -->
        <div class="glass rounded-3xl p-6 shadow-xl border border-white/20 lg:col-span-2">
            <h3 class="text-base font-black mb-5 flex items-center gap-2 text-slate-800 dark:text-white">
                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                สีแสดงกลุ่มสาระการเรียนรู้
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <?php foreach ($subjectTypeColors as $type => $colorClass): ?>
                <div class="flex flex-col gap-2 bg-white/40 dark:bg-slate-800/40 p-3 rounded-2xl border border-white/30 dark:border-slate-700 shadow-sm hover:shadow-md transition-all">
                    <div class="w-full h-1.5 rounded-full <?= $colorClass ?>"></div>
                    <span class="text-[11px] font-black text-slate-700 dark:text-slate-300"><?= $type ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Motivational Quote Card -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 shadow-xl text-white relative overflow-hidden group">
            <h3 class="text-sm font-black uppercase tracking-widest opacity-60 mb-8 flex items-center gap-2">
                <i class="fas fa-quote-left"></i> Wisdom
            </h3>
            <p class="text-lg font-black leading-tight mb-4 relative z-10">
                "หัวใจสำคัญของการเป็นครูที่ดี คือการไม่หยุดเรียนรู้และบริหารเวลาให้เกิดประโยชน์สูงสุด"
            </p>
            <div class="text-xs opacity-60 flex items-center gap-1 font-bold">
                <i class="fas fa-pencil-alt text-[10px]"></i> Teacher Assistant
            </div>
            <i class="fas fa-leaf absolute right-[-20px] bottom-[-20px] text-9xl opacity-10 group-hover:scale-110 transition-transform duration-700"></i>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Scripts for functionality -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
/**
 * Switch Mobile View Day
 * Logic to handles UI feedback and content display on mobile devices
 */
function switchMobileDay(day) {
    // 1. Update Buttons Styling
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('bg-emerald-500', 'text-white', 'ring-4', 'ring-emerald-500/20');
        btn.classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-500', 'dark:text-slate-400');
    });
    
    const activeBtn = document.getElementById('tab-' + day);
    activeBtn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-500', 'dark:text-slate-400');
    activeBtn.classList.add('bg-emerald-500', 'text-white', 'ring-4', 'ring-emerald-500/20');
    
    // 2. Switch Content with Fade Animation
    const allContents = document.querySelectorAll('.mobile-day-content');
    allContents.forEach(content => {
        content.classList.remove('active');
        content.classList.add('hidden');
    });
    
    const activeContent = document.querySelector(`[data-day="${day}"]`);
    activeContent.classList.remove('hidden');
    // Force a reflow to restart animation
    void activeContent.offsetWidth; 
    activeContent.classList.add('active');
    
    // 3. Smooth scroll target tab into center for better UX
    activeBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    
    // 4. Haptic feedback if supported (subtle vibrat on mobile)
    if (window.navigator && window.navigator.vibrate) {
        window.navigator.vibrate(10);
    }
}

/**
 * Modern Export & Tabular Logic
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Excel Export (Improved formatting)
    document.getElementById('btnExportExcel')?.addEventListener('click', () => {
        const table = document.getElementById('timetableMainTable');
        const wb = XLSX.utils.table_to_book(table, { 
            sheet: "Timetable_Data",
            raw: false,
            display: true
        });
        XLSX.writeFile(wb, `Timetable_Report_<?= date('Y-m-d') ?>.xlsx`);
    });

    // 2. PDF Export (High Quality Canvas)
    document.getElementById('btnExportPDF')?.addEventListener('click', () => {
        const element = document.querySelector('.timetable-wrapper');
        const opt = {
            margin:       [10, 10, 10, 10], // top, left, buttom, right
            filename:     'Weekly_Timetable_<?= urlencode($teacher_name) ?>.pdf',
            image:        { type: 'jpeg', quality: 1.0 },
            html2canvas:  { 
                scale: 3, 
                useCORS: true, 
                letterRendering: true,
                backgroundColor: '#ffffff'
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
            pagebreak:    { mode: 'avoid-all' }
        };
        
        Swal.fire({
            title: '🎨 กำลังเตรียมไฟล์ PDF...',
            text: 'รอกระบวนการสักครู่ครับ',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        html2pdf().set(opt).from(element).save().then(() => {
            Swal.close();
            Swal.fire({
                icon: 'success',
                title: 'ดาวน์โหลดสำเร็จ',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        });
    });

    // 3. CSV Export
    document.getElementById('btnExportCSV')?.addEventListener('click', () => {
        const rows = [];
        const table = document.getElementById('timetableMainTable');
        if(!table) return;
        
        const headers = ["วัน", "คาบ", "รหัสวิชา", "ชื่อวิชา", "ระดับ/ห้อง"];
        rows.push(headers);

        const tbodyRows = table.querySelectorAll('tbody tr');
        tbodyRows.forEach((tr) => {
            const day = tr.querySelector('td:first-child div:first-child')?.innerText || '';
            const cells = tr.querySelectorAll('td:not(:first-child)');
            cells.forEach((cell, pIndex) => {
                const chips = cell.querySelectorAll('.subject-card');
                chips.forEach(chip => {
                    const code = chip.querySelector('.font-black')?.innerText || '';
                    const name = chip.querySelector('.text-\\[11px\\].font-bold')?.innerText || '';
                    const room = chip.querySelector('.text-\\[9px\\]')?.innerText || '';
                    rows.push([day, pIndex + 1, code, name, room]);
                });
            });
        });

        const csvContent = "\uFEFF" + rows.map(e => e.map(cell => `"${cell.replace(/"/g, '""')}"`).join(",")).join("\n");
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", `Timetable_<?= date('Y-m-d') ?>.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // 4. Copy to Clipboard on Click
    document.querySelectorAll('.timetable-cell, .mobile-day-content .glass').forEach(card => {
        card.addEventListener('click', () => {
            const code = card.querySelector('h3, .font-black, .font-extrabold')?.innerText || '';
            const name = card.querySelector('p, .text-\\[11px\\].font-bold, .text-\\[11px\\].font-medium')?.innerText || '';
            const textToCopy = `${code} ${name}`.trim();
            
            if (textToCopy) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `คัดลอก: ${textToCopy}`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                });
            }
        });
    });
});
</script>
