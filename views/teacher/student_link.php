<?php
/**
 * Student Analysis View
 * MVC Pattern - View for student analysis links and reports
 */
?>

<!-- Custom Styles -->
<style>
    .tab-active {
        border-bottom: 3px solid #3b82f6;
        color: #1d4ed8;
        background-color: rgba(59, 130, 246, 0.05);
    }
    .glass-input {
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .dark .glass-input {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Print Styles */
    @media print {
        body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Hide UI Elements */
        nav,
        footer,
        .sidebar,
        aside,
        .sidebar-item,
        .teacher-sidebar,
        #sidebar,
        #sidebar-overlay,
        .content-header,
        #studentTabs,
        .aurora-wrapper > .glass,
        #tab-link,
        .p-0.mb-6,
        .flex-col.lg\:flex-row,
        .bg-slate-50,
        .flex.flex-col,
        .ml-64,
        #reportSubject,
        #printReportBtn,
        #excelReportBtn,
        #allSubject,
        #searchStudent,
        #printAllBtn,
        #excelAllBtn,
        .lg\:pt-5,
        .pt-5,
        .btn-delete {
            display: none !important;
        }

        /* Show active tab content */
        .tab-content:not(.hidden) {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Adjust Layout for Print */
        .glass {
            background: none !important;
            backdrop-filter: none !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .lg\:ml-64 {
            margin-left: 0 !important;
        }

        .p-4,
        .md\:p-6,
        .lg\:p-8 {
            padding: 0 !important;
        }

        /* Chart container adjustment for print */
        canvas {
            max-width: 100% !important;
            height: auto !important;
        }

        .grid {
            display: block !important;
        }

        .grid > div {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        /* Report Header (Hidden in Screen, Show in Print) */
        .print-only-header {
            display: block !important;
        }
    }

    .print-only-header {
        display: none;
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 10px;
        border-bottom: 2px solid #000;
        color: #000;
    }
</style>

<!-- Print Only Header -->
<div class="print-only-header">
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">แบบสรุปวิเคราะห์ผู้เรียนรายบุคคล</h1>
    <p style="font-size: 16px;"><?= htmlspecialchars($global['nameschool']) ?></p>
    <p style="font-size: 14px;">ครูผู้สอน: <?= htmlspecialchars($teacherName) ?> | กลุ่มสาระฯ: <?= htmlspecialchars($teacherMajor ?: '-') ?></p>
</div>

<div class="content-header p-0 mb-6">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-3xl font-bold bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 bg-clip-text text-transparent flex items-center gap-3">
                    🔗 <span class="drop-shadow-lg">วิเคราะห์ผู้เรียนรายบุคคล</span>
                </h1>
            </div>
        </div>
    </div>
</div>

<div class="aurora-wrapper relative z-10">
    <div class="glass rounded-3xl p-6 md:p-8 shadow-xl mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
            <div>
                <p class="uppercase tracking-[0.2em] text-[10px] font-black text-blue-500 mb-1">Student Insight Portal</p>
                <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    📱 ลิงก์เก็บข้อมูลและรายงานผล
                </h2>
                <div class="mt-2 flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-bold shadow-sm">
                        👨‍🏫 <?= htmlspecialchars($teacherName) ?>
                    </span>
                    <span class="text-gray-400 text-sm">|</span>
                    <span class="text-gray-500 dark:text-gray-400 text-xs font-medium">กลุ่มสาระฯ: <?= htmlspecialchars($teacherMajor ?: 'ไม่ระบุ') ?></span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mb-8 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex space-x-2" aria-label="Tabs" id="studentTabs">
                <button class="tab-btn px-6 py-3 font-bold text-sm rounded-t-2xl transition-all duration-200 tab-active" data-tab="tab-link">
                    <i class="fas fa-link mr-2"></i> ลิงก์สำหรับนักเรียน
                </button>
                <button class="tab-btn px-6 py-3 font-bold text-sm rounded-t-2xl transition-all duration-200 text-gray-500 hover:text-blue-600" data-tab="tab-report">
                    <i class="fas fa-chart-pie mr-2"></i> รายงานผลสรุป
                </button>
                <button class="tab-btn px-6 py-3 font-bold text-sm rounded-t-2xl transition-all duration-200 text-gray-500 hover:text-blue-600" data-tab="tab-all">
                    <i class="fas fa-list-ul mr-2"></i> ข้อมูลนักเรียนทั้งหมด
                </button>
            </nav>
        </div>

        <!-- Tab Content: Links -->
        <div id="tab-link" class="tab-content transition-all duration-300">
            <?php if (empty($subjects)): ?>
                <div class="py-20 text-center space-y-4">
                    <div class="text-6xl opacity-20">📚</div>
                    <p class="text-gray-400 font-bold">ไม่พบรายวิชาของคุณในระบบ</p>
                </div>
            <?php else: ?>
                <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="py-4 px-4 text-center font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px]">รหัสวิชา</th>
                                <th class="py-4 px-4 text-left font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px]">ชื่อวิชา</th>
                                <th class="py-4 px-4 text-center font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px]">ระดับชั้น</th>
                                <th class="py-4 px-4 text-left font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px]">ลิงก์สำหรับนักเรียน</th>
                                <th class="py-4 px-4 text-center font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px]">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900/50">
                            <?php foreach ($subjects as $sub): 
                                $link = $baseUrl . '?subject_id=' . $sub['id'];
                            ?>
                                <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                    <td class="py-4 px-4 text-center font-bold text-blue-600 dark:text-blue-400"><?= htmlspecialchars($sub['code']) ?></td>
                                    <td class="py-4 px-4 font-semibold text-slate-700 dark:text-gray-200"><?= htmlspecialchars($sub['name']) ?></td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold">
                                            ม.<?= intval($sub['level']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <code class="text-[10px] bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-slate-500 overflow-hidden max-w-[200px] truncate">
                                                <?= $link ?>
                                            </code>
                                            <a href="<?= $link ?>" target="_blank" class="text-blue-500 hover:text-blue-700" title="ไปที่ลิงก์">
                                                <i class="fas fa-external-link-alt text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <button class="copy-link-btn inline-flex items-center px-4 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md transition-all hover:scale-105" data-link="<?= $link ?>">
                                            <i class="fas fa-copy mr-1.5"></i> คัดลอกลิงก์
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex items-start gap-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-100 dark:border-amber-800">
                    <div class="text-amber-500 text-xl">💡</div>
                    <div class="text-sm text-amber-800 dark:text-amber-300">
                        <span class="font-bold">วิธีการใช้งาน:</span> ส่งลิงก์แต่ละวิชาให้นักเรียนในกลุ่มไลน์หรือช่องทางอื่นๆ เพื่อให้นักเรียนเข้ามากรอกข้อมูลแบบวิเคราะห์ผู้เรียนรายบุคคล เมื่อนักเรียนกรอกแล้วข้อมูลจะมาแสดงผลในแถบ "รายงานผลสรุป" และ "ข้อมูลนักเรียนทั้งหมด"
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Report -->
        <div id="tab-report" class="tab-content hidden transition-all duration-300">
            <div class="flex flex-col md:flex-row md:items-center gap-4 mb-8 bg-slate-50 dark:bg-slate-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex-1 space-y-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase">เลือกวิชาเพื่อดูรายงาน</label>
                    <select id="reportSubject" class="w-full glass-input rounded-xl px-4 py-2.5 font-bold text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-400 outline-none">
                        <option value="">-- เลือกวิชา --</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?> (<?= htmlspecialchars($sub['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-5">
                    <button id="printReportBtn" class="flex-1 md:flex-none inline-flex items-center px-5 py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl font-bold shadow-lg transition-all hover:-translate-y-1">
                        <i class="fas fa-print mr-2"></i> พิมพ์รายงาน
                    </button>
                    <button id="excelReportBtn" class="flex-1 md:flex-none inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg transition-all hover:-translate-y-1">
                        <i class="fas fa-file-excel mr-2"></i> ส่งออก Excel
                    </button>
                </div>
            </div>
            
            <div id="reportContent" class="min-h-[400px]">
                <div class="flex flex-col items-center justify-center py-20 text-gray-400 space-y-4">
                    <i class="fas fa-chart-bar text-6xl opacity-10"></i>
                    <p class="font-bold">กรุณาเลือกวิชาเพื่อวิเคราะห์ข้อมูล</p>
                </div>
            </div>
        </div>

        <!-- Tab Content: All Data -->
        <div id="tab-all" class="tab-content hidden transition-all duration-300">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 mb-8 bg-slate-50 dark:bg-slate-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex-1 space-y-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase">กรองตามรายวิชา</label>
                    <select id="allSubject" class="w-full glass-input rounded-xl px-4 py-2.5 font-bold text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-400 outline-none">
                        <option value="">-- เลือกวิชา --</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?> (<?= htmlspecialchars($sub['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1 space-y-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase">ค้นหารายชื่อ</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchStudent" placeholder="ค้นหาชื่อ, เลขที่ หรือห้องเรียน..." class="w-full glass-input rounded-xl pl-11 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 outline-none" />
                    </div>
                </div>
                <div class="flex items-center gap-3 lg:pt-5">
                    <button id="printAllBtn" class="flex-1 lg:flex-none inline-flex items-center px-5 py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl font-bold shadow-lg transition-all hover:-translate-y-1">
                        <i class="fas fa-print mr-2"></i> พิมพ์ทั้งหมด
                    </button>
                    <button id="excelAllBtn" class="flex-1 lg:flex-none inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg transition-all hover:-translate-y-1">
                        <i class="fas fa-file-excel mr-2"></i> ส่งออก Excel
                    </button>
                </div>
            </div>

            <div id="allContent" class="min-h-[400px]">
                <div class="flex flex-col items-center justify-center py-20 text-gray-400 space-y-4">
                    <i class="fas fa-table text-6xl opacity-10"></i>
                    <p class="font-bold">กรุณาเลือกวิชาเพื่อดูตารางข้อมูล</p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- JS Logic -->
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- XLSX Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const teacherMajor = <?= json_encode($teacherMajor) ?>;
    const teacherName = <?= json_encode($teacherName) ?>;
    const subjects = <?= json_encode($subjects) ?>;
    
    // Tab Controller
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('tab-active');
                b.classList.add('text-gray-500');
            });
            btn.classList.add('tab-active');
            btn.classList.remove('text-gray-500');
            
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById(btn.dataset.tab).classList.remove('hidden');
        });
    });

    // Copy to Clipboard
    document.querySelectorAll('.copy-link-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const link = btn.getAttribute('data-link');
            navigator.clipboard.writeText(link).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check mr-1.5"></i> คัดลอกแล้ว!';
                btn.classList.replace('bg-blue-500', 'bg-emerald-500');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.replace('bg-emerald-500', 'bg-blue-500');
                }, 2000);
            });
        });
    });

    // --- Report / Charts Logic ---
    $('#reportSubject').on('change', function() {
        const subjectId = $(this).val();
        const area = $('#reportContent');
        if (!subjectId) {
            area.html('<div class="flex flex-col items-center justify-center py-20 text-gray-400 space-y-4"><i class="fas fa-chart-bar text-6xl opacity-10"></i><p class="font-bold">กรุณาเลือกวิชาเพื่อวิเคราะห์ข้อมูล</p></div>');
            return;
        }
        
        area.html('<div class="flex flex-col items-center justify-center py-20"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div><p class="text-gray-500 font-bold">กำลังประมวลผลข้อมูลทางสถิติ...</p></div>');
        
        $.getJSON('../controllers/StudentAnalyzeController.php?subject_id=' + subjectId, function(res) {
            if (!res.success || !res.data.length) {
                area.html('<div class="flex flex-col items-center justify-center py-20 text-rose-500/50 space-y-4"><i class="fas fa-exclamation-circle text-6xl opacity-20"></i><p class="font-bold">ไม่พบข้อมูลนักเรียนในรายวิชานี้</p></div>');
                return;
            }

            // Stats Processing
            let male = 0, female = 0, other = 0;
            let likeSubjects = {}, gpaArr = [], gradeArr = [];
            let weightArr = [], heightArr = [];
            let roomSet = new Set();
            let diseaseMap = {}, activityMap = {}, skillMap = {}, liveWithMap = {};

            res.data.forEach(s => {
                // Gender
                if (s.prefix.includes('ด.ช.') || s.prefix.includes('นาย')) male++;
                else if (s.prefix.includes('ด.ญ.') || s.prefix.includes('น.ส.') || s.prefix.includes('นาง')) female++;
                else other++;

                // Academics
                if (s.gpa) gpaArr.push(parseFloat(s.gpa));
                if (s.last_com_grade) gradeArr.push(parseFloat(s.last_com_grade));
                roomSet.add(s.student_level_room);

                // Subjects
                (s.like_subjects || '').split(',').forEach(sub => {
                    sub = sub.trim();
                    if (sub) likeSubjects[sub] = (likeSubjects[sub] || 0) + 1;
                });

                // Body
                if (s.weight) weightArr.push(parseFloat(s.weight));
                if (s.height) heightArr.push(parseFloat(s.height));

                // Extras
                if (s.disease) diseaseMap[s.disease] = (diseaseMap[s.disease] || 0) + 1;
                (s.favorite_activity || '').split(',').forEach(a => { a = a.trim(); if(a) activityMap[a] = (activityMap[a] || 0) + 1; });
                (s.special_skill || '').split(',').forEach(sk => { sk = sk.trim(); if(sk) skillMap[sk] = (skillMap[sk] || 0) + 1; });
                if (s.live_with) liveWithMap[s.live_with] = (liveWithMap[s.live_with] || 0) + 1;
            });

            const avgGpa = gpaArr.length ? (gpaArr.reduce((a, b) => a + b, 0) / gpaArr.length).toFixed(2) : '-';
            const avgGrade = gradeArr.length ? (gradeArr.reduce((a, b) => a + b, 0) / gradeArr.length).toFixed(2) : '-';

            // Top lists
            const topLikes = Object.entries(likeSubjects).sort((a, b) => b[1] - a[1]).slice(0, 5);
            
            // Render View
            area.html(`
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="glass p-5 rounded-2xl border-l-4 border-blue-500 shadow-sm transition-transform hover:-translate-y-1">
                        <p class="text-[10px] font-black uppercase text-blue-500 tracking-wider">นักเรียนทั้งหมด</p>
                        <h4 class="text-3xl font-black text-slate-800 dark:text-white mt-1">${res.data.length} <span class="text-xs font-bold text-gray-400">คน</span></h4>
                    </div>
                    <div class="glass p-5 rounded-2xl border-l-4 border-emerald-500 shadow-sm transition-transform hover:-translate-y-1">
                        <p class="text-[10px] font-black uppercase text-emerald-500 tracking-wider">เกรดเฉลี่ย (GPA)</p>
                        <h4 class="text-3xl font-black text-slate-800 dark:text-white mt-1">${avgGpa}</h4>
                    </div>
                    <div class="glass p-5 rounded-2xl border-l-4 border-purple-500 shadow-sm transition-transform hover:-translate-y-1">
                        <p class="text-[10px] font-black uppercase text-purple-500 tracking-wider">เกรดเฉลี่ยวิชา</p>
                        <h4 class="text-3xl font-black text-slate-800 dark:text-white mt-1">${avgGrade}</h4>
                    </div>
                    <div class="glass p-5 rounded-2xl border-l-4 border-amber-500 shadow-sm transition-transform hover:-translate-y-1">
                        <p class="text-[10px] font-black uppercase text-amber-500 tracking-wider">ห้องเรียน</p>
                        <h4 class="text-3xl font-black text-slate-800 dark:text-white mt-1">${roomSet.size} <span class="text-xs font-bold text-gray-400">ห้อง</span></h4>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="glass p-6 rounded-3xl">
                        <h5 class="font-bold text-slate-700 dark:text-gray-200 mb-6 flex items-center gap-2">
                             📌 สัดส่วนเพศนักเรียน
                        </h5>
                        <div class="h-[250px] relative">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                    <div class="glass p-6 rounded-3xl">
                        <h5 class="font-bold text-slate-700 dark:text-gray-200 mb-6 flex items-center gap-2">
                             🏆 วิชาที่นักเรียนชอบมากที่สุด
                        </h5>
                        <div class="h-[250px] relative">
                            <canvas id="topSubjectsChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div class="glass p-6 rounded-2xl bg-indigo-50/30 dark:bg-indigo-900/10">
                        <h6 class="font-bold text-indigo-700 dark:text-indigo-400 mb-3 uppercase text-[10px] tracking-widest">🩺 สถิติสุขภาพ</h6>
                        <ul class="space-y-2">
                            <li class="flex justify-between"><span>น้ำหนักเฉลี่ย:</span> <span class="font-bold">${weightArr.length ? (weightArr.reduce((a,b)=>a+b,0)/weightArr.length).toFixed(1) : '-'} กก.</span></li>
                            <li class="flex justify-between"><span>ส่วนสูงเฉลี่ย:</span> <span class="font-bold">${heightArr.length ? (heightArr.reduce((a,b)=>a+b,0)/heightArr.length).toFixed(1) : '-'} ซม.</span></li>
                            <li class="mt-4 text-xs font-bold text-gray-400">โรคประจำตัวเด่น:</li>
                            ${Object.keys(diseaseMap).slice(0,3).map(d => `<li class="text-xs text-slate-600 dark:text-gray-400">• ${d} (${diseaseMap[d]} คน)</li>`).join('') || '<li class="text-xs text-gray-400 italic font-normal">• ไม่มีข้อมูล</li>'}
                        </ul>
                    </div>
                    <div class="glass p-6 rounded-2xl bg-fuchsia-50/30 dark:bg-fuchsia-900/10">
                        <h6 class="font-bold text-fuchsia-700 dark:text-fuchsia-400 mb-3 uppercase text-[10px] tracking-widest">🎨 กิจกรรม/ทักษะ</h6>
                        <ul class="space-y-2">
                            <li class="text-xs font-bold text-gray-400">กิจกรรมที่ชอบมากสุด:</li>
                            ${Object.entries(activityMap).sort((a,b)=>b[1]-a[1]).slice(0,2).map(([a,c]) => `<li class="text-xs text-slate-600 dark:text-gray-400">• ${a} (${c} คน)</li>`).join('')}
                            <li class="mt-4 text-xs font-bold text-gray-400">ความสามารถพิเศษ:</li>
                            ${Object.entries(skillMap).sort((a,b)=>b[1]-a[1]).slice(0,2).map(([s,c]) => `<li class="text-xs text-slate-600 dark:text-gray-400">• ${s} (${c} คน)</li>`).join('')}
                        </ul>
                    </div>
                    <div class="glass p-6 rounded-2xl bg-emerald-50/30 dark:bg-emerald-900/10">
                        <h6 class="font-bold text-emerald-700 dark:text-emerald-400 mb-3 uppercase text-[10px] tracking-widest">🏠 การอยู่อาศัย</h6>
                        <ul class="space-y-2">
                            ${Object.entries(liveWithMap).sort((a,b)=>b[1]-a[1]).slice(0,4).map(([key, val]) => `
                                <li class="flex justify-between"><span>${key}:</span> <span class="font-bold">${val} คน</span></li>
                            `).join('') || '<li class="text-center text-gray-400 italic">ไม่มีข้อมูล</li>'}
                        </ul>
                    </div>
                </div>
            `);

            // Charts Initialization
            new Chart(document.getElementById('genderChart'), {
                type: 'doughnut',
                data: {
                    labels: ['ชาย', 'หญิง', 'อื่นๆ'],
                    datasets: [{
                        data: [male, female, other],
                        backgroundColor: ['#3b82f6', '#ec4899', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 15
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { usePointStyle: true, font: { weight: 'bold' } } } } }
            });

            new Chart(document.getElementById('topSubjectsChart'), {
                type: 'bar',
                data: {
                    labels: topLikes.map(l => l[0]),
                    datasets: [{
                        label: 'จำนวนนักเรียน',
                        data: topLikes.map(l => l[1]),
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        borderRadius: 10
                    }]
                },
                options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } }, plugins: { legend: { display: false } } }
            });
        });
    });

    // --- All Data Table Logic ---
    const allSubject = $('#allSubject');
    allSubject.on('change', loadAllTable);
    $('#searchStudent').on('input', loadAllTable);

    function loadAllTable() {
        const subjectId = allSubject.val();
        const search = $('#searchStudent').val().toLowerCase();
        const area = $('#allContent');
        
        if (!subjectId) {
            area.html('<div class="flex flex-col items-center justify-center py-20 text-gray-400 space-y-4"><i class="fas fa-table text-6xl opacity-10"></i><p class="font-bold">กรุณาเลือกวิชาเพื่อดูตารางข้อมูล</p></div>');
            return;
        }

        area.html('<div class="flex flex-col items-center justify-center py-20"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-slate-500 mb-4"></div><p class="text-gray-500">กำลังโหลดข้อมูลนักเรียน...</p></div>');

        $.getJSON('../controllers/StudentAnalyzeController.php?subject_id=' + subjectId, function(res) {
            if (!res.success || !res.data.length) {
                area.html('<div class="py-20 text-center text-rose-400 font-bold">ไม่พบข้อมูล</div>');
                return;
            }

            let html = `
                <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-inner">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                        <thead class="bg-gray-50 dark:bg-slate-800">
                            <tr>
                                <th class="p-3 text-center font-bold">เลขที่</th>
                                <th class="p-3 text-left font-bold">นักเรียน</th>
                                <th class="p-3 text-center font-bold">ห้อง</th>
                                <th class="p-3 text-center font-bold">เพศ</th>
                                <th class="p-3 text-center font-bold">ข้อมูลกายภาพ</th>
                                <th class="p-3 text-left font-bold">โรคประจำตัว</th>
                                <th class="p-3 text-center font-bold">GPA</th>
                                <th class="p-3 text-center font-bold">เกรดวิชา</th>
                                <th class="p-3 text-center font-bold">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white/50 dark:bg-slate-900/30">
            `;

            res.data.forEach(s => {
                const searchStr = `${s.prefix}${s.student_firstname} ${s.student_lastname} ${s.student_no} ${s.student_level_room}`.toLowerCase();
                if (search && !searchStr.includes(search)) return;

                html += `
                    <tr class="hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="p-3 text-center font-bold text-gray-400">${s.student_no}</td>
                        <td class="p-3">
                            <div class="font-bold text-slate-800 dark:text-gray-200">${s.prefix}${s.student_firstname} ${s.student_lastname}</div>
                            <div class="text-[9px] text-gray-500">${s.student_phone || '-'}</div>
                        </td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">ม.${s.student_level_room}</span></td>
                        <td class="p-3 text-center">${s.prefix.includes('ด.ช.') || s.prefix.includes('นาย') ? '♂️' : (s.prefix.includes('ด.ญ.') || s.prefix.includes('น.ส.') ? '♀️' : '❓')}</td>
                        <td class="p-3 text-center">
                            <div class="text-[9px]">${s.weight} กก. / ${s.height} ซม.</div>
                        </td>
                        <td class="p-3">${s.disease || '-'}</td>
                        <td class="p-3 text-center font-bold text-emerald-600">${s.gpa || '-'}</td>
                        <td class="p-3 text-center font-bold text-blue-600">${s.last_com_grade || '-'}</td>
                        <td class="p-3 text-center">
                            <button class="btn-delete text-rose-500 hover:text-rose-700 transition-colors p-1.5" data-id="${s.id}" data-name="${s.student_firstname}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += `</tbody></table></div>`;
            area.html(html);

            // Delete Event
            $('.btn-delete').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: `ต้องการข้อมูลของนักเรียน ${name} หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ลบเลย',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#e11d48'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../controllers/StudentAnalyzeController.php',
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({ action: 'delete', id: id }),
                            success: (re) => { 
                                if(re.success) { Swal.fire('สำเร็จ', '', 'success'); loadAllTable(); }
                                else Swal.fire('ผิดพลาด', re.error, 'error');
                            }
                        });
                    }
                });
            });
        });
    }

    // --- Export / Print Logic ---
    $('#excelReportBtn').on('click', function() {
        const sid = $('#reportSubject').val();
        if(!sid) return Swal.fire('แจ้งเตือน', 'กรุณาเลือกวิชา', 'info');
        // Logic for excel export remains similar but enhanced for modern browser standards
        exportToExcel(sid, 'summary');
    });

    $('#excelAllBtn').on('click', function() {
        const sid = $('#allSubject').val();
        if(!sid) return Swal.fire('แจ้งเตือน', 'กรุณาเลือกวิชา', 'info');
        exportToExcel(sid, 'all');
    });

    function exportToExcel(subjectId, type) {
        $.getJSON('../controllers/StudentAnalyzeController.php?subject_id=' + subjectId, (res) => {
            const data = res.data;
            let exportData = [];
            if (type === 'all') {
                exportData = data.map(s => ({
                    'เลขที่': s.student_no,
                    'ชื่อ-สกุล': `${s.prefix}${s.student_firstname} ${s.student_lastname}`,
                    'ห้อง': s.student_level_room,
                    'เบอร์โทร': s.student_phone,
                    'น้ำหนัก': s.weight,
                    'ส่วนสูง': s.height,
                    'โรคประจำตัว': s.disease,
                    'GPA': s.gpa,
                    'เกรดวิชา': s.last_com_grade,
                    'วิชาที่ชอบ': s.like_subjects,
                    'กิจกรรมที่ชอบ': s.favorite_activity,
                    'อาศัยกับ': s.live_with
                }));
            } else {
                // Summary/Stats Processing
                let male = 0, female = 0, other = 0;
                let roomSet = new Set();
                let gpaArr = [], gradeArr = [], weightArr = [], heightArr = [];
                
                data.forEach(s => {
                    if (s.prefix.includes('ด.ช.') || s.prefix.includes('นาย')) male++;
                    else if (s.prefix.includes('ด.ญ.') || s.prefix.includes('น.ส.')) female++;
                    else other++;
                    roomSet.add(s.student_level_room);
                    if (s.gpa) gpaArr.push(parseFloat(s.gpa));
                    if (s.last_com_grade) gradeArr.push(parseFloat(s.last_com_grade));
                    if (s.weight) weightArr.push(parseFloat(s.weight));
                    if (s.height) heightArr.push(parseFloat(s.height));
                });

                const avg = (arr) => arr.length ? (arr.reduce((a, b) => a + b, 0) / arr.length).toFixed(2) : '-';

                exportData = [
                    { '📊 หัวข้อ': 'สรุปสรุปผลวิเคราะห์ผู้เรียนรายบุคคล', 'ค่าสถิติ': '' },
                    { '📊 หัวข้อ': 'จำนวนนักเรียนทั้งหมด', 'ค่าสถิติ': data.length + ' คน' },
                    { '📊 หัวข้อ': 'เพศชาย', 'ค่าสถิติ': male + ' คน' },
                    { '📊 หัวข้อ': 'เพศหญิง', 'ค่าสถิติ': female + ' คน' },
                    { '📊 หัวข้อ': 'เพศอื่นๆ', 'ค่าสถิติ': other + ' คน' },
                    { '📊 หัวข้อ': 'จำนวนห้องเรียน', 'ค่าสถิติ': roomSet.size + ' ห้อง' },
                    {},
                    { '📊 หัวข้อ': 'เกรดเฉลี่ย (GPA) เฉลี่ย', 'ค่าสถิติ': avg(gpaArr) },
                    { '📊 หัวข้อ': 'เกรดวิชาคอมพิวเตอร์เฉลี่ย', 'ค่าสถิติ': avg(gradeArr) },
                    { '📊 หัวข้อ': 'น้ำหนักเฉลี่ย', 'ค่าสถิติ': avg(weightArr) + ' กก.' },
                    { '📊 หัวข้อ': 'ส่วนสูงเฉลี่ย', 'ค่าสถิติ': avg(heightArr) + ' ซม.' }
                ];
            }

            const ws = XLSX.utils.json_to_sheet(exportData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Students");
            XLSX.writeFile(wb, `Student_Analysis_${type}_${new Date().getTime()}.xlsx`);
        });
    }

    $('#printReportBtn').on('click', () => window.print());
    $('#printAllBtn').on('click', () => window.print());

});
</script>
