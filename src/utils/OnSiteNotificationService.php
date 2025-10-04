<?php

require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';
require_once __DIR__ . '/../models/ComplaintModel.php';
require_once __DIR__ . '/../config/Config.php';

class OnSiteNotificationService {

    private $notificationModel;
    private $userModel;
    private $customerModel;
    private $complaintModel;

    public function __construct() {
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
        $this->customerModel = new CustomerModel();
        $this->complaintModel = new ComplaintModel();
    }

    /**
     * Notify users when a new ticket is created.
     * Sends notification to customer AND 'controller_nodal' of the ticket's division.
     */
    public function notifyUsersOfNewTicket($complaintId) {
        $ticket = $this->complaintModel->getComplaintWithDetails($complaintId);
        if (!$ticket) return;

        // 1. Create notification for CUSTOMER
        if ($ticket['customer_id']) {
            $this->notificationModel->createNotification([
                'customer_id' => $ticket['customer_id'],
                'user_type' => 'customer',
                'title' => 'Ticket Created Successfully',
                'message' => "Your ticket #{$complaintId} for {$ticket['category']} has been created and assigned. We'll update you on the progress.",
                'type' => 'ticket_created',
                'priority' => 'medium',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => $this->getTicketUrlByRole($complaintId, 'customer'),
                'complaint_id' => $complaintId,
            ]);
        }

        // 2. Create notifications for CONTROLLER NODAL users
        $users = $this->userModel->getUsersByRole('controller_nodal', $ticket['division']);

        foreach ($users as $user) {
            if ($user['role'] === 'superadmin') continue;

            $actionUrl = $this->getTicketUrlByRole($complaintId, $user['role']);

            $this->notificationModel->createNotification([
                'user_id' => $user['id'],
                'user_type' => $user['role'],
                'title' => 'New Ticket Created',
                'message' => "New support ticket #{$complaintId} has been created for {$ticket['category']} - {$ticket['type']} in {$ticket['division']} division. Priority: {$ticket['priority']}. Please review and take appropriate action.",
                'type' => 'ticket_created',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => $actionUrl,
                'complaint_id' => $complaintId,
            ]);
        }

        // 3. Create notifications for ADMIN users (for high priority tickets or all tickets based on settings)
        // Admins should be notified about new tickets for monitoring purposes
        $adminUsers = $this->userModel->findAll(['role' => 'admin', 'status' => 'active']);

        foreach ($adminUsers as $admin) {
            $actionUrl = $this->getTicketUrlByRole($complaintId, 'admin');

            $this->notificationModel->createNotification([
                'user_id' => $admin['id'],
                'user_type' => 'admin',
                'title' => 'New Ticket Created',
                'message' => "New support ticket #{$complaintId} has been created for {$ticket['category']} - {$ticket['type']} in {$ticket['division']} division. Priority: {$ticket['priority']}.",
                'type' => 'ticket_created',
                'priority' => $ticket['priority'] === 'high' ? 'high' : 'medium',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => $actionUrl,
                'complaint_id' => $complaintId,
            ]);
        }
    }

    /**
     * Notify users when a ticket is forwarded.
     */
    public function notifyUsersOfForwardedTicket($complaintId, $toDivision, $toDepartment) {
        $ticket = $this->complaintModel->getComplaintWithDetails($complaintId);
        if (!$ticket) return;

        $usersToNotify = [];

        // If forwarded to a new division, notify controller_nodals of that division
        if ($ticket['division'] !== $toDivision) {
            $usersToNotify = $this->userModel->getUsersByRole('controller_nodal', $toDivision);
        } else { // If forwarded within the same division, notify users of the department
            $usersInDept = $this->userModel->findAll(['division' => $toDivision, 'department' => $toDepartment, 'status' => 'active']);
            foreach ($usersInDept as $user) {
                if (in_array($user['role'], ['controller', 'controller_nodal'])) {
                    $usersToNotify[] = $user;
                }
            }
        }

        foreach ($usersToNotify as $user) {
            if ($user['role'] === 'superadmin') continue;

            $actionUrl = $this->getTicketUrlByRole($complaintId, $user['role']);

            $this->notificationModel->createNotification([
                'user_id' => $user['id'],
                'user_type' => $user['role'],
                'title' => 'Ticket Forwarded',
                'message' => "Ticket #{$complaintId} for {$ticket['category']} - {$ticket['type']} has been forwarded to your " . ($ticket['division'] !== $toDivision ? 'division' : 'department') . " for review and action. Priority: {$ticket['priority']}.",
                'type' => 'ticket_updated',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => $actionUrl,
                'complaint_id' => $complaintId,
            ]);
        }
    }

