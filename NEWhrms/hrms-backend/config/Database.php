<?php
// config/Database.php - Database connection class

class Database {
    private $host = 'localhost';
    private $db_name = 'hrms';
    private $user = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_CHARSET => 'utf8mb4'
                ]
            );
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }

        return $this->conn;
    }
}
?>