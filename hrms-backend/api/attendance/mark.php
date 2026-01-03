<?php
session_start();
include_once '../../config/database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id)) { // In real app, get from Token
    http_response_code(400);
    echo json_encode(["message" => "User ID required."]);
    exit();
}

// Find Employee ID from User ID
$query_emp = "SELECT id FROM employees WHERE user_id = :uid";
$stmt_emp = $db->prepare($query_emp);
$stmt_emp->bindParam(":uid", $data->user_id);
$stmt_emp->execute();

if ($stmt_emp->rowCount() == 0) {
    http_response_code(404);
    echo json_encode(["message" => "Employee not found."]);
    exit();
}

$emp_row = $stmt_emp->fetch(PDO::FETCH_ASSOC);
$employee_id = $emp_row['id'];
$date = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// Check existing attendance for today
$query_check = "SELECT id, check_in, check_out FROM attendance WHERE employee_id = :eid AND date = :date";
$stmt_check = $db->prepare($query_check);
$stmt_check->bindParam(":eid", $employee_id);
$stmt_check->bindParam(":date", $date);
$stmt_check->execute();

if ($stmt_check->rowCount() == 0) {
    // Check In
    $query = "INSERT INTO attendance (employee_id, date, check_in, status) VALUES (:eid, :date, :time, 'Present')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":eid", $employee_id);
    $stmt->bindParam(":date", $date);
    $stmt->bindParam(":time", $now);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Checked In successfully.", "status" => "Checked In", "time" => $now]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to check in."]);
    }
} else {
    // Already checked in, try Check Out
    $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
    if ($row['check_out'] == null) {
        $query = "UPDATE attendance SET check_out = :time WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":time", $now);
        $stmt->bindParam(":id", $row['id']);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Checked Out successfully.", "status" => "Checked Out", "time" => $now]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to check out."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Already checked out for today."]);
    }
}
?>