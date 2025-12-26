/**
 * Calendar Page JavaScript
 * MVC Pattern - Handles calendar interactions and data loading
 */

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const searchInput = document.getElementById('search-subject');
    const filterSubject = document.getElementById('filter-subject');
    const filterLevel = document.getElementById('filter-level');
    const legendContainer = document.getElementById('subject-legend');
    const helpBtn = document.getElementById('helpBtn');

    // Cache reports for fast filtering
    window._reportsCache = null;

    // Thai date formatter
    function formatThaiDate(dateStr) {
        if (!dateStr) return '-';
        const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
    }

    // Generate stable color from string
    function colorFor(text) {
        const palette = [
            'linear-gradient(135deg, #06b6d4, #0891b2)', // cyan
            'linear-gradient(135deg, #8b5cf6, #7c3aed)', // violet
            'linear-gradient(135deg, #f97316, #ea580c)', // orange
            'linear-gradient(135deg, #10b981, #059669)', // emerald
            'linear-gradient(135deg, #ef4444, #dc2626)', // red
            'linear-gradient(135deg, #3b82f6, #2563eb)', // blue
            'linear-gradient(135deg, #ec4899, #db2777)'  // pink
        ];
        if (!text) return palette[0];
        let h = 0;
        for (let i = 0; i < text.length; i++) h = (h << 5) - h + text.charCodeAt(i);
        return palette[Math.abs(h) % palette.length];
    }

    // Map reports to calendar events
    function mapReportsToEvents(reports) {
        return reports.map(report => ({
            id: report.id,
            title: `${report.subject_name || '-'} (${report.class_room || '-'})`,
            start: report.report_date,
            extendedProps: report,
            backgroundColor: 'transparent',
            borderColor: 'transparent'
        }));
    }

    // Filter events based on search, subject, and level
    function filteredEvents(successCallback) {
        const q = (searchInput.value || '').trim().toLowerCase();
        const subj = (filterSubject.value || '').trim();
        const lvl = (filterLevel.value || '').trim();
        const reports = (window._reportsCache || []).filter(r => {
            let ok = true;
            if (q) {
                const hay = `${r.subject_name || ''} ${r.plan_topic || ''} ${r.activity || ''}`.toLowerCase();
                ok = ok && hay.includes(q);
            }
            if (subj) {
                ok = ok && (r.subject_name || '') === subj;
            }
            if (lvl) {
                ok = ok && String(r.level || '') === lvl;
            }
            return ok;
        });
        successCallback(mapReportsToEvents(reports));
    }

    // Build dynamic legend and filters from data
    function buildLegendAndFilters(reports) {
        // Get unique subjects
        const subjects = [...new Set(reports.map(r => r.subject_name).filter(Boolean))];
        const levels = [...new Set(reports.map(r => r.level).filter(Boolean))].sort((a, b) => a - b);

        // Build subject filter options
        filterSubject.innerHTML = '<option value="">ทุกวิชา</option>';
        subjects.forEach(subj => {
            filterSubject.innerHTML += `<option value="${subj}">${subj}</option>`;
        });

        // Build level filter options
        filterLevel.innerHTML = '<option value="">ทุกระดับ</option>';
        levels.forEach(lvl => {
            filterLevel.innerHTML += `<option value="${lvl}">ม.${lvl}</option>`;
        });

        // Build legend
        if (subjects.length === 0) {
            legendContainer.innerHTML = '<div class="text-xs text-gray-400 py-2">ยังไม่มีข้อมูลวิชา</div>';
            return;
        }

        legendContainer.innerHTML = subjects.map(subj => {
            const color = colorFor(subj);
            return `
                <button type="button" class="legend-pill cursor-pointer hover:ring-2 hover:ring-indigo-300" data-subject="${subj}">
                    <span class="legend-dot" style="background: ${color}"></span>
                    <span class="text-gray-700 dark:text-gray-300 text-xs">${subj}</span>
                </button>
            `;
        }).join('');

        // Click on legend to filter
        legendContainer.querySelectorAll('.legend-pill').forEach(pill => {
            pill.addEventListener('click', function () {
                const subject = this.dataset.subject;
                filterSubject.value = subject;
                handleFilterChange();
            });
        });
    }

    // Show report detail modal
    function showReportDetail(reportId) {
        fetch('../controllers/TeachingReportController.php?action=detail&id=' + encodeURIComponent(reportId))
            .then(res => res.json())
            .then(report => {
                const countList = (s) => {
                    if (!s) return 0;
                    return s.split(/[,\n]/).map(x => x.trim()).filter(Boolean).length;
                };

                const attendanceBreakdown = [
                    { label: '❌ ขาดเรียน', value: report.absent_count ?? countList(report.absent_students), color: 'text-rose-500' },
                    { label: '🤒 ลาป่วย', value: report.sick_count ?? countList(report.sick_students), color: 'text-sky-500' },
                    { label: '📝 ลากิจ', value: report.personal_count ?? countList(report.personal_students), color: 'text-indigo-500' },
                    { label: '🎉 กิจกรรม', value: report.activity_count ?? countList(report.activity_students), color: 'text-purple-500' },
                    { label: '🚫 โดดเรียน', value: report.truant_count ?? countList(report.truant_students), color: 'text-gray-700' }
                ];

                const html = `
                    <div class="relative max-w-4xl mx-auto py-4 md:py-6">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/30 via-purple-500/20 to-pink-500/30 blur-3xl rounded-3xl"></div>
                        <div class="relative bg-white/95 dark:bg-gray-900/90 backdrop-blur-2xl rounded-2xl border border-white/40 shadow-2xl overflow-hidden">
                            <div class="p-4 md:p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                                
                                <!-- Header -->
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-slate-500">รายงานการสอน</p>
                                        <h3 class="text-lg md:text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            📑 ${report.subject_name || '-'}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            📅 ${formatThaiDate(report.report_date)} · ⏰ คาบ ${report.period_start}-${report.period_end} · 🏫 ม.${report.level}/${report.class_room}
                                        </p>
                                    </div>
                                    <div class="px-3 py-1.5 rounded-lg bg-gradient-to-r from-emerald-400 to-green-500 text-white font-semibold text-sm shadow">
                                        📋 แผน ${report.plan_number || '-'}
                                    </div>
                                </div>
                                
                                <!-- Topic & Activity -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="rounded-xl p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800">
                                        <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">📝 หัวข้อ/สาระการเรียนรู้</p>
                                        <p class="mt-1 text-sm text-slate-800 dark:text-white">${report.plan_topic || '-'}</p>
                                    </div>
                                    <div class="rounded-xl p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">
                                        <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase">👨‍🏫 กิจกรรมการเรียนรู้</p>
                                        <p class="mt-1 text-sm text-slate-800 dark:text-white">${report.activity || '-'}</p>
                                    </div>
                                </div>
                                
                                <!-- KPA Reflections -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                    <div class="rounded-lg p-3 bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-800">
                                        <p class="text-xs font-semibold text-green-600">💡 ความรู้ (K)</p>
                                        <p class="mt-1 text-xs text-slate-700 dark:text-gray-300">${report.reflection_k || '-'}</p>
                                    </div>
                                    <div class="rounded-lg p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800">
                                        <p class="text-xs font-semibold text-amber-600">⚡ กระบวนการ (P)</p>
                                        <p class="mt-1 text-xs text-slate-700 dark:text-gray-300">${report.reflection_p || '-'}</p>
                                    </div>
                                    <div class="rounded-lg p-3 bg-pink-50 dark:bg-pink-900/30 border border-pink-100 dark:border-pink-800">
                                        <p class="text-xs font-semibold text-pink-600">❤️ เจตคติ (A)</p>
                                        <p class="mt-1 text-xs text-slate-700 dark:text-gray-300">${report.reflection_a || '-'}</p>
                                    </div>
                                </div>
                                
                                <!-- Problems & Suggestions -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="rounded-xl p-3 bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800">
                                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase">⚠️ ปัญหา/อุปสรรค</p>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-gray-300">${report.problems || '-'}</p>
                                    </div>
                                    <div class="rounded-xl p-3 bg-teal-50 dark:bg-teal-900/30 border border-teal-100 dark:border-teal-800">
                                        <p class="text-xs font-semibold text-teal-600 dark:text-teal-400 uppercase">💬 ข้อเสนอแนะ</p>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-gray-300">${report.suggestions || '-'}</p>
                                    </div>
                                </div>
                                
                                <!-- Attendance -->
                                <div class="rounded-xl p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600">
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase mb-2">📊 สถานะการเข้าเรียน</p>
                                    <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                                        ${attendanceBreakdown.map(item => `
                                            <div class="text-center p-2 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                                                <div class="text-lg font-bold ${item.color}">${item.value || 0}</div>
                                                <div class="text-[10px] text-gray-500">${item.label}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                
                                <!-- Images -->
                                ${(report.image1 || report.image2) ? `
                                <div class="rounded-xl p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase mb-2">🖼️ รูปภาพประกอบ</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        ${report.image1 ? `<img src="../${report.image1}" class="w-full rounded-lg shadow-md cursor-pointer hover:scale-105 transition-transform" alt="รูปที่ 1" onclick="window.open('../${report.image1}', '_blank')">` : ''}
                                        ${report.image2 ? `<img src="../${report.image2}" class="w-full rounded-lg shadow-md cursor-pointer hover:scale-105 transition-transform" alt="รูปที่ 2" onclick="window.open('../${report.image2}', '_blank')">` : ''}
                                    </div>
                                </div>
                                ` : ''}
                                
                            </div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    html: html,
                    width: 850,
                    showCloseButton: true,
                    showConfirmButton: false,
                    background: 'transparent',
                    padding: 0
                });
            })
            .catch(err => {
                console.error('Error loading report:', err);
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
            });
    }

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
        locale: 'th',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'วันนี้',
            month: 'เดือน',
            week: 'สัปดาห์',
            list: 'รายการ'
        },
        dayMaxEvents: 3,
        moreLinkText: (n) => `+${n} เพิ่มเติม`,
        events: function (fetchInfo, successCallback, failureCallback) {
            if (window._reportsCache) {
                filteredEvents(successCallback);
                return;
            }
            fetch('../controllers/TeachingReportController.php?action=list')
                .then(res => res.json())
                .then(data => {
                    window._reportsCache = data || [];
                    buildLegendAndFilters(data || []);
                    filteredEvents(successCallback);
                })
                .catch(failureCallback);
        },
        eventContent: function (arg) {
            const rpt = arg.event.extendedProps || {};
            const subj = rpt.subject_name || '-';
            const room = rpt.class_room || '-';
            const level = rpt.level || '';
            const bgColor = colorFor(subj);
            const emoji = (rpt.activity && rpt.activity.toLowerCase().includes('สอบ')) ? '📝' : '📘';

            const html = `
                <div class="px-2 py-1 rounded-md text-white text-xs font-medium truncate" style="background: ${bgColor}">
                    ${emoji} ${subj} <span class="opacity-80">(ม.${level}/${room})</span>
                </div>
            `;
            return { html: html };
        },
        eventClick: function (info) {
            showReportDetail(info.event.id);
        },
        eventDidMount: function (info) {
            const title = `${info.event.extendedProps.subject_name || ''} — คลิกเพื่อดูรายละเอียด`;
            info.el.setAttribute('title', title);
        }
    });

    calendar.render();

    // Search and filter handlers
    let debounceTimer;
    function handleFilterChange() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => calendar.refetchEvents(), 300);
    }

    searchInput.addEventListener('input', handleFilterChange);
    filterSubject.addEventListener('change', handleFilterChange);
    filterLevel.addEventListener('change', handleFilterChange);

    // Help button
    helpBtn.addEventListener('click', function () {
        Swal.fire({
            title: '📅 คู่มือการใช้งานปฏิทิน',
            html: `
                <div class="text-left space-y-3 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="text-lg">👆</span>
                        <span><strong>คลิกที่เหตุการณ์</strong> เพื่อดูรายละเอียดรายงานการสอน</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-lg">🔍</span>
                        <span><strong>ใช้ช่องค้นหา</strong> เพื่อกรองตามชื่อวิชาหรือหัวข้อ</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-lg">🎚️</span>
                        <span><strong>เลือกระดับชั้น</strong> เพื่อกรองตามระดับ ม.1-6</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-lg">📱</span>
                        <span><strong>บน Mobile</strong> ใช้มุมมอง "รายการ" เพื่อดูง่ายขึ้น</span>
                    </div>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'เข้าใจแล้ว',
            confirmButtonColor: '#6366f1'
        });
    });

    // Responsive view switching
    window.addEventListener('resize', function () {
        const isMobile = window.innerWidth < 768;
        const currentView = calendar.view.type;
        if (isMobile && currentView === 'dayGridMonth') {
            // Keep current view on mobile, user can switch manually
        }
    });
});
