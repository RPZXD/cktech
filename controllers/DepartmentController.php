<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/Department.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    $departmentModel = new \App\Models\Department();

    switch ($action) {
        case 'list':
            // คืนค่ารายชื่อกลุ่มสาระ
            $departments = $departmentModel->getAllDepartments();
            echo json_encode($departments);
            break;
        case 'teachers':
            // คืนค่ารายชื่อครูในกลุ่มสาระ (parameter: department_name)
            $department_name = $_GET['department_id'] ?? $_POST['department_id'] ?? '';
            $teachers = $departmentModel->getTeachersByDepartment($department_name);
            echo json_encode($teachers);
            break;
        case 'listTeachers':
            // คืนค่ารายชื่อครูในกลุ่มสาระที่ระบุ (parameter: department)
            $department = $_GET['department'] ?? $_POST['department'] ?? '';
            $teachers = $departmentModel->getTeachersByDepartment($department);
            echo json_encode($teachers);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
