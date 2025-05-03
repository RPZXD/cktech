<?php
session_start();

// รับรหัสกิจกรรมจาก query string
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
// Read configuration from JSON file
$config = json_decode(file_get_contents('config.json'), true);
$global = $config['global'];

// ดึงค่า term และ pee จาก session
$term = isset($_SESSION['term']) ? $_SESSION['term'] : '-';
$pee = isset($_SESSION['pee']) ? $_SESSION['pee'] : '-';

require_once('header.php');

// เตรียมตัวแปรสำหรับรายละเอียดกิจกรรม
$activity = null;
$activity_id = null;
$already_registered = false;
$activity_detail_html = '';
$can_register = false;
$register_message = '';
$student_id = $_SESSION['user']['Stu_id'] ?? $_SESSION['username'] ?? null;

if ($code) {
    // 1. ตรวจสอบ code ว่าตรงกับกิจกรรมใด
    require_once('classes/DatabaseEvent.php');
    $db = new \App\DatabaseEvent();
    $pdo = $db->getPDO();

    // หา activity_id จาก code (unique หรือ code ปกติ)
    // ป้องกัน error ถ้าไม่มีตาราง activity_codes
    $activity = null;
    try {
        // ตรวจสอบว่าตาราง activity_codes มีอยู่หรือไม่
        $hasActivityCodes = false;
        $checkTable = $pdo->query("SHOW TABLES LIKE 'activity_codes'");
        if ($checkTable && $checkTable->fetch()) {
            $hasActivityCodes = true;
        }

        if ($hasActivityCodes) {
            $sql = "SELECT a.*, auc.activity_id AS unique_activity_id
                FROM activities a
                LEFT JOIN activity_unique_codes auc ON auc.code = ?
                WHERE a.id = auc.activity_id OR a.id IN (SELECT activity_id FROM activity_codes WHERE code = ?)
                LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $code]);
        } else {
            // ไม่มี activity_codes ให้หาเฉพาะจาก unique_codes
            $sql = "SELECT a.*, auc.activity_id AS unique_activity_id
                FROM activities a
                LEFT JOIN activity_unique_codes auc ON auc.code = ?
                WHERE a.id = auc.activity_id
                LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code]);
        }
        $activity = $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        $activity = null;
    }

    if ($activity) {
        $activity_id = $activity['id'];
        // ตรวจสอบว่านักเรียนลงทะเบียนกิจกรรมนี้แล้วหรือยัง
        if ($student_id) {
            $stmt2 = $pdo->prepare("SELECT 1 FROM student_activity_logs WHERE student_id = ? AND activity_id = ?");
            $stmt2->execute([$student_id, $activity_id]);
            $already_registered = $stmt2->fetch() ? true : false;
        }

        // เตรียมรายละเอียดกิจกรรม (เหมือน detail-modal)
        function thaiDate($dateStr) {
            $months = [
                "", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];
        
            $timestamp = strtotime($dateStr);
            $day = date('j', $timestamp);
            $month = $months[date('n', $timestamp)];
            $year = date('Y', $timestamp) + 543;
        
            return "$day $month $year";
        }
        
        // ตัวอย่างการใช้:
        $thaiDate = thaiDate($activity['event_date']);
        
        $activity_detail_html = '
            <div class="mb-2"><span class="font-bold">🎯 ชื่อกิจกรรม:</span> ' . htmlspecialchars($activity['title']) . '</div>
            <div class="mb-2"><span class="font-bold">📝 รายละเอียด:</span> ' . htmlspecialchars($activity['description'] ?? '-') . '</div>
            <div class="mb-2"><span class="font-bold">📅 วันที่จัด:</span> ' . $thaiDate . '</div>
            <div class="mb-2"><span class="font-bold">⏰ ชั่วโมง:</span> ' . htmlspecialchars($activity['hours']) . ' ชั่วโมง</div>
            <div class="mb-2"><span class="font-bold">🏷️ ประเภท:</span> ' . htmlspecialchars($activity['category'] ?? '-') . '</div>
            <div class="mb-2"><span class="font-bold">👥 จำนวนสูงสุด:</span> ' . (htmlspecialchars($activity['max_students'] ?? '-')) . ' คน</div>
        ';

        // เงื่อนไขแสดงปุ่มลงทะเบียน
        if (!$already_registered) {
            $can_register = true;
        } else {
            $register_message = '<div class="text-green-600 font-semibold mt-2">คุณได้ลงทะเบียนกิจกรรมนี้แล้ว</div>';
        }
    } else {
        $activity_detail_html = '<div class="text-red-500 font-semibold">ไม่พบกิจกรรมที่ตรงกับรหัสนี้</div>';
    }
}
?>
<body class="hold-transition sidebar-mini layout-fixed light-mode">
<div class="wrapper">
    <?php require_once('wrapper.php');?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">เข้าร่วมกิจกรรม</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content mb-4"> 
            <div class="container-fluid">
                <div class="card mt-6 max-w-2xl mx-auto bg-white rounded-lg shadow p-6 border border-blue-200"> 
                    <div class="card-header bg-blue-600 text-white font-semibold text-lg">
                        📲 ลงทะเบียนกิจกรรม
                    </div>
                    <div class="card-body ">
                        <div class="overflow-x-auto">
                            <form method="get" class="mb-4">
                                <label class="block mb-2 font-semibold">กรอกรหัสกิจกรรม</label>
                                <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" required class="w-full border rounded px-3 py-2 mb-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="รหัสกิจกรรม">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded">ค้นหา</button>
                            </form>
                            <?php if ($code): ?>
                                <div class="mt-4">
                                    <div class="font-semibold mb-2">🔎 รายละเอียดกิจกรรม:</div>
                                    <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-2">
                                        <?= $activity_detail_html ?>
                                    </div>
                                    <?php if ($can_register && $activity_id): ?>
                                        <button id="register-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded" 
                                            data-activity-id="<?= $activity_id ?>" data-code="<?= htmlspecialchars($code) ?>">
                                            ✅ ลงทะเบียนกิจกรรม
                                        </button>
                                    <?php endif; ?>
                                    <?= $register_message ?>
                                    <div id="register-result" class="mt-3"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('footer.php');?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const regBtn = document.getElementById('register-btn');
    if (regBtn) {
        regBtn.onclick = function() {
            Swal.fire({
                title: 'กรอกเลขประจำตัวนักเรียน',
                input: 'text',
                inputLabel: 'เลขประจำตัวนักเรียน',
                inputPlaceholder: 'กรอกเลขประจำตัว',
                inputAttributes: {
                    maxlength: 20,
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณากรอกเลขประจำตัว';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    regBtn.disabled = true;
                    regBtn.textContent = 'กำลังลงทะเบียน...';
                    fetch('controllers/EventController.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'register_activity',
                            activity_id: regBtn.dataset.activityId,
                            code: regBtn.dataset.code,
                            student_id: result.value.trim()
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire('สำเร็จ', 'ลงทะเบียนกิจกรรมเรียบร้อย', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('ผิดพลาด', res.message || 'ไม่สามารถลงทะเบียนได้', 'error');
                            regBtn.disabled = false;
                            regBtn.textContent = '✅ ลงทะเบียนกิจกรรม';
                        }
                    })
                    .catch(() => {
                        Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์', 'error');
                        regBtn.disabled = false;
                        regBtn.textContent = '✅ ลงทะเบียนกิจกรรม';
                    });
                }
            });
        };
    }
});
</script>
<?php require_once('script.php');?>
</body>
</html>
