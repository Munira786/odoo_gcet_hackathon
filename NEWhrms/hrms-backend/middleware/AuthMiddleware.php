<?php
/**
 * Authentication Middleware
 * Handles JWT token validation and authorization checks
 */

class AuthMiddleware {
    private $user = null;
    private $token_secret = 'hrms_secret_key_2024';
    private $token_lifetime = 604800; // 7 days in seconds

    /**
     * Constructor
     * Automatically authenticates the request
     */
    public function __construct() {
        $this->authenticateToken();
    }

    /**
     * Extract and validate JWT token from request
     * Sets $this->user if token is valid
     */
    private function authenticateToken() {
        // Get all headers
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return;
        }

        // Extract token from Bearer header
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $this->user = $this->decodeToken($token);
    }

    /**
     * Decode and validate JWT token
     * Returns payload array if valid, null otherwise
     */
    private function decodeToken($token) {
        // Split token into three parts
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }

        $header = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        $signature = $parts[2];

        // Verify signature
        $expected_signature = base64_encode(
            hash_hmac('sha256', "$parts[0].$parts[1]", $this->token_secret, true)
        );

        if ($signature !== $expected_signature) {
            return null;
        }

        // Check token expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Get authenticated user
     * Returns user payload or null if not authenticated
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Check if user has any of the specified roles
     * @param array $allowed_roles - Array of allowed role names
     * @return bool - True if user has one of the allowed roles
     */
    public function authorize($allowed_roles) {
        // User must be authenticated
        if (!$this->user) {
            return false;
        }

        // Check if user's role is in allowed roles
        return in_array($this->user['role'], $allowed_roles);
    }

    /**
     * Check if user is authenticated
     * @return bool - True if user has valid token
     */
    public function isAuthenticated() {
        return $this->user !== null;
    }

    /**
     * Check if user has specific role
     * @param string $role - Role name to check
     * @return bool - True if user has this role
     */
    public function hasRole($role) {
        return $this->user && $this->user['role'] === $role;
    }

    /**
     * Check if user is admin
     * @return bool
     */
    public function isAdmin() {
        return $this->hasRole('Admin');
    }

    /**
     * Check if user is HR
     * @return bool
     */
    public function isHR() {
        return $this->hasRole('HR');
    }

    /**
     * Check if user is employee
     * @return bool
     */
    public function isEmployee() {
        return $this->hasRole('Employee');
    }

    /**
     * Get user's employee ID
     * @return int|null
     */
    public function getEmployeeId() {
        return $this->user['employee_id'] ?? null;
    }

    /**
     * Get user's role
     * @return string|null
     */
    public function getRole() {
        return $this->user['role'] ?? null;
    }

    /**
     * Get user's email
     * @return string|null
     */
    public function getEmail() {
        return $this->user['email'] ?? null;
    }

    /**
     * Get user's ID
     * @return int|null
     */
    public function getUserId() {
        return $this->user['id'] ?? null;
    }
}
?>