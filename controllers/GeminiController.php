<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../classes/DatabaseUsers.php';
use App\DatabaseUsers;

// Check authentication
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$teacher_id = $_SESSION['user']['Teach_id'] ?? $_SESSION['Teacher_login'] ?? '';

if (!$teacher_id) {
    echo json_encode(['success' => false, 'error' => 'ไม่พบข้อมูลอาจารย์ผู้สอนในระบบ']);
    exit;
}

try {
    $db = new DatabaseUsers();
    $pdo = $db->getPDO();

    switch ($action) {
        case 'get_key':
            $stmt = $pdo->prepare("SELECT gemini_api_key FROM teacher WHERE Teach_id = ?");
            $stmt->execute([$teacher_id]);
            $teacher = $stmt->fetch();
            $key = $teacher['gemini_api_key'] ?? '';
            
            // Mask key for safety (show only first 4 and last 4 characters if not empty)
            $maskedKey = '';
            if (!empty($key)) {
                $len = strlen($key);
                if ($len > 8) {
                    $maskedKey = substr($key, 0, 4) . str_repeat('*', $len - 8) . substr($key, -4);
                } else {
                    $maskedKey = str_repeat('*', $len);
                }
            }
            echo json_encode(['success' => true, 'has_key' => !empty($key), 'masked_key' => $maskedKey]);
            break;

        case 'save_key':
            // Receive key from post
            $input = json_decode(file_get_contents('php://input'), true);
            $key = trim($input['gemini_api_key'] ?? '');
            
            // Allow clearing the key or saving a new one
            $stmt = $pdo->prepare("UPDATE teacher SET gemini_api_key = ? WHERE Teach_id = ?");
            $result = $stmt->execute([empty($key) ? null : $key, $teacher_id]);
            
            echo json_encode(['success' => $result]);
            break;

        case 'generate':
            // Get teacher's key first
            $stmt = $pdo->prepare("SELECT gemini_api_key FROM teacher WHERE Teach_id = ?");
            $stmt->execute([$teacher_id]);
            $teacher = $stmt->fetch();
            $apiKey = trim($teacher['gemini_api_key'] ?? '');

            if (empty($apiKey)) {
                echo json_encode(['success' => false, 'needs_key' => true, 'error' => 'กรุณาตั้งค่า Gemini API Key ก่อนใช้งาน']);
                exit;
            }

            // Receive subject name and plan topic
            $input = json_decode(file_get_contents('php://input'), true);
            $subjectName = trim($input['subject_name'] ?? '');
            $planTopic = trim($input['plan_topic'] ?? '');

            if (empty($planTopic)) {
                echo json_encode(['success' => false, 'error' => 'กรุณากรอกหัวข้อ/สาระการเรียนรู้เพื่อใช้วิเคราะห์']);
                exit;
            }

            // Call Gemini API
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
            
            $prompt = "คุณคือผู้ช่วยครูในการเขียนรายงานการสอนภาษาไทยระดับโรงเรียนมัธยมศึกษา "
                    . "จากวิชา: \"{$subjectName}\" "
                    . "หัวข้อ/สาระการเรียนรู้: \"{$planTopic}\" \n\n"
                    . "กรุณาแนะนำข้อมูลสำหรับการเขียนรายงานการสอน โดยส่งผลลัพธ์กลับมาเป็น JSON ภาษาไทยที่สอดคล้องกับหัวข้อ และอยู่ในโครงสร้างต่อไปนี้เท่านั้น (ห้ามมีคำนำเกริ่นนำหรือเครื่องหมายคำพูด Markdown คลุม ให้ส่งเฉพาะเนื้อหา JSON ดิบๆ เลย):\n"
                    . "{\n"
                    . "  \"activity\": \"ระบุกิจกรรมการเรียนรู้ (ขั้นนำ ขั้นสอน ขั้นสรุป สั้นๆ กระชับและเข้าใจง่ายประมาณ 2-3 ประโยค)\",\n"
                    . "  \"reflection_k\": \"ความรู้ (Knowledge) ที่ผู้เรียนได้รับในคาบนี้\",\n"
                    . "  \"reflection_p\": \"ทักษะ/กระบวนการ (Practice) ที่ผู้เรียนได้ฝึกฝนปฏิบัติ\",\n"
                    . "  \"reflection_a\": \"เจตคุณธรรม/เจตคติ (Attitude) ที่ผู้เรียนได้รับการปลูกฝัง\",\n"
                    . "  \"problems\": \"ปัญหา/อุปสรรคที่อาจจะเกิดขึ้นในการเรียนการสอนหัวข้อนี้ (ตัวอย่าง: นักเรียนบางคนเข้าใจเนื้อหาช้ากว่าเพื่อน หรือกิจกรรมต้องใช้เวลาเพิ่มขึ้น)\",\n"
                    . "  \"suggestions\": \"ข้อเสนอแนะ/แนวทางการแก้ไขปัญหาสำหรับใช้ในคาบเรียนถัดไป\"\n"
                    . "}";

            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // for local testing with XAMPP
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                echo json_encode(['success' => false, 'error' => 'การเชื่อมต่อล้มเหลว: ' . $curlError]);
                exit;
            }

            if ($httpCode !== 200) {
                // Decode response to see API error details
                $errData = json_decode($response, true);
                $msg = $errData['error']['message'] ?? 'Gemini API Error (HTTP ' . $httpCode . ')';
                if (strpos($msg, 'API_KEY_INVALID') !== false) {
                    echo json_encode(['success' => false, 'needs_key' => true, 'error' => 'API Key ของคุณไม่ถูกต้องตามที่ Google กำหนด กรุณาตรวจสอบหรือตั้งค่าใหม่อีกครั้ง']);
                } else {
                    echo json_encode(['success' => false, 'error' => $msg]);
                }
                exit;
            }

            $resDecoded = json_decode($response, true);
            $textResult = $resDecoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($textResult)) {
                echo json_encode(['success' => false, 'error' => 'ไม่ได้รับการตอบกลับจาก AI']);
                exit;
            }

            // Decode generated json
            $aiData = json_decode(trim($textResult), true);
            if (!$aiData) {
                // Fallback attempt to extract JSON if it was returned with markdown wrappers
                if (preg_match('/\{.*\}/s', $textResult, $matches)) {
                    $aiData = json_decode($matches[0], true);
                }
            }

            if (!$aiData) {
                echo json_encode(['success' => false, 'error' => 'ข้อมูลที่ได้รับจาก AI ไม่สอดคล้องกับรูปแบบที่กำหนด', 'raw' => $textResult]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $aiData
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
