<?php
namespace App\Models;

use DateInterval;
use DatePeriod;
use DateTime;
use PDO;

class Attendance
{
    protected $pdoTeaching;
    protected $pdoUsers;

    public function __construct()
    {
        require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
        require_once __DIR__ . '/../classes/DatabaseUsers.php';
        $dbT = new \App\DatabaseTeachingReport();
        $this->pdoTeaching = $dbT->getPDO();
        $dbU = new \App\DatabaseUsers();
        $this->pdoUsers = $dbU->getPDO();
    }

    /**
     * Save attendance rows for a subject+date. Rows: [{ student_id?, student_no?, status, class_room, report_id? }]
     * Returns summary array with counts and any warnings.
     */
    public function saveAttendance($subjectId, $date, $rows, $teacherId = null)
    {
        if (!$subjectId || !$date || empty($rows)) throw new \Exception('Missing parameters');

        // detect class_room column presence
        $hasClassRoom = false;
        try {
            $stmtCol = $this->pdoTeaching->query("SHOW COLUMNS FROM teaching_attendance_logs LIKE 'class_room'");
            if ($stmtCol && $stmtCol->fetch()) $hasClassRoom = true;
        } catch (\Exception $e) {
            $hasClassRoom = false;
        }

        // helper to resolve Stu_no -> Stu_id
        $resolveStudentId = function($studentNo) {
            if (!$studentNo) return null;
            $stmt = $this->pdoUsers->prepare('SELECT Stu_id FROM student WHERE Stu_no = ? AND Stu_status = 1 LIMIT 1');
            $stmt->execute([$studentNo]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return $r ? $r['Stu_id'] : null;
        };

        // helper to find report id for subject+date+room (prefer teacher match)
        $findReportId = function($room) use ($subjectId, $date, $teacherId) {
            $sql = 'SELECT id, class_room FROM teaching_reports WHERE subject_id = ? AND report_date = ?';
            $params = [$subjectId, $date];
            if ($teacherId) {
                $sql .= ' AND teacher_id = ?';
                $params[] = $teacherId;
            }
            $sql .= ' ORDER BY id DESC LIMIT 1';
            $stmt = $this->pdoTeaching->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) return null;
            // if room matches any row's class_room prefer that
            foreach ($rows as $r) {
                if ($room && isset($r['class_room']) && trim((string)$r['class_room']) === trim((string)$room)) return $r['id'];
            }
            return $rows[0]['id'];
        };

        $summary = ['processed' => 0, 'inserted' => 0, 'updated' => 0, 'warnings' => []];

        foreach ($rows as $row) {
            $studentId = $row['student_id'] ?? null;
            $studentNo = $row['student_no'] ?? null;
            $status = $row['status'] ?? null;
            $classRoom = $row['class_room'] ?? '';
            $reportId = $row['report_id'] ?? null;

            if (!$studentId && $studentNo) {
                $studentId = $resolveStudentId($studentNo);
            }
            if (!$studentId) {
                $summary['warnings'][] = 'Missing student_id for entry with student_no ' . ($studentNo ?? '(unknown)');
                continue;
            }

            // find report id if not provided
            if (!$reportId) {
                $reportId = $findReportId($classRoom);
            }
            if (!$reportId) {
                $summary['warnings'][] = 'No report found for room ' . ($classRoom ?: '(empty)');
                continue;
            }

            // check existing log for report_id + student_id
            $stmt = $this->pdoTeaching->prepare('SELECT id FROM teaching_attendance_logs WHERE report_id = ? AND student_id = ? LIMIT 1');
            $stmt->execute([$reportId, $studentId]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // update
                if ($hasClassRoom) {
                    $u = $this->pdoTeaching->prepare('UPDATE teaching_attendance_logs SET status = ?, class_room = ? WHERE id = ?');
                    $u->execute([$status, $classRoom, $exists['id']]);
                } else {
                    $u = $this->pdoTeaching->prepare('UPDATE teaching_attendance_logs SET status = ? WHERE id = ?');
                    $u->execute([$status, $exists['id']]);
                }
                $summary['updated']++;
            } else {
                // insert
                if ($hasClassRoom) {
                    $i = $this->pdoTeaching->prepare('INSERT INTO teaching_attendance_logs (report_id, student_id, status, class_room) VALUES (?, ?, ?, ?)');
                    $i->execute([$reportId, $studentId, $status, $classRoom]);
                } else {
                    $i = $this->pdoTeaching->prepare('INSERT INTO teaching_attendance_logs (report_id, student_id, status) VALUES (?, ?, ?)');
                    $i->execute([$reportId, $studentId, $status]);
                }
                $summary['inserted']++;
            }
            $summary['processed']++;
        }

        return $summary;
    }

