<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'หัวหน้ากลุ่มสาระ') {
    header('Location: ../login.php');
    exit;
}
// โหลด config
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
$department = $_SESSION['user']['Teach_major'];
require_once('header.php');
?>
<body class="hold-transition sidebar-mini layout-fixed light-mode bg-gray-50">
<div class="wrapper">
    <?php require_once('wrapper.php'); ?>
    <div class="content-wrapper bg-gray-50">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-2xl font-bold text-blue-700 flex items-center">
                            🏫 หน้าหลักหัวหน้ากลุ่มสาระ
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container mx-auto py-8 flex justify-center">
                <div class="max-w-2xl w-full">
                    <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center text-center border border-blue-100">
                        <div class="text-5xl mb-4">👩‍🏫📊</div>
                        <h2 class="text-2xl font-bold text-blue-700 mb-2">ระบบหัวหน้ากลุ่มสาระ</h2>
                        <p class="text-gray-700 mb-4">
                            ยินดีต้อนรับ <span class="font-semibold text-blue-700">หัวหน้ากลุ่มสาระ <?=$department?></span>
                        </p>
                        <div class="flex flex-col gap-4 w-full">
                            <a href="weekly_report.php" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg shadow flex items-center justify-center gap-2 text-lg font-semibold transition">
                                📅 สรุปรายงานการสอนรายสัปดาห์
                            </a>
                            <a href="report.php" class="w-full bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg shadow flex items-center justify-center gap-2 text-lg font-semibold transition">
                                📑 ตรวจสอบรายงานการสอนรายวัน
                            </a>
                            <a href="stat.php" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-lg shadow flex items-center justify-center gap-2 text-lg font-semibold transition">
                                📊 สถิติและวิเคราะห์ข้อมูล
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<?php require_once('script.php'); ?>
</body>
</html>
