<?php
namespace App\Models;

require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
use App\DatabaseTeachingReport;
use PDO;
use Exception;

class Supervision {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = new DatabaseTeachingReport();
        $this->pdo = $this->db->getPDO();
        $this->createTable();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO supervisions (
                teacher_id, teacher_name, position, academic_level, subject_group,
                subject_name, subject_code, class_level, supervision_round, supervision_date,
                term, pee,
                plan_effective, plan_correct, plan_activities, plan_media, plan_assessment,
                teach_techniques, teach_media, teach_assessment, teach_explanation, teach_control,
                teach_thinking, teach_adaptation, teach_integration, teach_language,
                eval_variety, eval_standards, eval_criteria, eval_feedback, eval_evidence,
                env_classroom, env_interaction, env_safety, env_management, env_rules, env_behavior,
                total_score, quality_level, lesson_plan, supervisor_photos, classroom_photos
            ) VALUES (
                :teacher_id, :teacher_name, :position, :academic_level, :subject_group,
                :subject_name, :subject_code, :class_level, :supervision_round, :supervision_date,
                :term, :pee,
                :plan_effective, :plan_correct, :plan_activities, :plan_media, :plan_assessment,
                :teach_techniques, :teach_media, :teach_assessment, :teach_explanation, :teach_control,
                :teach_thinking, :teach_adaptation, :teach_integration, :teach_language,
                :eval_variety, :eval_standards, :eval_criteria, :eval_feedback, :eval_evidence,
                :env_classroom, :env_interaction, :env_safety, :env_management, :env_rules, :env_behavior,
                :total_score, :quality_level, :lesson_plan, :supervisor_photos, :classroom_photos
            )";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($data);
            
