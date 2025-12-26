<?php
/**
 * Calendar View
 * MVC Pattern - View layer for teacher calendar page
 * Uses FullCalendar with enhanced mobile experience
 */
?>

<style>
    /* Calendar Custom Styles */
    .calendar-wrapper {
        position: relative;
        isolation: isolate;
    }
    .calendar-wrapper::before {
        content: '';
        position: absolute;
        inset: -40px;
        background: linear-gradient(135deg, rgba(14,165,233,0.3), rgba(139,92,246,0.2));
        filter: blur(60px);
        z-index: -1;
        border-radius: 999px;
        animation: calendarGlow 10s ease-in-out infinite;
    }
    @keyframes calendarGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    
    /* FullCalendar Customization */
    .fc { font-family: inherit; }
    .fc-toolbar { flex-wrap: wrap; gap: 0.5rem; }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700 !important; }
    .fc-button { 
        background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
        border: none !important;
        border-radius: 0.75rem !important;
        padding: 0.5rem 1rem !important;
        font-weight: 600 !important;
        transition: all 0.2s !important;
    }
    .fc-button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4); }
    .fc-button-active { background: linear-gradient(135deg, #4f46e5, #7c3aed) !important; }
    .fc-daygrid-day { transition: background 0.2s; }
    .fc-daygrid-day:hover { background: rgba(99, 102, 241, 0.05); }
    .fc-daygrid-day-number { font-weight: 600; color: #334155; }
    .fc-day-today { background: rgba(16, 185, 129, 0.1) !important; }
    .fc-event { 
        border-radius: 0.5rem !important; 
        border: none !important; 
        padding: 2px 6px !important;
        transition: all 0.2s !important;
        cursor: pointer;
    }
    .fc-event:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .fc-col-header-cell { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .fc-col-header-cell-cushion { color: white !important; font-weight: 600; padding: 0.75rem 0.5rem !important; }
    
    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .fc-toolbar { justify-content: center; }
        .fc-toolbar-title { font-size: 1rem !important; width: 100%; text-align: center; order: -1; margin-bottom: 0.5rem !important; }
        .fc-toolbar-chunk { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.25rem; }
        .fc-button { padding: 0.4rem 0.6rem !important; font-size: 0.75rem !important; }
        .fc-daygrid-day-number { font-size: 0.875rem; }
        .fc-daygrid-event { font-size: 0.7rem !important; }
        .fc-event { padding: 1px 4px !important; }
    }
    
    /* Event badge */
    .event-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    /* Legend pills */
    .legend-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: white;
        border: 1px solid rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .legend-pill:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
</style>

<!-- Page Header with Aurora Effect -->
<div class="calendar-wrapper mb-4 md:mb-6">
    <div class="glass rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-xl border border-white/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Title -->
            <div class="text-center md:text-left">
                <h1 class="text-xl md:text-2xl lg:text-3xl font-extrabold flex flex-wrap items-center justify-center md:justify-start gap-2 text-slate-900 dark:text-white">
                    <span class="text-2xl md:text-3xl">📅</span>
                    <span class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">
                        ปฏิทินการสอน
                    </span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-xs md:text-sm mt-1">
                    ดูรายงานการสอนของคุณในรูปแบบปฏิทิน
                </p>
            </div>
            
            <!-- Search & Filter -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                    <input id="search-subject" type="text" placeholder="ค้นหาวิชา, หัวข้อ..." 
                        class="w-full sm:w-48 pl-10 pr-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all" />
                </div>
                <select id="filter-subject" 
                    class="px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                    <option value="">ทุกวิชา</option>
                </select>
                <select id="filter-level" 
                    class="px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                    <option value="">ทุกระดับ</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Legend (Generated by JS) -->
<div id="subject-legend" class="flex flex-wrap justify-center md:justify-start gap-2 mb-4">
    <div class="text-xs text-gray-400 py-2">กำลังโหลดข้อมูลวิชา...</div>
</div>

<!-- Calendar Container -->
<div class="glow-card glass rounded-2xl md:rounded-3xl p-3 md:p-6 shadow-xl border border-white/20 bg-white/90 dark:bg-gray-800/90">
    <div id="calendar" class="min-h-[400px] md:min-h-[600px]"></div>
</div>

<!-- FAB Help Button (Mobile) -->
<button id="helpBtn" class="fixed right-4 bottom-20 md:bottom-6 z-30 w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full shadow-lg shadow-indigo-500/30 flex items-center justify-center text-xl hover:scale-110 transition-all duration-300" aria-label="ความช่วยเหลือ">
    ❓
</button>

<!-- FullCalendar Library -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<!-- External JS -->
<script src="js/calendar.js?v=3"></script>
