<?php 
session_start();
// เช็ค session และ role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ผู้บริหาร') {
    header('Location: ../login.php');
    exit;
}

// Get supervision ID
$supervision_id = $_GET['id'] ?? '';
if (!$supervision_id) {
    die('ไม่พบข้อมูลการนิเทศ');
}

// Fetch supervision data
require_once '../models/Supervision.php';
use App\Models\Supervision;

$supervisionModel = new Supervision();
$supervision = $supervisionModel->getById($supervision_id);

if (!$supervision) {
    die('ไม่พบข้อมูลการนิเทศ');
}

// Check if department evaluation exists
if (!$supervision['dept_score'] || $supervision['dept_score'] <= 0) {
    die('หัวหน้ากลุ่มสาระยังไม่ได้ประเมิน');
}

// Format date function
function formatDate($dateString) {
    $date = new DateTime($dateString);
    $months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    return $date->format('j') . ' ' . $months[(int)$date->format('n')] . ' ' . ($date->format('Y') + 543);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการนิเทศการสอน</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { 
            size: A4; 
            margin: 0.25in;
        }
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        /* Page 1 - Cover Page */
        .page-1 {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-after: always;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            background: url('../dist/img/logo-phicha.png') no-repeat center;
            background-size: contain;
        }
        
        .main-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 40px;
        }
        
        .subject-info {
            margin-bottom: 30px;
            line-height: 2;
        }
        
        .teacher-photo {
            width: 140px;
            height: 180px;
            margin: 25px auto;
            position: relative;
            background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 4px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        
        .teacher-photo::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(145deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 16px;
            z-index: 1;
        }
        
        .teacher-photo-inner {
            width: 100%;
            height: 100%;
            background-color: #f8f9fa;
            border-radius: 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .teacher-photo-fallback {
            color: #6b7280;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .teacher-photo-icon {
            width: 40px;
            height: 40px;
            background: rgba(107, 114, 128, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .teacher-info {
            margin-bottom: 40px;
            line-height: 2;
        }
        
        .school-hierarchy {
            font-size: 16px;
            font-weight: bold;
            line-height: 1.8;
        }
        
        /* Page 2 - Introduction */
        .page-2 {
            page-break-after: always;
            padding: 20px 0;
        }
        
        .page-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        .intro-content {
            text-align: justify;
            line-height: 2;
            text-indent: 40px;
            margin-bottom: 80px;
        }
        
        .signature-section-intro {
            text-align: center;
            margin-top: 60px;
        }
        
        /* Page 3 - Table of Contents */
        .page-3 {
            page-break-after: always;
            padding: 20px 0;
        }
        
        .toc-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        
        .toc-table td {
            padding: 8px;
            border-bottom: 1px dotted #ccc;
        }
        
        .toc-table .page-number {
            text-align: right;
            width: 50px;
        }
        
        /* Main Content Pages */
        .content-page {
            page-break-before: always;
            padding: 20px 0;
        }
        
        .section {
            margin-bottom: 25px;
            border: 1px solid #333;
            border-radius: 0;
        }
        
        .section-header {
            background-color: #f0f0f0;
            padding: 10px 15px;
            font-weight: bold;
            border-bottom: 1px solid #333;
        }
        
        .section-content {
            padding: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 150px;
        }
        
        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .evaluation-table th,
        .evaluation-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }
        
        .evaluation-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .evaluation-item {
            text-align: left;
            padding-left: 10px;
        }
        
        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }
        
        .no-print button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 2px;
        }
        
        .btn-print {
            background: #059669;
            color: white;
        }
        
        .btn-close {
            background: #6b7280;
            color: white;
        }
        
        @media print {
            .no-print { 
                display: none; 
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ พิมพ์</button>
        <button class="btn-close" onclick="window.close()">❌ ปิด</button>
    </div>

    <!-- หน้าที่ 1 - หน้าปก -->
    <div class="page-1">
        <div class="school-logo"></div>
        
        <div class="main-title">รายงานการนิเทศการสอน</div>
        
        <div class="subject-info">
            <strong>รายวิชา <?= htmlspecialchars($supervision['subject_name'] ?? '') ?> รหัสวิชา <?= htmlspecialchars($supervision['subject_code'] ?? '') ?></strong><br>
            <strong>ชั้นมัธยมศึกษาปีที่ <?= htmlspecialchars($supervision['class_level'] ?? '') ?></strong><br>
            <strong>ภาคเรียนที่ <?= htmlspecialchars($supervision['term'] ?? '') ?> ปีการศึกษา <?= htmlspecialchars($supervision['pee'] ?? '') ?></strong>
        </div>
        
        <div class="teacher-photo">
            <div class="teacher-photo-inner">
                <div class="teacher-photo-fallback">
                    <div class="teacher-photo-icon">👤</div>
                    <span>รูปครู</span>
                </div>
            </div>
        </div>
        
        <div class="teacher-info">
            <strong><?= htmlspecialchars($supervision['teacher_name'] ?? '') ?></strong><br>
            <strong>ตำแหน่ง <?= htmlspecialchars($supervision['position'] ?? '') ?> วิทยฐานะ <?= htmlspecialchars($supervision['academic_level'] ?? '') ?></strong><br>
            <strong>กลุ่มสาระการเรียนรู้ <?= htmlspecialchars($supervision['subject_group'] ?? '') ?></strong>
        </div>
        
        <div class="school-hierarchy">
            โรงเรียนเฉลิมขวัญเทคนิค<br>
            สำนักงานเขตพื้นที่การศึกษามัธยมศึกษาพิษณุโลก อุตรดิตถ์<br>
            สำนักงานคณะกรรมการศึกษาขั้นพื้นฐาน กระทรวงศึกษาธิการ
        </div>
    </div>


    <!-- หน้าที่ 4 - แบบการเยี่ยมชั้นเรียน -->
    <div class="content-page">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 20px;">แบบการเยี่ยมชั้นเรียน (CLASSROOM VISITATION)</h2>
            <p>ภาคเรียนที่ <?= htmlspecialchars($supervision['term'] ?? '') ?> ปีการศึกษา <?= htmlspecialchars($supervision['pee'] ?? '') ?></p>
        </div>
        
        <div class="section">
            <div class="section-header">ตอนที่ 1 ข้อมูลทั่วไป</div>
            <div class="section-content">
                <div style="margin-bottom: 15px;">
                    <strong>1. ผู้รับผิดชอบชั้นเรียน (ชื่อ-นามสกุล)</strong> <?= htmlspecialchars($supervision['teacher_name'] ?? '') ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>2. กลุ่มสาระการเรียนรู้/วิชา</strong> <?= htmlspecialchars($supervision['subject_group'] ?? '') ?> / <?= htmlspecialchars($supervision['subject_name'] ?? '') ?><br>
                    <strong>เรื่องที่สอน</strong> <?= str_repeat('.', 40) ?> <strong>ระดับชั้น ม.</strong> <?= htmlspecialchars($supervision['class_level'] ?? '') ?><br>
                    <strong>วันที่</strong> <?= str_repeat('.', 5) ?> <strong>เดือน</strong> <?= str_repeat('.', 15) ?> <strong>พ.ศ.</strong> <?= str_repeat('.', 8) ?> <strong>คาบที่</strong> <?= str_repeat('.', 5) ?> <strong>เวลา</strong> <?= str_repeat('.', 15) ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>3. ผู้เยี่ยมชั้นเรียน (ชื่อ- นามสกุล)</strong> <?= str_repeat('.', 50) ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">ตอนที่ 2 การเยี่ยมชั้นเรียน</div>
            <div class="section-content">
                <div style="margin-bottom: 20px;">
                    <strong>คำชี้แจง</strong> แบบการเยี่ยมชั้นเรียนนี้ เป็นแบบเยี่ยมการจัดการเรียนการสอนของครูในแต่ละรายวิชาที่สอน 
                    โดยผู้บริหารหรือผู้เยี่ยมชั้นเรียน และบันทึกข้อมูลจากการเยี่ยมชั้นเรียนโดยทำเครื่องหมายถูก () ในแบบประเมินทุกข้อ<br><br>
                    <strong>5</strong> = ปฏิบัติได้ระดับดีมาก &nbsp;&nbsp;&nbsp;&nbsp; <strong>4</strong> = ปฏิบัติได้ระดับดี &nbsp;&nbsp;&nbsp;&nbsp; <strong>3</strong> = ปฏิบัติได้ระดับปานกลาง<br>
                    <strong>2</strong> = ปฏิบัติได้ระดับพอใช้ &nbsp;&nbsp;&nbsp;&nbsp; <strong>1</strong> = ควรปรับปรุงแก้ไข
                </div>
                
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #333;">
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #333; padding: 8px; text-align: center; width: 40px;">ที่</th>
                        <th style="border: 1px solid #333; padding: 8px; text-align: center;">รายการ</th>
                        <th style="border: 1px solid #333; padding: 8px; text-align: center;" colspan="5">ระดับการปฏิบัติ</th>
                        <th style="border: 1px solid #333; padding: 8px; text-align: center; width: 80px;">หมายเหตุ</th>
                    </tr>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #333; padding: 4px;"></th>
                        <th style="border: 1px solid #333; padding: 4px;"></th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 30px;">5</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 30px;">4</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 30px;">3</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 30px;">2</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 30px;">1</th>
                        <th style="border: 1px solid #333; padding: 4px;"></th>
                    </tr>
                    
                    <?php 
                    $visitationItems = [
                        'ครูเข้าสอนตรงเวลา',
                        'ครูมีสื่อ อุปกรณ์การสอน พร้อมใช้งาน',
                        'ครูแต่งกายเหมาะสมกับสภาพความเป็นครู',
                        'ครูจัดบรรยากาศในห้องเรียนให้พร้อมต่อการเรียนรู้',
                        'ครูพูดด้วยน้ำเสียงน่าฟังและเร้าความสนใจของนักเรียน',
                        'ครูควบคุมดูแลการจัดห้องเรียนให้มีบรรยากาศแห่งการเรียนรู้',
                        'นักเรียนกระตือรือร้นที่จะเรียนรู้',
                        'นักเรียนมีความสนุกสนาน ร่าเริง แจ่มใส',
                        'นักเรียนสนใจปฏิบัติกิจกรรมที่ได้รับมอบหมาย',
                        'นักเรียนมีระเบียบวินัยดี และมีมารยาทเรียบร้อย'
                    ];
                    
                    foreach ($visitationItems as $index => $item):
                    ?>
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= $index + 1 ?></td>
                        <td style="border: 1px solid #333; padding: 8px;"><?= $item ?></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"></td>
                        <td style="border: 1px solid #333; padding: 8px;"></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td style="border: 1px solid #333; padding: 8px;" colspan="2">รวมคะแนน</td>
                        <td style="border: 1px solid #333; padding: 8px;" colspan="5"></td>
                        <td style="border: 1px solid #333; padding: 8px;"></td>
                    </tr>
                    
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td style="border: 1px solid #333; padding: 8px;" colspan="2">คะแนนเฉลี่ยทุกรายการ</td>
                        <td style="border: 1px solid #333; padding: 8px;" colspan="5">ระดับ <?= str_repeat('.', 15) ?></td>
                        <td style="border: 1px solid #333; padding: 8px;"></td>
                    </tr>
                </table>
                
                <div style="margin-top: 20px;">
                    <strong>เกณฑ์การแปลความหมาย</strong><br>
                    ระดับคะแนน &nbsp;&nbsp;&nbsp;&nbsp; 4.51 – 5.00 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึง &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ปฏิบัติได้ระดับดีมาก<br>
                    ระดับคะแนน &nbsp;&nbsp;&nbsp;&nbsp; 3.51 – 4.50 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึง &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ปฏิบัติได้ระดับดี<br>
                    ระดับคะแนน &nbsp;&nbsp;&nbsp;&nbsp; 2.51 – 3.50 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึง &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ปฏิบัติได้ระดับปานกลาง<br>
                    ระดับคะแนน &nbsp;&nbsp;&nbsp;&nbsp; 1.51 – 2.50 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึง &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ปฏิบัติได้ระดับพอใช้<br>
                    ระดับคะแนน &nbsp;&nbsp;&nbsp;&nbsp; 1.00 – 1.50 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึง &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ควรปรับปรุงแก้ไข
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
