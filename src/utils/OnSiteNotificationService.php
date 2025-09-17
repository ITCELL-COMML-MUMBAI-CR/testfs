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
     * Sends notification to 'controller_nodal' of the ticket's division.
     */
    public function notifyUsersOfNewTicket($complaintId) {
        $ticket = $this->complaintModel->getComplaintWithDetails($complaintId);
        if (!$ticket) return;

        $users = $this->userModel->getUsersByRole('controller_nodal', $ticket['division']);

        foreach ($users as $user) {
            if ($user['role'] === 'superadmin') continue;

            $this->notificationModel->createNotification([
                'user_id' => $user['id'],
                'title' => 'New Ticket Created: #' . $complaintId,
                'message' => 'A new ticket has been created in your division (' . $ticket['division'] . ').',
                'type' => 'new_ticket',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => Config::getAppUrl() . '/controller/tickets/' . $complaintId,
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

            $this->notificationModel->createNotification([
                'user_id' => $user['id'],
                'title' => 'Ticket Forwarded: #' . $complaintId,
                'message' => 'Ticket #' . $complaintId . ' has been forwarded to your ' . ($ticket['division'] !== $toDivision ? 'division.' : 'department.'),
                'type' => 'ticket_forwarded',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => Config::getAppUrl() . '/controller/tickets/' . $complaintId,
            ]);
        }
    }

    /**
     * Notify customer of a status change.
     */
    public function notifyCustomerOfStatusChange($complaintId, $status) {
        $ticket = $this->complaintModel->find($complaintId, 'complaint_id');
        if (!$ticket || !$ticket['customer_id']) return;

        $message = '';
        switch ($status) {
            case 'awaiting_info':
                $message = 'Your ticket #' . $complaintId . ' has been updated. We are awaiting additional information from you.';
                break;
            case 'awaiting_feedback':
                $message = 'An action has been taken on your ticket #' . $complaintId . '. We are awaiting your feedback.';
                break;
            default:
                return; // Only handle these two statuses
        }

        $this->notificationModel->createNotification([
            'customer_id' => $ticket['customer_id'],
            'title' => 'Ticket Update: #' . $complaintId,
            'message' => $message,
            'type' => 'status_update',
            'related_id' => $complaintId,
            'related_type' => 'ticket',
            'action_url' => Config::getAppUrl() . '/customer/tickets/' . $complaintId,
        ]);
    }

    /**
     * Notify relevant users of priority escalation.
     * Notifies all users except customers and superadmin.
     */
    public function notifyUsersOfPriorityEscalation($complaintId) {
        $ticket = $this->complaintModel->getComplaintWithDetails($complaintId);
        if (!$ticket) return;

        $users = $this->userModel->findAll(['status' => 'active']);

        foreach ($users as $user) {
            // Skip superadmin and customers
            if ($user['role'] === 'superadmin' || $user['role'] === 'customer') {
                continue;
            }

            $this->notificationModel->createNotification([
                'user_id' => $user['id'],
                'title' => 'Priority Escalation: Ticket #' . $complaintId,
                'message' => 'The priority for ticket #' . $complaintId . ' has been escalated.',
                'type' => 'priority_escalation',
                'priority' => 'high',
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'action_url' => Config::getAppUrl() . '/controller/tickets/' . $complaintId,
            ]);
        }
    }
}
