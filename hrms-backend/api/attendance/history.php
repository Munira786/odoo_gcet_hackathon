<?php
include_once '../../config/database.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : 'Employee'; // Should be from token

if ($role === 'Admin' || $role === 'HR') {
    $query = "SELECT a.*, e.first_name, e.last_name, e.employee_code 
              FROM attendance a 
              JOIN employees e ON a.employee_id = e.id 
              ORDER BY a.date DESC";
    $stmt = $db->prepare($query);
} else {
    $query = "SELECT a.*, e.first_name, e.last_name 
              FROM attendance a 
              JOIN employees e ON a.employee_id = e.id 
              WHERE e.user_id = :uid 
              ORDER BY a.date DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":uid", $user_id);
}

$stmt->execute();
$attendance_arr = array();
$attendance_arr["records"] = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    array_push($attendance_arr["records"], $row);
}

echo json_encode($attendance_arr);
?>
