<?php
require_once '../models/Supervision.php';
require_once '../models/TermPee.php';

use App\Models\Supervision;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// เช็ค session
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $supervision = new Supervision();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'list':
            $teacherId = $_GET['teacher_id'] ?? $_SESSION['username'];
            $supervisions = $supervision->getAll($teacherId);
            echo json_encode($supervisions);
            break;

        case 'detail':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                throw new Exception('ID is required');
            }
            $detail = $supervision->getById($id);
            if (!$detail) {
                throw new Exception('Supervision not found');
            }
            
            echo json_encode($detail);
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            // Get current term and pee
            $termPee = TermPee::getCurrent();

            // Handle file uploads
            $uploadedFiles = [];
            if (!empty($_FILES)) {
                $uploadedFiles = $supervision->uploadFiles($_FILES);
            }

            // Get form data with proper validation
            $data = [
                'teacher_id' => $_SESSION['username'],
                'teacher_name' => $_POST['teacher_name'] ?? '',
                'position' => $_POST['position'] ?? '',
                'academic_level' => $_POST['academic_level'] ?? '',
                'subject_group' => $_POST['subject_group'] ?? '',
                'subject_name' => $_POST['subject_name'] ?? '',
                'subject_code' => $_POST['subject_code'] ?? '',
                'class_level' => $_POST['class_level'] ?? '',
                'supervision_round' => intval($_POST['supervision_round'] ?? 1),
                'supervision_date' => $_POST['supervision_date'] ?? '',
                'term' => $termPee->term ?? '',
                'pee' => $termPee->pee ?? '',
                
                // แบบประเมิน
                'plan_effective' => intval($_POST['plan_effective'] ?? 0),
                'plan_correct' => intval($_POST['plan_correct'] ?? 0),
                'plan_activities' => intval($_POST['plan_activities'] ?? 0),
                'plan_media' => intval($_POST['plan_media'] ?? 0),
                'plan_assessment' => intval($_POST['plan_assessment'] ?? 0),
                
                'teach_techniques' => intval($_POST['teach_techniques'] ?? 0),
                'teach_media' => intval($_POST['teach_media'] ?? 0),
                'teach_assessment' => intval($_POST['teach_assessment'] ?? 0),
                'teach_explanation' => intval($_POST['teach_explanation'] ?? 0),
                'teach_control' => intval($_POST['teach_control'] ?? 0),
                'teach_thinking' => intval($_POST['teach_thinking'] ?? 0),
                'teach_adaptation' => intval($_POST['teach_adaptation'] ?? 0),
                'teach_integration' => intval($_POST['teach_integration'] ?? 0),
                'teach_language' => intval($_POST['teach_language'] ?? 0),
                
                'eval_variety' => intval($_POST['eval_variety'] ?? 0),
                'eval_standards' => intval($_POST['eval_standards'] ?? 0),
                'eval_criteria' => intval($_POST['eval_criteria'] ?? 0),
                'eval_feedback' => intval($_POST['eval_feedback'] ?? 0),
                'eval_evidence' => intval($_POST['eval_evidence'] ?? 0),
                
                'env_classroom' => intval($_POST['env_classroom'] ?? 0),
                'env_interaction' => intval($_POST['env_interaction'] ?? 0),
                'env_safety' => intval($_POST['env_safety'] ?? 0),
                'env_management' => intval($_POST['env_management'] ?? 0),
                'env_rules' => intval($_POST['env_rules'] ?? 0),
                'env_behavior' => intval($_POST['env_behavior'] ?? 0),
                
                'total_score' => intval($_POST['total_score'] ?? 0),
                'quality_level' => $_POST['quality_level'] ?? '',
                
                'observation_notes' => $_POST['observation_notes'] ?? '',
                'reflection_notes' => $_POST['reflection_notes'] ?? '',
                'strengths' => $_POST['strengths'] ?? '',
                'improvements' => $_POST['improvements'] ?? '',
                'supervisee_signature' => $_POST['supervisee_signature'] ?? '',
                'supervisor_signature' => $_POST['supervisor_signature'] ?? '',
                
                'lesson_plan' => $uploadedFiles['lesson_plan'] ?? '',
                'worksheets' => isset($uploadedFiles['worksheets']) ? implode(',', (array)$uploadedFiles['worksheets']) : '',
                'supervisor_photos' => isset($uploadedFiles['supervisor_photos']) ? implode(',', (array)$uploadedFiles['supervisor_photos']) : '',
                'classroom_photos' => isset($uploadedFiles['classroom_photos']) ? implode(',', (array)$uploadedFiles['classroom_photos']) : ''
            ];

            // Validate required fields
            if (empty($data['teacher_name'])) {
                throw new Exception('ชื่อผู้รับการนิเทศเป็นข้อมูลที่จำเป็น');
            }
            
            if (empty($data['supervision_date'])) {
                throw new Exception('วันที่รับการนิเทศเป็นข้อมูลที่จำเป็น');
            }

            // Log the data being sent for debugging
            error_log("Creating supervision with data: " . print_r($data, true));

            $id = $supervision->create($data);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'บันทึกการนิเทศสำเร็จ']);
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $id = $_POST['id'] ?? '';
            if (!$id) {
                throw new Exception('ID is required');
            }

            // Handle file uploads
            $uploadedFiles = [];
            if (!empty($_FILES)) {
                $uploadedFiles = $supervision->uploadFiles($_FILES);
            }

            // Get existing data
            $existing = $supervision->getById($id);
            if (!$existing) {
                throw new Exception('Supervision not found');
            }

            $data = [
                'teacher_name' => $_POST['teacher_name'] ?? $existing['teacher_name'],
                'position' => $_POST['position'] ?? $existing['position'],
                'academic_level' => $_POST['academic_level'] ?? $existing['academic_level'],
                'subject_group' => $_POST['subject_group'] ?? $existing['subject_group'],
                'subject_name' => $_POST['subject_name'] ?? $existing['subject_name'],
                'subject_code' => $_POST['subject_code'] ?? $existing['subject_code'],
                'class_level' => $_POST['class_level'] ?? $existing['class_level'],
                'supervision_round' => $_POST['supervision_round'] ?? $existing['supervision_round'],
                'supervision_date' => $_POST['supervision_date'] ?? $existing['supervision_date'],
                
                // แบบประเมิน - ใช้ค่าจาก POST หรือค่าเดิม
                'plan_effective' => isset($_POST['plan_effective']) ? intval($_POST['plan_effective']) : ($existing['plan_effective'] ?? 0),
                'plan_correct' => isset($_POST['plan_correct']) ? intval($_POST['plan_correct']) : ($existing['plan_correct'] ?? 0),
                'plan_activities' => isset($_POST['plan_activities']) ? intval($_POST['plan_activities']) : ($existing['plan_activities'] ?? 0),
                'plan_media' => isset($_POST['plan_media']) ? intval($_POST['plan_media']) : ($existing['plan_media'] ?? 0),
                'plan_assessment' => isset($_POST['plan_assessment']) ? intval($_POST['plan_assessment']) : ($existing['plan_assessment'] ?? 0),
                
                'teach_techniques' => isset($_POST['teach_techniques']) ? intval($_POST['teach_techniques']) : ($existing['teach_techniques'] ?? 0),
                'teach_media' => isset($_POST['teach_media']) ? intval($_POST['teach_media']) : ($existing['teach_media'] ?? 0),
                'teach_assessment' => isset($_POST['teach_assessment']) ? intval($_POST['teach_assessment']) : ($existing['teach_assessment'] ?? 0),
                'teach_explanation' => isset($_POST['teach_explanation']) ? intval($_POST['teach_explanation']) : ($existing['teach_explanation'] ?? 0),
                'teach_control' => isset($_POST['teach_control']) ? intval($_POST['teach_control']) : ($existing['teach_control'] ?? 0),
                'teach_thinking' => isset($_POST['teach_thinking']) ? intval($_POST['teach_thinking']) : ($existing['teach_thinking'] ?? 0),
                'teach_adaptation' => isset($_POST['teach_adaptation']) ? intval($_POST['teach_adaptation']) : ($existing['teach_adaptation'] ?? 0),
                'teach_integration' => isset($_POST['teach_integration']) ? intval($_POST['teach_integration']) : ($existing['teach_integration'] ?? 0),
                'teach_language' => isset($_POST['teach_language']) ? intval($_POST['teach_language']) : ($existing['teach_language'] ?? 0),
                
                'eval_variety' => isset($_POST['eval_variety']) ? intval($_POST['eval_variety']) : ($existing['eval_variety'] ?? 0),
                'eval_standards' => isset($_POST['eval_standards']) ? intval($_POST['eval_standards']) : ($existing['eval_standards'] ?? 0),
                'eval_criteria' => isset($_POST['eval_criteria']) ? intval($_POST['eval_criteria']) : ($existing['eval_criteria'] ?? 0),
                'eval_feedback' => isset($_POST['eval_feedback']) ? intval($_POST['eval_feedback']) : ($existing['eval_feedback'] ?? 0),
                'eval_evidence' => isset($_POST['eval_evidence']) ? intval($_POST['eval_evidence']) : ($existing['eval_evidence'] ?? 0),
                
                'env_classroom' => isset($_POST['env_classroom']) ? intval($_POST['env_classroom']) : ($existing['env_classroom'] ?? 0),
                'env_interaction' => isset($_POST['env_interaction']) ? intval($_POST['env_interaction']) : ($existing['env_interaction'] ?? 0),
                'env_safety' => isset($_POST['env_safety']) ? intval($_POST['env_safety']) : ($existing['env_safety'] ?? 0),
                'env_management' => isset($_POST['env_management']) ? intval($_POST['env_management']) : ($existing['env_management'] ?? 0),
                'env_rules' => isset($_POST['env_rules']) ? intval($_POST['env_rules']) : ($existing['env_rules'] ?? 0),
                'env_behavior' => isset($_POST['env_behavior']) ? intval($_POST['env_behavior']) : ($existing['env_behavior'] ?? 0),
                
                'total_score' => isset($_POST['total_score']) ? intval($_POST['total_score']) : ($existing['total_score'] ?? 0),
                'quality_level' => $_POST['quality_level'] ?? $existing['quality_level'],
                
                'observation_notes' => $_POST['observation_notes'] ?? $existing['observation_notes'],
                'reflection_notes' => $_POST['reflection_notes'] ?? $existing['reflection_notes'],
                'strengths' => $_POST['strengths'] ?? $existing['strengths'],
                'improvements' => $_POST['improvements'] ?? $existing['improvements'],
                'supervisee_signature' => $_POST['supervisee_signature'] ?? $existing['supervisee_signature'],
                'supervisor_signature' => $_POST['supervisor_signature'] ?? $existing['supervisor_signature'],
                
                'lesson_plan' => $uploadedFiles['lesson_plan'] ?? $existing['lesson_plan'],
                'worksheets' => isset($uploadedFiles['worksheets']) ? implode(',', (array)$uploadedFiles['worksheets']) : $existing['worksheets'],
                'supervisor_photos' => isset($uploadedFiles['supervisor_photos']) ? implode(',', (array)$uploadedFiles['supervisor_photos']) : $existing['supervisor_photos'],
                'classroom_photos' => isset($uploadedFiles['classroom_photos']) ? implode(',', (array)$uploadedFiles['classroom_photos']) : $existing['classroom_photos']
            ];

            $success = $supervision->update($id, $data);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'แก้ไขการนิเทศสำเร็จ']);
            } else {
                throw new Exception('Failed to update supervision');
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? '';
            
            if (!$id) {
                throw new Exception('ID is required');
            }

            $success = $supervision->delete($id);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'ลบการนิเทศสำเร็จ']);
            } else {
                throw new Exception('Failed to delete supervision');
            }
            break;

        case 'statistics':
            $teacherId = $_GET['teacher_id'] ?? $_SESSION['username'];
            $stats = $supervision->getStatistics($teacherId);
            echo json_encode($stats);
            break;

        case 'create_table':
            $success = $supervision->createTable();
            echo json_encode(['success' => $success, 'message' => 'สร้างตารางสำเร็จ']);
            break;

        case 'delete_file':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $filename = $input['filename'] ?? '';
            $supervisionId = $input['supervision_id'] ?? '';
            $fileType = $input['file_type'] ?? '';
            
            if (!$filename || !$supervisionId || !$fileType) {
                throw new Exception('Missing required parameters');
            }

            $success = $supervision->removeFile($supervisionId, $filename, $fileType);
            if ($success) {
                // Also delete physical file
                $filepath = '../uploads/supervision/' . $filename;
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                echo json_encode(['success' => true, 'message' => 'ลบไฟล์สำเร็จ']);
            } else {
                throw new Exception('Failed to delete file');
            }
            break;

        case 'get_file_info':
            $supervisionId = $_GET['supervision_id'] ?? '';
            if (!$supervisionId) {
                throw new Exception('Supervision ID is required');
            }
            
            $fileInfo = $supervision->getFileInfo($supervisionId);
            echo json_encode($fileInfo);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log("SupervisionController Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'debug' => $e->getTraceAsString()]);
}
