<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
// โหลด config
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];
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
                            🛡️ หน้าหลักผู้ดูแลระบบ (Admin)
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container mx-auto py-8 flex justify-center">
                <div class="max-w-2xl w-full">
                    <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center text-center border border-blue-100">
                        <div class="text-5xl mb-4">🛡️👨‍💻</div>
                        <h2 class="text-2xl font-bold text-blue-700 mb-2">แดชบอร์ดผู้ดูแลระบบ</h2>
                        <p class="text-gray-700 mb-4">
                            ยินดีต้อนรับ <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($_SESSION['user']['Teach_name'] ?? $_SESSION['username']); ?></span> สู่ระบบจัดการข้อมูล<br>
                            คุณสามารถจัดการผู้ใช้ ดูรายงาน และตั้งค่าระบบได้ที่นี่
                        </p>
                        <ul class="text-left text-gray-600 space-y-2 mb-4">
                            <li>👤 <span class="font-medium">จัดการผู้ใช้</span> (ครู/เจ้าหน้าที่/หัวหน้ากลุ่มสาระ/ผู้บริหาร)</li>
                            <li>📑 <span class="font-medium">ดูรายงานการสอน</span> และสถิติ</li>
                            <li>⚙️ <span class="font-medium">ตั้งค่าระบบ</span> และข้อมูลพื้นฐาน</li>
                        </ul>
                        <div class="flex flex-wrap gap-3 justify-center">
                            <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                                ผู้ดูแลระบบ: <?php echo htmlspecialchars($_SESSION['user']['Teach_name'] ?? $_SESSION['username']); ?>
                            </span>
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
