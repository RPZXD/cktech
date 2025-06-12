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
                plan_effective, plan_correct, plan_activities, plan_media, plan_assessment,
                teach_techniques, teach_media, teach_assessment, teach_explanation, teach_control,
                teach_thinking, teach_adaptation, teach_integration, teach_language,
                eval_variety, eval_standards, eval_criteria, eval_feedback, eval_evidence,
                env_classroom, env_interaction, env_safety, env_management, env_rules, env_behavior,
                total_score, quality_level, observation_notes, reflection_notes, strengths, improvements,
                supervisee_signature, supervisor_signature, lesson_plan, worksheets, supervisor_photos, classroom_photos
            ) VALUES (
                :teacher_id, :teacher_name, :position, :academic_level, :subject_group,
                :subject_name, :subject_code, :class_level, :supervision_round, :supervision_date,
                :plan_effective, :plan_correct, :plan_activities, :plan_media, :plan_assessment,
                :teach_techniques, :teach_media, :teach_assessment, :teach_explanation, :teach_control,
                :teach_thinking, :teach_adaptation, :teach_integration, :teach_language,
                :eval_variety, :eval_standards, :eval_criteria, :eval_feedback, :eval_evidence,
                :env_classroom, :env_interaction, :env_safety, :env_management, :env_rules, :env_behavior,
                :total_score, :quality_level, :observation_notes, :reflection_notes, :strengths, :improvements,
                :supervisee_signature, :supervisor_signature, :lesson_plan, :worksheets, :supervisor_photos, :classroom_photos
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
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                observation_notes = :observation_notes, reflection_notes = :reflection_notes,
                strengths = :strengths, improvements = :improvements,
                supervisee_signature = :supervisee_signature, supervisor_signature = :supervisor_signature,
                lesson_plan = :lesson_plan, worksheets = :worksheets,
                supervisor_photos = :supervisor_photos, classroom_photos = :classroom_photos,
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
                // Multiple files
                $uploadedFiles[$fieldName] = [];
                for ($i = 0; $i < count($fileData['name']); $i++) {
                    if ($fileData['error'][$i] === UPLOAD_ERR_OK) {
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
        
        $fileFields = ['lesson_plan', 'worksheets', 'supervisor_photos', 'classroom_photos'];
        
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
                'worksheets' => $supervision['worksheets'],
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
                supervision_date DATE NOT NULL,
                
                -- แบบประเมินด้านที่ 1: การจัดทำแผน
                plan_effective TINYINT DEFAULT 0,
                plan_correct TINYINT DEFAULT 0,
                plan_activities TINYINT DEFAULT 0,
                plan_media TINYINT DEFAULT 0,
                plan_assessment TINYINT DEFAULT 0,
                
                -- แบบประเมินด้านที่ 2: การจัดการเรียนรู้
                teach_techniques TINYINT DEFAULT 0,
                teach_media TINYINT DEFAULT 0,
                teach_assessment TINYINT DEFAULT 0,
                teach_explanation TINYINT DEFAULT 0,
                teach_control TINYINT DEFAULT 0,
                teach_thinking TINYINT DEFAULT 0,
                teach_adaptation TINYINT DEFAULT 0,
                teach_integration TINYINT DEFAULT 0,
                teach_language TINYINT DEFAULT 0,
                
                -- แบบประเมินด้านที่ 3: การประเมินผล
                eval_variety TINYINT DEFAULT 0,
                eval_standards TINYINT DEFAULT 0,
                eval_criteria TINYINT DEFAULT 0,
                eval_feedback TINYINT DEFAULT 0,
                eval_evidence TINYINT DEFAULT 0,
                
                -- แบบประเมินด้านที่ 4: สภาพแวดล้อม
                env_classroom TINYINT DEFAULT 0,
                env_interaction TINYINT DEFAULT 0,
                env_safety TINYINT DEFAULT 0,
                env_management TINYINT DEFAULT 0,
                env_rules TINYINT DEFAULT 0,
                env_behavior TINYINT DEFAULT 0,
                
                total_score INT DEFAULT 0,
                quality_level VARCHAR(50),
                
                observation_notes TEXT,
                reflection_notes TEXT,
                strengths TEXT,
                improvements TEXT,
                supervisee_signature VARCHAR(255),
                supervisor_signature VARCHAR(255),
                
                lesson_plan VARCHAR(500),
                worksheets VARCHAR(500),
                supervisor_photos VARCHAR(500),
                classroom_photos VARCHAR(500),
                
                teacher_id VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_teacher_id (teacher_id),
                INDEX idx_supervision_date (supervision_date)
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
            // Check if teacher_id column exists, if not add it
            $checkSql = "SHOW COLUMNS FROM supervisions LIKE 'teacher_id'";
            $stmt = $this->pdo->query($checkSql);
            
            if ($stmt->rowCount() == 0) {
                $alterSql = "ALTER TABLE supervisions ADD COLUMN teacher_id VARCHAR(50) AFTER classroom_photos";
                $this->pdo->exec($alterSql);
                error_log("Added teacher_id column to supervisions table");
            }

            // Update varchar lengths to match database
            $updates = [
                "ALTER TABLE supervisions MODIFY COLUMN lesson_plan VARCHAR(500)",
                "ALTER TABLE supervisions MODIFY COLUMN worksheets VARCHAR(500)", 
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

        } catch (Exception $e) {
            error_log("Error updating table structure: " . $e->getMessage());
        }
    }
}
