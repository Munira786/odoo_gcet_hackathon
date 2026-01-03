<?php
include_once '../../config/database.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

// Basic Auth Check (Simulated for now, would valid token in real app)
// $headers = getallheaders();

$query = "SELECT e.*, u.email, r.name as role_name 
          FROM employees e
          JOIN users u ON e.user_id = u.id
          JOIN roles r ON u.role_id = r.id
          ORDER BY e.created_at DESC"; // created_at might be missing in schema, sorting by id

$query = "SELECT e.*, u.email, r.name as role_name 
          FROM employees e
          JOIN users u ON e.user_id = u.id
          JOIN roles r ON u.role_id = r.id
          ORDER BY e.id DESC";

$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if($num > 0) {
    $employees_arr = array();
    $employees_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $employee_item = array(
            "id" => $id,
            "employee_code" => $employee_code,
            "name" => $first_name . " " . $last_name,
            "email" => $email,
            "role" => $role_name,
            "department" => $department,
            "position" => $job_position,
            "status" => "Active", // Placeholder, add status col in DB if needed
            "profile_picture" => $profile_picture
        );
        array_push($employees_arr["records"], $employee_item);
    }
    http_response_code(200);
    echo json_encode($employees_arr);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No employees found."]);
}
?>
