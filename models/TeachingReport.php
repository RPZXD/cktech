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
                ORDER BY r.report_date DESC, r.period_start ASC";
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

        // ดึง absent_students, sick_students, personal_students จาก teaching_attendance_logs
        foreach ($reports as &$report) {
            $statuses = [
                'ขาดเรียน' => [],
                'ลาป่วย' => [],
                'ลากิจ' => [],
                'เข้าร่วมกิจกรรม' => []
            ];
            $sql2 = "SELECT student_id, status FROM teaching_attendance_logs WHERE report_id = ? AND status IN ('ขาดเรียน','ลาป่วย','ลากิจ','เข้าร่วมกิจกรรม')";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([$report['id']]);
            $logs = $stmt2->fetchAll();

            $studentsInfo = [];
            foreach ($logs as $log) {
                $stuId = $log['student_id'];
                // ดึงชื่อจริงและเลขที่ (Stu_no) จากฐาน student
                $stmtStu = $pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id = ? ORder by Stu_no ASC");
                $stmtStu->execute([$stuId]);
                $stu = $stmtStu->fetch();
                $stu_no = $stu && isset($stu['Stu_no']) ? (int)$stu['Stu_no'] : 0;
                $label = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['label'] : $log['status'];
                $emoji = isset($statusLabelMap[$log['status']]) ? $statusLabelMap[$log['status']]['emoji'] : '';
                // แสดง: [Stu_no][Stu_id]fullname [emoji label] (ถ้ามี), ถ้าไม่มีให้แสดง Stu_id [emoji label]
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
                'เข้าร่วมกิจกรรม' => []
            ];
            foreach ($studentsInfo as $info) {
                if (isset($statuses[$info['status']])) {
                    $statuses[$info['status']][] = $info['display'];
                }
            }
            // ใช้ tailwind css แทน style
            $report['absent_students'] = $statuses['ขาดเรียน'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ขาดเรียน'])) . '</div>' : '';
            $report['sick_students'] = $statuses['ลาป่วย'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลาป่วย'])) . '</div>' : '';
            $report['personal_students'] = $statuses['ลากิจ'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['ลากิจ'])) . '</div>' : '';
            $report['activity_students'] = $statuses['เข้าร่วมกิจกรรม'] ? '<div class="flex flex-wrap gap-2">' . implode('', array_map(function($s){return '<span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-md text-sm">'.$s.'</span>';}, $statuses['เข้าร่วมกิจกรรม'])) . '</div>' : '';
        }
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
        return $stmt->fetch();
    }

    public function createMultiple($rows, $attendanceLogs)
    {
        try {
            $this->pdo->beginTransaction();
            $reportIds = [];
            $invalidRows = [];
            // ปรับ allowedStatuses ให้ตรงกับ ENUM ในฐานข้อมูล
            $allowedStatuses = [
                'ขาดเรียน', 'ลาป่วย', 'ลากิจ', 'มาเรียน'
            ];
            // เก็บ mapping class_room => report_id
            $classRoomToReportId = [];
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
                $classRoomToReportId[trim($row['class_room'])] = $reportId;
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
                        $logClassRoom = trim($log['class_room']);
                        if (!isset($classRoomToReportId[$logClassRoom])) continue;
                        $reportId = $classRoomToReportId[$logClassRoom];
                        $stmt = $this->pdo->prepare("INSERT INTO teaching_attendance_logs (report_id, student_id, status) VALUES (?, ?, ?)");
                        $stmt->execute([
                            $reportId,
                            $log['student_id'],
                            $log['status']
                        ]);
                    } else {
                        foreach ($classRoomToReportId as $reportId) {
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
}