    /**
     * ดึง attendance logs สำหรับ subject+date (และกรองตามห้องถ้ามี)
     * returns array of { student_id, student_no, student_name, status, class_room, report_id }
     */
    public function getAttendanceByDate($subjectId, $date, $rooms = [], $teacherId = null)
    {
        if (!$subjectId || !$date) return ['reports'=>[], 'studentsByRoom'=>[]];

        // ดึง report ids ของ subject+date (และกรองครูถ้ามี)
        $sql = "SELECT id, report_date, period_start, period_end, class_room, teacher_id FROM teaching_reports WHERE subject_id = ? AND report_date = ?";
        $params = [$subjectId, $date];
        if ($teacherId) { $sql .= ' AND teacher_id = ?'; $params[] = $teacherId; }
        $stmt = $this->pdoTeaching->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$reports) return ['reports'=>[], 'studentsByRoom'=>[]];

        $reportIds = array_column($reports, 'id');

        $in = implode(',', array_fill(0, count($reportIds), '?'));
        // check whether teaching_attendance_logs has class_room column
        $hasClassRoom = false;
        try {
            $stmtCol = $this->pdoTeaching->query("SHOW COLUMNS FROM teaching_attendance_logs LIKE 'class_room'");
            if ($stmtCol && $stmtCol->fetch()) $hasClassRoom = true;
        } catch (\Exception $e) {
            // ignore, assume column missing
            $hasClassRoom = false;
        }

        if ($hasClassRoom) {
            $sqlLogs = "SELECT report_id, student_id, status, class_room FROM teaching_attendance_logs WHERE report_id IN ($in)";
        } else {
            $sqlLogs = "SELECT report_id, student_id, status FROM teaching_attendance_logs WHERE report_id IN ($in)";
        }
        $stmtLogs = $this->pdoTeaching->prepare($sqlLogs);
        $stmtLogs->execute($reportIds);
        $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        // If logs exist but don't include class_room, populate it from the report rows
        $reportRoomMap = [];
        foreach ($reports as $r) {
            $reportRoomMap[$r['id']] = isset($r['class_room']) ? $r['class_room'] : '';
        }
        foreach ($logs as &$ll) {
            if (!isset($ll['class_room']) || $ll['class_room'] === null) {
                $ll['class_room'] = $reportRoomMap[$ll['report_id']] ?? '';
            }
        }
        unset($ll);

        // If rooms filter is provided, keep only logs matching rooms (normalize both)
        if (!empty($rooms)) {
            $normRooms = array_map(function($r){ return trim(mb_strtolower(str_replace('ห้อง ', '', $r))); }, $rooms);
            $logs = array_filter($logs, function($l) use ($normRooms){
                $cr = isset($l['class_room']) ? trim(mb_strtolower(str_replace('ห้อง ', '', $l['class_room']))) : '';
                return in_array($cr, $normRooms, true);
            });
        }

        // If no logs exist, try to parse student lists embedded in the report HTML fields
        if (empty($logs)) {
            // gather parsed entries per room first
            $fieldsMap = [
                'present_students' => 'present',
                'absent_students' => 'absent',
                'late_students' => 'late',
                'sick_students' => 'sick',
                'personal_students' => 'personal',
                'activity_students' => 'activity',
                'truant_students' => 'truant'
            ];

            $tmpByRoom = [];
            $allStuNos = [];
            foreach ($reports as $rep) {
                $room = isset($rep['class_room']) ? trim($rep['class_room']) : '';
                if (!isset($tmpByRoom[$room])) $tmpByRoom[$room] = [];

                foreach ($fieldsMap as $field => $status) {
                    if (empty($rep[$field])) continue;
                    // strip tags to simplify parsing
                    $text = trim(strip_tags($rep[$field]));
                    if ($text === '') continue;
                    // matches like [1][27625]Name... repeated
                    if (preg_match_all('/\[\d+\]\[(\d+)\]([^\[]+)/u', $text, $m)) {
                        foreach ($m[1] as $idx => $stuNo) {
                            $stuNo = trim($stuNo);
                            $stuName = isset($m[2][$idx]) ? trim($m[2][$idx]) : '';
                            $tmpByRoom[$room][] = [
                                'student_no' => $stuNo,
                                'student_name_parsed' => $stuName,
                                'status' => $status,
                                'report_id' => $rep['id'] ?? null
                            ];
                            $allStuNos[$stuNo] = $stuNo;
                        }
                    }
                }
            }

            // Resolve Stu_no -> Stu_id and full student name from students DB
            $studentsMapByNo = [];
            if (!empty($allStuNos)) {
                $placeholders = implode(',', array_fill(0, count($allStuNos), '?'));
                $stmtStu = $this->pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_no IN ($placeholders) AND Stu_status = 1");
                $stmtStu->execute(array_values($allStuNos));
                $rows = $stmtStu->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $r) {
                    $studentsMapByNo[$r['Stu_no']] = ['Stu_id' => $r['Stu_id'], 'Stu_no' => $r['Stu_no'], 'fullname' => $r['fullname']];
                }
            }

