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

    // 📑 ตรวจสอบรายงานการสอน ของครูในกลุ่มสาระ
    echo createNavItem('teacher.php', '', '👤 จัดการผู้ใช้');
    // 📊 ดูสถิติและวิเคราะห์ข้อมูล การสอน
    echo createNavItem('report.php', '', '📑 ดูรายงานการสอน');

    echo createNavItem('settings.php', '', '⚙️ ตั้งค่าระบบ ');

    echo createNavItem('../logout.php', 'bi-box-arrow-right', 'ออกจากระบบ');

?>