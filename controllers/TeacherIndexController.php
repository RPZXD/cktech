<?php
/**
 * Teacher Controller
 * MVC Pattern - Controller for teacher pages
 */

class TeacherIndexController
{
    private $config;
    private $global;
    private $user;

    public function __construct()
    {
        $this->loadConfig();
        $this->loadUser();
    }

    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../config.json';
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
            $this->global = $this->config['global'] ?? [];
        } else {
            $this->global = [
                'pageTitle' => 'ระบบ วิชาการ',
                'nameschool' => 'โรงเรียน',
                'logoLink' => 'logo-phicha.png'
            ];
        }
    }

    private function loadUser(): void
    {
        $this->user = [
            'name' => $_SESSION['teacher_name'] ?? $_SESSION['user']['Teach_name'] ?? 'ครู',
            'id' => $_SESSION['Teacher_login'] ?? $_SESSION['user']['Teach_id'] ?? null,
            'role' => $_SESSION['role'] ?? 'ครู',
            'photo' => $_SESSION['user']['Teach_photo'] ?? '',
            'major' => $_SESSION['user']['Teach_major'] ?? ''
        ];
    }

    /**
     * Teacher Dashboard / Index page
     */
    public function index(): array
    {
        return [
            'pageTitle' => 'หน้าหลักครู',
            'user' => $this->user,
            'global' => $this->global,
            'guides' => $this->getGuides(),
            'quickStats' => $this->getQuickStats()
        ];
    }

    /**
     * Get usage guides for teachers
     */
    private function getGuides(): array
    {
        return [
            [
                'icon' => '📚',
                'title' => 'จัดการรายวิชา',
                'description' => 'เพิ่ม แก้ไข หรือลบรายวิชาที่คุณสอน',
                'details' => ['เพิ่มรหัสวิชา ชื่อวิชา เลือกระดับชั้น', 'ประเภทวิชา และกำหนดห้องเรียน/คาบสอน'],
                'color' => 'blue',
                'link' => 'subjects.php'
            ],
            [
                'icon' => '📝',
                'title' => 'รายงานการสอน',
                'description' => 'บันทึกการสอนแต่ละคาบ',
                'details' => ['เลือกวันที่ วิชา ห้องเรียน คาบสอน', 'กรอกแผน/หัวข้อ กิจกรรม รายชื่อนักเรียนที่ขาดเรียน', 'แนบรูปภาพ และบันทึกสะท้อนคิด/ปัญหา/ข้อเสนอแนะ'],
                'color' => 'green',
                'link' => 'teaching-report.php'
            ],
            [
                'icon' => '🔍',
                'title' => 'ดู/แก้ไข/ลบข้อมูล',
                'description' => 'จัดการข้อมูลรายวิชาและรายงานการสอน',
                'details' => ['ใช้ปุ่ม ✏️ แก้ไข หรือ 🗑️ ลบ ในตาราง'],
                'color' => 'purple',
                'link' => 'teaching-report.php'
            ],
            [
                'icon' => '🏆',
                'title' => 'เกียรติบัตรนักเรียน',
                'description' => 'บันทึกผลงานและเกียรติบัตรนักเรียน',
                'details' => ['เพิ่มข้อมูลการแข่งขัน รางวัล และเกียรติบัตร'],
                'color' => 'orange',
                'link' => 'certificate.php'
            ],
            [
                'icon' => '💖',
                'title' => 'LOVES MODEL',
                'description' => 'นวัตกรรมระบบดูแลช่วยเหลือนักเรียน พาน้องกลับมาเรียน',
                'details' => ['แนวทางการติดตามนักเรียนกลุ่มเสี่ยง/ออกกลางคัน', 'กระบวนการ 5 ด้าน: Love, Organization, Variety, Encouragement, Sufficiency'],
                'color' => 'pink',
                'link' => 'loves_model.php'
            ],
        ];
    }

    /**
     * Get quick statistics for teacher
     */
    private function getQuickStats(): array
    {
        // In production, these would come from the database
        return [
            'total_reports' => 0,
            'this_month' => 0,
            'total_subjects' => 0,
            'total_students' => 0
        ];
    }
}
