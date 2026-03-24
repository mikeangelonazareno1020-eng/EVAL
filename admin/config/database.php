<?php
/**
 * Filename: config/database.php
 * Database Configuration
 */

class Database
{
    private $host = "localhost";
    private $db_name = "hcc-multicampus";
    private $username = "root";
    private $password = "";
    public $conn;

    // private $host = "localhost";
    // private $db_name = "u469776567_multicampus";
    // private $username = "u469776567_multicampus";
    // private $password = "Hcc_multicampus1946";
    // public $conn;

    /**
     * Get database connection
     */
    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8mb4");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>