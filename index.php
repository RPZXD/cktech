<?php 
// Read configuration from JSON file
$config = json_decode(file_get_contents('config.json'), true);
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
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <section class="content relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-indigo-100 min-h-screen">
      <!-- Floating Elements -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-10 left-10 text-4xl animate-bounce opacity-20">📚</div>
        <div class="absolute top-20 right-20 text-3xl animate-pulse opacity-30">✏️</div>
        <div class="absolute bottom-20 left-20 text-4xl animate-bounce opacity-25" style="animation-delay: 1s;">👨‍🏫</div>
        <div class="absolute bottom-10 right-10 text-3xl animate-pulse opacity-20" style="animation-delay: 2s;">📊</div>
        <div class="absolute top-1/2 left-1/4 text-2xl animate-bounce opacity-15" style="animation-delay: 0.5s;">🎓</div>
        <div class="absolute top-1/3 right-1/4 text-2xl animate-pulse opacity-25" style="animation-delay: 1.5s;">📅</div>
      </div>
      <div class="container mx-auto py-16 flex justify-center relative z-10">
        <div class="max-w-3xl w-full">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-10 flex flex-col items-center text-center border border-indigo-200 hover:shadow-3xl hover:scale-105 transition-all duration-500 transform">
            <div class="text-6xl mb-6 animate-pulse">📝📚</div>
            <h2 class="text-3xl font-bold text-indigo-700 mb-4 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">ระบบ วิชาการ</h2>
            <p class="text-gray-600 mb-6 text-lg leading-relaxed">
              ระบบนี้ช่วยให้ครูสามารถบันทึกและติดตามการสอนในแต่ละวันได้อย่างสะดวกและมีประสิทธิภาพ
            </p>
            <ul class="text-left text-gray-600 space-y-3 mb-6 w-full max-w-md">
              <li class="flex items-center space-x-3 hover:text-indigo-600 transition-colors duration-300">
                <span class="text-green-500 text-xl">✅</span>
                <span class="font-medium">บันทึกรายละเอียดวิชา และห้องเรียนที่สอน</span>
              </li>
              <li class="flex items-center space-x-3 hover:text-indigo-600 transition-colors duration-300">
                <span class="text-blue-500 text-xl">⏰</span>
                <span class="font-medium">ระบุคาบเรียน และวันสอนแต่ละห้อง</span>
              </li>
              <li class="flex items-center space-x-3 hover:text-indigo-600 transition-colors duration-300">
                <span class="text-purple-500 text-xl">📝</span>
                <span class="font-medium">บันทึกกิจกรรมการสอน และหัวข้อ/แผนการสอน</span>
              </li>
              <li class="flex items-center space-x-3 hover:text-indigo-600 transition-colors duration-300">
                <span class="text-orange-500 text-xl">🙋‍♂️</span>
                <span class="font-medium">เช็คชื่อ/ขาดเรียน พร้อมสถานะนักเรียน</span>
              </li>
              <li class="flex items-center space-x-3 hover:text-indigo-600 transition-colors duration-300">
                <span class="text-pink-500 text-xl">🖼️</span>
                <span class="font-medium">แนบรูปภาพ ประกอบการสอน</span>
              </li>
              <li class="flex items-center space-x-3 hover:text-indigo-600 transition-colors duration-300">
                <span class="text-teal-500 text-xl">📅</span>
                <span class="font-medium">ดูรายงานย้อนหลัง ในรูปแบบตารางและปฏิทิน</span>
              </li>
            </ul>
            <div class="flex flex-wrap gap-4 justify-center">
              <span class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full text-sm font-semibold shadow-lg hover:shadow-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-300 transform hover:scale-110">
                🚀 เริ่มต้นใช้งานได้ทันที!
              </span>
              <span class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full text-sm font-semibold shadow-lg hover:shadow-xl hover:from-green-600 hover:to-green-700 transition-all duration-300 transform hover:scale-110">
                🔒 ปลอดภัยและใช้งานง่าย
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
    <?php require_once('footer.php');?>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>

</script>
<?php require_once('script.php'); ?>
</body>
</html>