            if ($result) {
                return $this->pdo->lastInsertId();
            } else {
                // Log detailed error information
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error: " . print_r($errorInfo, true));
                error_log("Data being inserted: " . print_r($data, true));
                throw new Exception("Failed to insert data: " . $errorInfo[2]);
            }
        } catch (Exception $e) {
            error_log("Error creating supervision: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Data: " . print_r($data, true));
            throw new Exception("Failed to create supervision: " . $e->getMessage());
        }
    }

    public function getAll($teacherId = null) {
        try {
            $sql = "SELECT * FROM supervisions";
            $params = [];
            
            if ($teacherId) {
                $sql .= " WHERE teacher_id = :teacher_id";
                $params['teacher_id'] = $teacherId;
            }
            
            $sql .= " ORDER BY supervision_date DESC, created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $supervisions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Always enrich with teacher data for better filtering and display
            require_once __DIR__ . '/../classes/DatabaseUsers.php';
            $dbUsers = new \App\DatabaseUsers();
            $usersPdo = $dbUsers->getPDO();
            
            foreach ($supervisions as &$supervision) {
                $teacherSql = "SELECT Teach_name, Teach_major FROM teacher WHERE Teach_id = ?";
                $teacherStmt = $usersPdo->prepare($teacherSql);
                $teacherStmt->execute([$supervision['teacher_id']]);
                $teacherData = $teacherStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($teacherData) {
                    $supervision['teacher_full_name'] = $teacherData['Teach_name'];
                    $supervision['teacher_subject_group'] = $teacherData['Teach_major'];
                }
            }
            
            return $supervisions;
        } catch (Exception $e) {
            error_log("Error fetching supervisions: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $sql = "SELECT * FROM supervisions WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching supervision by ID: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $sql = "UPDATE supervisions SET
                teacher_name = :teacher_name, position = :position, academic_level = :academic_level,
                subject_group = :subject_group, subject_name = :subject_name, subject_code = :subject_code,
                class_level = :class_level, supervision_round = :supervision_round, supervision_date = :supervision_date,
                term = :term, pee = :pee,
                plan_effective = :plan_effective, plan_correct = :plan_correct, plan_activities = :plan_activities,
                plan_media = :plan_media, plan_assessment = :plan_assessment,
                teach_techniques = :teach_techniques, teach_media = :teach_media, teach_assessment = :teach_assessment,
                teach_explanation = :teach_explanation, teach_control = :teach_control, teach_thinking = :teach_thinking,
                teach_adaptation = :teach_adaptation, teach_integration = :teach_integration, teach_language = :teach_language,
                eval_variety = :eval_variety, eval_standards = :eval_standards, eval_criteria = :eval_criteria,
                eval_feedback = :eval_feedback, eval_evidence = :eval_evidence,
                env_classroom = :env_classroom, env_interaction = :env_interaction, env_safety = :env_safety,
                env_management = :env_management, env_rules = :env_rules, env_behavior = :env_behavior,
                total_score = :total_score, quality_level = :quality_level,
                lesson_plan = :lesson_plan, supervisor_photos = :supervisor_photos, classroom_photos = :classroom_photos,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

            $data['id'] = $id;
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute($data);
        } catch (Exception $e) {
            error_log("Error updating supervision: " . $e->getMessage());
            throw new Exception("Failed to update supervision: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            // Get file info before deleting
            $supervision = $this->getById($id);
            
            // Delete the record
            $sql = "DELETE FROM supervisions WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(['id' => $id]);
            
            // Delete associated files
            if ($result && $supervision) {
                $this->deleteFiles($supervision);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting supervision: " . $e->getMessage());
            return false;
        }
    }

    public function uploadFiles($files) {
        $uploadDir = '../uploads/supervision/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedFiles = [];

        foreach ($files as $fieldName => $fileData) {
            if (is_array($fileData['name'])) {
                // Multiple files (for photos)
                $uploadedFiles[$fieldName] = [];
                for ($i = 0; $i < count($fileData['name']); $i++) {
                    if ($fileData['error'][$i] === UPLOAD_ERR_OK) {
                        // Validate file type for lesson_plan
                        if ($fieldName === 'lesson_plan' && $fileData['type'][$i] !== 'application/pdf') {
                            throw new Exception('แผนการจัดการเรียนรู้ต้องเป็นไฟล์ PDF เท่านั้น');
                        }
                        
                        $filename = $this->generateUniqueFilename($fileData['name'][$i]);
                        $targetPath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($fileData['tmp_name'][$i], $targetPath)) {
                            // Store relative path from web root
                            $uploadedFiles[$fieldName][] = 'uploads/supervision/' . $filename;
                        }
                    }
                }
            } else {
                // Single file
                if ($fileData['error'] === UPLOAD_ERR_OK) {
                    // Validate file type for lesson_plan
                    if ($fieldName === 'lesson_plan' && $fileData['type'] !== 'application/pdf') {
                        throw new Exception('แผนการจัดการเรียนรู้ต้องเป็นไฟล์ PDF เท่านั้น');
                    }
                    
                    $filename = $this->generateUniqueFilename($fileData['name']);
                    $targetPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
                        // Store relative path from web root
                        $uploadedFiles[$fieldName] = 'uploads/supervision/' . $filename;
                    }
                }
            }
        }

        return $uploadedFiles;
    }

    private function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        
        return $baseName . '_' . uniqid() . '.' . $extension;
    }

    private function deleteFiles($supervision) {
        $uploadDir = '../uploads/supervision/';
        
        // Updated file fields - only existing fields in database
        $fileFields = ['lesson_plan', 'supervisor_photos', 'classroom_photos'];
        
        foreach ($fileFields as $field) {
            if (!empty($supervision[$field])) {
                if (strpos($supervision[$field], ',') !== false) {
                    // Multiple files
                    $files = explode(',', $supervision[$field]);
                    foreach ($files as $file) {
                        $file = trim($file);
                        // Handle both old and new path formats
                        if (strpos($file, 'uploads/') === 0) {
                            $filePath = '../' . $file;
                        } else {
                            $filePath = $uploadDir . $file;
                        }
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                } else {
                    // Single file
                    $file = $supervision[$field];
                    // Handle both old and new path formats
                    if (strpos($file, 'uploads/') === 0) {
                        $filePath = '../' . $file;
                    } else {
                        $filePath = $uploadDir . $file;
                    }
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        }
    }

    public function removeFile($supervisionId, $filename, $fileType) {
        try {
            $supervision = $this->getById($supervisionId);
            if (!$supervision) {
                return false;
            }

            $currentFiles = $supervision[$fileType];
            
            if (strpos($currentFiles, ',') !== false) {
                // Multiple files
                $files = explode(',', $currentFiles);
                $files = array_filter($files, function($file) use ($filename) {
                    return trim($file) !== $filename;
                });
                $newFiles = implode(',', $files);
            } else {
                // Single file
                $newFiles = ($currentFiles === $filename) ? '' : $currentFiles;
            }

            // Update database
            $sql = "UPDATE supervisions SET $fileType = :files WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                'files' => $newFiles,
                'id' => $supervisionId
            ]);
        } catch (Exception $e) {
            error_log("Error removing file: " . $e->getMessage());
            return false;
        }
    }

    public function getFileInfo($supervisionId) {
        try {
            $supervision = $this->getById($supervisionId);
            if (!$supervision) {
                return [];
            }

            return [
                'lesson_plan' => $supervision['lesson_plan'],
                'supervisor_photos' => $supervision['supervisor_photos'],
                'classroom_photos' => $supervision['classroom_photos']
            ];
        } catch (Exception $e) {
            error_log("Error getting file info: " . $e->getMessage());
            return [];
        }
    }

    public function getStatistics($teacherId = null) {
        try {
            $sql = "SELECT 
                COUNT(*) as total_supervisions,
                AVG(total_score) as avg_score,
                MAX(total_score) as max_score,
                MIN(total_score) as min_score,
                SUM(CASE WHEN quality_level = 'ดีเยี่ยม' THEN 1 ELSE 0 END) as excellent_count,
                SUM(CASE WHEN quality_level = 'ดีมาก' THEN 1 ELSE 0 END) as very_good_count,
                SUM(CASE WHEN quality_level = 'ดี' THEN 1 ELSE 0 END) as good_count,
                SUM(CASE WHEN quality_level = 'พอใช้' THEN 1 ELSE 0 END) as fair_count,
                SUM(CASE WHEN quality_level = 'ควรปรับปรุง' THEN 1 ELSE 0 END) as poor_count
                FROM supervisions";
            
            $params = [];
            if ($teacherId) {
                $sql .= " WHERE teacher_id = :teacher_id";
                $params['teacher_id'] = $teacherId;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [];
        }
    }

    public function createTable() {
        try {
            // First, check if table exists and add missing columns
            $this->updateTableStructure();
            
            $sql = "CREATE TABLE IF NOT EXISTS supervisions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                teacher_name VARCHAR(255) NOT NULL,
                position VARCHAR(100),
                academic_level VARCHAR(100),
                subject_group VARCHAR(100),
                subject_name VARCHAR(255),
                subject_code VARCHAR(50),
                class_level VARCHAR(50),
                supervision_round INT DEFAULT 1,
                term VARCHAR(10),
                pee VARCHAR(10),
                supervision_date DATE NOT NULL,
                
                -- แบบประเมินด้านที่ 1: การจัดทำแผน (Teacher)
                plan_effective TINYINT DEFAULT 0,
                plan_correct TINYINT DEFAULT 0,
                plan_activities TINYINT DEFAULT 0,
                plan_media TINYINT DEFAULT 0,
                plan_assessment TINYINT DEFAULT 0,
                
                -- แบบประเมินด้านที่ 2: การจัดการเรียนรู้ (Teacher)
                teach_techniques TINYINT DEFAULT 0,
                teach_media TINYINT DEFAULT 0,
                teach_assessment TINYINT DEFAULT 0,
                teach_explanation TINYINT DEFAULT 0,
                teach_control TINYINT DEFAULT 0,
                teach_thinking TINYINT DEFAULT 0,
                teach_adaptation TINYINT DEFAULT 0,
                teach_integration TINYINT DEFAULT 0,
                teach_language TINYINT DEFAULT 0,
                
                -- แบบประเมินด้านที่ 3: การประเมินผล (Teacher)
                eval_variety TINYINT DEFAULT 0,
                eval_standards TINYINT DEFAULT 0,
                eval_criteria TINYINT DEFAULT 0,
                eval_feedback TINYINT DEFAULT 0,
                eval_evidence TINYINT DEFAULT 0,
                
                -- แบบประเมินด้านที่ 4: สภาพแวดล้อม (Teacher)
                env_classroom TINYINT DEFAULT 0,
                env_interaction TINYINT DEFAULT 0,
                env_safety TINYINT DEFAULT 0,
                env_management TINYINT DEFAULT 0,
                env_rules TINYINT DEFAULT 0,
                env_behavior TINYINT DEFAULT 0,
                
                total_score INT DEFAULT 0,
                quality_level VARCHAR(50),
                
                -- Department Head Evaluation Fields
                -- ด้านที่ 1: การจัดทำแผน (Department Head)
                dept_plan_effective TINYINT DEFAULT 0,
                dept_plan_correct TINYINT DEFAULT 0,
                dept_plan_activities TINYINT DEFAULT 0,
                dept_plan_media TINYINT DEFAULT 0,
                dept_plan_assessment TINYINT DEFAULT 0,
                
                -- ด้านที่ 2: การจัดการเรียนรู้ (Department Head)
                dept_teach_techniques TINYINT DEFAULT 0,
                dept_teach_media TINYINT DEFAULT 0,
                dept_teach_assessment TINYINT DEFAULT 0,
                dept_teach_explanation TINYINT DEFAULT 0,
                dept_teach_control TINYINT DEFAULT 0,
                dept_teach_thinking TINYINT DEFAULT 0,
                dept_teach_adaptation TINYINT DEFAULT 0,
                dept_teach_integration TINYINT DEFAULT 0,
                dept_teach_language TINYINT DEFAULT 0,
                
                -- ด้านที่ 3: การประเมินผล (Department Head)
                dept_eval_variety TINYINT DEFAULT 0,
                dept_eval_standards TINYINT DEFAULT 0,
                dept_eval_criteria TINYINT DEFAULT 0,
                dept_eval_feedback TINYINT DEFAULT 0,
                dept_eval_evidence TINYINT DEFAULT 0,
                
                -- ด้านที่ 4: สภาพแวดล้อม (Department Head)
                dept_env_classroom TINYINT DEFAULT 0,
                dept_env_interaction TINYINT DEFAULT 0,
                dept_env_safety TINYINT DEFAULT 0,
                dept_env_management TINYINT DEFAULT 0,
                dept_env_rules TINYINT DEFAULT 0,
                dept_env_behavior TINYINT DEFAULT 0,
                
                -- Department evaluation summary
                dept_score INT DEFAULT 0,
                dept_quality_level VARCHAR(50),
                dept_observation_notes TEXT,
                dept_reflection_notes TEXT,
                dept_strengths TEXT,
                dept_suggestion TEXT,
                dept_improvements TEXT,
                dept_supervisor_signature VARCHAR(255),
                
                -- Director Evaluation Fields
                -- ด้านที่ 1: การจัดทำแผน (ผู้อำนวยการ)
                dir_plan_effective TINYINT DEFAULT 0,
                dir_plan_correct TINYINT DEFAULT 0,
                dir_plan_activities TINYINT DEFAULT 0,
                dir_plan_media TINYINT DEFAULT 0,
                dir_plan_assessment TINYINT DEFAULT 0,
                
                -- ด้านที่ 2: การจัดการเรียนรู้ (ผู้อำนวยการ)
                dir_teach_techniques TINYINT DEFAULT 0,
                dir_teach_media TINYINT DEFAULT 0,
                dir_teach_assessment TINYINT DEFAULT 0,
                dir_teach_explanation TINYINT DEFAULT 0,
                dir_teach_control TINYINT DEFAULT 0,
                dir_teach_thinking TINYINT DEFAULT 0,
                dir_teach_adaptation TINYINT DEFAULT 0,
                dir_teach_integration TINYINT DEFAULT 0,
                dir_teach_language TINYINT DEFAULT 0,
                
                -- ด้านที่ 3: การประเมินผล (ผู้อำนวยการ)
                dir_eval_variety TINYINT DEFAULT 0,
                dir_eval_standards TINYINT DEFAULT 0,
                dir_eval_criteria TINYINT DEFAULT 0,
                dir_eval_feedback TINYINT DEFAULT 0,
                dir_eval_evidence TINYINT DEFAULT 0,
                
                -- ด้านที่ 4: สภาพแวดล้อม (ผู้อำนวยการ)
                dir_env_classroom TINYINT DEFAULT 0,
                dir_env_interaction TINYINT DEFAULT 0,
                dir_env_safety TINYINT DEFAULT 0,
                dir_env_management TINYINT DEFAULT 0,
                dir_env_rules TINYINT DEFAULT 0,
                dir_env_behavior TINYINT DEFAULT 0,
                
                -- Director evaluation summary
                dir_score INT DEFAULT 0,
                dir_quality_level VARCHAR(50),
                dir_observation_notes TEXT,
                dir_reflection_notes TEXT,
                dir_strengths TEXT,
                dir_improvements TEXT,
                dir_supervisor_signature VARCHAR(255),
                
                -- File attachments
                lesson_plan VARCHAR(500),
                supervisor_photos VARCHAR(500),
                classroom_photos VARCHAR(500),
                
                teacher_id VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_teacher_id (teacher_id),
                INDEX idx_supervision_date (supervision_date),
                INDEX idx_subject_group (subject_group),
                INDEX idx_dept_score (dept_score),
                INDEX idx_total_score (total_score)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

            $this->pdo->exec($sql);
            return true;
        } catch (Exception $e) {
            error_log("Error creating supervisions table: " . $e->getMessage());
            return false;
        }
    }

    private function updateTableStructure() {
        try {
            // Check if all department evaluation columns exist, if not add them
            $deptColumns = [
                // Planning evaluation (Department Head)
                'dept_plan_effective' => 'TINYINT DEFAULT 0 COMMENT "การวางแผนการสอนที่มีประสิทธิภาพ (หัวหน้า)"',
                'dept_plan_correct' => 'TINYINT DEFAULT 0 COMMENT "แผนการจัดการเรียนรู้ถูกต้อง (หัวหน้า)"',
                'dept_plan_activities' => 'TINYINT DEFAULT 0 COMMENT "กิจกรรมการเรียนรู้ (หัวหน้า)"',
                'dept_plan_media' => 'TINYINT DEFAULT 0 COMMENT "การจัดหาสื่อการเรียน (หัวหน้า)"',
                'dept_plan_assessment' => 'TINYINT DEFAULT 0 COMMENT "การวัดและประเมินผล (หัวหน้า)"',
                
                // Teaching evaluation (Department Head)
                'dept_teach_techniques' => 'TINYINT DEFAULT 0 COMMENT "เทคนิคการสอน (หัวหน้า)"',
                'dept_teach_media' => 'TINYINT DEFAULT 0 COMMENT "การใช้สื่อและเทคโนโลยี (หัวหน้า)"',
                'dept_teach_assessment' => 'TINYINT DEFAULT 0 COMMENT "การประเมินระหว่างเรียน (หัวหน้า)"',
                'dept_teach_explanation' => 'TINYINT DEFAULT 0 COMMENT "การอธิบายเนื้อหา (หัวหน้า)"',
                'dept_teach_control' => 'TINYINT DEFAULT 0 COMMENT "การควบคุมชั้นเรียน (หัวหน้า)"',
                'dept_teach_thinking' => 'TINYINT DEFAULT 0 COMMENT "การพัฒนาการคิด (หัวหน้า)"',
                'dept_teach_adaptation' => 'TINYINT DEFAULT 0 COMMENT "การปรับเนื้อหา (หัวหน้า)"',
                'dept_teach_integration' => 'TINYINT DEFAULT 0 COMMENT "การบูรณาการ (หัวหน้า)"',
                'dept_teach_language' => 'TINYINT DEFAULT 0 COMMENT "การใช้ภาษา (หัวหน้า)"',
                
                // Evaluation assessment (Department Head)
                'dept_eval_variety' => 'TINYINT DEFAULT 0 COMMENT "วิธีการประเมินหลากหลาย (หัวหน้า)"',
                'dept_eval_standards' => 'TINYINT DEFAULT 0 COMMENT "สอดคล้องมาตรฐาน (หัวหน้า)"',
                'dept_eval_criteria' => 'TINYINT DEFAULT 0 COMMENT "เกณฑ์การประเมิน (หัวหน้า)"',
                'dept_eval_feedback' => 'TINYINT DEFAULT 0 COMMENT "การให้ข้อมูลย้อนกลับ (หัวหน้า)"',
                'dept_eval_evidence' => 'TINYINT DEFAULT 0 COMMENT "หลักฐานการเรียนรู้ (หัวหน้า)"',
                
                // Environment assessment (Department Head)
                'dept_env_classroom' => 'TINYINT DEFAULT 0 COMMENT "การจัดห้องเรียน (หัวหน้า)"',
                'dept_env_interaction' => 'TINYINT DEFAULT 0 COMMENT "ปฏิสัมพันธ์เชิงบวก (หัวหน้า)"',
                'dept_env_safety' => 'TINYINT DEFAULT 0 COMMENT "ความปลอดภัย (หัวหน้า)"',
                'dept_env_management' => 'TINYINT DEFAULT 0 COMMENT "การจัดการชั้นเรียน (หัวหน้า)"',
                'dept_env_rules' => 'TINYINT DEFAULT 0 COMMENT "กฎกติกาการเรียน (หัวหน้า)"',
                'dept_env_behavior' => 'TINYINT DEFAULT 0 COMMENT "การดูแลพฤติกรรม (หัวหน้า)"',
                
                // Summary fields
                'dept_score' => 'INT DEFAULT 0 COMMENT "คะแนนรวมหัวหน้า"',
                'dept_quality_level' => 'VARCHAR(50) COMMENT "ระดับคุณภาพหัวหน้า"',
                'dept_observation_notes' => 'TEXT COMMENT "บันทึกการสังเกต"',
                'dept_reflection_notes' => 'TEXT COMMENT "การสะท้อนความคิด"',
                'dept_strengths' => 'TEXT COMMENT "จุดเด่น"',
                'dept_suggestion' => 'TEXT COMMENT "ข้อเสนอแนะ"',
                'dept_improvements' => 'TEXT COMMENT "จุดที่ควรปรับปรุง"',
                'dept_supervisor_signature' => 'VARCHAR(255) COMMENT "ลายเซ็นผู้นิเทศ"'
            ];

            foreach ($deptColumns as $column => $definition) {
                $checkSql = "SHOW COLUMNS FROM supervisions LIKE '$column'";
                $stmt = $this->pdo->query($checkSql);
                
                if ($stmt->rowCount() == 0) {
                    $alterSql = "ALTER TABLE supervisions ADD COLUMN $column $definition";
                    $this->pdo->exec($alterSql);
                    error_log("Added $column column to supervisions table");
                }
            }

            // Check if all director evaluation columns exist, if not add them
            $dirColumns = [
                // Planning evaluation (Director)
                'dir_plan_effective' => 'TINYINT DEFAULT 0 COMMENT "การวางแผนการสอนที่มีประสิทธิภาพ (ผู้บริหาร)"',
                'dir_plan_correct' => 'TINYINT DEFAULT 0 COMMENT "แผนการจัดการเรียนรู้ถูกต้อง (ผู้บริหาร)"',
                'dir_plan_activities' => 'TINYINT DEFAULT 0 COMMENT "กิจกรรมการเรียนรู้ (ผู้บริหาร)"',
                'dir_plan_media' => 'TINYINT DEFAULT 0 COMMENT "การจัดหาสื่อการเรียน (ผู้บริหาร)"',
                'dir_plan_assessment' => 'TINYINT DEFAULT 0 COMMENT "การวัดและประเมินผล (ผู้บริหาร)"',
                
                // Teaching evaluation (Director)
                'dir_teach_techniques' => 'TINYINT DEFAULT 0 COMMENT "เทคนิคการสอน (ผู้บริหาร)"',
                'dir_teach_media' => 'TINYINT DEFAULT 0 COMMENT "การใช้สื่อและเทคโนโลยี (ผู้บริหาร)"',
                'dir_teach_assessment' => 'TINYINT DEFAULT 0 COMMENT "การประเมินระหว่างเรียน (ผู้บริหาร)"',
                'dir_teach_explanation' => 'TINYINT DEFAULT 0 COMMENT "การอธิบายเนื้อหา (ผู้บริหาร)"',
                'dir_teach_control' => 'TINYINT DEFAULT 0 COMMENT "การควบคุมชั้นเรียน (ผู้บริหาร)"',
                'dir_teach_thinking' => 'TINYINT DEFAULT 0 COMMENT "การพัฒนาการคิด (ผู้บริหาร)"',
                'dir_teach_adaptation' => 'TINYINT DEFAULT 0 COMMENT "การปรับเนื้อหา (ผู้บริหาร)"',
                'dir_teach_integration' => 'TINYINT DEFAULT 0 COMMENT "การบูรณาการ (ผู้บริหาร)"',
                'dir_teach_language' => 'TINYINT DEFAULT 0 COMMENT "การใช้ภาษา (ผู้บริหาร)"',
                
                // Evaluation assessment (Director)
                'dir_eval_variety' => 'TINYINT DEFAULT 0 COMMENT "วิธีการประเมินหลากหลาย (ผู้บริหาร)"',
                'dir_eval_standards' => 'TINYINT DEFAULT 0 COMMENT "สอดคล้องมาตรฐาน (ผู้บริหาร)"',
                'dir_eval_criteria' => 'TINYINT DEFAULT 0 COMMENT "เกณฑ์การประเมิน (ผู้บริหาร)"',
                'dir_eval_feedback' => 'TINYINT DEFAULT 0 COMMENT "การให้ข้อมูลย้อนกลับ (ผู้บริหาร)"',
                'dir_eval_evidence' => 'TINYINT DEFAULT 0 COMMENT "หลักฐานการเรียนรู้ (ผู้บริหาร)"',
                
                // Environment assessment (Director)
                'dir_env_classroom' => 'TINYINT DEFAULT 0 COMMENT "การจัดห้องเรียน (ผู้บริหาร)"',
                'dir_env_interaction' => 'TINYINT DEFAULT 0 COMMENT "ปฏิสัมพันธ์เชิงบวก (ผู้บริหาร)"',
                'dir_env_safety' => 'TINYINT DEFAULT 0 COMMENT "ความปลอดภัย (ผู้บริหาร)"',
                'dir_env_management' => 'TINYINT DEFAULT 0 COMMENT "การจัดการชั้นเรียน (ผู้บริหาร)"',
                'dir_env_rules' => 'TINYINT DEFAULT 0 COMMENT "กฎกติกาการเรียน (ผู้บริหาร)"',
                'dir_env_behavior' => 'TINYINT DEFAULT 0 COMMENT "การดูแลพฤติกรรม (ผู้บริหาร)"',
                
                // Summary fields (Director)
                'dir_score' => 'INT DEFAULT 0 COMMENT "คะแนนรวมผู้บริหาร"',
                'dir_quality_level' => 'VARCHAR(50) COMMENT "ระดับคุณภาพผู้บริหาร"',
                'dir_observation_notes' => 'TEXT COMMENT "บันทึกการสังเกต (ผู้บริหาร)"',
                'dir_strengths' => 'TEXT COMMENT "จุดเด่น (ผู้บริหาร)"',
                'dir_suggestion' => 'TEXT COMMENT "ข้อเสนอแนะ (ผู้บริหาร)"',
                'dir_supervisor_signature' => 'VARCHAR(255) COMMENT "ลายเซ็นผู้บริหาร"'
            ];

            foreach ($dirColumns as $column => $definition) {
                $checkSql = "SHOW COLUMNS FROM supervisions LIKE '$column'";
                $stmt = $this->pdo->query($checkSql);
                
                if ($stmt->rowCount() == 0) {
                    $alterSql = "ALTER TABLE supervisions ADD COLUMN $column $definition";
                    $this->pdo->exec($alterSql);
                    error_log("Added $column column to supervisions table");
                }
            }

            // Check if term and pee columns exist, if not add them
            $checkColumns = ['term', 'pee'];
            foreach ($checkColumns as $column) {
                $checkSql = "SHOW COLUMNS FROM supervisions LIKE '$column'";
                $stmt = $this->pdo->query($checkSql);
                
                if ($stmt->rowCount() == 0) {
                    $alterSql = "ALTER TABLE supervisions ADD COLUMN $column VARCHAR(10) AFTER supervision_round";
                    $this->pdo->exec($alterSql);
                    error_log("Added $column column to supervisions table");
                }
            }

            // Update varchar lengths to match database
            $updates = [
                "ALTER TABLE supervisions MODIFY COLUMN lesson_plan VARCHAR(500)",
                "ALTER TABLE supervisions MODIFY COLUMN supervisor_photos VARCHAR(500)",
                "ALTER TABLE supervisions MODIFY COLUMN classroom_photos VARCHAR(500)"
            ];

            foreach ($updates as $sql) {
                try {
                    $this->pdo->exec($sql);
                } catch (Exception $e) {
                    // Column might already be correct size, continue
                    error_log("Column update note: " . $e->getMessage());
                }
            }

            // Add indexes for better performance
            $indexes = [
                "CREATE INDEX IF NOT EXISTS idx_dept_evaluation ON supervisions (dept_score, dept_quality_level)",
                "CREATE INDEX IF NOT EXISTS idx_teacher_evaluation ON supervisions (total_score, quality_level)",
                "CREATE INDEX IF NOT EXISTS idx_term_pee ON supervisions (term, pee)"
            ];

            foreach ($indexes as $indexSql) {
                try {
                    $this->pdo->exec($indexSql);
                } catch (Exception $e) {
                    // Index might already exist, continue
                    error_log("Index creation note: " . $e->getMessage());
                }
            }

        } catch (Exception $e) {
            error_log("Error updating table structure: " . $e->getMessage());
        }
    }

    public function updateDepartmentEvaluation($id, $data) {
        $sql = "UPDATE supervisions SET
            dept_plan_effective = :dept_plan_effective,
            dept_plan_correct = :dept_plan_correct,
            dept_plan_activities = :dept_plan_activities,
            dept_plan_media = :dept_plan_media,
            dept_plan_assessment = :dept_plan_assessment,
            dept_teach_techniques = :dept_teach_techniques,
            dept_teach_media = :dept_teach_media,
            dept_teach_assessment = :dept_teach_assessment,
            dept_teach_explanation = :dept_teach_explanation,
            dept_teach_control = :dept_teach_control,
            dept_teach_thinking = :dept_teach_thinking,
            dept_teach_adaptation = :dept_teach_adaptation,
            dept_teach_integration = :dept_teach_integration,
            dept_teach_language = :dept_teach_language,
            dept_eval_variety = :dept_eval_variety,
            dept_eval_standards = :dept_eval_standards,
            dept_eval_criteria = :dept_eval_criteria,
            dept_eval_feedback = :dept_eval_feedback,
            dept_eval_evidence = :dept_eval_evidence,
            dept_env_classroom = :dept_env_classroom,
            dept_env_interaction = :dept_env_interaction,
            dept_env_safety = :dept_env_safety,
            dept_env_management = :dept_env_management,
            dept_env_rules = :dept_env_rules,
            dept_env_behavior = :dept_env_behavior,
            dept_score = :dept_score,
            dept_quality_level = :dept_quality_level,
            dept_observation_notes = :dept_observation_notes,
            dept_strengths = :dept_strengths,
            dept_suggestion = :dept_suggestion,
            dept_supervisor_signature = :dept_supervisor_signature
        WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function updateDirectorEvaluation($id, $data) {
        $sql = "UPDATE supervisions SET
            dir_plan_effective = :dir_plan_effective,
            dir_plan_correct = :dir_plan_correct,
            dir_plan_activities = :dir_plan_activities,
            dir_plan_media = :dir_plan_media,
            dir_plan_assessment = :dir_plan_assessment,
            dir_teach_techniques = :dir_teach_techniques,
            dir_teach_media = :dir_teach_media,
            dir_teach_assessment = :dir_teach_assessment,
            dir_teach_explanation = :dir_teach_explanation,
            dir_teach_control = :dir_teach_control,
            dir_teach_thinking = :dir_teach_thinking,
            dir_teach_adaptation = :dir_teach_adaptation,
            dir_teach_integration = :dir_teach_integration,
            dir_teach_language = :dir_teach_language,
            dir_eval_variety = :dir_eval_variety,
            dir_eval_standards = :dir_eval_standards,
            dir_eval_criteria = :dir_eval_criteria,
            dir_eval_feedback = :dir_eval_feedback,
            dir_eval_evidence = :dir_eval_evidence,
            dir_env_classroom = :dir_env_classroom,
            dir_env_interaction = :dir_env_interaction,
            dir_env_safety = :dir_env_safety,
            dir_env_management = :dir_env_management,
            dir_env_rules = :dir_env_rules,
            dir_env_behavior = :dir_env_behavior,
            dir_score = :dir_score,
            dir_quality_level = :dir_quality_level,
            dir_observation_notes = :dir_observation_notes,
            dir_strengths = :dir_strengths,
            dir_suggestion = :dir_suggestion,
            dir_supervisor_signature = :dir_supervisor_signature
        WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function getBySubjectGroup($subjectGroup) {
        try {
            // Since supervisions and teachers are in different databases,
            // we need to get teacher IDs first, then filter supervisions
            require_once __DIR__ . '/../classes/DatabaseUsers.php';
            $dbUsers = new \App\DatabaseUsers();
            $usersPdo = $dbUsers->getPDO();
            
            // Get teacher IDs that belong to the subject group
            $teacherSql = "SELECT Teach_id FROM teacher WHERE Teach_major = :subject_group";
            $teacherStmt = $usersPdo->prepare($teacherSql);
            $teacherStmt->execute(['subject_group' => $subjectGroup]);
            $teachers = $teacherStmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($teachers)) {
                return [];
            }
            
            // Create placeholders for the IN clause
            $placeholders = str_repeat('?,', count($teachers) - 1) . '?';
            
            // Get supervisions for these teachers
            $sql = "SELECT s.*, t.Teach_name as teacher_full_name, t.Teach_major as teacher_subject_group 
                    FROM supervisions s
                    LEFT JOIN (SELECT ? as teacher_id, ? as Teach_name, ? as Teach_major) t 
                    ON s.teacher_id = t.teacher_id
                    WHERE s.teacher_id IN ($placeholders) 
                    ORDER BY s.supervision_date DESC, s.created_at DESC";
            
            // Actually, let's simplify this since we can't easily do cross-database JOINs
            // We'll get supervisions and then enrich them with teacher data
            $sql = "SELECT * FROM supervisions WHERE teacher_id IN ($placeholders) 
                    ORDER BY supervision_date DESC, created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($teachers);
            $supervisions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enrich with teacher data
            foreach ($supervisions as &$supervision) {
                $teacherSql = "SELECT Teach_name, Teach_major FROM teacher WHERE Teach_id = ?";
                $teacherStmt = $usersPdo->prepare($teacherSql);
                $teacherStmt->execute([$supervision['teacher_id']]);
                $teacherData = $teacherStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($teacherData) {
                    $supervision['teacher_full_name'] = $teacherData['Teach_name'];
                    $supervision['teacher_subject_group'] = $teacherData['Teach_major'];
                }
            }
            
            return $supervisions;
            
        } catch (Exception $e) {
            error_log("Error fetching supervisions by subject group: " . $e->getMessage());
            return [];
        }
    }
}

