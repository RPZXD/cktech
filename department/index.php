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
                        <h2 class="text-2xl font-bold text-blue-700 mb-2">ระบบสำหรับหัวหน้ากลุ่มสาระ</h2>
                        <p class="text-gray-700 mb-4">
                            หน้านี้สำหรับหัวหน้ากลุ่มสาระ เพื่อดูภาพรวมการสอน ตรวจสอบรายงาน และวิเคราะห์ข้อมูลการสอนของครูในกลุ่มสาระ
                        </p>
                        <ul class="text-left text-gray-600 space-y-2 mb-4">
                            <li>📑 <span class="font-medium">ตรวจสอบรายงานการสอน</span> ของครูในกลุ่มสาระ</li>
                            <li>📊 <span class="font-medium">ดูสถิติและวิเคราะห์ข้อมูล</span> การสอน</li>
                        </ul>
                        <div class="flex flex-wrap gap-3 justify-center">
                            <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">ยินดีต้อนรับ หัวหน้ากลุ่มสาระ<?=$department?></span>
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
