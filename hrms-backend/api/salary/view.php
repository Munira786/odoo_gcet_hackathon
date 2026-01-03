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

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die();
$role = isset($_GET['role']) ? $_GET['role'] : 'Employee';
$target_emp_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : null;

if ($role === 'Employee') {
    // Can only view own salary
    $query = "SELECT s.* FROM salary_details s 
              JOIN employees e ON s.employee_id = e.id 
              WHERE e.user_id = :uid";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":uid", $user_id);
} else {
    // Admin/HR
    if ($target_emp_id) {
        $query = "SELECT * FROM salary_details WHERE employee_id = :eid";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":eid", $target_emp_id);
    } else {
        // List all (maybe just summary or forbidden, usually salary is per-employee view)
        $query = "SELECT s.*, e.first_name, e.last_name, e.employee_code 
                  FROM salary_details s 
                  JOIN employees e ON s.employee_id = e.id";
        $stmt = $db->prepare($query);
    }
}

$stmt->execute();
$num = $stmt->rowCount();

if ($num > 0) {
    if ($target_emp_id || $role === 'Employee') {
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr, $row);
        }
        echo json_encode($arr);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Salary details not found."]);
}
?>