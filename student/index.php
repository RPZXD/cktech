<?php 
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'นักเรียน') {
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];

// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];

// ดึงค่า term และ pee จาก session
$term = isset($_SESSION['term']) ? $_SESSION['term'] : '-';
$pee = isset($_SESSION['pee']) ? $_SESSION['pee'] : '-';

require_once('header.php');

?>
<body class="hold-transition sidebar-mini layout-fixed light-mode">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Student Dashboard</h1>
                        <!-- แสดงปีการศึกษาและภาคเรียน -->
                        <div class="mt-2 text-gray-700 text-base">
                            ปีการศึกษา: <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($pee); ?></span>
                            | ภาคเรียน: <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($term); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <!-- เนื้อหาสำหรับนักเรียน -->
                <div class="alert alert-success">
                    สวัสดีนักเรียน <?php echo $user['Stu_name'] . ' ' . $user['Stu_sur']; ?> ยินดีต้อนรับเข้าสู่ระบบ
                </div>
                <!-- ขั้นตอนการลงทะเบียนกิจกรรม -->
                <div class="mt-6 max-w-2xl mx-auto bg-white rounded-lg shadow p-6 border border-blue-200">
                    <h2 class="text-lg font-bold text-blue-700 mb-4 flex items-center gap-2">
                        📝 ขั้นตอนการลงทะเบียนกิจกรรม (สำหรับนักเรียน)
                    </h2>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 text-lg">1️⃣</span>
                            <span>
                                <b>เข้าสู่ระบบในฐานะนักเรียน</b> ผ่านหน้าเข้าสู่ระบบของระบบกิจกรรม
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-500 text-lg">2️⃣</span>
                            <span>
                                <b>ไปที่เมนู <a href="event_regis.php" class="font-semibold text-blue-600">"ลงทะเบียนกิจกรรมกิจกรรม"</a></b> เพื่อดูรายการกิจกรรมที่เปิดลงทะเบียน
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-yellow-500 text-lg">3️⃣</span>
                            <span>
                                <b>เลือกกิจกรรมที่ต้องการ</b> แล้วกดปุ่ม <span class="font-semibold text-blue-600">"ลงทะเบียน"</span> ข้างกิจกรรมนั้น
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500 text-lg">4️⃣</span>
                            <span>
                                <b>กรอกรหัสกิจกรรม (Code)</b> ที่ได้รับจากครูหรือ QR Code ของกิจกรรม แล้วกดยืนยัน
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-pink-500 text-lg">5️⃣</span>
                            <span>
                                <b>ตรวจสอบสถานะ</b> หากลงทะเบียนสำเร็จ จะมีข้อความแจ้งเตือนและสถานะ "ลงทะเบียนแล้ว" ในตาราง
                            </span>
                        </li>
                    </ol>
                    <div class="mt-4 text-blue-600 flex items-center gap-2">
                        <span>💡</span>
                        <span>
                            <b>หมายเหตุ:</b> 1 กิจกรรมใช้ได้ 1 code ต่อ 1 คน หากมีปัญหาในการลงทะเบียน กรุณาติดต่อครูผู้ดูแลกิจกรรม
                        </span>
                    </div>
                </div>
            </div>
        </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
    <?php require_once('../footer.php');?>
</div>
<!-- ./wrapper -->


<script>

</script>
<?php require_once('script.php');?>
</body>
</html>
