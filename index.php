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

    <section class="content">
      <div class="container mx-auto py-8 flex justify-center">
        <div class="max-w-2xl w-full">
          <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center text-center border border-blue-100">
            <div class="text-5xl mb-4">📝📚</div>
            <h2 class="text-2xl font-bold text-blue-700 mb-2">ระบบรายงานการสอน</h2>
            <p class="text-gray-700 mb-4">
              ระบบนี้ช่วยให้ครูสามารถบันทึกและติดตามการสอนในแต่ละวันได้อย่างสะดวกและมีประสิทธิภาพ
            </p>
            <ul class="text-left text-gray-600 space-y-2 mb-4">
              <li>✅ <span class="font-medium">บันทึกรายละเอียดวิชา</span> และห้องเรียนที่สอน</li>
              <li>⏰ <span class="font-medium">ระบุคาบเรียน</span> และวันสอนแต่ละห้อง</li>
              <li>📝 <span class="font-medium">บันทึกกิจกรรมการสอน</span> และหัวข้อ/แผนการสอน</li>
              <li>🙋‍♂️ <span class="font-medium">เช็คชื่อ/ขาดเรียน</span> พร้อมสถานะนักเรียน</li>
              <li>🖼️ <span class="font-medium">แนบรูปภาพ</span> ประกอบการสอน</li>
              <li>📅 <span class="font-medium">ดูรายงานย้อนหลัง</span> ในรูปแบบตารางและปฏิทิน</li>
            </ul>
            <div class="flex flex-wrap gap-3 justify-center">
              <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">เริ่มต้นใช้งานได้ทันที!</span>
              <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">ปลอดภัยและใช้งานง่าย</span>
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
