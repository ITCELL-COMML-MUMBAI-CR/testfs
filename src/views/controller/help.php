<?php
/**
 * Controller Help View - SAMPARK
 * Help and support resources for controller users
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::APP_URL . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'Help & Support - SAMPARK';
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-question-circle text-white fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Help & Support</h1>
                    <p class="text-muted mb-0">Find answers, guides, and get support for using SAMPARK</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-apple-secondary" onclick="searchHelp()">
                    <i class="fas fa-search me-2"></i>Search Help
                </button>
                <a href="mailto:<?= $contact_info['support_email'] ?? 'support@sampark.railway.gov.in' ?>" 
                   class="btn btn-apple-primary">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card card-apple mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" 
                               placeholder="Search for help articles, guides, or frequently asked questions..." 
                               id="helpSearch">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-apple-primary btn-lg" onclick="performSearch()">
                        Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Quick Start Guide -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>Quick Start Guide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="fas fa-ticket-alt text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">Managing Tickets</h6>
                                    <p class="text-muted small mb-2">Learn how to view, respond to, and manage customer support tickets effectively.</p>
                                    <a href="#ticket-management" class="btn btn-sm btn-apple-secondary">
                                        Read Guide <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="fas fa-share text-success"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">Forwarding Tickets</h6>
                                    <p class="text-muted small mb-2">Understand when and how to forward tickets to appropriate departments or users.</p>
                                    <a href="#forwarding" class="btn btn-sm btn-apple-secondary">
                                        Read Guide <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">SLA Management</h6>
                                    <p class="text-muted small mb-2">Learn about Service Level Agreements and how to meet resolution deadlines.</p>
                                    <a href="#sla-management" class="btn btn-sm btn-apple-secondary">
                                        Read Guide <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="fas fa-chart-line text-info"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">Reports & Analytics</h6>
                                    <p class="text-muted small mb-2">Generate and interpret performance reports and analytics dashboards.</p>
                                    <a href="#reports" class="btn btn-sm btn-apple-secondary">
                                        Read Guide <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Frequently Asked Questions -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <?php if (isset($faq) && is_array($faq)): ?>
                            <?php foreach ($faq as $index => $item): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq<?= $index ?>">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $index ?>">
                                        <?= htmlspecialchars($item['question']) ?>
                                    </button>
                                </h2>
                                <div id="faqCollapse<?= $index ?>" class="accordion-collapse collapse" 
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <?= nl2br(htmlspecialchars($item['answer'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default FAQ items -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq1">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                                        How do I respond to a customer ticket?
                                    </button>
                                </h2>
                                <div id="faqCollapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        To respond to a customer ticket:
                                        <ol>
                                            <li>Navigate to the Support Hub from your dashboard</li>
                                            <li>Click on the ticket you want to respond to</li>
                                            <li>Review the customer's issue and any attached evidence</li>
                                            <li>Click the "Reply to Customer" button</li>
                                            <li>Enter your response and describe the action taken</li>
                                            <li>Attach any supporting documents if needed</li>
                                            <li>Click "Send Reply" to notify the customer</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq2">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                                        When should I forward a ticket to another department?
                                    </button>
                                </h2>
                                <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You should forward a ticket when:
                                        <ul>
                                            <li>The issue requires expertise from a different department</li>
                                            <li>You don't have the authority to resolve the specific issue</li>
                                            <li>The ticket involves multiple departments and needs coordination</li>
                                            <li>Technical issues that require specialized knowledge</li>
                                        </ul>
                                        Note: Only nodal controllers can forward tickets in the system.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq3">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                                        What are SLA deadlines and why are they important?
                                    </button>
                                </h2>
                                <div id="faqCollapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        SLA (Service Level Agreement) deadlines ensure timely resolution of customer issues:
                                        <ul>
                                            <li><strong>Critical:</strong> 4 hours</li>
                                            <li><strong>High:</strong> 8 hours</li>
                                            <li><strong>Medium:</strong> 24 hours</li>
                                            <li><strong>Normal:</strong> 48 hours</li>
                                        </ul>
                                        Meeting SLA deadlines maintains customer satisfaction and service quality standards.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq4">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse4">
                                        How do I escalate a complex issue?
                                    </button>
                                </h2>
                                <div id="faqCollapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        To escalate a complex issue:
                                        <ol>
                                            <li>Document all attempts to resolve the issue</li>
                                            <li>Gather all relevant information and evidence</li>
                                            <li>Contact your supervisor or nodal controller</li>
                                            <li>Use the escalation feature in the ticket details</li>
                                            <li>Provide clear reasoning for the escalation</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq5">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse5">
                                        How can I generate performance reports?
                                    </button>
                                </h2>
                                <div id="faqCollapse5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        To generate performance reports:
                                        <ol>
                                            <li>Go to the Reports section from your dashboard</li>
                                            <li>Select the report type (Summary, Performance, SLA, etc.)</li>
                                            <li>Set your desired date range</li>
                                            <li>Apply any additional filters</li>
                                            <li>Click "Generate Report" to view the data</li>
                                            <li>Use "Export Report" to download as PDF or Excel</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Video Tutorials -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-play-circle me-2"></i>Video Tutorials
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-play text-primary fa-lg"></i>
                                    </div>
                                    <h6 class="fw-semibold">Getting Started with SAMPARK</h6>
                                    <p class="text-muted small">Basic overview of the system and key features</p>
                                    <button class="btn btn-sm btn-apple-primary" onclick="playVideo('getting-started')">
                                        <i class="fas fa-play me-1"></i>Watch (5:30)
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-play text-success fa-lg"></i>
                                    </div>
                                    <h6 class="fw-semibold">Managing Support Tickets</h6>
                                    <p class="text-muted small">Step-by-step ticket management process</p>
                                    <button class="btn btn-sm btn-apple-primary" onclick="playVideo('ticket-management')">
                                        <i class="fas fa-play me-1"></i>Watch (8:45)
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-play text-warning fa-lg"></i>
                                    </div>
                                    <h6 class="fw-semibold">Reports and Analytics</h6>
                                    <p class="text-muted small">How to generate and interpret reports</p>
                                    <button class="btn btn-sm btn-apple-primary" onclick="playVideo('reports')">
                                        <i class="fas fa-play me-1"></i>Watch (6:20)
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-play text-info fa-lg"></i>
                                    </div>
                                    <h6 class="fw-semibold">Best Practices</h6>
                                    <p class="text-muted small">Tips for efficient customer service</p>
                                    <button class="btn btn-sm btn-apple-primary" onclick="playVideo('best-practices')">
                                        <i class="fas fa-play me-1"></i>Watch (7:15)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Information -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-phone me-2"></i>Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                            <i class="fas fa-envelope text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">Email Support</div>
                            <a href="mailto:<?= htmlspecialchars($contact_info['support_email'] ?? 'controller-support@sampark.railway.gov.in') ?>" 
                               class="text-decoration-none small">
                                <?= htmlspecialchars($contact_info['support_email'] ?? 'controller-support@sampark.railway.gov.in') ?>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                            <i class="fas fa-phone text-success"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">Help Desk</div>
                            <a href="tel:<?= htmlspecialchars($contact_info['helpline'] ?? '1800-XXX-XXXX') ?>" 
                               class="text-decoration-none small">
                                <?= htmlspecialchars($contact_info['helpline'] ?? '1800-XXX-XXXX') ?>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                            <i class="fas fa-clock text-info"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">Office Hours</div>
                            <small class="text-muted">
                                <?= htmlspecialchars($contact_info['office_hours'] ?? 'Monday to Friday, 9:00 AM to 6:00 PM') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Guides -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>User Guides
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($user_guides) && is_array($user_guides)): ?>
                        <?php foreach ($user_guides as $guide): ?>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($guide['title']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($guide['description']) ?></small>
                            </div>
                            <a href="<?= htmlspecialchars($guide['url']) ?>" 
                               class="btn btn-sm btn-apple-secondary">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-semibold">Ticket Management Guide</div>
                                <small class="text-muted">Complete guide to handling customer tickets</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-apple-secondary">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-semibold">SLA Guidelines</div>
                                <small class="text-muted">Service Level Agreement requirements</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-apple-secondary">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">Escalation Procedures</div>
                                <small class="text-muted">When and how to escalate issues</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-apple-secondary">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Status -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2"></i>System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span>SAMPARK Platform</span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Operational
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span>Email Notifications</span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Active
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span>File Upload Service</span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Available
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Reporting System</span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Online
                        </span>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">Last updated: <?= date('M d, Y H:i') ?></small>
                    </div>
                </div>
            </div>

            <!-- Feedback -->
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-comment me-2"></i>Feedback
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Help us improve SAMPARK by sharing your feedback and suggestions.</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-apple-primary btn-sm" onclick="submitFeedback()">
                            <i class="fas fa-comment me-2"></i>Submit Feedback
                        </button>
                        <button class="btn btn-apple-secondary btn-sm" onclick="reportBug()">
                            <i class="fas fa-bug me-2"></i>Report Issue
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoTitle">Tutorial Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe id="videoFrame" src="" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="feedbackForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Feedback Type</label>
                        <select class="form-control-apple" name="type" required>
                            <option value="">Select type...</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="improvement">Improvement</option>
                            <option value="feature_request">Feature Request</option>
                            <option value="general">General Feedback</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Subject</label>
                        <input type="text" class="form-control-apple" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Message</label>
                        <textarea class="form-control-apple" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Help page JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    setupSearchFunctionality();
});

function setupSearchFunctionality() {
    const searchInput = document.getElementById('helpSearch');
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
}

function searchHelp() {
    const searchInput = document.getElementById('helpSearch');
    searchInput.focus();
}

function performSearch() {
    const query = document.getElementById('helpSearch').value.trim();
    if (!query) {
        Swal.fire('Info', 'Please enter a search term', 'info');
        return;
    }
    
    // Simple client-side search through FAQ items
    const faqItems = document.querySelectorAll('.accordion-item');
    let found = false;
    
    faqItems.forEach(item => {
        const question = item.querySelector('.accordion-button').textContent.toLowerCase();
        const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
        
        if (question.includes(query.toLowerCase()) || answer.includes(query.toLowerCase())) {
            item.style.display = 'block';
            item.classList.add('search-highlight');
            found = true;
        } else {
            item.style.display = 'none';
            item.classList.remove('search-highlight');
        }
    });
    
    if (!found) {
        Swal.fire('No Results', 'No help articles found matching your search. Please try different keywords or contact support.', 'info');
        // Reset search
        faqItems.forEach(item => {
            item.style.display = 'block';
            item.classList.remove('search-highlight');
        });
    }
}

function playVideo(videoType) {
    const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
    const videoFrame = document.getElementById('videoFrame');
    const videoTitle = document.getElementById('videoTitle');
    
    // Video URLs (these would be actual video URLs in production)
    const videos = {
        'getting-started': {
            url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            title: 'Getting Started with SAMPARK'
        },
        'ticket-management': {
            url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            title: 'Managing Support Tickets'
        },
        'reports': {
            url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            title: 'Reports and Analytics'
        },
        'best-practices': {
            url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            title: 'Best Practices for Customer Service'
        }
    };
    
    if (videos[videoType]) {
        videoFrame.src = videos[videoType].url;
        videoTitle.textContent = videos[videoType].title;
        videoModal.show();
    }
    
    // Clear video when modal closes
    document.getElementById('videoModal').addEventListener('hidden.bs.modal', function() {
        videoFrame.src = '';
    });
}

function submitFeedback() {
    new bootstrap.Modal(document.getElementById('feedbackModal')).show();
}

function reportBug() {
    Swal.fire({
        title: 'Report an Issue',
        html: `
            <div class="text-start">
                <p>To report a technical issue, please provide:</p>
                <ul>
                    <li>Detailed description of the problem</li>
                    <li>Steps to reproduce the issue</li>
                    <li>Browser and version you're using</li>
                    <li>Screenshots if applicable</li>
                </ul>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Email',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const subject = encodeURIComponent('SAMPARK Issue Report');
            const body = encodeURIComponent(`
Dear Support Team,

I am reporting an issue with the SAMPARK system:

Issue Description:
[Please describe the issue in detail]

Steps to Reproduce:
1. 
2. 
3. 

Browser/System Information:
- Browser: ${navigator.userAgent}
- User Role: <?= $user_role ?>
- User ID: <?= $user['id'] ?? 'N/A' ?>

Additional Information:
[Any additional context or screenshots]

Best regards,
<?= htmlspecialchars($user_name) ?>
            `);
            
            window.location.href = `mailto:<?= htmlspecialchars($contact_info['support_email'] ?? 'support@sampark.railway.gov.in') ?>?subject=${subject}&body=${body}`;
        }
    });
}

// Feedback form submission
document.getElementById('feedbackForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/feedback`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Thank You!', 'Your feedback has been submitted successfully.', 'success');
            bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
            this.reset();
        } else {
            Swal.fire('Error', result.message || 'Failed to submit feedback', 'error');
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to submit feedback. Please try again.', 'error');
    }
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('helpSearch').focus();
    }
});

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay')?.classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay')?.classList.add('d-none');
}
</script>

<style>
/* Help page specific styles */
.search-highlight {
    background: rgba(var(--apple-primary-rgb), 0.1);
    border-left: 3px solid var(--apple-primary);
}

