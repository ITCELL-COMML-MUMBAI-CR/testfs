<?php
/**
 * Validation Utility Class for SAMPARK
 * Handles form validation and data sanitization
 */

class Validator {
    
    private $errors = [];
    
    public function validate($data, $rules) {
        $this->errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $fieldRules = explode('|', $rule);
            
            foreach ($fieldRules as $singleRule) {
                $this->validateField($field, $value, $singleRule, $data);
            }
        }
        
        return empty($this->errors);
    }
    
    private function validateField($field, $value, $rule, $allData) {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $parameter = $parts[1] ?? null;
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, ucfirst($field) . ' is required');
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid email address');
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $parameter) {
                    $this->addError($field, ucfirst($field) . " must be at least {$parameter} characters");
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $parameter) {
                    $this->addError($field, ucfirst($field) . " must not exceed {$parameter} characters");
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a number');
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters');
                }
                break;
                
            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters and numbers');
                }
                break;
                
            case 'phone':
                if (!empty($value) && !preg_match('/^[6-9]\d{9}$/', $value)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid 10-digit mobile number');
                }
                break;
                
            case 'gstin':
                if (!empty($value) && !$this->validateGSTIN($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid GSTIN');
                }
                break;
                
            case 'password':
                if (!empty($value) && !$this->validatePassword($value)) {
                    $this->addError($field, 'Password must be at least 8 characters with uppercase, lowercase, number and special character');
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!empty($value) && $value !== ($allData[$confirmField] ?? '')) {
                    $this->addError($field, ucfirst($field) . ' confirmation does not match');
                }
                break;
                
            case 'unique':
                // Format: unique:table,column,except_id,primary_key_column
                $tableParts = explode(',', $parameter);
                $table = $tableParts[0];
                $column = $tableParts[1] ?? $field;
                $exceptId = $tableParts[2] ?? null;
                $primaryKeyColumn = $tableParts[3] ?? 'id';
                
                if (!empty($value) && !$this->isUnique($table, $column, $value, $exceptId, $primaryKeyColumn)) {
                    $this->addError($field, ucfirst($field) . ' already exists');
                }
                break;
                
            case 'exists':
                // Format: exists:table,column
                $tableParts = explode(',', $parameter);
                $table = $tableParts[0];
                $column = $tableParts[1] ?? $field;
                
                if (!empty($value) && !$this->exists($table, $column, $value)) {
                    $this->addError($field, 'Selected ' . $field . ' is invalid');
                }
                break;
                
            case 'in':
                // Format: in:value1,value2,value3
                $allowedValues = explode(',', $parameter);
                if (!empty($value) && !in_array($value, $allowedValues)) {
                    $this->addError($field, ucfirst($field) . ' must be one of: ' . implode(', ', $allowedValues));
                }
                break;
                
            case 'file':
                if (!empty($value) && !$this->validateFile($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid file');
                }
                break;
                
            case 'image':
                if (!empty($value) && !$this->validateImage($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid image file');
                }
                break;
                
            case 'date':
                if (!empty($value) && !$this->validateDate($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid date');
                }
                break;
                
            case 'future_date':
                if (!empty($value) && !$this->validateFutureDate($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a future date');
                }
                break;
                
            case 'past_date':
                if (!empty($value) && !$this->validatePastDate($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a past date');
                }
                break;
        }
    }
    
    private function validateGSTIN($gstin) {
        // GSTIN validation pattern
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/';
        return preg_match($pattern, $gstin);
    }
    
    private function validatePassword($password) {
        // Password must have: 8+ chars, uppercase, lowercase, number, special char
        if (strlen($password) < 8) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
        
        return true;
    }
    
    private function isUnique($table, $column, $value, $exceptId = null, $primaryKeyColumn = 'id') {
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];
        
        if ($exceptId) {
            $sql .= " AND {$primaryKeyColumn} != ?";
            $params[] = $exceptId;
        }
        
        $result = $db->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    private function exists($table, $column, $value) {
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $db->fetch($sql, [$value]);
        return $result['count'] > 0;
    }
    
    private function validateFile($file) {
        if (!is_array($file)) return false;
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        
        $allowedTypes = Config::ALLOWED_FILE_TYPES;
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        return in_array($extension, $allowedTypes);
    }
    
    private function validateImage($file) {
        if (!$this->validateFile($file)) return false;
        
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        return in_array($extension, $imageTypes);
    }
    
    private function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    private function validateFutureDate($date) {
        if (!$this->validateDate($date)) return false;
        return strtotime($date) > time();
    }
    
    private function validatePastDate($date) {
        if (!$this->validateDate($date)) return false;
        return strtotime($date) < time();
    }
    
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getError($field) {
        return $this->errors[$field] ?? [];
    }
    
    public function hasError($field = null) {
        if ($field === null) {
            return !empty($this->errors);
        }
        return isset($this->errors[$field]);
    }
    
    public function getFirstError($field) {
        $errors = $this->getError($field);
        return !empty($errors) ? $errors[0] : null;
    }
    
    public function getAllErrorMessages() {
        $messages = [];
        foreach ($this->errors as $field => $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return $messages;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Clean data for database insertion
     */
    public static function clean($data) {
        if (is_array($data)) {
            return array_map([self::class, 'clean'], $data);
        }
        
        return trim($data);
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $maxSize = null, $allowedTypes = null) {
        $maxSize = $maxSize ?? Config::MAX_FILE_SIZE;
        $allowedTypes = $allowedTypes ?? Config::ALLOWED_FILE_TYPES;
        
        $errors = [];
        
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'No file uploaded or invalid file';
            return $errors;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error: ' . $file['error'];
            return $errors;
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size of ' . ($maxSize / 1024 / 1024) . 'MB';
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
        }
        
        // Additional security checks
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (isset($allowedMimes[$extension]) && $mimeType !== $allowedMimes[$extension]) {
            $errors[] = 'File type does not match its content';
        }
        
        return $errors;
    }
}
