<?php
session_start();

require_once '../classes/DatabaseTeachingReport.php';
require_once '../classes/DatabaseUsers.php';
require_once '../models/Certificate.php';

use App\Models\Certificate;

class CertificateController {
    private $certificateModel;

    public function __construct() {
        $this->certificateModel = new Certificate();
    }

    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('ไม่พบข้อมูลที่ส่งมา');
            }

            $this->validateInput($input);

            $students = $input['students'] ?? [];
            $commonData = [
                'award_type' => $input['award_type'],
                'award_detail' => $input['award_detail'],
                'award_date' => $input['award_date'],
                'note' => $input['note'] ?? '',
                'term' => $input['term'] ?? null,
                'year' => $input['year'] ?? null,
                'teacher_id' => $_SESSION['user']['Teach_id'] ?? null
            ];

            $insertedIds = $this->certificateModel->createMultiple($students, $commonData);

            $this->sendResponse([
                'success' => true,
                'message' => 'บันทึกเกียรติบัตรสำหรับนักเรียน ' . count($students) . ' คน เรียบร้อยแล้ว',
                'inserted_ids' => $insertedIds,
                'count' => count($insertedIds),
                'term_info' => [
                    'term' => $input['term'],
                    'year' => $input['year']
                ]
            ]);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function list() {
        try {
            $teacherId = $_GET['teacherId'] ?? null;
            $certificates = $this->certificateModel->getAll($teacherId);

            $this->sendResponse($certificates);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function detail() {
        try {
            $id = $_GET['id'] ?? null;

            if (!$id) {
                throw new Exception('ไม่พบ ID ที่ต้องการดู');
            }

            $certificate = $this->certificateModel->getById($id);
            $this->sendResponse($certificate);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('ไม่พบข้อมูลที่ส่งมา');
            }

            $id = $input['id'] ?? null;
            if (!$id) {
                throw new Exception('ไม่พบ ID ที่ต้องการแก้ไข');
            }

            $this->validateInput($input);

            $student = $input['students'][0] ?? [];
            $data = [
                'student_name' => $student['name'],
                'student_class' => $student['class'],
                'student_room' => $student['room'],
                'award_type' => $input['award_type'],
                'award_detail' => $input['award_detail'],
                'award_date' => $input['award_date'],
                'note' => $input['note'] ?? '',
                'term' => $input['term'] ?? null,
                'year' => $input['year'] ?? null,
                'certificate_image' => $student['certificate_image'] ?? null
            ];

            $success = $this->certificateModel->update($id, $data);

            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'แก้ไขเกียรติบัตรเรียบร้อยแล้ว'
                ]);
            } else {
                throw new Exception('ไม่สามารถแก้ไขเกียรติบัตรได้');
            }

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;

            if (!$id) {
                throw new Exception('ไม่พบ ID ที่ต้องการลบ');
            }

            $success = $this->certificateModel->delete($id);

            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'ลบเกียรติบัตรเรียบร้อยแล้ว'
                ]);
            } else {
                throw new Exception('ไม่สามารถลบเกียรติบัตรได้');
            }

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function uploadImage() {
        try {
            if (!isset($_FILES['certificate_image'])) {
                throw new Exception('ไม่พบไฟล์ที่อัพโหลด');
            }

            $fileName = $this->certificateModel->uploadImage($_FILES['certificate_image']);

            $this->sendResponse([
                'success' => true,
                'filename' => $fileName,
                'message' => 'อัพโหลดรูปภาพเรียบร้อยแล้ว'
            ]);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function statistics() {
        try {
            $teacherId = $_GET['teacherId'] ?? null;
            $stats = $this->certificateModel->getStatistics($teacherId);

            $this->sendResponse([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function search() {
        try {
            $searchTerm = $_GET['term'] ?? '';
            $teacherId = $_GET['teacherId'] ?? null;
            $termFilter = $_GET['termFilter'] ?? null;
            $yearFilter = $_GET['yearFilter'] ?? null;
            $classFilter = $_GET['classFilter'] ?? null;
            $awardFilter = $_GET['awardFilter'] ?? null;
            
            $certificates = $this->certificateModel->searchCertificates(
                $searchTerm, 
                $teacherId, 
                $termFilter, 
                $yearFilter, 
                $classFilter, 
                $awardFilter
            );
            
            $this->sendResponse([
                'success' => true,
                'data' => $certificates,
                'count' => count($certificates)
            ]);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function topStudents() {
        try {
            $teacherId = $_GET['teacherId'] ?? null;
            $limit = $_GET['limit'] ?? 10;
            
            $students = $this->certificateModel->getTopStudents($teacherId, $limit);
            
            $this->sendResponse([
                'success' => true,
                'data' => $students
            ]);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function recent() {
        try {
            $teacherId = $_GET['teacherId'] ?? null;
            $limit = $_GET['limit'] ?? 5;
            
            $certificates = $this->certificateModel->getRecentCertificates($teacherId, $limit);
            
            $this->sendResponse([
                'success' => true,
                'data' => $certificates
            ]);

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function export() {
        try {
            $teacherId = $_GET['teacherId'] ?? null;
            $format = $_GET['format'] ?? 'csv';

            $certificates = $this->certificateModel->getAll($teacherId);

            switch ($format) {
                case 'csv':
                    $this->exportCSV($certificates);
                    break;
                case 'json':
                    $this->exportJSON($certificates);
                    break;
                case 'excel':
                case 'xlsx':
                    $this->exportExcel($certificates);
                    break;
                case 'pdf':
                    $this->exportPDF($certificates);
                    break;
                default:
                    throw new Exception('รูปแบบการส่งออกไม่ถูกต้อง');
            }

        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function termInfo() {
        try {
            $termInfo = $this->certificateModel->getCurrentTermInfo();
            $this->sendResponse([
                'success' => true,
                'data' => $termInfo
            ]);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function availableTerms() {
        try {
            $teacherId = $_GET['teacherId'] ?? null;
            $terms = $this->certificateModel->getAvailableTermsAndYears($teacherId);
            
            $this->sendResponse([
                'success' => true,
                'data' => $terms
            ]);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function exportCSV($certificates) {
        // Set proper headers for Thai language support
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="certificates_' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 to ensure proper Thai display in Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers in Thai
        fputcsv($output, [
            'ลำดับ',
            'ชื่อนักเรียน',
            'ระดับชั้น',
            'ห้อง',
            'ประเภทรางวัล',
            'รายละเอียดรางวัล',
            'วันที่ได้รับรางวัล',
            'ภาคเรียน',
            'ปีการศึกษา',
            'หมายเหตุ',
            'ผู้บันทึก',
            'วันที่บันทึก'
        ]);
        
        // Data with proper encoding
        foreach ($certificates as $index => $cert) {
            fputcsv($output, [
                $index + 1,
                $cert['student_name'],
                $cert['student_class'],
                $cert['student_room'],
                $cert['award_type'],
                $cert['award_detail'],
                $cert['award_date'],
                $cert['term'] ?? '-',
                $cert['year'] ?? '-',
                $cert['note'] ?? '',
                $cert['teacher_name'] ?? '-',
                $cert['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }

    private function exportJSON($certificates) {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="certificates_' . date('Y-m-d') . '.json"');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode([
            'export_date' => date('Y-m-d H:i:s'),
            'total_records' => count($certificates),
            'certificates' => $certificates
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // เพิ่มฟังก์ชันสำหรับ Excel
    private function exportExcel($certificates) {
        try {
            // ตรวจสอบว่ามี autoload หรือไม่
            $autoloadPath = __DIR__ . '/../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                throw new Exception('กรุณาติดตั้ง dependencies ด้วยคำสั่ง: composer install');
            }
            
            require_once $autoloadPath;
            
            // ตรวจสอบว่า class มีอยู่หรือไม่
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                throw new Exception('ไม่พบ PhpSpreadsheet library กรุณาติดตั้งด้วย: composer require phpoffice/phpspreadsheet');
            }
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // ตั้งค่าฟอนต์ไทย - ใช้ฟอนต์ที่รองรับภาษาไทย
            $defaultFont = 'Tahoma'; // ฟอนต์ที่รองรับภาษาไทยและมีในระบบส่วนใหญ่
            $sheet->getDefaultStyle()->getFont()->setName($defaultFont)->setSize(14);

            // ตั้งค่า encoding สำหรับ worksheet
            $sheet->setTitle('เกียรติบัตร');

            // Header with Thai text
            $headers = [
                'ลำดับ', 'ชื่อนักเรียน', 'ระดับชั้น', 'ห้อง', 'ประเภทรางวัล', 'รายละเอียดรางวัล',
                'วันที่ได้รับรางวัล', 'ภาคเรียน', 'ปีการศึกษา', 'หมายเหตุ', 'ผู้บันทึก', 'วันที่บันทึก'
            ];
            
            // Set headers with formatting
            foreach ($headers as $col => $header) {
                $cell = $sheet->setCellValue(chr(65 + $col) . '1', $header);
                $sheet->getStyle(chr(65 + $col) . '1')->getFont()->setBold(true);
                $sheet->getStyle(chr(65 + $col) . '1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE6E6FA');
            }

            // Data
            $row = 2;
            foreach ($certificates as $index => $cert) {
                $rowData = [
                    $index + 1,
                    $cert['student_name'],
                    $cert['student_class'],
                    $cert['student_room'],
                    $cert['award_type'],
                    $cert['award_detail'],
                    $cert['award_date'],
                    $cert['term'] ?? '-',
                    $cert['year'] ?? '-',
                    $cert['note'] ?? '',
                    $cert['teacher_name'] ?? '-',
                    $cert['created_at']
                ];
                
                foreach ($rowData as $col => $data) {
                    $sheet->setCellValue(chr(65 + $col) . $row, $data);
                }
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set proper headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="certificates_' . date('Y-m-d') . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: no-cache, must-revalidate');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            error_log('Excel export error: ' . $e->getMessage());
            // Fallback to CSV if Excel export fails
            $this->exportCSVFallback($certificates, 'xlsx');
        }
    }

    // เพิ่มฟังก์ชันสำหรับ PDF
    private function exportPDF($certificates) {
        try {
            // ตรวจสอบว่ามี autoload หรือไม่
            $autoloadPath = __DIR__ . '/../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                throw new Exception('กรุณาติดตั้ง dependencies ด้วยคำสั่ง: composer install');
            }
            
            require_once $autoloadPath;
            
            // ตรวจสอบว่า class มีอยู่หรือไม่
            if (!class_exists('Mpdf\Mpdf')) {
                throw new Exception('ไม่พบ mPDF library กรุณาติดตั้งด้วย: composer require mpdf/mpdf');
            }
            
            // Configuration for Thai language support
            $config = [
                'mode' => 'utf-8',
                'format' => 'A4-L', // Landscape for better table display
                'default_font_size' => 12,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 20,
                'tempDir' => sys_get_temp_dir()
            ];

            // Try to use DejaVu Sans font (good Thai support) or fallback to basic fonts
            try {
                $config['default_font'] = 'dejavusans';
            } catch (Exception $e) {
                // Fallback to basic font
                $config['default_font'] = 'sans-serif';
            }

            $mpdf = new \Mpdf\Mpdf($config);
            
            // Set document properties
            $mpdf->SetCreator('CK Tech Certificate System');
            $mpdf->SetTitle('รายงานเกียรติบัตร');
            
            // HTML content with proper Thai encoding
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: "dejavusans", sans-serif; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
                    .date { font-size: 12px; color: #666; }
                    table { width: 100%; border-collapse: collapse; font-size: 10px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
                    .center { text-align: center; }
                    .truncate { max-width: 150px; word-wrap: break-word; }
                </style>
            </head>
            <body>';
            
            $html .= '<div class="header">';
            $html .= '<div class="title">รายงานเกียรติบัตรนักเรียน</div>';
            $html .= '<div class="date">วันที่ออกรายงาน: ' . date('d/m/Y H:i:s') . '</div>';
            $html .= '<div class="date">จำนวนรายการทั้งหมด: ' . count($certificates) . ' รายการ</div>';
            $html .= '</div>';
            
            $html .= '<table>';
            $html .= '<thead><tr>';
            $headers = [
                'ลำดับ', 'ชื่อนักเรียน', 'ชั้น/ห้อง', 'ประเภทรางวัล', 'รายละเอียด',
                'วันที่ได้รับ', 'ภาค/ปี', 'ผู้บันทึก'
            ];
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            
            foreach ($certificates as $index => $cert) {
                $html .= '<tr>';
                $html .= '<td class="center">' . ($index + 1) . '</td>';
                $html .= '<td>' . htmlspecialchars($cert['student_name'], ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td class="center">' . htmlspecialchars($cert['student_class'], ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($cert['student_room'], ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($cert['award_type'], ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td class="truncate">' . htmlspecialchars(mb_substr($cert['award_detail'], 0, 50, 'UTF-8') . (mb_strlen($cert['award_detail'], 'UTF-8') > 50 ? '...' : ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td class="center">' . htmlspecialchars($cert['award_date'], ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td class="center">' . htmlspecialchars(($cert['term'] ?? '-') . '/' . ($cert['year'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($cert['teacher_name'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody></table>';
            $html .= '</body></html>';
            
            $mpdf->WriteHTML($html);
            
            // Set headers for download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="certificates_' . date('Y-m-d') . '.pdf"');
            header('Cache-Control: no-cache, must-revalidate');
            
            $mpdf->Output('certificates_' . date('Y-m-d') . '.pdf', 'D');
            exit;
            
        } catch (Exception $e) {
            error_log('PDF export error: ' . $e->getMessage());
            // Fallback to HTML export if PDF fails
            $this->exportHTMLFallback($certificates);
        }
    }

    // Fallback function for Excel export (exports as CSV with Excel-like formatting)
    private function exportCSVFallback($certificates, $requestedFormat = 'excel') {
        // Set proper headers for Excel-compatible CSV
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="certificates_' . date('Y-m-d') . '_fallback.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 to ensure proper Thai display in Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add notice about fallback
        fputcsv($output, ['*** ไม่สามารถส่งออกเป็น ' . strtoupper($requestedFormat) . ' ได้ กรุณาติดตั้ง PHP libraries ***']);
        fputcsv($output, ['*** ข้อมูลนี้ส่งออกเป็น CSV แทน ***']);
        fputcsv($output, []);
        
        // Headers in Thai
        fputcsv($output, [
            'ลำดับ',
            'ชื่อนักเรียน',
            'ระดับชั้น',
            'ห้อง',
            'ประเภทรางวัล',
            'รายละเอียดรางวัล',
            'วันที่ได้รับรางวัล',
            'ภาคเรียน',
            'ปีการศึกษา',
            'หมายเหตุ',
            'ผู้บันทึก',
            'วันที่บันทึก'
        ]);
        
        // Data with proper encoding
        foreach ($certificates as $index => $cert) {
            fputcsv($output, [
                $index + 1,
                $cert['student_name'],
                $cert['student_class'],
                $cert['student_room'],
                $cert['award_type'],
                $cert['award_detail'],
                $cert['award_date'],
                $cert['term'] ?? '-',
                $cert['year'] ?? '-',
                $cert['note'] ?? '',
                $cert['teacher_name'] ?? '-',
                $cert['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }

    // Fallback function for PDF export (exports as HTML)
    private function exportHTMLFallback($certificates) {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="certificates_' . date('Y-m-d') . '_report.html"');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo '<!DOCTYPE html>
        <html lang="th">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>รายงานเกียรติบัตร</title>
            <style>
                body { font-family: "Tahoma", sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; color: #333; }
                .subtitle { font-size: 16px; color: #666; margin-bottom: 5px; }
                .notice { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
                table { width: 100%; border-collapse: collapse; font-size: 14px; }
                th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; text-align: center; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .center { text-align: center; }
                .no-wrap { white-space: nowrap; }
                @media print {
                    .notice { display: none; }
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">รายงานเกียรติบัตรนักเรียน</div>
                <div class="subtitle">วันที่ออกรายงาน: ' . date('d/m/Y H:i:s') . '</div>
                <div class="subtitle">จำนวนรายการทั้งหมด: ' . count($certificates) . ' รายการ</div>
            </div>
            
            
            <table>
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อนักเรียน</th>
                        <th>ชั้น/ห้อง</th>
                        <th>ประเภทรางวัล</th>
                        <th>รายละเอียด</th>
                        <th>วันที่ได้รับ</th>
                        <th>ภาค/ปี</th>
                        <th>ผู้บันทึก</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($certificates as $index => $cert) {
            echo '<tr>';
            echo '<td class="center">' . ($index + 1) . '</td>';
            echo '<td>' . htmlspecialchars($cert['student_name'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td class="center no-wrap">' . htmlspecialchars($cert['student_class'], ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($cert['student_room'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($cert['award_type'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($cert['award_detail'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td class="center no-wrap">' . htmlspecialchars($cert['award_date'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td class="center no-wrap">' . htmlspecialchars(($cert['term'] ?? '-') . '/' . ($cert['year'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($cert['teacher_name'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>
            </table>
            
            <script>
                // Auto-print option
                if (confirm("ต้องการพิมพ์รายงานนี้หรือไม่?")) {
                    window.print();
                }
            </script>
        </body>
        </html>';
        exit;
    }

    private function validateInput($input) {
        $students = $input['students'] ?? [];
        $awardType = $input['award_type'] ?? '';
        $awardDetail = $input['award_detail'] ?? '';
        $awardDate = $input['award_date'] ?? '';
        $term = $input['term'] ?? '';
        $year = $input['year'] ?? '';

        if (empty($students)) {
            throw new Exception('กรุณาเพิ่มข้อมูลนักเรียนอย่างน้อย 1 คน');
        }

        if (empty($awardType) || empty($awardDetail) || empty($awardDate)) {
            throw new Exception('กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        if (empty($term) || empty($year)) {
            throw new Exception('กรุณากรอกภาคเรียนและปีการศึกษา');
        }

        foreach ($students as $student) {
            if (empty($student['name']) || empty($student['class']) || empty($student['room'])) {
                throw new Exception('กรุณากรอกข้อมูลนักเรียนให้ครบถ้วน');
            }
        }
    }

    private function sendResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

// Handle requests
$action = $_GET['action'] ?? '';
$controller = new CertificateController();

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'list':
        $controller->list();
        break;
    case 'detail':
        $controller->detail();
        break;
    case 'update':
        $controller->update();
        break;
    case 'delete':
        $controller->delete();
        break;
    case 'upload':
        $controller->uploadImage();
        break;
    case 'statistics':
        $controller->statistics();
        break;
    case 'search':
        $controller->search();
        break;
    case 'topStudents':
        $controller->topStudents();
        break;
    case 'recent':
        $controller->recent();
        break;
    case 'export':
        $controller->export();
        break;
    case 'termInfo':
        $controller->termInfo();
        break;
    case 'availableTerms':
        $controller->availableTerms();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
        break;
}
?>
