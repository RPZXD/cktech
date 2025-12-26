<?php
/**
 * Department Supervision View
 * MVC Pattern - View for department head supervision evaluation
 */
?>

<style>
    .modal-backdrop { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); }
    .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    
    /* Scoring Radios */
    .score-radio { display: none; }
    .score-label {
        display: flex; align-items: center; justify-content: center;
        width: 40px; height: 40px; border-radius: 12px;
        background: #f8fafc; border: 2px solid #e2e8f0;
        cursor: pointer; transition: all 0.2s; font-weight: 800; color: #64748b;
    }
    .score-radio:checked + .score-label {
        background: #3b82f6; border-color: #3b82f6; color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transform: scale(1.1);
    }
    .score-label:hover { border-color: #3b82f6; color: #3b82f6; }
</style>

<div class="space-y-6">
    <!-- Header & Stats -->
    <div class="glass rounded-[2.5rem] p-8 shadow-xl border border-white/20 slide-up">
        <div class="flex flex-col lg:flex-row justify-between gap-8">
            <div class="flex-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-purple-500/20">
                        👁️
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-black text-slate-800 dark:text-white">การนิเทศการสอน</h1>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">จัดการและประเมินผลการนิเทศของครูในกลุ่มสาระ <?php echo htmlspecialchars($department); ?></p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8">
                    <div class="p-4 bg-white/50 dark:bg-slate-800/50 rounded-3xl border border-white/50 shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">ทั้งหมด</p>
                        <p id="statTotal" class="text-2xl font-black text-slate-800 dark:text-white">0</p>
                    </div>
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-3xl border border-emerald-100 dark:border-emerald-900/30">
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">ประเมินแล้ว</p>
                        <p id="statEvaluated" class="text-2xl font-black text-emerald-600 dark:text-emerald-400">0</p>
                    </div>
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-3xl border border-amber-100 dark:border-amber-900/30">
                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">รอดำเนินการ</p>
                        <p id="statPending" class="text-2xl font-black text-amber-600 dark:text-amber-400">0</p>
                    </div>
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-3xl border border-blue-100 dark:border-blue-900/30">
                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">คะแนนเฉลี่ย</p>
                        <p id="statAvgScore" class="text-2xl font-black text-blue-600 dark:text-blue-400">0.0</p>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="w-full lg:w-80 space-y-4">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="ค้นหาชื่อครู หรือ รายวิชา..." class="w-full pl-12 pr-4 py-3 bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl focus:border-purple-500 transition-all font-bold text-sm">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <select id="filterYear" class="bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-500">
                        <option value="">ทุกปีการศึกษา</option>
                        <?php for($y = date('Y')+543; $y >= 2565; $y--): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <select id="filterRound" class="bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-500">
                        <option value="">ทุกรอบการนิเทศ</option>
                        <option value="1">รอบที่ 1</option>
                        <option value="2">รอบที่ 2</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="glass rounded-[2.5rem] shadow-xl border border-white/20 overflow-hidden fade-in">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-black tracking-widest">
                        <th class="p-6 text-center w-16">#</th>
                        <th class="p-6 text-left">ข้อมูลครูและรายวิชา</th>
                        <th class="p-6 text-center">ระดับชั้น</th>
                        <th class="p-6 text-center">วันที่นิเทศ</th>
                        <th class="p-6 text-center">คะแนนครู</th>
                        <th class="p-6 text-center">การประเมิน</th>
                        <th class="p-6 text-center w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="supervisionTableBody" class="divide-y divide-slate-50 dark:divide-slate-800">
                    <!-- JS Filled -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Evaluation Modal -->
<div id="modalSupervision" class="fixed inset-0 z-[60] overflow-y-auto hidden">
    <div class="modal-backdrop absolute inset-0"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-5xl overflow-hidden animate-slide-up">
            <!-- Modal Header -->
            <div class="bg-slate-50 dark:bg-slate-800/50 p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center text-white text-xl">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-800 dark:text-white">ประเมินการนิเทศการสอน</h2>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Evaluation for Department Head</p>
                    </div>
                </div>
                <button id="closeModalSupervision" class="w-10 h-10 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-8">
                <form id="formSupervision" class="space-y-8">
                    <!-- Info Summary Row -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="md:col-span-2 space-y-4">
                            <div class="flex items-center gap-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-3xl border border-blue-100 dark:border-blue-900/30">
                                <div class="w-16 h-16 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center text-2xl">👨‍🏫</div>
                                <div>
                                    <h3 id="modalTeacherName" class="text-xl font-black text-blue-700 dark:text-blue-400">Name Loading...</h3>
                                    <p id="modalSubjectInfo" class="text-sm font-bold text-slate-600 dark:text-slate-400">Subject Info</p>
                                    <p id="modalDateInfo" class="text-xs text-slate-400 mt-1">Date Info</p>
                                </div>
                            </div>
                            
                            <div id="teacherEvaluationSummary">
                                <!-- JS Populated -->
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div id="teacherDocuments" class="h-full glass rounded-3xl p-6 border border-slate-100 overflow-y-auto">
                                <!-- JS Populated Docs -->
                            </div>
                        </div>
                    </div>

                    <!-- Scoring Sections -->
                    <div class="space-y-12">
                        <?php
                        $sections = [
                            ['title' => 'ด้านที่ 1 การเตรียมการสอน / แผนการจัดการเรียนรู้', 'icon' => 'fa-pencil-ruler', 'color' => 'blue', 'items' => [
                                'dept_plan_effective' => 'แผนการจัดการเรียนรู้สอดคล้องกับตัวชี้วัด/ผลการเรียนรู้',
                                'dept_plan_correct' => 'องค์ประกอบของแผนการจัดการเรียนรู้ถูกต้องครบถ้วน',
                                'dept_plan_activities' => 'การออกแบบกิจกรรมการจัดการเรียนรู้เน้นผู้เรียนเป็นสำคัญ',
                                'dept_plan_media' => 'มีการใช้สื่อหรือแหล่งเรียนรู้ที่หลากหลายสอดคล้องกับกิจกรรม',
                                'dept_plan_assessment' => 'มีวิธีการวัดผลและประเมินผลที่หลากหลายและชัดเจน'
                            ]],
                            ['title' => 'ด้านที่ 2 การจัดการเรียนรู้ทางการสอน', 'icon' => 'fa-graduation-cap', 'color' => 'emerald', 'items' => [
                                'dept_teach_techniques' => 'เทคนิคและวิธีการสอนที่ใช้มีความหลากหลายและน่าสนใจ',
                                'dept_teach_media' => 'การใช้สื่อและเทคโนโลยีส่งเสริมการเรียนรู้ได้อย่างเหมาะสม',
                                'dept_teach_assessment' => 'มีการวัดและประเมินผลระหว่างเรียนสอดคล้องกับจุดประสงค์',
                                'dept_teach_explanation' => 'การอธิบายเนื้อหาและสาธิตมีความชัดเจน เข้าใจง่าย',
                                'dept_teach_control' => 'การบริหารจัดการชั้นเรียนสร้างบรรยากาศที่ส่งเสริมการเรียนรู้',
                                'dept_teach_thinking' => 'กิจกรรมส่งเสริมทักษะการคิดวิเคราะห์และสร้างสรรค์',
                                'dept_teach_adaptation' => 'มีการปรับใช้แนวคิดเชิงนวัตกรรมในการจัดการเรียนการสอน',
                                'dept_teach_integration' => 'มีการบูรณาการเนื้อหาและทักษะที่เกี่ยวข้องเข้าด้วยกัน',
                                'dept_teach_language' => 'การใช้ภาษาและน้ำเสียงในการสื่อสารมีความเหมาะสม'
                            ]],
                            ['title' => 'ด้านที่ 3 การวัดผลและประเมินผล', 'icon' => 'fa-check-circle', 'color' => 'purple', 'items' => [
                                'dept_eval_variety' => 'ใช้วิธีการวัดผลที่หลากหลายตามสภาพจริง',
                                'dept_eval_standards' => 'การประเมินผลสอดคล้องกับมาตรฐานการเรียนรู้',
                                'dept_eval_criteria' => 'มีเกณฑ์การให้คะแนน (Rubrics) ที่ชัดเจน',
                                'dept_eval_feedback' => 'มีการแจ้งผลและให้คำแนะนำแก่ผู้เรียนสม่ำเสมอ',
                                'dept_eval_evidence' => 'มีร่องรอยหลักฐานการประเมินผลที่เป็นระบบ'
                            ]],
                            ['title' => 'ด้านที่ 4 สภาพแวดล้อมและบรรยากาศในห้องเรียน', 'icon' => 'fa-leaf', 'color' => 'orange', 'items' => [
                                'dept_env_classroom' => 'ห้องเรียนมีความสะอาด ระเบียบเรียบร้อย และปลอดภัย',
                                'dept_env_interaction' => 'ปฏิสัมพันธ์ระหว่างครูและนักเรียนเป็นเชิงบวก',
                                'dept_env_safety' => 'คำนึงถึงความปลอดภัยของนักเรียนระหว่างทำกิจกรรม',
                                'dept_env_management' => 'การจัดที่นั่งและอุปกรณ์ส่งเสริมการทำงานร่วมกัน',
                                'dept_env_rules' => 'มีการตกลงแนวทางและระเบียบวินัยในห้องเรียนที่ชัดเจน',
                                'dept_env_behavior' => 'การควบคุมพฤติกรรมใช้เทคนิคเชิงบวกและเข้าใจผู้เรียน'
                            ]]
                        ];

                        foreach ($sections as $section):
                        ?>
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-<?php echo $section['color']; ?>-500 rounded-xl flex items-center justify-center text-white shadow-lg">
                                    <i class="fas <?php echo $section['icon']; ?>"></i>
                                </div>
                                <h4 class="text-lg font-black text-slate-800 dark:text-white"><?php echo $section['title']; ?></h4>
                            </div>
                            
                            <div class="bg-slate-50 dark:bg-slate-800/30 rounded-[2rem] p-6 space-y-4">
                                <?php foreach ($section['items'] as $name => $label): ?>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 hover:border-<?php echo $section['color']; ?>-300 transition-colors shadow-sm">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300"><?php echo $label; ?></span>
                                    <div class="flex gap-2">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <label class="flex shrink-0">
                                            <input type="radio" name="<?php echo $name; ?>" value="<?php echo $i; ?>" class="score-radio">
                                            <span class="score-label"><?php echo $i; ?></span>
                                        </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Final Comments & Summary -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-8 border-t border-slate-100">
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 mb-2 block">หมายเหตุ / ผลการสังเกตเพิ่มเติม</label>
                                <textarea name="dept_observation_notes" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl p-4 focus:border-blue-500 transition-all font-medium text-sm" placeholder="ระบุสิ่งที่พบเห็นเพิ่มเติม..."></textarea>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-black text-emerald-500 uppercase tracking-widest ml-2 mb-2 block">จุดเด่นที่ควรชื่นชม</label>
                                    <textarea name="dept_strengths" rows="3" class="w-full bg-emerald-50/30 dark:bg-emerald-900/10 border-2 border-emerald-100/50 rounded-2xl p-4 focus:border-emerald-500 transition-all font-medium text-sm" placeholder="สิ่งที่ครูทำได้ดีเยี่ยม..."></textarea>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-amber-500 uppercase tracking-widest ml-2 mb-2 block">สิ่งที่ควรพัฒนา/ข้อค้นพบ</label>
                                    <textarea name="dept_suggestion" rows="3" class="w-full bg-amber-50/30 dark:bg-amber-900/10 border-2 border-amber-100/50 rounded-2xl p-4 focus:border-amber-500 transition-all font-medium text-sm" placeholder="จุดที่สามารถพัฒนาต่อได้..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col justify-between p-8 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[2.5rem] text-white shadow-xl shadow-blue-500/20">
                            <div>
                                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                                    <i class="fas fa-calculator opacity-60"></i> สรุปผลการประเมิน
                                </h3>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-white/10 p-4 rounded-3xl border border-white/20">
                                        <p class="text-[10px] font-black opacity-60 uppercase mb-1">คะแนนรวม</p>
                                        <input type="text" id="deptScore" name="dept_score" readonly class="bg-transparent text-3xl font-black w-full focus:outline-none" value="0">
                                    </div>
                                    <div class="bg-white/10 p-4 rounded-3xl border border-white/20">
                                        <p class="text-[10px] font-black opacity-60 uppercase mb-1">ระดับคุณภาพ</p>
                                        <input type="text" id="deptQualityLevel" name="dept_quality_level" readonly class="bg-transparent text-2xl font-black w-full focus:outline-none" value="-">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                                <button type="button" id="calculateDeptScore" class="flex-1 px-6 py-4 bg-white text-blue-700 font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fas fa-sync"></i> คำนวณคะแนน
                                </button>
                                <button type="submit" class="flex-1 px-6 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2 border border-emerald-400">
                                    <i class="fas fa-save"></i> บันทึกประเมิน
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-8 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 flex justify-end">
                <button id="cancelSupervision" type="button" class="px-8 py-3 bg-white dark:bg-slate-700 text-slate-500 font-bold rounded-2xl hover:bg-slate-100 transition-all border border-slate-200">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>

<script src="js/department-supervision.js"></script>
<script>
    window.manager = new DepartmentSupervisionManager({
        department: '<?php echo $department; ?>'
    });
</script>
