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
                            <div class="w-full bg-blue-50 border-l-4 border-blue-400 rounded p-4 text-left mb-2">
                                <div class="font-bold text-blue-700 text-lg flex items-center gap-2 mb-1">📅 สรุปรายงานการสอนรายสัปดาห์</div>
                                <div class="text-gray-700">ดูภาพรวมการส่งรายงานการสอนของครูในกลุ่มสาระของคุณในแต่ละสัปดาห์ พร้อมสถิติและกราฟประกอบ</div>
                            </div>
                            <div class="w-full bg-green-50 border-l-4 border-green-400 rounded p-4 text-left mb-2">
                                <div class="font-bold text-green-700 text-lg flex items-center gap-2 mb-1">📑 ตรวจสอบรายงานการสอนรายวัน</div>
                                <div class="text-gray-700">ตรวจสอบและเข้าดูรายละเอียดรายงานการสอนของครูแต่ละคนในกลุ่มสาระของคุณแบบรายวัน ทั้งในรูปแบบตารางและปฏิทิน</div>
                            </div>
                            <div class="w-full bg-indigo-50 border-l-4 border-indigo-400 rounded p-4 text-left">
                                <div class="font-bold text-indigo-700 text-lg flex items-center gap-2 mb-1">📊 สถิติและวิเคราะห์ข้อมูล</div>
                                <div class="text-gray-700">ดูสถิติการส่งรายงาน ขาดเรียน ลาป่วย ลากิจ และกิจกรรมต่าง ๆ ของครูในกลุ่มสาระ พร้อมสรุปรายเดือนและกราฟวิเคราะห์</div>
                            </div>
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
