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

    <section class="content relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-indigo-100 min-h-screen">
      <!-- Floating Elements -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-10 left-10 text-4xl animate-bounce opacity-20">📚</div>
        <div class="absolute top-20 right-20 text-3xl animate-pulse opacity-30">👩‍🏫</div>
        <div class="absolute bottom-20 left-20 text-4xl animate-bounce opacity-25" style="animation-delay: 1s;">📝</div>
        <div class="absolute bottom-10 right-10 text-3xl animate-pulse opacity-20" style="animation-delay: 2s;">🎓</div>
        <div class="absolute top-1/2 left-1/4 text-2xl animate-bounce opacity-15" style="animation-delay: 0.5s;">✨</div>
        <div class="absolute top-1/3 right-1/4 text-2xl animate-pulse opacity-25" style="animation-delay: 1.5s;">💡</div>
      </div>
      <div class="container mx-auto max-w-4xl mt-16 relative z-10">
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-10 border border-indigo-200 hover:shadow-3xl transition-all duration-500 transform hover:scale-105">
          <h2 class="text-3xl font-bold text-indigo-700 mb-6 flex items-center gap-3 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent animate-pulse">👩‍🏫 คู่มือการใช้งานสำหรับครู</h2>
          <div class="space-y-8 text-lg text-gray-700">
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 transition-all duration-300 transform hover:scale-102">
              <span class="text-4xl animate-bounce">📚</span>
              <div>
                <span class="font-semibold text-blue-600 text-xl">จัดการรายวิชา</span> <br>
                ไปที่ <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-medium">เมนู "จัดการรายวิชา"</span> เพื่อเพิ่ม แก้ไข หรือลบรายวิชาที่คุณสอน <br>
                <span class="text-gray-500 text-sm">- เพิ่มรหัสวิชา ชื่อวิชา เลือกระดับชั้น ประเภทวิชา และกำหนดห้องเรียน/คาบสอน</span>
              </div>
            </div>
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-102">
              <span class="text-4xl animate-pulse">📝</span>
              <div>
                <span class="font-semibold text-green-600 text-xl">รายงานการสอน</span> <br>
                ไปที่ <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full font-medium">เมนู "รายงานการสอน"</span> เพื่อบันทึกการสอนแต่ละคาบ <br>
                <span class="text-gray-500 text-sm">- เลือกวันที่ วิชา ห้องเรียน คาบสอน<br>
                - กรอกแผน/หัวข้อ กิจกรรม รายชื่อนักเรียนที่ขาดเรียน<br>
                - แนบรูปภาพ และบันทึกสะท้อนคิด/ปัญหา/ข้อเสนอแนะ</span>
              </div>
            </div>
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-r from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 transition-all duration-300 transform hover:scale-102">
              <span class="text-4xl animate-bounce">🔍</span>
              <div>
                <span class="font-semibold text-purple-600 text-xl">ดู/แก้ไข/ลบข้อมูล</span> <br>
                สามารถดูรายละเอียด แก้ไข หรือลบข้อมูลรายวิชาและรายงานการสอนได้จากแต่ละเมนู <br>
                <span class="text-gray-500 text-sm">- ใช้ปุ่ม <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">✏️ แก้ไข</span> หรือ <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full">🗑️ ลบ</span> ในตาราง</span>
              </div>
            </div>
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-r from-pink-50 to-pink-100 hover:from-pink-100 hover:to-pink-200 transition-all duration-300 transform hover:scale-102">
              <span class="text-4xl animate-pulse">💡</span>
              <div>
                <span class="font-semibold text-pink-600 text-xl">คำแนะนำ</span> <br>
                <ul class="list-disc pl-6 text-gray-600 text-base space-y-1">
                  <li>ควรบันทึกข้อมูลให้ครบถ้วนและตรวจสอบก่อนกดบันทึก</li>
                  <li>สามารถแก้ไขข้อมูลย้อนหลังได้หากมีข้อผิดพลาด</li>
                  <li>หากพบปัญหาในการใช้งาน กรุณาติดต่อผู้ดูแลระบบ</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="mt-10 text-center">
            <span class="text-gray-500 text-base bg-gradient-to-r from-indigo-100 to-purple-100 px-6 py-3 rounded-full shadow-md">✨ ระบบนี้ออกแบบมาเพื่อช่วยให้ครูจัดการข้อมูลการสอนได้สะดวกและมีประสิทธิภาพมากขึ้น ✨</span>
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
