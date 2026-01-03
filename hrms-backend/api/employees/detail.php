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

$database = new Database();
$db = $database->getConnection();

$employee_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$employee_id) {
    http_response_code(400);
    echo json_encode(["message" => "Employee ID required."]);
    exit();
}

$query = "SELECT e.*, u.email, r.name as role_name 
          FROM employees e
          JOIN users u ON e.user_id = u.id
          JOIN roles r ON u.role_id = r.id
          WHERE e.id = :id";

$stmt = $db->prepare($query);
$stmt->bindParam(":id", $employee_id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $employee_data = array(
        "id" => $row['id'],
        "user_id" => $row['user_id'],
        "employee_code" => $row['employee_code'],
        "first_name" => $row['first_name'],
        "last_name" => $row['last_name'],
        "name" => $row['first_name'] . " " . $row['last_name'],
        "email" => $row['email'],
        "role" => $row['role_name'],
        "department" => $row['department'],
        "job_position" => $row['job_position'],
        "phone" => $row['phone'],
        "joining_date" => $row['joining_date'],
        "manager_id" => $row['manager_id'],
        "address" => $row['address'],
        "profile_picture" => $row['profile_picture']
    );

    http_response_code(200);
    echo json_encode($employee_data);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Employee not found."]);
}
?>