<?php
/**
 * Home Controller for SAMPARK
 * Handles public pages and landing page
 */

require_once 'BaseController.php';
class HomeController extends BaseController {
    
    public function index() {
        $data = [
            'page_title' => 'SAMPARK - Support and Mediation Portal for All Rail Cargo',
            'latest_news' => $this->getLatestNews(),
            'announcements' => $this->getAnnouncements(),
            'quick_links' => $this->getQuickLinks(),
            'marquee_content' => $this->getMarqueeContent()
        ];
        
        $this->view('public/home', $data);
    }
    
    
    public function privacyPolicy() {
        $data = [
            'page_title' => 'Privacy Policy - SAMPARK'
        ];
        
        $this->view('public/privacy-policy', $data);
    }
    
    public function help() {
        $data = [
            'page_title' => 'Help & Support - SAMPARK',
            'faq' => $this->getFAQ(),
            'contact_info' => $this->getContactInfo()
        ];
        
        $this->view('public/help', $data);
    }
    
    private function getLatestNews() {
        $sql = "SELECT id, title, short_description, content, publish_date, type, priority 
                FROM news 
                WHERE is_active = 1 
                  AND show_on_homepage = 1 
                  AND type = 'news'
                  AND publish_date <= NOW() 
                  AND (expire_date IS NULL OR expire_date > NOW())
                ORDER BY priority DESC, publish_date DESC 
                LIMIT 5";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getAnnouncements() {
        $sql = "SELECT id, title, short_description, content, publish_date, type, priority 
                FROM news 
                WHERE is_active = 1 
                  AND show_on_homepage = 1 
                  AND type IN ('announcement', 'alert', 'update')
                  AND publish_date <= NOW() 
                  AND (expire_date IS NULL OR expire_date > NOW())
                ORDER BY priority DESC, publish_date DESC 
                LIMIT 5";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getQuickLinks() {
        $sql = "SELECT title, description, url, icon, target 
                FROM quick_links 
                WHERE is_active = 1 
                ORDER BY sort_order ASC, title ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getMarqueeContent() {
        $sql = "SELECT title, short_description 
                FROM news 
                WHERE is_active = 1 
                  AND show_on_marquee = 1 
                  AND publish_date <= NOW() 
                  AND (expire_date IS NULL OR expire_date > NOW())
                ORDER BY priority DESC, publish_date DESC 
                LIMIT 10";
        
        $items = $this->db->fetchAll($sql);
        $content = [];
        
        foreach ($items as $item) {
            $text = $item['short_description'] ?: $item['title'];
            $content[] = strip_tags($text);
        }
        
        return implode(' • ', $content);
    }
    
    
    private function getFAQ() {
        return [
            [
                'question' => 'How do I register as a customer?',
                'answer' => 'Click on the "Sign Up" button and fill in all required details. Your registration will be reviewed by the divisional administrator and you will receive an email confirmation once approved.'
            ],
            [
                'question' => 'How long does it take to get a response to my support ticket?',
                'answer' => 'We aim to respond to all support tickets within 24-48 hours. High priority tickets are handled faster.'
            ],
            [
                'question' => 'Can I upload files with my support ticket?',
                'answer' => 'Yes, you can upload up to 3 files (max 2MB each) in formats: JPG, PNG, PDF, DOC, DOCX to support your complaint.'
            ],
            [
                'question' => 'How can I track the status of my ticket?',
                'answer' => 'Once logged in, go to "My Support Tickets" to view all your tickets and their current status. You will also receive email notifications for any updates.'
            ],
            [
                'question' => 'What information should I include in my support ticket?',
                'answer' => 'Please include complete location details (shed/terminal), date and time of incident, wagon details if applicable, and a clear description of the issue.'
            ],
            [
                'question' => 'Can I modify my support ticket after submission?',
                'answer' => 'You cannot directly modify a ticket after submission, but you can provide additional information when requested by the support team.'
            ],
            [
                'question' => 'How do I reset my password?',
                'answer' => 'Click on "Forgot Password" on the login page and follow the instructions. You will receive a password reset link via email.'
            ],
            [
                'question' => 'Who can I contact for technical support?',
                'answer' => 'For technical issues with the portal, contact our IT support team at support@sampark.railway.gov.in or call the helpline number provided below.'
            ]
        ];
    }
    
    private function getContactInfo() {
        return [
            'support_email' => 'support@sampark.railway.gov.in',
            'helpline' => '1800-XXX-XXXX',
            'office_hours' => 'Monday to Friday, 9:00 AM to 6:00 PM',
            'address' => 'Railway Board, Rail Bhavan, New Delhi - 110001'
        ];
    }
    
    public function getNewsDetails() {
        $newsId = $_GET['id'] ?? null;
        
        if (!$newsId) {
            $this->json(['error' => 'News ID is required'], 400);
            return;
        }
        
        $sql = "SELECT id, title, content, publish_date, type 
                FROM news 
                WHERE id = ? 
                  AND is_active = 1 
                  AND publish_date <= NOW() 
                  AND (expire_date IS NULL OR expire_date > NOW())";
        
        $news = $this->db->fetch($sql, [$newsId]);
        
        if (!$news) {
            $this->json(['error' => 'News not found'], 404);
            return;
        }
        
        $this->json([
            'success' => true,
            'data' => $news
        ]);
    }
    
    public function checkMaintenanceMode() {
        // Check if system is in maintenance mode
        $sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'";
        $result = $this->db->fetch($sql);
        
        $maintenanceMode = $result ? (bool)$result['setting_value'] : false;
        
        if ($maintenanceMode) {
            $sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_message'";
            $messageResult = $this->db->fetch($sql);
            $message = $messageResult ? $messageResult['setting_value'] : 'System is under maintenance. Please try again later.';
            
            $data = [
                'page_title' => 'Maintenance Mode - SAMPARK',
                'maintenance_message' => $message
            ];
            
            $this->view('public/maintenance', $data);
            return;
        }
    }
}
