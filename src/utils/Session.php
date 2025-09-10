<?php
/**
 * Session Management Class for SAMPARK
 * Handles secure session management and authentication
 */

require_once __DIR__ . '/../config/database.php';

class Session {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            $this->configureSession();
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateId();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            $this->regenerateId();
        }
        
        // Check session timeout
        $this->checkTimeout();
    }
    
    private function configureSession() {
        // Set secure session configuration
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        
        // Set session name
        session_name('SAMPARK_SESSION');
    }
    
    private function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    private function checkTimeout() {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > Config::SESSION_TIMEOUT) {
                $this->destroy();
                return;
            }
        }
        $_SESSION['last_activity'] = time();
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public function destroy() {
        session_unset();
        session_destroy();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }
    
    public function login($userType, $userData, $rememberMe = false) {
        // Regenerate session ID on login
        $this->regenerateId();
        
        // Store user data in session
        $_SESSION['logged_in'] = true;
        $_SESSION['user_type'] = $userType; // 'customer' or 'user'
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_role'] = $userData['role'];
        $_SESSION['user_name'] = $userData['name'];
        $_SESSION['user_email'] = $userData['email'];
        
        if ($userType === 'user') {
            $_SESSION['user_department'] = $userData['department'] ?? '';
            $_SESSION['user_division'] = $userData['division'] ?? '';
            $_SESSION['user_zone'] = $userData['zone'] ?? '';
            $_SESSION['login_id'] = $userData['login_id'] ?? '';
        } else {
            $_SESSION['customer_id'] = $userData['customer_id'] ?? '';
            $_SESSION['company_name'] = $userData['company_name'] ?? '';
            $_SESSION['mobile'] = $userData['mobile'] ?? '';
        }
        
        $_SESSION['login_time'] = time();
        $_SESSION['remember_me'] = $rememberMe;
        
        // Handle remember me functionality
        if ($rememberMe) {
            $this->setRememberMeCookie($userType, $userData['id']);
        }
        
        // Generate CSRF token
        $this->generateCSRFToken();
    }
    
    public function logout() {
        // Clear remember me cookie if it exists
        $this->clearRememberMeCookie();
        $this->destroy();
    }
    
    public function isLoggedIn() {
        return $this->get('logged_in', false);
    }
    
    public function getUserType() {
        return $this->get('user_type');
    }
    
    public function getUserRole() {
        return $this->get('user_role');
    }
    
    public function canAccess($requiredRoles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $userRole = $this->getUserRole();
        
        if (is_string($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }
        
        return in_array($userRole, $requiredRoles);
    }
    
    public function generateCSRFToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    public function getCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $this->generateCSRFToken();
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public function setFlash($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
    
    public function getFlash($type = null) {
        if ($type === null) {
            $flash = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            return $flash;
        }
        
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    
    public function hasFlash($type = null) {
        if ($type === null) {
            return !empty($_SESSION['flash']);
        }
        return isset($_SESSION['flash'][$type]);
    }
    
    public function getSessionInfo() {
        return [
            'id' => session_id(),
            'logged_in' => $this->isLoggedIn(),
            'user_type' => $this->getUserType(),
            'user_role' => $this->getUserRole(),
            'login_time' => $this->get('login_time'),
            'last_activity' => $this->get('last_activity'),
            'remaining_time' => Config::SESSION_TIMEOUT - (time() - $this->get('last_activity', time()))
        ];
    }
    
    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie($userType, $userId) {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        $selector = bin2hex(random_bytes(16));
        
        // Store token hash in database (would need a remember_tokens table)
        $tokenHash = hash('sha256', $token);
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        try {
            $db = Database::getInstance();
            
            // Create remember_tokens table if it doesn't exist
            $createTableSql = "CREATE TABLE IF NOT EXISTS remember_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                selector VARCHAR(32) NOT NULL UNIQUE,
                token_hash VARCHAR(64) NOT NULL,
                user_type ENUM('customer', 'user') NOT NULL,
                user_id VARCHAR(50) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_selector (selector),
                INDEX idx_user (user_type, user_id),
                INDEX idx_expires (expires_at)
            )";
            $db->query($createTableSql);
            
            // Clear any existing tokens for this user
            $db->query(
                "DELETE FROM remember_tokens WHERE user_type = ? AND user_id = ?",
                [$userType, $userId]
            );
            
            // Insert new remember token
            $db->query(
                "INSERT INTO remember_tokens (selector, token_hash, user_type, user_id, expires_at) VALUES (?, ?, ?, ?, ?)",
                [$selector, $tokenHash, $userType, $userId, date('Y-m-d H:i:s', $expiry)]
            );
            
            // Set cookie
            $cookieValue = $selector . ':' . $token;
            setcookie(
                'remember_me',
                $cookieValue,
                $expiry,
                '/',
                '',
                isset($_SERVER['HTTPS']),
                true // HttpOnly
            );
            
        } catch (Exception $e) {
            error_log("Remember me cookie error: " . $e->getMessage());
        }
    }
    
    /**
     * Clear remember me cookie
     */
    private function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            // Parse cookie to get selector
            $parts = explode(':', $_COOKIE['remember_me'], 2);
            if (count($parts) === 2) {
                $selector = $parts[0];
                
                try {
                    $db = Database::getInstance();
                    $db->query("DELETE FROM remember_tokens WHERE selector = ?", [$selector]);
                } catch (Exception $e) {
                    error_log("Error clearing remember token: " . $e->getMessage());
                }
            }
            
            // Clear cookie
            setcookie(
                'remember_me',
                '',
                time() - 3600,
                '/',
                '',
                isset($_SERVER['HTTPS']),
                true
            );
        }
    }
    
    /**
     * Check and process remember me cookie
     */
    public function checkRememberMe() {
        if (!$this->isLoggedIn() && isset($_COOKIE['remember_me'])) {
            $parts = explode(':', $_COOKIE['remember_me'], 2);
            
            if (count($parts) === 2) {
                $selector = $parts[0];
                $token = $parts[1];
                
                try {
                    $db = Database::getInstance();
                    
                    // Get remember token from database
                    $rememberToken = $db->fetch(
                        "SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW()",
                        [$selector]
                    );
                    
                    if ($rememberToken && hash_equals($rememberToken['token_hash'], hash('sha256', $token))) {
                        // Valid remember token, auto-login user
                        $userType = $rememberToken['user_type'];
                        $userId = $rememberToken['user_id'];
                        
                        if ($userType === 'customer') {
                            $user = $db->fetch(
                                "SELECT * FROM customers WHERE customer_id = ? AND status = 'approved'",
                                [$userId]
                            );
                            
                            if ($user) {
                                $userData = [
                                    'id' => $user['customer_id'],
                                    'customer_id' => $user['customer_id'],
                                    'role' => 'customer',
                                    'name' => $user['name'],
                                    'email' => $user['email'],
                                    'mobile' => $user['mobile'],
                                    'company_name' => $user['company_name']
                                ];
                                
                                // Log the user in
                                $this->login($userType, $userData, true);
                                
                                // Update last login
                                $db->query("UPDATE customers SET updated_at = NOW() WHERE customer_id = ?", [$userId]);
                                
                                return true;
                            }
                        } else {
                            $user = $db->fetch(
                                "SELECT * FROM users WHERE id = ? AND status = 'active'",
                                [$userId]
                            );
                            
                            if ($user) {
                                $userData = [
                                    'id' => $user['id'],
                                    'login_id' => $user['login_id'],
                                    'role' => $user['role'],
                                    'name' => $user['name'],
                                    'email' => $user['email'],
                                    'mobile' => $user['mobile'],
                                    'department' => $user['department'],
                                    'division' => $user['division'],
                                    'zone' => $user['zone']
                                ];
                                
                                // Log the user in
                                $this->login($userType, $userData, true);
                                
                                // Update last login
                                $db->query("UPDATE users SET updated_at = NOW() WHERE id = ?", [$userId]);
                                
                                return true;
                            }
                        }
                    }
                    
                    // Invalid or expired token, clear it
                    $this->clearRememberMeCookie();
                    
                } catch (Exception $e) {
                    error_log("Remember me check error: " . $e->getMessage());
                    $this->clearRememberMeCookie();
                }
            }
        }
        
        return false;
    }
}
