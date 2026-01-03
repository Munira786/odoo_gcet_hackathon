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

// Get date range if provided
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Today

$query = "SELECT a.*, e.first_name, e.last_name, e.employee_code, e.department
          FROM attendance a
          JOIN employees e ON a.employee_id = e.id
          WHERE a.date BETWEEN :start_date AND :end_date
          ORDER BY a.date DESC, e.first_name ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(":start_date", $start_date);
$stmt->bindParam(":end_date", $end_date);
$stmt->execute();

$attendance_arr = array();
$attendance_arr["records"] = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $attendance_item = array(
        "id" => $row['id'],
        "employee_id" => $row['employee_id'],
        "employee_name" => $row['first_name'] . " " . $row['last_name'],
        "employee_code" => $row['employee_code'],
        "department" => $row['department'],
        "date" => $row['date'],
        "check_in" => $row['check_in'],
        "check_out" => $row['check_out'],
        "status" => $row['status']
    );
    array_push($attendance_arr["records"], $attendance_item);
}

http_response_code(200);
echo json_encode($attendance_arr);
?>