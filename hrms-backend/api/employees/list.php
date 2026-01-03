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

$query = "SELECT e.*, u.email, r.name as role_name 
          FROM employees e
          JOIN users u ON e.user_id = u.id
          JOIN roles r ON u.role_id = r.id
          ORDER BY e.id DESC";

$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $employees_arr = array();
    $employees_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $employee_item = array(
            "id" => $id,
            "employee_code" => $employee_code,
            "name" => $first_name . " " . $last_name,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "role" => $role_name,
            "department" => $department,
            "position" => $job_position,
            "phone" => $phone,
            "joining_date" => $joining_date,
            "status" => "Active",
            "profile_picture" => $profile_picture
        );
        array_push($employees_arr["records"], $employee_item);
    }
    http_response_code(200);
    echo json_encode($employees_arr);
} else {
    http_response_code(200);
    echo json_encode(["records" => []]);
}
?>