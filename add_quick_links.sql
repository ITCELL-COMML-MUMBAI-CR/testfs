-- Add sample content to SAMPARK database
-- Run this script to add quick links, news, and announcements that will appear on the home page

-- Add Quick Links
INSERT INTO `quick_links` (`title`, `description`, `url`, `icon`, `target`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('Indian Railways', 'Official Indian Railways website', 'https://indianrailways.gov.in', 'fas fa-train', '_blank', 1, 1, NOW(), NOW()),
('Railway Board', 'Ministry of Railways - Railway Board', 'https://railwayboard.gov.in', 'fas fa-building', '_blank', 1, 2, NOW(), NOW()),
('Freight Business Portal', 'Online freight booking and tracking', 'https://freight.indianrailways.gov.in', 'fas fa-shipping-fast', '_blank', 1, 3, NOW(), NOW()),
('FOIS - Freight Operations', 'Freight Operations Information System', 'https://fois.indianrailways.gov.in', 'fas fa-chart-line', '_blank', 1, 4, NOW(), NOW()),
('NTES - Train Enquiry', 'National Train Enquiry System', 'https://enquiry.indianrailways.gov.in', 'fas fa-search', '_blank', 1, 5, NOW(), NOW()),
('Railway Safety Guidelines', 'Safety guidelines and protocols', 'https://safety.indianrailways.gov.in', 'fas fa-shield-alt', '_blank', 1, 6, NOW(), NOW()),
('Complaints & Suggestions', 'Rail Madad - Customer Care Portal', 'https://railmadad.indianrailways.gov.in', 'fas fa-comment-dots', '_blank', 1, 7, NOW(), NOW()),
('Tender Notices', 'Railway procurement and tenders', 'https://tender.indianrailways.gov.in', 'fas fa-file-contract', '_blank', 1, 8, NOW(), NOW());

-- Add Sample News and Announcements
INSERT INTO `news` (`title`, `content`, `short_description`, `type`, `priority`, `is_active`, `show_on_homepage`, `show_on_marquee`, `publish_date`, `expire_date`, `division_specific`, `zone_specific`, `created_by`, `created_at`, `updated_at`) VALUES

-- News Items
('SAMPARK Portal Launched for Enhanced Customer Support', 
'<p>We are pleased to announce the launch of SAMPARK (Support and Mediation Portal for All Rail Cargo), a comprehensive digital platform designed to streamline freight customer support services across Indian Railways.</p>
<p><strong>Key Features:</strong></p>
<ul>
<li>Online ticket submission and tracking</li>
<li>Real-time status updates</li>
<li>Document upload capabilities</li>
<li>Automated escalation system</li>
<li>Mobile-responsive design</li>
</ul>
<p>This portal will significantly reduce response times and improve the overall customer experience for freight services.</p>', 
'Launch of SAMPARK portal for improved freight customer support with online ticket tracking and real-time updates.', 
'news', 'high', 1, 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), NULL, NULL, 1, NOW(), NOW()),

('Enhanced Security Measures Implemented', 
'<p>Indian Railways has implemented enhanced security protocols across all freight terminals and goods sheds to ensure the safety of cargo and personnel.</p>
<p><strong>New Security Features:</strong></p>
<ul>
<li>24/7 CCTV surveillance</li>
<li>Biometric access controls</li>
<li>GPS tracking for high-value consignments</li>
<li>Enhanced lighting and perimeter security</li>
</ul>
<p>These measures will help prevent theft, damage, and unauthorized access to freight facilities.</p>', 
'New security protocols including CCTV surveillance, biometric controls, and GPS tracking implemented across freight terminals.', 
'news', 'medium', 1, 1, 0, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 25 DAY), NULL, NULL, 1, NOW(), NOW()),

('Digital Documentation System Now Live', 
'<p>All freight booking and delivery processes have been digitized to reduce paperwork and improve efficiency.</p>
<p><strong>Benefits:</strong></p>
<ul>
<li>Paperless transactions</li>
<li>Faster processing times</li>
<li>Real-time document verification</li>
<li>Environmental sustainability</li>
</ul>
<p>Customers can now upload all required documents through the SAMPARK portal, eliminating the need for physical document submission.</p>', 
'Complete digitization of freight documentation processes for paperless, faster, and more efficient operations.', 
'update', 'medium', 1, 1, 0, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 20 DAY), NULL, NULL, 1, NOW(), NOW()),

-- Announcements
('Scheduled Maintenance - September 15, 2025', 
'<p><strong>IMPORTANT NOTICE:</strong> Scheduled maintenance of SAMPARK portal and related systems.</p>
<p><strong>Maintenance Window:</strong><br>
Date: September 15, 2025<br>
Time: 02:00 AM to 06:00 AM IST</p>
<p><strong>Services Affected:</strong></p>
<ul>
<li>Online ticket submission</li>
<li>Status tracking</li>
<li>Document uploads</li>
<li>Customer portal access</li>
</ul>
<p>Emergency support will be available through telephone helpline during this period. We apologize for any inconvenience caused.</p>', 
'Scheduled system maintenance on September 15, 2025 from 2:00 AM to 6:00 AM. Online services will be temporarily unavailable.', 
'announcement', 'high', 1, 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), NULL, NULL, 1, NOW(), NOW()),

('New Freight Booking Guidelines Effective Immediately', 
'<p>Updated freight booking guidelines are now in effect to streamline operations and improve service quality.</p>
<p><strong>Key Changes:</strong></p>
<ul>
<li>Advance booking window extended to 30 days</li>
<li>Enhanced documentation requirements for hazardous materials</li>
<li>New weight and dimension verification procedures</li>
<li>Mandatory insurance for high-value consignments</li>
</ul>
<p>Please refer to the updated guidelines available in the documents section of the portal.</p>', 
'Updated freight booking guidelines now effective with extended advance booking and new documentation requirements.', 
'announcement', 'medium', 1, 1, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 60 DAY), NULL, NULL, 1, NOW(), NOW()),

('Customer Feedback Program Launch', 
'<p>We are launching a comprehensive customer feedback program to continuously improve our freight services.</p>
<p><strong>How to Participate:</strong></p>
<ul>
<li>Complete online surveys after service delivery</li>
<li>Provide ratings for terminal facilities</li>
<li>Submit improvement suggestions</li>
<li>Participate in quarterly feedback sessions</li>
</ul>
<p>Your feedback is valuable and will directly influence service improvements and infrastructure development.</p>', 
'New customer feedback program launched to collect suggestions and continuously improve freight service quality.', 
'announcement', 'low', 1, 1, 0, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 45 DAY), NULL, NULL, 1, NOW(), NOW()),

-- Alert
('Emergency Contact Information Updated', 
'<p><strong>URGENT UPDATE:</strong> Emergency contact information for freight services has been updated.</p>
<p><strong>New Emergency Helpline:</strong><br>
📞 1800-111-321 (24/7 Available)<br>
📧 emergency@sampark.railway.gov.in</p>
<p><strong>For Immediate Assistance:</strong></p>
<ul>
<li>Cargo theft or damage</li>
<li>Safety incidents</li>
<li>Urgent delivery requirements</li>
<li>System technical issues</li>
</ul>
<p>Please update your records with the new contact information.</p>', 
'Emergency contact information updated. New 24/7 helpline: 1800-111-321 for urgent freight service assistance.', 
'alert', 'urgent', 1, 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY), NULL, NULL, 1, NOW(), NOW());