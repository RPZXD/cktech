<?php
session_start();

// // เพิ่ม error reporting สำหรับ debug (ลบออกใน production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// โหลด config
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$pageConfig = $config['global'];

// เพิ่ม: เรียกใช้ LoginController
require_once __DIR__ . '/controllers/LoginController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];
    $input_role = $_POST['role'];

    $controller = new LoginController();
    $error = $controller->login($input_username, $input_password, $input_role);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="dist/img/logo-phicha.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageConfig['pageTitle']); ?> | <?php echo htmlspecialchars($pageConfig['nameschool']); ?></title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($pageConfig['logoLink']); ?>" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Google Font: Mali -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 font-sans relative overflow-hidden" style="font-family: 'Mali', sans-serif;">
    <!-- Floating decorative elements (hidden on small screens) -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="hidden sm:block absolute top-10 left-10 text-4xl motion-safe:animate-bounce opacity-20" aria-hidden="true">🔐</div>
        <div class="hidden sm:block absolute top-20 right-20 text-3xl motion-safe:animate-pulse opacity-30" aria-hidden="true">👤</div>
        <div class="hidden md:block absolute bottom-20 left-20 text-4xl motion-safe:animate-bounce opacity-25" style="animation-delay: 1s;" aria-hidden="true">📚</div>
        <div class="hidden md:block absolute bottom-10 right-10 text-3xl motion-safe:animate-pulse opacity-20" style="animation-delay: 2s;" aria-hidden="true">🎓</div>
        <div class="hidden lg:block absolute top-1/2 left-1/4 text-2xl motion-safe:animate-bounce opacity-15" style="animation-delay: 0.5s;" aria-hidden="true">✨</div>
        <div class="hidden lg:block absolute top-1/3 right-1/4 text-2xl motion-safe:animate-pulse opacity-25" style="animation-delay: 1.5s;" aria-hidden="true">🌟</div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="min-h-screen flex items-center justify-center relative z-10">
        <div class="bg-white backdrop-blur-lg rounded-3xl shadow-2xl p-6 sm:p-8 md:p-10 w-full max-w-md sm:max-w-lg border border-white/20 hover:shadow-3xl transition-all duration-500 transform hover:scale-105" data-aos="fade-up">
            <div class="flex flex-col items-center mb-6">
                <?php if (!empty($pageConfig['logoLink'])): ?>
                    <?php
                    // ตรวจสอบว่า logoLink เป็น path หรือแค่ชื่อไฟล์
                    $logoSrc = (strpos($pageConfig['logoLink'], '/') === false && strpos($pageConfig['logoLink'], '\\') === false)
                        ? 'dist/img/' . htmlspecialchars($pageConfig['logoLink'])
                        : htmlspecialchars($pageConfig['logoLink']);
                    ?>
                    <img src="<?php echo $logoSrc; ?>" alt="logo" class="h-16 w-16 mb-4 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 p-2 shadow-lg motion-safe:animate-pulse object-contain" />
                <?php endif; ?>
                <span class="text-indigo-700 font-bold text-xl bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent" id="login-school"><?php echo htmlspecialchars($pageConfig['nameschool']); ?></span>
            </div>
            <h2 class="text-4xl font-bold text-center text-indigo-600 mb-8 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent animate-pulse" id="login-title"><?php echo htmlspecialchars($pageConfig['pageTitle']); ?> 🌟</h2>

            <?php if (isset($error) && $error !== 'success') { ?>
                <div class="w-full mb-4">
                    <div class="rounded-md bg-red-50 p-3 border border-red-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/></svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">เข้าสู่ระบบไม่สำเร็จ</p>
                                <p class="mt-1 text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <form action="login.php" method="POST" role="form" aria-labelledby="login-title" novalidate>
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ใช้</label>
                    <input type="text" name="username" id="username" class="mt-1 p-3 w-full border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 transition-colors duration-200" placeholder="กรอกชื่อผู้ใช้" required aria-required="true" autocomplete="username" inputmode="text" autocapitalize="off" autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="mt-1 p-3 w-full border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 transition-colors duration-200 pr-12" placeholder="กรอกรหัสผ่าน" required aria-required="true" aria-describedby="togglePassword" autocomplete="current-password">
                        <button type="button" id="togglePassword" tabindex="0"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300 rounded" aria-label="แสดง/ซ่อนรหัสผ่าน" aria-pressed="false">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0c0 2.21-3.582 6-9 6s-9-3.79-9-6z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">เลือกบทบาท</label>
                    <div class="relative">
                        <select name="role" id="role" class="mt-1 p-3 w-full border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 transition-colors duration-200 bg-white appearance-none pr-10" required>
                            <option value="">-- เลือกบทบาท --</option>
                            <option value="ครู" selected>ครู</option>
                            <option value="หัวหน้ากลุ่มสาระ">หัวหน้ากลุ่มสาระ</option>
                            <option value="ผู้บริหาร">ผู้บริหาร</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400"><svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input type="checkbox" name="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <span class="ml-2">จดจำฉัน</span>
                    </label>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-800">ลืมรหัสผ่าน?</a>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg text-lg font-semibold hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg transition-transform duration-200 transform active:scale-95">เข้าสู่ระบบ</button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">ยังไม่มีบัญชี? <a href="#" class="text-blue-500 hover:underline">ให้ติดต่อผู้ดูแลระบบ</a></p>
            </div>
        </div>
    </div>

    <footer class="w-full text-center text-white text-sm mt-8 mb-2 bg-white/10 backdrop-blur-sm rounded-t-lg py-4 px-6">
        <p>&copy; <?=date('Y')?> <?php echo htmlspecialchars($pageConfig['nameschool']); ?>. All rights reserved. | <?php echo htmlspecialchars($pageConfig['footerCredit']); ?> ❤️</p>
    </footer>

    <!-- AOS (Animate On Scroll) script initialization -->
    <script>
        AOS.init({
            duration: 1200,  // Time of animation
            easing: 'ease-out-back',  // Easing function for smooth transition
        });
    </script>

    <!-- sweetalert2 script initialization -->
    <script>
    // Show SweetAlert2 on successful logout
    <?php if (isset($_GET['logout']) && $_GET['logout'] == '1') { ?>
        Swal.fire({
            icon: 'success',
            title: 'ออกจากระบบสำเร็จ',
            text: 'คุณได้ออกจากระบบเรียบร้อยแล้ว',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#3085d6'
        });
    <?php } ?>

    // Show SweetAlert2 on successful login (redirect after login)
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($error) && $error === 'success') { ?>
        Swal.fire({
            icon: 'success',
            title: 'เข้าสู่ระบบสำเร็จ',
            text: 'กำลังเข้าสู่ระบบ...',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            // Redirect by role
            <?php
            $redirect = 'dashboard.php';
            if (isset($_POST['role']) && $_POST['role'] === 'ครู') {
                $redirect = 'teacher/index.php';
            } else if (isset($_POST['role']) && $_POST['role'] === 'หัวหน้ากลุ่มสาระ') {
                $redirect = 'department/index.php';
            } else if (isset($_POST['role']) && $_POST['role'] === 'ผู้บริหาร') {
                $redirect = 'director/index.php';
            } else if (isset($_POST['role']) && $_POST['role'] === 'admin') {
                $redirect = 'admin/index.php';
            }
            ?>
            window.location.href = <?= json_encode($redirect) ?>;
        });
    <?php } ?>
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        let show = false;
        toggleBtn.addEventListener('click', function () {
            show = !show;
            passwordInput.type = show ? 'text' : 'password';
            toggleBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
            eyeIcon.innerHTML = show
                ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-5.418 0-9-3.79-9-6a9 9 0 0115.584-5.991M15 12a3 3 0 11-6 0 3 3 0 016 0zm6.121 6.121l-18-18" />`
                : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0c0 2.21-3.582 6-9 6s-9-3.79-9-6z" />`;
        });
    });
    </script>

</body>
</html>
