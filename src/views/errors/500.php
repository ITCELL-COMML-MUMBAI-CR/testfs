<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - SAMPARK</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="<?= Config::getAppUrl() ?>/libs/bootstrap/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="<?= Config::getAppUrl() ?>/libs/fontawesome/all.min.css" rel="stylesheet">
    
    <!-- Apple Design CSS -->
    <link href="<?= Config::getAppUrl() ?>/assets/css/apple-design.css" rel="stylesheet">
    
    <!-- Inter Font -->
    <link href="<?= Config::getAppUrl() ?>/assets/fonts/inter.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .error-container {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 300;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 2rem;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .status-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            border-radius: 2rem;
            color: #dc3545;
            font-size: 0.875rem;
            margin: 1rem 0;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: blink 1.5s ease-in-out infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 4rem;
            }
            
            .error-icon {
                font-size: 3rem;
            }
        }
        
        .retry-button {
            position: relative;
            overflow: hidden;
        }
        
        .retry-button:disabled {
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card-apple shadow-apple-medium error-container">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    
                    <div class="error-code">500</div>
                    
                    <h2 class="display-3 mb-3">Server Error</h2>
                    
                    <div class="status-indicator">
                        <div class="status-dot"></div>
                        System temporarily unavailable
                    </div>
                    
                    <p class="lead text-muted mb-4">
                        We're experiencing technical difficulties. Our team has been notified and is working to resolve this issue.
                    </p>
                    
                    <div class="alert alert-warning border-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What happened?</strong> The server encountered an unexpected error while processing your request.
                    </div>
                    
                    <!-- Auto-retry feature -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <span class="text-muted me-2">Auto-retry in:</span>
                            <span id="countdown" class="fw-bold text-apple-blue">30</span>
                            <span class="text-muted ms-1">seconds</span>
                        </div>
                        
                        <div class="progress mb-3" style="height: 4px;">
                            <div class="progress-bar bg-apple-blue" id="progressBar" style="width: 100%"></div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
                        <button class="btn btn-apple-primary retry-button" onclick="retryNow()" id="retryBtn">
                            <i class="fas fa-redo me-2"></i>Retry Now
                        </button>
                        <a href="<?= Config::getAppUrl() ?>/" class="btn btn-apple-glass">
                            <i class="fas fa-home me-2"></i>Go Home
                        </a>
                    </div>
                    
                    <!-- Error ID for support -->
                    <div class="mb-4">
                        <p class="small text-muted mb-2">Error Reference ID:</p>
                        <code class="bg-light p-2 rounded" id="errorId"><?= uniqid('ERR_') ?></code>
                        <button class="btn btn-link btn-sm p-0 ms-2" onclick="copyErrorId()" title="Copy Error ID">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    
                    <!-- Alternative Actions -->
                    <div class="row g-2 mb-4">
                        <div class="col-6 col-md-3">
                            <button class="btn btn-apple-glass btn-sm w-100" onclick="checkStatus()">
                                <i class="fas fa-heartbeat me-1"></i>System Status
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-apple-glass btn-sm w-100" onclick="reportIssue()">
                                <i class="fas fa-bug me-1"></i>Report Issue
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="<?= Config::getAppUrl() ?>/help" class="btn btn-apple-glass btn-sm w-100">
                                <i class="fas fa-question-circle me-1"></i>Help
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-apple-glass btn-sm w-100" onclick="showContact()">
                                <i class="fas fa-envelope me-1"></i>Contact
                            </button>
                        </div>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="mt-4 pt-3 border-top">
                        <details class="text-start">
                            <summary class="text-muted small mb-2" style="cursor: pointer;">
                                <i class="fas fa-chevron-right me-1"></i>Technical Details
                            </summary>
                            <div class="small text-muted">
                                <p><strong>Time:</strong> <?= date('Y-m-d H:i:s T') ?></p>
                                <p><strong>Server:</strong> <?= $_SERVER['SERVER_NAME'] ?? 'Unknown' ?></p>
                                <p><strong>Request:</strong> <?= $_SERVER['REQUEST_METHOD'] ?? 'GET' ?> <?= $_SERVER['REQUEST_URI'] ?? '/' ?></p>
                                <p><strong>User Agent:</strong> <?= substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 60) ?>...</p>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="<?= Config::getAppUrl() ?>/libs/bootstrap/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="<?= Config::getAppUrl() ?>/libs/sweetalert2/sweetalert2.min.js"></script>
    
    <script>
        let countdownTimer;
        let retryCount = 0;
        const maxRetries = 3;
        
        // Start countdown
        function startCountdown() {
            let seconds = 30;
            const countdownElement = document.getElementById('countdown');
            const progressBar = document.getElementById('progressBar');
            
            countdownTimer = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                
                const progress = (seconds / 30) * 100;
                progressBar.style.width = progress + '%';
                
                if (seconds <= 0) {
                    clearInterval(countdownTimer);
                    autoRetry();
                }
            }, 1000);
        }
        
        // Auto retry function
        function autoRetry() {
            if (retryCount < maxRetries) {
                retryNow();
            } else {
                document.getElementById('countdown').textContent = 'Max retries reached';
                document.getElementById('progressBar').style.width = '0%';
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Auto-retry Disabled',
                    text: 'Maximum retry attempts reached. Please try manually or contact support.',
                    confirmButtonClass: 'btn btn-apple-primary'
                });
            }
        }
        
        // Manual retry function
        function retryNow() {
            const retryBtn = document.getElementById('retryBtn');
            const originalText = retryBtn.innerHTML;
            
            retryBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Retrying...';
            retryBtn.disabled = true;
            
            retryCount++;
            
            // Clear countdown
            clearInterval(countdownTimer);
            
            // Simulate retry delay
            setTimeout(() => {
                // In a real application, you would check server status here
                // For now, we'll just reload the page
                window.location.reload();
            }, 2000);
        }
        
        // Copy error ID to clipboard
        function copyErrorId() {
            const errorId = document.getElementById('errorId').textContent;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(errorId).then(() => {
                    showToast('Error ID copied to clipboard', 'success');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = errorId;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('Error ID copied to clipboard', 'success');
            }
        }
        
        // Check system status
        function checkStatus() {
            Swal.fire({
                title: 'System Status Check',
                html: '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-3">Checking system status...</p></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            // Simulate status check
            setTimeout(() => {
                Swal.fire({
                    icon: 'info',
                    title: 'System Status',
                    html: `
                        <div class="text-start">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-danger rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <span>Database: Degraded</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-warning rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <span>File Storage: Slow</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-success rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <span>Email Service: Operational</span>
                            </div>
                            <hr>
                            <p class="small text-muted mb-0">Our team is working to resolve the issues. Estimated fix time: 15-30 minutes.</p>
                        </div>
                    `,
                    confirmButtonClass: 'btn btn-apple-primary'
                });
            }, 2000);
        }
        
        // Report issue
        function reportIssue() {
            const errorId = document.getElementById('errorId').textContent;
            
            Swal.fire({
                title: 'Report Issue',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label for="issueDescription" class="form-label">Describe what you were doing when this error occurred:</label>
                            <textarea class="form-control" id="issueDescription" rows="3" placeholder="I was trying to..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">Your email (optional):</label>
                            <input type="email" class="form-control" id="userEmail" placeholder="your.email@example.com">
                        </div>
                        <div class="alert alert-info">
                            <small><strong>Error ID:</strong> ${errorId}</small>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Send Report',
                confirmButtonClass: 'btn btn-apple-primary',
                cancelButtonClass: 'btn btn-apple-glass',
                preConfirm: () => {
                    const description = document.getElementById('issueDescription').value;
                    if (!description.trim()) {
                        Swal.showValidationMessage('Please describe the issue');
                        return false;
                    }
                    return {
                        description: description,
                        email: document.getElementById('userEmail').value,
                        errorId: errorId
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // In a real app, send this to your error reporting system
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Sent',
                        text: 'Thank you for your report. Our team will investigate this issue.',
                        confirmButtonClass: 'btn btn-apple-primary'
                    });
                }
            });
        }
        
        // Show contact information
        function showContact() {
            Swal.fire({
                title: 'Emergency Contact',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <h6><i class="fas fa-phone text-danger me-2"></i>Emergency Helpline</h6>
                            <p class="text-muted">1800-XXX-XXXX (24/7 Technical Support)</p>
                        </div>
                        <div class="mb-3">
                            <h6><i class="fas fa-envelope text-primary me-2"></i>Technical Support</h6>
                            <p class="text-muted">tech-support@sampark.railway.gov.in</p>
                        </div>
                        <div class="mb-0">
                            <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>For Urgent Issues</h6>
                            <p class="text-muted">Please mention Error ID: <code>${document.getElementById('errorId').textContent}</code></p>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true
            });
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
        
        // Start the countdown when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
            
            // Log error for analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'exception', {
                    description: '500 Server Error',
                    fatal: false
                });
            }
        });
        
        // Handle page visibility change (pause countdown when tab is hidden)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(countdownTimer);
            } else {
                // Resume countdown with remaining time
                startCountdown();
            }
        });
    </script>
</body>
</html>