    /**
     * Notify customer of a status change.
     * Customers ONLY see awaiting_info and awaiting_feedback notifications.
     */
    public function notifyCustomerOfStatusChange($complaintId, $status) {
        $ticket = $this->complaintModel->getComplaintWithDetails($complaintId);
        if (!$ticket || !$ticket['customer_id']) return;

        // Only notify customers for awaiting_info and awaiting_feedback
        // NO other status changes
        if (!in_array($status, ['awaiting_info', 'awaiting_feedback'])) {
            return;
        }

        $message = '';
        $title = '';
        switch ($status) {
            case 'awaiting_info':
                $title = 'Additional Information Required';
                $message = "Your ticket #{$complaintId} regarding {$ticket['category']} - {$ticket['type']} requires additional information to proceed. Please provide the requested details to help us resolve your issue.";
                break;
            case 'awaiting_feedback':
                $title = 'Action Completed - Feedback Required';
                $message = "Action has been taken on your ticket #{$complaintId} regarding {$ticket['category']} - {$ticket['type']}. Please review the resolution and provide your feedback.";
                break;
        }

        $this->notificationModel->createNotification([
            'customer_id' => $ticket['customer_id'],
            'user_type' => 'customer',
            'title' => $title,
            'message' => $message,
            'type' => $status, // Use 'awaiting_info' or 'awaiting_feedback' as type
            'related_id' => $complaintId,
            'related_type' => 'ticket',
            'action_url' => $this->getTicketUrlByRole($complaintId, 'customer'),
            'complaint_id' => $complaintId,
        ]);
    }

    /**
     * Notify relevant users of priority escalation.
     * Notifies controllers, controller_nodals, and admins.
     * Note: Customers should NEVER see priority escalation notifications.
     */
    public function notifyUsersOfPriorityEscalation($complaintId) {
        $ticket = $this->complaintModel->getComplaintWithDetails($complaintId);
        if (!$ticket) return;

        // Get users who should be notified: controller, controller_nodal, admin
        $users = $this->userModel->findAll(['status' => 'active']);

        foreach ($users as $user) {
            // Skip superadmin and customers - customers should NOT see priority escalation
            if ($user['role'] === 'superadmin' || $user['role'] === 'customer') {
                continue;
            }

            // Only notify controller, controller_nodal, and admin
            if (!in_array($user['role'], ['controller', 'controller_nodal', 'admin'])) {
                continue;
            }

            // Generate role-appropriate URL
            $actionUrl = $this->getTicketUrlByRole($complaintId, $user['role']);

            $this->notificationModel->createNotification([
                'user_id' => $user['id'],
                'user_type' => $user['role'],
                'title' => 'Priority Escalation Alert',
                'message' => "Ticket #{$complaintId} for {$ticket['category']} - {$ticket['type']} has been escalated to {$ticket['priority']} priority due to time elapsed. Immediate attention required.",
                'type' => 'priority_escalated',
                'priority' => 'high',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => $actionUrl,
                'complaint_id' => $complaintId,
            ]);
        }
    }

    /**
     * Get ticket URL based on user role
     */
    private function getTicketUrlByRole($ticketId, $role) {
        $baseUrl = Config::getAppUrl();

        switch ($role) {
            case 'customer':
                return $baseUrl . '/customer/tickets/' . $ticketId;
            case 'controller':
            case 'controller_nodal':
                return $baseUrl . '/controller/tickets/' . $ticketId;
            case 'admin':
            case 'superadmin':
                return $baseUrl . '/admin/tickets/' . $ticketId . '/view';
            default:
                return $baseUrl . '/controller/tickets/' . $ticketId;
        }
    }
}
