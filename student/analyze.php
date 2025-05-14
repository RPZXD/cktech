<?php
session_start();
$subjectId = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

require_once('../classes/DatabaseTeachingReport.php');
$db = new \App\DatabaseTeachingReport();
$pdo = $db->getPDO();

// ดึงข้อมูลวิชา
$subject = null;
if ($subjectId) {
    $stmt = $pdo->prepare("SELECT name, code, level FROM subjects WHERE id = ?");
    $stmt->execute([$subjectId]);
    $subject = $stmt->fetch();
}

// POST: รับข้อมูลฟอร์ม
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $student_level_room = trim($_POST['student_level_room'] ?? '');
    $student_no = trim($_POST['student_no'] ?? '');
    $prefix = trim($_POST['prefix'] ?? '');
    $student_firstname = trim($_POST['student_firstname'] ?? '');
    $student_lastname = trim($_POST['student_lastname'] ?? '');
    $student_phone = trim($_POST['student_phone'] ?? '');
    $weight = trim($_POST['weight'] ?? '');
    $height = trim($_POST['height'] ?? '');
    $disease = trim($_POST['disease'] ?? '');
    $parent_name = trim($_POST['parent_name'] ?? '');
    $live_with = trim($_POST['live_with'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $parent_phone = trim($_POST['parent_phone'] ?? '');
    $favorite_activity = trim($_POST['favorite_activity'] ?? '');
    $special_skill = trim($_POST['special_skill'] ?? '');
    $gpa = trim($_POST['gpa'] ?? '');
    $last_com_grade = trim($_POST['last_com_grade'] ?? '');
    $like_subjects = isset($_POST['like_subjects']) ? $_POST['like_subjects'] : [];
    $like_subjects_other = trim($_POST['like_subjects_other'] ?? '');
    $dislike_subjects = isset($_POST['dislike_subjects']) ? $_POST['dislike_subjects'] : [];
    $dislike_subjects_other = trim($_POST['dislike_subjects_other'] ?? '');
    $subject_id = intval($_POST['subject_id'] ?? 0);

    // ตรวจสอบข้อมูล
    if (
        $student_level_room && $student_no && $prefix && $student_firstname && $student_lastname &&
        $student_phone && $weight && $height && $disease && $parent_name && $live_with &&
        $address && $parent_phone && $favorite_activity && $special_skill && $gpa && $last_com_grade &&
        (!empty($like_subjects) || $like_subjects_other) &&
        (!empty($dislike_subjects) || $dislike_subjects_other) && $subject_id
    ) {
        // Validate เฉพาะตัวเลข
        if (!preg_match('/^\d{1,2}$/', $student_no)) {
            $error = 'เลขที่ต้องเป็นตัวเลข 1-2 หลัก';
        } elseif (!preg_match('/^0[689]\d{8}$/', $student_phone)) {
            $error = 'เบอร์โทรศัพท์นักเรียนไม่ถูกต้อง (ต้องขึ้นต้น 06, 08, 09 และ 10 หลัก)';
        } elseif (!preg_match('/^0[689]\d{8}$/', $parent_phone)) {
            $error = 'เบอร์โทรศัพท์ผู้ปกครองไม่ถูกต้อง (ต้องขึ้นต้น 06, 08, 09 และ 10 หลัก)';
        } elseif (!is_numeric($weight) || $weight < 10 || $weight > 200) {
            $error = 'น้ำหนักต้องเป็นตัวเลขระหว่าง 10-200 กิโลกรัม';
        } elseif (!is_numeric($height) || $height < 50 || $height > 250) {
            $error = 'ส่วนสูงต้องเป็นตัวเลขระหว่าง 50-250 เซนติเมตร';
        } elseif (!is_numeric($gpa) || $gpa < 0 || $gpa > 4) {
            $error = 'เกรดเฉลี่ยต้องเป็นตัวเลขระหว่าง 0-4';
        } else {
            // ตรวจสอบซ้ำ: ห้อง+เลขที่+subject_id
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM student_analyze WHERE subject_id=? AND student_level_room=? AND student_no=?");
            $stmtCheck->execute([$subject_id, $student_level_room, $student_no]);
            if ($stmtCheck->fetchColumn() > 0) {
                $error = 'มีข้อมูลของห้องและเลขที่นี้ในวิชานี้แล้ว';
            }
        }

        if (!$error) {
            // รวมข้อมูลวิชาที่ชอบ/ไม่ชอบ
            $like_subjects_str = implode(', ', $like_subjects);
            if ($like_subjects_other) $like_subjects_str .= ($like_subjects_str ? ', ' : '') . $like_subjects_other;
            $dislike_subjects_str = implode(', ', $dislike_subjects);
            if ($dislike_subjects_other) $dislike_subjects_str .= ($dislike_subjects_str ? ', ' : '') . $dislike_subjects_other;

            $stmt = $pdo->prepare("INSERT INTO student_analyze (
                subject_id, student_level_room, student_no, prefix, student_firstname, student_lastname, student_phone,
                weight, height, disease, parent_name, live_with, address, parent_phone, favorite_activity, special_skill,
                gpa, last_com_grade, like_subjects, dislike_subjects, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $success = $stmt->execute([
                $subject_id, $student_level_room, $student_no, $prefix, $student_firstname, $student_lastname, $student_phone,
                $weight, $height, $disease, $parent_name, $live_with, $address, $parent_phone, $favorite_activity, $special_skill,
                $gpa, $last_com_grade, $like_subjects_str, $dislike_subjects_str
            ]);
            if (!$success) {
                $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แบบวิเคราะห์ผู้เรียนรายบุคคล</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap">
    <style>
        body, input, select, textarea, button {
            font-family: 'Mali', cursive !important;
        }
        .emoji-label {
            font-size: 1.1em;
            margin-right: 0.3em;
        }
        .focus-highlight:focus {
            box-shadow: 0 0 0 2px #60a5fa;
            border-color: #2563eb;
        }
        .form-section {
            background: linear-gradient(90deg, #f0f9ff 0%, #e0e7ff 100%);
        }
        .form-card {
            background: linear-gradient(120deg, #fff 60%, #f0f9ff 100%);
        }
        .form-animate {
            animation: fadeInUp 0.7s;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-3xl form-card rounded-2xl shadow-2xl p-8 mt-8 form-animate">
        <h1 class="text-3xl font-extrabold text-blue-700 mb-6 flex items-center gap-2">
            <span class="emoji-label">📋</span>แบบวิเคราะห์ผู้เรียนรายบุคคล
        </h1>
        <?php if ($subject): ?>
            <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 flex flex-col md:flex-row gap-4 items-center">
                <div class="flex-1 flex flex-col gap-1">
                    <div><span class="font-semibold text-blue-700">รหัสวิชา:</span> <?= htmlspecialchars($subject['code']) ?></div>
                    <div><span class="font-semibold text-blue-700">ชื่อวิชา:</span> <?= htmlspecialchars($subject['name']) ?></div>
                    <div><span class="font-semibold text-blue-700">ระดับชั้น:</span> <?= 'ม.' . intval($subject['level']) ?></div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="emoji-label">🧑‍💻</span>
                    <span class="text-blue-600 font-bold">กรุณากรอกข้อมูลให้ครบถ้วน</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4 flex items-center gap-2 animate-bounce">
                <span class="emoji-label">✅</span>บันทึกข้อมูลเรียบร้อยแล้ว ขอบคุณค่ะ/ครับ
            </div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4 flex items-center gap-2 animate-pulse">
                <span class="emoji-label">❌</span><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subjectId) ?>">
            <div class="form-section rounded-lg p-4">
                <div class="mb-3 flex gap-2">
                    <div class="w-1/2">
                        <label class="block font-medium mb-1"><span class="emoji-label">🏫</span>ชั้น/ห้อง <span class="text-red-500">*</span></label>
                        <select name="student_level_room" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight">
                            <option value="">-- เลือกห้อง --</option>
                            <?php
                            $selectedRoom = $_POST['student_level_room'] ?? '';
                            for ($i = 1; $i <= 12; $i++) {
                                $val = "ห้อง $i";
                                $sel = ($selectedRoom === $val) ? 'selected' : '';
                                echo "<option value=\"ห้อง $i\" $sel>ห้อง $i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="w-1/2">
                        <label class="block font-medium mb-1"><span class="emoji-label">🔢</span>เลขที่ <span class="text-red-500">*</span></label>
                        <input type="number" name="student_no" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" min="1" max="50" value="<?= htmlspecialchars($_POST['student_no'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3 flex gap-2">
                    <div class="w-1/3">
                        <label class="block font-medium mb-1"><span class="emoji-label">🧑</span>คำนำหน้า <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="prefix" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight bg-white appearance-none transition-all duration-200 hover:ring-2 hover:ring-blue-200">
                                <option value="">-- เลือกคำนำหน้า --</option>
                                <?php
                                $prefixes = [
                                    'ด.ช.' => '👦 ด.ช.',
                                    'ด.ญ.' => '👧 ด.ญ.',
                                    'นาย' => '👨 นาย',
                                    'นางสาว' => '👩 นางสาว',
                                    'น.ส.' => '👩 น.ส.',
                                    'นาง' => '👩‍🦳 นาง',
                                    'อื่นๆ' => 'อื่นๆ'
                                ];
                                $selectedPrefix = $_POST['prefix'] ?? '';
                                foreach ($prefixes as $val => $label) {
                                    $sel = ($selectedPrefix === $val) ? 'selected' : '';
                                    echo "<option value=\"$val\" $sel>$label</option>";
                                }
                                ?>
                            </select>
                            <span class="absolute right-3 top-3 text-gray-400 pointer-events-none">▼</span>
                        </div>
                    </div>
                    <div class="w-1/3">
                        <label class="block font-medium mb-1"><span class="emoji-label">👤</span>ชื่อ <span class="text-red-500">*</span></label>
                        <input type="text" name="student_firstname" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight transition-all duration-200 hover:ring-2 hover:ring-blue-200" value="<?= htmlspecialchars($_POST['student_firstname'] ?? '') ?>">
                    </div>
                    <div class="w-1/3">
                        <label class="block font-medium mb-1"><span class="emoji-label">👤</span>สกุล <span class="text-red-500">*</span></label>
                        <input type="text" name="student_lastname" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight transition-all duration-200 hover:ring-2 hover:ring-blue-200" value="<?= htmlspecialchars($_POST['student_lastname'] ?? '') ?>">
                    </div>
                </div>
                <!-- เพิ่มลูกเล่น: แสดง progress bar การกรอก -->
                <div class="mb-6">
                    <div class="h-3 bg-blue-100 rounded-full overflow-hidden">
                        <div id="progressBar" class="bg-gradient-to-r from-blue-400 to-blue-600 h-3 transition-all duration-300" style="width:0%"></div>
                    </div>
                    <div class="text-xs text-right text-blue-700 mt-1" id="progressText"></div>
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">📱</span>เบอร์โทรศัพท์นักเรียน <span class="text-red-500">*</span></label>
                    <input type="text" name="student_phone" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['student_phone'] ?? '') ?>">
                </div>
                <div class="mb-3 flex gap-2">
                    <div class="w-1/2">
                        <label class="block font-medium mb-1"><span class="emoji-label">⚖️</span>น้ำหนัก (กิโลกรัม) <span class="text-red-500">*</span></label>
                        <input type="number" name="weight" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>">
                    </div>
                    <div class="w-1/2">
                        <label class="block font-medium mb-1"><span class="emoji-label">📏</span>ส่วนสูง (เซนติเมตร) <span class="text-red-500">*</span></label>
                        <input type="number" name="height" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['height'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">💊</span>โรคประจำตัว <span class="text-red-500">*</span></label>
                    <input type="text" name="disease" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['disease'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">👨‍👩‍👧‍👦</span>ชื่อผู้ปกครอง <span class="text-red-500">*</span></label>
                    <input type="text" name="parent_name" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['parent_name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">🏠</span>อาศัยอยู่กับ <span class="text-red-500">*</span></label>
                    <input type="text" name="live_with" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['live_with'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">📬</span>ที่อยู่ปัจจุบัน <span class="text-red-500">*</span></label>
                    <textarea name="address" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">📞</span>เบอร์โทรศัพท์ผู้ปกครอง <span class="text-red-500">*</span></label>
                    <input type="text" name="parent_phone" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['parent_phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">🎯</span>กิจกรรมที่ชอบ <span class="text-red-500">*</span></label>
                    <input type="text" name="favorite_activity" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['favorite_activity'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">🌟</span>ความสามารถพิเศษ <span class="text-red-500">*</span></label>
                    <input type="text" name="special_skill" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['special_skill'] ?? '') ?>">
                </div>
                <div class="mb-3 flex gap-2">
                    <div class="w-1/2">
                        <label class="block font-medium mb-1"><span class="emoji-label">📊</span>เกรดเฉลี่ย (ระดับชั้นเดิม) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="gpa" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" min="0" max="4" value="<?= htmlspecialchars($_POST['gpa'] ?? '') ?>">
                    </div>
                    <div class="w-1/2">
                        <label class="block font-medium mb-1"><span class="emoji-label">💻</span>ผลการเรียนวิชาคอมพิวเตอร์ในภาคเรียนที่ผ่านมา <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="last_com_grade" required class="w-full border rounded px-3 py-2 focus:outline-none focus-highlight" min="0" max="4" value="<?= htmlspecialchars($_POST['last_com_grade'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">❤️</span>วิชาที่ชอบ (เลือกได้มากกว่า 1) <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-3">
                        <?php
                        $likeSubjectsList = [
                            'คณิตศาสตร์','วิทยาศาสตร์','ภาษาไทย','สังคมศึกษา','ศิลปะศึกษา',
                            'สุขศึกษาและพละศึกษา','การงานอาชีพ','เทคโนโลยี','ภาษาต่างประเทศ'
                        ];
                        foreach ($likeSubjectsList as $subj) {
                            $checked = (isset($_POST['like_subjects']) && in_array($subj, (array)$_POST['like_subjects'])) ? 'checked' : '';
                            echo '<label class="flex items-center gap-1"><input type="checkbox" name="like_subjects[]" value="'.$subj.'" '.$checked.'> '.$subj.'</label>';
                        }
                        ?>
                        <label class="flex items-center gap-1">
                            <input type="checkbox" name="like_subjects[]" value="อื่นๆ" <?= (isset($_POST['like_subjects']) && in_array('อื่นๆ', (array)$_POST['like_subjects'])) ? 'checked' : '' ?>>
                            อื่นๆ:
                            <input type="text" name="like_subjects_other" class="border rounded px-2 py-1 w-32 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['like_subjects_other'] ?? '') ?>">
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1"><span class="emoji-label">💔</span>วิชาที่ไม่ชอบ (เลือกได้มากกว่า 1) <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-3">
                        <?php
                        $dislikeSubjectsList = [
                            'คณิตศาสตร์','วิทยาศาสตร์','ภาษาไทย','สังคมศึกษา','ศิลปะศึกษา',
                            'สุขศึกษาและพละศึกษา','การงานอาชีพ','เทคโนโลยี','ภาษาต่างประเทศ'
                        ];
                        foreach ($dislikeSubjectsList as $subj) {
                            $checked = (isset($_POST['dislike_subjects']) && in_array($subj, (array)$_POST['dislike_subjects'])) ? 'checked' : '';
                            echo '<label class="flex items-center gap-1"><input type="checkbox" name="dislike_subjects[]" value="'.$subj.'" '.$checked.'> '.$subj.'</label>';
                        }
                        ?>
                        <label class="flex items-center gap-1">
                            <input type="checkbox" name="dislike_subjects[]" value="อื่นๆ" <?= (isset($_POST['dislike_subjects']) && in_array('อื่นๆ', (array)$_POST['dislike_subjects'])) ? 'checked' : '' ?>>
                            อื่นๆ:
                            <input type="text" name="dislike_subjects_other" class="border rounded px-2 py-1 w-32 focus:outline-none focus-highlight" value="<?= htmlspecialchars($_POST['dislike_subjects_other'] ?? '') ?>">
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white px-8 py-2 rounded shadow text-lg transition-all duration-200 transform hover:scale-105 flex items-center gap-2 animate-bounce">
                    <span class="emoji-label">💾</span>บันทึก
                </button>
            </div>
        </form>
    </div>
    <script>
    // Progress bar ลูกเล่น
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const requiredFields = Array.from(form.querySelectorAll('[required]'));
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        function updateProgress() {
            let filled = 0;
            requiredFields.forEach(f => {
                if ((f.type === 'checkbox' && f.checked) ||
                    (f.type !== 'checkbox' && f.value.trim() !== '')) filled++;
            });
            const percent = Math.round((filled / requiredFields.length) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = `กรอกข้อมูลแล้ว ${filled} / ${requiredFields.length} ช่อง (${percent}%)`;
        }
        form.addEventListener('input', updateProgress);
        updateProgress();
    });
    </script>
</body>
</html>
