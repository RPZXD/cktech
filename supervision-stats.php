<?php
/**
 * Supervision Statistics Page Entry Point
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/SupervisionStatsController.php';

use App\Controllers\SupervisionStatsController;

$controller = new SupervisionStatsController();
$data = $controller->index();

$pageTitle = $data['pageTitle'];
$overallStats = $data['overallStats'];
$supervisionsByMonth = $data['supervisionsByMonth'];
$recentSupervisions = $data['recentSupervisions'];
$global = $data['global'];

ob_start();
include __DIR__ . '/views/supervision-stats/index.php';
$content = ob_get_clean();

include __DIR__ . '/views/layouts/app.php';
