<?php
include_once '../../config/database.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$role = isset($_GET['role']) ? $_GET['role'] : 'Employee';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if($role === 'Admin' || $role === 'HR') {
    // See all
    $query = "SELECT l.*, e.first_name, e.last_name, e.employee_code 
              FROM leave_requests l 
              JOIN employees e ON l.employee_id = e.id 
              ORDER BY l.created_at DESC";
    $stmt = $db->prepare($query);
} else {
    // See own
    $query = "SELECT l.* FROM leave_requests l 
              JOIN employees e ON l.employee_id = e.id 
              WHERE e.user_id = :uid 
              ORDER BY l.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":uid", $user_id);
}

$stmt->execute();
$leaves_arr = array();
$leaves_arr["records"] = array(); // wrapping for standard structure

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    array_push($leaves_arr["records"], $row);
}

echo json_encode($leaves_arr);
?>
