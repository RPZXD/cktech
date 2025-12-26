<?php
/**
 * Supervision View
 * MVC Pattern - Teaching supervision evaluation form
 */

$jsTeacherName = json_encode($teacher_name ?? '');
$jsTeacherId = json_encode($teacherId ?? null);
?>

<style>
    /* Aurora Effect */
    .supervision-wrapper {
        position: relative;
        isolation: isolate;
    }
    .supervision-wrapper::before {
        content: '';
        position: absolute;
        inset: -40px;
        background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(168,85,247,0.2));
        filter: blur(60px);
        z-index: -1;
        border-radius: 999px;
        animation: supervisionGlow 10s ease-in-out infinite;
    }
    @keyframes supervisionGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    
    /* Radio Button Styles */
    .rating-group {
        display: flex;
        gap: 0.5rem;
    }
    .rating-option {
        position: relative;
    }
    .rating-option input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    .rating-option label {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        background: #f1f5f9;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .rating-option input:checked + label {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
    }
    .rating-option label:hover {
        background: #e2e8f0;
    }
    
    /* Mobile Table */
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards { display: flex !important; }
        .rating-group { flex-wrap: wrap; gap: 0.25rem; }
        .rating-option label { width: 32px; height: 32px; font-size: 0.75rem; }
    }
    @media (min-width: 769px) {
        .desktop-table { display: block !important; }
        .mobile-cards { display: none !important; }
    }
    
    /* Evaluation Card */
    .eval-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: white;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    .eval-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .dark .eval-item {
        background: #1e293b;
    }
    
    /* Score Badge */
    .score-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 1.25rem;
    }
</style>

<!-- Page Header -->
<div class="supervision-wrapper mb-4 md:mb-6">
    <div class="glass rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-xl border border-white/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="text-center md:text-left">
                <h1 class="text-xl md:text-2xl lg:text-3xl font-extrabold flex flex-wrap items-center justify-center md:justify-start gap-2 text-slate-900 dark:text-white">
                    <span class="text-2xl md:text-3xl">👁️</span>
                    <span class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">
                        การนิเทศการสอน
                    </span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-xs md:text-sm mt-1">
                    บันทึกและประเมินสมรรถนะการจัดการเรียนรู้
                </p>
            </div>
            
            <button id="btnAddSupervision" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <span class="text-xl">➕</span>
                <span>บันทึกการนิเทศ</span>
            </button>
        </div>
    </div>
</div>

<!-- Desktop Table -->
<div class="desktop-table glow-card glass rounded-2xl md:rounded-3xl p-3 md:p-6 shadow-xl border border-white/20 bg-white/90 dark:bg-gray-800/90 mb-4">
    <div class="overflow-x-auto">
        <table class="w-full" id="supervisionTable">
            <thead>
                <tr class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm">
                    <th class="py-3 px-3 rounded-tl-lg">📅 วันที่</th>
                    <th class="py-3 px-3">👨‍🏫 ผู้รับการนิเทศ</th>
                    <th class="py-3 px-3">📖 วิชา</th>
                    <th class="py-3 px-3">🏫 ชั้น</th>
                    <th class="py-3 px-3">🔢 ครั้งที่</th>
                    <th class="py-3 px-3">📊 คะแนน</th>
                    <th class="py-3 px-3">🏆 ระดับ</th>
                    <th class="py-3 px-3 rounded-tr-lg">⚙️ จัดการ</th>
                </tr>
            </thead>
            <tbody id="supervisionTableBody" class="text-sm">
                <tr>
                    <td colspan="8" class="text-center py-8 text-gray-500">
                        <div class="text-4xl mb-2">👁️</div>
                        กำลังโหลดข้อมูล...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Cards -->
<div id="mobileSupervisionCards" class="mobile-cards flex-col gap-3" style="display: none;">
    <div class="text-center py-8 text-gray-500">
        <div class="text-4xl mb-2">👁️</div>
        กำลังโหลดข้อมูล...
    </div>
</div>

