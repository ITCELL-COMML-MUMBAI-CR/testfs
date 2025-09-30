<?php
/**
 * PublicController - Handles public pages and content
 * Manages privacy policy, terms of service, and other public pages
 */

require_once 'BaseController.php';


class PublicController extends BaseController {
    
    /**
     * Display privacy policy page
     */
    public function privacyPolicy() {
        $data = [
            'title' => 'Privacy Policy - SAMPARK',
            'meta_description' => 'Privacy Policy for SAMPARK - Support and Mediation Portal for All Rail Cargo',
            'last_updated' => '2024-01-01', // This should be dynamic
            'contact_email' => 'privacy@railway.gov.in'
        ];
        
        return $this->view('public/privacy-policy', $data);
    }
    
    /**
     * Display terms of service page
     */
    public function termsOfService() {
        $data = [
            'title' => 'Terms of Service - SAMPARK',
            'meta_description' => 'Terms of Service for SAMPARK Railway Support Portal',
            'last_updated' => '2024-01-01',
            'contact_email' => 'support@railway.gov.in'
        ];
        
        return $this->view('public/terms-of-service', $data);
    }
    
    /**
     * Display about page
     */
    public function about() {
        $data = [
            'title' => 'About SAMPARK - Railway Support Portal',
            'meta_description' => 'Learn about SAMPARK - the comprehensive freight customer support portal for Indian Railways',
            'features' => [
                'Multi-Role Authentication System',
                'Advanced Ticket Management', 
                'Smart Routing & Escalation',
                'Real-time Status Tracking',
                'Evidence Upload System',
                'Comprehensive Reporting'
            ],
            'stats' => [
                'active_users' => $this->getActiveUsersCount(),
                'total_tickets' => $this->getTotalTicketsCount(),
                'resolved_tickets' => $this->getResolvedTicketsCount(),
                'average_resolution_time' => $this->getAverageResolutionTime()
            ]
        ];
        
        return $this->view('public/about', $data);
    }
    
    /**
     * Display help/FAQ page
     */
    public function help() {
        $data = [
            'title' => 'Help & User Manual - SAMPARK',
            'meta_description' => 'Complete user manual and help guide for all SAMPARK roles',
            'page_title' => 'Help & User Manual - SAMPARK'
        ];
        
        return $this->view('help/help', $data);
    }
    
    /**
     * Display standalone help page (no login required)
     */
    public function helpStandalone() {
        // This method will directly include the standalone help page
        // No need for layout wrapper since it's a complete HTML page
        include __DIR__ . '/../views/help/help_standalone.php';
        exit;
    }
    
    /**
     * Display contact page
     */
    public function contact() {
        $data = [
            'title' => 'Contact Us - SAMPARK',
            'meta_description' => 'Contact SAMPARK support team for assistance',
            'offices' => [
                [
                    'name' => 'Railway Board',
                    'address' => 'Rail Bhavan, New Delhi - 110001',
                    'phone' => '+91-11-2338-4150',
                    'email' => 'chairman@railnet.gov.in'
                ],
                [
                    'name' => 'Freight Operations',
                    'address' => 'FOIS Centre, New Delhi',
                    'phone' => '+91-11-2338-4200',
                    'email' => 'freight.support@railnet.gov.in'
                ]
            ]
        ];
        
        return $this->view('public/contact', $data);
    }
    
