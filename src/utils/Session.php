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
        
        // Regenerate session ID periodically for security (only if logged in)
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            if (!isset($_SESSION['last_regeneration'])) {
                $this->regenerateId();
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes instead of 5
                $this->regenerateId();
            }
        }
        
        // Check session timeout
        $this->checkTimeout();
    }
    
    private function configureSession() {
        // Set secure session configuration
        ini_set('session.cookie_lifetime', Config::SESSION_TIMEOUT); // Use actual session timeout
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Lax'); // Changed from Strict to Lax for better compatibility
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', Config::SESSION_TIMEOUT);

        // Set session name
        session_name('SAMPARK_SESSION');
    }
    
    private function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    private function checkTimeout() {
        $currentTime = time();

        // Always set last_activity if not set
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = $currentTime;
            return;
        }

        // Only check timeout if user is logged in
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            $_SESSION['last_activity'] = $currentTime;
            return;
        }

        $inactiveTime = $currentTime - $_SESSION['last_activity'];

        // Only destroy session if user has been truly inactive (add 60 second buffer)
        if ($inactiveTime > (Config::SESSION_TIMEOUT + 60)) {
            error_log("Session expired for user " . ($_SESSION['user_email'] ?? 'unknown') . " after {$inactiveTime} seconds of inactivity");
            $this->destroy();
            return;
        }

        // Update last activity on all requests except heartbeats to prevent premature timeouts
        if (!$this->isHeartbeatRequest()) {
            $_SESSION['last_activity'] = $currentTime;
        }
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
    
    public function login($userType, $userData) {
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
        
        // Generate CSRF token
        $this->generateCSRFToken();
    }
    
    public function logout() {
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
    
    private function isHeartbeatRequest() {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        return $isAjax && (
            strpos($requestUri, '/api/session-heartbeat') !== false ||
            strpos($requestUri, '/api/heartbeat') !== false ||
            strpos($requestUri, '/api/background-tasks') !== false ||
            strpos($requestUri, '/api/session-status') !== false ||
            strpos($requestUri, '/api/refresh-session') !== false
        );
    }

    public function refreshTimeout() {
        if ($this->isLoggedIn()) {
            $_SESSION['last_activity'] = time();
        }
    }

    public function refreshActivity() {
        if ($this->isLoggedIn()) {
            $_SESSION['last_activity'] = time();
        }
    }

    public function updateActivity() {
        if ($this->isLoggedIn()) {
            $_SESSION['last_activity'] = time();
        }
    }

    public function isExpired() {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        return (time() - $_SESSION['last_activity']) > Config::SESSION_TIMEOUT;
    }

    public function getTimeRemaining() {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        $remaining = Config::SESSION_TIMEOUT - (time() - $_SESSION['last_activity']);
        return max(0, $remaining);
    }

    public function getSessionInfo() {
        return [
            'id' => session_id(),
            'logged_in' => $this->isLoggedIn(),
            'user_type' => $this->getUserType(),
            'user_role' => $this->getUserRole(),
            'login_time' => $this->get('login_time'),
            'last_activity' => $this->get('last_activity'),
            'remaining_time' => $this->getTimeRemaining(),
            'is_expired' => $this->isExpired()
        ];
    }
    
}
