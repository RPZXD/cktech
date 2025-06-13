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


    echo createNavItem('index.php', 'bi-house', 'หน้าหลัก');

    echo createNavItem('report.php', 'bi-journal-text', 'รายงานการสอน');
    echo createNavItem('reportindate.php', 'bi-calendar3', 'ปฏิทินการรายงานการสอน');

    echo createNavItem('subject.php', 'bi-book', 'จัดการรายวิชา');    
    echo createNavItem('timetable.php', 'bi-table', 'ตารางสอน');

    echo createNavItem('supervision.php', 'bi-eye', 'บันทึกนิเทศการสอน');

    // เมนู "กำลังพัฒนา" พร้อมเมนูย่อย "วิเคราะห์ผู้เรียน"
    echo '
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="bi bi-tools"></i>
            <p>
                กำลังพัฒนา
                <i class="right bi bi-chevron-down"></i>
            </p>
        </a>
        <ul class="nav nav-treeview ml-3">
            <li class="nav-item">
                <a href="student_link.php" class="nav-link">
                    <i class="bi bi-person-lines-fill"></i>
                    <p>วิเคราะห์ผู้เรียน</p>
                </a>
            </li>
        </ul>
    </li>
    ';

    echo createNavItem('../logout.php', 'bi-box-arrow-right', 'ออกจากระบบ');

?>