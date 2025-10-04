<?php
/**
 * Customer Email Service for SAMPARK
 * Centralized service to handle ALL customer email communications
 *
 * Customer emails are sent ONLY in these scenarios:
 * 1. Ticket created successfully
 * 2. Ticket reverted for more information
 * 3. Ticket solved and feedback pending
 * 4. Customer registration
 * 5. Customer registration approved by admin
 *
 * NO emails are sent to users (Controllers, Admins, etc.)
 */

require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../config/database.php';

class CustomerEmailService {

    private $emailService;
    private $db;

    public function __construct() {
        $this->emailService = new EmailService();
        $this->db = Database::getInstance();
    }

    /**
     * Send ticket created email to customer
     */
    public function sendTicketCreated($ticketId, $customerEmail, $customerName, $companyName = '') {
        $subject = "Ticket #{$ticketId} Created Successfully";

        $viewUrl = Config::getAppUrl() . '/login?redirect=' . urlencode('/customer/tickets/' . $ticketId);

        $body = $this->getEmailTemplate('ticket_created', [
            'ticket_id' => $ticketId,
            'customer_name' => $customerName,
            'company_name' => $companyName,
            'view_url' => $viewUrl,
            'app_name' => Config::APP_NAME,
            'app_url' => Config::getAppUrl()
        ]);

        return $this->emailService->sendEmail($customerEmail, $subject, $body, true);
    }

    /**
     * Send ticket reverted/more info needed email to customer
     */
    public function sendTicketReverted($ticketId, $customerEmail, $customerName, $ticketSubject = '', $companyName = '') {
        $subject = "Additional Information Required - Ticket #{$ticketId}";

        $viewUrl = Config::getAppUrl() . '/login?redirect=' . urlencode('/customer/tickets/' . $ticketId);

        $body = $this->getEmailTemplate('ticket_reverted', [
            'ticket_id' => $ticketId,
            'customer_name' => $customerName,
            'company_name' => $companyName,
            'ticket_subject' => $ticketSubject,
            'view_url' => $viewUrl,
            'app_name' => Config::APP_NAME,
            'app_url' => Config::getAppUrl()
        ]);

        return $this->emailService->sendEmail($customerEmail, $subject, $body, true);
    }

    /**
     * Send feedback requested email to customer
     */
    public function sendFeedbackRequested($ticketId, $customerEmail, $customerName, $ticketSubject = '', $companyName = '') {
        $subject = "Ticket #{$ticketId} - Your Feedback Required";

        $viewUrl = Config::getAppUrl() . '/login?redirect=' . urlencode('/customer/tickets/' . $ticketId);

        $body = $this->getEmailTemplate('ticket_feedback', [
            'ticket_id' => $ticketId,
            'customer_name' => $customerName,
            'company_name' => $companyName,
            'ticket_subject' => $ticketSubject,
            'view_url' => $viewUrl,
            'app_name' => Config::APP_NAME,
            'app_url' => Config::getAppUrl()
        ]);

        return $this->emailService->sendEmail($customerEmail, $subject, $body, true);
    }

    /**
     * Send registration confirmation email to customer
     */
    public function sendRegistrationReceived($customerId, $customerEmail, $customerName, $companyName = '', $division = '') {
        $subject = "Registration Received - " . Config::APP_NAME;

        $body = $this->getEmailTemplate('customer_registration', [
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'company_name' => $companyName,
            'division' => $division,
            'app_name' => Config::APP_NAME,
            'app_url' => Config::getAppUrl()
        ]);

        return $this->emailService->sendEmail($customerEmail, $subject, $body, true);
    }

    /**
     * Send registration approved email to customer
     */
    public function sendRegistrationApproved($customerId, $customerEmail, $customerName, $companyName = '', $division = '') {
        $subject = "Account Approved - Welcome to " . Config::APP_NAME;

        $loginUrl = Config::getAppUrl() . '/login';

        $body = $this->getEmailTemplate('registration_approved', [
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'company_name' => $companyName,
            'division' => $division,
            'login_url' => $loginUrl,
            'app_name' => Config::APP_NAME,
            'app_url' => Config::getAppUrl()
        ]);

        return $this->emailService->sendEmail($customerEmail, $subject, $body, true);
    }

