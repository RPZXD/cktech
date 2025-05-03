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

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'นักเรียน') {
    echo createNavItem('../login.php', 'bi-box-arrow-in-left', 'เข้าสู่ระบบ');
} else {
    echo createNavItem('index.php', 'bi-house', 'หน้าหลัก');
    echo createNavItem('event_regis.php', 'bi-people-fill', 'ลงทะเบียนกิจกรรม');
    echo createNavItem('event_list.php', 'bi-list-check', 'กิจกรรมที่ลงทะเบียน');
    echo createNavItem('../logout.php', 'bi-box-arrow-right', 'ออกจากระบบ');
}


?>