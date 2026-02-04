<?php
/**
 * Database Configuration
 * Sistema Gestione Ticket Riparazione
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'RiparazioniDB');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Sistema Gestione Ticket Riparazione');
define('SESSION_TIMEOUT', 1800); // 30 minuti in secondi

// Database connection class
class Database {
    private $conn = null;
    
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $this->conn = new PDO($dsn, DB_USER, DB_PASS);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                die("Errore di connessione al database: " . $e->getMessage());
            }
        }
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}
?>
