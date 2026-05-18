<?php
session_start();
$subjectId = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

require_once('../classes/DatabaseTeachingReport.php');
require_once('../classes/DatabaseUsers.php');
$db = new \App\DatabaseTeachingReport();
$dbUsers = new \App\DatabaseUsers();
$pdo = $db->getPDO();
$pdoUsers = $dbUsers->getPDO();

// ดึงข้อมูลวิชา
$subject = null;
$teacherMajor = '';
if ($subjectId) {
    $stmt = $pdo->prepare("SELECT name, code, level, created_by FROM subjects WHERE id = ?");
    $stmt->execute([$subjectId]);
    $subject = $stmt->fetch();
    
    // ดึงข้อมูลกลุ่มสาระของครูผู้สอน
    if ($subject && $subject['created_by']) {
        $stmtTeacher = $pdoUsers->prepare("SELECT Teach_major FROM teacher WHERE Teach_id = ?");
        $stmtTeacher->execute([$subject['created_by']]);
        $teacher = $stmtTeacher->fetch();
        if ($teacher) {
            $teacherMajor = $teacher['Teach_major'];
        }
    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบวิเคราะห์ผู้เรียนรายบุคคล</title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS 3 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Mali', 'cursive', 'sans-serif'],
                        heading: ['Kanit', 'sans-serif']
                    },
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#ec4899',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Mali:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
            background-attachment: fixed;
            font-family: 'Mali', cursive;
        }
        h1, h2, h3, h4, h5, h6, .heading-font {
            font-family: 'Kanit', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        .section-glass {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        .section-glass:hover {
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }
        .input-glass {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(226, 232, 240, 0.8);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            width: 100%;
            font-family: 'Mali', cursive;
            color: #1e293b;
        }
        .input-glass:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: #fff;
        }
        .form-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        .required-star {
            color: #ef4444;
            margin-left: 0.25rem;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            font-family: 'Kanit', sans-serif;
        }
        .btn-gradient:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 20px rgba(168, 85, 247, 0.4);
            color: white;
        }
        .check-card {
            background: rgba(255,255,255,0.7);
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .check-card:hover {
            background: rgba(255,255,255,1);
            border-color: #818cf8;
        }
        .check-card input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            accent-color: #6366f1;
            cursor: pointer;
        }
        .step-indicator {
            height: 8px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.5);
            overflow: hidden;
            margin-bottom: 0.5rem;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }
        .step-progress {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            width: 0%;
            transition: width 0.5s ease-out;
            border-radius: 4px;
        }
        /* Override Bootstrap Container */
        .container-custom {
            max-width: 900px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <div class="container-custom">
        <div class="glass-card rounded-3xl p-6 md:p-10 relative overflow-hidden animate-[fadeInUp_0.8s_ease-out]">
            <!-- Decorative Elements -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-purple-400 rounded-full mix-blend-multiply filter blur-2xl opacity-50 animate-pulse"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-400 rounded-full mix-blend-multiply filter blur-2xl opacity-50 animate-pulse" style="animation-delay: 2s;"></div>

            <div class="relative z-10">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 text-white shadow-lg mb-4">
                        <i class="fa-solid fa-clipboard-user text-2xl"></i>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-purple-700 mb-2">
                        แบบวิเคราะห์ผู้เรียนรายบุคคล
                    </h1>
                    <p class="text-slate-500 heading-font text-lg">รวบรวมข้อมูลเพื่อการจัดการเรียนรู้ที่เหมาะสม</p>
                </div>

                <?php if ($subject): ?>
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-5 mb-8 shadow-sm flex flex-col md:flex-row items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xl">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="flex-grow text-center md:text-left heading-font">
                        <h4 class="text-lg font-bold text-blue-900 mb-1"><?= htmlspecialchars($subject['name']) ?></h4>
                        <div class="flex flex-wrap justify-center md:justify-start gap-3 text-sm text-blue-700 font-medium">
                            <span class="bg-blue-100/50 px-3 py-1 rounded-full"><i class="fa-solid fa-barcode mr-1"></i> <?= htmlspecialchars($subject['code']) ?></span>
                            <span class="bg-blue-100/50 px-3 py-1 rounded-full"><i class="fa-solid fa-graduation-cap mr-1"></i> ชั้น ม.<?= intval($subject['level']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกข้อมูลสำเร็จ!',
                                text: 'ขอบคุณที่ให้ความร่วมมือในการให้ข้อมูลค่ะ/ครับ',
                                confirmButtonColor: '#4f46e5',
                                confirmButtonText: 'ตกลง',
                                customClass: {
                                    popup: 'rounded-2xl',
                                    title: 'heading-font',
                                    htmlContainer: 'font-sans'
                                }
                            });
                        });
                    </script>
                <?php elseif ($error): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: <?= json_encode($error) ?>,
                                confirmButtonColor: '#ec4899',
                                confirmButtonText: 'ลองอีกครั้ง',
                                customClass: {
                                    popup: 'rounded-2xl',
                                    title: 'heading-font',
                                    htmlContainer: 'font-sans'
                                }
                            });
                        });
                    </script>
                <?php endif; ?>

                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex justify-between items-end mb-2 heading-font">
                        <span class="text-sm font-semibold text-slate-600">ความคืบหน้า</span>
                        <span id="progressText" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">0%</span>
                    </div>
                    <div class="step-indicator">
                        <div id="progressBar" class="step-progress"></div>
                    </div>
                </div>

                <form method="post" id="analyzeForm" class="space-y-6 relative">
                    <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subjectId) ?>">

                    <!-- Section 1: ข้อมูลนักเรียนเบื้องต้น -->
                    <div class="section-glass">
                        <h4 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-200/60 pb-3">
                            <i class="fa-solid fa-id-card text-indigo-500"></i> 1. ข้อมูลนักเรียนเบื้องต้น
                        </h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">ชั้น/ห้อง<span class="required-star">*</span></label>
                                <select name="student_level_room" required class="input-glass">
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
                            <div class="col-md-6">
                                <label class="form-label">เลขที่<span class="required-star">*</span></label>
                                <input type="number" name="student_no" required class="input-glass" min="1" max="50" placeholder="เช่น 1" value="<?= htmlspecialchars($_POST['student_no'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">คำนำหน้า<span class="required-star">*</span></label>
                                <select name="prefix" required class="input-glass">
                                    <option value="">-- เลือก --</option>
                                    <?php
                                    $prefixes = ['ด.ช.', 'ด.ญ.', 'นาย', 'นางสาว', 'น.ส.', 'นาง', 'อื่นๆ'];
                                    $selectedPrefix = $_POST['prefix'] ?? '';
                                    foreach ($prefixes as $val) {
                                        $sel = ($selectedPrefix === $val) ? 'selected' : '';
                                        echo "<option value=\"$val\" $sel>$val</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ชื่อจริง<span class="required-star">*</span></label>
                                <input type="text" name="student_firstname" required class="input-glass" placeholder="ชื่อ" value="<?= htmlspecialchars($_POST['student_firstname'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">นามสกุล<span class="required-star">*</span></label>
                                <input type="text" name="student_lastname" required class="input-glass" placeholder="นามสกุล" value="<?= htmlspecialchars($_POST['student_lastname'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์นักเรียน<span class="required-star">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-mobile-screen"></i></span>
                                    <input type="tel" name="student_phone" required class="input-glass pl-10" placeholder="08xxxxxxxx" maxlength="10" value="<?= htmlspecialchars($_POST['student_phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: ข้อมูลกายภาพและสุขภาพ -->
                    <div class="section-glass">
                        <h4 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-200/60 pb-3">
                            <i class="fa-solid fa-heart-pulse text-rose-500"></i> 2. ข้อมูลกายภาพและสุขภาพ
                        </h4>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">น้ำหนัก (กิโลกรัม)<span class="required-star">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="weight" required class="input-glass pr-12" placeholder="0.0" value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">กก.</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ส่วนสูง (เซนติเมตร)<span class="required-star">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="height" required class="input-glass pr-12" placeholder="0.0" value="<?= htmlspecialchars($_POST['height'] ?? '') ?>">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">ซม.</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">โรคประจำตัว<span class="required-star">*</span></label>
                                <input type="text" name="disease" required class="input-glass" placeholder="หากไม่มีให้ระบุ 'ไม่มี'" value="<?= htmlspecialchars($_POST['disease'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: ข้อมูลครอบครัวและที่อยู่ -->
                    <div class="section-glass">
                        <h4 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-200/60 pb-3">
                            <i class="fa-solid fa-house-chimney text-amber-500"></i> 3. ข้อมูลครอบครัวและที่อยู่
                        </h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ-สกุลผู้ปกครอง<span class="required-star">*</span></label>
                                <input type="text" name="parent_name" required class="input-glass" placeholder="ระบุชื่อ-สกุล" value="<?= htmlspecialchars($_POST['parent_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์ผู้ปกครอง<span class="required-star">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" name="parent_phone" required class="input-glass pl-10" placeholder="08xxxxxxxx" maxlength="10" value="<?= htmlspecialchars($_POST['parent_phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">ปัจจุบันนักเรียนอาศัยอยู่กับใคร<span class="required-star">*</span></label>
                                <input type="text" name="live_with" required class="input-glass" placeholder="เช่น บิดามารดา, ปู่ย่าตายาย, ญาติ" value="<?= htmlspecialchars($_POST['live_with'] ?? '') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">ที่อยู่ปัจจุบันที่สามารถติดต่อได้<span class="required-star">*</span></label>
                                <textarea name="address" required class="input-glass" rows="2" placeholder="บ้านเลขที่ หมู่ ซอย ถนน ตำบล อำเภอ จังหวัด รหัสไปรษณีย์"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: ความสนใจและความสามารถ -->
                    <div class="section-glass">
                        <h4 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-200/60 pb-3">
                            <i class="fa-solid fa-star text-yellow-500"></i> 4. ความสนใจและผลการเรียน
                        </h4>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">เกรดเฉลี่ย (GPA) ภาคเรียนล่าสุด<span class="required-star">*</span></label>
                                <input type="number" step="0.01" name="gpa" required class="input-glass" min="0" max="4" placeholder="0.00" value="<?= htmlspecialchars($_POST['gpa'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ผลการเรียนวิชา<?= htmlspecialchars($teacherMajor)?> ในภาคเรียนที่ผ่านมา<span class="required-star">*</span></label>
                                <input type="number" step="0.01" name="last_com_grade" required class="input-glass" min="0" max="4" placeholder="0.00" value="<?= htmlspecialchars($_POST['last_com_grade'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">กิจกรรมยามว่างที่ชอบ<span class="required-star">*</span></label>
                                <input type="text" name="favorite_activity" required class="input-glass" placeholder="เช่น เล่นเกม, เล่นกีฬา, ดูซีรีส์" value="<?= htmlspecialchars($_POST['favorite_activity'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ความสามารถพิเศษ<span class="required-star">*</span></label>
                                <input type="text" name="special_skill" required class="input-glass" placeholder="เช่น ร้องเพลง, วาดรูป, เขียนโปรแกรม" value="<?= htmlspecialchars($_POST['special_skill'] ?? '') ?>">
                            </div>
                        </div>

                        <?php
                        $subjectsList = [
                            'คณิตศาสตร์' => 'fa-calculator text-blue-500',
                            'วิทยาศาสตร์' => 'fa-flask text-emerald-500',
                            'ภาษาไทย' => 'fa-language text-rose-500',
                            'สังคมศึกษา' => 'fa-globe text-amber-500',
                            'ศิลปะศึกษา' => 'fa-palette text-purple-500',
                            'สุขศึกษาและพละศึกษา' => 'fa-dumbbell text-orange-500',
                            'การงานอาชีพ' => 'fa-hammer text-stone-500',
                            'เทคโนโลยี' => 'fa-computer text-cyan-500',
                            'ภาษาต่างประเทศ' => 'fa-comments text-indigo-500'
                        ];
                        ?>
                        
                        <div class="mb-4">
                            <label class="form-label block mb-3"><i class="fa-solid fa-thumbs-up text-blue-500 mr-1"></i> วิชาที่ชอบ (เลือกได้มากกว่า 1 ข้อ)<span class="required-star">*</span></label>
                            <div class="row g-3">
                                <?php foreach ($subjectsList as $subj => $icon): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="check-card w-100">
                                        <input type="checkbox" name="like_subjects[]" value="<?= $subj ?>" <?= (isset($_POST['like_subjects']) && in_array($subj, (array)$_POST['like_subjects'])) ? 'checked' : '' ?>>
                                        <span class="text-sm"><i class="fa-solid <?= $icon ?> w-5 text-center mr-1"></i> <?= $subj ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                                <div class="col-12 col-md-6">
                                    <div class="flex items-center gap-2 mt-1">
                                        <label class="check-card flex-shrink-0">
                                            <input type="checkbox" name="like_subjects[]" value="อื่นๆ" id="checkLikeOther" <?= (isset($_POST['like_subjects']) && in_array('อื่นๆ', (array)$_POST['like_subjects'])) ? 'checked' : '' ?>>
                                            <span class="text-sm">อื่นๆ ระบุ:</span>
                                        </label>
                                        <input type="text" name="like_subjects_other" id="inputLikeOther" class="input-glass py-1.5 flex-grow" value="<?= htmlspecialchars($_POST['like_subjects_other'] ?? '') ?>" placeholder="...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label block mb-3"><i class="fa-solid fa-thumbs-down text-rose-500 mr-1"></i> วิชาที่ไม่ชอบ (เลือกได้มากกว่า 1 ข้อ)<span class="required-star">*</span></label>
                            <div class="row g-3">
                                <?php foreach ($subjectsList as $subj => $icon): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="check-card w-100">
                                        <input type="checkbox" name="dislike_subjects[]" value="<?= $subj ?>" <?= (isset($_POST['dislike_subjects']) && in_array($subj, (array)$_POST['dislike_subjects'])) ? 'checked' : '' ?>>
                                        <span class="text-sm"><i class="fa-solid <?= $icon ?> w-5 text-center mr-1"></i> <?= $subj ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                                <div class="col-12 col-md-6">
                                    <div class="flex items-center gap-2 mt-1">
                                        <label class="check-card flex-shrink-0">
                                            <input type="checkbox" name="dislike_subjects[]" value="อื่นๆ" id="checkDislikeOther" <?= (isset($_POST['dislike_subjects']) && in_array('อื่นๆ', (array)$_POST['dislike_subjects'])) ? 'checked' : '' ?>>
                                            <span class="text-sm">อื่นๆ ระบุ:</span>
                                        </label>
                                        <input type="text" name="dislike_subjects_other" id="inputDislikeOther" class="input-glass py-1.5 flex-grow" value="<?= htmlspecialchars($_POST['dislike_subjects_other'] ?? '') ?>" placeholder="...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Area -->
                    <div class="mt-8 text-center bg-white/40 p-4 rounded-2xl border border-white/60">
                        <p class="text-sm text-slate-500 mb-4 heading-font"><i class="fa-solid fa-circle-info text-blue-500"></i> ข้อมูลของนักเรียนจะถูกเก็บเป็นความลับเพื่อใช้ในการวางแผนการเรียนการสอนเท่านั้น</p>
                        <button type="submit" class="btn-gradient w-full md:w-auto md:px-12 py-3 shadow-xl">
                            <i class="fa-solid fa-paper-plane mr-2"></i> บันทึกข้อมูลการวิเคราะห์ผู้เรียน
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-6 text-sm text-slate-600/70 font-medium heading-font drop-shadow-sm">
            &copy; <?= date('Y') ?> ระบบดูแลช่วยเหลือนักเรียน - แบบวิเคราะห์ผู้เรียน
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Progress bar logic
        const form = document.getElementById('analyzeForm');
        const requiredInputs = Array.from(form.querySelectorAll('input[required], select[required], textarea[required]'));
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        // Custom requirements for checkboxes (at least one must be checked)
        const likeChecks = Array.from(form.querySelectorAll('input[name="like_subjects[]"]'));
        const dislikeChecks = Array.from(form.querySelectorAll('input[name="dislike_subjects[]"]'));

        function calculateProgress() {
            let filledCount = 0;
            const totalFields = requiredInputs.length + 2; // +2 for the two checkbox groups

            // Check standard inputs
            requiredInputs.forEach(input => {
                if (input.value.trim() !== '') {
                    filledCount++;
                }
            });

            // Check checkboxes
            if (likeChecks.some(chk => chk.checked)) filledCount++;
            if (dislikeChecks.some(chk => chk.checked)) filledCount++;

            const percent = Math.min(100, Math.round((filledCount / totalFields) * 100));
            
            progressBar.style.width = percent + '%';
            progressText.textContent = percent + '%';
            
            if (percent === 100) {
                progressText.classList.replace('text-indigo-600', 'text-emerald-600');
                progressText.classList.replace('bg-indigo-50', 'bg-emerald-50');
                progressBar.style.background = 'linear-gradient(90deg, #10b981, #34d399)';
            } else {
                progressText.classList.replace('text-emerald-600', 'text-indigo-600');
                progressText.classList.replace('bg-emerald-50', 'bg-indigo-50');
                progressBar.style.background = 'linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899)';
            }
        }

        form.addEventListener('input', calculateProgress);
        form.addEventListener('change', calculateProgress);
        calculateProgress(); // init

        // Logic for "Other" text inputs
        const checkLikeOther = document.getElementById('checkLikeOther');
        const inputLikeOther = document.getElementById('inputLikeOther');
        checkLikeOther.addEventListener('change', (e) => {
            if(e.target.checked) { inputLikeOther.focus(); }
        });
        inputLikeOther.addEventListener('input', () => {
            if(inputLikeOther.value.trim() !== '') checkLikeOther.checked = true;
        });

        const checkDislikeOther = document.getElementById('checkDislikeOther');
        const inputDislikeOther = document.getElementById('inputDislikeOther');
        checkDislikeOther.addEventListener('change', (e) => {
            if(e.target.checked) { inputDislikeOther.focus(); }
        });
        inputDislikeOther.addEventListener('input', () => {
            if(inputDislikeOther.value.trim() !== '') checkDislikeOther.checked = true;
        });

        // Form submission validation for checkboxes
        form.addEventListener('submit', function(e) {
            const hasLike = likeChecks.some(chk => chk.checked);
            const hasDislike = dislikeChecks.some(chk => chk.checked);
            
            if (!hasLike) {
                e.preventDefault();
                Swal.fire('กรุณาระบุข้อมูล', 'กรุณาเลือกวิชาที่ชอบอย่างน้อย 1 วิชา', 'warning');
                return false;
            }
            if (!hasDislike) {
                e.preventDefault();
                Swal.fire('กรุณาระบุข้อมูล', 'กรุณาเลือกวิชาที่ไม่ชอบอย่างน้อย 1 วิชา', 'warning');
                return false;
            }
        });
    });
    </script>
</body>
</html>
