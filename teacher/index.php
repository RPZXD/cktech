<?php 
session_start();
// เช็ค session และ role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ครู') {
    header('Location: ../login.php');
    exit;
}
// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];

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
            <h1 class="m-0"><?php echo $global['nameschool']; ?> <span class="text-blue-600">| ครู</span></h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <section class="content">
      <div class="container mx-auto max-w-3xl mt-10">
        <div class="bg-white rounded-xl shadow-lg p-8 border border-blue-100">
          <h2 class="text-2xl font-bold text-blue-700 mb-4 flex items-center gap-2">👩‍🏫 คู่มือการใช้งานสำหรับครู</h2>
          <div class="space-y-6 text-base text-gray-700">
            <div class="flex items-start gap-3">
              <span class="text-3xl">📚</span>
              <div>
                <span class="font-semibold text-blue-600">จัดการรายวิชา</span> <br>
                ไปที่ <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">เมนู "จัดการรายวิชา"</span> เพื่อเพิ่ม แก้ไข หรือลบรายวิชาที่คุณสอน <br>
                <span class="text-gray-500 text-sm">- เพิ่มรหัสวิชา ชื่อวิชา เลือกระดับชั้น ประเภทวิชา และกำหนดห้องเรียน/คาบสอน</span>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-3xl">📝</span>
              <div>
                <span class="font-semibold text-green-600">รายงานการสอน</span> <br>
                ไปที่ <span class="bg-green-100 text-green-700 px-2 py-1 rounded">เมนู "รายงานการสอน"</span> เพื่อบันทึกการสอนแต่ละคาบ <br>
                <span class="text-gray-500 text-sm">- เลือกวันที่ วิชา ห้องเรียน คาบสอน<br>
                - กรอกแผน/หัวข้อ กิจกรรม รายชื่อนักเรียนที่ขาดเรียน<br>
                - แนบรูปภาพ และบันทึกสะท้อนคิด/ปัญหา/ข้อเสนอแนะ</span>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-3xl">🔍</span>
              <div>
                <span class="font-semibold text-purple-600">ดู/แก้ไข/ลบข้อมูล</span> <br>
                สามารถดูรายละเอียด แก้ไข หรือลบข้อมูลรายวิชาและรายงานการสอนได้จากแต่ละเมนู <br>
                <span class="text-gray-500 text-sm">- ใช้ปุ่ม <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">✏️ แก้ไข</span> หรือ <span class="bg-red-100 text-red-700 px-2 py-1 rounded">🗑️ ลบ</span> ในตาราง</span>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-3xl">💡</span>
              <div>
                <span class="font-semibold text-pink-600">คำแนะนำ</span> <br>
                <ul class="list-disc pl-5 text-gray-600 text-sm">
                  <li>ควรบันทึกข้อมูลให้ครบถ้วนและตรวจสอบก่อนกดบันทึก</li>
                  <li>สามารถแก้ไขข้อมูลย้อนหลังได้หากมีข้อผิดพลาด</li>
                  <li>หากพบปัญหาในการใช้งาน กรุณาติดต่อผู้ดูแลระบบ</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="mt-8 text-center">
            <span class="text-gray-400 text-sm">✨ ระบบนี้ออกแบบมาเพื่อช่วยให้ครูจัดการข้อมูลการสอนได้สะดวกและมีประสิทธิภาพมากขึ้น ✨</span>
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
