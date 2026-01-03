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

if(!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["message" => "Email and password are required."]);
    exit();
}

$email = trim($data->email);
$password = $data->password;

$query = "SELECT u.id, u.email, u.password, r.name as role_name, e.id as employee_id, e.first_name, e.last_name, e.profile_picture, e.employee_code 
          FROM users u 
          JOIN roles r ON u.role_id = r.id 
          LEFT JOIN employees e ON u.id = e.user_id 
          WHERE u.email = :email LIMIT 0,1";

$stmt = $db->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(password_verify($password, $row['password'])) {
        // Successful login - create session
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['employee_id'] = $row['employee_id'];
        $_SESSION['role'] = $row['role_name'];
        $_SESSION['email'] = $row['email'];
        
        $user_data = [
            "id" => $row['id'],
            "employee_id" => $row['employee_id'],
            "email" => $row['email'],
            "role" => $row['role_name'],
            "name" => $row['first_name'] . ' ' . $row['last_name'],
            "first_name" => $row['first_name'],
            "last_name" => $row['last_name'],
            "employee_code" => $row['employee_code'],
            "profile_picture" => $row['profile_picture']
        ];

        http_response_code(200);
        echo json_encode([
            "message" => "Login successful.",
            "user" => $user_data,
            "token" => session_id() // Return session ID as token
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid password."]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "User not found."]);
}
?>
