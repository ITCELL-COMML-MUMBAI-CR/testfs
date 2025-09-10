<?php
/**
 * Database Configuration for SAMPARK
 * Support and Mediation Portal for All Rail Cargo
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuration
    private $host = 'localhost';
    private $dbname = 'sampark_db';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // First check if Config class is loaded, otherwise use basic error_log
            if (class_exists('Config')) {
                // Get backtrace to find what called this connection attempt
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
                $caller_info = [];
                
                // Find the first caller that isn't inside database.php
                foreach ($backtrace as $trace) {
                    if (isset($trace['file']) && basename($trace['file']) !== 'database.php') {
                        $caller_info = [
                            'caller_file' => $trace['file'],
                            'caller_line' => $trace['line'],
                            'caller_function' => $trace['function'] ?? 'unknown'
                        ];
                        break;
                    }
                }
                
                // Log with connection information and caller context
                Config::logError("Database Connection Error: " . $e->getMessage(), [
                    'host' => $this->host,
                    'database' => $this->dbname,
                    'caller_file' => $caller_info['caller_file'] ?? 'unknown',
                    'caller_line' => $caller_info['caller_line'] ?? 0,
                    'caller_function' => $caller_info['caller_function'] ?? 'unknown'
                ]);
            } else {
                error_log("Database Connection Error: " . $e->getMessage());
            }
            throw new Exception("Database connection failed");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // First check if Config class is loaded, otherwise use basic error_log
            if (class_exists('Config')) {
                // Get backtrace to find what called this query
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
                $caller_info = [];
                
                // Find the first caller that isn't inside database.php
                foreach ($backtrace as $trace) {
                    if (isset($trace['file']) && basename($trace['file']) !== 'database.php') {
                        $caller_info = [
                            'caller_file' => $trace['file'],
                            'caller_line' => $trace['line'],
                            'caller_function' => $trace['function'] ?? 'unknown'
                        ];
                        break;
                    }
                }
                
                // Log with SQL information and caller context
                Config::logError("Database Query Error: " . $e->getMessage(), [
                    'sql' => $sql,
                    'params' => $params,
                    'caller_file' => $caller_info['caller_file'] ?? 'unknown',
                    'caller_line' => $caller_info['caller_line'] ?? 0,
                    'caller_function' => $caller_info['caller_function'] ?? 'unknown'
                ]);
            } else {
                error_log("Database Query Error: " . $e->getMessage());
            }
            throw new Exception("Database query failed");
        }
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
}
