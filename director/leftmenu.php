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
    echo createNavItem('report.php', '', '📑 ตรวจสอบรายงานการสอน');
    // �️ การนิเทศการสอน (ผู้บริหาร)
    echo createNavItem('supervision.php', '', '👁️ การนิเทศการสอน');
    // �📊 ดูสถิติและวิเคราะห์ข้อมูล การสอน
    echo createNavItem('stat.php', '', '📊 สถิติและวิเคราะห์ข้อมูล');
    echo createNavItem('weekly_report.php', '', '📅 รายงานรายสัปดาห์ (กลุ่มสาระ)');

    echo createNavItem('../logout.php', 'bi-box-arrow-right', 'ออกจากระบบ');

?>