    /**
     * Get email template with consistent styling
     * All templates follow the same design theme and requirements
     */
    private function getEmailTemplate($templateType, $data) {
        $templates = [
            'ticket_created' => $this->ticketCreatedTemplate($data),
            'ticket_reverted' => $this->ticketRevertedTemplate($data),
            'ticket_feedback' => $this->ticketFeedbackTemplate($data),
            'customer_registration' => $this->customerRegistrationTemplate($data),
            'registration_approved' => $this->registrationApprovedTemplate($data)
        ];

        return $templates[$templateType] ?? '';
    }

    /**
     * Ticket Created Template
     * Follows requirements: No user/dept names, no ETA, includes view button, basic ticket info
     * Uses professional pastel colors
     */
    private function ticketCreatedTemplate($data) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">' . htmlspecialchars($data['app_name']) . '</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.95;">Support & Mediation Portal</p>
        </div>

        <!-- Success Banner -->
        <div style="background: linear-gradient(135deg, #86efac 0%, #a7f3d0 100%); color: #065f46; padding: 25px 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Ticket Created Successfully</h2>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Your support request has been received</p>
        </div>

        <!-- Main Content -->
        <div style="padding: 30px 20px;">
            <h3 style="color: #059669; margin: 0 0 20px 0; font-size: 20px;">Dear ' . htmlspecialchars($data['customer_name']) . ',</h3>

            <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin: 0 0 20px 0;">
                Thank you for contacting us. Your support ticket has been created successfully and is being processed.
            </p>

