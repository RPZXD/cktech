<?php 
session_start();
// เช็ค session และ role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ครู') {
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

require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

$dbUsers = new DatabaseUsers();
$pdo = $dbUsers->getPDO();

$TeacherData = $dbUsers->getTeacherByUsername($_SESSION['username']);


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
            background: url('https://std.phichai.ac.th/teacher/uploads/phototeach/<?=$TeacherData['Teach_photo']?>') no-repeat center;
            background-size: cover;
            border-radius: 16px;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            overflow: hidden;
        }
        
        .teacher-photo-inner::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30%;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
            z-index: 2;
        }
        
        .teacher-photo-fallback {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .teacher-photo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
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
            
            /* Ensure images print properly */
            img {
                max-width: 100% !important;
                height: auto !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        
        @media screen {
            .print-only {
                display: none;
            }
        }
        
        /* Enhanced image styling */
        img {
            transition: transform 0.3s ease;
        }
        
        img:hover {
            transform: scale(1.02);
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
        <br><br><br>
        <div class="subject-info">
            <strong>รายวิชา <?= htmlspecialchars($supervision['subject_name'] ?? '') ?> รหัสวิชา <?= htmlspecialchars($supervision['subject_code'] ?? '') ?></strong><br>
            <strong>ชั้นมัธยมศึกษาปีที่ <?= htmlspecialchars($supervision['class_level'] ?? '') ?></strong><br>
            <strong>ภาคเรียนที่ <?= htmlspecialchars($supervision['term'] ?? '') ?> ปีการศึกษา <?= htmlspecialchars($supervision['pee'] ?? '') ?></strong>
        </div>
        <br><br><br>
        <div class="teacher-photo">
            <div class="teacher-photo-inner">
                <?php if (empty($TeacherData['Teach_photo']) || !file_exists("https://std.phichai.ac.th/teacher/uploads/phototeach/".$TeacherData['Teach_photo'])): ?>
                <div class="teacher-photo-fallback">
                    <div class="teacher-photo-icon">👤</div>
                    <span>รูปครู</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <br>
        <div class="teacher-info">
            <strong><?= htmlspecialchars($supervision['teacher_name'] ?? '') ?></strong><br>
            <strong>ตำแหน่ง <?= htmlspecialchars($supervision['position'] ?? '') ?> วิทยฐานะ <?= htmlspecialchars($supervision['academic_level'] ?? '') ?></strong><br>
            <strong>กลุ่มสาระการเรียนรู้ <?= htmlspecialchars($supervision['subject_group'] ?? '') ?></strong>
        </div>
        <br><br><br><br>
        <div class="school-hierarchy">
            โรงเรียนพิชัย<br>
            สำนักงานเขตพื้นที่การศึกษามัธยมศึกษาพิษณุโลก อุตรดิตถ์<br>
            สำนักงานคณะกรรมการศึกษาขั้นพื้นฐาน กระทรวงศึกษาธิการ
        </div>
    </div>

    <!-- หน้าที่ 2 - คำนำ -->
    <div class="page-2">
        <div class="page-title">คำนำ</div>
        
        <div class="intro-content">
            โรงเรียนพิชัย มีนโยบายในการบริหารทรัพยากรบุคคลให้มีประสิทธิภาพ เพื่อพัฒนาคุณภาพการจัดการเรียนการสอน การนิเทศภายในโรงเรียนจึงเป็นมาตรฐานสำคัญในการปรับปรุงการเรียนการสอนและเพื่อพัฒนาคุณภาพการจัดการเรียนการสอนของครูให้เกิดประสิทธิภาพสูงสุด และยังเป็นการแลกเปลี่ยนเรียนรู้ ให้คำแนะนำปรึกษาระหว่างเพื่อนครูด้วย
        </div>
        
        <div class="intro-content">
            ข้าพเจ้า <?= htmlspecialchars($supervision['teacher_name'] ?? '') ?> ครูกลุ่มสาระการเรียนรู้ <?= htmlspecialchars($supervision['subject_group'] ?? '') ?> ได้ดำเนินการนิเทศภายในกลุ่มสาระการเรียนรู้ เพื่อให้การปฏิบัติงานบรรลุวัตถุประสงค์และเป้าหมายที่โรงเรียนกำหนด และเกิดประโยชน์สูงสุดในการพัฒนาการจัดการเรียนการสอนต่อไป
        </div>
        
        <div class="signature-section-intro">
            <br><br><br>
            (<?= htmlspecialchars($supervision['teacher_name'] ?? '') ?>)<br>
            <?= htmlspecialchars($supervision['position'] ?? '') ?> วิทยฐานะ <?= htmlspecialchars($supervision['academic_level'] ?? '') ?>
        </div>
    </div>

    <!-- หน้าที่ 3 - สารบัญ -->
    <div class="page-3">
        <div class="page-title">สารบัญ</div>
        
        <table class="toc-table">
            <tr>
                <td><strong>เรื่อง</strong></td>
                <td class="page-number"><strong>หน้า</strong></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>แบบการเยี่ยมชั้นเรียน (ถ้ามี)</td>
                <td class="page-number">1</td>
            </tr>
            <tr>
                <td>แบบบันทึกการนิเทศการจัดการเรียนรู้</td>
                <td class="page-number"></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;ตอนที่ 1 ข้อมูลทั่วไปของผู้รับการนิเทศ</td>
                <td class="page-number">4</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;ตอนที่ 2 แบบประเมินสมรรถนะการจัดการเรียนรู้ของผู้รับการนิเทศ</td>
                <td class="page-number">4</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;ตอนที่ 3 ผู้นิเทศบันทึกเพิ่มเติมการนิเทศการจัดการเรียนรู้</td>
                <td class="page-number">7</td>
            </tr>
            <tr>
                <td>แผนการจัดการเรียนรู้</td>
                <td class="page-number">8</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;ใบความรู้</td>
                <td class="page-number"></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;ใบงาน</td>
                <td class="page-number"></td>
            </tr>
            <tr>
                <td>ภาพบรรยากาศการนิเทศ</td>
                <td class="page-number"></td>
            </tr>
        </table>
    </div>

    <!-- หน้าที่ 4 - แบบบันทึกการนิเทศการจัดการเรียนรู้ ตอนที่ 1-2 -->
    <div class="content-page">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 20px;">แบบบันทึกการนิเทศการจัดการเรียนรู้</h2>
        </div>
        
        <div class="section">
            <div class="section-header">ตอนที่ 1 ข้อมูลทั่วไปของผู้รับการนิเทศ</div>
            <div class="section-content">
                <p style="margin-bottom: 15px;"><strong>คำชี้แจง</strong> โปรดเติมข้อความลงในช่องว่างที่กำหนดให้</p>
                
                <div style="margin-bottom: 15px;">
                    <strong>ชื่อผู้รับการนิเทศ</strong> <?= htmlspecialchars($supervision['teacher_name'] ?? '') ?> 
                    <strong>ตำแหน่ง</strong> <?= htmlspecialchars($supervision['position'] ?? '') ?> 
                    <strong>วิทยฐานะ</strong> <?= htmlspecialchars($supervision['academic_level'] ?? '') ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>กลุ่มสาระการเรียนรู้</strong> <?= htmlspecialchars($supervision['subject_group'] ?? '') ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>รายวิชาที่สอน</strong> <?= htmlspecialchars($supervision['subject_name'] ?? '') ?> 
                    <strong>รหัสวิชา</strong> <?= htmlspecialchars($supervision['subject_code'] ?? '') ?> 
                    <strong>ชั้น</strong> <?= htmlspecialchars($supervision['class_level'] ?? '') ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>นิเทศครั้งที่</strong> <?= htmlspecialchars($supervision['supervision_round'] ?? '') ?> 
                    <strong>วัน เดือน ปีที่รับการนิเทศ</strong> <?= formatDate($supervision['supervision_date']) ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">ตอนที่ 2 แบบประเมินสมรรถนะการจัดการเรียนรู้ของผู้รับการนิเทศ</div>
            <div class="section-content">
                <div style="margin-bottom: 20px;">
                    <strong>คำชี้แจง</strong> ให้ผู้นิเทศสังเกตกระบวนการจัดการเรียนรู้ของผู้รับการนิเทศทั้ง 4 ด้าน แล้วทำเครื่องหมาย ✓ ในช่องที่มีการปฏิบัติมากที่สุดถึงน้อยที่สุด โดยใช้เกณฑ์ดังนี้<br>
                    <strong>5</strong> หมายถึง ดีมาก &nbsp;&nbsp;&nbsp;&nbsp; <strong>4</strong> หมายถึง ดี &nbsp;&nbsp;&nbsp;&nbsp; <strong>3</strong> หมายถึง ปานกลาง &nbsp;&nbsp;&nbsp;&nbsp; <strong>2</strong> หมายถึง พอใช้ &nbsp;&nbsp;&nbsp;&nbsp; <strong>1</strong> หมายถึง ควรปรับปรุง
                </div>
                
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #333; font-size: 12px;">
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #333; padding: 6px; text-align: center; width: 30px;">ที่</th>
                        <th style="border: 1px solid #333; padding: 6px; text-align: center;">รายการ</th>
                        <th style="border: 1px solid #333; padding: 6px; text-align: center;" colspan="5">ระดับการปฏิบัติ</th>
                    </tr>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #333; padding: 4px;"></th>
                        <th style="border: 1px solid #333; padding: 4px;"></th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">5</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">4</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">3</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">2</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">1</th>
                    </tr>
                    
                    <!-- ด้านที่ 1: การจัดทำแผน -->
                    <tr style="background-color: #f8f9fa;">
                        <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">1. ด้านความสามารถในการจัดทำแผนการจัดการเรียนรู้</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">1</td>
                        <td style="border: 1px solid #333; padding: 6px;">การวางแผนการสอนที่มีประสิทธิภาพ</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_effective'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_effective'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_effective'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_effective'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_effective'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">2</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้ถูกต้อง เป็นขั้นตอน และครบองค์ประกอบ</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_correct'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_correct'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_correct'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_correct'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_correct'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">3</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้มีกิจกรรมที่ทำให้นักเรียนเกิดการเรียนรู้</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_activities'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_activities'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_activities'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_activities'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_activities'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">4</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้มีการจัดหาสื่อที่เหมาะสมกับการเรียนรู้ของนักเรียน</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_media'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_media'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_media'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_media'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_media'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">5</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้มีการวัดและประเมินผลผู้เรียนได้อย่างเหมาะสม</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_assessment'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_assessment'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_assessment'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_assessment'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['dept_plan_assessment'] == 1) ? '✓' : '' ?></td>
                    </tr>

            <!-- ด้านที่ 2: การจัดการเรียนรู้ -->
            <tr style="background-color: #f8f9fa;">
                <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">2. ด้านความสามารถในการจัดการเรียนรู้</td>
            </tr>
            <?php 
            $teachingItems = [
                ['num' => 6, 'text' => 'ใช้เทคนิคต่าง ๆ ที่ทำให้นักเรียนทุกคนมีส่วนร่วมในชั้นเรียน', 'field' => 'dept_teach_techniques'],
                ['num' => 7, 'text' => 'เลือกใช้สื่อ เทคโนโลยีและอุปกรณ์การสอนที่เหมาะสม', 'field' => 'dept_teach_media'],
                ['num' => 8, 'text' => 'มีการประเมินนักเรียนระหว่างเรียน', 'field' => 'dept_teach_assessment'],
                ['num' => 9, 'text' => 'อธิบายเนื้อหาบทเรียนได้อย่างชัดเจน', 'field' => 'dept_teach_explanation'],
                ['num' => 10, 'text' => 'มีความสามารถในการควบคุมชั้นเรียนเมื่อทำกิจกรรม', 'field' => 'dept_teach_control'],
                ['num' => 11, 'text' => 'มีการจัดกิจกรรมการเรียนรู้ที่เน้นการพัฒนาการคิด ได้อภิปราย ซักถาม และแสดงความคิดเห็น', 'field' => 'dept_teach_thinking'],
                ['num' => 12, 'text' => 'มีการปรับเนื้อหา กิจกรรมในขณะจัดการเรียนรู้เพื่อให้เหมาะสมตามสถานการณ์หรือให้ทันเวลาที่เหลือ', 'field' => 'dept_teach_adaptation'],
                ['num' => 13, 'text' => 'มีกิจกรรมการเรียนการสอนที่เชื่อมโยงหรือบูรณาการกับชีวิตประจำวัน สอดแทรกคุณธรรม จริยธรรมระหว่างเรียน', 'field' => 'dept_teach_integration'],
                ['num' => 14, 'text' => 'ใช้ภาษาพูดและภาษาเขียนได้ถูกต้อง เหมาะสม', 'field' => 'dept_teach_language']
            ];
            
            foreach ($teachingItems as $item):
            ?>
            <tr>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 30px;"><?= $item['num'] ?></td>
                <td style="border: 1px solid #333; padding: 6px;"><?= $item['text'] ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 5) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 4) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 3) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 2) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 1) ? '✓' : '' ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- ด้านที่ 3: การประเมินผล -->
            <tr style="background-color: #f8f9fa;">
                <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">3. ด้านความสามารถในการประเมินผล</td>
            </tr>
            <?php 
            $evaluationItems = [
                ['num' => 15, 'text' => 'วัดและประเมินผลด้วยวิธีการที่หลากหลาย', 'field' => 'dept_eval_variety'],
                ['num' => 16, 'text' => 'วัดและประเมินผลสอดคล้องกับมาตรฐานการเรียนรู้ ตัวชี้วัด และจุดประสงค์การเรียนรู้', 'field' => 'dept_eval_standards'],
                ['num' => 17, 'text' => 'มีเกณฑ์การวัดและประเมินผลที่ชัดเจน', 'field' => 'dept_eval_criteria'],
                ['num' => 18, 'text' => 'ให้ข้อมูลย้อนกลับแก่นักเรียนเพื่อการปรับปรุงหรือพัฒนา', 'field' => 'dept_eval_feedback'],
                ['num' => 19, 'text' => 'มีผลงาน ชิ้นงาน ภาระงาน ซึ่งเป็นหลักฐานการเรียนรู้', 'field' => 'dept_eval_evidence']
            ];
            
            foreach ($evaluationItems as $item):
            ?>
            <tr>
                <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= $item['num'] ?></td>
                <td style="border: 1px solid #333; padding: 6px;"><?= $item['text'] ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 5) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 4) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 3) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 2) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 1) ? '✓' : '' ?></td>
            </tr>
            <?php endforeach; ?>

            <tr style="background-color: #f8f9fa;">
                <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">4. ด้านความสามารถในการจัดสภาพแวดล้อมในชั้นเรียน</td>
            </tr>
            <?php 
            $environmentItems = [
                ['num' => 20, 'text' => 'จัดสภาพห้องเรียนได้อย่างเหมาะสม และเอื้อต่อการเรียนรู้ของนักเรียน', 'field' => 'dept_env_classroom'],
                ['num' => 21, 'text' => 'สร้างปฏิสัมพันธ์เชิงบวกในชั้นเรียน', 'field' => 'dept_env_interaction'],
                ['num' => 22, 'text' => 'จัดชั้นเรียนให้มีความปลอดภัย ไม่เสี่ยงต่อการเกิดอุบัติเหตุในระหว่างการจัดการเรียนการสอน', 'field' => 'dept_env_safety'],
                ['num' => 23, 'text' => 'มีความสามารถในการควบคุมชั้นเรียน', 'field' => 'dept_env_management'],
                ['num' => 24, 'text' => 'ชี้แจงกฎกติกาหรือข้อตกลงในการเรียน', 'field' => 'dept_env_rules'],
                ['num' => 25, 'text' => 'มีการดูแลพฤติกรรมของนักเรียนในชั้นเรียนอย่างใกล้ชิด', 'field' => 'dept_env_behavior']
            ];
            
            foreach ($environmentItems as $item):
            ?>
            <tr>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 30px;"><?= $item['num'] ?></td>
                <td style="border: 1px solid #333; padding: 6px;"><?= $item['text'] ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 5) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 4) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 3) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 2) ? '✓' : '' ?></td>
                <td style="border: 1px solid #333; padding: 6px; text-align: center; width: 25px;"><?= ($supervision[$item['field']] == 1) ? '✓' : '' ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- สรุปคะแนน -->
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td style="border: 1px solid #333; padding: 8px;" colspan="2">รวมคะแนนตามระดับคุณภาพการปฏิบัติ</td>
                <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['dept_plan_effective'], $supervision['dept_plan_correct'], $supervision['dept_plan_activities'], $supervision['dept_plan_media'], $supervision['dept_plan_assessment'], $supervision['dept_teach_techniques'], $supervision['dept_teach_media'], $supervision['dept_teach_assessment'], $supervision['dept_teach_explanation'], $supervision['dept_teach_control'], $supervision['dept_teach_thinking'], $supervision['dept_teach_adaptation'], $supervision['dept_teach_integration'], $supervision['dept_teach_language'], $supervision['dept_eval_variety'], $supervision['dept_eval_standards'], $supervision['dept_eval_criteria'], $supervision['dept_eval_feedback'], $supervision['dept_eval_evidence'], $supervision['dept_env_classroom'], $supervision['dept_env_interaction'], $supervision['dept_env_safety'], $supervision['dept_env_management'], $supervision['dept_env_rules'], $supervision['dept_env_behavior']], function($v) { return $v == 5; })) ?></td>
                <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['dept_plan_effective'], $supervision['dept_plan_correct'], $supervision['dept_plan_activities'], $supervision['dept_plan_media'], $supervision['dept_plan_assessment'], $supervision['dept_teach_techniques'], $supervision['dept_teach_media'], $supervision['dept_teach_assessment'], $supervision['dept_teach_explanation'], $supervision['dept_teach_control'], $supervision['dept_teach_thinking'], $supervision['dept_teach_adaptation'], $supervision['dept_teach_integration'], $supervision['dept_teach_language'], $supervision['dept_eval_variety'], $supervision['dept_eval_standards'], $supervision['dept_eval_criteria'], $supervision['dept_eval_feedback'], $supervision['dept_eval_evidence'], $supervision['dept_env_classroom'], $supervision['dept_env_interaction'], $supervision['dept_env_safety'], $supervision['dept_env_management'], $supervision['dept_env_rules'], $supervision['dept_env_behavior']], function($v) { return $v == 4; })) ?></td>
                <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['dept_plan_effective'], $supervision['dept_plan_correct'], $supervision['dept_plan_activities'], $supervision['dept_plan_media'], $supervision['dept_plan_assessment'], $supervision['dept_teach_techniques'], $supervision['dept_teach_media'], $supervision['dept_teach_assessment'], $supervision['dept_teach_explanation'], $supervision['dept_teach_control'], $supervision['dept_teach_thinking'], $supervision['dept_teach_adaptation'], $supervision['dept_teach_integration'], $supervision['dept_teach_language'], $supervision['dept_eval_variety'], $supervision['dept_eval_standards'], $supervision['dept_eval_criteria'], $supervision['dept_eval_feedback'], $supervision['dept_eval_evidence'], $supervision['dept_env_classroom'], $supervision['dept_env_interaction'], $supervision['dept_env_safety'], $supervision['dept_env_management'], $supervision['dept_env_rules'], $supervision['dept_env_behavior']], function($v) { return $v == 3; })) ?></td>
                <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['dept_plan_effective'], $supervision['dept_plan_correct'], $supervision['dept_plan_activities'], $supervision['dept_plan_media'], $supervision['dept_plan_assessment'], $supervision['dept_teach_techniques'], $supervision['dept_teach_media'], $supervision['dept_teach_assessment'], $supervision['dept_teach_explanation'], $supervision['dept_teach_control'], $supervision['dept_teach_thinking'], $supervision['dept_teach_adaptation'], $supervision['dept_teach_integration'], $supervision['dept_teach_language'], $supervision['dept_eval_variety'], $supervision['dept_eval_standards'], $supervision['dept_eval_criteria'], $supervision['dept_eval_feedback'], $supervision['dept_eval_evidence'], $supervision['dept_env_classroom'], $supervision['dept_env_interaction'], $supervision['dept_env_safety'], $supervision['dept_env_management'], $supervision['dept_env_rules'], $supervision['dept_env_behavior']], function($v) { return $v == 2; })) ?></td>
                <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['dept_plan_effective'], $supervision['dept_plan_correct'], $supervision['dept_plan_activities'], $supervision['dept_plan_media'], $supervision['dept_plan_assessment'], $supervision['dept_teach_techniques'], $supervision['dept_teach_media'], $supervision['dept_teach_assessment'], $supervision['dept_teach_explanation'], $supervision['dept_teach_control'], $supervision['dept_teach_thinking'], $supervision['dept_teach_adaptation'], $supervision['dept_teach_integration'], $supervision['dept_teach_language'], $supervision['dept_eval_variety'], $supervision['dept_eval_standards'], $supervision['dept_eval_criteria'], $supervision['dept_eval_feedback'], $supervision['dept_eval_evidence'], $supervision['dept_env_classroom'], $supervision['dept_env_interaction'], $supervision['dept_env_safety'], $supervision['dept_env_management'], $supervision['dept_env_rules'], $supervision['dept_env_behavior']], function($v) { return $v == 1; })) ?></td>
            </tr>
            
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td style="border: 1px solid #333; padding: 8px;" colspan="2">รวมคะแนนทุกรายการ</td>
                <td style="border: 1px solid #333; padding: 8px; text-align: center;" colspan="5">
                <?= $supervision['dept_score'] ?? 0 ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 30px;">
            <h4 style="font-weight: bold; margin-bottom: 15px;">ผลการนิเทศการจัดการเรียนรู้</h4>
            <p><strong>เกณฑ์การเปรียบเทียบระดับคุณภาพการปฏิบัติ</strong></p>
            <ul style="list-style: none; padding-left: 0;">
                <li>คุณภาพระหว่าง &nbsp;&nbsp;&nbsp;&nbsp; 98 - 125 &nbsp;&nbsp;&nbsp;&nbsp; หมายถึงเกณฑ์อยู่ในระดับ &nbsp;&nbsp;&nbsp;&nbsp; <strong>ดีเยี่ยม</strong></li>
                <li>คุณภาพระหว่าง &nbsp;&nbsp;&nbsp;&nbsp; 74 - 97 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึงเกณฑ์อยู่ในระดับ &nbsp;&nbsp;&nbsp;&nbsp; <strong>ดีมาก</strong></li>
                <li>คุณภาพระหว่าง &nbsp;&nbsp;&nbsp;&nbsp; 50 - 73 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึงเกณฑ์อยู่ในระดับ &nbsp;&nbsp;&nbsp;&nbsp; <strong>ดี</strong></li>
                <li>คุณภาพระหว่าง &nbsp;&nbsp;&nbsp;&nbsp; 26 – 49 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; หมายถึงเกณฑ์อยู่ในระดับ &nbsp;&nbsp;&nbsp;&nbsp; <strong>พอใช้</strong></li>
                <li>คุณภาพระหว่าง &nbsp;&nbsp;&nbsp;&nbsp; ต่ำกว่า 25 &nbsp;&nbsp;&nbsp;&nbsp; หมายถึงเกณฑ์อยู่ในระดับ &nbsp;&nbsp;&nbsp;&nbsp; <strong>ควรปรับปรุง</strong></li>
            </ul>
            
            <div style="margin-top: 20px;">
                <p><strong>สรุป</strong> คะแนนคุณภาพ = <strong><?= $supervision['dept_score'] ?? 0 ?></strong></p>
                <p>อยู่ในระดับคุณภาพ &nbsp;&nbsp;&nbsp;&nbsp; <strong><?= htmlspecialchars($supervision['dept_quality_level'] ?? '') ?></strong></p>
            </div>
        </div>
    </div>

    <!-- หน้าที่ 7 - ตอนที่ 3 ผู้นิเทศบันทึกเพิ่มเติม -->
    <div class="content-page">
        <div class="section">
            <div class="section-header">ตอนที่ 3 ผู้นิเทศบันทึกเพิ่มเติมการนิเทศการจัดการเรียนรู้</div>
            <div class="section-content">
                <div style="margin-bottom: 40px;">
                    <strong>1. สิ่งที่พบจากการสังเกตการจัดการเรียนรู้ในชั้นเรียนของผู้รับการนิเทศ</strong><br>
                    <div style="min-height: 80px; border-bottom: 1px dotted #333; padding: 10px 0;">
                        <?= nl2br(htmlspecialchars($supervision['dept_observation_notes'] ?? '')) ?>
                    </div>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <strong>2. การสะท้อนความคิดจากการจัดการเรียนรู้ในชั้นเรียนของผู้รับการนิเทศ</strong><br>
                    <div style="min-height: 80px; border-bottom: 1px dotted #333; padding: 10px 0;">
                        <?= nl2br(htmlspecialchars($supervision['dept_reflection_notes'] ?? '')) ?>
                    </div>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <strong>3. ความประทับใจหรือจุดเด่นในการจัดการเรียนรู้ครั้งนี้</strong><br>
                    <div style="min-height: 80px; border-bottom: 1px dotted #333; padding: 10px 0;">
                        <?= nl2br(htmlspecialchars($supervision['dept_strengths'] ?? '')) ?>
                    </div>
                </div>
                
                <div style="margin-bottom: 60px;">
                    <strong>4. สิ่งที่ควรปรับปรุงหรือพัฒนา</strong><br>
                    <div style="min-height: 80px; border-bottom: 1px dotted #333; padding: 10px 0;">
                        <?= nl2br(htmlspecialchars($supervision['dept_improvements'] ?? '')) ?>
                    </div>
                </div>
                
                <div style="margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 80px;">
                    <div style="text-align: center;">
                        <p>(ลงชื่อ) <?= str_repeat('.', 40) ?> ผู้รับการนิเทศ</p>
                        <p>(<?= htmlspecialchars($supervision['teacher_name'] ?? '') ?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                    </div>
                    <div style="text-align: center;">
                        <p>(ลงชื่อ) <?= str_repeat('.', 40) ?> ผู้นิเทศ</p>
                        <p>(<?= htmlspecialchars($supervision['dept_supervisor_signature'] ?? '') ?>)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- หน้าที่ 8 - แบบประเมินตนเองของครู -->
    <div class="content-page">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 20px;">แบบประเมินสมรรถนะการจัดการเรียนรู้ของครู (ครูประเมินตนเอง)</h2>
        </div>
        
        <div style="border: 1px solid #333; margin-bottom: 25px;">
            <div style="background-color: #f0f0f0; padding: 10px 15px; font-weight: bold; border-bottom: 1px solid #333;">
                แบบประเมินสมรรถนะการจัดการเรียนรู้ของผู้รับการนิเทศ (ครูประเมินตนเอง)
            </div>
            <div style="padding: 15px;">
                <div style="margin-bottom: 20px;">
                    <strong>คำชี้แจง</strong> ให้ครูประเมินตนเองในกระบวนการจัดการเรียนรู้ทั้ง 4 ด้าน แล้วทำเครื่องหมาย ✓ ในช่องที่มีการปฏิบัติมากที่สุดถึงน้อยที่สุด โดยใช้เกณฑ์ดังนี้<br>
                    <strong>5</strong> หมายถึง ดีมาก &nbsp;&nbsp;&nbsp;&nbsp; <strong>4</strong> หมายถึง ดี &nbsp;&nbsp;&nbsp;&nbsp; <strong>3</strong> หมายถึง ปานกลาง &nbsp;&nbsp;&nbsp;&nbsp; <strong>2</strong> หมายถึง พอใช้ &nbsp;&nbsp;&nbsp;&nbsp; <strong>1</strong> หมายถึง ควรปรับปรุง
                </div>
                
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #333; font-size: 12px;">
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #333; padding: 6px; text-align: center; width: 30px;">ที่</th>
                        <th style="border: 1px solid #333; padding: 6px; text-align: center;">รายการ</th>
                        <th style="border: 1px solid #333; padding: 6px; text-align: center;" colspan="5">ระดับการปฏิบัติ</th>
                    </tr>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #333; padding: 4px;"></th>
                        <th style="border: 1px solid #333; padding: 4px;"></th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">5</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">4</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">3</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">2</th>
                        <th style="border: 1px solid #333; padding: 4px; text-align: center; width: 25px;">1</th>
                    </tr>
                    
                    <!-- ด้านที่ 1: การจัดทำแผน (ครูประเมินตนเอง) -->
                    <tr style="background-color: #e8f4fd;">
                        <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">1. ด้านความสามารถในการจัดทำแผนการจัดการเรียนรู้</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">1</td>
                        <td style="border: 1px solid #333; padding: 6px;">การวางแผนการสอนที่มีประสิทธิภาพ</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_effective'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_effective'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_effective'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_effective'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_effective'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">2</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้ถูกต้อง เป็นขั้นตอน และครบองค์ประกอบ</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_correct'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_correct'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_correct'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_correct'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_correct'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">3</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้มีกิจกรรมที่ทำให้นักเรียนเกิดการเรียนรู้</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_activities'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_activities'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_activities'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_activities'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_activities'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">4</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้มีการจัดหาสื่อที่เหมาะสมกับการเรียนรู้ของนักเรียน</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_media'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_media'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_media'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_media'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_media'] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;">5</td>
                        <td style="border: 1px solid #333; padding: 6px;">แผนการจัดการเรียนรู้มีการวัดและประเมินผลผู้เรียนได้อย่างเหมาะสม</td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_assessment'] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_assessment'] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_assessment'] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_assessment'] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision['plan_assessment'] == 1) ? '✓' : '' ?></td>
                    </tr>

                    <!-- ด้านที่ 2: การจัดการเรียนรู้ (ครูประเมินตนเอง) -->
                    <tr style="background-color: #e8f4fd;">
                        <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">2. ด้านความสามารถในการจัดการเรียนรู้</td>
                    </tr>
                    <?php 
                    $teacherSelfItems = [
                        ['num' => 6, 'text' => 'ใช้เทคนิคต่าง ๆ ที่ทำให้นักเรียนทุกคนมีส่วนร่วมในชั้นเรียน', 'field' => 'teach_techniques'],
                        ['num' => 7, 'text' => 'เลือกใช้สื่อ เทคโนโลยีและอุปกรณ์การสอนที่เหมาะสม', 'field' => 'teach_media'],
                        ['num' => 8, 'text' => 'มีการประเมินนักเรียนระหว่างเรียน', 'field' => 'teach_assessment'],
                        ['num' => 9, 'text' => 'อธิบายเนื้อหาบทเรียนได้อย่างชัดเจน', 'field' => 'teach_explanation'],
                        ['num' => 10, 'text' => 'มีความสามารถในการควบคุมชั้นเรียนเมื่อทำกิจกรรม', 'field' => 'teach_control'],
                        ['num' => 11, 'text' => 'มีการจัดกิจกรรมการเรียนรู้ที่เน้นการพัฒนาการคิด ได้อภิปราย ซักถาม และแสดงความคิดเห็น', 'field' => 'teach_thinking'],
                        ['num' => 12, 'text' => 'มีการปรับเนื้อหา กิจกรรมในขณะจัดการเรียนรู้เพื่อให้เหมาะสมตามสถานการณ์หรือให้ทันเวลาที่เหลือ', 'field' => 'teach_adaptation'],
                        ['num' => 13, 'text' => 'มีกิจกรรมการเรียนการสอนที่เชื่อมโยงหรือบูรณาการกับชีวิตประจำวัน สอดแทรกคุณธรรม จริยธรรมระหว่างเรียน', 'field' => 'teach_integration'],
                        ['num' => 14, 'text' => 'ใช้ภาษาพูดและภาษาเขียนได้ถูกต้อง เหมาะสม', 'field' => 'teach_language']
                    ];
                    
                    foreach ($teacherSelfItems as $item):
                    ?>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= $item['num'] ?></td>
                        <td style="border: 1px solid #333; padding: 6px;"><?= $item['text'] ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- ด้านที่ 3: การประเมินผล (ครูประเมินตนเอง) -->
                    <tr style="background-color: #e8f4fd;">
                        <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">3. ด้านความสามารถในการประเมินผล</td>
                    </tr>
                    <?php 
                    $teacherEvalItems = [
                        ['num' => 15, 'text' => 'วัดและประเมินผลด้วยวิธีการที่หลากหลาย', 'field' => 'eval_variety'],
                        ['num' => 16, 'text' => 'วัดและประเมินผลสอดคล้องกับมาตรฐานการเรียนรู้ ตัวชี้วัด และจุดประสงค์การเรียนรู้', 'field' => 'eval_standards'],
                        ['num' => 17, 'text' => 'มีเกณฑ์การวัดและประเมินผลที่ชัดเจน', 'field' => 'eval_criteria'],
                        ['num' => 18, 'text' => 'ให้ข้อมูลย้อนกลับแก่นักเรียนเพื่อการปรับปรุงหรือพัฒนา', 'field' => 'eval_feedback'],
                        ['num' => 19, 'text' => 'มีผลงาน ชิ้นงาน ภาระงาน ซึ่งเป็นหลักฐานการเรียนรู้', 'field' => 'eval_evidence']
                    ];
                    
                    foreach ($teacherEvalItems as $item):
                    ?>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= $item['num'] ?></td>
                        <td style="border: 1px solid #333; padding: 6px;"><?= $item['text'] ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- ด้านที่ 4: สภาพแวดล้อม (ครูประเมินตนเอง) -->
                    <tr style="background-color: #e8f4fd;">
                        <td style="border: 1px solid #333; padding: 6px; font-weight: bold;" colspan="7">4. ด้านความสามารถในการจัดสภาพแวดล้อมในชั้นเรียน</td>
                    </tr>
                    <?php 
                    $teacherEnvItems = [
                        ['num' => 20, 'text' => 'จัดสภาพห้องเรียนได้อย่างเหมาะสม และเอื้อต่อการเรียนรู้ของนักเรียน', 'field' => 'env_classroom'],
                        ['num' => 21, 'text' => 'สร้างปฏิสัมพันธ์เชิงบวกในชั้นเรียน', 'field' => 'env_interaction'],
                        ['num' => 22, 'text' => 'จัดชั้นเรียนให้มีความปลอดภัย ไม่เสี่ยงต่อการเกิดอุบัติเหตุในระหว่างการจัดการเรียนการสอน', 'field' => 'env_safety'],
                        ['num' => 23, 'text' => 'มีความสามารถในการควบคุมชั้นเรียน', 'field' => 'env_management'],
                        ['num' => 24, 'text' => 'ชี้แจงกฎกติกาหรือข้อตกลงในการเรียน', 'field' => 'env_rules'],
                        ['num' => 25, 'text' => 'มีการดูแลพฤติกรรมของนักเรียนในชั้นเรียนอย่างใกล้ชิด', 'field' => 'env_behavior']
                    ];
                    
                    foreach ($teacherEnvItems as $item):
                    ?>
                    <tr>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= $item['num'] ?></td>
                        <td style="border: 1px solid #333; padding: 6px;"><?= $item['text'] ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 5) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 4) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 3) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 2) ? '✓' : '' ?></td>
                        <td style="border: 1px solid #333; padding: 6px; text-align: center;"><?= ($supervision[$item['field']] == 1) ? '✓' : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- สรุปคะแนนครูประเมินตนเอง -->
                    <tr style="background-color: #e8f4fd; font-weight: bold;">
                        <td style="border: 1px solid #333; padding: 8px;" colspan="2">รวมคะแนนตามระดับคุณภาพการปฏิบัติ</td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['plan_effective'], $supervision['plan_correct'], $supervision['plan_activities'], $supervision['plan_media'], $supervision['plan_assessment'], $supervision['teach_techniques'], $supervision['teach_media'], $supervision['teach_assessment'], $supervision['teach_explanation'], $supervision['teach_control'], $supervision['teach_thinking'], $supervision['teach_adaptation'], $supervision['teach_integration'], $supervision['teach_language'], $supervision['eval_variety'], $supervision['eval_standards'], $supervision['eval_criteria'], $supervision['eval_feedback'], $supervision['eval_evidence'], $supervision['env_classroom'], $supervision['env_interaction'], $supervision['env_safety'], $supervision['env_management'], $supervision['env_rules'], $supervision['env_behavior']], function($v) { return $v == 5; })) ?></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['plan_effective'], $supervision['plan_correct'], $supervision['plan_activities'], $supervision['plan_media'], $supervision['plan_assessment'], $supervision['teach_techniques'], $supervision['teach_media'], $supervision['teach_assessment'], $supervision['teach_explanation'], $supervision['teach_control'], $supervision['teach_thinking'], $supervision['teach_adaptation'], $supervision['teach_integration'], $supervision['teach_language'], $supervision['eval_variety'], $supervision['eval_standards'], $supervision['eval_criteria'], $supervision['eval_feedback'], $supervision['eval_evidence'], $supervision['env_classroom'], $supervision['env_interaction'], $supervision['env_safety'], $supervision['env_management'], $supervision['env_rules'], $supervision['env_behavior']], function($v) { return $v == 4; })) ?></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['plan_effective'], $supervision['plan_correct'], $supervision['plan_activities'], $supervision['plan_media'], $supervision['plan_assessment'], $supervision['teach_techniques'], $supervision['teach_media'], $supervision['teach_assessment'], $supervision['teach_explanation'], $supervision['teach_control'], $supervision['teach_thinking'], $supervision['teach_adaptation'], $supervision['teach_integration'], $supervision['teach_language'], $supervision['eval_variety'], $supervision['eval_standards'], $supervision['eval_criteria'], $supervision['eval_feedback'], $supervision['eval_evidence'], $supervision['env_classroom'], $supervision['env_interaction'], $supervision['env_safety'], $supervision['env_management'], $supervision['env_rules'], $supervision['env_behavior']], function($v) { return $v == 3; })) ?></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['plan_effective'], $supervision['plan_correct'], $supervision['plan_activities'], $supervision['plan_media'], $supervision['plan_assessment'], $supervision['teach_techniques'], $supervision['teach_media'], $supervision['teach_assessment'], $supervision['teach_explanation'], $supervision['teach_control'], $supervision['teach_thinking'], $supervision['teach_adaptation'], $supervision['teach_integration'], $supervision['teach_language'], $supervision['eval_variety'], $supervision['eval_standards'], $supervision['eval_criteria'], $supervision['eval_feedback'], $supervision['eval_evidence'], $supervision['env_classroom'], $supervision['env_interaction'], $supervision['env_safety'], $supervision['env_management'], $supervision['env_rules'], $supervision['env_behavior']], function($v) { return $v == 2; })) ?></td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;"><?= array_sum(array_filter([$supervision['plan_effective'], $supervision['plan_correct'], $supervision['plan_activities'], $supervision['plan_media'], $supervision['plan_assessment'], $supervision['teach_techniques'], $supervision['teach_media'], $supervision['teach_assessment'], $supervision['teach_explanation'], $supervision['teach_control'], $supervision['teach_thinking'], $supervision['teach_adaptation'], $supervision['teach_integration'], $supervision['teach_language'], $supervision['eval_variety'], $supervision['eval_standards'], $supervision['eval_criteria'], $supervision['eval_feedback'], $supervision['eval_evidence'], $supervision['env_classroom'], $supervision['env_interaction'], $supervision['env_safety'], $supervision['env_management'], $supervision['env_rules'], $supervision['env_behavior']], function($v) { return $v == 1; })) ?></td>
                    </tr>
                    
                    <tr style="background-color: #e8f4fd; font-weight: bold;">
                        <td style="border: 1px solid #333; padding: 8px;" colspan="2">รวมคะแนนทุกรายการ</td>
                        <td style="border: 1px solid #333; padding: 8px; text-align: center;" colspan="5"><?= $supervision['total_score'] ?? 0 ?></td>
                    </tr>
                </table>
                
                <br><br>
                
                <div style="margin-top: 30px; padding: 20px; border: 1px solid #333; background-color: #f8f9fa;">
                    <h4 style="font-weight: bold; margin-bottom: 20px; text-align: center; font-size: 16px;">ผลการประเมินตนเองของครู</h4>
                    
                    <div style="margin-bottom: 20px;">
                        <p style="font-weight: bold; margin-bottom: 10px;">เกณฑ์การเปรียบเทียบระดับคุณภาพการปฏิบัติ</p>
                        
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <td style="padding: 5px 0; width: 30%;">คุณภาพระหว่าง</td>
                                <td style="padding: 5px 0; width: 20%; text-align: center; font-weight: bold;">98 - 125</td>
                                <td style="padding: 5px 0; width: 20%;">หมายถึงเกณฑ์อยู่ในระดับ</td>
                                <td style="padding: 5px 0; width: 30%; font-weight: bold;">ดีเยี่ยม</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">คุณภาพระหว่าง</td>
                                <td style="padding: 5px 0; text-align: center; font-weight: bold;">74 - 97</td>
                                <td style="padding: 5px 0;">หมายถึงเกณฑ์อยู่ในระดับ</td>
                                <td style="padding: 5px 0; font-weight: bold;">ดีมาก</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">คุณภาพระหว่าง</td>
                                <td style="padding: 5px 0; text-align: center; font-weight: bold;">50 - 73</td>
                                <td style="padding: 5px 0;">หมายถึงเกณฑ์อยู่ในระดับ</td>
                                <td style="padding: 5px 0; font-weight: bold;">ดี</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">คุณภาพระหว่าง</td>
                                <td style="padding: 5px 0; text-align: center; font-weight: bold;">26 - 49</td>
                                <td style="padding: 5px 0;">หมายถึงเกณฑ์อยู่ในระดับ</td>
                                <td style="padding: 5px 0; font-weight: bold;">พอใช้</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">คุณภาพระหว่าง</td>
                                <td style="padding: 5px 0; text-align: center; font-weight: bold;">ต่ำกว่า 25</td>
                                <td style="padding: 5px 0;">หมายถึงเกณฑ์อยู่ในระดับ</td>
                                <td style="padding: 5px 0; font-weight: bold;">ควรปรับปรุง</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style="margin-top: 30px; text-align: center; padding: 15px; background-color: #dbeafe; border: 2px solid #3b82f6; border-radius: 5px;">
                        <p style="font-size: 16px; margin-bottom: 10px;"><strong>สรุป</strong> คะแนนคุณภาพ = <strong style="font-size: 18px; color: #3b82f6;"><?= $supervision['total_score'] ?? 0 ?></strong></p>
                        <p style="font-size: 16px; margin: 0;"><strong>อยู่ในระดับคุณภาพ</strong> <strong style="font-size: 18px; color: #1d4ed8;"><?= htmlspecialchars($supervision['quality_level'] ?? '') ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- หน้าที่ 9 - แผนการจัดการเรียนรู้ (PDF) -->
    <div class="content-page">
        
        
        <!-- Additional Documents Section -->
        <?php if (!empty($supervision['supervisor_photos']) || !empty($supervision['classroom_photos'])): ?>
        <div style="border: 1px solid #333; margin-bottom: 25px;">
            <div style="background-color: #f0f0f0; padding: 10px 15px; font-weight: bold; border-bottom: 1px solid #333;">
                เอกสารประกอบอื่น ๆ
            </div>
            <div style="padding: 15px;">
                
                <?php if (!empty($supervision['supervisor_photos'])): ?>
                <div style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 15px; font-size: 16px; color: #333;">📸 ภาพการนิเทศ</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <?php 
                        $supervisorPhotos = explode(',', $supervision['supervisor_photos']);
                        foreach ($supervisorPhotos as $photo): 
                            if (trim($photo)):
                                $photoPath = '../' . trim($photo);
                                $photoName = basename(trim($photo));
                        ?>
                        <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <div style="position: relative; width: 100%; height: 200px; overflow: hidden; background-color: #f5f5f5;">
                                <?php if (file_exists($photoPath)): ?>
                                <img src="<?= htmlspecialchars(trim($photo)) ?>" 
                                     alt="ภาพการนิเทศ" 
                                     style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; flex-direction: column; color: #666; position: absolute; top: 0; left: 0; background-color: #f5f5f5;">
                                    <div style="font-size: 40px; margin-bottom: 10px;">📷</div>
                                    <div style="font-size: 14px;">ไม่สามารถแสดงภาพได้</div>
                                </div>
                                <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #666; background-color: #f5f5f5;">
                                    <div style="font-size: 40px; margin-bottom: 10px;">📷</div>
                                    <div style="font-size: 14px;">ไม่พบไฟล์ภาพ</div>
                                </div>
                                <?php endif; ?>
                            </div>

                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($supervision['classroom_photos'])): ?>
                <div style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 15px; font-size: 16px; color: #333;">🏫 ภาพบรรยากาศในชั้นเรียน</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <?php 
                        $classroomPhotos = explode(',', $supervision['classroom_photos']);
                        foreach ($classroomPhotos as $photo): 
                            if (trim($photo)):
                                $photoPath = '../' . trim($photo);
                                $photoName = basename(trim($photo));
                        ?>
                        <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <div style="position: relative; width: 100%; height: 200px; overflow: hidden; background-color: #f5f5f5;">
                                <?php if (file_exists($photoPath)): ?>
                                <img src="../<?= htmlspecialchars(trim($photo)) ?>" 
                                     alt="ภาพบรรยากาศในชั้นเรียน" 
                                     style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; flex-direction: column; color: #666; position: absolute; top: 0; left: 0; background-color: #f5f5f5;">
                                    <div style="font-size: 40px; margin-bottom: 10px;">🏫</div>
                                    <div style="font-size: 14px;">ไม่สามารถแสดงภาพได้</div>
                                </div>
                                <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #666; background-color: #f5f5f5;">
                                    <div style="font-size: 40px; margin-bottom: 10px;">🏫</div>
                                    <div style="font-size: 14px;">ไม่พบไฟล์ภาพ</div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 10px; text-align: center; background-color: #f8f9fa;">
                                <div style="font-size: 12px; color: #666; word-break: break-all;">
                                    <?= htmlspecialchars($photoName) ?>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>
    </div>

    

    <script>
        // Auto print when page loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
        
        // Enhanced photo loading with fallback
        document.addEventListener('DOMContentLoaded', function() {
            const photoContainer = document.querySelector('.teacher-photo-inner');
            const fallback = document.querySelector('.teacher-photo-fallback');
            
            // Check if teacher photo exists
            const img = new Image();
            img.onload = function() {
                if (fallback) {
                    fallback.style.display = 'none';
                }
            };
            img.onerror = function() {
                if (fallback) {
                    fallback.style.display = 'flex';
                }
            };
            img.src = 'https://std.phichai.ac.th/teacher/uploads/phototeach/<?=$TeacherData['Teach_photo']?>';
        });
        
        // Handle PDF loading errors
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.querySelector('iframe');
            if (iframe) {
                iframe.addEventListener('error', function() {
                    console.log('PDF loading failed');
                });
            }
        });
    </script>
    
    <style>
        @media print {
            .no-print { 
                display: none; 
            }
            .print-only {
                display: block !important;
            }
            iframe {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            
            /* Ensure images print properly */
            img {
                max-width: 100% !important;
                height: auto !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        
        @media screen {
            .print-only {
                display: none;
            }
        }
        
        /* Enhanced image styling */
        img {
            transition: transform 0.3s ease;
        }
        
        img:hover {
            transform: scale(1.02);
        }
    </style>
</body>
</html>
