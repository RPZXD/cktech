<?php
/**
 * Home Page Entry Point
 * MVC Pattern - Routes to HomeController
 * 
 * Architecture Flow:
 * index.php (Entry Point) → HomeController → Home Model → View → Layout
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to Thailand
date_default_timezone_set('Asia/Bangkok');

// Include the controller
require_once __DIR__ . '/controllers/HomeController.php';

use App\Controllers\HomeController;

// Initialize the controller
$controller = new HomeController();

// Execute the index action and get data for view
$data = $controller->index();

// Extract data for use in view and layout
$pageTitle = $data['pageTitle'];
$statistics = $data['statistics'];
$quickLinks = $data['quickLinks'];
$recentActivities = $data['recentActivities'];
$todayStats = $data['todayStats'] ?? [];
$currentUser = $data['currentUser'];
$global = $data['global'];
$welcomeMessage = $data['welcomeMessage'];

// Capture the view content
ob_start();
include __DIR__ . '/views/home/index.php';
$content = ob_get_clean();

// Include the main layout
include __DIR__ . '/views/layouts/app.php';
