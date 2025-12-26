<?php
namespace App\Models;

use PDO;

class TeachingReport
{
    public $pdo; // <-- เปลี่ยนจาก private เป็น public
    protected $dbUsers;

    public function __construct()
    {
        require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
        require_once __DIR__ . '/../classes/DatabaseUsers.php';
        $db = new \App\DatabaseTeachingReport();
        $this->pdo = $db->getPDO();
        $this->dbUsers = new \App\DatabaseUsers();
    }

    public function getAllByTeacher($teacher_id)
    {
        // ตรวจสอบการเชื่อมต่อฐานข้อมูล DatabaseTeachingReport
        if (!$this->pdo) {
            throw new \Exception('ไม่สามารถเชื่อมต่อฐานข้อมูล TeachingReport');
        }

        $sql = "SELECT r.*, s.name AS subject_name , s.level
                FROM teaching_reports r
                LEFT JOIN subjects s ON r.subject_id = s.id
                WHERE r.teacher_id = ?
                ORDER BY r.report_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$teacher_id]);
        $reports = $stmt->fetchAll();

        // เตรียมเชื่อมต่อฐานข้อมูล student
        // ตรวจสอบ DatabaseUsers ว่าเชื่อมต่อสำเร็จหรือไม่
        if (!$this->dbUsers) {
            throw new \Exception('ไม่สามารถเชื่อมต่อฐานข้อมูล Users');
        }
        $pdoUsers = $this->dbUsers->getPDO();

        // กำหนด mapping สำหรับ label ภาษาไทย
        $statusLabelMap = [
            'ขาดเรียน' => ['label' => 'ขาด', 'emoji' => '❌'],
            'ลาป่วย' => ['label' => 'ป่วย', 'emoji' => '🤒'],
            'ลากิจ' => ['label' => 'ลากิจ', 'emoji' => '📝'],
            'มาเรียน' => ['label' => 'มา', 'emoji' => '✅'],
            'มาสาย' => ['label' => 'สาย', 'emoji' => '⏰'],
            'เข้าร่วมกิจกรรม' => ['label' => 'กิจกรรม', 'emoji' => '🎉']
        ];

        // ดึง absent_students, sick_students, personal_students และโดดเรียน จาก teaching_attendance_logs
        foreach ($reports as &$report) {
            $statuses = [
                'ขาดเรียน' => [],
                'ลาป่วย' => [],
                'ลากิจ' => [],
                'เข้าร่วมกิจกรรม' => [],
                'โดดเรียน' => []
            ];
            $sql2 = "SELECT student_id, status FROM teaching_attendance_logs WHERE report_id = ? AND status IN ('ขาดเรียน','ลาป่วย','ลากิจ','มาเรียน','มาสาย','เข้าร่วมกิจกรรม','โดดเรียน')";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([$report['id']]);
            $logs = $stmt2->fetchAll();

            $studentsInfo = [];
            foreach ($logs as $log) {
                $stuId = $log['student_id'];
                // ดึงชื่อจริงและเลขที่ (Stu_no) จากฐาน student
                $stmtStu = $pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id = ? ORDER BY Stu_no ASC");
                $stmtStu->execute([$stuId]);
                $stu = $stmtStu->fetch();
                $stu_no = $stu && isset($stu['Stu_no']) ? (int)$stu['Stu_no'] : 0;
                $label = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['label'] : $log['status'];
                $emoji = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['emoji'] : '';
                $display = $stu
                    ? (
                        '<span style="display:inline-block;margin-right:4px;">[' . ($stu['Stu_no'] ?? '-') . '][' . $stu['Stu_id'] . ']' . htmlspecialchars($stu['fullname']) . '</span> <span style="font-weight:bold;">' . $emoji . ' ' . $label . '</span>'
                    )
                    : '<span>' . $stuId . '</span> <span style="font-weight:bold;">' . $emoji . ' ' . $label . '</span>';
                $studentsInfo[] = [
                    'stu_no' => $stu_no,
                    'status' => $log['status'],
                    'display' => $display
                ];
            }
            // เรียงตาม Stu_no
            usort($studentsInfo, function($a, $b) {
                return $a['stu_no'] <=> $b['stu_no'];
            });
            // แยกแต่ละ status
            $statuses = [
                'ขาดเรียน' => [],
                'ลาป่วย' => [],
                'ลากิจ' => [],
                'มาเรียน' => [],
                'มาสาย' => [],
                'เข้าร่วมกิจกรรม' => [],
                'โดดเรียน' => []
            ];
            foreach ($studentsInfo as $info) {
                if (isset($statuses[$info['status']])) {
                    $statuses[$info['status']][] = $info['display'];
                }
            }
            // สร้าง HTML และจำนวนสำหรับแต่ละกลุ่ม
            $report['absent_students'] = $statuses['ขาดเรียน'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ขาดเรียน'])) . '</div>' : '';
            $report['sick_students'] = $statuses['ลาป่วย'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลาป่วย'])) . '</div>' : '';
            $report['personal_students'] = $statuses['ลากิจ'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลากิจ'])) . '</div>' : '';
            $report['present_students'] = $statuses['มาเรียน'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['มาเรียน'])) . '</div>' : '';
            $report['late_students'] = $statuses['มาสาย'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-amber-100 text-amber-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['มาสาย'])) . '</div>' : '';
            $report['activity_students'] = $statuses['เข้าร่วมกิจกรรม'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['เข้าร่วมกิจกรรม'])) . '</div>' : '';
            $report['truant_students'] = $statuses['โดดเรียน'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['โดดเรียน'])) . '</div>' : '';
            $report['absent_count'] = is_array($statuses['ขาดเรียน']) ? count($statuses['ขาดเรียน']) : 0;
            $report['sick_count'] = is_array($statuses['ลาป่วย']) ? count($statuses['ลาป่วย']) : 0;
            $report['personal_count'] = is_array($statuses['ลากิจ']) ? count($statuses['ลากิจ']) : 0;
            $report['present_count'] = is_array($statuses['มาเรียน']) ? count($statuses['มาเรียน']) : 0;
            $report['late_count'] = is_array($statuses['มาสาย']) ? count($statuses['มาสาย']) : 0;
            $report['activity_count'] = is_array($statuses['เข้าร่วมกิจกรรม']) ? count($statuses['เข้าร่วมกิจกรรม']) : 0;
            $report['truant_count'] = is_array($statuses['โดดเรียน']) ? count($statuses['โดดเรียน']) : 0;
            $report['present_count'] = is_array($statuses['มาเรียน']) ? count($statuses['มาเรียน']) : 0;
            $report['late_count'] = is_array($statuses['มาสาย']) ? count($statuses['มาสาย']) : 0;
            $report['activity_count'] = is_array($statuses['เข้าร่วมกิจกรรม']) ? count($statuses['เข้าร่วมกิจกรรม']) : 0;
            $report['truant_count'] = is_array($statuses['โดดเรียน']) ? count($statuses['โดดเรียน']) : 0;
        }
        return $reports;
    }

    /**
     * Get all reports for admin with optional filters
     * @param string $teacher Teacher ID filter
     * @param string $department Department filter
     * @param string $level Level filter (ม.1-6)
     * @param string $dateStart Start date filter
     * @param string $dateEnd End date filter
     * @return array
     */
    public function getAllReportsForAdmin($teacher = '', $department = '', $level = '', $dateStart = '', $dateEnd = '')
    {
        $params = [];
        $where = [];

        $sql = "SELECT r.*, s.name AS subject_name, s.level
                FROM teaching_reports r
                LEFT JOIN subjects s ON r.subject_id = s.id";

        // Filter by teacher
        if (!empty($teacher)) {
            $where[] = "r.teacher_id = ?";
            $params[] = $teacher;
        }

        // Filter by level
        if (!empty($level)) {
            $where[] = "s.level = ?";
            $params[] = $level;
        }

        // Filter by date range
        if (!empty($dateStart)) {
            $where[] = "r.report_date >= ?";
            $params[] = $dateStart;
        }
        if (!empty($dateEnd)) {
            $where[] = "r.report_date <= ?";
            $params[] = $dateEnd;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY r.report_date DESC, r.id DESC LIMIT 500";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();

        // Get teacher names and absent counts
        $pdoUsers = $this->dbUsers->getPDO();
        
        foreach ($reports as &$report) {
            // Get teacher name
            if (!empty($report['teacher_id'])) {
                $stmtTeacher = $pdoUsers->prepare("SELECT Teach_name, Teach_major FROM teacher WHERE Teach_id = ?");
                $stmtTeacher->execute([$report['teacher_id']]);
                $teacher = $stmtTeacher->fetch();
                $report['teacher_name'] = $teacher['Teach_name'] ?? '-';
                $report['teacher_department'] = $teacher['Teach_major'] ?? '-';
                
                // Filter by department if provided (after fetch since it's in different DB)
                if (!empty($department) && $report['teacher_department'] !== $department) {
                    $report = null;
                    continue;
                }
            } else {
                $report['teacher_name'] = '-';
                $report['teacher_department'] = '-';
            }

            // Get absent count
            $stmtAbsent = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM teaching_attendance_logs WHERE report_id = ? AND status = 'ขาดเรียน'");
            $stmtAbsent->execute([$report['id']]);
            $report['absent_count'] = $stmtAbsent->fetch()['cnt'] ?? 0;
        }

        // Remove null entries (filtered by department)
        $reports = array_filter($reports, function($r) { return $r !== null; });
        $reports = array_values($reports);

        return $reports;
    }

    public function getById($id)
    {
        $sql = "SELECT r.*, s.name AS subject_name, s.level
                FROM teaching_reports r
                LEFT JOIN subjects s ON r.subject_id = s.id
                WHERE r.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $report = $stmt->fetch();

        if ($report) {
            // เตรียมเชื่อมต่อฐานข้อมูล student
            if (!$this->dbUsers) {
                throw new \Exception('ไม่สามารถเชื่อมต่อฐานข้อมูล Users');
            }
            $pdoUsers = $this->dbUsers->getPDO();

            // กำหนด mapping สำหรับ label ภาษาไทย
            $statusLabelMap = [
                'ขาดเรียน' => ['label' => 'ขาด', 'emoji' => '❌'],
                'ลาป่วย' => ['label' => 'ป่วย', 'emoji' => '🤒'],
                'ลากิจ' => ['label' => 'ลากิจ', 'emoji' => '📝'],
                'มาเรียน' => ['label' => 'มา', 'emoji' => '✅'],
                'มาสาย' => ['label' => 'สาย', 'emoji' => '⏰'],
                'เข้าร่วมกิจกรรม' => ['label' => 'กิจกรรม', 'emoji' => '🎉']
            ];

            // ดึง absent_students, sick_students, personal_students และโดดเรียน จาก teaching_attendance_logs
            $statuses = [
                'ขาดเรียน' => [],
                'ลาป่วย' => [],
                'ลากิจ' => [],
                'เข้าร่วมกิจกรรม' => [],
                'โดดเรียน' => []
            ];
            $sql2 = "SELECT student_id, status FROM teaching_attendance_logs WHERE report_id = ? AND status IN ('ขาดเรียน','ลาป่วย','ลากิจ','มาเรียน','มาสาย','เข้าร่วมกิจกรรม','โดดเรียน')";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([$report['id']]);
            $logs = $stmt2->fetchAll();

            $studentsInfo = [];
            foreach ($logs as $log) {
                $stuId = $log['student_id'];
                // ดึงชื่อจริงและเลขที่ (Stu_no) จากฐาน student
                $stmtStu = $pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id = ? ORDER BY Stu_no ASC");
                $stmtStu->execute([$stuId]);
                $stu = $stmtStu->fetch();
                $stu_no = $stu && isset($stu['Stu_no']) ? (int)$stu['Stu_no'] : 0;
                $label = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['label'] : $log['status'];
                $emoji = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['emoji'] : '';
                $display = $stu
                    ? (
                        '<span style="display:inline-block;margin-right:4px;">[' . ($stu['Stu_no'] ?? '-') . '][' . $stu['Stu_id'] . ']' . htmlspecialchars($stu['fullname']) . '</span> <span style="font-weight:bold;">' . $emoji . ' ' . $label . '</span>'
                    )
                    : '<span>' . $stuId . '</span> <span style="font-weight:bold;">' . $emoji . ' ' . $label . '</span>';
                $studentsInfo[] = [
                    'stu_no' => $stu_no,
                    'status' => $log['status'],
                    'display' => $display
                ];
            }
            // เรียงตาม Stu_no
            usort($studentsInfo, function($a, $b) {
                return $a['stu_no'] <=> $b['stu_no'];
            });
            // แยกแต่ละ status
            $statuses = [
                'ขาดเรียน' => [],
                'ลาป่วย' => [],
                'ลากิจ' => [],
                'มาเรียน' => [],
                'มาสาย' => [],
                'เข้าร่วมกิจกรรม' => [],
                'โดดเรียน' => []
            ];
            foreach ($studentsInfo as $info) {
                if (isset($statuses[$info['status']])) {
                    $statuses[$info['status']][] = $info['display'];
                }
            }
            // สร้าง HTML และจำนวนสำหรับแต่ละกลุ่ม
            $report['absent_students'] = $statuses['ขาดเรียน'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ขาดเรียน'])) . '</div>' : '';
            $report['sick_students'] = $statuses['ลาป่วย'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลาป่วย'])) . '</div>' : '';
            $report['personal_students'] = $statuses['ลากิจ'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลากิจ'])) . '</div>' : '';
            $report['present_students'] = $statuses['มาเรียน'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['มาเรียน'])) . '</div>' : '';
            $report['late_students'] = $statuses['มาสาย'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-amber-100 text-amber-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['มาสาย'])) . '</div>' : '';
            $report['activity_students'] = $statuses['เข้าร่วมกิจกรรม'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['เข้าร่วมกิจกรรม'])) . '</div>' : '';
            $report['truant_students'] = $statuses['โดดเรียน'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['โดดเรียน'])) . '</div>' : '';
            $report['absent_count'] = is_array($statuses['ขาดเรียน']) ? count($statuses['ขาดเรียน']) : 0;
            $report['sick_count'] = is_array($statuses['ลาป่วย']) ? count($statuses['ลาป่วย']) : 0;
            $report['personal_count'] = is_array($statuses['ลากิจ']) ? count($statuses['ลากิจ']) : 0;
            $report['present_count'] = is_array($statuses['มาเรียน']) ? count($statuses['มาเรียน']) : 0;
            $report['late_count'] = is_array($statuses['มาสาย']) ? count($statuses['มาสาย']) : 0;
            $report['activity_count'] = is_array($statuses['เข้าร่วมกิจกรรม']) ? count($statuses['เข้าร่วมกิจกรรม']) : 0;
            $report['truant_count'] = is_array($statuses['โดดเรียน']) ? count($statuses['โดดเรียน']) : 0;
        }

        return $report;
    }

    public function createMultiple($rows, $attendanceLogs)
    {
        try {
            $this->pdo->beginTransaction();
            $reportIds = [];
            $invalidRows = [];
            // ปรับ allowedStatuses ให้ตรงกับ ENUM ในฐานข้อมูล (รวม 'เข้าร่วมกิจกรรม' และ 'โดดเรียน')
            $allowedStatuses = [
                'ขาดเรียน', 'ลาป่วย', 'ลากิจ', 'มาเรียน', 'มาสาย', 'เข้าร่วมกิจกรรม', 'โดดเรียน'
            ];
            // เก็บ mapping class_room => report_id
            $classRoomToReportId = [];
            // helper: normalize room representation (รองรับ 'ห้อง 12' และ '12')
            $normalizeRoom = function($r) {
                if ($r === null) return '';
                $r = trim($r);
                // ถ้าเริ่มต้นด้วย 'ห้อง ' ให้ตัดออก
                if (mb_stripos($r, 'ห้อง ') === 0) {
                    $r = trim(mb_substr($r, mb_strlen('ห้อง ')));
                }
                return $r;
            };
            foreach ($rows as $row) {
                // ตรวจสอบข้อมูลจำเป็น
                if (
                    !isset($row['report_date']) || $row['report_date'] === '' ||
                    !isset($row['subject_id']) || $row['subject_id'] === '' ||
                    !isset($row['class_room']) || $row['class_room'] === '' ||
                    !isset($row['period_start']) || $row['period_start'] === '' ||
                    !isset($row['period_end']) || $row['period_end'] === '' ||
                    !isset($row['teacher_id']) || $row['teacher_id'] === ''
                ) {
                    $invalidRows[] = $row;
                    continue;
                }
                // ตรวจสอบว่า period_start/period_end เป็นตัวเลข
                if (!is_numeric($row['period_start']) || !is_numeric($row['period_end'])) {
                    $invalidRows[] = $row;
                    continue;
                }
                $img1 = !empty($row['image1']) ? $row['image1'] : null;
                $img2 = !empty($row['image2']) ? $row['image2'] : null;

                $stmt = $this->pdo->prepare("INSERT INTO teaching_reports 
                    (report_date, subject_id, class_room, period_start, period_end, plan_number, plan_topic, activity, reflection_k, reflection_p, reflection_a, problems, suggestions, teacher_id, image1, image2, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $row['report_date'],
                    $row['subject_id'],
                    $row['class_room'],
                    $row['period_start'],
                    $row['period_end'],
                    $row['plan_number'] ?? null,
                    $row['plan_topic'] ?? null,
                    $row['activity'] ?? null,
                    $row['reflection_k'] ?? null,
                    $row['reflection_p'] ?? null,
                    $row['reflection_a'] ?? null,
                    $row['problems'] ?? null,
                    $row['suggestions'] ?? null,
                    $row['teacher_id'],
                    $img1,
                    $img2
                ]);
                $reportId = $this->pdo->lastInsertId();
                $reportIds[] = $reportId;
                $rawRoom = isset($row['class_room']) ? $row['class_room'] : '';
                $norm = $normalizeRoom($rawRoom);
                // เก็บทั้งรูปแบบตัวเลขและรูปแบบมีคำว่า 'ห้อง ' เพื่อให้การแม็ปยืดหยุ่น
                if ($norm !== '') {
                    $classRoomToReportId[$norm] = $reportId;
                    $classRoomToReportId['ห้อง ' . $norm] = $reportId;
                } else {
                    $classRoomToReportId[$rawRoom] = $reportId;
                }
            }
            if (empty($reportIds)) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'error' => 'ไม่มีข้อมูลแถวที่ถูกต้องสำหรับบันทึก'
                ];
            }
            // ตรวจสอบว่ามี class_room ใน attendanceLogs หรือไม่ ถ้าไม่มีให้ map ทุก log ไปยังทุกห้อง (กรณีเก่า)
            $hasClassRoom = false;
            foreach ($attendanceLogs as $log) {
                if (isset($log['class_room'])) {
                    $hasClassRoom = true;
                    break;
                }
            }
            if (!empty($attendanceLogs) && !empty($classRoomToReportId)) {
                // ตรวจสอบว่าตาราง teaching_attendance_logs มีคอลัมน์ class_room หรือไม่
                $hasClassRoomColumn = false;
                try {
                    $stmtCol = $this->pdo->query("SHOW COLUMNS FROM teaching_attendance_logs LIKE 'class_room'");
                    if ($stmtCol && $stmtCol->fetch()) $hasClassRoomColumn = true;
                } catch (\Exception $e) {
                    // ignore
                }

                foreach ($attendanceLogs as $log) {
                    // ตรวจสอบ student_id, status และ whitelist
                    if (
                        !isset($log['student_id']) || $log['student_id'] === '' ||
                        !isset($log['status']) || $log['status'] === '' ||
                        !in_array($log['status'], $allowedStatuses, true)
                    ) continue;
                    if (!is_numeric($log['student_id'])) continue;
                    $log['status'] = trim($log['status']);

                    if ($hasClassRoom) {
                        if (!isset($log['class_room']) || $log['class_room'] === '') continue;
                        // Normalize class_room for matching
                        $logClassRoom = $normalizeRoom($log['class_room']);
                        // try both normalized forms
                        $candidateKeys = [$logClassRoom, 'ห้อง ' . $logClassRoom, trim($log['class_room'])];
                        $found = false;
                        foreach ($candidateKeys as $ck) {
                            if ($ck === null || $ck === '') continue;
                            if (isset($classRoomToReportId[$ck])) {
                                $reportId = $classRoomToReportId[$ck];
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) continue;

                        if ($hasClassRoomColumn) {
                            $stmt = $this->pdo->prepare("INSERT INTO teaching_attendance_logs (report_id, student_id, status, class_room) VALUES (?, ?, ?, ?)");
                            $stmt->execute([
                                $reportId,
                                $log['student_id'],
                                $log['status'],
                                $log['class_room']
                            ]);
                        } else {
                            $stmt = $this->pdo->prepare("INSERT INTO teaching_attendance_logs (report_id, student_id, status) VALUES (?, ?, ?)");
                            $stmt->execute([
                                $reportId,
                                $log['student_id'],
                                $log['status']
                            ]);
                        }
                    } else {
                        // map ไปยังทุกรายงานที่สร้างใน session เดียวกัน
                        foreach ($classRoomToReportId as $reportId) {
                            if ($hasClassRoomColumn) {
                                $stmt = $this->pdo->prepare("INSERT INTO teaching_attendance_logs (report_id, student_id, status, class_room) VALUES (?, ?, ?, ?)");
                                $stmt->execute([
                                    $reportId,
                                    $log['student_id'],
                                    $log['status'],
                                    ''
                                ]);
                            } else {
                                $stmt = $this->pdo->prepare("INSERT INTO teaching_attendance_logs (report_id, student_id, status) VALUES (?, ?, ?)");
                                $stmt->execute([
                                    $reportId,
                                    $log['student_id'],
                                    $log['status']
                                ]);
                            }
                        }
                    }
                }
            }
            $this->pdo->commit();
            return ['success' => true];
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'success' => false,
                'error' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'success' => false,
                'error' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
            ];
        }
    }

    public function updateReport($id, $row, $attendanceLogs)
    {
        try {
            $this->pdo->beginTransaction();
            // อัปเดต teaching_reports
            $stmt = $this->pdo->prepare("UPDATE teaching_reports SET 
                report_date=?, subject_id=?, class_room=?, period_start=?, period_end=?, plan_number=?, plan_topic=?, activity=?, reflection_k=?, reflection_p=?, reflection_a=?, problems=?, suggestions=?, teacher_id=?, image1=?, image2=?
                WHERE id=?");
            $stmt->execute([
                $row['report_date'],
                $row['subject_id'],
                $row['class_room'],
                $row['period_start'],
                $row['period_end'],
                $row['plan_number'],
                $row['plan_topic'],
                $row['activity'],
                $row['reflection_k'],
                $row['reflection_p'],
                $row['reflection_a'],
                $row['problems'],
                $row['suggestions'],
                $row['teacher_id'],
                !empty($row['image1']) ? $row['image1'] : null,
                !empty($row['image2']) ? $row['image2'] : null,
                $id
            ]);
            // ลบ attendance logs เดิม
            $stmtDel = $this->pdo->prepare("DELETE FROM teaching_attendance_logs WHERE report_id=?");
            $stmtDel->execute([$id]);
            // เพิ่ม attendance logs ใหม่
            foreach ($attendanceLogs as $log) {
                if (empty($log['student_id']) || empty($log['status'])) continue;
                $stmtIns = $this->pdo->prepare("INSERT INTO teaching_attendance_logs (report_id, student_id, status) VALUES (?, ?, ?)");
                $stmtIns->execute([
                    $id,
                    $log['student_id'],
                    $log['status']
                ]);
            }
            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }
        // ดึง attendance log ของรายงานนี้ (รองรับ schema ที่ไม่มี class_room)
    public function getAttendanceLogByReportId($reportId)
    {
        // ตรวจสอบว่าคอลัมน์ class_room มีอยู่ใน teaching_attendance_logs หรือไม่
        $hasClassRoom = false;
        $stmtCol = $this->pdo->query("SHOW COLUMNS FROM teaching_attendance_logs LIKE 'class_room'");
        if ($stmtCol && $stmtCol->fetch()) {
            $hasClassRoom = true;
        }

        if ($hasClassRoom) {
            $stmt = $this->pdo->prepare("SELECT student_id, status, class_room, comment FROM teaching_attendance_logs WHERE report_id=?");
        } else {
            $stmt = $this->pdo->prepare("SELECT student_id, status, comment FROM teaching_attendance_logs WHERE report_id=?");
        }
        $stmt->execute([$reportId]);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // ถ้ามี DatabaseUsers ให้ต่อไปดึงชื่อ-สกุล นักเรียน เพื่อให้ frontend แสดงรายชื่อได้สะดวก
        $result = [];
        try {
            if ($this->dbUsers) {
                $pdoUsers = $this->dbUsers->getPDO();
                $stmtStu = $pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id = ?");
                foreach ($logs as $log) {
                    $stuName = null;
                    $stmtStu->execute([$log['student_id']]);
                    $stu = $stmtStu->fetch();
                    if ($stu) $stuName = $stu['fullname'];
                    $entry = [
                        'student_id' => $log['student_id'],
                        'student_name' => $stuName,
                        'status' => $log['status'] ?? '',
                        'comment' => $log['comment'] ?? null
                    ];
                    if ($hasClassRoom) $entry['class_room'] = $log['class_room'];
                    $result[] = $entry;
                }
                return $result;
            }
        } catch (\Exception $e) {
            // ถ้ามีปัญหา ให้ fallback ส่งข้อมูลเดิม
        }

        return $logs;
    }

    /**
     * ดึงตารางการเช็คชื่อสำหรับวันที่หนึ่ง ๆ โดยรับ subject, class_room และวันที่
     * คืนค่าโครงสร้าง: [ 'reports' => [ {id, period_start, period_end} ], 'students' => [ {Stu_id, Stu_no, fullname, statuses: {reportId: status}} ] ]
     */
    public function getAttendanceByDay($subjectId, $classRoom, $date, $teacherId = null)
    {
        $result = ['reports' => [], 'students' => []];
        if (!$subjectId || !$classRoom || !$date) return $result;

        // Normalize class room and match normalized value to avoid cross-room matches
        $normalize = function($r) {
            $r = trim($r);
            if (mb_stripos($r, 'ห้อง ') === 0) $r = trim(mb_substr($r, mb_strlen('ห้อง ')));
            return $r;
        };
        $norm = $normalize($classRoom);

        $sql = "SELECT id, period_start, period_end, class_room, teacher_id FROM teaching_reports 
                WHERE subject_id = ? AND report_date = ? AND TRIM(REPLACE(class_room, 'ห้อง ', '')) = ?";
        $params = [$subjectId, $date, $norm];
        if ($teacherId) { $sql .= ' AND teacher_id = ?'; $params[] = $teacherId; }
        $sql .= ' ORDER BY period_start, period_end';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();
        if (!$reports) return $result;

        $reportIds = array_column($reports, 'id');
        $result['reports'] = array_map(function($r){ return ['id' => $r['id'], 'report_date' => $r['report_date'], 'period_start' => $r['period_start'], 'period_end' => $r['period_end'], 'class_room' => $r['class_room']]; }, $reports);

        // ดึงรายชื่อนักเรียนจากฐาน student โดยใช้ Stu_room = normalized room (หรือ fallback)
        $students = [];
        try {
            if ($this->dbUsers) {
                $pdoUsers = $this->dbUsers->getPDO();
                // compare normalized Stu_room to handle values like 'ห้อง 11' or '11'
                $stmtStu = $pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE TRIM(REPLACE(Stu_room, 'ห้อง ', '')) = ? OR TRIM(Stu_room) = ? ORDER BY Stu_no ASC");
                $stmtStu->execute([$norm, $classRoom]);
                $students = $stmtStu->fetchAll();
            }
        } catch (\Exception $e) {
            $students = [];
        }

        // Prepare a map studentId => student info
        $studentMap = [];
        foreach ($students as $s) {
            $studentMap[$s['Stu_id']] = [
                'Stu_id' => $s['Stu_id'],
                'Stu_no' => $s['Stu_no'],
                'fullname' => $s['fullname'],
                'statuses' => []
            ];
        }

        // For each report, fetch attendance logs and map statuses
        foreach ($reportIds as $rid) {
            $logs = $this->getAttendanceLogByReportId($rid);
            foreach ($logs as $log) {
                $stuId = $log['student_id'];
                $status = $log['status'] ?? '';
                if (!isset($studentMap[$stuId])) {
                    // add missing students (in case attendance has entries for students not in student table)
                    $studentMap[$stuId] = [
                        'Stu_id' => $stuId,
                        'Stu_no' => 0,
                        'fullname' => $log['student_name'] ?? $stuId,
                        'statuses' => []
                    ];
                }
                $studentMap[$stuId]['statuses'][$rid] = $status;
            }
        }

        // Convert map to array sorted by Stu_no
        $arr = array_values($studentMap);
        usort($arr, function($a, $b){ return ($a['Stu_no'] ?? 0) <=> ($b['Stu_no'] ?? 0); });
        $result['students'] = $arr;
        return $result;
    }

    /**
     * ดึงตารางการเช็คชื่อสำหรับ subject+classRoom หลายๆ วัน (ล่าสุดก่อน)
     * คืนค่าโครงสร้าง: [ 'reports' => [ {id, report_date, period_start, period_end} ], 'students' => [ {Stu_id, Stu_no, fullname, statuses: {reportId: status}} ] ]
     */
    public function getAttendanceByRoom($subjectId, $classRoom, $teacherId = null, $limit = 14)
    {
        $result = ['reports' => [], 'students' => []];
        if (!$subjectId || !$classRoom) return $result;

        // Normalize class room and match normalized value in SQL to avoid matching other rooms
        $normalize = function($r) {
            $r = trim($r);
            if (mb_stripos($r, 'ห้อง ') === 0) $r = trim(mb_substr($r, mb_strlen('ห้อง ')));
            return $r;
        };
        $norm = $normalize($classRoom);

        // Use SQL normalization: remove 'ห้อง ' prefix before comparison so storage format variations are handled,
        // but restrict to the normalized room value to avoid matching other rooms unintentionally.
        $sql = "SELECT id, report_date, period_start, period_end, class_room FROM teaching_reports 
                WHERE subject_id = ? AND TRIM(REPLACE(class_room, 'ห้อง ', '')) = ?";
        $params = [$subjectId, $norm];
        if ($teacherId) { $sql .= ' AND teacher_id = ?'; $params[] = $teacherId; }
        $sql .= ' ORDER BY report_date DESC, period_start LIMIT ' . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();
        if (!$reports) return $result;

        // reverse to show oldest -> newest in columns (optional). Keep as returned (newest first)
        $reportIds = array_column($reports, 'id');
        $result['reports'] = array_map(function($r){ return ['id'=>$r['id'],'report_date'=>$r['report_date'],'period_start'=>$r['period_start'],'period_end'=>$r['period_end'],'class_room'=>$r['class_room']]; }, $reports);

        // collect students for the room (use normalized room)
        $students = [];
        try {
            if ($this->dbUsers) {
                $pdoUsers = $this->dbUsers->getPDO();
                // compare normalized Stu_room to handle values like 'ห้อง 11' or '11'
                $stmtStu = $pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE TRIM(REPLACE(Stu_room, 'ห้อง ', '')) = ? OR TRIM(Stu_room) = ? ORDER BY Stu_no ASC");
                $stmtStu->execute([$norm, $classRoom]);
                $students = $stmtStu->fetchAll();
            }
        } catch (\Exception $e) {
            $students = [];
        }

        $studentMap = [];
        foreach ($students as $s) {
            $studentMap[$s['Stu_id']] = ['Stu_id'=>$s['Stu_id'],'Stu_no'=>$s['Stu_no'],'fullname'=>$s['fullname'],'statuses'=>[]];
        }

        // Fetch attendance logs for all reportIds
        if (!empty($reportIds)) {
            $in = implode(',', array_fill(0, count($reportIds), '?'));
            $stmtLogs = $this->pdo->prepare("SELECT report_id, student_id, status, comment FROM teaching_attendance_logs WHERE report_id IN ($in)");
            $stmtLogs->execute($reportIds);
            $logs = $stmtLogs->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($logs as $log) {
                $rid = $log['report_id'];
                $stuId = $log['student_id'];
                $status = $log['status'] ?? '';
                if (!isset($studentMap[$stuId])) {
                    $studentMap[$stuId] = ['Stu_id'=>$stuId,'Stu_no'=>0,'fullname'=>$log['student_name'] ?? $stuId,'statuses'=>[]];
                }
                $studentMap[$stuId]['statuses'][$rid] = $status;
            }
        }

        $arr = array_values($studentMap);
        usort($arr, function($a,$b){ return ($a['Stu_no'] ?? 0) <=> ($b['Stu_no'] ?? 0); });
        $result['students'] = $arr;
        return $result;
    }
}