            // Build final studentsByRoom from tmpByRoom
            $studentsByRoom = [];
            foreach ($tmpByRoom as $room => $list) {
                $map = [];
                foreach ($list as $entry) {
                    $stuNo = $entry['student_no'];
                    $sid = isset($studentsMapByNo[$stuNo]) ? $studentsMapByNo[$stuNo]['Stu_id'] : null;
                    $sname = isset($studentsMapByNo[$stuNo]) ? $studentsMapByNo[$stuNo]['fullname'] : $entry['student_name_parsed'];
                    // use student_no as sorting key if Stu_no exists
                    $key = $sid ? $sid : ('no_' . $stuNo);
                    // avoid duplicates, prefer later report_id
                    if (!isset($map[$key]) || ($entry['report_id'] && $entry['report_id'] > ($map[$key]['report_id'] ?? 0))) {
                        $map[$key] = [
                            'report_id' => $entry['report_id'],
                            'student_id' => $sid,
                            'student_no' => $stuNo,
                            'student_name' => $sname,
                            'status' => $entry['status'],
                            'class_room' => $room
                        ];
                    }
                }
                // convert to array and sort by student_no numeric when possible
                $arr = array_values($map);
                usort($arr, function($a, $b){
                    $na = is_numeric($a['student_no']) ? intval($a['student_no']) : 0;
                    $nb = is_numeric($b['student_no']) ? intval($b['student_no']) : 0;
                    return $na <=> $nb;
                });
                $studentsByRoom[$room] = $arr;
            }

