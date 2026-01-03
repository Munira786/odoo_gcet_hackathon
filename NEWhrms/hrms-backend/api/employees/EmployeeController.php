<?php
/**
 * Employee Controller
 * Handles employee CRUD operations
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/config/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/middleware/AuthMiddleware.php';

class EmployeeController {
    private $db;
    private $auth;

    public function __construct() {
        $db = new Database();
        $this->db = $db->connect();
        $this->auth = new AuthMiddleware();
    }

    /**
     * Get All Employees
     * GET /api/employees
     * Admin/HR only
     */
    public function getAll() {
        if (!$this->auth->authorize(['Admin', 'HR'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $query = "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.job_position, 
                             e.phone, e.date_of_birth, e.join_date, e.is_active,
                             d.name as department,
                             (SELECT COUNT(*) FROM attendance WHERE employee_id = e.id AND attendance_date = CURDATE() AND check_in_time IS NOT NULL) as checked_in
                      FROM employees e
                      LEFT JOIN departments d ON e.department_id = d.id
                      ORDER BY e.first_name";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $employees = $stmt->fetchAll();

            foreach ($employees as &$emp) {
                $emp['status'] = $this->getEmployeeStatus($emp['id']);
            }

            http_response_code(200);
            echo json_encode(['data' => $employees]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch employees']);
        }
    }

    /**
     * Get Single Employee
     * GET /api/employees/{id}
     */
    public function getById($id) {
        $user = $this->auth->getUser();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($user['role'] === 'Employee' && $user['employee_id'] != $id) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $query = "SELECT e.*, u.email, c.name as company_name, d.name as department_name,
                             m.first_name as manager_first_name, m.last_name as manager_last_name
                      FROM employees e
                      LEFT JOIN users u ON e.user_id = u.id
                      LEFT JOIN companies c ON e.company_id = c.id
                      LEFT JOIN departments d ON e.department_id = d.id
                      LEFT JOIN employees m ON e.manager_id = m.id
                      WHERE e.id = ?";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $employee = $stmt->fetch();

            if (!$employee) {
                http_response_code(404);
                echo json_encode(['error' => 'Employee not found']);
                return;
            }

            http_response_code(200);
            echo json_encode(['data' => $employee]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch employee']);
        }
    }

    /**
     * Create Employee
     * POST /api/employees
     * Admin only
     */
    public function create() {
        if (!$this->auth->authorize(['Admin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['first_name'], $data['last_name'], $data['email'], 
                   $data['password'], $data['company_id'], $data['department_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        try {
            $this->db->beginTransaction();

            $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
            $query = "INSERT INTO users (email, password, role_id, is_active) VALUES (?, ?, 3, 1)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$data['email'], $hashed_password]);
            $user_id = $this->db->lastInsertId();

            $employee_code = $this->generateEmployeeCode($data['company_id']);

            $query = "INSERT INTO employees (user_id, company_id, department_id, manager_id, employee_code, 
                     first_name, last_name, date_of_birth, gender, phone, job_position, join_date)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $user_id,
                $data['company_id'],
                $data['department_id'],
                $data['manager_id'] ?? null,
                $employee_code,
                $data['first_name'],
                $data['last_name'],
                $data['date_of_birth'] ?? null,
                $data['gender'] ?? null,
                $data['phone'] ?? null,
                $data['job_position'] ?? null,
                $data['join_date'] ?? date('Y-m-d')
            ]);
            $employee_id = $this->db->lastInsertId();

            $query = "INSERT INTO salary_details (employee_id, basic_salary) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $data['basic_salary'] ?? 0]);

            $this->db->commit();

            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Employee created successfully',
                'employee_code' => $employee_code
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create employee']);
        }
    }

    /**
     * Update Employee
     * PUT /api/employees/{id}
     */
    public function update($id) {
        $user = $this->auth->getUser();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($user['role'] === 'Employee' && $user['employee_id'] != $id) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $allowed_fields = ['phone', 'address', 'city', 'state', 'country', 'date_of_birth'];
        
        $set_clause = [];
        $values = [];

        foreach ($allowed_fields as $field) {
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
            $values[] = $id;
            $query = "UPDATE employees SET " . implode(', ', $set_clause) . " WHERE id = ?";
            $stmt = $this->db->prepare($query);

            if ($stmt->execute($values)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
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
     * Get Employee Status
     */
    private function getEmployeeStatus($employee_id) {
        try {
            $today = date('Y-m-d');

            $query = "SELECT id FROM leave_requests WHERE employee_id = ? AND status = 'approved' 
                     AND start_date <= ? AND end_date >= ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $today, $today]);
            
            if ($stmt->fetch()) {
                return 'on_leave';
            }

            $query = "SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ? AND check_in_time IS NOT NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $today]);
            
            if ($stmt->fetch()) {
                return 'present';
            }

            return 'absent';

        } catch (Exception $e) {
            return 'absent';
        }
    }

    /**
     * Generate Employee Code
     * Format: [CompanyCode][Year][SerialNumber]
     */
    private function generateEmployeeCode($company_id) {
        try {
            $query = "SELECT code FROM companies WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$company_id]);
            $company = $stmt->fetch();

            if (!$company) {
                return 'EMP' . time();
            }

            $year = date('Y');

            $query = "SELECT COUNT(*) as count FROM employees WHERE company_id = ? AND YEAR(join_date) = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$company_id, $year]);
            $result = $stmt->fetch();

            $serial = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
            
            return $company['code'] . $year . $serial;

        } catch (Exception $e) {
            return 'EMP' . time();
        }
    }
}

// Route handling
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

$controller = new EmployeeController();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($request_method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$id = null;
if (preg_match('/\/api\/employees\/(\d+)/', $request_uri, $matches)) {
    $id = $matches[1];
}

if (strpos($request_uri, '/api/employees') !== false) {
    if ($request_method === 'GET' && $id) {
        $controller->getById($id);
    } elseif ($request_method === 'GET') {
        $controller->getAll();
    } elseif ($request_method === 'POST') {
        $controller->create();
    } elseif ($request_method === 'PUT' && $id) {
        $controller->update($id);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
?>