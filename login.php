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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageConfig['pageTitle']); ?></title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($pageConfig['logoLink']); ?>" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Google Font: Mali -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-600 font-sans" style="font-family: 'Mali', sans-serif;">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md" data-aos="fade-up">
            <div class="flex flex-col items-center mb-4">
                <?php if (!empty($pageConfig['logoLink'])): ?>
                    <?php
                    // ตรวจสอบว่า logoLink เป็น path หรือแค่ชื่อไฟล์
                    $logoSrc = (strpos($pageConfig['logoLink'], '/') === false && strpos($pageConfig['logoLink'], '\\') === false)
                        ? 'dist/img/' . htmlspecialchars($pageConfig['logoLink'])
                        : htmlspecialchars($pageConfig['logoLink']);
                    ?>
                    <img src="<?php echo $logoSrc; ?>" alt="logo" class="h-14 w-14 mb-2 rounded-full bg-white p-1 shadow" />
                <?php endif; ?>
                <span class="text-blue-700 font-bold text-lg"><?php echo htmlspecialchars($pageConfig['nameschool']); ?></span>
            </div>
            <h2 class="text-3xl font-bold text-center text-blue-600 mb-6"><?php echo htmlspecialchars($pageConfig['pageTitle']); ?> 🌟</h2>

            <?php if (isset($error) && $error !== 'success') { ?>
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'เข้าสู่ระบบไม่สำเร็จ',
                    text: <?= json_encode($error) ?>,
                    confirmButtonText: 'ปิด',
                    confirmButtonColor: '#3085d6'
                });
                </script>
            <?php } ?>

            <form action="login.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-lg font-medium text-gray-700">ชื่อผู้ใช้ 👤</label>
                    <input type="text" name="username" id="username" class="mt-1 p-3 w-full border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="กรอกชื่อผู้ใช้" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-lg font-medium text-gray-700">รหัสผ่าน 🔒</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="mt-1 p-3 w-full border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-12" placeholder="กรอกรหัสผ่าน" required>
                        <button type="button" id="togglePassword" tabindex="-1"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 focus:outline-none"
                            aria-label="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0c0 2.21-3.582 6-9 6s-9-3.79-9-6z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-lg font-medium text-gray-700">เลือกบทบาท 🛡️</label>
                    <select name="role" id="role" class="mt-1 p-3 w-full border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- เลือกบทบาท --</option>
                        <option value="ครู" selected>ครู</option>
                        <option value="เจ้าหน้าที่">เจ้าหน้าที่</option>
                        <option value="หัวหน้ากลุ่มสาระ">หัวหน้ากลุ่มสาระ</option>
                        <option value="ผู้บริหาร">ผู้บริหาร</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg text-xl font-semibold hover:bg-blue-700 transition duration-300 transform hover:scale-105">เข้าสู่ระบบ</button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">ยังไม่มีบัญชี? <a href="#" class="text-blue-500 hover:underline">ให้ติดต่อผู้ดูแลระบบ</a></p>
            </div>
        </div>
    </div>

    <footer class="w-full text-center text-white text-xs mt-8 mb-2">
        <p>&copy; <?=date('Y')?> <?php echo htmlspecialchars($pageConfig['nameschool']); ?>. All rights reserved. | <?php echo htmlspecialchars($pageConfig['footerCredit']); ?></p>
    </footer>

    <!-- AOS (Animate On Scroll) script initialization -->
    <script>
        AOS.init({
            duration: 1200,  // Time of animation
            easing: 'ease-out-back',  // Easing function for smooth transition
        });
    </script>

    <!-- sweetalert2 script initialization -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