            <!-- Ticket Details Box -->
            <div style="background-color: #ecfdf5; border-left: 4px solid #86efac; padding: 20px; margin: 25px 0; border-radius: 6px;">
                <h4 style="margin: 0 0 15px 0; color: #047857; font-size: 18px;">Ticket Details</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Ticket ID:</td>
                        <td style="padding: 8px 0; color: #059669; font-weight: 600; font-size: 16px;">#' . htmlspecialchars($data['ticket_id']) . '</td>
                    </tr>' .
                    (!empty($data['company_name']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Company:</td>
                        <td style="padding: 8px 0; color: #1f2937;">' . htmlspecialchars($data['company_name']) . '</td>
                    </tr>' : '') . '
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Status:</td>
                        <td style="padding: 8px 0;"><span style="background: #86efac; color: #065f46; padding: 4px 12px; border-radius: 4px; font-size: 13px; font-weight: 600;">ACTIVE</span></td>
                    </tr>
                </table>
            </div>

            <!-- What Happens Next Box -->
            <div style="background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 20px; margin: 25px 0;">
                <h4 style="margin: 0 0 15px 0; color: #047857;">What happens next?</h4>
                <ul style="margin: 0; padding-left: 20px; color: #065f46; line-height: 1.8;">
                    <li>Your ticket will be reviewed and processed</li>
                    <li>You will receive email updates on any changes</li>
                    <li>You can track your ticket status online anytime</li>
                    <li>Additional information may be requested if needed</li>
                </ul>
            </div>

            <!-- View Ticket Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($data['view_url']) . '" style="display: inline-block; background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 15px 35px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(96,165,250,0.3);">View Ticket Details</a>
            </div>

            <!-- Important Note -->
            <div style="background-color: #fef9c3; border: 1px solid #fde68a; border-radius: 8px; padding: 15px; margin: 25px 0;">
                <p style="margin: 0; color: #78350f; font-size: 14px;">
                    <strong>Note:</strong> Please save this email for your records. You can use Ticket ID <strong>#' . htmlspecialchars($data['ticket_id']) . '</strong> to track your ticket.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #6b7280; color: white; text-align: center; padding: 25px 20px; font-size: 13px;">
            <p style="margin: 0 0 10px 0;"><strong>' . htmlspecialchars($data['app_name']) . ' Support Team</strong></p>
            <p style="margin: 0; opacity: 0.9;">This is an automated message. Please do not reply to this email.</p>
            <p style="margin: 10px 0 0 0; opacity: 0.75;">© ' . date('Y') . ' ' . htmlspecialchars($data['app_name']) . '. All rights reserved.</p>
        </div>

    </div>
</body>
</html>';
    }

    /**
     * Ticket Reverted Template
     * Follows requirements: No user/dept names, no ETA, includes view button, basic ticket info
     * Uses professional pastel colors
     */
    private function ticketRevertedTemplate($data) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">' . htmlspecialchars($data['app_name']) . '</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.95;">Support & Mediation Portal</p>
        </div>

        <!-- Alert Banner -->
        <div style="background: linear-gradient(135deg, #fca5a5 0%, #fecaca 100%); color: #991b1b; padding: 25px 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Additional Information Required</h2>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Action needed for your ticket</p>
        </div>

        <!-- Main Content -->
        <div style="padding: 30px 20px;">
            <h3 style="color: #dc2626; margin: 0 0 20px 0; font-size: 20px;">Dear ' . htmlspecialchars($data['customer_name']) . ',</h3>

            <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin: 0 0 20px 0;">
                We need additional information to process your support ticket. Please review your ticket and provide the requested details.
            </p>

            <!-- Ticket Details Box -->
            <div style="background-color: #fef2f2; border-left: 4px solid #fca5a5; padding: 20px; margin: 25px 0; border-radius: 6px;">
                <h4 style="margin: 0 0 15px 0; color: #dc2626; font-size: 18px;">Ticket Information</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Ticket ID:</td>
                        <td style="padding: 8px 0; color: #dc2626; font-weight: bold; font-size: 16px;">#' . htmlspecialchars($data['ticket_id']) . '</td>
                    </tr>' .
                    (!empty($data['ticket_subject']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Subject:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['ticket_subject']) . '</td>
                    </tr>' : '') .
                    (!empty($data['company_name']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Company:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['company_name']) . '</td>
                    </tr>' : '') . '
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Status:</td>
                        <td style="padding: 8px 0;"><span style="background: #fca5a5; color: #991b1b; padding: 4px 12px; border-radius: 4px; font-size: 13px; font-weight: 600;">AWAITING INFO</span></td>
                    </tr>
                </table>
            </div>

            <!-- Action Required Box -->
            <div style="background-color: #fef9c3; border: 1px solid #fde68a; border-radius: 8px; padding: 20px; margin: 25px 0;">
                <h4 style="margin: 0 0 15px 0; color: #b45309;">Action Required</h4>
                <p style="margin: 0; color: #78350f; line-height: 1.6;">
                    Please login to your account to view the information request and provide the necessary details to help us process your ticket.
                </p>
            </div>

            <!-- View Ticket Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($data['view_url']) . '" style="display: inline-block; background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 15px 35px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(96,165,250,0.3);">View Ticket & Respond</a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #6b7280; color: white; text-align: center; padding: 25px 20px; font-size: 13px;">
            <p style="margin: 0 0 10px 0;"><strong>' . htmlspecialchars($data['app_name']) . ' Support Team</strong></p>
            <p style="margin: 0; opacity: 0.9;">This is an automated message. Please do not reply to this email.</p>
            <p style="margin: 10px 0 0 0; opacity: 0.75;">© ' . date('Y') . ' ' . htmlspecialchars($data['app_name']) . '. All rights reserved.</p>
        </div>

    </div>
</body>
</html>';
    }

    /**
     * Ticket Feedback Template
     * Follows requirements: No user/dept names, no ETA, includes view button, basic ticket info
     * Uses professional pastel colors
     */
    private function ticketFeedbackTemplate($data) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">' . htmlspecialchars($data['app_name']) . '</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.95;">Support & Mediation Portal</p>
        </div>

        <!-- Success Banner -->
        <div style="background: linear-gradient(135deg, #c4b5fd 0%, #ddd6fe 100%); color: #5b21b6; padding: 25px 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Your Feedback is Important</h2>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Please rate your experience</p>
        </div>

        <!-- Main Content -->
        <div style="padding: 30px 20px;">
            <h3 style="color: #7c3aed; margin: 0 0 20px 0; font-size: 20px;">Dear ' . htmlspecialchars($data['customer_name']) . ',</h3>

            <p style="font-size: 16px; line-height: 1.6; color: #374151; margin: 0 0 20px 0;">
                Your ticket has been processed. We would appreciate your feedback to help us improve our service quality.
            </p>

            <!-- Ticket Details Box -->
            <div style="background-color: #faf5ff; border-left: 4px solid #c4b5fd; padding: 20px; margin: 25px 0; border-radius: 6px;">
                <h4 style="margin: 0 0 15px 0; color: #7c3aed; font-size: 18px;">Ticket Information</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Ticket ID:</td>
                        <td style="padding: 8px 0; color: #7c3aed; font-weight: 600; font-size: 16px;">#' . htmlspecialchars($data['ticket_id']) . '</td>
                    </tr>' .
                    (!empty($data['ticket_subject']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Subject:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['ticket_subject']) . '</td>
                    </tr>' : '') .
                    (!empty($data['company_name']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Company:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['company_name']) . '</td>
                    </tr>' : '') . '
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Status:</td>
                        <td style="padding: 8px 0;"><span style="background: #c4b5fd; color: #5b21b6; padding: 4px 12px; border-radius: 4px; font-size: 13px; font-weight: 600;">AWAITING FEEDBACK</span></td>
                    </tr>
                </table>
            </div>

            <!-- Feedback Request Box -->
            <div style="background-color: #f5f3ff; border: 1px solid #c4b5fd; border-radius: 8px; padding: 20px; margin: 25px 0;">
                <h4 style="margin: 0 0 15px 0; color: #7c3aed;">Help Us Improve</h4>
                <p style="margin: 0; color: #5b21b6; line-height: 1.6;">
                    Your feedback helps us serve you better. Please take a moment to share your experience and rate our service.
                </p>
            </div>

            <!-- View Ticket Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($data['view_url']) . '" style="display: inline-block; background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 15px 35px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(96,165,250,0.3);">Provide Feedback</a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #6b7280; color: white; text-align: center; padding: 25px 20px; font-size: 13px;">
            <p style="margin: 0 0 10px 0;"><strong>' . htmlspecialchars($data['app_name']) . ' Support Team</strong></p>
            <p style="margin: 0; opacity: 0.9;">This is an automated message. Please do not reply to this email.</p>
            <p style="margin: 10px 0 0 0; opacity: 0.75;">© ' . date('Y') . ' ' . htmlspecialchars($data['app_name']) . '. All rights reserved.</p>
        </div>

    </div>
</body>
</html>';
    }

    /**
     * Customer Registration Template
     * Follows requirements: Simple confirmation, no promises of timelines
     * Uses professional pastel colors
     */
    private function customerRegistrationTemplate($data) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">' . htmlspecialchars($data['app_name']) . '</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.95;">Support & Mediation Portal</p>
        </div>

        <!-- Banner -->
        <div style="background: linear-gradient(135deg, #a5f3fc 0%, #cffafe 100%); color: #0e7490; padding: 25px 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Registration Received</h2>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Thank you for registering</p>
        </div>

        <!-- Main Content -->
        <div style="padding: 30px 20px;">
            <h3 style="color: #0891b2; margin: 0 0 20px 0; font-size: 20px;">Dear ' . htmlspecialchars($data['customer_name']) . ',</h3>

            <p style="font-size: 16px; line-height: 1.6; color: #374151; margin: 0 0 20px 0;">
                Thank you for registering with ' . htmlspecialchars($data['app_name']) . '. We have received your registration request.
            </p>

            <!-- Registration Details Box -->
            <div style="background-color: #ecfeff; border-left: 4px solid #a5f3fc; padding: 20px; margin: 25px 0; border-radius: 6px;">
                <h4 style="margin: 0 0 15px 0; color: #0891b2; font-size: 18px;">Registration Details</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Customer ID:</td>
                        <td style="padding: 8px 0; color: #0891b2; font-weight: 600;">' . htmlspecialchars($data['customer_id']) . '</td>
                    </tr>' .
                    (!empty($data['company_name']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Company:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['company_name']) . '</td>
                    </tr>' : '') .
                    (!empty($data['division']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Division:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['division']) . '</td>
                    </tr>' : '') . '
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Status:</td>
                        <td style="padding: 8px 0;"><span style="background: #f59e0b; color: white; padding: 4px 12px; border-radius: 4px; font-size: 13px;">PENDING APPROVAL</span></td>
                    </tr>
                </table>
            </div>

            <!-- What\'s Next Box -->
            <div style="background-color: #ecfeff; border: 1px solid #0891b2; border-radius: 6px; padding: 20px; margin: 25px 0;">
                <h4 style="margin: 0 0 15px 0; color: #155e75;">What happens next?</h4>
                <ul style="margin: 0; padding-left: 20px; color: #155e75; line-height: 1.8;">
                    <li>Your registration will be reviewed by our team</li>
                    <li>You will receive an email notification once approved</li>
                    <li>Your login credentials will be provided upon approval</li>
                </ul>
            </div>

            <!-- Note Box -->
            <div style="background-color: #fef3c7; border: 1px solid #fbbf24; border-radius: 6px; padding: 15px; margin: 25px 0;">
                <p style="margin: 0; color: #92400e; font-size: 14px;">
                    <strong>Note:</strong> Please save this email for your records. Your Customer ID is <strong>' . htmlspecialchars($data['customer_id']) . '</strong>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #1f2937; color: white; text-align: center; padding: 25px 20px; font-size: 13px;">
            <p style="margin: 0 0 10px 0;"><strong>' . htmlspecialchars($data['app_name']) . ' Support Team</strong></p>
            <p style="margin: 0; opacity: 0.8;">This is an automated message. Please do not reply to this email.</p>
            <p style="margin: 10px 0 0 0; opacity: 0.6;">© ' . date('Y') . ' ' . htmlspecialchars($data['app_name']) . '. All rights reserved.</p>
        </div>

    </div>
</body>
</html>';
    }

    /**
     * Registration Approved Template
     * Follows requirements: Provide login ID and link to login page
     * Uses professional pastel colors
     */
    private function registrationApprovedTemplate($data) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">' . htmlspecialchars($data['app_name']) . '</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.95;">Support & Mediation Portal</p>
        </div>

        <!-- Success Banner -->
        <div style="background: linear-gradient(135deg, #86efac 0%, #a7f3d0 100%); color: #065f46; padding: 25px 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Account Approved - Welcome!</h2>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Your registration has been approved</p>
        </div>

        <!-- Main Content -->
        <div style="padding: 30px 20px;">
            <h3 style="color: #059669; margin: 0 0 20px 0; font-size: 20px;">Dear ' . htmlspecialchars($data['customer_name']) . ',</h3>

            <p style="font-size: 16px; line-height: 1.6; color: #374151; margin: 0 0 20px 0;">
                Congratulations! Your registration for ' . htmlspecialchars($data['app_name']) . ' has been approved. You can now access all services.
            </p>

            <!-- Login Credentials Box -->
            <div style="background-color: #ecfdf5; border-left: 4px solid #86efac; padding: 20px; margin: 25px 0; border-radius: 6px;">
                <h4 style="margin: 0 0 15px 0; color: #047857; font-size: 18px;">Your Login Credentials</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Login ID / Email:</td>
                        <td style="padding: 8px 0; color: #059669; font-weight: 600;">' . htmlspecialchars($data['customer_email']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Customer ID:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['customer_id']) . '</td>
                    </tr>' .
                    (!empty($data['division']) ?
                    '<tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Division:</td>
                        <td style="padding: 8px 0; color: #111827;">' . htmlspecialchars($data['division']) . '</td>
                    </tr>' : '') . '
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: bold;">Password:</td>
                        <td style="padding: 8px 0; color: #111827;">Use the password you provided during registration</td>
                    </tr>
                </table>
            </div>

            <!-- Access Features Box -->
            <div style="background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 20px; margin: 25px 0;">
                <h4 style="margin: 0 0 15px 0; color: #047857;">You can now:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #065f46; line-height: 1.8;">
                    <li>Create and track support tickets</li>
                    <li>View your complete ticket history</li>
                    <li>Update your profile information</li>
                    <li>Access all ' . htmlspecialchars($data['app_name']) . ' services</li>
                    <li>Receive notifications on ticket updates</li>
                </ul>
            </div>

            <!-- Login Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($data['login_url']) . '" style="display: inline-block; background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 100%); color: white; padding: 15px 35px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(96,165,250,0.3);">Login to Your Account</a>
            </div>

            <!-- Getting Started Box -->
            <div style="background-color: #dbeafe; border: 1px solid #93c5fd; border-radius: 8px; padding: 15px; margin: 25px 0;">
                <p style="margin: 0; color: #1e40af; font-size: 14px;">
                    <strong>Getting Started:</strong> After logging in, explore the dashboard to familiarize yourself with all available features.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #6b7280; color: white; text-align: center; padding: 25px 20px; font-size: 13px;">
            <p style="margin: 0 0 10px 0;"><strong>' . htmlspecialchars($data['app_name']) . ' Support Team</strong></p>
            <p style="margin: 0; opacity: 0.9;">This is an automated message. Please do not reply to this email.</p>
            <p style="margin: 10px 0 0 0; opacity: 0.75;">© ' . date('Y') . ' ' . htmlspecialchars($data['app_name']) . '. All rights reserved.</p>
        </div>

    </div>
</body>
</html>';
    }
}
