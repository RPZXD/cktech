<?php
function createNavItem($href, $iconClass, $text) {
    return '
    <li class="nav-item">
        <a href="' . htmlspecialchars($href) . '" class="nav-link">
            <i class="bi ' . htmlspecialchars($iconClass) . '"></i>
            <p>' . htmlspecialchars($text) . '</p>
        </a>
    </li>';
}

function createTreeNav($title, $iconClass, $items) {
    $html = "\n    <li class=\"nav-item has-treeview\">\n        <a href=\"#\" class=\"nav-link\">\n            <i class=\"bi " . htmlspecialchars($iconClass) . "\"></i>\n            <p>\n                " . htmlspecialchars($title) . "\n                <i class=\"right bi bi-chevron-down\"></i>\n            </p>\n        </a>\n        <ul class=\"nav nav-treeview ml-3\">";

    foreach ($items as $it) {
        $href = isset($it['href']) ? $it['href'] : '#';
        $icon = isset($it['icon']) ? $it['icon'] : '';
        $text = isset($it['text']) ? $it['text'] : '';

        $html .= "\n            <li class=\"nav-item\">\n                <a href=\"" . htmlspecialchars($href) . "\" class=\"nav-link\">\n                    <i class=\"bi " . htmlspecialchars($icon) . "\"></i>\n                    <p>" . htmlspecialchars($text) . "</p>\n                </a>\n            </li>";
    }

    $html .= "\n        </ul>\n    </li>\n    ";
    return $html;
}


    echo createNavItem('index.php', 'bi-house', 'หน้าหลัก');

    // รวมเมนูที่เกี่ยวกับการบันทึกไว้เป็นกลุ่มเดียวกัน
    echo createTreeNav('รายงานการสอน', 'bi-journal-text', array(
        array('href' => 'report.php', 'icon' => 'bi-journal-text', 'text' => 'บันทึกรายงาน'),
        array('href' => 'reportindate.php', 'icon' => 'bi-calendar3', 'text' => 'ปฏิทินการบันทึก'),
        array('href' => 'attendance_by_day.php', 'icon' => 'bi-calendar-check', 'text' => 'สมุดเช็คชื่อรายเดือน'),
        array('href' => 'subject.php', 'icon' => 'bi-book', 'text' => 'จัดการรายวิชา'),
    ));
    echo createNavItem('supervision.php', 'bi-eye', 'บันทึกนิเทศการสอน');
    echo createNavItem('certificate.php', 'bi-award', 'บันทึกเกียรติบัตรนักเรียน');

    echo createNavItem('timetable.php', 'bi-table', 'ตารางสอน');

    // เมนู "กำลังพัฒนา" พร้อมเมนูย่อย "วิเคราะห์ผู้เรียน"
    echo createTreeNav('กำลังพัฒนา', 'bi-tools', array(
        array('href' => 'student_link.php', 'icon' => 'bi-person-lines-fill', 'text' => 'วิเคราะห์ผู้เรียน'),
    ));

    echo createNavItem('../logout.php', 'bi-box-arrow-right', 'ออกจากระบบ');

?>