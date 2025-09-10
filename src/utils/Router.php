<?php
/**
 * Router Class for SAMPARK MVC Framework
 * Handles URL routing and request dispatching
 */

class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put($path, $handler, $middleware = []) {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete($path, $handler, $middleware = []) {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    private function addRoute($method, $path, $handler, $middleware) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if running in subdirectory
        $basePath = Config::getBasePath();
        if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        if (empty($requestUri) || $requestUri === '/') {
            $requestUri = '/';
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    if (!$this->runMiddleware($middleware)) {
                        return;
                    }
                }
                
                // Extract parameters
                $params = $this->extractParams($route['path'], $requestUri);
                
                // Call handler
                return $this->callHandler($route['handler'], $params);
            }
        }
        
        // 404 Not Found
        $this->handleNotFound();
    }
    
    private function matchPath($routePath, $requestUri) {
        // Convert route path to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestUri);
    }
    
    private function extractParams($routePath, $requestUri) {
        $params = [];
        
        // Extract parameter names from route path
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Extract parameter values from request URI
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        preg_match($pattern, $requestUri, $paramValues);
        
        // Combine names and values
        if (!empty($paramNames[1]) && !empty($paramValues)) {
            array_shift($paramValues); // Remove full match
            $params = array_combine($paramNames[1], $paramValues);
        }
        
        return $params;
    }
    
    private function callHandler($handler, $params = []) {
        if (is_string($handler)) {
            // Format: "ControllerName@methodName"
            list($controller, $method) = explode('@', $handler);
            
            $controllerClass = $controller . 'Controller';
            $controllerFile = __DIR__ . "/../controllers/{$controllerClass}.php";
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    
                    if (method_exists($controllerInstance, $method)) {
                        // Convert associative array to indexed array for method parameters
                        $methodParams = array_values($params);
                        return call_user_func_array([$controllerInstance, $method], $methodParams);
                    }
                }
            }
        } elseif (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        $this->handleNotFound();
    }
    
    private function runMiddleware($middleware) {
        if (is_string($middleware)) {
            $middlewareFile = __DIR__ . "/middleware/{$middleware}.php";
            if (file_exists($middlewareFile)) {
                require_once $middlewareFile;
                $middlewareClass = $middleware . 'Middleware';
                if (class_exists($middlewareClass)) {
                    $middlewareInstance = new $middlewareClass();
                    return $middlewareInstance->handle();
                }
            }
        } elseif (is_callable($middleware)) {
            return $middleware();
        }
        
        return true;
    }
    
    private function handleNotFound() {
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
        exit;
    }
    
    public function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    
    /**
     * Add route caching functionality
     */
    private $routeCache = [];
    private $cacheEnabled = false;
    
    public function enableCache($enable = true) {
        $this->cacheEnabled = $enable;
    }
    
    /**
     * Get cached route or cache new route match
     */
    private function getCachedRoute($key, $callback) {
        if (!$this->cacheEnabled) {
            return $callback();
        }
        
        if (isset($this->routeCache[$key])) {
            return $this->routeCache[$key];
        }
        
        $result = $callback();
        $this->routeCache[$key] = $result;
        
        return $result;
    }
    
    /**
     * Enhanced error handling with proper HTTP status codes
     */
    private function handleError($code, $message = '') {
        http_response_code($code);
        
        $errorFile = __DIR__ . "/../views/errors/{$code}.php";
        if (file_exists($errorFile)) {
            include $errorFile;
        } else {
            // Fallback error display
            echo "<h1>Error {$code}</h1>";
            if (!empty($message)) {
                echo "<p>" . htmlspecialchars($message) . "</p>";
            }
        }
        exit;
    }
    
    /**
     * Handle 500 Internal Server Error
     */
    private function handleInternalError($message = 'Internal Server Error') {
        $this->handleError(500, $message);
    }
    
    /**
     * Handle 403 Forbidden
     */
    private function handleForbidden($message = 'Access Forbidden') {
        $this->handleError(403, $message);
    }
}
