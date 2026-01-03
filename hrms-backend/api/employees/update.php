<?php
session_start();
include_once '../../config/database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(["message" => "Employee ID required."]);
    exit();
}

try {
    $query = "UPDATE employees SET 
              first_name = :first_name,
              last_name = :last_name,
              job_position = :job_position,
              department = :department,
              phone = :phone,
              address = :address,
              manager_id = :manager_id
              WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $data->id);
    $stmt->bindParam(":first_name", $data->first_name);
    $stmt->bindParam(":last_name", $data->last_name);
    $stmt->bindParam(":job_position", $data->job_position);
    $stmt->bindParam(":department", $data->department);
    $stmt->bindParam(":phone", $data->phone);
    $stmt->bindParam(":address", $data->address);
    $manager_id = isset($data->manager_id) ? $data->manager_id : null;
    $stmt->bindParam(":manager_id", $manager_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Employee updated successfully."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to update employee."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Update failed: " . $e->getMessage()]);
}
?>