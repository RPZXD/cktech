<?php
namespace App\Models;

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
            $stmt = $this->pdoUsers->prepare('SELECT Stu_id FROM student WHERE Stu_no = ? LIMIT 1');
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
                $stmtStu = $this->pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_no IN ($placeholders)");
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
            $stmtStu = $this->pdoUsers->prepare("SELECT Stu_id, Stu_no, CONCAT(Stu_pre,Stu_name,' ',Stu_sur) AS fullname FROM student WHERE Stu_id IN ($in2)");
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
}
