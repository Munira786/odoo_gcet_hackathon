<?php
/**
 * Authentication Controller
 * Handles user login, registration, token verification, and logout
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/hrms-backend/config/Database.php';

class AuthController {
    private $db;
    private $token_secret = 'hrms_secret_key_2024';

    public function __construct() {
        $db = new Database();
        $this->db = $db->connect();
    }

    /**
     * User Login
     * POST /api/auth/login
     * Returns: JWT token and user data
     */
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password required']);
            return;
        }

        try {
            $query = "SELECT u.id, u.email, u.password, u.role_id, r.name as role_name, 
                             e.id as employee_id, e.first_name, e.last_name, e.employee_code
                      FROM users u
                      LEFT JOIN roles r ON u.role_id = r.id
                      LEFT JOIN employees e ON u.id = e.user_id
                      WHERE u.email = ? AND u.is_active = 1";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch();

            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid email or password']);
                return;
            }

            if (!password_verify($data['password'], $user['password'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid email or password']);
                return;
            }

            $token = $this->generateToken($user);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role_name'],
                    'employee_id' => $user['employee_id'],
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'employee_code' => $user['employee_code']
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
        }
    }

    /**
     * User Registration (Admin Only)
     * POST /api/auth/register
     */
    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['email']) || !isset($data['password']) || !isset($data['role_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email, password, and role required']);
            return;
        }

        try {
            $query = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$data['email']]);
            
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['error' => 'Email already exists']);
                return;
            }

            $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);

            $query = "INSERT INTO users (email, password, role_id, is_active) VALUES (?, ?, ?, 1)";
            $stmt = $this->db->prepare($query);

            if ($stmt->execute([$data['email'], $hashed_password, $data['role_id']])) {
                http_response_code(201);
                echo json_encode([
                    'success' => true, 
                    'message' => 'User registered successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Registration failed']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Verify Token
     * POST /api/auth/verify
     */
    public function verifyToken() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            return;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = $this->decodeToken($token);

        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        http_response_code(200);
        echo json_encode(['valid' => true, 'user' => $decoded]);
    }

    /**
     * Logout
     * POST /api/auth/logout
     */
    public function logout() {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    }

    /**
     * Generate JWT Token (HS256)
     */
    private function generateToken($user) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role_name'],
            'employee_id' => $user['employee_id'],
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60)
        ]));

        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $this->token_secret, true));

        return "$header.$payload.$signature";
    }

    /**
     * Decode JWT Token
     */
    private function decodeToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        $signature = $parts[2];

        $expected_signature = base64_encode(hash_hmac('sha256', "$parts[0].$parts[1]", $this->token_secret, true));

        if ($signature !== $expected_signature) {
            return false;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}

// Route handling
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($request_method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (strpos($request_uri, '/api/auth/login') !== false && $request_method === 'POST') {
    $auth->login();
} elseif (strpos($request_uri, '/api/auth/register') !== false && $request_method === 'POST') {
    $auth->register();
} elseif (strpos($request_uri, '/api/auth/verify') !== false && $request_method === 'POST') {
    $auth->verifyToken();
} elseif (strpos($request_uri, '/api/auth/logout') !== false && $request_method === 'POST') {
    $auth->logout();
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Auth endpoint not found']);
}
?>
