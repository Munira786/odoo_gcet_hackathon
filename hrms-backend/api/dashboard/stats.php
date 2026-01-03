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

// Get total employees
$query_total = "SELECT COUNT(*) as total FROM employees";
$stmt_total = $db->prepare($query_total);
$stmt_total->execute();
$total_row = $stmt_total->fetch(PDO::FETCH_ASSOC);

// Get today's attendance stats
$today = date('Y-m-d');
$query_present = "SELECT COUNT(*) as present FROM attendance WHERE date = :date AND status = 'Present'";
$stmt_present = $db->prepare($query_present);
$stmt_present->bindParam(":date", $today);
$stmt_present->execute();
$present_row = $stmt_present->fetch(PDO::FETCH_ASSOC);

// Get on leave count
$query_leave = "SELECT COUNT(*) as on_leave FROM attendance WHERE date = :date AND status = 'Leave'";
$stmt_leave = $db->prepare($query_leave);
$stmt_leave->bindParam(":date", $today);
$stmt_leave->execute();
$leave_row = $stmt_leave->fetch(PDO::FETCH_ASSOC);

// Calculate absent (total - present - on_leave)
$absent = $total_row['total'] - $present_row['present'] - $leave_row['on_leave'];

// Get pending leave requests
$query_pending = "SELECT COUNT(*) as pending FROM leave_requests WHERE status = 'Pending'";
$stmt_pending = $db->prepare($query_pending);
$stmt_pending->execute();
$pending_row = $stmt_pending->fetch(PDO::FETCH_ASSOC);

$stats = array(
    "total_employees" => $total_row['total'],
    "present_today" => $present_row['present'],
    "on_leave" => $leave_row['on_leave'],
    "absent" => $absent,
    "pending_leaves" => $pending_row['pending']
);

http_response_code(200);
echo json_encode($stats);
?>