<?php
/**
 * Attendance Controller
 * Handles check-in/out and attendance records
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/config/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/middleware/AuthMiddleware.php';

class AttendanceController {
    private $db;
    private $auth;

    public function __construct() {
        $db = new Database();
        $this->db = $db->connect();
        $this->auth = new AuthMiddleware();
    }

    /**
     * POST /api/attendance/check-in
     */
    public function checkIn() {
        $user = $this->auth->getUser();

        if (!$user || $user['role'] !== 'Employee') {
            http_response_code(403);
            echo json_encode(['error' => 'Only employees can check in']);
            return;
        }

        try {
            $today = date('Y-m-d');
            $employee_id = $user['employee_id'];

            $query = "SELECT id FROM attendance 
                      WHERE employee_id = ? AND attendance_date = ? AND check_in_time IS NOT NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $today]);

            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Already checked in today']);
                return;
            }

            $now = date('Y-m-d H:i:s');
            $query = "INSERT INTO attendance (employee_id, check_in_time, attendance_date, status)
                      VALUES (?, ?, ?, 'present')
                      ON DUPLICATE KEY UPDATE check_in_time = VALUES(check_in_time), status = 'present'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $now, $today]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Checked in successfully',
                'check_in_time' => $now
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Check-in failed']);
        }
    }

    /**
     * POST /api/attendance/check-out
     */
    public function checkOut() {
        $user = $this->auth->getUser();

        if (!$user || $user['role'] !== 'Employee') {
            http_response_code(403);
            echo json_encode(['error' => 'Only employees can check out']);
            return;
        }

        try {
            $today = date('Y-m-d');
            $employee_id = $user['employee_id'];

            $query = "SELECT id FROM attendance 
                      WHERE employee_id = ? AND attendance_date = ?
                      AND check_in_time IS NOT NULL AND check_out_time IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $today]);

            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Not checked in or already checked out']);
                return;
            }

            $now = date('Y-m-d H:i:s');
            $query = "UPDATE attendance 
                      SET check_out_time = ? 
                      WHERE employee_id = ? AND attendance_date = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$now, $employee_id, $today]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Checked out successfully',
                'check_out_time' => $now
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Check-out failed']);
        }
    }

    /**
     * GET /api/attendance/today
     */
    public function getToday() {
        $user = $this->auth->getUser();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $today = date('Y-m-d');
            $employee_id = $user['employee_id'];

            $query = "SELECT check_in_time, check_out_time, status 
                      FROM attendance WHERE employee_id = ? AND attendance_date = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $today]);

            $attendance = $stmt->fetch();

            http_response_code(200);
            echo json_encode([
                'data' => $attendance ?: [
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => 'absent'
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch today attendance']);
        }
    }

    /**
     * GET /api/attendance
     */
    public function getAttendance() {
        $user = $this->auth->getUser();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            if ($user['role'] === 'Employee') {
                $query = "SELECT * FROM attendance 
                          WHERE employee_id = ? 
                          ORDER BY attendance_date DESC";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$user['employee_id']]);
            } else {
                $query = "SELECT a.*, e.first_name, e.last_name, e.employee_code
                          FROM attendance a
                          LEFT JOIN employees e ON a.employee_id = e.id
                          ORDER BY a.attendance_date DESC";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
            }

            http_response_code(200);
            echo json_encode(['data' => $stmt->fetchAll()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch attendance']);
        }
    }

    /**
     * GET /api/attendance/summary
     */
    public function getSummary() {
        $user = $this->auth->getUser();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $month = $_GET['month'] ?? date('Y-m');
            $employee_id = $user['employee_id'];

            $query = "SELECT 
                        SUM(status='present') as present_days,
                        SUM(status='leave') as leave_days,
                        SUM(status='absent') as absent_days
                      FROM attendance
                      WHERE employee_id = ?
                      AND DATE_FORMAT(attendance_date, '%Y-%m') = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id, $month]);
            $summary = $stmt->fetch();

            http_response_code(200);
            echo json_encode([
                'data' => [
                    'present_days' => (int)$summary['present_days'],
                    'leave_days' => (int)$summary['leave_days'],
                    'absent_days' => (int)$summary['absent_days'],
                    'total_days' => 30
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch summary']);
        }
    }
}

/* ================= ROUTER ================= */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$controller = new AttendanceController();

if ($method === 'POST' && strpos($uri, '/api/attendance/check-in') !== false) {
    $controller->checkIn();
} elseif ($method === 'POST' && strpos($uri, '/api/attendance/check-out') !== false) {
    $controller->checkOut();
} elseif ($method === 'GET' && strpos($uri, '/api/attendance/today') !== false) {
    $controller->getToday();
} elseif ($method === 'GET' && strpos($uri, '/api/attendance/summary') !== false) {
    $controller->getSummary();
} elseif ($method === 'GET' && strpos($uri, '/api/attendance') !== false) {
    $controller->getAttendance();
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Attendance API route not found']);
}
