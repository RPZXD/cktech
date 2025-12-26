<?php
/**
 * Subject Management View
 * MVC Pattern - View layer for subject management page
 */

// Pass PHP variables to JavaScript
$jsTeacherId = json_encode($teacherId ?? null);
?>

<style>
    /* Subject Page Styles */
    .subject-wrapper {
        position: relative;
        isolation: isolate;
    }
    .subject-wrapper::before {
        content: '';
        position: absolute;
        inset: -40px;
        background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2));
        filter: blur(60px);
        z-index: -1;
        border-radius: 999px;
        animation: subjectGlow 10s ease-in-out infinite;
    }
    @keyframes subjectGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    
    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }
    input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }
    
    /* Filter Chips */
    .filter-chip {
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        background: white;
        border: 2px solid #e2e8f0;
        transition: all 0.2s;
    }
    .filter-chip:hover {
        border-color: #6366f1;
    }
    .filter-chip.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border-color: transparent;
    }
    
    /* Mobile Cards */
    @media (max-width: 768px) {
        .subject-table-wrap { display: none; }
        .subject-cards { display: flex; flex-direction: column; gap: 1rem; }
    }
    @media (min-width: 769px) {
        .subject-cards { display: none; }
    }
</style>

<!-- Page Header -->
<div class="subject-wrapper mb-4 md:mb-6">
    <div class="glass rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-xl border border-white/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Title -->
            <div class="text-center md:text-left">
                <h1 class="text-xl md:text-2xl lg:text-3xl font-extrabold flex flex-wrap items-center justify-center md:justify-start gap-2 text-slate-900 dark:text-white">
                    <span class="text-2xl md:text-3xl">📚</span>
                    <span class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">
                        จัดการรายวิชา
                    </span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-xs md:text-sm mt-1">
                    เพิ่ม แก้ไข และจัดการรายวิชาที่คุณสอน
                </p>
            </div>
            
            <!-- Add Button -->
            <button id="btnAddSubject" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <span class="text-xl">➕</span>
                <span>เพิ่มรายวิชา</span>
            </button>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="glow-card glass rounded-2xl md:rounded-3xl p-4 shadow-xl border border-white/20 mb-4">
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <!-- Search -->
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            <input id="subjectSearch" type="text" placeholder="ค้นหารหัสหรือชื่อวิชา..." 
                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all" />
        </div>
        
        <!-- Filter Chips -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <button class="filter-chip active" data-val="">ทั้งหมด</button>
            <button class="filter-chip" data-val="เปิดสอน">✅ เปิดสอน</button>
            <button class="filter-chip" data-val="ไม่เปิดสอน">❌ ปิด</button>
        </div>
    </div>
</div>

<!-- Desktop Table -->
<div class="subject-table-wrap glow-card glass rounded-2xl md:rounded-3xl p-3 md:p-6 shadow-xl border border-white/20 bg-white/90 dark:bg-gray-800/90 mb-4">
    <div class="overflow-x-auto">
        <table class="w-full" id="subjectTable">
            <thead>
                <tr class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm">
                    <th class="py-3 px-4 rounded-tl-lg">🔢 รหัส</th>
                    <th class="py-3 px-4 text-left">📖 ชื่อวิชา</th>
                    <th class="py-3 px-4">🏫 ระดับ</th>
                    <th class="py-3 px-4">🗂️ ประเภท</th>
                    <th class="py-3 px-4">✅ สถานะ</th>
                    <th class="py-3 px-4">⏰ คาบสอน</th>
                    <th class="py-3 px-4 rounded-tr-lg">⚙️ จัดการ</th>
                </tr>
            </thead>
            <tbody class="text-sm" id="subjectTableBody">
                <tr>
                    <td colspan="7" class="text-center py-8 text-gray-500">
                        <div class="text-4xl mb-2">📚</div>
                        กำลังโหลดข้อมูล...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Cards -->
<div class="subject-cards" id="subjectCards">
    <div class="text-center py-8 text-gray-500">
        <div class="text-4xl mb-2">📚</div>
        กำลังโหลดข้อมูล...
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modalAddSubject" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-start md:items-center justify-center z-50 hidden overflow-y-auto p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl my-4 ring-1 ring-gray-200 dark:ring-gray-700">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h2 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    🎓 <span id="modalTitleText">เพิ่มรายวิชา</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">กรอกข้อมูลให้ครบถ้วนแล้วกดบันทึก</p>
            </div>
            <button id="closeModalAddSubject" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
                ✕
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="formAddSubject" class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Code -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        รหัสวิชา <span class="text-red-500">*</span>
                    </label>
                    <input id="inputCode" type="text" name="code" required maxlength="10"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        placeholder="เช่น ง11101" />
                </div>
                
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        ชื่อวิชา <span class="text-red-500">*</span>
                    </label>
                    <input id="inputName" type="text" name="name" required
                        class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        placeholder="ชื่อวิชา" />
                </div>
                
                <!-- Level -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        ระดับชั้น <span class="text-red-500">*</span>
                    </label>
                    <select name="level" required
                        class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">-- เลือกระดับชั้น --</option>
                        <option value="1">มัธยมศึกษาปีที่ 1</option>
                        <option value="2">มัธยมศึกษาปีที่ 2</option>
                        <option value="3">มัธยมศึกษาปีที่ 3</option>
                        <option value="4">มัธยมศึกษาปีที่ 4</option>
                        <option value="5">มัธยมศึกษาปีที่ 5</option>
                        <option value="6">มัธยมศึกษาปีที่ 6</option>
                    </select>
                </div>
                
                <!-- Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        ประเภทวิชา
                    </label>
                    <select name="subject_type"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">-- เลือกประเภท --</option>
                        <option value="พื้นฐาน">พื้นฐาน</option>
                        <option value="เพิ่มเติม">เพิ่มเติม</option>
                        <option value="กิจกรรมพัฒนาผู้เรียน">กิจกรรมพัฒนาผู้เรียน</option>
                    </select>
                </div>
                
                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        สถานะ
                    </label>
                    <select name="status"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="เปิดสอน">✅ เปิดสอน</option>
                        <option value="ไม่เปิดสอน">❌ ไม่เปิดสอน</option>
                    </select>
                </div>
            </div>
            
            <!-- Class Rooms -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    เลือกห้องเรียน <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-wrap gap-2" id="classRoomCheckboxWrap">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-indigo-400 cursor-pointer text-sm transition-colors">
                        <input type="checkbox" name="class_room[]" value="ห้อง <?php echo $i; ?>" 
                            class="form-checkbox text-indigo-600 rounded class-room-checkbox" />
                        <span class="text-gray-700 dark:text-gray-300">ห้อง <?php echo $i; ?></span>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Class Room Details -->
            <div id="classRoomDetails" class="space-y-3">
                <!-- Dynamic content from JS -->
            </div>
        </form>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" id="cancelAddSubject" class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium transition-colors">
                ยกเลิก
            </button>
            <button type="submit" form="formAddSubject" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium shadow-lg transition-all">
                💾 บันทึก
            </button>
        </div>
    </div>
</div>

<!-- Pass PHP variables to JavaScript -->
<script>
    window.SUBJECT_CONFIG = {
        teacherId: <?php echo $jsTeacherId; ?>
    };
</script>

<!-- External JS -->
<script src="js/subject.js?v=1"></script>
