<?php
include_once '../../config/database.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
    exit();
}

$email = $data->email;
$password = $data->password;

$query = "SELECT u.id, u.email, u.password, r.name as role_name, e.first_name, e.last_name, e.profile_picture 
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
        // Successful login
        // In a real app, generate a JWT here. 
        // For this Demo, we return the User object to be stored in Client Context.
        
        $user_data = [
            "id" => $row['id'],
            "email" => $row['email'],
            "role" => $row['role_name'],
            "name" => $row['first_name'] . ' ' . $row['last_name'],
            "profile_picture" => $row['profile_picture']
        ];

        http_response_code(200);
        echo json_encode([
            "message" => "Login successful.",
            "user" => $user_data,
            "token" => bin2hex(random_bytes(16)) // Dummy token for client side checks
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
