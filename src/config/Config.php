<?php
/**
 * Configuration Class for SAMPARK
 * Support and Mediation Portal for All Rail Cargo
 * Contains application constants, settings, and configuration methods
 */

class Config {
    
    // Application Information
    const APP_NAME = 'SAMPARK';
    const APP_VERSION = '1.0.0';
    const APP_DESCRIPTION = 'Support and Mediation Portal for All Rail Cargo';
    const DEBUG_MODE = true;
    
    // APP_URL and BASE_PATH are now dynamic
    // Use Config::getAppUrl() and Config::getBasePath() instead of constants
    
    // Email Configuration
    const FROM_EMAIL = 'noreply@sampark.railway.gov.in';
    const FROM_NAME = 'SAMPARK Support System';
    const SMTP_HOST = 'localhost';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = '';
    const SMTP_PASSWORD = '';
    const SMTP_ENCRYPTION = 'tls';
    
    // SMS Configuration
    const SMS_API_URL = '';
    const SMS_API_KEY = '';
    const SMS_SENDER_ID = 'SAMPARK';
    
    // File Upload Configuration
    const MAX_FILE_SIZE = 2097152; // 2MB in bytes (final compressed size)
    const MAX_FILES_PER_TICKET = 3;
    const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx'];
    const UPLOAD_PATH = '/uploads/evidence/';
    
    // Pagination
    const RECORDS_PER_PAGE = 25;
    const MAX_RECORDS_PER_PAGE = 100;
    
    // Session Configuration
    const SESSION_TIMEOUT = 3600; // 1 hour in seconds
    const SESSION_NAME = 'SAMPARK_SESSION';
    const CSRF_TOKEN_EXPIRY = 3600; // 1 hour
    
    // Background Refresh Settings
    const BACKGROUND_REFRESH_INTERVAL = 30000; // 30 seconds in milliseconds
    const HEARTBEAT_INTERVAL = 60000; // 1 minute in milliseconds
    const AUTOMATION_INTERVAL = 30; // 30 seconds for automation tasks
    
    // Cache Settings
    const CACHE_DURATION = 300; // 5 minutes in seconds
    const STATS_CACHE_DURATION = 60; // 1 minute for stats
    
    // Security Settings
    const PASSWORD_MIN_LENGTH = 8;
    const PASSWORD_REQUIRE_SPECIAL_CHARS = true;
    const PASSWORD_REQUIRE_NUMBERS = true;
    const PASSWORD_REQUIRE_UPPERCASE = true;
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 900; // 15 minutes
    const LOCKOUT_TIME = 900; // 15 minutes (alias for compatibility)
    
    // Priority Escalation Settings (in hours)
    const PRIORITY_ESCALATION = [
        'medium' => 3,
        'high' => 12,
        'critical' => 24
    ];
    
    
    // Ticket Status Options
    const TICKET_STATUS = [
        'pending' => 'Pending',
        'awaiting_info' => 'Awaiting Information',
        'awaiting_approval' => 'Awaiting Approval',
        'awaiting_feedback' => 'Awaiting Feedback',
        'closed' => 'Closed'
    ];
    
    // Priority Levels
    const PRIORITY_LEVELS = [
        'normal' => 'Normal',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical'
    ];
    
    // User Roles
    const USER_ROLES = [
        'customer' => 'Customer',
        'controller' => 'Controller',
        'controller_nodal' => 'Nodal Controller',
        'admin' => 'Administrator',
        'superadmin' => 'Super Administrator'
    ];
    
