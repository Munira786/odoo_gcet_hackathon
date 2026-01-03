<?php
/**
 * HRMS Backend - Main API Router
 * Routes all requests to appropriate controllers
 */

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS requests (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request URI and method
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Define base path
$base_path = '/hrms-backend';

// Remove base path from request URI
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Ensure request URI starts with /
if (!str_starts_with($request_uri, '/')) {
    $request_uri = '/' . $request_uri;
}

/**
 * Route the request to appropriate controller
 */
try {
    // Auth routes
    if (strpos($request_uri, '/api/auth') === 0) {
        require 'api/auth/AuthController.php';
    }
    // Employee routes
    elseif (strpos($request_uri, '/api/employees') === 0) {
        require 'api/employees/EmployeeController.php';
    }
    // Attendance routes
    elseif (strpos($request_uri, '/api/attendance') === 0) {
        require 'api/attendance/AttendanceController.php';
    }
    // Leave routes
    elseif (strpos($request_uri, '/api/leave') === 0) {
        require 'api/leave/LeaveController.php';
    }
    // Salary routes
    elseif (strpos($request_uri, '/api/salary') === 0) {
        require 'api/salary/SalaryController.php';
    }
    // API info endpoint
    elseif ($request_uri === '/api' || $request_uri === '/api/' || $request_uri === '/') {
        http_response_code(200);
        echo json_encode([
            'name' => 'HRMS API',
            'version' => '1.0.0',
            'description' => 'Human Resource Management System API',
            'status' => 'running',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    // Health check endpoint
    elseif ($request_uri === '/health') {
        http_response_code(200);
        echo json_encode([
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    // Endpoint not found
    else {
        http_response_code(404);
        echo json_encode([
            'error' => 'API endpoint not found',
            'path' => $request_uri,
            'method' => $request_method
        ]);
    }

} catch (Exception $e) {
    // Error handling
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?>