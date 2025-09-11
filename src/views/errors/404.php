<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - SAMPARK</title>
    
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
            color: var(--apple-blue);
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-icon {
            font-size: 4rem;
            color: var(--apple-light-gray);
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .search-box {
            max-width: 400px;
            margin: 2rem auto;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 4rem;
            }
            
            .error-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card-apple shadow-apple-medium error-container">
                    <div class="error-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    
                    <div class="error-code">404</div>
                    
                    <h2 class="display-3 mb-3">Page Not Found</h2>
                    
                    <p class="lead text-muted mb-4">
                        The page you're looking for doesn't exist or has been moved.
                    </p>
                    
                    <p class="text-muted mb-4">
                        Don't worry, it happens to the best of us. Here are some helpful links to get you back on track:
                    </p>
                    
                    <!-- Quick Search -->
                    <div class="search-box">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control form-control-apple" 
                                   placeholder="Search for what you need..."
                                   id="searchInput">
                            <button class="btn btn-apple-primary" type="button" onclick="performSearch()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
                        <a href="<?= Config::getAppUrl() ?>/" class="btn btn-apple-primary">
                            <i class="fas fa-home me-2"></i>Go Home
                        </a>
                        <a href="javascript:history.back()" class="btn btn-apple-glass">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </a>
                    </div>
                    
                    <!-- Popular Links -->
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-3">Popular Pages</h6>
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <a href="<?= Config::getAppUrl() ?>/login" class="btn btn-apple-glass btn-sm w-100">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login
                                </a>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="<?= Config::getAppUrl() ?>/signup" class="btn btn-apple-glass btn-sm w-100">
                                    <i class="fas fa-user-plus me-1"></i>Sign Up
                                </a>
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
                    </div>
                    
                    <!-- Additional Help -->
                    <div class="mt-4 pt-3 border-top">
                        <p class="small text-muted mb-0">
                            Still need help? Contact our support team at 
                            <a href="mailto:support@sampark.railway.gov.in" class="text-apple-blue">
                                support@sampark.railway.gov.in
                            </a>
                        </p>
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
        // Search functionality
        function performSearch() {
            const query = document.getElementById('searchInput').value.trim();
            
            if (!query) {
                return;
            }
            
            // Simple search redirect - in a real app, you'd implement proper search
            if (query.toLowerCase().includes('login')) {
                window.location.href = '<?= Config::getAppUrl() ?>/login';
            } else if (query.toLowerCase().includes('signup') || query.toLowerCase().includes('register')) {
                window.location.href = '<?= Config::getAppUrl() ?>/signup';
            } else if (query.toLowerCase().includes('help') || query.toLowerCase().includes('support')) {
                window.location.href = '<?= Config::getAppUrl() ?>/help';
            } else if (query.toLowerCase().includes('ticket')) {
                window.location.href = '<?= Config::getAppUrl() ?>/login';
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Search Results',
                    text: `No exact matches found for "${query}". Try the popular links below or contact support.`,
                    confirmButtonClass: 'btn btn-apple-primary'
                });
            }
        }
        
        // Enter key search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Show contact information
        function showContact() {
            Swal.fire({
                title: 'Contact Support',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <h6><i class="fas fa-envelope text-primary me-2"></i>Email Support</h6>
                            <p class="text-muted">support@sampark.railway.gov.in</p>
                        </div>
                        <div class="mb-3">
                            <h6><i class="fas fa-phone text-primary me-2"></i>Phone Support</h6>
                            <p class="text-muted">1800-XXX-XXXX</p>
                        </div>
                        <div class="mb-3">
                            <h6><i class="fas fa-clock text-primary me-2"></i>Support Hours</h6>
                            <p class="text-muted">Monday to Friday: 9:00 AM - 6:00 PM</p>
                        </div>
                        <div class="mb-0">
                            <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Address</h6>
                            <p class="text-muted">Railway Board, Rail Bhavan, New Delhi - 110001</p>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true
            });
        }
        
        // Track 404 errors for analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_view', {
                page_title: '404 - Page Not Found',
                page_location: window.location.href
            });
        }
        
        // Focus search input
        document.getElementById('searchInput').focus();
    </script>
</body>
</html>
