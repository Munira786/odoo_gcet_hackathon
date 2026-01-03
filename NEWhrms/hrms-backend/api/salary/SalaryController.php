<?php
/**
 * Salary Controller
 * Handles salary information and slip generation
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/config/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/middleware/AuthMiddleware.php';

class SalaryController {
    private $db;
    private $auth;

    public function __construct() {
        $db = new Database();
        $this->db = $db->connect();
        $this->auth = new AuthMiddleware();
    }

    /**
     * Get Salary Details
     * GET /api/salary/{id}
     */
    public function getSalaryDetails($employee_id) {
        $user = $this->auth->getUser();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($user['role'] === 'Employee' && $user['employee_id'] != $employee_id) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $query = "SELECT sd.*, e.first_name, e.last_name, e.employee_code, e.job_position
                     FROM salary_details sd
                     LEFT JOIN employees e ON sd.employee_id = e.id
                     WHERE sd.employee_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id]);
            $salary = $stmt->fetch();

            if (!$salary) {
                http_response_code(404);
                echo json_encode(['error' => 'Salary details not found']);
                return;
            }

            $salary['gross_salary'] = floatval($salary['basic_salary']) + floatval($salary['hra']) + floatval($salary['allowances']) + floatval($salary['bonus']);
            $salary['total_deductions'] = floatval($salary['pf']) + floatval($salary['professional_tax']);
            $salary['net_salary'] = $salary['gross_salary'] - $salary['total_deductions'];

            http_response_code(200);
            echo json_encode(['data' => $salary]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch salary details']);
        }
    }

    /**
     * Update Salary
     * PUT /api/salary/{id}
     * HR/Admin only
     */
    public function update($employee_id) {
        if (!$this->auth->authorize(['Admin', 'HR'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $updatable = [
            'basic_salary', 'hra', 'allowances', 'bonus', 'pf', 'professional_tax',
            'bank_name', 'account_number', 'ifsc_code', 'pan', 'uan', 'salary_revision_date'
        ];

        $set_clause = [];
        $values = [];

        foreach ($updatable as $field) {
            if (isset($data[$field])) {
                $set_clause[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($set_clause)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid fields to update']);
            return;
        }

        try {
            $values[] = $employee_id;
            $query = "UPDATE salary_details SET " . implode(', ', $set_clause) . " WHERE employee_id = ?";
            $stmt = $this->db->prepare($query);

            if ($stmt->execute($values)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Salary updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Update failed']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Update failed']);
        }
    }

    /**
     * Get All Salaries
     * GET /api/salary
     * HR/Admin only
     */
    public function getAll() {
        if (!$this->auth->authorize(['Admin', 'HR'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $query = "SELECT sd.id, e.employee_code, e.first_name, e.last_name, e.job_position,
                             sd.basic_salary, sd.hra, sd.allowances, sd.bonus,
                             (sd.basic_salary + sd.hra + sd.allowances + sd.bonus) as gross_salary,
                             (sd.pf + sd.professional_tax) as total_deductions,
                             (sd.basic_salary + sd.hra + sd.allowances + sd.bonus - sd.pf - sd.professional_tax) as net_salary
                      FROM salary_details sd
                      LEFT JOIN employees e ON sd.employee_id = e.id
                      ORDER BY e.first_name";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            http_response_code(200);
            echo json_encode(['data' => $stmt->fetchAll()]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch salaries']);
        }
    }

    /**
     * Generate Salary Slip
     * GET /api/salary/slip/{id}
     */
    public function generateSlip($employee_id) {
        $user = $this->auth->getUser();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($user['role'] === 'Employee' && $user['employee_id'] != $employee_id) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $month = $_GET['month'] ?? date('Y-m');

            $query = "SELECT sd.*, e.first_name, e.last_name, e.employee_code, e.job_position, d.name as department
                     FROM salary_details sd
                     LEFT JOIN employees e ON sd.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     WHERE sd.employee_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id]);
            $salary = $stmt->fetch();

            if (!$salary) {
                http_response_code(404);
                echo json_encode(['error' => 'Salary details not found']);
                return;
            }

            $query = "SELECT COUNT(*) as present_days FROM attendance 
                     WHERE employee_id = ? AND status = 'present' AND DATE_FORMAT(attendance_date, '%Y-%m') = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $month]);
            $attendance = $stmt->fetch();
            $present_days = $attendance['present_days'] ?? 0;

            $query = "SELECT COUNT(DISTINCT attendance_date) as leave_days FROM attendance 
                     WHERE employee_id = ? AND status = 'leave' AND DATE_FORMAT(attendance_date, '%Y-%m') = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $month]);
            $leaves = $stmt->fetch();
            $leave_days = $leaves['leave_days'] ?? 0;

            $total_working_days = 30;
            $payable_days = $present_days + $leave_days;
            
            $basic = ($salary['basic_salary'] / $total_working_days) * $payable_days;
            $hra = ($salary['hra'] / $total_working_days) * $payable_days;
            $allowances = ($salary['allowances'] / $total_working_days) * $payable_days;
            $bonus = ($salary['bonus'] / $total_working_days) * $payable_days;
            $pf = ($salary['pf'] / $total_working_days) * $payable_days;
            $professional_tax = ($salary['professional_tax'] / $total_working_days) * $payable_days;

            $gross = $basic + $hra + $allowances + $bonus;
            $deductions = $pf + $professional_tax;
            $net = $gross - $deductions;

            http_response_code(200);
            echo json_encode([
                'data' => [
                    'employee_name' => $salary['first_name'] . ' ' . $salary['last_name'],
                    'employee_code' => $salary['employee_code'],
                    'department' => $salary['department'],
                    'job_position' => $salary['job_position'],
                    'month' => $month,
                    'attendance' => [
                        'present_days' => (int)$present_days,
                        'leave_days' => (int)$leave_days,
                        'absent_days' => $total_working_days - $present_days - $leave_days,
                        'payable_days' => (int)$payable_days,
                        'total_days' => $total_working_days
                    ],
                    'earnings' => [
                        'basic' => round($basic, 2),
                        'hra' => round($hra, 2),
                        'allowances' => round($allowances, 2),
                        'bonus' => round($bonus, 2),
                        'gross' => round($gross, 2)
                    ],
                    'deductions' => [
                        'pf' => round($pf, 2),
                        'professional_tax' => round($professional_tax, 2),
                        'total' => round($deductions, 2)
                    ],
                    'net_salary' => round($net, 2),
                    'bank_details' => [
                        'bank_name' => $salary['bank_name'] ?? 'N/A',
                        'account_number' => $salary['account_number'] ?? 'N/A',
                        'ifsc_code' => $salary['ifsc_code'] ?? 'N/A'
                    ]
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to generate salary slip']);
        }
    }
}

// Route handling
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

$controller = new SalaryController();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($request_method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$id = null;
if (preg_match('/\/api\/salary\/(\d+)/', $request_uri, $matches)) {
    $id = $matches[1];
}

if (strpos($request_uri, '/api/salary/slip') !== false && $request_method === 'GET' && $id) {
    $controller->generateSlip($id);
} elseif (strpos($request_uri, '/api/salary') !== false && $request_method === 'GET' && $id) {
    $controller->getSalaryDetails($id);
} elseif (strpos($request_uri, '/api/salary') !== false && $request_method === 'GET') {
    $controller->getAll();
} elseif (strpos($request_uri, '/api/salary') !== false && $request_method === 'PUT' && $id) {
    $controller->update($id);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
?>