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

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Ideally check role here, assumed handled by frontend + backend token verification middleware
if (!isset($data->employee_id) || !isset($data->basic_salary)) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
    exit();
}

// Calculate Net Salary (Simplistic logic)
$basic = $data->basic_salary;
$hra = isset($data->hra) ? $data->hra : 0;
$allowances = isset($data->allowances) ? $data->allowances : 0;
$pf = isset($data->pf) ? $data->pf : 0;
$tax = isset($data->professional_tax) ? $data->professional_tax : 0;
$bonus = isset($data->bonus) ? $data->bonus : 0;

$net_salary = ($basic + $hra + $allowances + $bonus) - ($pf + $tax);

// Check if salary record exists
$check = "SELECT id FROM salary_details WHERE employee_id = :eid";
$stmt_check = $db->prepare($check);
$stmt_check->bindParam(":eid", $data->employee_id);
$stmt_check->execute();

if ($stmt_check->rowCount() > 0) {
    // Update
    $query = "UPDATE salary_details SET 
                basic_salary = :basic, hra = :hra, allowances = :allow, 
                pf = :pf, professional_tax = :tax, bonus = :bonus, net_salary = :net,
                bank_name = :bank, account_number = :acc, ifsc_code = :ifsc, 
                pan_number = :pan, uan_number = :uan
              WHERE employee_id = :eid";
} else {
    // Insert
    $query = "INSERT INTO salary_details SET 
                employee_id = :eid,
                basic_salary = :basic, hra = :hra, allowances = :allow, 
                pf = :pf, professional_tax = :tax, bonus = :bonus, net_salary = :net,
                bank_name = :bank, account_number = :acc, ifsc_code = :ifsc, 
                pan_number = :pan, uan_number = :uan";
}

$stmt = $db->prepare($query);

$stmt->bindParam(":eid", $data->employee_id);
$stmt->bindParam(":basic", $basic);
$stmt->bindParam(":hra", $hra);
$stmt->bindParam(":allow", $allowances);
$stmt->bindParam(":pf", $pf);
$stmt->bindParam(":tax", $tax);
$stmt->bindParam(":bonus", $bonus);
$stmt->bindParam(":net", $net_salary);
// Bank details
$bank = isset($data->bank_name) ? $data->bank_name : '';
$acc = isset($data->account_number) ? $data->account_number : '';
$ifsc = isset($data->ifsc_code) ? $data->ifsc_code : '';
$pan = isset($data->pan_number) ? $data->pan_number : '';
$uan = isset($data->uan_number) ? $data->uan_number : '';

$stmt->bindParam(":bank", $bank);
$stmt->bindParam(":acc", $acc);
$stmt->bindParam(":ifsc", $ifsc);
$stmt->bindParam(":pan", $pan);
$stmt->bindParam(":uan", $uan);

if ($stmt->execute()) {
    echo json_encode(["message" => "Salary details updated.", "net_salary" => $net_salary]);
} else {
    http_response_code(503);
    echo json_encode(["message" => "Unable to update salary."]);
}
?>