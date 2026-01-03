<?php
/**
 * Leave Controller
 * Handles leave requests and approvals
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/config/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/middleware/AuthMiddleware.php';

class LeaveController {
    private $db;
    private $auth;

    public function __construct() {
        $db = new Database();
        $this->db = $db->connect();
        $this->auth = new AuthMiddleware();
    }

    /**
     * Apply for Leave
     * POST /api/leave/apply
     */
    public function apply() {
        $user = $this->auth->getUser();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['leave_type_id'], $data['start_date'], $data['end_date'], $data['reason'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid date range']);
            return;
        }

        try {
            $employee_id = $user['role'] === 'Employee' ? $user['employee_id'] : $data['employee_id'];

            $query = "SELECT id FROM leave_requests 
                     WHERE employee_id = ? AND status IN ('pending', 'approved')
                     AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?))";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $employee_id,
                $data['end_date'],
                $data['start_date'],
                $data['end_date'],
                $data['start_date']
            ]);

            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Overlapping leave request exists']);
                return;
            }

            $attachment_url = null;
            if ($data['leave_type_id'] == 2) {
                if (isset($_FILES['attachment'])) {
                    $attachment_url = $this->handleFileUpload($_FILES['attachment']);
                    if ($attachment_url === false) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid file upload']);
                        return;
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Attachment required for sick leave']);
                    return;
                }
            }

            $query = "INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, reason, attachment_url, status)
                     VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $this->db->prepare($query);

            if ($stmt->execute([$employee_id, $data['leave_type_id'], $data['start_date'], $data['end_date'], $data['reason'], $attachment_url])) {
                http_response_code(201);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Leave request submitted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to submit leave request']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to submit leave request']);
        }
    }

    /**
     * Get Leave Requests
     * GET /api/leave
     */
    public function getRequests() {
        $user = $this->auth->getUser();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            if ($user['role'] === 'Employee') {
                $query = "SELECT lr.*, lt.name as leave_type, e.first_name, e.last_name
                         FROM leave_requests lr
                         LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                         LEFT JOIN employees e ON lr.employee_id = e.id
                         WHERE lr.employee_id = ?
                         ORDER BY lr.created_at DESC";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$user['employee_id']]);
            } else {
                $status = $_GET['status'] ?? null;
                $where = "WHERE 1=1";
                $params = [];

                if ($status) {
                    $where .= " AND lr.status = ?";
                    $params[] = $status;
                }

                $query = "SELECT lr.*, lt.name as leave_type, e.first_name, e.last_name, e.employee_code
                         FROM leave_requests lr
                         LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                         LEFT JOIN employees e ON lr.employee_id = e.id
                         $where
                         ORDER BY lr.created_at DESC";
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
            }

            http_response_code(200);
            echo json_encode(['data' => $stmt->fetchAll()]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch leave requests']);
        }
    }

    /**
     * Approve Leave
     * POST /api/leave/approve/{id}
     * HR/Admin only
     */
    public function approve($id) {
        if (!$this->auth->authorize(['Admin', 'HR'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = $this->auth->getUser();
        
        try {
            $now = date('Y-m-d H:i:s');

            $this->db->beginTransaction();

            $query = "UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_date = ?
                     WHERE id = ? AND status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user['id'], $now, $id]);

            $query = "SELECT start_date, end_date, employee_id FROM leave_requests WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $leave = $stmt->fetch();

            if ($leave) {
                $start = new DateTime($leave['start_date']);
                $end = new DateTime($leave['end_date']);
                
                while ($start <= $end) {
                    $date = $start->format('Y-m-d');
                    $query = "INSERT INTO attendance (employee_id, attendance_date, status)
                             VALUES (?, ?, 'leave')
                             ON DUPLICATE KEY UPDATE status = 'leave'";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([$leave['employee_id'], $date]);
                    $start->modify('+1 day');
                }
            }

            $this->db->commit();

            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'message' => 'Leave approved successfully'
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to approve leave']);
        }
    }

    /**
     * Reject Leave
     * POST /api/leave/reject/{id}
     * HR/Admin only
     */
    public function reject($id) {
        if (!$this->auth->authorize(['Admin', 'HR'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $reason = $data['rejection_reason'] ?? 'No reason provided';

        try {
            $query = "UPDATE leave_requests SET status = 'rejected', rejection_reason = ?
                     WHERE id = ? AND status = 'pending'";
            $stmt = $this->db->prepare($query);

            if ($stmt->execute([$reason, $id])) {
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Leave rejected successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to reject leave']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to reject leave']);
        }
    }

    /**
     * Get Leave Balance
     * GET /api/leave/balance
     */
    public function getBalance() {
        $user = $this->auth->getUser();
        
        if (!$user || $user['role'] !== 'Employee') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $employee_id = $user['employee_id'];
            $year = date('Y');

            $query = "SELECT lt.id, lt.name, lt.days_per_year,
                             COALESCE(SUM(DATEDIFF(lr.end_date, lr.start_date) + 1), 0) as used_days
                      FROM leave_types lt
                      LEFT JOIN leave_requests lr ON lt.id = lr.leave_type_id 
                        AND lr.employee_id = ? AND lr.status = 'approved' AND YEAR(lr.start_date) = ?
                      GROUP BY lt.id, lt.name, lt.days_per_year";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $year]);
            $balances = $stmt->fetchAll();

            foreach ($balances as &$balance) {
                $balance['remaining_days'] = $balance['days_per_year'] - $balance['used_days'];
            }

            http_response_code(200);
            echo json_encode(['data' => $balances]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch leave balance']);
        }
    }

    /**
     * Handle File Upload
     */
    private function handleFileUpload($file) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/uploads/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            return '/hrms-backend/uploads/' . $filename;
        }

        return false;
    }
}

// Route handling
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

$controller = new LeaveController();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($request_method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$id = null;
if (preg_match('/\/api\/leave\/(\d+)/', $request_uri, $matches)) {
    $id = $matches[1];
}

if (strpos($request_uri, '/api/leave/apply') !== false && $request_method === 'POST') {
    $controller->apply();
} elseif (strpos($request_uri, '/api/leave/approve') !== false && $request_method === 'POST' && $id) {
    $controller->approve($id);
} elseif (strpos($request_uri, '/api/leave/reject') !== false && $request_method === 'POST' && $id) {
    $controller->reject($id);
} elseif (strpos($request_uri, '/api/leave/balance') !== false && $request_method === 'GET') {
    $controller->getBalance();
} elseif (strpos($request_uri, '/api/leave') !== false && $request_method === 'GET') {
    $controller->getRequests();
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
?>