    // User Status Options
    const USER_STATUS = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended'
    ];
    
    // Customer Status Options
    const CUSTOMER_STATUS = [
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended'
    ];
    
    // Wagon Types
    const WAGON_TYPES = [
        'container' => 'Container Wagon',
        'covered' => 'Covered Wagon',
        'flat' => 'Flat Wagon',
        'hopper' => 'Hopper Wagon',
        'open' => 'Open Wagon',
        'tank' => 'Tank Wagon',
        'well' => 'Well Wagon'
    ];
    
    // News/Content Types
    const NEWS_TYPES = [
        'news' => 'News',
        'announcement' => 'Announcement',
        'alert' => 'Alert',
        'update' => 'Update'
    ];
    
    // Priority Options for News
    const NEWS_PRIORITY = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent'
    ];
    
    // Notification Types
    const NOTIFICATION_TYPES = [
        'info' => 'Information',
        'success' => 'Success',
        'warning' => 'Warning',
        'error' => 'Error',
        'escalation' => 'Escalation'
    ];
    
    // Railway Divisions (These would typically be loaded from database)
    const RAILWAY_DIVISIONS = [
        'CENTRAL' => 'Central Railway',
        'WESTERN' => 'Western Railway',
        'NORTHERN' => 'Northern Railway',
        'SOUTHERN' => 'Southern Railway',
        'EASTERN' => 'Eastern Railway',
        'NORTHEASTERN' => 'Northeastern Railway',
        'NORTHCENTRAL' => 'North Central Railway',
        'EASTCENTRAL' => 'East Central Railway',
        'WESTCENTRAL' => 'West Central Railway',
        'SOUTHCENTRAL' => 'South Central Railway',
        'SOUTHEAST' => 'Southeast Railway',
        'SOUTHWEST' => 'Southwest Railway',
        'NORTHWESTSERN' => 'Northwestern Railway',
        'EASTCOAST' => 'East Coast Railway',
        'NORTHEASTFRONTIER' => 'Northeast Frontier Railway',
        'SOUTHEASTCENTRAL' => 'Southeast Central Railway',
        'SOUTHWESTCENTRAL' => 'Southwest Central Railway'
    ];
    
    // Railway Zones
    const RAILWAY_ZONES = [
        'CENTRAL' => 'Central Zone',
        'WESTERN' => 'Western Zone',
        'NORTHERN' => 'Northern Zone',
        'SOUTHERN' => 'Southern Zone',
        'EASTERN' => 'Eastern Zone',
        'NORTHEASTERN' => 'Northeastern Zone'
    ];
    
    // Business Hours
    const BUSINESS_START_HOUR = 9;
    const BUSINESS_END_HOUR = 18;
    const BUSINESS_DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    // SLA Configuration
    const SLA_BUSINESS_HOURS_ONLY = true;
    const SLA_EXCLUDE_HOLIDAYS = true;
    const AUTO_CLOSE_DAYS = 3;
    
    // Database Configuration (These would typically be in environment variables)
    private static $dbConfig = [
        'host' => 'localhost',
        'dbname' => 'sampark_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
    
    // Upload Paths (removed hardcoded paths - now calculated dynamically)
    
    /**
     * Get database configuration
     */
    public static function getDatabaseConfig() {
        return self::$dbConfig;
    }
    
    /**
     * Set database configuration
     */
    public static function setDatabaseConfig($config) {
        self::$dbConfig = array_merge(self::$dbConfig, $config);
    }
    
    /**
     * Get upload path (absolute file system path)
     */
    public static function getUploadPath() {
        // Get the document root and build path that works in both XAMPP and hosting
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        
        // For XAMPP, the document root might be something like D:\Apps\XAMPP\htdocs
        // For hosting, it should be the actual document root
        
        // If we're in a subdirectory (like /testfs/), adjust accordingly
        $scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptPath && $scriptPath !== '/') {
            $uploadPath = $documentRoot . $scriptPath . self::UPLOAD_PATH;
        } else {
            $uploadPath = $documentRoot . self::UPLOAD_PATH;
        }
        
        return $uploadPath;
    }
    
    /**
     * Get public upload path (for URLs)
     */
    public static function getPublicUploadPath() {
        // Get the base URL path that works in both XAMPP and hosting
        $scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        if ($scriptPath && $scriptPath !== '/') {
            // We're in a subdirectory like /testfs/public
            return $scriptPath . self::UPLOAD_PATH;
        } else {
            // We're in document root
            return self::UPLOAD_PATH;
        }
    }
    
    /**
     * Get base path for URL routing (dynamic based on environment)
     */
    public static function getBasePath() {
        // Auto-detect based on script name and server configuration
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        
        // Extract the directory part of the script name
        $basePath = dirname($scriptName);
        
        // If running in document root, return empty
        if ($basePath === '/' || $basePath === '\\') {
            return '';
        }
        
        // Auto-detect project folder name from script path
        if (preg_match('#/([^/]+)/public/?#', $scriptName, $matches)) {
            $projectFolder = $matches[1]; // testfs or sampark
            return "/{$projectFolder}/public";
        } elseif (strpos($documentRoot, '/public') !== false && strpos($documentRoot, 'sampark') !== false) {
            // Production: document root ends with sampark/public
            return '';
        }
        
        // Fallback: return the detected base path
        return $basePath;
    }
    
    /**
     * Get application URL (dynamic based on environment)
     */
    public static function getAppUrl() {
        // Check if running from command line
        if (php_sapi_name() === 'cli') {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            if (preg_match('#/([^/]+)/public/?#', $scriptName, $matches)) {
                $projectFolder = $matches[1]; // testfs or sampark
                return "http://localhost/{$projectFolder}"; // XAMPP/localhost CLI (clean URL)
            }
            return 'http://localhost'; // Production CLI
        }
        
        // Auto-detect protocol
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        
        // Get host
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Get clean base path (without /public for user-facing URLs)
        $basePath = self::getCleanBasePath();
        
        return $protocol . '://' . $host . $basePath;
    }
    
    /**
     * Get clean base path for user-facing URLs (without /public)
     */
    public static function getCleanBasePath() {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        
        // Auto-detect project folder name from script path
        if (preg_match('#/([^/]+)/public/?#', $scriptName, $matches)) {
            $projectFolder = $matches[1]; // testfs or sampark
            return "/{$projectFolder}"; // Return clean path without /public
        } elseif (strpos($documentRoot, '/public') !== false && strpos($documentRoot, 'sampark') !== false) {
            // Production: document root ends with sampark/public
            return '';
        }
        
        // Fallback
        $basePath = dirname($scriptName);
        if ($basePath === '/' || $basePath === '\\') {
            return '';
        }
        
        // Remove /public if present
        return str_replace('/public', '', $basePath);
    }
    
    /**
     * Legacy support - get APP_URL
     */
    public static function getAppUrlLegacy() {
        return self::getAppUrl();
    }
    
    /**
     * Generate complaint number
     */
    public static function generateComplaintNumber() {
        $date = date('Ymd');
        $db = Database::getInstance();
        
        // Get the last complaint number for today
        $sql = "SELECT MAX(CAST(SUBSTRING(complaint_id, 9) AS UNSIGNED)) as last_number 
                FROM complaints 
                WHERE complaint_id LIKE ?";
        $result = $db->fetch($sql, [$date . '%']);
        
        $next_number = ($result['last_number'] ?? 0) + 1;
        $complaint_id = $date . str_pad($next_number, 4, '0', STR_PAD_LEFT);
        
        return $complaint_id;
    }
    
    /**
     * Get configuration value with dynamic APP_URL support
     */
    public static function get($key, $default = null) {
        // Handle special dynamic constants
        if (strtoupper($key) === 'APP_URL') {
            return self::getAppUrl();
        }
        if (strtoupper($key) === 'BASE_PATH') {
            return self::getBasePath();
        }
        
        return defined('self::' . strtoupper($key)) ? constant('self::' . strtoupper($key)) : $default;
    }
    
    // Dynamic constants - calculated at runtime
    // We'll initialize these in the constructor or via static initialization
    
    /**
     * Get maximum upload size in bytes
     */
    public static function getMaxUploadSize() {
        return self::MAX_FILE_SIZE;
    }
    
    /**
     * Get maximum upload size formatted
     */
    public static function getMaxUploadSizeFormatted() {
        $size = self::MAX_FILE_SIZE;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
    
    /**
     * Validate file type
     */
    public static function isAllowedFileType($extension) {
        return in_array(strtolower($extension), self::ALLOWED_FILE_TYPES);
    }
    
    /**
     * Get file type MIME mapping
     */
    public static function getFileMimeTypes() {
        return [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }
    
    /**
     * Check if current time is within business hours
     */
    public static function isBusinessHours() {
        $currentHour = (int)date('H');
        $currentDay = date('l');
        
        return in_array($currentDay, self::BUSINESS_DAYS) && 
               $currentHour >= self::BUSINESS_START_HOUR && 
               $currentHour < self::BUSINESS_END_HOUR;
    }
    
    /**
     * Calculate business hours between two dates
     */
    public static function calculateBusinessHours($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $businessHours = 0;
        
        while ($start < $end) {
            $dayOfWeek = $start->format('l');
            $hour = (int)$start->format('H');
            
            if (in_array($dayOfWeek, self::BUSINESS_DAYS) && 
                $hour >= self::BUSINESS_START_HOUR && 
                $hour < self::BUSINESS_END_HOUR) {
                $businessHours++;
            }
            
            $start->add(new DateInterval('PT1H'));
        }
        
        return $businessHours;
    }
    
    /**
     * Get next business day
     */
    public static function getNextBusinessDay($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $nextDay = new DateTime($date);
        $nextDay->add(new DateInterval('P1D'));
        
        while (!in_array($nextDay->format('l'), self::BUSINESS_DAYS)) {
            $nextDay->add(new DateInterval('P1D'));
        }
        
        return $nextDay->format('Y-m-d');
    }
    
    /**
     * Format priority for display
     */
    public static function formatPriority($priority) {
        return self::PRIORITY_LEVELS[$priority] ?? ucfirst($priority);
    }
    
    /**
     * Format status for display
     */
    public static function formatStatus($status) {
        return self::TICKET_STATUS[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
    
    /**
     * Get priority color class
     */
    public static function getPriorityColorClass($priority) {
        $colors = [
            'normal' => 'text-secondary',
            'medium' => 'text-warning',
            'high' => 'text-danger',
            'critical' => 'text-danger fw-bold'
        ];
        
        return $colors[$priority] ?? 'text-secondary';
    }
    
    /**
     * Get status color class
     */
    public static function getStatusColorClass($status) {
        $colors = [
            'pending' => 'text-warning',
            'awaiting_info' => 'text-info',
            'awaiting_approval' => 'text-warning',
            'awaiting_feedback' => 'text-primary',
            'closed' => 'text-success'
        ];
        
        return $colors[$status] ?? 'text-secondary';
    }
    
    /**
     * Get badge class for priority
     */
    public static function getPriorityBadgeClass($priority) {
        $badges = [
            'normal' => 'badge bg-secondary',
            'medium' => 'badge bg-warning',
            'high' => 'badge bg-danger',
            'critical' => 'badge bg-danger'
        ];
        
        return $badges[$priority] ?? 'badge bg-secondary';
    }
    
    /**
     * Get badge class for status
     */
    public static function getStatusBadgeClass($status) {
        $badges = [
            'pending' => 'badge bg-warning',
            'awaiting_info' => 'badge bg-info',
            'awaiting_approval' => 'badge bg-warning',
            'awaiting_feedback' => 'badge bg-primary',
            'closed' => 'badge bg-success'
        ];
        
        return $badges[$status] ?? 'badge bg-secondary';
    }
    
    /**
     * Validate email address
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indian format)
     */
    public static function validatePhone($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Indian mobile number
        return preg_match('/^[6-9]\d{9}$/', $phone);
    }
    
    /**
     * Validate GSTIN format
     */
    public static function validateGSTIN($gstin) {
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/';
        return preg_match($pattern, $gstin);
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . self::PASSWORD_MIN_LENGTH . ' characters long';
        }
        
        if (self::PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (self::PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (self::PASSWORD_REQUIRE_SPECIAL_CHARS && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get environment setting
     */
    public static function getEnv($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
    
    /**
     * Check if application is in debug mode
     */
    public static function isDebugMode() {
        return self::getEnv('APP_DEBUG', false) === 'true';
    }
    
    /**
     * Get timezone
     */
    public static function getTimezone() {
        return self::getEnv('APP_TIMEZONE', 'Asia/Kolkata');
    }
    
    /**
     * Format date for display
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        if (!$date) return '';
        
        $dateTime = new DateTime($date);
        $dateTime->setTimezone(new DateTimeZone(self::getTimezone()));
        
        return $dateTime->format($format);
    }
    
    /**
     * Get relative time (e.g., "2 hours ago")
     */
    public static function getRelativeTime($date) {
        if (!$date) return '';
        
        $dateTime = new DateTime($date);
        $now = new DateTime();
        $interval = $now->diff($dateTime);
        
        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }
    
    /**
     * Log error message
     */
    public static function logError($message, $context = []) {
        $logFile = '../logs/error.log';
        $timestamp = date('Y-m-d H:i:s');
        
        // Get backtrace to identify the file where error occurred
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4); // Increased depth to find caller
        $file = $backtrace[0]['file'] ?? 'unknown';
        $line = $backtrace[0]['line'] ?? 0;
        
        // Find the original caller (usually 2-3 levels up in the stack)
        $caller_file = null;
        $caller_line = null;
        
        // Look for the first file that isn't Config.php or database.php
        foreach ($backtrace as $trace) {
            if (isset($trace['file'])) {
                $current = basename($trace['file']);
                if ($current !== 'Config.php' && $current !== 'database.php') {
                    $caller_file = $trace['file'];
                    $caller_line = $trace['line'] ?? 0;
                    break;
                }
            }
        }
        
        // Add file and line to context if not already present
        if (!isset($context['file'])) {
            $context['file'] = $file;
        }
        if (!isset($context['line'])) {
            $context['line'] = $line;
        }
        
        // Add caller information to context
        if ($caller_file) {
            $context['caller_file'] = $caller_file;
            $context['caller_line'] = $caller_line;
        }
        
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        
        // Include both immediate file and original caller in log
        $fileInfo = basename($file) . ':' . $line;
        $callerInfo = $caller_file ? ' | Called from: ' . basename($caller_file) . ':' . $caller_line : '';
        
        $logEntry = "[{$timestamp}] ERROR [{$fileInfo}]{$callerInfo}: {$message}{$contextStr}" . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log info message
     */
    public static function logInfo($message, $context = []) {
        if (self::isDebugMode()) {
            $logFile = '../logs/app.log';
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
            
            $logEntry = "[{$timestamp}] INFO: {$message}{$contextStr}" . PHP_EOL;
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Custom error handler to log PHP errors
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED',
        ];
        
        $type = $errorTypes[$errno] ?? 'UNKNOWN ERROR';
        $message = "$type: $errstr";
        $context = [
            'file' => $errfile,
            'line' => $errline
        ];
        self::logError($message, $context);
        
        // Don't execute PHP's internal error handler
        return true;
    }
    
    /**
     * Custom exception handler to log PHP exceptions
     */
    public static function exceptionHandler($exception) {
        $file = $exception->getFile();
        $line = $exception->getLine();
        $fileInfo = basename($file) . ':' . $line;
        
        $message = "EXCEPTION [{$fileInfo}]: " . $exception->getMessage();
        $context = [
            'file' => $file,
            'line' => $line,
            'trace' => $exception->getTraceAsString()
        ];
        self::logError($message, $context);
        
        // Show error page
        if (self::isDebugMode()) {
            echo "<h1>Application Error</h1>";
            echo "<p>" . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($file) . " on line " . $line . "</p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            http_response_code(500);
            include '../src/views/errors/500.php';
        }
    }
    
    /**
     * Custom shutdown handler to catch fatal errors
     */
    public static function shutdownHandler() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
            // For fatal errors, include the file name in the log message for quick identification
            $fileInfo = basename($error['file']) . ':' . $error['line'];
            $message = "FATAL ERROR [{$fileInfo}]: " . $error['message'];
            self::logError($message, [
                'file' => $error['file'], 
                'line' => $error['line']
            ]);
        }
    }
}

// Configuration is now dynamic - APP_URL and BASE_PATH are calculated at runtime
