<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Example Routing: localhost/hrms-backend/api/auth/login
// $uri[0] -> empty (if leading slash)
// $uri[1] -> hrms-backend (or api if root) - Adjust based on XAMPP folder depth
// Ideally, we look for 'api' and route from there.

echo json_encode(["message" => "HRMS Backend Running. Endpoint: " . $_SERVER['REQUEST_URI']]);
?>
