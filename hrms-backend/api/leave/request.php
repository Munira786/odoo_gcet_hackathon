<?php
include_once '../../config/database.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!isset($data->user_id) || !isset($data->leave_type) || !isset($data->start_date)) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
    exit();
}

// Get Employee ID
$query_emp = "SELECT id FROM employees WHERE user_id = :uid";
$stmt_emp = $db->prepare($query_emp);
$stmt_emp->bindParam(":uid", $data->user_id);
$stmt_emp->execute();
$emp = $stmt_emp->fetch(PDO::FETCH_ASSOC);

if(!$emp) {
    http_response_code(404);
    echo json_encode(["message" => "Employee not found."]);
    exit();
}

$query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, status)
          VALUES (:eid, :type, :start, :end, :reason, 'Pending')";

$stmt = $db->prepare($query);
$stmt->bindParam(":eid", $emp['id']);
$stmt->bindParam(":type", $data->leave_type);
$stmt->bindParam(":start", $data->start_date);
$stmt->bindParam(":end", $data->end_date);
$stmt->bindParam(":reason", $data->reason);

if($stmt->execute()) {
    echo json_encode(["message" => "Leave request submitted."]);
} else {
    http_response_code(503);
    echo json_encode(["message" => "Unable to submit leave request."]);
}
?>
