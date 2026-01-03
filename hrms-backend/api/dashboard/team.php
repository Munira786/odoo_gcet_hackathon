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

$today = date('Y-m-d');

$query = "SELECT e.id, e.first_name, e.last_name, e.employee_code, e.job_position, e.department,
          a.status, a.check_in, a.check_out
          FROM employees e
          LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = :date
          ORDER BY e.first_name ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(":date", $today);
$stmt->execute();

$team_arr = array();
$team_arr["records"] = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status = $row['status'] ? $row['status'] : 'Absent';

    $team_item = array(
        "id" => $row['id'],
        "name" => $row['first_name'] . " " . $row['last_name'],
        "employee_code" => $row['employee_code'],
        "job_position" => $row['job_position'],
        "department" => $row['department'],
        "status" => $status,
        "check_in" => $row['check_in'],
        "check_out" => $row['check_out']
    );
    array_push($team_arr["records"], $team_item);
}

http_response_code(200);
echo json_encode($team_arr);
?>