    /**
     * Handle contact form submission
     */
    public function submitContact() {
        try {
            $name = $this->sanitize($_POST['name'] ?? '');
            $email = $this->sanitize($_POST['email'] ?? '');
            $subject = $this->sanitize($_POST['subject'] ?? '');
            $message = $this->sanitize($_POST['message'] ?? '');
            
            // Validate required fields
            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                Session::flash('error', 'All fields are required');
                return $this->redirect('/contact');
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::flash('error', 'Please provide a valid email address');
                return $this->redirect('/contact');
            }
            
            // Save contact inquiry to database
            $this->saveContactInquiry($name, $email, $subject, $message);
            
            // Send email notification to admin
            $this->sendContactNotification($name, $email, $subject, $message);
            
            Session::flash('success', 'Thank you for contacting us. We will respond to your inquiry within 24 hours.');
            return $this->redirect('/contact');
            
        } catch (Exception $e) {
            $this->log('error', 'Contact form submission failed', ['error' => $e->getMessage()]);
            Session::flash('error', 'There was an error submitting your message. Please try again.');
            return $this->redirect('/contact');
        }
    }
    
    /**
     * Display sitemap page
     */
    public function sitemap() {
        $data = [
            'title' => 'Sitemap - SAMPARK',
            'meta_description' => 'Navigate through all pages of SAMPARK portal',
            'sections' => [
                'Public Pages' => [
                    '/' => 'Home',
                    '/about' => 'About',
                    '/help' => 'Help & FAQ',
                    '/contact' => 'Contact Us',
                    '/privacy-policy' => 'Privacy Policy',
                    '/terms-of-service' => 'Terms of Service'
                ],
                'Authentication' => [
                    '/login' => 'Login',
                    '/signup' => 'Sign Up'
                ],
                'Customer Portal' => [
                    '/customer/dashboard' => 'Dashboard',
                    '/customer/tickets' => 'My Tickets',
                    '/customer/tickets/create' => 'Create Ticket',
                    '/customer/profile' => 'Profile'
                ],
                'Staff Portal' => [
                    '/controller/dashboard' => 'Controller Dashboard',
                    '/controller/tickets' => 'Manage Tickets',
                    '/controller/reports' => 'Reports'
                ],
                'Admin Portal' => [
                    '/admin/dashboard' => 'Admin Dashboard',
                    '/admin/users' => 'User Management',
                    '/admin/customers' => 'Customer Management',
                    '/admin/categories' => 'Category Management',
                    '/admin/sheds' => 'Shed Management'
                ]
            ]
        ];
        
        return $this->view('public/sitemap', $data);
    }
    
    /**
     * Display accessibility statement
     */
    public function accessibility() {
        $data = [
            'title' => 'Accessibility Statement - SAMPARK',
            'meta_description' => 'SAMPARK accessibility commitment and guidelines',
            'last_updated' => '2024-01-01',
            'compliance_level' => 'WCAG 2.1 AA',
            'features' => [
                'Keyboard navigation support',
                'Screen reader compatibility',
                'High contrast mode',
                'Adjustable text size',
                'Alternative text for images',
                'Descriptive link text'
            ]
        ];
        
        return $this->view('public/accessibility', $data);
    }
    
    /**
     * Get active users count for statistics
     */
    private function getActiveUsersCount() {
        try {
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM customers WHERE status = 'active') +
                        (SELECT COUNT(*) FROM users WHERE status = 'active') as total";
            $result = $this->db->fetch($sql);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get total tickets count for statistics
     */
    private function getTotalTicketsCount() {
        try {
            $sql = "SELECT COUNT(*) as total FROM complaint_tickets";
            $result = $this->db->fetch($sql);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get resolved tickets count for statistics
     */
    private function getResolvedTicketsCount() {
        try {
            $sql = "SELECT COUNT(*) as total FROM complaint_tickets WHERE status = 'resolved'";
            $result = $this->db->fetch($sql);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get average resolution time for statistics
     */
    private function getAverageResolutionTime() {
        try {
            $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours 
                    FROM complaint_tickets 
                    WHERE status = 'resolved' AND resolved_at IS NOT NULL";
            $result = $this->db->fetch($sql);
            $hours = $result['avg_hours'] ?? 0;
            
            if ($hours > 24) {
                return round($hours / 24, 1) . ' days';
            } else {
                return round($hours, 1) . ' hours';
            }
        } catch (Exception $e) {
            return 'N/A';
        }
    }
    
    /**
     * Get FAQ data
     */
    private function getFAQData() {
        return [
            'General' => [
                [
                    'question' => 'What is SAMPARK?',
                    'answer' => 'SAMPARK is a comprehensive freight customer support portal designed for Indian Railways, enabling freight customers to effectively communicate their needs and bottlenecks while providing administration with insights into root causes of customer concerns.'
                ],
                [
                    'question' => 'How do I register for SAMPARK?',
                    'answer' => 'Click on "Sign Up" from the homepage, fill in your personal and company details, and submit the form. Your account will be activated after admin approval.'
                ],
                [
                    'question' => 'Is SAMPARK available 24/7?',
                    'answer' => 'Yes, SAMPARK is available 24/7 for creating and tracking tickets. However, admin response times may vary based on priority and working hours.'
                ]
            ],
            'Tickets' => [
                [
                    'question' => 'How do I create a support ticket?',
                    'answer' => 'After logging in, go to "Create New Ticket", select the appropriate category, provide detailed information about your issue, and upload any relevant evidence files.'
                ],
                [
                    'question' => 'How can I track my ticket status?',
                    'answer' => 'You can view all your tickets in the "My Tickets" section of your dashboard. Each ticket shows current status, priority level, and any responses from railway staff.'
                ],
                [
                    'question' => 'What file types can I upload as evidence?',
                    'answer' => 'You can upload images (JPG, PNG, GIF), documents (PDF, DOC, DOCX), and other common file formats. Maximum file size is 10MB per file.'
                ]
            ],
            'Account' => [
                [
                    'question' => 'How do I reset my password?',
                    'answer' => 'Click on "Forgot Password" on the login page, enter your registered email address, and follow the instructions sent to your email.'
                ],
                [
                    'question' => 'How do I update my profile information?',
                    'answer' => 'Go to your profile section after logging in and click "Edit Profile" to update your personal and company information.'
                ],
                [
                    'question' => 'Why is my account pending approval?',
                    'answer' => 'All new customer accounts require verification by railway administrators to ensure authenticity. This process typically takes 1-2 business days.'
                ]
            ]
        ];
    }
    
    /**
     * Save contact inquiry to database
     */
    private function saveContactInquiry($name, $email, $subject, $message) {
        try {
            $sql = "INSERT INTO contact_inquiries (name, email, subject, message, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            return $this->db->query($sql, [$name, $email, $subject, $message]);
        } catch (Exception $e) {
            // Log error but don't fail the entire process
            $this->log('error', 'Failed to save contact inquiry', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Send contact notification email
     */
    private function sendContactNotification($name, $email, $subject, $message) {
        try {
            // This would integrate with the existing NotificationService
            // For now, just log it
            $this->log('info', 'Contact form submitted', [
                'name' => $name,
                'email' => $email,
                'subject' => $subject
            ]);

            return true;
        } catch (Exception $e) {
            $this->log('error', 'Failed to send contact notification', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get company name suggestions for autocomplete
     */
    public function getCompanySuggestions() {
        try {
            $search = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8')) : '';

            if (strlen($search) < 2) {
                return $this->json([]);
            }

            $sql = "SELECT DISTINCT company_name
                    FROM customers
                    WHERE company_name LIKE ?
                    AND company_name IS NOT NULL
                    AND company_name != ''
                    ORDER BY company_name ASC
                    LIMIT 10";

            $results = $this->db->fetchAll($sql, ["%{$search}%"]);

            $suggestions = array_map(function($row) {
                return $row['company_name'];
            }, $results);

            return $this->json($suggestions);

        } catch (Exception $e) {
            error_log('Company suggestions error: ' . $e->getMessage());
            return $this->json([]);
        }
    }

    /**
     * Get designation suggestions for autocomplete
     */
    public function getDesignationSuggestions() {
        try {
            $search = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8')) : '';

            if (strlen($search) < 2) {
                return $this->json([]);
            }

            $sql = "SELECT DISTINCT designation
                    FROM customers
                    WHERE designation LIKE ?
                    AND designation IS NOT NULL
                    AND designation != ''
                    ORDER BY designation ASC
                    LIMIT 10";

            $results = $this->db->fetchAll($sql, ["%{$search}%"]);

            $suggestions = array_map(function($row) {
                return $row['designation'];
            }, $results);

            return $this->json($suggestions);

        } catch (Exception $e) {
            error_log('Designation suggestions error: ' . $e->getMessage());
            return $this->json([]);
        }
    }
}