<?php
/**
 * Notification Service for SAMPARK
 * Handles email and SMS notifications
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Config.php';

class NotificationService {

    private $db;
    private $emailEnabled;
    private $smsEnabled;
    private $emailService;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->emailEnabled = $this->getSetting('enable_email', true);
        $this->smsEnabled = $this->getSetting('enable_sms', false);

        // Include EmailService if not already loaded
        if (!class_exists('EmailService')) {
            require_once __DIR__ . '/EmailService.php';
        }
        $this->emailService = new EmailService();
    }
    
    /**
     * Send notification
     */
    public function send($type, $recipients, $data) {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $result = $this->sendToRecipient($type, $recipient, $data);
            $results[] = $result;
        }
        
        return $results;
    }
    
    /**
     * Send notification to single recipient
     */
    private function sendToRecipient($type, $recipient, $data) {
        $result = [
            'recipient' => $recipient,
            'email_sent' => false,
            'sms_sent' => false,
            'errors' => []
        ];
        
        try {
            // Get template
            $template = $this->getTemplate($type);
            if (!$template) {
                $result['errors'][] = "Template not found for type: {$type}";
                return $result;
            }
            
            // Process template
            $processedTemplate = $this->processTemplate($template, $data);
            
            // Send email
            if ($this->emailEnabled && !empty($recipient['email'])) {
                $emailResult = $this->sendEmail(
                    $recipient['email'],
                    $processedTemplate['subject'],
                    $processedTemplate['body_html'],
                    $processedTemplate['body_text']
                );
                
                $result['email_sent'] = $emailResult['success'];
                if (!$emailResult['success']) {
                    $result['errors'][] = "Email error: " . $emailResult['error'];
                }
            }
            
            // Send SMS
            if ($this->smsEnabled && !empty($recipient['mobile']) && !empty($processedTemplate['sms_text'])) {
                $smsResult = $this->sendSMS(
                    $recipient['mobile'],
                    $processedTemplate['sms_text']
                );
                
                $result['sms_sent'] = $smsResult['success'];
                if (!$smsResult['success']) {
                    $result['errors'][] = "SMS error: " . $smsResult['error'];
                }
            }
            
            // Log notification
            $this->logNotification($type, $recipient, $result);
            
        } catch (Exception $e) {
            $result['errors'][] = "Notification error: " . $e->getMessage();
            error_log("Notification error: " . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Send email using SMTP service
     */
    private function sendEmail($to, $subject, $bodyHtml, $bodyText = null) {
        try {
            $result = $this->emailService->sendEmail($to, $subject, $bodyHtml, true);

            return [
                'success' => $result['success'],
                'error' => $result['error']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send SMS
     */
    private function sendSMS($mobile, $message) {
        try {
            // Integration with SMS gateway
            // This would be implemented based on the SMS service provider
            
            if (!$this->smsEnabled) {
                return [
                    'success' => false,
                    'error' => 'SMS service is disabled'
                ];
            }
            
            // Example implementation (replace with actual SMS API)
            $apiUrl = Config::SMS_API_URL;
            $apiKey = Config::SMS_API_KEY;
            $senderId = Config::SMS_SENDER_ID;
            
            if (empty($apiUrl) || empty($apiKey)) {
                return [
                    'success' => false,
                    'error' => 'SMS configuration incomplete'
                ];
            }
            
            $postData = [
                'apikey' => $apiKey,
                'sender' => $senderId,
                'mobile' => $mobile,
                'message' => $message,
                'format' => 'json'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $responseData = json_decode($response, true);
                if ($responseData && isset($responseData['status']) && $responseData['status'] === 'success') {
                    return ['success' => true, 'error' => null];
                } else {
                    return ['success' => false, 'error' => 'SMS API error: ' . ($responseData['message'] ?? 'Unknown error')];
                }
            } else {
                return ['success' => false, 'error' => "SMS API HTTP error: {$httpCode}"];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get email template
     */
    private function getTemplate($templateCode) {
        $sql = "SELECT * FROM email_templates WHERE template_code = ? AND is_active = 1";
        return $this->db->fetch($sql, [$templateCode]);
    }
    
    /**
     * Process template with data
     */
    private function processTemplate($template, $data) {
        $subject = $this->replaceVariables($template['subject'], $data);
        $bodyHtml = $this->replaceVariables($template['body_html'], $data);
        $bodyText = $template['body_text'] ? $this->replaceVariables($template['body_text'], $data) : null;
        
        // Generate SMS text from subject and first part of body
        $smsText = $this->generateSmsText($subject, $bodyHtml, $data);
        
        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'sms_text' => $smsText
        ];
    }
    
    /**
     * Replace template variables
     */
    private function replaceVariables($content, $data) {
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $content = str_replace($placeholder, $value, $content);
        }
        
        // Replace common system variables
        $systemVars = [
            '{{app_name}}' => Config::APP_NAME,
            '{{app_url}}' => Config::getAppUrl(),
            '{{current_date}}' => date('Y-m-d'),
            '{{current_time}}' => date('H:i:s'),
            '{{current_year}}' => date('Y')
        ];
        
        foreach ($systemVars as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Generate SMS text from email content
     */
    private function generateSmsText($subject, $bodyHtml, $data) {
        // Strip HTML tags and get plain text
        $plainText = strip_tags($bodyHtml);
        
        // Create short SMS message
        $smsText = $subject;
        
        if (isset($data['complaint_id'])) {
            $smsText .= " | Ticket: #{$data['complaint_id']}";
        }
        
        if (isset($data['status'])) {
            $smsText .= " | Status: " . ucfirst($data['status']);
        }
        
        $smsText .= " | Check " . Config::APP_NAME . " portal for details.";
        
        // Limit to 160 characters for SMS
        if (strlen($smsText) > 160) {
            $smsText = substr($smsText, 0, 157) . '...';
        }
        
        return $smsText;
    }
    
    /**
     * Log notification
     */
    private function logNotification($type, $recipient, $result) {
        try {
            $sql = "INSERT INTO notifications (
                user_id, customer_id, title, message, type, 
                complaint_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $recipient['user_id'] ?? null,
                $recipient['customer_id'] ?? null,
                $type . ' notification',
                'Notification sent via ' . 
                    ($result['email_sent'] ? 'email' : '') . 
                    ($result['email_sent'] && $result['sms_sent'] ? ' and ' : '') .
                    ($result['sms_sent'] ? 'SMS' : ''),
                'info',
                $recipient['complaint_id'] ?? null
            ];
            
            $this->db->query($sql, $params);
            
        } catch (Exception $e) {
            error_log("Notification logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Send ticket created notification
     */
    public function sendTicketCreated($complaintId, $customer, $assignedUser = null) {
        $data = [
            'complaint_id' => $complaintId,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'company_name' => $customer['company_name'],
            'view_url' => Config::getAppUrl() . '/customer/ticket/' . $complaintId
        ];

        $recipients = [
            [
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile']
            ]
        ];

        // Add assigned user to recipients
        if ($assignedUser) {
            $recipients[] = [
                'user_id' => $assignedUser['user_id'],
                'email' => $assignedUser['email'],
                'mobile' => $assignedUser['mobile'] ?? null
            ];
        }

        return $this->send('ticket_created', $recipients, $data);
    }

    /**
     * Send customer signup approved notification
     */
    public function sendSignupApproved($customer) {
        $data = [
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'company_name' => $customer['company_name'],
            'login_url' => Config::getAppUrl() . '/login'
        ];

        $recipients = [
            [
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile']
            ]
        ];

        return $this->send('signup_approved', $recipients, $data);
    }

    /**
     * Send ticket status awaiting info notification
     */
    public function sendTicketAwaitingInfo($complaintId, $customer, $message = '') {
        $data = [
            'complaint_id' => $complaintId,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'company_name' => $customer['company_name'],
            'message' => $message,
            'view_url' => Config::getAppUrl() . '/customer/ticket/' . $complaintId,
            'login_url' => Config::getAppUrl() . '/login'
        ];

        $recipients = [
            [
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile']
            ]
        ];

        return $this->send('ticket_awaiting_info', $recipients, $data);
    }

    /**
     * Send ticket status awaiting feedback notification
     */
    public function sendTicketAwaitingFeedback($complaintId, $customer, $message = '') {
        $data = [
            'complaint_id' => $complaintId,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'company_name' => $customer['company_name'],
            'message' => $message,
            'view_url' => Config::getAppUrl() . '/customer/ticket/' . $complaintId,
            'login_url' => Config::getAppUrl() . '/login'
        ];

        $recipients = [
            [
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile']
            ]
        ];

        return $this->send('ticket_awaiting_feedback', $recipients, $data);
    }
    
    /**
     * Send ticket assigned notification
     */
    public function sendTicketAssigned($complaintId, $customer, $assignedUser) {
        $data = [
            'complaint_id' => $complaintId,
            'customer_name' => $customer['name'],
            'assigned_user' => $assignedUser['name'],
            'priority' => $customer['priority'] ?? 'normal'
        ];
        
        $recipients = [
            [
                'user_id' => $assignedUser['user_id'],
                'email' => $assignedUser['email'],
                'mobile' => $assignedUser['mobile'] ?? null,
                'complaint_id' => $complaintId
            ]
        ];
        
        return $this->send('ticket_assigned', $recipients, $data);
    }
    
    /**
     * Send priority escalation notification with RBAC
     */
    public function sendPriorityEscalated($complaintId, $customer, $newPriority, $oldPriority = null, $escalationReason = 'Automatic escalation') {
        // Get ticket details
        $ticket = $this->getTicketDetails($complaintId);
        if (!$ticket) {
            return ['success' => false, 'error' => 'Ticket not found'];
        }

        $data = [
            'ticket_id' => $complaintId,
            'customer_name' => $customer['name'] ?? $ticket['customer_name'],
            'priority' => ucfirst($newPriority),
            'old_priority' => $oldPriority ? ucfirst($oldPriority) : 'Unknown',
            'escalation_time' => date('Y-m-d H:i:s'),
            'escalation_reason' => $escalationReason,
            'division' => $ticket['division'] ?? 'N/A',
            'department' => $ticket['assigned_to_department'] ?? 'N/A'
        ];

        try {
            // Create persistent browser notifications based on RBAC
            $this->createPriorityEscalationNotifications($complaintId, $newPriority, $data, $ticket);

            // Send email/SMS notifications to relevant users
            $recipients = $this->getEscalationRecipients($ticket, $newPriority);
            $results = $this->send('priority_escalated', $recipients, $data);

            // Check if any notifications were sent successfully
            $successCount = 0;
            $errorCount = 0;
            foreach ($results as $result) {
                if ($result['email_sent'] || $result['sms_sent']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            return [
                'success' => true,
                'message' => "Priority escalation notifications processed",
                'notifications_sent' => $successCount,
                'errors' => $errorCount,
                'details' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to send priority escalation notifications: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send bulk notifications
     */
    public function sendBulk($templateCode, $recipients, $commonData = []) {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $recipientData = array_merge($commonData, $recipient['data'] ?? []);
            $result = $this->sendToRecipient($templateCode, $recipient, $recipientData);
            $results[] = $result;
            
            // Add small delay to prevent overwhelming email server
            usleep(100000); // 0.1 second delay
        }
        
        return $results;
    }
    
    /**
     * Get notification history
     */
    public function getNotificationHistory($userId, $userType, $limit = 50) {
        $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';
        
        $sql = "SELECT * FROM notifications 
                WHERE {$whereClause}
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId, $userType) {
        $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';
        
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND {$whereClause}";
        
        return $this->db->query($sql, [$notificationId, $userId]);
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount($userId, $userType) {
        $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';
        
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE {$whereClause} AND is_read = 0";
        
        $result = $this->db->fetch($sql, [$userId]);
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Create priority escalation notifications with RBAC
     */
    private function createPriorityEscalationNotifications($complaintId, $priority, $data, $ticket) {
        require_once __DIR__ . '/../models/NotificationModel.php';
        $notificationModel = new NotificationModel();

        // Get all users who should receive notifications based on RBAC
        $recipients = $this->getEscalationRecipients($ticket, $priority);

        foreach ($recipients as $recipient) {
            // Generate role-appropriate URL
            $actionUrl = $this->generateTicketUrl($complaintId, $recipient['user_type']);

            $notificationData = [
                'user_id' => $recipient['user_id'] ?? null,
                'user_type' => $recipient['user_type'],
                'title' => "Ticket #{$complaintId} Priority Escalated",
                'message' => "Ticket #{$complaintId} from {$data['customer_name']} has been escalated to {$priority} priority.",
                'type' => 'priority_escalated',
                'priority' => $priority,
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => $actionUrl,
                'metadata' => json_encode([
                    'old_priority' => $data['old_priority'],
                    'escalation_reason' => $data['escalation_reason'],
                    'division' => $data['division'],
                    'department' => $data['department']
                ])
            ];

            // Set customer_id if recipient is customer
            if ($recipient['user_type'] === 'customer') {
                $notificationData['customer_id'] = $recipient['customer_id'];
            }

            $notificationModel->createNotification($notificationData);
        }
    }

    /**
     * Get escalation recipients based on RBAC and priority level
     */
    private function getEscalationRecipients($ticket, $priority) {
        $recipients = [];

        // Critical priority notifications go to admin/superadmin
        if ($priority === 'critical') {
            $adminUsers = $this->db->fetchAll(
                "SELECT id as user_id, role as user_type, email, mobile, name
                 FROM users
                 WHERE role IN ('admin', 'superadmin')
                 AND status = 'active'"
            );
            $recipients = array_merge($recipients, $adminUsers);
        }

        // High priority notifications go to controllers and admins
        if (in_array($priority, ['high', 'critical'])) {
            $controllerUsers = $this->db->fetchAll(
                "SELECT id as user_id, role as user_type, email, mobile, name
                 FROM users
                 WHERE role IN ('controller', 'controller_nodal')
                 AND status = 'active'
                 AND (division = ? OR role = 'controller_nodal')",
                [$ticket['division'] ?? '']
            );
            $recipients = array_merge($recipients, $controllerUsers);
        }

        // Always notify assigned user if exists
        if (!empty($ticket['assigned_to_user_id'])) {
            $assignedUser = $this->db->fetch(
                "SELECT id as user_id, role as user_type, email, mobile, name
                 FROM users
                 WHERE id = ? AND status = 'active'",
                [$ticket['assigned_to_user_id']]
            );
            if ($assignedUser) {
                $recipients[] = $assignedUser;
            }
        }

        // Always notify the customer
        if (!empty($ticket['customer_id'])) {
            $customer = $this->db->fetch(
                "SELECT customer_id, 'customer' as user_type, email, mobile, name
                 FROM customers
                 WHERE customer_id = ? AND status = 'active'",
                [$ticket['customer_id']]
            );
            if ($customer) {
                $recipients[] = $customer;
            }
        }

        return array_unique($recipients, SORT_REGULAR);
    }

    /**
     * Get ticket details for notifications
     */
    private function getTicketDetails($complaintId) {
        return $this->db->fetch(
            "SELECT c.*, cust.name as customer_name, cust.email as customer_email
             FROM complaints c
             LEFT JOIN customers cust ON c.customer_id = cust.customer_id
             WHERE c.complaint_id = ?",
            [$complaintId]
        );
    }

    /**
     * Get user notifications with RBAC filtering
     */
    public function getUserNotifications($userId, $userType, $limit = 20, $unreadOnly = false) {
        require_once __DIR__ . '/../models/NotificationModel.php';
        $notificationModel = new NotificationModel();

        return $notificationModel->getUserNotifications($userId, $userType, $limit, $unreadOnly);
    }

    /**
     * Mark notification as dismissed (different from read)
     */
    public function dismissNotification($notificationId, $userId, $userType) {
        $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';

        $sql = "UPDATE notifications
                SET dismissed_at = NOW(), updated_at = NOW()
                WHERE id = ? AND {$whereClause} AND dismissed_at IS NULL";

        return $this->db->query($sql, [$notificationId, $userId]);
    }

    /**
     * Get notification count including dismissed status
     */
    public function getNotificationCounts($userId, $userType) {
        try {
            // Check if enhanced columns exist
            $hasEnhancedColumns = $this->checkEnhancedColumns();

            $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';

            if ($hasEnhancedColumns) {
                // Use enhanced query with new columns
                $sql = "SELECT COUNT(*) as total, " .
                       "COUNT(CASE WHEN is_read = 0 AND dismissed_at IS NULL THEN 1 END) as unread, " .
                       "COUNT(CASE WHEN dismissed_at IS NULL THEN 1 END) as active, " .
                       "COUNT(CASE WHEN priority IN ('high', 'critical', 'urgent') AND dismissed_at IS NULL THEN 1 END) as priority_high " .
                       "FROM notifications " .
                       "WHERE {$whereClause} " .
                       "AND (expires_at IS NULL OR expires_at > NOW())";
            } else {
                // Use basic query for backward compatibility
                $sql = "SELECT COUNT(*) as total, " .
                       "COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread, " .
                       "COUNT(*) as active, " .
                       "0 as priority_high " .
                       "FROM notifications " .
                       "WHERE {$whereClause}";
            }

            $result = $this->db->fetch($sql, [$userId]);

            // Map priority_high back to high_priority for consistency
            if ($result && isset($result['priority_high'])) {
                $result['high_priority'] = $result['priority_high'];
                unset($result['priority_high']);
            }

            return $result ?: ['total' => 0, 'unread' => 0, 'active' => 0, 'high_priority' => 0];

        } catch (Exception $e) {
            error_log("Error getting notification counts: " . $e->getMessage());
            return ['total' => 0, 'unread' => 0, 'active' => 0, 'high_priority' => 0];
        }
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpiredNotifications() {
        $sql = "DELETE FROM notifications
                WHERE expires_at IS NOT NULL
                AND expires_at < NOW()";

        return $this->db->query($sql);
    }

    /**
     * Check if enhanced notification columns exist
     */
    private function checkEnhancedColumns() {
        try {
            // Try to query for the priority column - if it exists, we have enhanced columns
            $result = $this->db->fetch("SHOW COLUMNS FROM notifications LIKE 'priority'");
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get system setting
     */
    private function getSetting($key, $default = null) {
        try {
            $sql = "SELECT setting_value FROM system_settings WHERE setting_key = ?";
            $result = $this->db->fetch($sql, [$key]);

            if ($result) {
                return $result['setting_value'] === '1' ? true : ($result['setting_value'] === '0' ? false : $result['setting_value']);
            }

            return $default;

        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * Generate appropriate ticket URL based on user type
     */
    private function generateTicketUrl($ticketId, $userType) {
        $baseUrl = Config::getAppUrl();

        switch ($userType) {
            case 'customer':
                return $baseUrl . '/customer/tickets/' . $ticketId;
            case 'controller':
            case 'controller_nodal':
                return $baseUrl . '/controller/tickets/' . $ticketId;
            case 'admin':
            case 'superadmin':
                return $baseUrl . '/admin/tickets/' . $ticketId . '/view';
            default:
                return $baseUrl . '/customer/tickets/' . $ticketId;
        }
    }
}
