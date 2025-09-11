<?php
/**
 * Notification Service for SAMPARK
 * Handles email and SMS notifications
 */

class NotificationService {
    
    private $db;
    private $emailEnabled;
    private $smsEnabled;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->emailEnabled = $this->getSetting('enable_email', true);
        $this->smsEnabled = $this->getSetting('enable_sms', false);
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
     * Send email
     */
    private function sendEmail($to, $subject, $bodyHtml, $bodyText = null) {
        try {
            // Use PHPMailer or similar library in production
            // For now, using basic PHP mail() function
            
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . Config::FROM_NAME . ' <' . Config::FROM_EMAIL . '>',
                'Reply-To: ' . Config::FROM_EMAIL,
                'X-Mailer: SAMPARK v' . Config::APP_VERSION
            ];
            
            $success = mail($to, $subject, $bodyHtml, implode("\r\n", $headers));
            
            return [
                'success' => $success,
                'error' => $success ? null : 'Failed to send email'
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
            'company_name' => $customer['company_name']
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
     * Send priority escalation notification
     */
    public function sendPriorityEscalated($complaintId, $customer, $newPriority, $recipients) {
        $data = [
            'complaint_id' => $complaintId,
            'customer_name' => $customer['name'],
            'priority' => ucfirst($newPriority),
            'escalation_time' => date('Y-m-d H:i:s')
        ];
        
        return $this->send('priority_escalated', $recipients, $data);
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
}
