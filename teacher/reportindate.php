<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ครู') {
    header('Location: ../login.php');
    exit;
}
// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
require_once('header.php');
?>
<!-- Tailwind CDN + small theme additions -->
<style>
    /* small custom styles for calendar card and event hover */
    .calendar-card { background: linear-gradient(135deg, rgba(239,246,255,0.9), rgba(250,242,255,0.95)); }
    .fc-event { transition: transform .12s ease, box-shadow .12s ease; border-radius: .5rem; }
    .fc-event:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(15,23,42,0.12); }
    .event-dot { width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:8px; vertical-align:middle; }
    .legend-bullet { width:12px; height:12px; border-radius:9999px; display:inline-block; margin-right:8px; }
    .swal2-html-container img { max-height:160px; border-radius:8px; margin-right:8px; }
</style>

<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gradient-to-br from-slate-50 to-white">
<div class="wrapper">
    <?php require_once('wrapper.php'); ?>
    <div class="content-wrapper bg-gray-50">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center">
                            📅 รายงานการสอนแบบปฏิทิน
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid flex justify-center">
                <div class="w-full max-w-8xl">
                    <div class="calendar-card bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <h2 class="text-xl font-semibold text-sky-700">📅 ปฏิทินการสอน</h2>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <input id="search-subject" placeholder="🔎 ค้นหาวิชา, หัวข้อ..." class="px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-primary focus:border-primary outline-none" />
                                    <select id="filter-level" class="px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-accent outline-none">
                                        <option value="">ทั้งหมดระดับ</option>
                                        <option value="ม">ม.</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <span class="legend-bullet" style="background:#06b6d4"></span><span>ปกติ</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="legend-bullet" style="background:#7c3aed"></span><span>กิจกรรม</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="legend-bullet" style="background:#f97316"></span><span>สอบ/ชิ้นงาน</span>
                                </div>
                            </div>
                        </div>
                        <div id="calendar" class="rounded-lg bg-white"></div>

                        <!-- floating help button -->
                        <button id="helpBtn" title="คำอธิบาย" class="fixed right-6 bottom-6 z-50 bg-primary text-white p-3 rounded-full shadow-2xl hover:scale-105 transform transition">
                            ❓
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ฟังก์ชันแปลงวันที่เป็นภาษาไทย
    function formatThaiDate(dateStr) {
        if (!dateStr) return '-';
        const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        const day = d.getDate();
        const month = months[d.getMonth() + 1];
        const year = d.getFullYear() + 543;
        return `${day} ${month} ${year}`;
    }

    function escapeHtml(unsafe) {
        if (!unsafe && unsafe !== 0) return '-';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // generate a stable color from a string
    function colorFor(text) {
        const palette = ['#06b6d4','#7c3aed','#f97316','#10b981','#ef4444','#1e3a8a','#8b5cf6'];
        if (!text) return palette[0];
        let h = 0; for (let i = 0; i < text.length; i++) h = (h<<5) - h + text.charCodeAt(i);
        return palette[Math.abs(h) % palette.length];
    }

// Bring in the same showReportDetail layout used in teacher/report.php
function showReportDetail(reportId) {
        fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
            .then(res => res.json())
            .then(report => {
                const countList = (s) => {
                    if (!s) return 0;
                    return s.split(/[,\n]/).map(x => x.trim()).filter(Boolean).length;
                };

                const attendanceBreakdown = [
                    { label: '❌ ขาดเรียน', value: (typeof report.absent_count !== 'undefined') ? Number(report.absent_count) : countList(report.absent_students), color: 'text-rose-600' },
                    { label: '🤒 ลาป่วย', value: (typeof report.sick_count !== 'undefined') ? Number(report.sick_count) : countList(report.sick_students), color: 'text-sky-600' },
                    { label: '📝 ลากิจ', value: (typeof report.personal_count !== 'undefined') ? Number(report.personal_count) : countList(report.personal_students), color: 'text-amber-600' },
                    { label: '🎉 กิจกรรม', value: (typeof report.activity_count !== 'undefined') ? Number(report.activity_count) : countList(report.activity_students), color: 'text-violet-600' },
                    { label: '🚫 โดดเรียน', value: (typeof report.truant_count !== 'undefined') ? Number(report.truant_count) : countList(report.truant_students), color: 'text-gray-800' }
                ];

                const html = `
                    <div class="relative max-w-5xl mx-auto py-12">
                        <div class="absolute inset-0 bg-gradient-to-br from-sky-500 via-indigo-600 to-fuchsia-500 opacity-30 dark:opacity-20 blur-3xl rounded-3xl" aria-hidden="true"></div>

                        <div class="relative bg-white/95 dark:bg-gray-900/90 backdrop-blur-2xl rounded-3xl border border-white/30 dark:border-white/8 shadow-2xl overflow-hidden">
                            <div class="p-6 md:p-8 space-y-8 max-h-[85vh] overflow-y-auto">

                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">รายงานการสอน</p>
                                        <h3 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                                            📑 ${report.subject_name || '-'}
                                        </h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">${formatThaiDate(report.report_date)} · คาบ ${report.period_start}-${report.period_end} · ม.${report.level}/${report.class_room}</p>
                                    </div>
                                    <div class="flex-shrink-0 px-5 py-3 rounded-2xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white font-semibold flex items-center gap-2 shadow-lg">
                                        ✨ แผนที่ ${report.plan_number || 'ไม่ระบุ'}
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="rounded-2xl p-5 bg-gradient-to-br from-slate-50 to-white border border-slate-100 transition-all duration-300 hover:shadow-lg">
                                        <p class="text-sm text-slate-500">📝 แผน/หัวข้อ</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">${report.plan_topic || '-'}</p>
                                    </div>
                                    <div class="rounded-2xl p-5 bg-gradient-to-br from-slate-50 to-white border border-slate-100 transition-all duration-300 hover:shadow-lg">
                                        <p class="text-sm text-slate-500">👨‍🏫 กิจกรรม</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">${report.activity || '-'}</p>
                                    </div>
                                    <div class="rounded-2xl p-5 bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-100 transition-all duration-300 hover:shadow-lg">
                                        <p class="text-sm text-slate-500">📋 สถานะการเข้าเรียน</p>
                                        <div class="mt-3 space-y-1 text-sm">
                                            ${attendanceBreakdown.map(item => `<div class="flex justify-between ${item.color}"><span>${item.label}</span><span class="font-semibold">${typeof item.value === 'number' ? item.value : (item.value || 0)}</span></div>`).join('')}
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    ${['K','P','A'].map(key => {
                                        const labels = { K: 'ความรู้ (Knowledge)', P: 'ทักษะ (Practice)', A: 'เจตคติ (Attitude)' };
                                        const icons = { K: '💡', P: '🚀', A: '❤️' };
                                        const value = report[`reflection_${key.toLowerCase()}`] || '-';
                                        const colors = {
                                            K: 'from-sky-50 to-white border-sky-100 hover:shadow-sky-300/20',
                                            P: 'from-amber-50 to-white border-amber-100 hover:shadow-amber-300/20',
                                            A: 'from-pink-50 to-white border-pink-100 hover:shadow-pink-300/20'
                                        };
                                        return `
                                            <div class="rounded-2xl p-5 bg-gradient-to-br ${colors[key]} border transition-all duration-300 hover:shadow-xl">
                                                <p class="text-sm text-slate-500">${icons[key]} สะท้อนคิด (${key})</p>
                                                <p class="mt-2 text-base leading-relaxed text-slate-800">${value}</p>
                                                <p class="text-xs text-slate-400 mt-2">${labels[key]}</p>
                                            </div>`;
                                    }).join('')}
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="rounded-2xl p-5 bg-gradient-to-br from-rose-50 to-rose-100 border border-rose-200 transition-all duration-300 hover:shadow-lg">
                                        <p class="text-sm font-semibold text-rose-600">❗ ปัญหา / อุปสรรค</p>
                                        <p class="mt-3 text-base text-slate-800">${report.problems || '-'}</p>
                                    </div>
                                    <div class="rounded-2xl p-5 bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 transition-all duration-300 hover:shadow-lg">
                                        <p class="text-sm font-semibold text-emerald-600">📝 ข้อเสนอแนะ</p>
                                        <p class="mt-3 text-base text-slate-800">${report.suggestions || '-'}</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <p class="text-sm text-slate-500">🖼️ รูปภาพประกอบ</p>
                                    <div class="flex flex-wrap gap-4">
                                        ${report.image1 ? `<img src="../${report.image1}" class="w-40 h-28 object-cover rounded-2xl border border-slate-200 drop-shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" />` : `<div class="w-40 h-28 flex items-center justify-center rounded-2xl border border-dashed border-slate-300 text-sm text-slate-400">ไม่มีรูปภาพ</div>`}
                                        ${report.image2 ? `<img src="../${report.image2}" class="w-40 h-28 object-cover rounded-2xl border border-slate-200 drop-shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" />` : ''}
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-slate-200">
                                    <div class="text-xs text-slate-500 flex items-center gap-2">
                                        👩‍🏫 ผู้รายงาน: <span class="font-semibold text-slate-700">${report.teacher_name || report.teacher_id || '-'}</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    html: html,
                    width: 950,
                    showCloseButton: true,
                    showConfirmButton: false,
                    background: 'transparent',
                    padding: 0
                });
            });
}

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var searchInput = document.getElementById('search-subject');
        var filterLevel = document.getElementById('filter-level');
        var helpBtn = document.getElementById('helpBtn');

        // cache reports so searching is fast
        window._reportsCache = null;

        function mapReportsToEvents(reports) {
            return reports.map(report => ({
                id: report.id,
                title: (report.subject_name || '-') + ' (' + (report.class_room || '-') + ')',
                start: report.report_date,
                extendedProps: report
            }));
        }

        function filteredEvents(successCallback) {
            const q = (searchInput.value || '').trim().toLowerCase();
            const lvl = (filterLevel.value || '').trim();
            const reports = (window._reportsCache || []).filter(r => {
                let ok = true;
                if (q) {
                    const hay = ((r.subject_name||'') + ' ' + (r.plan_topic||'') + ' ' + (r.activity||'')).toLowerCase();
                    ok = ok && hay.indexOf(q) !== -1;
                }
                if (lvl) {
                    ok = ok && String(r.level||'').indexOf(lvl) !== -1;
                }
                return ok;
            });
            successCallback(mapReportsToEvents(reports));
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'th',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            dayMaxEvents: true,
            events: function(fetchInfo, successCallback, failureCallback) {
                if (window._reportsCache) {
                    filteredEvents(successCallback);
                    return;
                }
                fetch('../controllers/TeachingReportController.php?action=list')
                    .then(res => res.json())
                    .then(data => {
                        window._reportsCache = data || [];
                        filteredEvents(successCallback);
                    })
                    .catch(failureCallback);
            },
            eventContent: function(arg) {
                const rpt = arg.event.extendedProps || {};
                const subj = escapeHtml(rpt.subject_name || '-');
                const room = escapeHtml(rpt.class_room || '-');
                const color = colorFor(subj + room);
                const emoji = rpt.activity && rpt.activity.toLowerCase().includes('สอบ') ? '📝' : '📘';
                const html = ` <div class="flex items-center gap-2 px-2 py-1">
                        <span class="event-dot" style="background:${color}"></span>
                        <div class="truncate text-sm"><strong class="block">${emoji} ${subj}</strong><span class="text-xs text-white">ห้อง ${room}</span></div>
                    </div>`;
                return { html: html };
            },
            eventDidMount: function(info) {
                // add subtle border glow using box-shadow color
                const subj = info.event.extendedProps.subject_name || '';
                const color = colorFor(subj);
                info.el.style.border = '1px solid rgba(0,0,0,0.04)';
                info.el.style.boxShadow = 'none';
                info.el.title = (info.event.extendedProps.subject_name || '') + ' — คลิกเพื่อดูรายละเอียด';
            },
            eventClick: function(info) {
                // Reuse the detailed modal view used in report.php
                showReportDetail(info.event.id);
            }
        });

        calendar.render();

        // wire up search and filter to re-render events
        [searchInput, filterLevel].forEach(el => el.addEventListener('input', function() { calendar.refetchEvents(); }));

        helpBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'คู่มือปฏิทิน',
                html: '<div class="text-left">🔹 คลิกบนเหตุการณ์เพื่อดูรายละเอียด<br/>🔹 ใช้ช่องค้นหาเพื่อกรองวิชา/หัวข้อ<br/>🔹 เลือกระดับเพื่อกรองตามระดับชั้น</div>',
                icon: 'info'
            });
        });
    });
</script>
<?php require_once('script.php'); ?>
</body>
</html>