            return ['reports' => $reports, 'studentsByRoom' => $studentsByRoom];
        }

        // collect student ids
        $studentIds = array_values(array_unique(array_map(function($l){ return $l['student_id']; }, $logs)));
        $students = [];
        if (!empty($studentIds)) {
            $in2 = implode(',', array_fill(0, count($studentIds), '?'));
            $stmtStu = $this->pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id IN ($in2) AND Stu_status = 1");
            $stmtStu->execute($studentIds);
            $rows = $stmtStu->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $students[$r['Stu_id']] = ['Stu_no' => $r['Stu_no'], 'fullname' => $r['fullname']];
            }
        }

        // Group logs by room, then by student. For each student pick latest report_id status (most recent)
        $studentsByRoom = [];
        foreach ($logs as $l) {
            $room = $l['class_room'] ?? '';
            $normRoom = trim($room);
            if (!isset($studentsByRoom[$normRoom])) $studentsByRoom[$normRoom] = [];
            $sid = $l['student_id'];
            // keep entry if not exists, or replace if this log's report_id is newer
            if (!isset($studentsByRoom[$normRoom][$sid]) || (isset($studentsByRoom[$normRoom][$sid]['report_id']) && $l['report_id'] > $studentsByRoom[$normRoom][$sid]['report_id'])) {
                $studentsByRoom[$normRoom][$sid] = [
                    'report_id' => $l['report_id'],
                    'student_id' => $sid,
                    'student_no' => isset($students[$sid]) ? $students[$sid]['Stu_no'] : null,
                    'student_name' => isset($students[$sid]) ? $students[$sid]['fullname'] : null,
                    'status' => $l['status'] ?? null,
                    'class_room' => $room
                ];
            }
        }

        // convert inner maps to arrays and sort by student_no
        foreach ($studentsByRoom as $r => $map) {
            $arr = array_values($map);
            usort($arr, function($a, $b){ return ($a['student_no'] ?? 0) <=> ($b['student_no'] ?? 0); });
            $studentsByRoom[$r] = $arr;
        }

        // Return reports (as fetched) and students grouped by room
        return ['reports' => $reports, 'studentsByRoom' => $studentsByRoom];
    }

    /**
     * สร้างข้อมูลตารางรายเดือน (subject + class_room + month)
     * คืนค่าเป็นโครงสร้าง { days: [...], students: [...], meta: {...}, summary: {...} }
     */
    public function getMonthlyGrid($subjectId, $classRoom, $month, $teacherId = null)
    {
        $statusMeta = $this->getStatusMeta();
        $summaryColumns = $this->getSummaryColumns();
        $totalsTemplate = [];
        foreach ($summaryColumns as $col) {
            $totalsTemplate[$col['key']] = 0;
        }
        $totalsTemplate['other'] = 0;

        $result = [
            'days' => [],
            'students' => [],
            'meta' => [
                'class_room' => $classRoom,
                'subject_id' => $subjectId,
                'month' => $month,
                'report_dates' => []
            ],
            'summary' => [
                'status_meta' => $statusMeta,
                'columns' => $summaryColumns,
                'totals_template' => $totalsTemplate
            ]
        ];

        if (!$subjectId || !$classRoom || !$month) {
            return $result;
        }

        try {
            $start = new DateTime($month . '-01');
        } catch (\Exception $e) {
            return $result;
        }
        $end = (clone $start)->modify('last day of this month');

        $period = new DatePeriod($start, new DateInterval('P1D'), (clone $end)->modify('+1 day'));
        foreach ($period as $day) {
            $result['days'][] = [
                'day' => (int) $day->format('j'),
                'date' => $day->format('Y-m-d'),
                'weekday' => $day->format('D'),
                'weekday_th' => $this->weekdayShortTh($day)
            ];
        }

        $roomTokens = $this->extractRoomTokens($classRoom);
        $normRoom = $roomTokens['normalized'];

        $subjectMeta = $this->getSubjectMeta($subjectId);
        $subjectLevel = $subjectMeta['level'] ?? null;
        if ($subjectLevel !== null) {
            $result['meta']['level'] = $subjectLevel;
        }

        $sql = "SELECT id, report_date, class_room, period_start, period_end FROM teaching_reports " .
               "WHERE subject_id = ? AND report_date BETWEEN ? AND ?";
        $params = [$subjectId, $start->format('Y-m-d'), $end->format('Y-m-d')];
        if ($normRoom !== '') {
            $sql .= " AND (" .
                "TRIM(REPLACE(COALESCE(class_room,''), 'ห้อง ', '')) = ? OR " .
                "TRIM(COALESCE(class_room,'')) = ? OR " .
                "TRIM(SUBSTRING_INDEX(COALESCE(class_room,''), '/', -1)) = ?" .
            ")";
            $params[] = $normRoom;
            $params[] = $roomTokens['clean'] !== '' ? $roomTokens['clean'] : $classRoom;
            $params[] = $roomTokens['number'] ?? $normRoom;
        }
        if ($teacherId) {
            $sql .= ' AND teacher_id = ?';
            $params[] = $teacherId;
        }
        $sql .= ' ORDER BY report_date ASC, period_start ASC';

        $stmt = $this->pdoTeaching->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reportIds = [];
        $reportDateMap = [];
        $reportDates = [];
        foreach ($reports as $rep) {
            $reportIds[] = $rep['id'];
            $reportDateMap[$rep['id']] = $rep['report_date'];
            $reportDates[$rep['report_date']] = true;
        }
        $result['meta']['report_dates'] = array_keys($reportDates);

        // roster จากฐาน student
        $studentRoster = $this->loadStudentRoster($roomTokens, $subjectLevel, $classRoom);

        $studentMap = [];
        foreach ($studentRoster as $stu) {
            $studentMap[$stu['Stu_id']] = [
                'student_id' => $stu['Stu_id'],
                'student_no' => $stu['Stu_no'],
                'student_name' => $stu['fullname'],
                'statuses' => [],
                'totals' => $totalsTemplate
            ];
        }

        $studentStatuses = [];
        if (!empty($reportIds)) {
            $in = implode(',', array_fill(0, count($reportIds), '?'));
            $stmtLogs = $this->pdoTeaching->prepare("SELECT report_id, student_id, status FROM teaching_attendance_logs WHERE report_id IN ($in)");
            $stmtLogs->execute($reportIds);
            $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($logs as $log) {
                $rid = $log['report_id'];
                $date = $reportDateMap[$rid] ?? null;
                if (!$date) continue;
                $sid = $log['student_id'];
                $status = $log['status'] ?? '';
                if (!isset($statusMeta[$status])) {
                    $statusMeta[$status] = [
                        'label' => $status ?: 'ไม่ระบุ',
                        'emoji' => '❔',
                        'summary_key' => 'other',
                        'priority' => 5,
                        'cell_class' => 'status-other'
                    ];
                }
                $current = $studentStatuses[$sid][$date] ?? null;
                if (!$current) {
                    $studentStatuses[$sid][$date] = $status;
                } else {
                    $currPriority = $statusMeta[$current]['priority'] ?? 0;
                    $incoming = $statusMeta[$status]['priority'] ?? 0;
                    if ($incoming > $currPriority) {
                        $studentStatuses[$sid][$date] = $status;
                    }
                }
            }
        }

        // เติมนักเรียนที่อยู่ใน log แต่ไม่มีใน roster
        $missing = [];
        foreach ($studentStatuses as $sid => $dates) {
            if (!isset($studentMap[$sid])) {
                $missing[$sid] = $sid;
            }
        }
        if (!empty($missing)) {
            try {
                $placeholders = implode(',', array_fill(0, count($missing), '?'));
                $stmtMissing = $this->pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id IN ($placeholders) AND Stu_status = 1");
                $stmtMissing->execute(array_values($missing));
                $rows = $stmtMissing->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $stu) {
                    $studentMap[$stu['Stu_id']] = [
                        'student_id' => $stu['Stu_id'],
                        'student_no' => $stu['Stu_no'],
                        'student_name' => $stu['fullname'],
                        'statuses' => [],
                        'totals' => $totalsTemplate
                    ];
                    unset($missing[$stu['Stu_id']]);
                }
            } catch (\Exception $e) {
                // ignore
            }
            foreach ($missing as $sid) {
                $studentMap[$sid] = [
                    'student_id' => $sid,
                    'student_no' => null,
                    'student_name' => 'ID ' . $sid,
                    'statuses' => [],
                    'totals' => $totalsTemplate
                ];
            }
        }

        foreach ($studentMap as $sid => &$info) {
            $info['statuses'] = $studentStatuses[$sid] ?? [];
            $totals = $totalsTemplate;
            foreach ($info['statuses'] as $date => $status) {
                $key = $statusMeta[$status]['summary_key'] ?? 'other';
                if (!isset($totals[$key])) {
                    $totals[$key] = 0;
                }
                $totals[$key]++;
            }
            $info['totals'] = $totals;
        }
        unset($info);

        $students = array_values($studentMap);
        usort($students, function($a, $b) {
            $aNo = is_numeric($a['student_no']) ? (int) $a['student_no'] : 9999;
            $bNo = is_numeric($b['student_no']) ? (int) $b['student_no'] : 9999;
            if ($aNo === $bNo) {
                return strcmp($a['student_name'] ?? '', $b['student_name'] ?? '');
            }
            return $aNo <=> $bNo;
        });

        $result['students'] = $students;
        $result['meta']['student_count'] = count($students);
        $result['meta']['month_label'] = $start->format('F Y');
        return $result;
    }

    protected function normalizeRoomLabel($room)
    {
        $tokens = $this->extractRoomTokens($room);
        return $tokens['normalized'];
    }

    protected function extractRoomTokens($room)
    {
        $raw = trim((string) $room);
        $clean = $raw;
        if ($clean !== '') {
            $clean = preg_replace('/^(ห้องเรียน|ห้อง)\s*/u', '', $clean);
            $clean = preg_replace('/^room\s*/i', '', $clean);
            $clean = trim($clean);
        }

        $number = null;
        if ($clean !== '' && preg_match('/(\d{1,2})$/u', $clean, $match)) {
            $number = ltrim($match[1], '0');
            if ($number === '') {
                $number = $match[1];
            }
        }

        $normalized = $number ?? $clean;

        return [
            'raw' => $raw,
            'clean' => $clean,
            'normalized' => $normalized ?? '',
            'number' => $number,
        ];
    }

    protected function loadStudentRoster(array $roomTokens, $subjectLevel, $classRoom)
    {
        $conditions = ['Stu_status = 1'];
        $params = [];
        $hasRoomConstraint = false;

        if ($roomTokens['number'] !== null) {
            $conditions[] = 'Stu_room = ?';
            $params[] = (int) $roomTokens['number'];
            $hasRoomConstraint = true;
        } elseif ($roomTokens['normalized'] !== '') {
            $conditions[] = "(TRIM(REPLACE(COALESCE(Stu_room,''), 'ห้อง ', '')) = ? OR TRIM(COALESCE(Stu_room,'')) = ?)";
            $params[] = $roomTokens['normalized'];
            $params[] = $roomTokens['clean'] !== '' ? $roomTokens['clean'] : $classRoom;
            $hasRoomConstraint = true;
        }

        $levelDigit = $this->normalizeLevelDigit($subjectLevel, $roomTokens['raw']);
        if ($levelDigit !== null) {
            $conditions[] = '(CAST(Stu_major AS CHAR) = ? OR CAST(Stu_major AS CHAR) LIKE ?)';
            $params[] = $levelDigit;
            $params[] = $levelDigit . '%';
        }

        if (!$hasRoomConstraint) {
            return [];
        }

        $sql = "SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE " . implode(' AND ', $conditions) . " ORDER BY Stu_no ASC, Stu_id DESC";
        try {
            $stmt = $this->pdoUsers->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function normalizeLevelDigit($level, $roomLabel)
    {
        if ($level !== null && $level !== '') {
            $digits = preg_replace('/\D+/', '', (string) $level);
            if ($digits !== '') {
                return (string) (int) $digits;
            }
        }
        if ($roomLabel !== null && $roomLabel !== '') {
            if (preg_match('/ม\.?\s*(\d)/u', $roomLabel, $match)) {
                return (string) (int) $match[1];
            }
        }
        return null;
    }

    protected function getSubjectMeta($subjectId)
    {
        static $cache = [];
        if (isset($cache[$subjectId])) {
            return $cache[$subjectId];
        }
        $stmt = $this->pdoTeaching->prepare('SELECT id, level FROM subjects WHERE id = ? LIMIT 1');
        $stmt->execute([$subjectId]);
        $cache[$subjectId] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return $cache[$subjectId];
    }

    protected function weekdayShortTh(DateTime $date)
    {
        $map = [
            'Mon' => 'จ',
            'Tue' => 'อ',
            'Wed' => 'พ',
            'Thu' => 'พฤ',
            'Fri' => 'ศ',
            'Sat' => 'ส',
            'Sun' => 'อา'
        ];
        return $map[$date->format('D')] ?? $date->format('D');
    }

    protected function getStatusMeta()
    {
        return [
            'ขาดเรียน' => [
                'label' => 'ขาดเรียน',
                'emoji' => '❌',
                'summary_key' => 'absent',
                'priority' => 120,
                'cell_class' => 'status-absent'
            ],
            'โดดเรียน' => [
                'label' => 'โดดเรียน',
                'emoji' => '⚠️',
                'summary_key' => 'truant',
                'priority' => 110,
                'cell_class' => 'status-truant'
            ],
            'ลาป่วย' => [
                'label' => 'ลาป่วย',
                'emoji' => '🤒',
                'summary_key' => 'sick',
                'priority' => 100,
                'cell_class' => 'status-sick'
            ],
            'ลากิจ' => [
                'label' => 'ลากิจ',
                'emoji' => '📝',
                'summary_key' => 'personal',
                'priority' => 90,
                'cell_class' => 'status-personal'
            ],
            'เข้าร่วมกิจกรรม' => [
                'label' => 'เข้าร่วมกิจกรรม',
                'emoji' => '🎉',
                'summary_key' => 'activity',
                'priority' => 80,
                'cell_class' => 'status-activity'
            ],
            'มาสาย' => [
                'label' => 'มาสาย',
                'emoji' => '⏰',
                'summary_key' => 'late',
                'priority' => 70,
                'cell_class' => 'status-late'
            ],
            'มาเรียน' => [
                'label' => 'มาเรียน',
                'emoji' => '✅',
                'summary_key' => 'present',
                'priority' => 60,
                'cell_class' => 'status-present'
            ]
        ];
    }

    protected function getSummaryColumns()
    {
        return [
            ['key' => 'present', 'label' => 'มาเรียน', 'emoji' => '✅'],
            ['key' => 'absent', 'label' => 'ขาดเรียน', 'emoji' => '❌'],
            ['key' => 'late', 'label' => 'มาสาย', 'emoji' => '⏰'],
            ['key' => 'sick', 'label' => 'ลาป่วย', 'emoji' => '🤒'],
            ['key' => 'personal', 'label' => 'ลากิจ', 'emoji' => '📝'],
            ['key' => 'activity', 'label' => 'กิจกรรม', 'emoji' => '🎉'],
            ['key' => 'truant', 'label' => 'โดดเรียน', 'emoji' => '⚠️']
        ];
    }
}
