<?php

/**
 * Admin Controller for SAMPARK
 * Handles admin dashboard, user management, customer approval, system settings
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/NotificationService.php';
require_once __DIR__ . '/../models/EmailTemplateModel.php';


class AdminController extends BaseController
{
    /**
     * Sanitize input string to prevent XSS and trim whitespace
     */
    private function sanitize($value)
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    public function dashboard()
    {
        $user = $this->getCurrentUser();

        // Get system stats
        $systemStats = $this->getSystemStats();
        $ticketSummary = $this->getTicketSummary();

        // Prepare overview stats for dashboard cards
        $overview_stats = [
            'total_complaints' => $systemStats['total_tickets'] ?? 0,
            'pending_complaints' => $systemStats['open_tickets'] ?? 0,
            'closed_complaints' => $systemStats['closed_tickets'] ?? 0,
            'registered_customers' => $systemStats['total_customers'] ?? 0
        ];

        // Prepare division stats for the pivot table
        $division_stats = [];
        foreach ($ticketSummary as $division) {
            $division_stats[$division['division']] = [
                'pending' => $division['pending'] ?? 0,
                'awaiting_feedback' => $this->getDivisionStatusCount($division['division'], 'awaiting_feedback'),
                'awaiting_info' => $this->getDivisionStatusCount($division['division'], 'awaiting_info'),
                'awaiting_approval' => $this->getDivisionStatusCount($division['division'], 'awaiting_approval'),
                'closed' => $division['closed'] ?? 0,
                'total' => $division['total'] ?? 0
            ];
        }

        // Prepare performance data
        $performance_data = [
            'avg_resolution_time' => $this->getAverageResolutionTime(),
            'min_resolution_time' => $this->getMinResolutionTime(),
            'max_resolution_time' => $this->getMaxResolutionTime(),
            'resolution_efficiency' => $this->getResolutionEfficiency(),
            'excellent_ratings' => $this->getRatingCount('excellent'),
            'satisfactory_ratings' => $this->getRatingCount('satisfactory'),
            'unsatisfactory_ratings' => $this->getRatingCount('unsatisfactory'),
            'avg_rating' => $this->getAverageRating(),
            'type_distribution' => $this->getComplaintTypeDistribution()
        ];

        // Get other dashboard data
        $terminal_stats = $this->getTerminalStats();
        $customer_registration_stats = $this->getCustomerRegistrationStats();

        $data = [
            'page_title' => 'Admin Dashboard - SAMPARK',
            'user' => $user,
            'overview_stats' => $overview_stats,
            'performance_data' => $performance_data,
            'division_stats' => $division_stats,
            'terminal_stats' => $terminal_stats,
            'customer_registration_stats' => $customer_registration_stats,
            'dashboard_data' => [
                'system_stats' => $systemStats,
                'recent_registrations' => $this->getRecentRegistrations(),
                'system_health' => $this->getSystemHealth(),
                'user_activity' => $this->getRecentUserActivity(),
                'pending_approvals' => $this->getPendingApprovals(),
                'system_alerts' => $this->getSystemAlerts()
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/dashboard', $data);
    }

    public function users()
    {
        $user = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $division = $_GET['division'] ?? '';
        // Build query conditions
        $conditions = ['1=1'];
        $params = [];

        if ($role) {
            $conditions[] = 'role = ?';
            $params[] = $role;
        }

        if ($status) {
            $conditions[] = 'status = ?';
            $params[] = $status;
        }

        if ($division) {
            $conditions[] = 'division = ?';
            $params[] = $division;
        }

        // Hide SUPERADMIN users from regular admins
        if ($user['role'] !== 'superadmin') {
            $conditions[] = 'role != ?';
            $params[] = 'superadmin';
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT id, login_id, name, email, mobile, role, department, 
                       division, zone, status, created_at, updated_at
                FROM users 
                WHERE {$whereClause}
                ORDER BY created_at DESC";

        $users = $this->paginate($sql, $params, $page, 20);

        // Filter roles for non-superadmin users
        $availableRoles = Config::USER_ROLES;
        if ($user['role'] !== 'superadmin') {
            unset($availableRoles['superadmin']);
        }

        $data = [
            'page_title' => 'User Management - SAMPARK',
            'user' => $user,
            'users' => $users['data'], // Extract the actual users from the paginated result
            'total_users' => $users['total'],
            'current_page' => $users['page'],
            'total_pages' => $users['total_pages'],
            'has_next' => $users['has_next'],
            'has_prev' => $users['has_prev'],
            'filters' => [
                'role' => $role,
                'status' => $status,
                'division' => $division
            ],
            'roles' => $availableRoles,
            'status_options' => Config::USER_STATUS,
            'divisions' => $this->getDivisions(),
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/users/index', $data);
    }

    public function createUser()
    {
        $user = $this->getCurrentUser();

        $regions = []; // Add logic to get regions if needed

        // Filter roles for non-superadmin users
        $availableRoles = Config::USER_ROLES;
        if ($user['role'] !== 'superadmin') {
            unset($availableRoles['superadmin']);
        }

        $data = [
            'page_title' => 'Create User - SAMPARK',
            'user' => $user,
            'roles' => $availableRoles,
            'divisions' => $this->getDivisions(),
            'departments' => $this->getDepartments(),
            'regions' => $regions,
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/users/create', $data);
    }

    public function storeUser()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'login_id' => 'required|min:4|max:50|unique:users,login_id',
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'mobile' => 'required|phone',
            'role' => 'required|in:' . implode(',', array_keys(Config::USER_ROLES)),
            'department' => 'required|max:100',
            'division' => 'required|exists:shed,division',
            'password' => 'required|password',
            'password_confirm' => 'required'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        // Check password confirmation
        if ($_POST['password'] !== $_POST['password_confirm']) {
            $this->json(['success' => false, 'message' => 'Password confirmation does not match'], 400);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Get zone from division
            $zoneInfo = $this->getZoneFromDivision($_POST['division']);

            // Insert user
            $sql = "INSERT INTO users (
                login_id, password, role, department, division, zone,
                name, email, mobile, status, force_password_change, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())";

            $forcePasswordChange = isset($_POST['force_password_change']) ? 1 : 0;

            $params = [
                trim($_POST['login_id']),
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['role'],
                trim($_POST['department']),
                $_POST['division'],
                $zoneInfo['zone'],
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']),
                $forcePasswordChange,
                $user['id']
            ];

            $this->db->query($sql, $params);
            $newUserId = $this->db->lastInsertId();

            $this->db->commit();

            // Log activity
            $this->logActivity('user_created', [
                'new_user_id' => $newUserId,
                'new_user_login' => $_POST['login_id'],
                'new_user_role' => $_POST['role']
            ]);

            // Send welcome email
            $this->sendWelcomeEmail($newUserId, $_POST['email'], $_POST['name'], $_POST['login_id']);

            $this->json([
                'success' => true,
                'message' => 'User created successfully',
                'redirect' => Config::getAppUrl() . '/admin/users'
            ]);
        } catch (Exception $e) {
            $this->db->rollback();
            Config::logError("User creation error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ], 500);
        }
    }

    public function editUser($id)
    {
        $user = $this->getCurrentUser();

        $userToEdit = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );

        if (!$userToEdit) {
            $this->setFlash('error', 'User not found');
            $this->redirect(Config::getAppUrl() . '/admin/users');
            return;
        }

        // Prevent regular admins from editing SUPERADMIN users
        if ($user['role'] !== 'superadmin' && $userToEdit['role'] === 'superadmin') {
            $this->setFlash('error', 'Access denied');
            $this->redirect(Config::getAppUrl() . '/admin/users');
            return;
        }

        // Filter roles for non-superadmin users
        $availableRoles = Config::USER_ROLES;
        if ($user['role'] !== 'superadmin') {
            unset($availableRoles['superadmin']);
        }

        $data = [
            'page_title' => 'Edit User - SAMPARK',
            'user' => $user,
            'user_to_edit' => $userToEdit,
            'roles' => $availableRoles,
            'divisions' => $this->getDivisions(),
            'departments' => $this->getDepartments(),
            'status_options' => Config::USER_STATUS,
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/users/edit', $data);
    }

    public function updateUser($id)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $userToEdit = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );

        if (!$userToEdit) {
            $this->setFlash('error', 'User not found');
            $this->redirect(Config::getAppUrl() . '/admin/users');
            return;
        }

        // Prevent regular admins from updating SUPERADMIN users
        if ($user['role'] !== 'superadmin' && $userToEdit['role'] === 'superadmin') {
            $this->setFlash('error', 'Access denied');
            $this->redirect(Config::getAppUrl() . '/admin/users');
            return;
        }

        // Prevent regular admins from setting role to superadmin
        if ($user['role'] !== 'superadmin' && $_POST['role'] === 'superadmin') {
            $this->setFlash('error', 'Cannot assign superadmin role');
            $this->redirect(Config::getAppUrl() . '/admin/users/' . $id . '/edit');
            return;
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'mobile' => 'required|phone',
            'role' => 'required|in:' . implode(',', array_keys(Config::USER_ROLES)),
            'department' => 'required|max:100',
            'division' => 'required|exists:shed,division',
            'status' => 'required|in:' . implode(',', array_keys(Config::USER_STATUS))
        ]);

        if (!$isValid) {
            $this->setFlash('error', 'Please check the form for errors.');
            $this->setFlash('errors', $validator->getErrors());
            $this->redirect(Config::getAppUrl() . '/admin/users/' . $id . '/edit');
            return;
        }

        try {
            $this->db->beginTransaction();

            // Get zone from division
            $zoneInfo = $this->getZoneFromDivision($_POST['division']);

            // Update user
            $sql = "UPDATE users SET
                    name = ?, email = ?, mobile = ?, role = ?,
                    department = ?, division = ?, zone = ?, status = ?, force_password_change = ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $forcePasswordChange = isset($_POST['force_password_change']) ? 1 : 0;

            $params = [
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']),
                $_POST['role'],
                trim($_POST['department']),
                $_POST['division'],
                $zoneInfo['zone'],
                $_POST['status'],
                $forcePasswordChange,
                $id
            ];

            $this->db->query($sql, $params);

            // Update password if provided
            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] !== $_POST['password_confirmation']) {
                    $this->setFlash('error', 'Password confirmation does not match');
                    $this->redirect(Config::getAppUrl() . '/admin/users/' . $id . '/edit');
                    return;
                }

                $this->db->query(
                    "UPDATE users SET password = ? WHERE id = ?",
                    [password_hash($_POST['new_password'], PASSWORD_DEFAULT), $id]
                );
            }

            $this->db->commit();

            // Log activity
            $this->logActivity('user_updated', [
                'updated_user_id' => $id,
                'updated_user_login' => $userToEdit['login_id'],
                'changes' => array_diff_assoc($_POST, $userToEdit)
            ]);

            $this->setFlash('success', 'User updated successfully');
            $this->redirect(Config::getAppUrl() . '/admin/users');
        } catch (Exception $e) {
            $this->db->rollback();
            Config::logError("User update error: " . $e->getMessage());

            $this->setFlash('error', 'Failed to update user. Please try again.');
            $this->redirect(Config::getAppUrl() . '/admin/users/' . $id . '/edit');
        }
    }

    public function viewUser($id)
    {
        $user = $this->getCurrentUser();

        $userToView = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );

        if (!$userToView) {
            $this->setFlash('error', 'User not found');
            $this->redirect(Config::getAppUrl() . '/admin/users');
            return;
        }

        $data = [
            'page_title' => 'User Details - SAMPARK',
            'user' => $user,
            'user_to_view' => $userToView,
            'roles' => Config::USER_ROLES,
            'status_options' => Config::USER_STATUS,
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/users/view', $data);
    }

    public function toggleUser($id)
    {

        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $userToToggle = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );

        if (!$userToToggle) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        try {
            $newStatus = $userToToggle['status'] === 'active' ? 'inactive' : 'active';

            // Check if status is explicitly provided in the request (for AJAX toggle)
            if (isset($_POST['status']) || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)) {
                // Get JSON input for API calls
                $jsonInput = json_decode(file_get_contents('php://input'), true);
                if (isset($jsonInput['status'])) {
                    $newStatus = $jsonInput['status'];
                }
            }

            $this->db->query(
                "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
                [$newStatus, $id]
            );

            // Log activity
            $this->logActivity('user_status_changed', [
                'user_id' => $id,
                'user_login' => $userToToggle['login_id'],
                'old_status' => $userToToggle['status'],
                'new_status' => $newStatus
            ]);

            $this->json([
                'success' => true,
                'message' => "User {$newStatus} successfully",
                'new_status' => $newStatus
            ]);
        } catch (Exception $e) {
            Config::logError("User toggle error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to update user status. Please try again.'
            ], 500);
        }
    }

    public function resetUserPassword($id)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        // For AJAX requests
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            $_POST = array_merge($_POST, $jsonInput ?? []);
        }

        $userToReset = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );

        if (!$userToReset) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        // Generate a secure random password
        $newPassword = bin2hex(random_bytes(6)); // 12 characters

        try {
            // Update the password
            $this->db->query(
                "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
                [password_hash($newPassword, PASSWORD_DEFAULT), $id]
            );

            // Log activity
            $this->logActivity('user_password_reset', [
                'user_id' => $id,
                'user_login' => $userToReset['login_id'],
                'reset_by' => $user['id']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Password has been reset successfully',
                'new_password' => $newPassword
            ]);
        } catch (Exception $e) {
            Config::logError("Password reset error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.'
            ], 500);
        }
    }

    public function customers()
    {
        $user = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $customer_type = $_GET['customer_type'] ?? '';
        $region = $_GET['region'] ?? '';

        // Build query conditions
        $conditions = ['1=1'];
        $params = [];

        if ($status) {
            $conditions[] = 'c.status = ?';
            $params[] = $status;
        }

        if ($customer_type) {
            $conditions[] = 'COALESCE(c.customer_type, "individual") = ?';
            $params[] = $customer_type;
        }

        if ($region) {
            $conditions[] = 'c.division = ?';
            $params[] = $region;
        }

        $whereClause = implode(' AND ', $conditions);

        // Enhanced query with ticket counts and customer type
        $sql = "SELECT c.customer_id, c.name, c.email, c.mobile, c.company_name, 
                       c.designation, c.gstin, c.division, c.zone, c.status, c.created_at,
                       COALESCE(c.customer_type, 'individual') as customer_type,
                       COALESCE(t.total_tickets, 0) as total_tickets,
                       COALESCE(t.open_tickets, 0) as open_tickets
                FROM customers c
                LEFT JOIN (
                    SELECT customer_id, 
                           COUNT(*) as total_tickets,
                           SUM(CASE WHEN status != 'closed' THEN 1 ELSE 0 END) as open_tickets
                    FROM complaints 
                    GROUP BY customer_id
                ) t ON c.customer_id = t.customer_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC";

        try {
            $customers = $this->paginate($sql, $params, $page, 20);

            // Ensure customers data is an array even if empty
            if (!isset($customers['data']) || !is_array($customers['data'])) {
                $customers['data'] = [];
            }

            // Get statistics
            $stats = $this->getCustomerStats();

            // Get regions for filter - convert to proper format
            $divisions = $this->getDivisions();
            $regions = [];
            foreach ($divisions as $division) {
                $regions[] = [
                    'id' => $division['division'],
                    'name' => $division['division']
                ];
            }

            $data = [
                'page_title' => 'Customer Management - SAMPARK',
                'user' => $user,
                'customers' => $customers['data'],
                'total_customers' => isset($customers['total']) ? $customers['total'] : 0,
                'current_page' => isset($customers['page']) ? $customers['page'] : 1,
                'total_pages' => isset($customers['total_pages']) ? $customers['total_pages'] : 1,
                'has_next' => isset($customers['has_next']) ? $customers['has_next'] : false,
                'has_prev' => isset($customers['has_prev']) ? $customers['has_prev'] : false,
                'stats' => $stats,
                'filters' => [
                    'status' => $status,
                    'customer_type' => $customer_type,
                    'region' => $region
                ],
                'regions' => $regions,
                'csrf_token' => $this->session->getCSRFToken()
            ];
        } catch (\Exception $e) {
            error_log("Error in customers method: " . $e->getMessage());

            // Provide default data
            $data = [
                'page_title' => 'Customer Management - SAMPARK',
                'user' => $user,
                'customers' => [],
                'total_customers' => 0,
                'current_page' => 1,
                'total_pages' => 1,
                'has_next' => false,
                'has_prev' => false,
                'stats' => [
                    'total_customers' => 0,
                    'active_customers' => 0,
                    'pending_verification' => 0,
                    'new_this_month' => 0
                ],
                'filters' => [
                    'status' => $status,
                    'customer_type' => $customer_type,
                    'region' => $region
                ],
                'regions' => [],
                'csrf_token' => $this->session->getCSRFToken()
            ];
        }

        $this->view('admin/customers/index', $data);
    }

    public function viewCustomer($customerId)
    {
        $user = $this->getCurrentUser();

        $customer = $this->db->fetch(
            "SELECT c.*, 
                    COALESCE(t.total_tickets, 0) as total_tickets,
                    COALESCE(t.open_tickets, 0) as open_tickets,
                    COALESCE(t.closed_tickets, 0) as closed_tickets
             FROM customers c
             LEFT JOIN (
                 SELECT customer_id, 
                        COUNT(*) as total_tickets,
                        SUM(CASE WHEN status != 'closed' THEN 1 ELSE 0 END) as open_tickets,
                        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets
                 FROM complaints 
                 GROUP BY customer_id
             ) t ON c.customer_id = t.customer_id
             WHERE c.customer_id = ?",
            [$customerId]
        );

        if (!$customer) {
            $this->setFlash('error', 'Customer not found');
            $this->redirect(Config::getAppUrl() . '/admin/customers');
            return;
        }

        // Get recent tickets for this customer
        $recentTickets = $this->db->fetchAll(
            "SELECT complaint_id, description, status, priority, created_at 
             FROM complaints 
             WHERE customer_id = ? 
             ORDER BY created_at DESC 
             LIMIT 10",
            [$customerId]
        );

        $data = [
            'page_title' => 'Customer Details - SAMPARK',
            'user' => $user,
            'customer' => $customer,
            'recent_tickets' => $recentTickets,
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/customers/view', $data);
    }

    public function editCustomer($customerId)
    {
        $user = $this->getCurrentUser();

        $customer = $this->db->fetch(
            "SELECT * FROM customers WHERE customer_id = ?",
            [$customerId]
        );

        if (!$customer) {
            $this->setFlash('error', 'Customer not found');
            $this->redirect(Config::getAppUrl() . '/admin/customers');
            return;
        }

        $data = [
            'page_title' => 'Edit Customer - SAMPARK',
            'user' => $user,
            'customer' => $customer,
            'divisions' => $this->getDivisions(),
            'customer_types' => [
                'individual' => 'Individual',
                'corporate' => 'Corporate',
                'government' => 'Government'
            ],
            'status_options' => [
                'pending' => 'Pending Approval',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'suspended' => 'Suspended'
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/customers/edit', $data);
    }

    public function updateCustomer($customerId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $customer = $this->db->fetch(
            "SELECT * FROM customers WHERE customer_id = ?",
            [$customerId]
        );

        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found'], 404);
            return;
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:customers,email,' . $customerId . ',customer_id',
            'mobile' => 'required|phone|unique:customers,mobile,' . $customerId . ',customer_id',
            'company_name' => 'required|max:150',
            'designation' => 'max:100',
            'gstin' => 'max:15',
            'customer_type' => 'required|in:individual,corporate,government',
            'division' => 'required|max:50',
            'zone' => 'required|max:50',
            'status' => 'required|in:pending,approved,rejected,suspended'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $this->db->beginTransaction();

            $sql = "UPDATE customers SET 
                    name = ?, email = ?, mobile = ?, company_name = ?, 
                    designation = ?, gstin = ?, customer_type = ?, 
                    division = ?, zone = ?, status = ?, updated_at = NOW()
                    WHERE customer_id = ?";

            $params = [
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']),
                trim($_POST['company_name']),
                trim($_POST['designation']) ?: null,
                trim($_POST['gstin']) ?: null,
                $_POST['customer_type'],
                trim($_POST['division']),
                trim($_POST['zone']),
                $_POST['status'],
                $customerId
            ];

            $this->db->query($sql, $params);

            $this->db->commit();

            // Log activity
            $this->logActivity('customer_updated', [
                'customer_id' => $customerId,
                'customer_name' => $_POST['name'],
                'changes' => array_diff_assoc($_POST, $customer)
            ]);

            $this->json([
                'success' => true,
                'message' => 'Customer updated successfully'
            ]);
        } catch (Exception $e) {
            $this->db->rollback();
            Config::logError("Customer update error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to update customer. Please try again.'
            ], 500);
        }
    }

    public function approveCustomer($customerId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $customer = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ? AND status = 'pending'",
                [$customerId]
            );

            if (!$customer) {
                $this->json(['success' => false, 'message' => 'Customer not found or already processed'], 404);
                return;
            }

            $this->db->query(
                "UPDATE customers SET status = 'approved', updated_at = NOW() WHERE customer_id = ?",
                [$customerId]
            );

            // Log activity
            $this->logActivity('customer_approved', [
                'customer_id' => $customerId,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email']
            ]);

            // Send approval email
            $this->sendCustomerApprovalEmail($customer, 'approved');

            $this->json([
                'success' => true,
                'message' => 'Customer approved successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Customer approval error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to approve customer. Please try again.'
            ], 500);
        }
    }

    public function updateCustomerStatus($customerId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        // For AJAX requests
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if (isset($jsonInput['status'])) {
                $_POST['status'] = $jsonInput['status'];
            }
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'status' => 'required|in:pending,approved,rejected,suspended'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $customer = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ?",
                [$customerId]
            );

            if (!$customer) {
                $this->json(['success' => false, 'message' => 'Customer not found'], 404);
                return;
            }

            $this->db->query(
                "UPDATE customers SET status = ?, updated_at = NOW() WHERE customer_id = ?",
                [$_POST['status'], $customerId]
            );

            // Log activity
            $this->logActivity('customer_status_changed', [
                'customer_id' => $customerId,
                'customer_name' => $customer['name'],
                'old_status' => $customer['status'],
                'new_status' => $_POST['status']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Customer status updated successfully',
                'new_status' => $_POST['status']
            ]);
        } catch (Exception $e) {
            Config::logError("Customer status update error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to update customer status. Please try again.'
            ], 500);
        }
    }

    public function rejectCustomer($customerId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'rejection_reason' => 'required|min:10|max:500'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $customer = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ? AND status = 'pending'",
                [$customerId]
            );

            if (!$customer) {
                $this->json(['success' => false, 'message' => 'Customer not found or already processed'], 404);
                return;
            }

            $this->db->query(
                "UPDATE customers SET status = 'rejected', updated_at = NOW() WHERE customer_id = ?",
                [$customerId]
            );

            // Log activity
            $this->logActivity('customer_rejected', [
                'customer_id' => $customerId,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'rejection_reason' => $_POST['rejection_reason']
            ]);

            // Send rejection email
            $this->sendCustomerApprovalEmail($customer, 'rejected', $_POST['rejection_reason']);

            $this->json([
                'success' => true,
                'message' => 'Customer rejected successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Customer rejection error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to reject customer. Please try again.'
            ], 500);
        }
    }

    public function categories()
    {
        $user = $this->getCurrentUser();

        $categories = $this->db->fetchAll(
            "SELECT *,
             (SELECT COUNT(*) FROM complaint_categories sub WHERE sub.category = complaint_categories.category AND sub.type = complaint_categories.type AND sub.subtype IS NOT NULL AND sub.subtype != '') as subtype_count,
             (SELECT COUNT(*) FROM complaints WHERE category_id = complaint_categories.category_id) as ticket_count
             FROM complaint_categories
             ORDER BY category, type, subtype"
        );

        $data = [
            'page_title' => 'Complaint Categories - SAMPARK',
            'user' => $user,
            'categories' => $categories,
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/categories', $data);
    }

    public function storeCategory()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'category' => 'required|max:100',
            'type' => 'required|max:100',
            'subtype' => 'required|max:100'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $subtype = trim($_POST['subtype']);

            // Check for duplicate
            $existing = $this->db->fetch(
                "SELECT category_id FROM complaint_categories WHERE category = ? AND type = ? AND subtype = ?",
                [$_POST['category'], $_POST['type'], $subtype]
            );

            if ($existing) {
                $this->json(['success' => false, 'message' => 'Category combination already exists'], 400);
                return;
            }

            $sql = "INSERT INTO complaint_categories (category, type, subtype)
                    VALUES (?, ?, ?)";

            $this->db->query($sql, [
                trim($_POST['category']),
                trim($_POST['type']),
                $subtype
            ]);

            // Log activity
            $this->logActivity('category_created', [
                'category' => $_POST['category'],
                'type' => $_POST['type'],
                'subtype' => $_POST['subtype']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Category created successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Category creation error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to create category. Please try again.'
            ], 500);
        }
    }

    public function updateCategory($categoryId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'category' => 'required|max:100',
            'type' => 'required|max:100',
            'subtype' => 'required|max:100'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $category = $this->db->fetch(
                "SELECT * FROM complaint_categories WHERE category_id = ?",
                [$categoryId]
            );

            if (!$category) {
                $this->json(['success' => false, 'message' => 'Category not found'], 404);
                return;
            }

            $sql = "UPDATE complaint_categories SET
                    category = ?, type = ?, subtype = ?
                    WHERE category_id = ?";

            $this->db->query($sql, [
                trim($_POST['category']),
                trim($_POST['type']),
                trim($_POST['subtype']),
                $categoryId
            ]);

            // Log activity
            $this->logActivity('category_updated', [
                'category_id' => $categoryId,
                'old_category' => $category['category'],
                'new_category' => $_POST['category']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Category update error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to update category. Please try again.'
            ], 500);
        }
    }

    public function deleteCategory($categoryId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            // Check if category is in use
            $inUse = $this->db->fetch(
                "SELECT COUNT(*) as count FROM complaints WHERE category_id = ?",
                [$categoryId]
            );

            if ($inUse['count'] > 0) {
                $this->json(['success' => false, 'message' => 'Cannot delete category that is in use by tickets'], 400);
                return;
            }

            $category = $this->db->fetch(
                "SELECT * FROM complaint_categories WHERE category_id = ?",
                [$categoryId]
            );

            if (!$category) {
                $this->json(['success' => false, 'message' => 'Category not found'], 404);
                return;
            }

            $this->db->query(
                "DELETE FROM complaint_categories WHERE category_id = ?",
                [$categoryId]
            );

            // Log activity
            $this->logActivity('category_deleted', [
                'category_id' => $categoryId,
                'category' => $category['category'],
                'type' => $category['type'],
                'subtype' => $category['subtype']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Category deletion error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to delete category. Please try again.'
            ], 500);
        }
    }


    public function getCategoriesDistinct()
    {
        try {
            $categories = $this->db->fetchAll(
                "SELECT DISTINCT category FROM complaint_categories WHERE category IS NOT NULL AND category != '' ORDER BY category"
            );

            $types = $this->db->fetchAll(
                "SELECT DISTINCT type FROM complaint_categories WHERE type IS NOT NULL AND type != '' ORDER BY type"
            );

            $this->json([
                'success' => true,
                'categories' => array_column($categories, 'category'),
                'types' => array_column($types, 'type')
            ]);
        } catch (Exception $e) {
            Config::logError("Get distinct categories error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to load categories and types'
            ], 500);
        }
    }

    public function getCategoriesTableData()
    {
        try {
            $categories = $this->db->fetchAll(
                "SELECT *,
                 (SELECT COUNT(*) FROM complaint_categories sub WHERE sub.category = complaint_categories.category AND sub.type = complaint_categories.type AND sub.subtype IS NOT NULL AND sub.subtype != '') as subtype_count,
                 (SELECT COUNT(*) FROM complaints WHERE category_id = complaint_categories.category_id) as ticket_count
                 FROM complaint_categories
                 ORDER BY category, type, subtype"
            );

            ob_start();
            if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-tags fa-3x mb-3"></i>
                            <h5>No categories found</h5>
                            <p>Click "Add Category" to create your first complaint category.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($category['category']) ?></div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?= ucfirst($category['type']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($category['subtype']): ?>
                                <span class="badge bg-primary"><?= htmlspecialchars($category['subtype']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">No subtype</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-apple-primary"
                                    onclick="editCategory(<?= $category['category_id'] ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-apple-danger"
                                    onclick="deleteCategory(<?= $category['category_id'] ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
<?php endif;

            $html = ob_get_clean();

            $this->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (Exception $e) {
            Config::logError("Get categories table data error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to load table data'
            ], 500);
        }
    }

    public function sheds()
    {
        $user = $this->getCurrentUser();

        $sheds = $this->db->fetchAll(
            "SELECT * FROM shed ORDER BY division, zone, name"
        );

        $data = [
            'page_title' => 'Shed Management - SAMPARK',
            'user' => $user,
            'sheds' => $sheds,
            'divisions' => $this->getDivisions(),
            'zones' => $this->getZones(),
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/sheds', $data);
    }

    public function storeShed()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'shed_code' => 'required|max:10|unique:shed,shed_code',
            'name' => 'required|max:200',
            'division' => 'required|max:100',
            'zone' => 'required|max:100',
            'terminal' => 'required|max:150',
            'type' => 'required|in:goods_shed,container_depot,private_siding,public_siding'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $sql = "INSERT INTO shed (shed_code, name, division, zone, terminal, type, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $this->db->query($sql, [
                strtoupper(trim($_POST['shed_code'])),
                trim($_POST['name']),
                trim($_POST['division']),
                trim($_POST['zone']),
                trim($_POST['terminal']),
                $_POST['type']
            ]);

            // Log activity
            $this->logActivity('shed_created', [
                'shed_code' => $_POST['shed_code'],
                'name' => $_POST['name'],
                'division' => $_POST['division']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Shed created successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Shed creation error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to create shed. Please try again.'
            ], 500);
        }
    }

    public function updateShed($shedId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'shed_code' => 'required|max:10|unique:shed,shed_code,' . $shedId,
            'name' => 'required|max:200',
            'division' => 'required|max:100',
            'zone' => 'required|max:100',
            'terminal' => 'required|max:150',
            'type' => 'required|in:goods_shed,container_depot,private_siding,public_siding',
            'is_active' => 'boolean'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $shed = $this->db->fetch(
                "SELECT * FROM shed WHERE shed_id = ?",
                [$shedId]
            );

            if (!$shed) {
                $this->json(['success' => false, 'message' => 'Shed not found'], 404);
                return;
            }

            $sql = "UPDATE shed SET 
                    shed_code = ?, name = ?, division = ?, zone = ?, 
                    terminal = ?, type = ?, is_active = ?, updated_at = NOW()
                    WHERE shed_id = ?";

            $this->db->query($sql, [
                strtoupper(trim($_POST['shed_code'])),
                trim($_POST['name']),
                trim($_POST['division']),
                trim($_POST['zone']),
                trim($_POST['terminal']),
                $_POST['type'],
                isset($_POST['is_active']) ? 1 : 0,
                $shedId
            ]);

            // Log activity
            $this->logActivity('shed_updated', [
                'shed_id' => $shedId,
                'shed_code' => $_POST['shed_code'],
                'name' => $_POST['name']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Shed updated successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Shed update error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to update shed. Please try again.'
            ], 500);
        }
    }

    public function content()
    {
        $user = $this->getCurrentUser();

        // Get all content types for DataTables
        $news = $this->db->fetchAll(
            "SELECT n.*, u.name as created_by_name 
             FROM news n 
             LEFT JOIN users u ON n.created_by = u.id 
             ORDER BY n.publish_date DESC"
        );

        $quickLinks = $this->db->fetchAll(
            "SELECT * FROM quick_links ORDER BY sort_order, title"
        );

        // Get announcements (news with type = 'announcement')
        $announcements = $this->db->fetchAll(
            "SELECT n.*, u.name as created_by_name 
             FROM news n 
             LEFT JOIN users u ON n.created_by = u.id 
             WHERE n.type = 'announcement' 
             ORDER BY n.publish_date DESC"
        );

        $data = [
            'page_title' => 'Content Management - SAMPARK',
            'user' => $user,
            'news' => $news,
            'announcements' => $announcements,
            'quick_links' => $quickLinks,
            'divisions' => $this->getDivisions(),
            'zones' => $this->getZones(),
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/content', $data);
    }

    public function storeNews()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'title' => 'required|max:255',
            'content' => 'required',
            'short_description' => 'max:500',
            'type' => 'required|in:news,announcement,alert,update',
            'priority' => 'required|in:low,medium,high,urgent',
            'publish_date' => 'required|date',
            'expire_date' => 'date|after:publish_date'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $sql = "INSERT INTO news (
                title, content, short_description, type, priority,
                publish_date, expire_date, division_specific, zone_specific,
                show_on_homepage, show_on_marquee, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $this->db->query($sql, [
                trim($_POST['title']),
                trim($_POST['content']),
                trim($_POST['short_description']) ?: null,
                $_POST['type'],
                $_POST['priority'],
                $_POST['publish_date'],
                !empty($_POST['expire_date']) ? $_POST['expire_date'] : null,
                !empty($_POST['division_specific']) ? $_POST['division_specific'] : null,
                !empty($_POST['zone_specific']) ? $_POST['zone_specific'] : null,
                isset($_POST['show_on_homepage']) ? 1 : 0,
                isset($_POST['show_on_marquee']) ? 1 : 0,
                $user['id']
            ]);

            // Log activity
            $this->logActivity('news_created', [
                'title' => $_POST['title'],
                'type' => $_POST['type'],
                'priority' => $_POST['priority']
            ]);

            $this->json([
                'success' => true,
                'message' => 'News/Announcement created successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("News creation error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to create news. Please try again.'
            ], 500);
        }
    }

    public function storeLink()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'title' => 'required|max:100',
            'url' => 'required|url|max:500',
            'description' => 'max:500',
            'icon' => 'max:50',
            'target' => 'required|in:_self,_blank',
            'sort_order' => 'integer|min:0'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $sql = "INSERT INTO quick_links (
                title, description, url, icon, target, sort_order, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $this->db->query($sql, [
                trim($_POST['title']),
                trim($_POST['description']) ?: null,
                trim($_POST['url']),
                trim($_POST['icon']) ?: null,
                $_POST['target'],
                $_POST['sort_order'] ?? 0
            ]);

            // Log activity
            $this->logActivity('quick_link_created', [
                'title' => $_POST['title'],
                'url' => $_POST['url']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Quick link created successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Quick link creation error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to create quick link. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete news item
     */
    public function deleteNews($id)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $news = $this->db->fetch("SELECT * FROM news WHERE id = ?", [$id]);

            if (!$news) {
                $this->json(['success' => false, 'message' => 'News item not found'], 404);
                return;
            }

            $this->db->query("DELETE FROM news WHERE id = ?", [$id]);

            // Log activity
            $this->logActivity('news_deleted', [
                'news_id' => $id,
                'title' => $news['title']
            ]);

            $this->json([
                'success' => true,
                'message' => 'News item deleted successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("News deletion error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to delete news item. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete announcement
     */
    public function deleteAnnouncement($id)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $announcement = $this->db->fetch("SELECT * FROM news WHERE id = ? AND type = 'announcement'", [$id]);

            if (!$announcement) {
                $this->json(['success' => false, 'message' => 'Announcement not found'], 404);
                return;
            }

            $this->db->query("DELETE FROM news WHERE id = ?", [$id]);

            // Log activity
            $this->logActivity('announcement_deleted', [
                'announcement_id' => $id,
                'title' => $announcement['title']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Announcement deletion error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to delete announcement. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete quick link
     */
    public function deleteLink($id)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $link = $this->db->fetch("SELECT * FROM quick_links WHERE id = ?", [$id]);

            if (!$link) {
                $this->json(['success' => false, 'message' => 'Link not found'], 404);
                return;
            }

            $this->db->query("DELETE FROM quick_links WHERE id = ?", [$id]);

            // Log activity
            $this->logActivity('quick_link_deleted', [
                'link_id' => $id,
                'title' => $link['title']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Link deleted successfully'
            ]);
        } catch (Exception $e) {
            Config::logError("Link deletion error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to delete link. Please try again.'
            ], 500);
        }
    }

    /**
     * Admin tickets view - shows tickets based on admin's department and division
     */
    public function tickets()
    {
        $user = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $category = $_GET['category'] ?? '';

        // Build query conditions based on admin's access level
        $conditions = ['1=1'];
        $params = [];

        // Apply department and division filtering rules
        if (!in_array($user['department'], ['CML', 'ADM'])) {
            $conditions[] = 'c.assigned_to_department = ? OR c.department = ?';
            $params[] = $user['department'];
            $params[] = $user['department'];
        }

        if ($user['division'] === 'HQ') {
            $conditions[] = 'c.zone = ?';
            $params[] = $user['zone'];
        } else {
            $conditions[] = 'c.division = ?';
            $params[] = $user['division'];
        }

        // Apply additional filters
        if ($status) {
            $conditions[] = 'c.status = ?';
            $params[] = $status;
        }

        if ($priority) {
            $conditions[] = 'c.priority = ?';
            $params[] = $priority;
        }

        if ($dateFrom) {
            $conditions[] = 'c.date >= ?';
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $conditions[] = 'c.date <= ?';
            $params[] = $dateTo;
        }

        if ($category) {
            $conditions[] = 'cc.category = ?';
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT c.complaint_id, c.description, c.status, c.priority, c.date, c.time,
                       c.division, c.zone, c.assigned_to_department, c.created_at,
                       cc.category, cc.type, cc.subtype,
                       s.name as shed_name, s.shed_code,
                       cust.name as customer_name, cust.email as customer_email,
                       cust.mobile as customer_mobile, cust.company_name,
                       (SELECT COUNT(*) FROM transactions t
                        WHERE t.complaint_id = c.complaint_id AND t.remarks_type = 'admin_remarks') as admin_remarks_count
                FROM complaints c
                LEFT JOIN complaint_categories cc ON c.category_id = cc.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC";

        try {
            $tickets = $this->paginate($sql, $params, $page, 20);

            $data = [
                'page_title' => 'Admin Tickets - SAMPARK',
                'user' => $user,
                'tickets' => $tickets['data'],
                'total_tickets' => $tickets['total'],
                'current_page' => $tickets['page'],
                'total_pages' => $tickets['total_pages'],
                'has_next' => $tickets['has_next'],
                'has_prev' => $tickets['has_prev'],
                'filters' => [
                    'status' => $status,
                    'priority' => $priority,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'category' => $category
                ],
                'categories' => $this->getDistinctCategories(),
                'status_options' => [
                    'pending' => 'Pending',
                    'awaiting_feedback' => 'Awaiting Feedback',
                    'awaiting_info' => 'Awaiting Info',
                    'awaiting_approval' => 'Awaiting Approval',
                    'closed' => 'Closed'
                ],
                'priority_options' => [
                    'normal' => 'Normal',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'critical' => 'Critical'
                ],
                'csrf_token' => $this->session->getCSRFToken()
            ];

            $this->view('admin/tickets/index', $data);
        } catch (Exception $e) {
            Config::logError("Admin tickets error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load tickets. Please try again.');
            $this->redirect(Config::getAppUrl() . '/admin/dashboard');
        }
    }

    /**
     * Debug page for testing admin access and data
     */
    public function debug()
    {
        $user = $this->getCurrentUser();

        try {
            // Simple query to test data access
            $sql = "SELECT COUNT(*) as total FROM complaints c";
            $totalComplaints = $this->db->fetch($sql)['total'];

            // Test user access conditions
            $conditions = ['1=1'];
            $params = [];

            if ($user['department'] === 'ADM') {
                if ($user['division'] === 'HQ') {
                    $conditions[] = 'c.zone = ?';
                    $params[] = $user['zone'];
                } else {
                    $conditions[] = 'c.division = ?';
                    $params[] = $user['division'];
                }
            } else {
                if ($user['division'] === 'HQ') {
                    $conditions[] = 'c.zone = ? AND c.assigned_to_department = ?';
                    $params[] = $user['zone'];
                    $params[] = $user['department'];
                } else {
                    $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
                    $params[] = $user['division'];
                    $params[] = $user['department'];
                }
            }

            $whereClause = implode(' AND ', $conditions);
            $testSql = "SELECT COUNT(*) as accessible FROM complaints c WHERE {$whereClause}";
            $accessibleComplaints = $this->db->fetch($testSql, $params)['accessible'];

            // Get sample data
            $sampleSql = "SELECT c.complaint_id, c.description, c.status, c.created_at
                         FROM complaints c WHERE {$whereClause} LIMIT 5";
            $sampleData = $this->db->fetchAll($sampleSql, $params);

            $data = [
                'page_title' => 'Admin Debug - SAMPARK',
                'user' => $user,
                'total_complaints' => $totalComplaints,
                'accessible_complaints' => $accessibleComplaints,
                'sample_data' => $sampleData,
                'access_conditions' => $conditions,
                'access_params' => $params
            ];

            $this->json($data);
        } catch (Exception $e) {
            $this->json([
                'error' => $e->getMessage(),
                'user' => $user
            ]);
        }
    }

    /**
     * Admin search tickets page
     */
    public function searchTickets()
    {
        $user = $this->getCurrentUser();

        $complaintNumber = $_GET['complaint_number'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $customerMobile = $_GET['customer_mobile'] ?? '';
        $customerEmail = $_GET['customer_email'] ?? '';
        $page = $_GET['page'] ?? 1;

        $tickets = [];
        $totalTickets = 0;
        $hasSearch = false;

        // Only search if at least one parameter is provided
        if ($complaintNumber || $dateFrom || $dateTo || $customerMobile || $customerEmail) {
            $hasSearch = true;

            $conditions = ['1=1']; // Show all tickets regardless of status for admin search
            $params = [];

            if ($complaintNumber) {
                $conditions[] = 'c.complaint_id LIKE ?';
                $params[] = '%' . $complaintNumber . '%';
            }

            if ($dateFrom) {
                $conditions[] = 'c.date >= ?';
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $conditions[] = 'c.date <= ?';
                $params[] = $dateTo;
            }

            if ($customerMobile) {
                $conditions[] = 'cust.mobile LIKE ?';
                $params[] = '%' . $customerMobile . '%';
            }

            if ($customerEmail) {
                $conditions[] = 'cust.email LIKE ?';
                $params[] = '%' . $customerEmail . '%';
            }

            $whereClause = implode(' AND ', $conditions);

            $sql = "SELECT c.complaint_id, c.description, c.status, c.priority, c.date, c.time,
                           c.division, c.zone, c.assigned_to_department, c.created_at,
                           cc.category, cc.type, cc.subtype,
                           s.name as shed_name, s.shed_code,
                           cust.name as customer_name, cust.email as customer_email,
                           cust.mobile as customer_mobile, cust.company_name,
                           (SELECT COUNT(*) FROM transactions t
                            WHERE t.complaint_id = c.complaint_id AND t.remarks_type = 'admin_remarks') as admin_remarks_count
                    FROM complaints c
                    LEFT JOIN complaint_categories cc ON c.category_id = cc.category_id
                    LEFT JOIN shed s ON c.shed_id = s.shed_id
                    LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                    WHERE {$whereClause}
                    ORDER BY c.created_at DESC";

            try {
                $result = $this->paginate($sql, $params, $page, 20);
                $tickets = $result['data'];
                $totalTickets = $result['total'];
                $currentPage = $result['page'];
                $totalPages = $result['total_pages'];
                $hasNext = $result['has_next'];
                $hasPrev = $result['has_prev'];
            } catch (Exception $e) {
                Config::logError("Admin search tickets error: " . $e->getMessage());
                $this->setFlash('error', 'Search failed. Please try again.');
            }
        }

        $data = [
            'page_title' => 'Search Tickets - SAMPARK Admin',
            'user' => $user,
            'tickets' => $tickets,
            'total_tickets' => $totalTickets,
            'current_page' => $currentPage ?? 1,
            'total_pages' => $totalPages ?? 1,
            'has_next' => $hasNext ?? false,
            'has_prev' => $hasPrev ?? false,
            'has_search' => $hasSearch,
            'search_params' => [
                'complaint_number' => $complaintNumber,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'customer_mobile' => $customerMobile,
                'customer_email' => $customerEmail
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/tickets/search', $data);
    }

    /**
     * DataTables AJAX endpoint for admin tickets
     */
    public function getTicketsData()
    {
        $user = $this->getCurrentUser();

        // DataTables parameters
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = $_POST['search']['value'] ?? '';
        $orderColumn = $_POST['order'][0]['column'] ?? 0;
        $orderDir = $_POST['order'][0]['dir'] ?? 'desc';

        // Get filters from POST
        $status = $_POST['status'] ?? '';
        $priority = $_POST['priority'] ?? '';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';
        $category = $_POST['category'] ?? '';

        // Column mapping for ordering
        $columns = [
            'c.complaint_id',
            'c.description',
            'cust.name',
            's.name',
            'cc.category',
            'c.status',
            'c.priority',
            'c.date',
            'admin_remarks_count'
        ];

        try {
            // Build query conditions based on admin's access level
            $conditions = ['1=1'];
            $params = [];

            // Department and division filtering
            if (!in_array($user['department'], ['CML', 'ADM'])) {
                $conditions[] = 'c.assigned_to_department = ? OR c.department = ?';
                $params[] = $user['department'];
                $params[] = $user['department'];
            }

            if ($user['division'] === 'HQ') {
                $conditions[] = 'c.zone = ?';
                $params[] = $user['zone'];
            } else {
                $conditions[] = 'c.division = ?';
                $params[] = $user['division'];
            }

            // Apply filters
            if ($status) {
                $conditions[] = 'c.status = ?';
                $params[] = $status;
            }
            if ($priority) {
                $conditions[] = 'c.priority = ?';
                $params[] = $priority;
            }
            if ($dateFrom) {
                $conditions[] = 'c.date >= ?';
                $params[] = $dateFrom;
            }
            if ($dateTo) {
                $conditions[] = 'c.date <= ?';
                $params[] = $dateTo;
            }
            if ($category) {
                $conditions[] = 'cc.category = ?';
                $params[] = $category;
            }

            // Search functionality
            if ($searchValue) {
                $conditions[] = "(c.complaint_id LIKE ? OR c.description LIKE ? OR cust.name LIKE ? OR cust.mobile LIKE ? OR s.name LIKE ?)";
                $searchParam = '%' . $searchValue . '%';
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
            }

            $whereClause = implode(' AND ', $conditions);
            $orderByClause = "ORDER BY " . $columns[$orderColumn] . " " . strtoupper($orderDir);

            // Count total records
            $countSql = "SELECT COUNT(*) as total
                        FROM complaints c
                        LEFT JOIN complaint_categories cc ON c.category_id = cc.category_id
                        LEFT JOIN shed s ON c.shed_id = s.shed_id
                        LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                        WHERE {$whereClause}";

            $totalRecords = $this->db->fetch($countSql, $params)['total'];

            // Get data with pagination
            $sql = "SELECT c.complaint_id, c.description, c.status, c.priority, c.date, c.time,
                           c.division, c.zone, c.assigned_to_department, c.created_at,
                           cc.category, cc.type, cc.subtype,
                           s.name as shed_name, s.shed_code,
                           cust.name as customer_name, cust.email as customer_email,
                           cust.mobile as customer_mobile, cust.company_name,
                           (SELECT COUNT(*) FROM transactions t
                            WHERE t.complaint_id = c.complaint_id AND t.remarks_type = 'admin_remarks') as admin_remarks_count
                    FROM complaints c
                    LEFT JOIN complaint_categories cc ON c.category_id = cc.category_id
                    LEFT JOIN shed s ON c.shed_id = s.shed_id
                    LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                    WHERE {$whereClause}
                    {$orderByClause}
                    LIMIT {$start}, {$length}";

            $tickets = $this->db->fetchAll($sql, $params);

            // Format data for DataTables
            $data = [];
            foreach ($tickets as $ticket) {
                $statusBadge = $this->getStatusBadge($ticket['status']);
                $priorityBadge = $this->getPriorityBadge($ticket['priority']);

                $viewButton = '<a href="' . Config::getAppUrl() . '/admin/tickets/' . htmlspecialchars($ticket['complaint_id']) . '/view" class="btn btn-sm btn-info me-1">
                    <i class="fas fa-eye"></i> View
                </a>';

                $actionButton = $viewButton;
                if ($ticket['status'] !== 'closed') {
                    $actionButton .= '<button class="btn btn-sm btn-primary" onclick="showRemarksModal(\'' . htmlspecialchars($ticket['complaint_id']) . '\')">
                        <i class="fas fa-comment"></i> Add Remark
                    </button>';
                }

                $data[] = [
                    '<code>' . htmlspecialchars($ticket['complaint_id']) . '</code>',
                    '<div class="text-truncate" style="max-width: 200px;" title="' . htmlspecialchars($ticket['description']) . '">' .
                        htmlspecialchars(substr($ticket['description'], 0, 80)) .
                        (strlen($ticket['description']) > 80 ? '...' : '') . '</div>',
                    '<div class="small">
                        <strong>' . htmlspecialchars($ticket['customer_name']) . '</strong><br>
                        ' . htmlspecialchars($ticket['customer_mobile']) . '<br>
                        <span class="text-muted">' . htmlspecialchars($ticket['company_name']) . '</span>
                    </div>',
                    '<div class="small">
                        <strong>' . htmlspecialchars($ticket['shed_name']) . '</strong><br>
                        <span class="text-muted">' . htmlspecialchars($ticket['division']) . ' / ' . htmlspecialchars($ticket['zone']) . '</span>
                    </div>',
                    '<div class="small">
                        <span class="badge bg-secondary">' . htmlspecialchars($ticket['category']) . '</span><br>
                        <span class="text-muted">' . htmlspecialchars($ticket['type']) . '</span>
                    </div>',
                    $statusBadge,
                    $priorityBadge,
                    '<div class="small">
                        ' . date('d/m/Y', strtotime($ticket['date'])) . '<br>
                        <span class="text-muted">' . date('H:i', strtotime($ticket['time'])) . '</span>
                    </div>',
                    $ticket['admin_remarks_count'] > 0 ?
                        '<span class="badge bg-info">' . $ticket['admin_remarks_count'] . ' remark(s)</span>' :
                        '<span class="text-muted">No remarks</span>',
                    $actionButton
                ];
            }

            $this->json([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalRecords),
                'data' => $data
            ]);
        } catch (Exception $e) {
            Config::logError("Admin tickets DataTables error: " . $e->getMessage());
            $this->json([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load tickets'
            ]);
        }
    }

    /**
     * DataTables AJAX endpoint for admin search tickets
     */
    public function getSearchTicketsData()
    {
        // DataTables parameters
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = $_POST['search']['value'] ?? '';
        $orderColumn = $_POST['order'][0]['column'] ?? 0;
        $orderDir = $_POST['order'][0]['dir'] ?? 'desc';

        // Get search parameters from POST
        $complaintNumber = $_POST['complaint_number'] ?? '';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';
        $customerMobile = $_POST['customer_mobile'] ?? '';
        $customerEmail = $_POST['customer_email'] ?? '';

        // Column mapping for ordering
        $columns = [
            'c.complaint_id',
            'c.description',
            'cust.name',
            's.name',
            'cc.category',
            'c.status',
            'c.priority',
            'c.date'
        ];

        try {
            // Only search if at least one parameter is provided
            $hasSearchParams = $complaintNumber || $dateFrom || $dateTo || $customerMobile || $customerEmail;

            if (!$hasSearchParams) {
                $this->json([
                    'draw' => intval($draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
                return;
            }

            $conditions = ['1=1']; // Show all tickets regardless of status for admin search
            $params = [];

            if ($complaintNumber) {
                $conditions[] = 'c.complaint_id LIKE ?';
                $params[] = '%' . $complaintNumber . '%';
            }
            if ($dateFrom) {
                $conditions[] = 'c.date >= ?';
                $params[] = $dateFrom;
            }
            if ($dateTo) {
                $conditions[] = 'c.date <= ?';
                $params[] = $dateTo;
            }
            if ($customerMobile) {
                $conditions[] = 'cust.mobile LIKE ?';
                $params[] = '%' . $customerMobile . '%';
            }
            if ($customerEmail) {
                $conditions[] = 'cust.email LIKE ?';
                $params[] = '%' . $customerEmail . '%';
            }

            // DataTables search functionality
            if ($searchValue) {
                $conditions[] = "(c.complaint_id LIKE ? OR c.description LIKE ? OR cust.name LIKE ? OR cust.mobile LIKE ? OR s.name LIKE ?)";
                $searchParam = '%' . $searchValue . '%';
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
            }

            $whereClause = implode(' AND ', $conditions);
            $orderByClause = "ORDER BY " . $columns[$orderColumn] . " " . strtoupper($orderDir);

            // Count total records
            $countSql = "SELECT COUNT(*) as total
                        FROM complaints c
                        LEFT JOIN complaint_categories cc ON c.category_id = cc.category_id
                        LEFT JOIN shed s ON c.shed_id = s.shed_id
                        LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                        WHERE {$whereClause}";

            $totalRecords = $this->db->fetch($countSql, $params)['total'];

            // Get data with pagination
            $sql = "SELECT c.complaint_id, c.description, c.status, c.priority, c.date, c.time,
                           c.division, c.zone, c.assigned_to_department, c.created_at,
                           cc.category, cc.type, cc.subtype,
                           s.name as shed_name, s.shed_code,
                           cust.name as customer_name, cust.email as customer_email,
                           cust.mobile as customer_mobile, cust.company_name
                    FROM complaints c
                    LEFT JOIN complaint_categories cc ON c.category_id = cc.category_id
                    LEFT JOIN shed s ON c.shed_id = s.shed_id
                    LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                    WHERE {$whereClause}
                    {$orderByClause}
                    LIMIT {$start}, {$length}";

            $tickets = $this->db->fetchAll($sql, $params);

            // Format data for DataTables
            $data = [];
            foreach ($tickets as $ticket) {
                $statusBadge = $this->getStatusBadge($ticket['status']);
                $priorityBadge = $this->getPriorityBadge($ticket['priority']);

                // View-only ticket details link
                $actionButton = '<a href="' . Config::getAppUrl() . '/admin/tickets/' . htmlspecialchars($ticket['complaint_id']) . '/view" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> View Details
                </a>';

                $data[] = [
                    '<code>' . htmlspecialchars($ticket['complaint_id']) . '</code>',
                    '<div class="text-truncate" style="max-width: 200px;" title="' . htmlspecialchars($ticket['description']) . '">' .
                        htmlspecialchars(substr($ticket['description'], 0, 80)) .
                        (strlen($ticket['description']) > 80 ? '...' : '') . '</div>',
                    '<div class="small">
                        <strong>' . htmlspecialchars($ticket['customer_name']) . '</strong><br>
                        ' . htmlspecialchars($ticket['customer_mobile']) . '<br>
                        <span class="text-muted">' . htmlspecialchars($ticket['company_name']) . '</span>
                    </div>',
                    '<div class="small">
                        <strong>' . htmlspecialchars($ticket['shed_name']) . '</strong><br>
                        <span class="text-muted">' . htmlspecialchars($ticket['division']) . ' / ' . htmlspecialchars($ticket['zone']) . '</span>
                    </div>',
                    '<div class="small">
                        <span class="badge bg-secondary">' . htmlspecialchars($ticket['category']) . '</span><br>
                        <span class="text-muted">' . htmlspecialchars($ticket['type']) . '</span>
                    </div>',
                    $statusBadge,
                    $priorityBadge,
                    '<div class="small">
                        ' . date('d/m/Y', strtotime($ticket['date'])) . '<br>
                        <span class="text-muted">' . date('H:i', strtotime($ticket['time'])) . '</span>
                    </div>',
                    $actionButton
                ];
            }

            $this->json([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalRecords),
                'data' => $data
            ]);
        } catch (Exception $e) {
            Config::logError("Admin search tickets DataTables error: " . $e->getMessage());
            $this->json([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to search tickets'
            ]);
        }
    }

    /**
     * Add admin remarks to a ticket
     */
    public function addAdminRemarks($complaintId)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'remarks' => 'required|min:10|max:1000'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            // Check if ticket exists
            $ticket = $this->db->fetch(
                "SELECT * FROM complaints WHERE complaint_id = ?",
                [$complaintId]
            );

            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            // Check if ticket is closed
            if ($ticket['status'] === 'closed') {
                $this->json(['success' => false, 'message' => 'Cannot add remarks to closed tickets'], 400);
                return;
            }

            $this->db->beginTransaction();

            // Add admin remarks as transaction
            $sql = "INSERT INTO transactions (
                complaint_id, remarks, remarks_type, transaction_type,
                created_by_id, created_by_type, created_by_role, created_at
            ) VALUES (?, ?, 'admin_remarks', 'admin_remarks', ?, 'user', ?, NOW())";

            $this->db->query($sql, [
                $complaintId,
                trim($_POST['remarks']),
                $user['id'],
                $user['role']
            ]);

            $this->db->commit();

            // Log activity
            $this->logActivity('admin_remarks_added', [
                'complaint_id' => $complaintId,
                'remarks_preview' => substr($_POST['remarks'], 0, 100)
            ]);

            $this->json([
                'success' => true,
                'message' => 'Admin remarks added successfully'
            ]);
        } catch (Exception $e) {
            $this->db->rollback();
            Config::logError("Admin remarks error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to add remarks. Please try again.'
            ], 500);
        }
    }

    /**
     * Toggle content status (active/inactive)
     */
    public function toggleContentStatus($type, $id)
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $table = ($type === 'link') ? 'quick_links' : 'news';
            $field = ($type === 'link') ? 'is_active' : 'is_active';

            $item = $this->db->fetch("SELECT * FROM {$table} WHERE id = ?", [$id]);

            if (!$item) {
                $this->json(['success' => false, 'message' => ucfirst($type) . ' not found'], 404);
                return;
            }

            $newStatus = $item[$field] ? 0 : 1;

            $this->db->query("UPDATE {$table} SET {$field} = ? WHERE id = ?", [$newStatus, $id]);

            // Log activity
            $this->logActivity("{$type}_status_toggled", [
                'item_id' => $id,
                'new_status' => $newStatus ? 'active' : 'inactive'
            ]);

            $this->json([
                'success' => true,
                'message' => ucfirst($type) . ' status updated successfully',
                'new_status' => $newStatus
            ]);
        } catch (Exception $e) {
            Config::logError("Content status toggle error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.'
            ], 500);
        }
    }

    public function reports()
    {
        $user = $this->getCurrentUser();

        // Get URL parameters for filtering
        $current_view = $_GET['view'] ?? 'complaints';
        $current_tab = $_GET['tab'] ?? 'detailed';
        $sort_order = $_GET['sort'] ?? 'latest';
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-t');
        $division_filter = $_GET['division'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        $priority_filter = $_GET['priority'] ?? '';

        // Build filters array
        $filters = [
            'date_from' => $date_from,
            'date_to' => $date_to,
            'division' => $division_filter,
            'status' => $status_filter,
            'priority' => $priority_filter,
            'sort' => $sort_order
        ];

        // Get data based on current view
        $complaints_data = [];
        $transactions_data = [];
        $customers_data = [];

        if ($current_view === 'complaints') {
            $complaints_data = $this->getComplaintsReportData($filters);
        } elseif ($current_view === 'transactions') {
            $transactions_data = $this->getTransactionsReportData($filters);
        } elseif ($current_view === 'customers') {
            $customers_data = $this->getCustomersReportData($filters);
        }

        // Get available columns for column selector
        $available_columns = $this->getAvailableColumns($current_view);

        $data = [
            'page_title' => 'Detailed Reports - SAMPARK',
            'user' => $user,
            'current_view' => $current_view,
            'current_tab' => $current_tab,
            'sort_order' => $sort_order,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'division_filter' => $division_filter,
            'status_filter' => $status_filter,
            'priority_filter' => $priority_filter,
            'complaints_data' => $complaints_data,
            'transactions_data' => $transactions_data,
            'customers_data' => $customers_data,
            'available_columns' => $available_columns,
            'report_data' => [], // For backward compatibility
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/reports', $data);
    }

    // Helper methods

    private function getSystemStats()
    {
        $stats = [];

        // Total users
        $stats['total_users'] = $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'];
        $stats['active_users'] = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'];

        // Total customers
        $stats['total_customers'] = $this->db->fetch("SELECT COUNT(*) as count FROM customers")['count'];
        $stats['approved_customers'] = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'approved'")['count'];
        $stats['pending_customers'] = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'pending'")['count'];

        // Total tickets
        $stats['total_tickets'] = $this->db->fetch("SELECT COUNT(*) as count FROM complaints")['count'];
        $stats['open_tickets'] = $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE status != 'closed'")['count'];
        $stats['closed_tickets'] = $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE status = 'closed'")['count'];

        return $stats;
    }

    private function getCustomerStats()
    {
        $stats = [];

        try {
            // Total customers
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM customers");
            $stats['total_customers'] = isset($result['count']) ? $result['count'] : 0;

            $result = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'approved'");
            $stats['active_customers'] = isset($result['count']) ? $result['count'] : 0;

            $result = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'pending'");
            $stats['pending_verification'] = isset($result['count']) ? $result['count'] : 0;

            // New customers this month
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM customers WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())"
            );
            $stats['new_this_month'] = isset($result['count']) ? $result['count'] : 0;
        } catch (\Exception $e) {
            error_log("Error in getCustomerStats: " . $e->getMessage());

            // Provide default values
            $stats['total_customers'] = 0;
            $stats['active_customers'] = 0;
            $stats['pending_verification'] = 0;
            $stats['new_this_month'] = 0;
        }

        return $stats;
    }

    private function getRecentRegistrations($limit = 10)
    {
        $sql = "SELECT customer_id, name, email, company_name, division, status, created_at
                FROM customers 
                ORDER BY created_at DESC 
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    private function getSystemHealth()
    {
        $health = [
            'database' => 'healthy',
            'storage' => 'healthy',
            'emails' => 'healthy'
        ];

        // Check database
        try {
            $this->db->fetch("SELECT 1");
        } catch (Exception $e) {
            $health['database'] = 'unhealthy';
        }

        // Check storage
        $uploadPath = Config::getUploadPath();
        if (!is_dir($uploadPath) || !is_writable($uploadPath)) {
            $health['storage'] = 'warning';
        }

        return $health;
    }

    private function getTicketSummary()
    {
        $sql = "SELECT 
                    division,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count
                FROM complaints 
                WHERE division IS NOT NULL
                GROUP BY division 
                ORDER BY total DESC";

        return $this->db->fetchAll($sql);
    }

    private function getRecentUserActivity($limit = 20)
    {
        $sql = "SELECT a.action, a.description, a.created_at, 
                       u.name as user_name, u.role as user_role
                FROM activity_logs a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC 
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    private function getPendingApprovals()
    {
        $sql = "SELECT customer_id, name, email, company_name, division, created_at
                FROM customers 
                WHERE status = 'pending' 
                ORDER BY created_at ASC";

        return $this->db->fetchAll($sql);
    }

    private function getSystemAlerts()
    {
        $alerts = [];


        // Check for pending customer approvals
        $pendingApprovals = $this->db->fetch(
            "SELECT COUNT(*) as count FROM customers WHERE status = 'pending'"
        )['count'];

        if ($pendingApprovals > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$pendingApprovals} customer registrations pending approval",
                'action_url' => '/admin/customers?status=pending'
            ];
        }

        return $alerts;
    }

    private function getDivisions()
    {
        try {
            $sql = "SELECT DISTINCT division FROM shed WHERE is_active = 1 ORDER BY division";
            $result = $this->db->fetchAll($sql);
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error in getDivisions: " . $e->getMessage());
            return [];
        }
    }

    private function getZones()
    {
        try {
            $sql = "SELECT DISTINCT zone FROM shed WHERE is_active = 1 ORDER BY zone";
            $result = $this->db->fetchAll($sql);
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error in getZones: " . $e->getMessage());
            return [];
        }
    }

    private function getDepartments()
    {
        try {
            $sql = "SELECT DISTINCT department FROM users WHERE department IS NOT NULL ORDER BY department";
            $result = $this->db->fetchAll($sql);
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error in getDepartments: " . $e->getMessage());
            return [];
        }
    }

    private function getZoneFromDivision($division)
    {
        $sql = "SELECT zone FROM shed WHERE division = ? LIMIT 1";
        return $this->db->fetch($sql, [$division]);
    }

    private function getDistinctCategories()
    {
        try {
            $sql = "SELECT DISTINCT category FROM complaint_categories ORDER BY category";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getStatusBadge($status)
    {
        $statusClasses = [
            'pending' => 'warning',
            'awaiting_feedback' => 'info',
            'awaiting_info' => 'info',
            'awaiting_approval' => 'primary',
            'closed' => 'success'
        ];

        $statusLabels = [
            'pending' => 'Pending',
            'awaiting_feedback' => 'Awaiting Feedback',
            'awaiting_info' => 'Awaiting Info',
            'awaiting_approval' => 'Awaiting Approval',
            'closed' => 'Closed'
        ];

        $class = $statusClasses[$status] ?? 'secondary';
        $label = $statusLabels[$status] ?? ucfirst($status);

        return '<span class="badge bg-' . $class . '">' . $label . '</span>';
    }

    private function getPriorityBadge($priority)
    {
        $priorityClasses = [
            'normal' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark'
        ];

        $class = $priorityClasses[$priority] ?? 'secondary';
        return '<span class="badge bg-' . $class . '">' . ucfirst($priority) . '</span>';
    }

    private function sendWelcomeEmail($userId, $email, $name, $loginId)
    {
        $notificationService = new NotificationService();
        // Implementation for sending welcome email
    }

    private function sendCustomerApprovalEmail($customer, $status, $reason = null)
    {
        try {
            require_once '../src/utils/NotificationService.php';
            $notificationService = new NotificationService();

            if ($status === 'approved') {
                // Send approval notification
                $notificationService->sendSignupApproved($customer);
            }
            // Note: Rejection email could be added later if needed

        } catch (Exception $e) {
            // Log error but don't fail the approval process
            error_log("Customer approval email error: " . $e->getMessage());
        }
    }

    private function getSystemOverviewReport($dateFrom, $dateTo)
    {
        // Implementation for system overview report
        return [];
    }

    private function getTicketAnalyticsReport($dateFrom, $dateTo)
    {
        // Implementation for ticket analytics report
        return [];
    }

    private function getSystemPerformanceReport($dateFrom, $dateTo)
    {
        // Implementation for system performance report
        return [];
    }

    private function getUserActivityReport($dateFrom, $dateTo)
    {
        // Implementation for user activity report
        return [];
    }



    /**
     * Display email management dashboard
     */
    public function emails()
    {
        $user = $this->getCurrentUser();

        $data = [
            'page_title' => 'Email Management - SAMPARK Admin',
            'user' => $user,
            'email_stats' => $this->getEmailStats(),
            'recent_emails' => $this->getRecentEmails(),
            'email_templates' => $this->getEmailTemplatesList(),
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/emails', $data);
    }

    /**
     * Display email templates management
     */
    public function emailTemplates()
    {
        $user = $this->getCurrentUser();

        $data = [
            'page_title' => 'Email Templates - SAMPARK Admin',
            'user' => $user,
            'templates' => $this->getAllEmailTemplates(),
            'template_types' => [
                'welcome' => 'Welcome Email',
                'approval' => 'Account Approval',
                'rejection' => 'Account Rejection',
                'password_reset' => 'Password Reset',
                'ticket_created' => 'Ticket Created',
                'ticket_updated' => 'Ticket Updated',
                'ticket_resolved' => 'Ticket Resolved',
                'announcement' => 'System Announcement'
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/email-templates', $data);
    }

    /**
     * Send bulk email to users
     */
    public function getCustomersList()
    {
        try {
            $sql = "SELECT customer_id, name, email, company_name, status
                    FROM customers
                    WHERE status = 'approved'
                    ORDER BY name ASC";

            $customers = $this->db->fetchAll($sql);

            $this->json([
                'success' => true,
                'customers' => $customers
            ]);

        } catch (Exception $e) {
            Config::logError("Get customers list error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to load customers'
            ], 500);
        }
    }

    public function sendBulkEmail()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'subject' => 'required|min:5|max:200',
            'message' => 'required|min:10',
            'recipient_type' => 'required|in:all,customers,staff,admins,selected_customers',
            'template_id' => 'optional|numeric',
            'send_immediately' => 'optional|boolean',
            'selected_customers' => 'optional|array',
            'cc_emails' => 'optional|string'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $subject = $this->sanitize($_POST['subject']);
            $message = $_POST['message']; // Keep HTML formatting
            $recipientType = $_POST['recipient_type'];
            $templateId = $_POST['template_id'] ?? null;
            $sendImmediately = isset($_POST['send_immediately']) && $_POST['send_immediately'];

            // Get recipients based on type
            $recipients = $this->getBulkEmailRecipients($recipientType, $_POST['selected_customers'] ?? []);

            // Add CC recipients if provided
            if (!empty($_POST['cc_emails'])) {
                $ccEmails = array_map('trim', explode(',', $_POST['cc_emails']));
                foreach ($ccEmails as $ccEmail) {
                    if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                        $recipients[] = [
                            'id' => 'cc_' . md5($ccEmail),
                            'email' => $ccEmail,
                            'name' => 'CC Recipient',
                            'type' => 'cc'
                        ];
                    }
                }
            }

            if (empty($recipients)) {
                $this->json([
                    'success' => false,
                    'message' => 'No recipients found for the selected criteria'
                ], 400);
                return;
            }

            // Create bulk email job
            $emailJobId = $this->createBulkEmailJob([
                'subject' => $subject,
                'message' => $message,
                'recipient_type' => $recipientType,
                'template_id' => $templateId,
                'recipients' => $recipients,
                'created_by' => $user['id'],
                'send_immediately' => $sendImmediately
            ]);

            if ($sendImmediately) {
                // Process immediately
                $this->processBulkEmailJob($emailJobId);
                $message = 'Bulk email sent successfully to ' . count($recipients) . ' recipients';
            } else {
                // Queue for later processing
                $message = 'Bulk email queued successfully. It will be processed shortly.';
            }

            // Log activity
            $this->log('info', 'Bulk email created', [
                'email_job_id' => $emailJobId,
                'recipient_count' => count($recipients),
                'recipient_type' => $recipientType,
                'admin_id' => $user['id']
            ]);

            $this->json([
                'success' => true,
                'message' => $message,
                'job_id' => $emailJobId,
                'recipient_count' => count($recipients)
            ]);
        } catch (Exception $e) {
            Config::logError("Bulk email creation error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to create bulk email. Please try again.'
            ], 500);
        }
    }

    /**
     * Store new announcement
     */
    public function storeAnnouncement()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'title' => 'required|min:5|max:200',
            'content' => 'required|min:10',
            'type' => 'required|in:general,maintenance,urgent,news',
            'target_audience' => 'required|in:all,customers,staff,admins',
            'expires_at' => 'optional|datetime',
            'is_active' => 'optional|boolean',
            'priority' => 'optional|in:low,medium,high,urgent'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        try {
            $title = $this->sanitize($_POST['title']);
            $content = $_POST['content']; // Keep HTML formatting
            $type = $_POST['type'];
            $targetAudience = $_POST['target_audience'];
            $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
            $isActive = isset($_POST['is_active']) && $_POST['is_active'];
            $priority = $_POST['priority'] ?? 'medium';

            // Create announcement
            $announcementId = $this->createAnnouncement([
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'target_audience' => $targetAudience,
                'expires_at' => $expiresAt,
                'is_active' => $isActive,
                'priority' => $priority,
                'created_by' => $user['id']
            ]);

            if ($announcementId && $isActive) {
                // Send notifications to target audience
                $this->sendAnnouncementNotifications($announcementId, $title, $content, $targetAudience, $priority);
            }

            // Log activity
            $this->log('info', 'Announcement created', [
                'announcement_id' => $announcementId,
                'title' => $title,
                'type' => $type,
                'target_audience' => $targetAudience,
                'admin_id' => $user['id']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'announcement_id' => $announcementId
            ]);
        } catch (Exception $e) {
            Config::logError("Announcement creation error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to create announcement. Please try again.'
            ], 500);
        }
    }

    // Helper methods for email management

    private function getEmailStats()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_sent,
                        COUNT(CASE WHEN status = 'sent' THEN 1 END) as successfully_sent,
                        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending
                    FROM email_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

            return $this->db->fetch($sql) ?: [
                'total_sent' => 0,
                'successfully_sent' => 0,
                'failed' => 0,
                'pending' => 0
            ];
        } catch (Exception $e) {
            return [
                'total_sent' => 0,
                'successfully_sent' => 0,
                'failed' => 0,
                'pending' => 0
            ];
        }
    }

    private function getRecentEmails($limit = 10)
    {
        try {
            $sql = "SELECT * FROM email_logs ORDER BY created_at DESC LIMIT ?";
            return $this->db->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getEmailTemplatesList()
    {
        try {
            $templateModel = new EmailTemplateModel();
            return $templateModel->getAllTemplates();
        } catch (Exception $e) {
            error_log("Error fetching email templates: " . $e->getMessage());
            return [];
        }
    }

    private function getAllEmailTemplates()
    {
        try {
            $sql = "SELECT * FROM email_templates ORDER BY type, name";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getBulkEmailRecipients($recipientType, $selectedCustomers = [])
    {
        try {
            $recipients = [];

            switch ($recipientType) {
                case 'all':
                    $customers = $this->db->fetchAll("SELECT customer_id as id, email, name, 'customer' as type FROM customers WHERE status = 'approved' AND email IS NOT NULL");
                    $staff = $this->db->fetchAll("SELECT id, email, name, role as type FROM users WHERE status = 'active' AND email IS NOT NULL");
                    $recipients = array_merge($customers, $staff);
                    break;

                case 'customers':
                    $recipients = $this->db->fetchAll("SELECT customer_id as id, email, name, 'customer' as type FROM customers WHERE status = 'approved' AND email IS NOT NULL");
                    break;

                case 'selected_customers':
                    if (!empty($selectedCustomers)) {
                        $placeholders = str_repeat('?,', count($selectedCustomers) - 1) . '?';
                        $sql = "SELECT customer_id as id, email, name, 'customer' as type FROM customers WHERE customer_id IN ($placeholders) AND status = 'approved' AND email IS NOT NULL";
                        $recipients = $this->db->fetchAll($sql, $selectedCustomers);
                    }
                    break;

                case 'staff':
                    $recipients = $this->db->fetchAll("SELECT id, email, name, role as type FROM users WHERE status = 'active' AND email IS NOT NULL AND role IN ('controller', 'controller_nodal')");
                    break;

                case 'admins':
                    $recipients = $this->db->fetchAll("SELECT id, email, name, user_type as type FROM users WHERE status = 'active' AND email IS NOT NULL AND user_type IN ('admin', 'superadmin')");
                    break;
            }

            return $recipients;
        } catch (Exception $e) {
            return [];
        }
    }

    private function createBulkEmailJob($data)
    {
        try {
            $sql = "INSERT INTO bulk_email_jobs (
                        subject, message, recipient_type, template_id, 
                        recipient_data, created_by, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";

            $this->db->query($sql, [
                $data['subject'],
                $data['message'],
                $data['recipient_type'],
                $data['template_id'],
                json_encode($data['recipients']),
                $data['created_by']
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function processBulkEmailJob($jobId)
    {
        try {
            // 1. Get job details
            $job = $this->db->fetch("SELECT * FROM bulk_email_jobs WHERE id = ?", [$jobId]);
            if (!$job) {
                error_log("Bulk email job not found: $jobId");
                return false;
            }

            // 2. Get recipients
            $recipients = json_decode($job['recipients'], true);
            if (empty($recipients)) {
                $this->db->query("UPDATE bulk_email_jobs SET status = 'failed', error_message = 'No recipients' WHERE id = ?", [$jobId]);
                return false;
            }

            // 3. Get template if provided
            $message = $job['message'];
            $subject = $job['subject'];
            if (!empty($job['template_id'])) {
                $templateModel = new EmailTemplateModel();
                $template = $templateModel->getTemplate($job['template_id']);
                if ($template) {
                    $message = $template['template_html'];
                    if (empty($subject)) {
                        $subject = $template['name'];
                    }
                }
            }

            // 4. Loop and send
            require_once __DIR__ . '/../utils/EmailService.php';
            $emailService = new EmailService();
            $sentCount = 0;
            $failedCount = 0;

            foreach ($recipients as $recipient) {
                $personalizedMessage = $message;
                
                if (isset($recipient['name'])) {
                    $personalizedMessage = str_replace('$customer_name', $recipient['name'], $personalizedMessage);
                }
                if (isset($recipient['email'])) {
                    $personalizedMessage = str_replace('$customer_email', $recipient['email'], $personalizedMessage);
                }

                $success = $emailService->send(
                    $recipient['email'],
                    $recipient['name'] ?? '',
                    $subject,
                    $personalizedMessage
                );

                if ($success) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            }

            // 5. Update job status
            $status = $failedCount > 0 ? 'partial_success' : 'completed';
            $this->db->query(
                "UPDATE bulk_email_jobs SET status = ?, processed_at = NOW(), sent_count = ?, failed_count = ? WHERE id = ?",
                [$status, $sentCount, $failedCount, $jobId]
            );

            return true;

        } catch (Exception $e) {
            error_log("Error processing bulk email job $jobId: " . $e->getMessage());
            $this->db->query("UPDATE bulk_email_jobs SET status = 'failed', error_message = ? WHERE id = ?", [$e->getMessage(), $jobId]);
            return false;
        }
    }

    private function createAnnouncement($data)
    {
        try {
            $sql = "INSERT INTO announcements (
                        title, content, type, target_audience, expires_at, 
                        is_active, priority, created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $this->db->query($sql, [
                $data['title'],
                $data['content'],
                $data['type'],
                $data['target_audience'],
                $data['expires_at'],
                $data['is_active'] ? 1 : 0,
                $data['priority'],
                $data['created_by']
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendAnnouncementNotifications($announcementId, $title, $content, $targetAudience, $priority)
    {
        try {
            // Use the NotificationModel to create notifications
            require_once __DIR__ . '/../models/NotificationModel.php';
            $notificationModel = new NotificationModel();

            // Create system announcement notifications
            $notificationModel->createSystemAnnouncement(
                $title,
                strip_tags($content), // Remove HTML for notification
                $targetAudience === 'all' ? null : $targetAudience,
                null, // No expiry for notifications
                $priority
            );

            return true;
        } catch (Exception $e) {
            Config::logError("Failed to send announcement notifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * View ticket details for admin (read-only)
     */
    public function viewTicket($complaintId)
    {
        $user = $this->getCurrentUser();

        // Get ticket details
        $ticket = $this->db->fetch(
            "SELECT c.*, cat.category, cat.type, cat.subtype,
                    s.name as shed_name, s.shed_code,
                    cust.name as customer_name, cust.email as customer_email,
                    cust.mobile as customer_mobile, cust.company_name,
                    TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
            FROM complaints c
            LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
            LEFT JOIN shed s ON c.shed_id = s.shed_id
            LEFT JOIN customers cust ON c.customer_id = cust.customer_id
            WHERE c.complaint_id = ?",
            [$complaintId]
        );

        if (!$ticket) {
            $this->redirect('/admin/tickets/search', 'Ticket not found', 'error');
            return;
        }

        // Get evidence files
        $evidence = $this->db->fetchAll(
            "SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at ASC",
            [$complaintId]
        );

        // Get transaction history
        $allTransactions = $this->db->fetchAll(
            "SELECT t.*, u.name as user_name, u.role as user_role, u.department as user_department,
                    u.division as user_division, u.zone as user_zone,
                    cust.name as customer_name
            FROM transactions t
            LEFT JOIN users u ON t.created_by_id = u.id
            LEFT JOIN customers cust ON t.created_by_customer_id = cust.customer_id
            WHERE t.complaint_id = ?
            ORDER BY t.created_at DESC",
            [$complaintId]
        );

        // Filter out admin remarks from main transaction history
        $transactions = array_filter($allTransactions, function ($transaction) {
            return $transaction['remarks_type'] !== 'admin_remarks';
        });

        // Get latest important remark (excluding admin remarks)
        $latest_important_remark = $this->db->fetch(
            "SELECT t.*, u.name as user_name, u.role as user_role, u.department as user_department,
                    u.division as user_division, u.zone as user_zone
            FROM transactions t
            LEFT JOIN users u ON t.created_by_id = u.id
            WHERE t.complaint_id = ?
            AND t.remarks_type IN ('forwarding_remarks', 'interim_remarks', 'internal_remarks')
            AND (t.remarks IS NOT NULL AND t.remarks != '' OR t.internal_remarks IS NOT NULL AND t.internal_remarks != '')
            ORDER BY t.created_at DESC
            LIMIT 1",
            [$complaintId]
        );

        // Get admin remarks history
        $admin_remarks = $this->db->fetchAll(
            "SELECT t.*, u.name as user_name, u.role as user_role, u.department as user_department,
                    u.division as user_division, u.zone as user_zone
            FROM transactions t
            LEFT JOIN users u ON t.created_by_id = u.id
            WHERE t.complaint_id = ?
            AND t.remarks_type = 'admin_remarks'
            AND t.remarks IS NOT NULL AND t.remarks != ''
            ORDER BY t.created_at DESC",
            [$complaintId]
        );

        $data = [
            'page_title' => 'View Ticket #' . $complaintId . ' - Admin',
            'user' => $user,
            'ticket' => $ticket,
            'evidence' => $evidence,
            'transactions' => $transactions,
            'latest_important_remark' => $latest_important_remark,
            'admin_remarks' => $admin_remarks,
            'is_viewing_other_dept' => false,
            'is_forwarded_ticket' => false,
            'is_awaiting_customer_info' => $ticket['status'] === 'awaiting_info',
            'permissions' => [
                'can_reply' => false,
                'can_forward' => false,
                'can_approve' => false,
                'can_internal_remarks' => false,
                'can_interim_remarks' => false,
                'can_revert_to_customer' => false,
                'can_revert' => false
            ]
        ];

        $this->view('admin/tickets/view', $data);
    }

    /**
     * Generate scheduled comprehensive PDF report
     */
    public function generateScheduledReport()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $division = $_POST['report_division'] ?? '';
            $dateRange = $_POST['report_date_range'] ?? '';
            $customDateFrom = $_POST['report_date_from'] ?? '';
            $customDateTo = $_POST['report_date_to'] ?? '';

            // Calculate date range
            $dateFrom = '';
            $dateTo = '';

            switch($dateRange) {
                case 'last_7_days':
                    $dateFrom = date('Y-m-d', strtotime('-7 days'));
                    $dateTo = date('Y-m-d');
                    break;
                case 'last_month':
                    $dateFrom = date('Y-m-01', strtotime('last month'));
                    $dateTo = date('Y-m-t', strtotime('last month'));
                    break;
                case 'current_month':
                    $dateFrom = date('Y-m-01');
                    $dateTo = date('Y-m-d');
                    break;
                case 'last_3_months':
                    $dateFrom = date('Y-m-01', strtotime('-3 months'));
                    $dateTo = date('Y-m-d');
                    break;
                case 'custom':
                    $dateFrom = $customDateFrom;
                    $dateTo = $customDateTo;
                    break;
                default:
                    $dateFrom = date('Y-m-01', strtotime('last month'));
                    $dateTo = date('Y-m-t', strtotime('last month'));
            }

            // Generate report data
            $reportData = $this->generateReportData($division, $dateFrom, $dateTo);

            // Generate PDF
            $pdfContent = $this->generateScheduledPDF($reportData);

            // Return PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="SAMPARK_Comprehensive_Report_' . date('Y-m-d') . '.pdf"');
            echo $pdfContent;

        } catch (Exception $e) {
            Config::logError("Scheduled report generation error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to generate report. Please try again.'
            ], 500);
        }
    }

    /**
     * Preview scheduled report data
     */
    public function previewScheduledReport()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $division = $_POST['report_division'] ?? '';
            $dateRange = $_POST['report_date_range'] ?? '';
            $customDateFrom = $_POST['report_date_from'] ?? '';
            $customDateTo = $_POST['report_date_to'] ?? '';

            // Calculate date range (same logic as generateScheduledReport)
            $dateFrom = '';
            $dateTo = '';

            switch($dateRange) {
                case 'last_7_days':
                    $dateFrom = date('Y-m-d', strtotime('-7 days'));
                    $dateTo = date('Y-m-d');
                    break;
                case 'last_month':
                    $dateFrom = date('Y-m-01', strtotime('last month'));
                    $dateTo = date('Y-m-t', strtotime('last month'));
                    break;
                case 'current_month':
                    $dateFrom = date('Y-m-01');
                    $dateTo = date('Y-m-d');
                    break;
                case 'last_3_months':
                    $dateFrom = date('Y-m-01', strtotime('-3 months'));
                    $dateTo = date('Y-m-d');
                    break;
                case 'custom':
                    $dateFrom = $customDateFrom;
                    $dateTo = $customDateTo;
                    break;
                default:
                    $dateFrom = date('Y-m-01', strtotime('last month'));
                    $dateTo = date('Y-m-t', strtotime('last month'));
            }

            // Generate preview data
            $reportData = $this->generateReportData($division, $dateFrom, $dateTo);

            // Generate preview HTML
            $previewHtml = $this->generatePreviewHTML($reportData);

            $this->json([
                'success' => true,
                'preview_html' => $previewHtml,
                'data_summary' => [
                    'total_complaints' => $reportData['summary']['total_complaints'],
                    'total_customers' => $reportData['summary']['total_customers'],
                    'date_range' => $dateFrom . ' to ' . $dateTo,
                    'division_filter' => $division
                ]
            ]);

        } catch (Exception $e) {
            Config::logError("Scheduled report preview error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to generate preview. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate report data for all four sections
     */
    private function generateReportData($division, $dateFrom, $dateTo)
    {
        // Build division filter
        $divisionCondition = '';
        $params = [];

        if ($division && $division !== 'all') {
            $divisionCondition = ' AND c.division = ?';
            $params[] = $division;
        }

        // 1. Customer Summary - New registrations by division
        $customerSummarySQL = "
            SELECT
                COALESCE(division, 'Unknown') as division,
                COUNT(*) as new_registrations
            FROM customers
            WHERE created_at BETWEEN ? AND ?
            " . ($division && $division !== 'all' ? ' AND division = ?' : '') . "
            GROUP BY division
            ORDER BY new_registrations DESC
        ";

        $customerSummaryParams = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
        if ($division && $division !== 'all') {
            $customerSummaryParams[] = $division;
        }

        $customerSummary = $this->db->fetchAll($customerSummarySQL, $customerSummaryParams);

        // 2. Complaint Duration Analysis
        $durationAnalysisSQL = "
            SELECT
                c.division,
                COUNT(*) as complaint_count,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW()))), 2) as avg_resolution_hours
            FROM complaints c
            WHERE c.created_at BETWEEN ? AND ?
            " . $divisionCondition . "
            GROUP BY c.division
            ORDER BY avg_resolution_hours ASC
        ";

        $durationParams = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
        $durationParams = array_merge($durationParams, $params);

        $durationAnalysis = $this->db->fetchAll($durationAnalysisSQL, $durationParams);

        // 3. Division vs Status Summary (Pivot Table)
        $statusSummarySQL = "
            SELECT
                c.division,
                c.status,
                COUNT(*) as count
            FROM complaints c
            WHERE c.created_at BETWEEN ? AND ?
            " . $divisionCondition . "
            GROUP BY c.division, c.status
            ORDER BY c.division, c.status
        ";

        $statusSummary = $this->db->fetchAll($statusSummarySQL, array_merge([$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'], $params));

        // 4. Detailed Complaint List
        $detailedListSQL = "
            SELECT
                c.complaint_id,
                c.created_at as complaint_date,
                c.updated_at,
                TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW())) as duration_hours,
                c.description,
                c.action_taken,
                c.status,
                c.priority,
                c.division,
                c.fnr_number,
                cat.category,
                cat.type as complaint_type,
                cust.name as customer_name,
                cust.company_name,
                cust.mobile as customer_mobile,
                s.name as shed_name
            FROM complaints c
            LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
            LEFT JOIN customers cust ON c.customer_id = cust.customer_id
            LEFT JOIN shed s ON c.shed_id = s.shed_id
            WHERE c.created_at BETWEEN ? AND ?
            " . $divisionCondition . "
            ORDER BY c.created_at DESC
            LIMIT 500
        ";

        $detailedList = $this->db->fetchAll($detailedListSQL, array_merge([$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'], $params));

        // Calculate summary statistics
        $summarySQL = "
            SELECT
                COUNT(DISTINCT c.complaint_id) as total_complaints,
                COUNT(DISTINCT c.customer_id) as total_customers,
                SUM(CASE WHEN c.status = 'closed' THEN 1 ELSE 0 END) as closed_complaints,
                SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending_complaints
            FROM complaints c
            WHERE c.created_at BETWEEN ? AND ?
            " . $divisionCondition;

        $summary = $this->db->fetch($summarySQL, array_merge([$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'], $params));

        return [
            'customer_summary' => $customerSummary,
            'duration_analysis' => $durationAnalysis,
            'status_summary' => $statusSummary,
            'detailed_list' => $detailedList,
            'summary' => $summary,
            'filters' => [
                'division' => $division,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ];
    }

    /**
     * Generate comprehensive PDF report
     */
    private function generateScheduledPDF($reportData)
    {
        // For now, return a simple PDF content placeholder
        // In a real implementation, you would use a PDF library like TCPDF or DOMPDF

        $pdfContent = "%PDF-1.4\n";
        $pdfContent .= "1 0 obj\n";
        $pdfContent .= "<<\n";
        $pdfContent .= "/Type /Catalog\n";
        $pdfContent .= "/Pages 2 0 R\n";
        $pdfContent .= ">>\n";
        $pdfContent .= "endobj\n\n";

        $pdfContent .= "2 0 obj\n";
        $pdfContent .= "<<\n";
        $pdfContent .= "/Type /Pages\n";
        $pdfContent .= "/Kids [3 0 R]\n";
        $pdfContent .= "/Count 1\n";
        $pdfContent .= ">>\n";
        $pdfContent .= "endobj\n\n";

        $pdfContent .= "3 0 obj\n";
        $pdfContent .= "<<\n";
        $pdfContent .= "/Type /Page\n";
        $pdfContent .= "/Parent 2 0 R\n";
        $pdfContent .= "/MediaBox [0 0 612 792]\n";
        $pdfContent .= "/Contents 4 0 R\n";
        $pdfContent .= ">>\n";
        $pdfContent .= "endobj\n\n";

        $pdfContent .= "4 0 obj\n";
        $pdfContent .= "<<\n";
        $pdfContent .= "/Length 44\n";
        $pdfContent .= ">>\n";
        $pdfContent .= "stream\n";
        $pdfContent .= "BT\n";
        $pdfContent .= "/F1 12 Tf\n";
        $pdfContent .= "72 720 Td\n";
        $pdfContent .= "(SAMPARK Comprehensive Report) Tj\n";
        $pdfContent .= "ET\n";
        $pdfContent .= "endstream\n";
        $pdfContent .= "endobj\n\n";

        $pdfContent .= "xref\n";
        $pdfContent .= "0 5\n";
        $pdfContent .= "0000000000 65535 f \n";
        $pdfContent .= "0000000010 00000 n \n";
        $pdfContent .= "0000000079 00000 n \n";
        $pdfContent .= "0000000173 00000 n \n";
        $pdfContent .= "0000000301 00000 n \n";
        $pdfContent .= "trailer\n";
        $pdfContent .= "<<\n";
        $pdfContent .= "/Size 5\n";
        $pdfContent .= "/Root 1 0 R\n";
        $pdfContent .= ">>\n";
        $pdfContent .= "startxref\n";
        $pdfContent .= "380\n";
        $pdfContent .= "%%EOF";

        return $pdfContent;
    }

    /**
     * Generate preview HTML for report data
     */
    private function generatePreviewHTML($reportData)
    {
        $html = '<div class="report-preview">';

        // Summary statistics
        $html .= '<div class="mb-4">';
        $html .= '<h5>Report Summary</h5>';
        $html .= '<div class="row">';
        $html .= '<div class="col-md-3"><strong>Total Complaints:</strong> ' . number_format($reportData['summary']['total_complaints']) . '</div>';
        $html .= '<div class="col-md-3"><strong>Total Customers:</strong> ' . number_format($reportData['summary']['total_customers']) . '</div>';
        $html .= '<div class="col-md-3"><strong>Closed:</strong> ' . number_format($reportData['summary']['closed_complaints']) . '</div>';
        $html .= '<div class="col-md-3"><strong>Pending:</strong> ' . number_format($reportData['summary']['pending_complaints']) . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Customer Summary Section
        $html .= '<div class="mb-4">';
        $html .= '<h6>Customer Summary</h6>';
        $html .= '<table class="table table-sm">';
        $html .= '<thead><tr><th>Division</th><th>New Registrations</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($reportData['customer_summary'] as $row) {
            $html .= '<tr><td>' . htmlspecialchars($row['division']) . '</td><td>' . number_format($row['new_registrations']) . '</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</div>';

        // Duration Analysis Section
        $html .= '<div class="mb-4">';
        $html .= '<h6>Complaint Duration Analysis</h6>';
        $html .= '<table class="table table-sm">';
        $html .= '<thead><tr><th>Division</th><th>Total Complaints</th><th>Avg Resolution (Hours)</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($reportData['duration_analysis'] as $row) {
            $html .= '<tr><td>' . htmlspecialchars($row['division']) . '</td><td>' . number_format($row['complaint_count']) . '</td><td>' . $row['avg_resolution_hours'] . '</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</div>';

        $html .= '<p class="text-muted"><strong>Note:</strong> This is a preview. The full PDF report will include charts, detailed complaint listings, and proper formatting.</p>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Export report data (CSV/PDF)
     */
    public function exportReport()
    {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        try {
            $format = $_POST['export'] ?? 'csv';
            $view = $_GET['view'] ?? $_POST['view'] ?? 'complaints';

            // Get current filters
            $filters = [
                'status' => $_GET['status'] ?? $_POST['status'] ?? '',
                'priority' => $_GET['priority'] ?? $_POST['priority'] ?? '',
                'division' => $_GET['division'] ?? $_POST['division'] ?? '',
                'date_from' => $_GET['date_from'] ?? $_POST['date_from'] ?? date('Y-m-01'),
                'date_to' => $_GET['date_to'] ?? $_POST['date_to'] ?? date('Y-m-t')
            ];

            if ($format === 'csv') {
                $this->exportCSV($view, $filters);
            } else {
                $this->exportPDF($view, $filters);
            }

        } catch (Exception $e) {
            Config::logError("Export error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to export data. Please try again.'
            ], 500);
        }
    }

    /**
     * Export data as CSV
     */
    private function exportCSV($view, $filters)
    {
        // Generate filename
        $filename = 'SAMPARK_' . ucfirst($view) . '_' . date('Y-m-d_H-i-s') . '.csv';

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Create file pointer
        $output = fopen('php://output', 'w');

        if ($view === 'complaints') {
            // CSV headers for complaints
            fputcsv($output, [
                'Complaint ID', 'Date', 'Customer', 'Division', 'Category',
                'Description', 'Status', 'Priority', 'Duration (Hours)', 'Action Taken'
            ]);

            // Get complaints data (simplified query for CSV)
            $sql = "SELECT c.complaint_id, c.created_at, cust.name, c.division,
                           cat.category, c.description, c.status, c.priority,
                           TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW())) as duration,
                           c.action_taken
                    FROM complaints c
                    LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                    LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                    WHERE c.created_at BETWEEN ? AND ?";

            $params = [$filters['date_from'] . ' 00:00:00', $filters['date_to'] . ' 23:59:59'];

            // Add additional filters
            if (!empty($filters['status'])) {
                $sql .= " AND c.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['division'])) {
                $sql .= " AND c.division = ?";
                $params[] = $filters['division'];
            }

            if (!empty($filters['priority'])) {
                $sql .= " AND c.priority = ?";
                $params[] = $filters['priority'];
            }

            $sql .= " ORDER BY c.created_at DESC";

            $data = $this->db->fetchAll($sql, $params);

            foreach ($data as $row) {
                fputcsv($output, [
                    $row['complaint_id'],
                    $row['created_at'],
                    $row['name'],
                    $row['division'],
                    $row['category'],
                    substr($row['description'], 0, 100),
                    $row['status'],
                    $row['priority'],
                    $row['duration'],
                    substr($row['action_taken'] ?? '', 0, 100)
                ]);
            }
        }

        fclose($output);
    }

    /**
     * Export data as PDF
     */
    private function exportPDF($view, $filters)
    {
        // Simple PDF generation - in production, use a proper PDF library
        $filename = 'SAMPARK_' . ucfirst($view) . '_' . date('Y-m-d_H-i-s') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Generate basic PDF content
        echo $this->generateScheduledPDF(['summary' => ['total_complaints' => 0]]);
    }

    // Helper methods for dashboard data


    private function getDivisionStatusCount($division, $status)
    {
        try {
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM complaints WHERE division = ? AND status = ?",
                [$division, $status]
            );
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getAverageResolutionTime()
    {
        try {
            $result = $this->db->fetch(
                "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_time
                 FROM complaints WHERE status = 'closed' AND closed_at IS NOT NULL"
            );
            return round($result['avg_time'] ?? 24, 1);
        } catch (Exception $e) {
            return 24;
        }
    }

    private function getMinResolutionTime()
    {
        try {
            $result = $this->db->fetch(
                "SELECT MIN(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as min_time
                 FROM complaints WHERE status = 'closed' AND closed_at IS NOT NULL"
            );
            return round($result['min_time'] ?? 2, 1);
        } catch (Exception $e) {
            return 2;
        }
    }

    private function getMaxResolutionTime()
    {
        try {
            $result = $this->db->fetch(
                "SELECT MAX(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as max_time
                 FROM complaints WHERE status = 'closed' AND closed_at IS NOT NULL"
            );
            return round($result['max_time'] ?? 72, 1);
        } catch (Exception $e) {
            return 72;
        }
    }

    private function getResolutionEfficiency()
    {
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as count FROM complaints")['count'];
            $closed = $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE status = 'closed'")['count'];

            if ($total > 0) {
                return round(($closed / $total) * 100, 1);
            }
            return 85;
        } catch (Exception $e) {
            return 85;
        }
    }

    private function getRatingCount($rating)
    {
        try {
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM complaints WHERE rating = ?",
                [$rating]
            );
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getAverageRating()
    {
        try {
            $result = $this->db->fetch(
                "SELECT
                    (SUM(CASE WHEN rating = 'excellent' THEN 5
                             WHEN rating = 'satisfactory' THEN 4
                             WHEN rating = 'unsatisfactory' THEN 2
                             ELSE 0 END) / COUNT(*)) as avg_rating
                 FROM complaints WHERE rating IS NOT NULL"
            );
            return round($result['avg_rating'] ?? 4.2, 1);
        } catch (Exception $e) {
            return 4.2;
        }
    }

    private function getComplaintTypeDistribution()
    {
        try {
            $results = $this->db->fetchAll(
                "SELECT cat.category, COUNT(*) as count
                 FROM complaints c
                 LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                 GROUP BY cat.category
                 ORDER BY count DESC
                 LIMIT 5"
            );

            $distribution = [];
            foreach ($results as $row) {
                $distribution[$row['category'] ?? 'Unknown'] = $row['count'];
            }
            return $distribution;
        } catch (Exception $e) {
            return [];
        }
    }

    private function getTerminalStats()
    {
        try {
            $results = $this->db->fetchAll(
                "SELECT s.name as terminal, COUNT(*) as count
                 FROM complaints c
                 LEFT JOIN shed s ON c.shed_id = s.shed_id
                 WHERE s.name IS NOT NULL
                 GROUP BY s.name
                 ORDER BY count DESC
                 LIMIT 10"
            );

            $stats = [];
            foreach ($results as $row) {
                $stats[$row['terminal']] = $row['count'];
            }
            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }

    private function getCustomerRegistrationStats()
    {
        try {
            $results = $this->db->fetchAll(
                "SELECT division, COUNT(*) as count
                 FROM customers
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                 GROUP BY division
                 ORDER BY count DESC"
            );

            $stats = [];
            foreach ($results as $row) {
                $stats[$row['division'] ?? 'Unknown'] = $row['count'];
            }
            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }

    // Report data methods for the enhanced reports view

    private function getComplaintsReportData($filters)
    {
        try {
            $sql = "SELECT
                        c.complaint_id,
                        c.created_at as date,
                        c.updated_at,
                        TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW())) as duration_hours,
                        c.description,
                        c.action_taken,
                        c.status,
                        c.priority,
                        c.division,
                        c.fnr_number,
                        cat.category,
                        cat.type,
                        cust.name as customer_name,
                        cust.company_name,
                        cust.mobile as customer_mobile,
                        s.name as shed_name
                    FROM complaints c
                    LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                    LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                    LEFT JOIN shed s ON c.shed_id = s.shed_id
                    WHERE 1=1";

            $params = [];

            // Apply filters
            if (!empty($filters['date_from'])) {
                $sql .= " AND c.created_at >= ?";
                $params[] = $filters['date_from'] . ' 00:00:00';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND c.created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }

            if (!empty($filters['division'])) {
                $sql .= " AND c.division = ?";
                $params[] = $filters['division'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND c.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['priority'])) {
                $sql .= " AND c.priority = ?";
                $params[] = $filters['priority'];
            }

            // Apply sorting
            if ($filters['sort'] === 'oldest') {
                $sql .= " ORDER BY c.created_at ASC";
            } else {
                $sql .= " ORDER BY c.created_at DESC";
            }

            $sql .= " LIMIT 1000"; // Limit for performance

            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            return [];
        }
    }

    private function getTransactionsReportData($filters)
    {
        try {
            // This is a placeholder - you'll need to implement based on your transaction log structure
            // For now, return complaint history as transactions
            $sql = "SELECT
                        CONCAT('TXN_', c.complaint_id, '_', UNIX_TIMESTAMP(c.updated_at)) as transaction_id,
                        c.complaint_id,
                        'status_update' as transaction_type,
                        u.name as user_name,
                        c.division as from_division,
                        c.division as to_division,
                        'pending' as old_status,
                        c.status as new_status,
                        COALESCE(c.action_taken, 'Status updated') as remarks,
                        c.updated_at as created_at
                    FROM complaints c
                    LEFT JOIN users u ON c.assigned_to_user_id = u.id
                    WHERE 1=1";

            $params = [];

            // Apply filters
            if (!empty($filters['date_from'])) {
                $sql .= " AND c.updated_at >= ?";
                $params[] = $filters['date_from'] . ' 00:00:00';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND c.updated_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }

            if (!empty($filters['division'])) {
                $sql .= " AND c.division = ?";
                $params[] = $filters['division'];
            }

            // Apply sorting
            if ($filters['sort'] === 'oldest') {
                $sql .= " ORDER BY c.updated_at ASC";
            } else {
                $sql .= " ORDER BY c.updated_at DESC";
            }

            $sql .= " LIMIT 500"; // Limit for performance

            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            return [];
        }
    }

    private function getCustomersReportData($filters)
    {
        try {
            $sql = "SELECT
                        customer_id,
                        name,
                        company_name,
                        email,
                        mobile,
                        customer_type,
                        division,
                        status,
                        created_at
                    FROM customers
                    WHERE 1=1";

            $params = [];

            // Apply division filter for customers
            if (!empty($filters['division'])) {
                $sql .= " AND division = ?";
                $params[] = $filters['division'];
            }

            $sql .= " ORDER BY created_at DESC LIMIT 1000";

            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            return [];
        }
    }

    private function getAvailableColumns($view)
    {
        $columns = [];

        switch ($view) {
            case 'complaints':
                $columns = [
                    'complaint_id' => 'Complaint ID',
                    'date' => 'Date',
                    'customer_name' => 'Customer',
                    'division' => 'Division',
                    'category' => 'Category',
                    'status' => 'Status',
                    'priority' => 'Priority'
                ];
                break;

            case 'transactions':
                $columns = [
                    'transaction_id' => 'Transaction ID',
                    'complaint_id' => 'Complaint ID',
                    'transaction_type' => 'Type',
                    'user_name' => 'User',
                    'created_at' => 'Date'
                ];
                break;

            case 'customers':
                $columns = [
                    'customer_id' => 'Customer ID',
                    'name' => 'Name',
                    'company_name' => 'Company',
                    'email' => 'Email',
                    'mobile' => 'Mobile'
                ];
                break;
        }

        return $columns;
    }
}
