<?php
function createNavItem($href, $iconClass, $text) {
    $current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $target = basename($href);
    $isActive = ($current === $target) || ($current === '' && $target === 'index.php');
    $activeClass = $isActive ? ' active' : '';
    $ariaCurrent = $isActive ? ' aria-current="page"' : '';
    return "
    <li class=\"nav-item\">
        <a href=\"" . htmlspecialchars($href) . "\" class=\"nav-link hover:bg-gray-700 hover:text-white transition-all duration-300 transform hover:scale-105" . $activeClass . "\" role=\"menuitem\" tabindex=\"0\" aria-label=\"" . htmlspecialchars($text) . "\"" . $ariaCurrent . ">
            <i class=\"bi " . htmlspecialchars($iconClass) . "\"></i>
            <p>" . htmlspecialchars($text) . "</p>
        </a>
    </li>";
}

    // guest/ยังไม่ login
    echo createNavItem('index.php', 'bi-house', 'หน้าหลัก');
    echo createNavItem('login.php', 'bi-box-arrow-in-right', 'ลงชื่อเข้าสู่ระบบ');

?>