.search-highlight .accordion-button {
    background: rgba(var(--apple-primary-rgb), 0.05);
    color: var(--apple-primary);
    font-weight: 600;
}

/* Video tutorial cards */
.video-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.video-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--apple-shadow-medium);
}

/* FAQ accordion styling */
.accordion-item {
    border: none;
    margin-bottom: 0.5rem;
    background: rgba(var(--bs-light), 0.3);
    border-radius: var(--apple-radius-medium);
    overflow: hidden;
}

.accordion-button {
    background: transparent;
    border: none;
    font-weight: 500;
    padding: 1rem 1.25rem;
}

.accordion-button:not(.collapsed) {
    background: rgba(var(--apple-primary-rgb), 0.1);
    color: var(--apple-primary);
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--apple-primary-rgb), 0.25);
}

.accordion-body {
    background: white;
    border-top: 1px solid rgba(var(--bs-border-color), 0.5);
}

/* Quick start guide cards */
.quick-start-item {
    transition: all 0.2s ease;
    border-radius: var(--apple-radius-medium);
    padding: 1rem;
}

.quick-start-item:hover {
    background: rgba(var(--bs-light), 0.5);
    transform: translateY(-2px);
}

/* Contact information styling */
.contact-item {
    transition: all 0.2s ease;
    padding: 0.75rem;
    border-radius: var(--apple-radius-small);
    margin-bottom: 0.75rem;
}

