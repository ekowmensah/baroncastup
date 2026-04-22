<?php
/**
 * SmartCast - Voting Management System
 * Main entry point
 */

// Start session
session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration and autoloader
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

// Initialize the application
$app = new SmartCast\Core\Application();

// Handle the request
$app->run();
