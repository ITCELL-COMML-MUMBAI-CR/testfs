<?php

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/OnSiteNotificationService.php';
require_once __DIR__ . '/../utils/EmailService.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/EmailTemplateModel.php';

class TestController extends BaseController {

    private $userModel;
    private $customerModel;
    private $emailTemplateModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole(['superadmin']);
        $this->userModel = new UserModel();
        $this->customerModel = new CustomerModel();
        $this->emailTemplateModel = new EmailTemplateModel();
    }
    public function notifications() {
        $data = [
            'page_title' => 'Test Notifications',
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->session->getCSRFToken(),
            'user_types' => $this->userModel->getDistinct('role'),
            'divisions' => $this->userModel->getDistinct('division'),
            'zones' => $this->userModel->getDistinct('zone'),
            'departments' => $this->userModel->getDistinct('department'),
            'customers' => $this->customerModel->findAll([], 'name ASC'),
            'users' => $this->userModel->findAll([], 'name ASC'),
        ];

        $this->view('admin/testing/notifications', $data);
    }

    public function emails() {
        $data = [
            'page_title' => 'Test Emails',
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->session->getCSRFToken(),
            'user_types' => $this->userModel->getDistinct('role'),
            'divisions' => $this->userModel->getDistinct('division'),
            'zones' => $this->userModel->getDistinct('zone'),
            'departments' => $this->userModel->getDistinct('department'),
            'customers' => $this->customerModel->findAll([], 'name ASC'),
            'users' => $this->userModel->findAll([], 'name ASC'),
            'email_templates' => $this->emailTemplateModel->getActiveTemplates(),
        ];

        $this->view('admin/testing/emails', $data);
    }

    public function sendTestNotification() {
        $this->validateCSRF();

        $title = $_POST['title'];
        $message = $_POST['message'];
        $sendTo = $_POST['send_to'];

        $notificationModel = new NotificationModel();
        $recipients = [];

        switch ($sendTo) {
            case 'all_customers':
                $customers = $this->customerModel->findAll();
                foreach ($customers as $customer) {
                    $recipients[] = ['customer_id' => $customer['customer_id']];
                }
                break;
            case 'specific_customers':
                $customerIds = $_POST['specific_customers'] ?? [];
                foreach ($customerIds as $customerId) {
                    $recipients[] = ['customer_id' => $customerId];
                }
                break;
            case 'structured':
                $recipients = $this->getStructuredNotificationRecipients();
                break;
            case 'user_type':
                $users = $this->userModel->getUsersByRole($_POST['user_type']);
                foreach ($users as $user) {
                    if ($user['role'] !== 'superadmin') {
                        $recipients[] = ['user_id' => $user['id']];
                    }
                }
                break;
            case 'specific_users':
                $userIds = $_POST['specific_users'] ?? [];
                foreach ($userIds as $userId) {
                    $recipients[] = ['user_id' => $userId];
                }
                break;
        }

        foreach ($recipients as $recipient) {
            $notificationData = [
                'title' => $title,
                'message' => $message,
                'type' => 'manual',
            ];
            if (isset($recipient['user_id'])) {
                $notificationData['user_id'] = $recipient['user_id'];
            } else {
                $notificationData['customer_id'] = $recipient['customer_id'];
            }
            $notificationModel->createNotification($notificationData);
        }

        $this->json(['success' => true, 'message' => 'Sent ' . count($recipients) . ' notifications.']);
    }

    public function sendTestEmail() {
        $this->validateCSRF();

        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $sendTo = $_POST['send_to'];

        $emailService = new EmailService();
        $recipients = [];

        switch ($sendTo) {
            case 'all_customers':
                $recipients = $this->customerModel->findAll();
                break;
            case 'specific_customers':
                $customerIds = $_POST['specific_customers'] ?? [];
                $recipients = $this->customerModel->findAll(['customer_id' => $customerIds]);
                break;
            case 'all_users':
                $recipients = $this->userModel->findAll();
                break;
            case 'user_type':
                $recipients = $this->userModel->getUsersByRole($_POST['user_type']);
                break;
            case 'division':
                $conditions = ['status' => 'active'];
                if (!empty($_POST['division'])) $conditions['division'] = $_POST['division'];
                if (!empty($_POST['zone'])) $conditions['zone'] = $_POST['zone'];
                if (!empty($_POST['department'])) $conditions['department'] = $_POST['department'];
                $recipients = $this->userModel->findAll($conditions);
                break;
            case 'specific_users':
                $userIds = $_POST['specific_users'] ?? [];
                $recipients = $this->userModel->findAll(['id' => $userIds]);
                break;
        }

        $sentCount = 0;
        foreach ($recipients as $recipient) {
            if (isset($recipient['email']) && !empty($recipient['email'])) {
                if ($sendTo === 'all_users' || $sendTo === 'user_type' || $sendTo === 'division' || $sendTo === 'specific_users') {
                    if ($recipient['role'] === 'superadmin') continue;
                }
                $emailService->sendEmail($recipient['email'], $subject, $body, true);
                $sentCount++;
            }
        }

        $this->json(['success' => true, 'message' => 'Sent ' . $sentCount . ' emails.']);
    }

    public function sendTemplateEmail() {
        $this->validateCSRF();

        $templateCode = $_POST['template_code'];
        $testEmail = $_POST['test_email'];

        if (empty($templateCode) || empty($testEmail)) {
            $this->json(['success' => false, 'message' => 'Template and email are required.']);
            return;
        }

        try {
            // Sample data for template testing
            $sampleData = $this->getSampleTemplateData($templateCode);

            // Process template
            $processedTemplate = $this->emailTemplateModel->processTemplate($templateCode, $sampleData);

            // Send email
            $emailService = new EmailService();
            $result = $emailService->sendEmail(
                $testEmail,
                $processedTemplate['subject'],
                $processedTemplate['body_html'],
                true
            );

            if ($result['success']) {
                $this->json(['success' => true, 'message' => "Template email sent successfully to {$testEmail}"]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to send email: ' . $result['error']]);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function previewTemplate() {
        $templateCode = $_GET['template_code'] ?? '';

        if (empty($templateCode)) {
            $this->json(['success' => false, 'message' => 'Template code required.']);
            return;
        }

        try {
            // Get sample data for template
            $sampleData = $this->getSampleTemplateData($templateCode);

            // Process template
            $processedTemplate = $this->emailTemplateModel->processTemplate($templateCode, $sampleData);

            $this->json([
                'success' => true,
                'preview' => [
                    'subject' => $processedTemplate['subject'],
                    'body_html' => $processedTemplate['body_html'],
                    'body_text' => $processedTemplate['body_text'],
                    'sample_data' => $sampleData
                ]
            ]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function getSampleTemplateData($templateCode) {
        $baseData = [
            'app_name' => 'SAMPARK',
            'customer_name' => 'John Doe',
            'company_name' => 'Sample Company Ltd.',
            'email' => 'john.doe@example.com',
            'login_url' => Config::getAppUrl() . '/login',
            'view_url' => Config::getAppUrl() . '/customer/ticket-details?id=202501010001',
            'created_date' => date('Y-m-d H:i:s'),
            'division' => 'Central Division'
        ];

        switch ($templateCode) {
            case 'ticket_created':
                return array_merge($baseData, [
                    'complaint_id' => '202501010001',
                ]);

            case 'registration_approved':
                return $baseData;

            case 'awaiting_info':
                return array_merge($baseData, [
                    'complaint_id' => '202501010001',
                    'additional_info_request' => 'Please provide more details about the issue you are experiencing, including any error messages and screenshots if available.',
                ]);

            case 'awaiting_feedback':
                return array_merge($baseData, [
                    'complaint_id' => '202501010001',
                    'recent_update' => 'We have resolved the network connectivity issue by updating your service configuration. Please test your connection and confirm if everything is working properly.',
                ]);

            default:
                return $baseData;
        }
    }

    /**
     * Get recipients for structured notifications based on zone/division/department hierarchy
     */
    private function getStructuredNotificationRecipients() {
        $zones = $_POST['structured_zone'] ?? [];
        $divisions = $_POST['structured_division'] ?? [];
        $departments = $_POST['structured_department'] ?? [];
        $userTypes = $_POST['structured_user_types'] ?? [];

        if (empty($userTypes)) {
            return [];
        }

        $recipients = [];
        $conditions = ['status' => 'active'];
        $conditionParams = [];

        // Build conditions based on hierarchy
        if (!empty($zones) && !in_array('', $zones)) {
            $zonePlaceholders = str_repeat('?,', count($zones) - 1) . '?';
            $conditions[] = "zone IN ($zonePlaceholders)";
            $conditionParams = array_merge($conditionParams, $zones);
        }

        if (!empty($divisions) && !in_array('', $divisions)) {
            $divisionPlaceholders = str_repeat('?,', count($divisions) - 1) . '?';
            $conditions[] = "division IN ($divisionPlaceholders)";
            $conditionParams = array_merge($conditionParams, $divisions);
        }

        if (!empty($departments) && !in_array('', $departments)) {
            $departmentPlaceholders = str_repeat('?,', count($departments) - 1) . '?';
            $conditions[] = "department IN ($departmentPlaceholders)";
            $conditionParams = array_merge($conditionParams, $departments);
        }

        // Build user type conditions
        $userTypePlaceholders = str_repeat('?,', count($userTypes) - 1) . '?';
        $conditions[] = "role IN ($userTypePlaceholders)";
        $conditionParams = array_merge($conditionParams, $userTypes);

        // Exclude superadmin
        $conditions[] = "role != 'superadmin'";

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT id FROM users WHERE $whereClause";

        $users = $this->db->fetchAll($sql, $conditionParams);

        foreach ($users as $user) {
            $recipients[] = ['user_id' => $user['id']];
        }

        return $recipients;
    }
}