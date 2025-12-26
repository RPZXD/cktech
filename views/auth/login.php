<?php
/**
 * Login View
 * MVC Pattern - View for login page
 * Variables: $global, $error, $success, $redirect, $logoutMessage
 */
?>

<!-- SweetAlert for logout/login messages -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($logoutMessage ?? false): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'ออกจากระบบสำเร็จ',
        text: 'คุณได้ออกจากระบบเรียบร้อยแล้ว',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#3b82f6'
    });
});
</script>
<?php endif; ?>

<?php if ($success ?? false): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'เข้าสู่ระบบสำเร็จ',
        text: 'กำลังเข้าสู่ระบบ...',
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        window.location.href = <?php echo json_encode($redirect ?? 'index.php'); ?>;
    });
});
</script>
<?php endif; ?>

<!-- Login View -->
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo & Header -->
        <div class="text-center animate-fade-in">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-primary-500 to-indigo-600 rounded-2xl shadow-xl shadow-primary-500/30 mb-6 animate-bounce-slow">
                <i class="fas fa-user-shield text-4xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold gradient-text">เข้าสู่ระบบ</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($global['nameschool'] ?? 'โรงเรียน'); ?></p>
        </div>

        <!-- Login Form Card -->
        <div class="glass rounded-2xl p-8 shadow-xl animate-slide-up">
            <?php if (!empty($error)): ?>
            <!-- Error Message -->
            <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">เข้าสู่ระบบไม่สำเร็จ</p>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-user mr-2 text-primary-500"></i>ชื่อผู้ใช้งาน
                    </label>
                    <input type="text" name="username" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                           placeholder="กรุณากรอกชื่อผู้ใช้งาน" autocomplete="username" autofocus>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-lock mr-2 text-primary-500"></i>รหัสผ่าน
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-12"
                               placeholder="กรุณากรอกรหัสผ่าน" autocomplete="current-password">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-id-badge mr-2 text-primary-500"></i>เลือกบทบาท
                    </label>
                    <select name="role" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">-- เลือกบทบาท --</option>
                        <option value="ครู" selected>👨‍🏫 ครู</option>
                        <option value="หัวหน้ากลุ่มสาระ">📋 หัวหน้ากลุ่มสาระ</option>
                        <option value="ผู้บริหาร">👔 ผู้บริหาร</option>
                        <option value="admin">🛠️ Admin</option>
                    </select>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        <span class="ml-2">จดจำฉัน</span>
                    </label>
                    <a href="#" class="text-sm text-primary-500 hover:text-primary-600 font-medium">ลืมรหัสผ่าน?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-4 px-6 bg-gradient-to-r from-primary-500 to-indigo-600 hover:from-primary-600 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/50 transition-all transform hover:-translate-y-1 active:scale-95">
                    <i class="fas fa-sign-in-alt mr-2"></i>เข้าสู่ระบบ
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white/80 dark:bg-slate-800/80 text-gray-500 dark:text-gray-400 backdrop-blur">หรือ</span>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>ยังไม่มีบัญชี? <a href="#" class="text-primary-500 hover:text-primary-600 font-medium">ติดต่อผู้ดูแลระบบ</a></p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="index.php" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors group">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                <span>กลับหน้าหลัก</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center text-gray-400 text-xs">
            <span class="mr-1">📚</span> <?php echo htmlspecialchars($global['pageTitle'] ?? 'Vichakan System'); ?> <span class="ml-1">🎓</span>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var passwordInput = document.getElementById('password');
    var toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
