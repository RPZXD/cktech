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

    echo createNavItem('../logout.php', 'bi-box-arrow-right', 'ออกจากระบบ');

?>