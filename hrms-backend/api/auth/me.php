<?php
session_start();
include_once '../../config/database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT u.id, u.email, r.name as role_name, e.id as employee_id, e.first_name, e.last_name, e.profile_picture, e.employee_code 
          FROM users u 
          JOIN roles r ON u.role_id = r.id 
          LEFT JOIN employees e ON u.id = e.user_id 
          WHERE u.id = :user_id LIMIT 0,1";

$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_data = [
        "id" => $row['id'],
        "employee_id" => $row['employee_id'],
        "email" => $row['email'],
        "role" => $row['role_name'],
        "name" => $row['first_name'] . ' ' . $row['last_name'],
        "first_name" => $row['first_name'],
        "last_name" => $row['last_name'],
        "employee_code" => $row['employee_code'],
        "profile_picture" => $row['profile_picture']
    ];

    http_response_code(200);
    echo json_encode([
        "user" => $user_data,
        "token" => session_id()
    ]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "User not found."]);
}
?>