.contact-item:hover {
    background: rgba(var(--bs-light), 0.3);
}

/* User guide links */
.guide-link {
    transition: all 0.2s ease;
    padding: 0.75rem;
    border-radius: var(--apple-radius-small);
    margin-bottom: 0.5rem;
}

.guide-link:hover {
    background: rgba(var(--bs-light), 0.5);
}

/* System status indicators */
.status-item {
    padding: 0.5rem;
    border-radius: var(--apple-radius-small);
    margin-bottom: 0.25rem;
}

.status-item:hover {
    background: rgba(var(--bs-light), 0.3);
}

/* Search input enhancement */
.input-group-text {
    background: transparent;
    border-color: var(--bs-border-color);
}

.input-group .form-control {
    border-color: var(--bs-border-color);
}

.input-group .form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(var(--apple-primary-rgb), 0.25);
    border-color: var(--apple-primary);
}

/* Video modal enhancements */
.modal-lg .modal-dialog {
    max-width: 800px;
}

.ratio iframe {
    border-radius: var(--apple-radius-medium);
}

/* Loading states */
.help-loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .quick-start-item {
        margin-bottom: 1rem;
    }
    
    .video-card {
        margin-bottom: 1rem;
    }
    
    .accordion-button {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .accordion-body {
        padding: 1rem;
        font-size: 0.9rem;
    }
    
    .contact-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .contact-item .btn {
        margin-top: 0.5rem;
        width: 100%;
    }
}

/* Print styles */
@media print {
    .btn, .btn-group,
    .modal, .video-card button,
    .card-header .btn {
        display: none !important;
    }
    
    .accordion-collapse {
        display: block !important;
    }
    
    .accordion-button::after {
        display: none;
    }
    
    .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    a[href^="mailto:"]:after {
        content: " (" attr(href) ")";
    }
    
    a[href^="tel:"]:after {
        content: " (" attr(href) ")";
    }
}

/* Animation for search results */
@keyframes highlightFade {
    0% { background-color: rgba(var(--apple-primary-rgb), 0.3); }
    100% { background-color: rgba(var(--apple-primary-rgb), 0.1); }
}

.search-highlight {
    animation: highlightFade 1s ease-out;
}

/* Accessibility improvements */
.accordion-button:focus,
.btn:focus,
.form-control:focus {
    outline: 2px solid var(--apple-primary);
    outline-offset: 2px;
}

/* Dark mode support (future enhancement) */
@media (prefers-color-scheme: dark) {
    .accordion-item {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .accordion-body {
        background: rgba(255, 255, 255, 0.02);
        border-top-color: rgba(255, 255, 255, 0.1);
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>