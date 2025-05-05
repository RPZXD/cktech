<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/Teacher.php';

use App\Models\Teacher;

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$teacherModel = new Teacher();

try {
    switch ($action) {
        case 'list':
            echo json_encode($teacherModel->getAll());
            break;
        case 'get':
            $id = $_GET['id'] ?? $_POST['id'] ?? '';
            echo json_encode($teacherModel->getById($id));
            break;
        case 'create':
            $data = [
                'Teach_id' => $_POST['Teach_id'],
                'Teach_name' => $_POST['Teach_name'],
                'Teach_major' => $_POST['Teach_major'],
                'Teach_status' => $_POST['Teach_status'],
                'role_ckteach' => $_POST['role_ckteach']
            ];
            $teacherModel->create($data);
            echo json_encode(['success' => true]);
            break;
        case 'update':
            $id = $_POST['Teach_id'];
            $data = [
                'Teach_name' => $_POST['Teach_name'],
                'Teach_major' => $_POST['Teach_major'],
                'Teach_status' => $_POST['Teach_status'],
                'role_ckteach' => $_POST['role_ckteach']
            ];
            $teacherModel->update($id, $data);
            echo json_encode(['success' => true]);
            break;
        case 'delete':
            $id = $_POST['Teach_id'];
            $result = $teacherModel->delete($id);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Not found or already deleted']);
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
