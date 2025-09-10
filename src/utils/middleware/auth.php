<?php
/**
 * Authentication Middleware for SAMPARK
 * Ensures user is logged in before accessing protected routes
 */

class AuthMiddleware {
    
    public function handle() {
        $session = new Session();
        
        if (!$session->isLoggedIn()) {
            // Store the current URL for redirect after login
            $currentUrl = $_SERVER['REQUEST_URI'];
            $session->set('redirect_after_login', $currentUrl);
            
            // Check if it's an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required', 'redirect' => Config::APP_URL . '/login']);
                exit;
            }
            
            // Redirect to login page
            header('Location: ' . Config::APP_URL . '/login');
            exit;
        }
        
        return true;
    }
}
