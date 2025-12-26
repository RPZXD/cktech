<?php
/**
 * Admin Settings View
 * MVC Pattern - View for system settings
 * Enhanced UI/UX with Tailwind CSS - Mobile Responsive
 */

// Load current config
$configPath = __DIR__ . '/../../config.json';
$currentConfig = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
?>

<style>
    /* Floating Animation */
    .float-animation { animation: floating 3s ease-in-out infinite; }
    @keyframes floating { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    
    /* Setting card hover */
    .setting-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .setting-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
</style>

<!-- Page Header -->
<div class="mb-6 md:mb-8">
    <div class="relative glass rounded-2xl md:rounded-3xl p-5 md:p-8 shadow-xl overflow-hidden">
        <div class="absolute -top-20 -right-20 w-40 h-40 md:w-60 md:h-60 bg-gradient-to-br from-pink-400/20 to-rose-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-3xl md:text-4xl float-animation">⚙️</span>
                    <span class="px-3 py-1 bg-pink-500 text-white text-xs font-bold rounded-full uppercase tracking-wider">System Settings</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                    ตั้งค่าระบบ
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm md:text-base">
                    ปรับแต่งการตั้งค่าและข้อมูลพื้นฐานของระบบ
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Settings Sections -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- General Settings -->
    <div class="setting-card glass rounded-2xl shadow-lg overflow-hidden">
        <div class="p-4 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-blue-500/10 to-indigo-500/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                    <i class="fas fa-globe text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white">ข้อมูลทั่วไป</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">ตั้งค่าข้อมูลพื้นฐานของโรงเรียน</p>
                </div>
            </div>
        </div>
        <div class="p-4 md:p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ชื่อโรงเรียน</label>
                <input type="text" id="setting-school-name" value="<?php echo htmlspecialchars($currentConfig['global']['nameschool'] ?? ''); ?>" 
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ชื่อระบบ</label>
                <input type="text" id="setting-system-name" value="<?php echo htmlspecialchars($currentConfig['global']['nameTitle'] ?? ''); ?>" 
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ชื่อหน้า (Page Title)</label>
                <input type="text" id="setting-page-title" value="<?php echo htmlspecialchars($currentConfig['global']['pageTitle'] ?? ''); ?>" 
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ไฟล์โลโก้</label>
                <input type="text" id="setting-logo" value="<?php echo htmlspecialchars($currentConfig['global']['logoLink'] ?? ''); ?>" 
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>
    </div>
    
    <!-- Academic Settings -->
    <div class="setting-card glass rounded-2xl shadow-lg overflow-hidden">
        <div class="p-4 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-emerald-500/10 to-green-500/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg">
                    <i class="fas fa-graduation-cap text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white">ข้อมูลวิชาการ</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">ตั้งค่าปีการศึกษาและภาคเรียน</p>
                </div>
            </div>
        </div>
        <div class="p-4 md:p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ปีการศึกษา</label>
                <select id="setting-academic-year" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                    <?php 
                    $currentYear = (int)date('Y') + 543;
                    $selectedYear = $currentConfig['academic']['year'] ?? $currentYear;
                    for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++): 
                    ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $selectedYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ภาคเรียน</label>
                <select id="setting-semester" class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                    <option value="1" <?php echo ($currentConfig['academic']['semester'] ?? 1) == 1 ? 'selected' : ''; ?>>ภาคเรียนที่ 1</option>
                    <option value="2" <?php echo ($currentConfig['academic']['semester'] ?? 1) == 2 ? 'selected' : ''; ?>>ภาคเรียนที่ 2</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">จำนวนคาบต่อวัน</label>
                <input type="number" id="setting-periods-per-day" value="<?php echo htmlspecialchars($currentConfig['academic']['periodsPerDay'] ?? 9); ?>" min="1" max="12"
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>
    </div>
    
    <!-- System Settings -->
    <div class="setting-card glass rounded-2xl shadow-lg overflow-hidden">
        <div class="p-4 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-purple-500/10 to-violet-500/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl shadow-lg">
                    <i class="fas fa-cogs text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white">ตั้งค่าระบบ</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">ปรับแต่งการทำงานของระบบ</p>
                </div>
            </div>
        </div>
        <div class="p-4 md:p-6 space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                <div>
                    <p class="font-medium text-gray-800 dark:text-white">เปิดใช้งาน Dark Mode</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">อนุญาตให้เปลี่ยนธีมมืด</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting-dark-mode" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                </label>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                <div>
                    <p class="font-medium text-gray-800 dark:text-white">แจ้งเตือนอัตโนมัติ</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">ส่งการแจ้งเตือนเมื่อมีรายงานใหม่</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting-notifications" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                </label>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                <div>
                    <p class="font-medium text-gray-800 dark:text-white">โหมดบำรุงรักษา</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">ปิดการใช้งานชั่วคราว</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting-maintenance" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Security Settings -->
    <div class="setting-card glass rounded-2xl shadow-lg overflow-hidden">
        <div class="p-4 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-red-500/10 to-rose-500/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-red-500 to-rose-600 rounded-xl shadow-lg">
                    <i class="fas fa-shield-alt text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white">ความปลอดภัย</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">ตั้งค่าความปลอดภัยของระบบ</p>
                </div>
            </div>
        </div>
        <div class="p-4 md:p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">รหัสผ่านเริ่มต้น (สำหรับรีเซ็ต)</label>
                <input type="password" id="setting-default-password" value="123456" 
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 bg-white dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">หมดเวลา Session (นาที)</label>
                <input type="number" id="setting-session-timeout" value="60" min="5" max="480"
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 bg-white dark:bg-gray-700 dark:text-gray-100">
            </div>
            <button id="btn-clear-cache" class="w-full px-4 py-3 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-xl font-medium transition-all flex items-center justify-center gap-2">
                <i class="fas fa-broom"></i>
                <span>ล้างแคชระบบ</span>
            </button>
        </div>
    </div>
</div>

<!-- Save Button -->
<div class="flex justify-end gap-3">
    <button id="btn-reset-settings" class="px-6 py-3 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-xl font-medium transition-all flex items-center gap-2">
        <i class="fas fa-undo"></i>
        <span>รีเซ็ตค่าเริ่มต้น</span>
    </button>
    <button id="btn-save-settings" class="px-6 py-3 bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
        <i class="fas fa-save"></i>
        <span>บันทึกการตั้งค่า</span>
    </button>
</div>

<!-- External JS -->
<script src="js/admin-settings.js"></script>
