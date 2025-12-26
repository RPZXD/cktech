<?php
/**
 * Certificate Statistics Page Entry Point
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/CertificateStatsController.php';

use App\Controllers\CertificateStatsController;

$controller = new CertificateStatsController();
$data = $controller->index();

$pageTitle = $data['pageTitle'];
$overallStats = $data['overallStats'];
$certificatesByMonth = $data['certificatesByMonth'];
$recentCertificates = $data['recentCertificates'];
$global = $data['global'];

ob_start();
include __DIR__ . '/views/certificate-stats/index.php';
$content = ob_get_clean();

include __DIR__ . '/views/layouts/app.php';
