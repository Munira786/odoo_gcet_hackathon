<?php
session_start();
include_once '../../config/database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
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

try {
    // Get user_id first
    $query_user = "SELECT user_id FROM employees WHERE id = :id";
    $stmt_user = $db->prepare($query_user);
    $stmt_user->bindParam(":id", $employee_id);
    $stmt_user->execute();

    if ($stmt_user->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(["message" => "Employee not found."]);
        exit();
    }

    $user_row = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $user_id = $user_row['user_id'];

    // Delete employee (cascades will handle related records)
    $query = "DELETE FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Employee deleted successfully."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to delete employee."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Delete failed: " . $e->getMessage()]);
}
?>