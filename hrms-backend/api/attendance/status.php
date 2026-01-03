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

$query = "SELECT a.check_in, a.check_out, a.status 
          FROM attendance a 
          JOIN employees e ON a.employee_id = e.id 
          WHERE e.user_id = :uid AND a.date = :date";

$stmt = $db->prepare($query);
$stmt->bindParam(":uid", $user_id);
$today = date('Y-m-d');
$stmt->bindParam(":date", $today);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
} else {
    echo json_encode(["status" => "Not Checked In"]);
}
?>