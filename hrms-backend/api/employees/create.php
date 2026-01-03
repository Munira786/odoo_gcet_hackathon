<?php
include_once '../../config/database.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(
    !isset($data->first_name) || 
    !isset($data->last_name) ||
    !isset($data->email) ||
    !isset($data->role_id)
) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
    exit();
}

try {
    $db->beginTransaction();

    // 1. Create User
    $password_hash = password_hash("welcome123", PASSWORD_BCRYPT); // Default password
    $query_user = "INSERT INTO users (email, password, role_id) VALUES (:email, :password, :role_id)";
    $stmt_user = $db->prepare($query_user);
    $stmt_user->bindParam(":email", $data->email);
    $stmt_user->bindParam(":password", $password_hash);
    $stmt_user->bindParam(":role_id", $data->role_id);
    
    if(!$stmt_user->execute()) {
        throw new Exception("Unable to create user (Email might exist).");
    }
    $user_id = $db->lastInsertId();

    // 2. Generate Employee Code
    // Format: [CompanyCode][Initials][Year][SerialNumber] -> HRM JD 2023 0001
    $company_code = "HRM";
    $initials = strtoupper(substr($data->first_name, 0, 1) . substr($data->last_name, 0, 1));
    $year = date("Y");
    
    // Find last serial for this year/pattern
    // Simplification: We just count employees + 1 or find max ID. 
    // Ideally should lock table or use atomic sequence.
    $query_count = "SELECT count(*) as total FROM employees WHERE employee_code LIKE :pattern";
    $pattern = $company_code . $initials . $year . "%";
    $stmt_count = $db->prepare($query_count);
    $stmt_count->bindParam(":pattern", $pattern);
    $stmt_count->execute();
    $row_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $serial = str_pad($row_count['total'] + 1, 4, '0', STR_PAD_LEFT);
    
    $employee_code = $company_code . $initials . $year . $serial;

    // 3. Create Employee
    $query_emp = "INSERT INTO employees (user_id, employee_code, first_name, last_name, job_position, department, joining_date)
                  VALUES (:user_id, :code, :fname, :lname, :pos, :dept, :jdate)";
    
    $stmt_emp = $db->prepare($query_emp);
    $stmt_emp->bindParam(":user_id", $user_id);
    $stmt_emp->bindParam(":code", $employee_code);
    $stmt_emp->bindParam(":fname", $data->first_name);
    $stmt_emp->bindParam(":lname", $data->last_name);
    $stmt_emp->bindParam(":pos", $data->job_position);
    $stmt_emp->bindParam(":dept", $data->department);
    $today = date('Y-m-d');
    $stmt_emp->bindParam(":jdate", $today);

    if($stmt_emp->execute()) {
        $db->commit();
        http_response_code(201);
        echo json_encode(["message" => "Employee created successfully.", "employee_code" => $employee_code]);
    } else {
        throw new Exception("Unable to create employee record.");
    }

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Failed: " . $e->getMessage()]);
}
?>