<!-- Modal -->
<div id="modalSupervision" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-start justify-center z-50 hidden overflow-y-auto p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl my-4 ring-1 ring-gray-200 dark:ring-gray-700">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10 rounded-t-2xl">
            <div>
                <h2 id="modalSupervisionTitle" class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    👁️ <span id="modalTitleText">บันทึกการนิเทศ</span>
                </h2>
            </div>
            <button id="closeModalSupervision" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
                ✕
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="formSupervision" class="p-4 space-y-6 max-h-[75vh] overflow-y-auto" enctype="multipart/form-data">
            
            <!-- Section 1: Basic Info -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-4 rounded-xl border-l-4 border-blue-500">
                <h3 class="text-lg font-bold text-blue-700 dark:text-blue-400 mb-4 flex items-center gap-2">
                    📋 ข้อมูลทั่วไป
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            ชื่อผู้รับการนิเทศ <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="teacher_name" value="<?php echo htmlspecialchars($teacher_name); ?>" required
                            class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">ตำแหน่ง</label>
                        <input type="text" name="position" placeholder="เช่น ครู"
                            class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">กลุ่มสาระการเรียนรู้</label>
                        <select name="subject_group" class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">-- เลือกกลุ่มสาระ --</option>
                            <option value="ภาษาไทย">ภาษาไทย</option>
                            <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                            <option value="วิทยาศาสตร์และเทคโนโลยี">วิทยาศาสตร์และเทคโนโลยี</option>
                            <option value="สังคมศึกษา ศาสนา และวัฒนธรรม">สังคมศึกษา ศาสนา และวัฒนธรรม</option>
                            <option value="สุขศึกษาและพลศึกษา">สุขศึกษาและพลศึกษา</option>
                            <option value="ศิลปะ">ศิลปะ</option>
                            <option value="การงานอาชีพ">การงานอาชีพ</option>
                            <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">รายวิชา</label>
                        <input type="text" name="subject_name" 
                            class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">ชั้น</label>
                        <input type="text" name="class_level" placeholder="เช่น 1/1"
                            class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">นิเทศครั้งที่</label>
                        <input type="number" name="supervision_round" min="1" value="1"
                            class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            วันที่นิเทศ <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="supervision_date" required
                            class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">ภาคเรียน/ปีการศึกษา</label>
                        <input type="text" name="semester_year" placeholder="เช่น 2/2567"
                            class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Evaluation - Planning -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-xl border-l-4 border-green-500">
                <h3 class="text-lg font-bold text-green-700 dark:text-green-400 mb-4 flex items-center gap-2">
                    📊 ด้านการจัดทำแผนการเรียนรู้
                </h3>
                
                <div class="space-y-3" id="evalSection1">
                    <!-- Items will be populated by JS -->
                </div>
            </div>
            
            <!-- Section 3: Evaluation - Teaching -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 p-4 rounded-xl border-l-4 border-purple-500">
                <h3 class="text-lg font-bold text-purple-700 dark:text-purple-400 mb-4 flex items-center gap-2">
                    👨‍🏫 ด้านการจัดการเรียนรู้
                </h3>
                
                <div class="space-y-3" id="evalSection2">
                    <!-- Items will be populated by JS -->
                </div>
            </div>
            
            <!-- Section 4: Evaluation - Assessment -->
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-4 rounded-xl border-l-4 border-amber-500">
                <h3 class="text-lg font-bold text-amber-700 dark:text-amber-400 mb-4 flex items-center gap-2">
                    📝 ด้านการประเมินผล (5 ข้อ)
                </h3>
                
                <div class="space-y-3" id="evalSection3">
                    <!-- Items will be populated by JS -->
                </div>
            </div>
            
            <!-- Section 5: Evaluation - Environment -->
            <div class="bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 p-4 rounded-xl border-l-4 border-teal-500">
                <h3 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                    🏫 ด้านการจัดสภาพแวดล้อม (6 ข้อ)
                </h3>
                
                <div class="space-y-3" id="evalSection4">
                    <!-- Items will be populated by JS -->
                </div>
            </div>
            
            <!-- Section 6: File Uploads -->
            <div class="bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 p-4 rounded-xl border-l-4 border-cyan-500">
                <h3 class="text-lg font-bold text-cyan-700 dark:text-cyan-400 mb-4 flex items-center gap-2">
                    📎 เอกสารแนบ
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            แผนการจัดการเรียนรู้ (PDF) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="lesson_plan" accept=".pdf"
                            class="w-full px-4 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-100 file:text-indigo-700" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            รูปภาพประกอบ
                        </label>
                        <input type="file" name="images[]" accept="image/*" multiple
                            class="w-full px-4 py-2 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-100 file:text-indigo-700" />
                    </div>
                </div>
            </div>
            
            <!-- Section 6: Summary -->
            <div class="bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900/20 dark:to-slate-900/20 p-4 rounded-xl border-l-4 border-gray-500">
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-400 mb-4 flex items-center gap-2">
                    💬 ความคิดเห็น/ข้อเสนอแนะ
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">จุดเด่น</label>
                        <textarea name="strengths" rows="2" class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">จุดที่ควรพัฒนา</label>
                        <textarea name="improvements" rows="2" class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">ข้อเสนอแนะ</label>
                        <textarea name="suggestions" rows="2" class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700 sticky bottom-0 bg-white dark:bg-gray-800 rounded-b-2xl">
            <div id="totalScoreDisplay" class="text-lg font-bold text-gray-700 dark:text-gray-300">
                คะแนนรวม: <span id="totalScoreValue" class="text-indigo-600">0</span>/125
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="cancelSupervision" class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium transition-colors">
                    ยกเลิก
                </button>
                <button type="submit" form="formSupervision" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium shadow-lg transition-all">
                    💾 บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Config for JS -->
<script>
    window.SUPERVISION_CONFIG = {
        teacherId: <?php echo $jsTeacherId; ?>,
        teacherName: <?php echo $jsTeacherName; ?>
    };
</script>

<!-- External JS -->
<script src="js/supervision.js?v=4"></script>
