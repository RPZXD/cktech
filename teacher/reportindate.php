<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ครู') {
    header('Location: ../login.php');
    exit;
}
// Read configuration from JSON file
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
                            📅 รายงานการสอนแบบปฏิทิน
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid flex justify-center">
                <div class="w-full max-w-7xl">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php'); ?>
</div>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'th',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('../controllers/TeachingReportController.php?action=list')
                .then(res => res.json())
                .then(data => {
                    // แปลงข้อมูลเป็น event ของ FullCalendar
                    const events = data.map(report => ({
                        id: report.id,
                        title: (report.subject_name || '-') + ' (' + (report.class_room || '-') + ')',
                        start: report.report_date,
                        extendedProps: report
                    }));
                    successCallback(events);
                })
                .catch(failureCallback);
        },
        eventClick: function(info) {
            const report = info.event.extendedProps;
            let html = `<div class="text-lg font-bold mb-2">รายละเอียดรายงานการสอน</div>
                <div class="mb-2 text-left"><span class="font-semibold">📅 วันที่:</span> ${report.report_date}</div>
                <div class="mb-2 text-left"><span class="font-semibold">📖 วิชา:</span> ${report.subject_name || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">🏫 ห้อง:</span> ${report.class_room}</div>
                <div class="mb-2 text-left"><span class="font-semibold">⏰ คาบ:</span> ${report.period_start} - ${report.period_end}</div>
                <div class="mb-2 text-left"><span class="font-semibold">📝 แผน/หัวข้อ:</span> ${report.plan_topic || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">👨‍🏫 กิจกรรม:</span> ${report.activity || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">🙋‍♂️ ขาดเรียน:</span> ${report.absent_students || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">💡 K:</span> ${report.reflection_k || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">💡 P:</span> ${report.reflection_p || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">💡 A:</span> ${report.reflection_a || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">❗ ปัญหา:</span> ${report.problems || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">📝 ข้อเสนอแนะ:</span> ${report.suggestions || '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">🖼️ รูปภาพ 1:</span> ${report.image1 ? `<img src="../${report.image1}" class="inline-block max-h-32 rounded border" />` : '-'}</div>
                <div class="mb-2 text-left"><span class="font-semibold">🖼️ รูปภาพ 2:</span> ${report.image2 ? `<img src="../${report.image2}" class="inline-block max-h-32 rounded border" />` : '-'}</div>
            `;
            Swal.fire({
                html: html,
                width: 700,
                showCloseButton: true,
                showConfirmButton: false
            });
        }
    });
    calendar.render();
});
</script>
<?php require_once('script.php'); ?>
</body>
</html>
