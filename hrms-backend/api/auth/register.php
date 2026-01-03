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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (!isset($data->email) || !isset($data->password) || !isset($data->first_name) || !isset($data->last_name) || !isset($data->role_id)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields: email, password, first_name, last_name, role_id"]);
    exit();
}

$email = trim($data->email);
$password = $data->password;
$first_name = trim($data->first_name);
$last_name = trim($data->last_name);
$role_id = $data->role_id;
$job_position = isset($data->job_position) ? trim($data->job_position) : null;
$department = isset($data->department) ? trim($data->department) : null;
$phone = isset($data->phone) ? trim($data->phone) : null;

// Check if email already exists
$check_query = "SELECT id FROM users WHERE email = :email";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindParam(":email", $email);
$check_stmt->execute();

if ($check_stmt->rowCount() > 0) {
    http_response_code(409);
    echo json_encode(["message" => "Email already exists."]);
    exit();
}

try {
    $db->beginTransaction();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $user_query = "INSERT INTO users (email, password, role_id) VALUES (:email, :password, :role_id)";
    $user_stmt = $db->prepare($user_query);
    $user_stmt->bindParam(":email", $email);
    $user_stmt->bindParam(":password", $hashed_password);
    $user_stmt->bindParam(":role_id", $role_id);
    $user_stmt->execute();

    $user_id = $db->lastInsertId();

    // Generate employee code (format: EMP + year + sequential number)
    $year = date('Y');
    $count_query = "SELECT COUNT(*) as count FROM employees WHERE employee_code LIKE :pattern";
    $count_stmt = $db->prepare($count_query);
    $pattern = "EMP{$year}%";
    $count_stmt->bindParam(":pattern", $pattern);
    $count_stmt->execute();
    $count_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $next_number = str_pad($count_row['count'] + 1, 4, '0', STR_PAD_LEFT);
    $employee_code = "EMP{$year}{$next_number}";

    // Insert employee
    $emp_query = "INSERT INTO employees (user_id, employee_code, first_name, last_name, job_position, department, phone, joining_date) 
                  VALUES (:user_id, :employee_code, :first_name, :last_name, :job_position, :department, :phone, CURDATE())";
    $emp_stmt = $db->prepare($emp_query);
    $emp_stmt->bindParam(":user_id", $user_id);
    $emp_stmt->bindParam(":employee_code", $employee_code);
    $emp_stmt->bindParam(":first_name", $first_name);
    $emp_stmt->bindParam(":last_name", $last_name);
    $emp_stmt->bindParam(":job_position", $job_position);
    $emp_stmt->bindParam(":department", $department);
    $emp_stmt->bindParam(":phone", $phone);
    $emp_stmt->execute();

    $db->commit();

    http_response_code(201);
    echo json_encode([
        "message" => "Registration successful.",
        "employee_code" => $employee_code,
        "user_id" => $user_id
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Registration failed: " . $e->getMessage()]);
}
?>