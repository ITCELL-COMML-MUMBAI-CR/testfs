<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SAMPARK - Support and Mediation Portal for All Rail Cargo. Streamlined freight customer support system for Indian Railways.">
    <meta name="keywords" content="railway, freight, cargo, support, tickets, complaints, Indian Railways">
    <meta name="author" content="Central Railways">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    <meta name="app-url" content="<?= Config::getAppUrl() ?>">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    
    <title><?= htmlspecialchars($page_title ?? 'SAMPARK - Support Portal') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= Config::getAppUrl() ?>/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="<?= Config::getAppUrl() ?>/assets/images/apple-touch-icon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome Icons -->
    <link href="<?= Config::getAppUrl() ?>/libs/fontawesome/all.min.css" rel="stylesheet">
    
    <!-- Apple Design System CSS -->
    <link href="<?= Config::getAppUrl() ?>/assets/css/apple-design.css" rel="stylesheet">
    
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Professional Fonts CSS -->
    <link href="<?= Config::getAppUrl() ?>/assets/fonts/professional-fonts.css" rel="stylesheet">
    <link href="<?= Config::getAppUrl() ?>/assets/fonts/inter.css" rel="stylesheet">
    
    <style>
        /* Override Apple font family to use professional fonts */
        :root {
            --apple-font-family: var(--font-primary);
        }
        
        body {
            font-family: var(--font-primary);
        }
        
        /* Professional typography enhancements */
        .navbar-brand {
            font-family: var(--font-display);
            font-weight: 600;
        }
        
        .display-1, .display-2, .display-3 {
            font-family: var(--font-display);
        }
        
        .lead {
            font-family: var(--font-body);
        }
        
        .btn-apple-primary, .btn-apple-glass, .btn-apple-secondary {
            font-family: var(--font-primary);
            font-weight: 500;
        }
        
        .form-control-apple {
            font-family: var(--font-body);
        }
        
        .form-label-apple {
            font-family: var(--font-primary);
            font-weight: 500;
        }
    </style>
</head>
<body class="<?= $body_class ?? '' ?>">
    
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top apple-glass-nav" id="mainNavbar">
        <div class="container-xl">
            <!-- Logo/Brand -->
            <a class="navbar-brand d-flex align-items-center" href="<?= Config::getAppUrl() ?>/">
                <div class="railway-dual-logo">
                    <img src="<?= Config::getAppUrl() ?>/assets/images/railway-logo-left.png" alt="Indian Railways" class="me-2">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold">SAMPARK</span>
                    </div>
                    <img src="<?= Config::getAppUrl() ?>/assets/images/railway-logo-right.png" alt="" class="ms-2">
                </div>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    
                    <?php if (!isset($is_logged_in) || !$is_logged_in): ?>
                        <!-- Guest Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= Config::getAppUrl() ?>/">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= Config::getAppUrl() ?>/help">
                                <i class="fas fa-question-circle me-1"></i>Help
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>Account
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-apple">
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </a></li>
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/signup">
                                    <i class="fas fa-user-plus me-2"></i>Customer Registration
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/login">
                                    <i class="fas fa-ticket-alt me-2"></i>Raise Ticket
                                </a></li>
                            </ul>
                        </li>
                        
                    <?php else: ?>
                        <!-- Authenticated Navigation -->
                        <?php 
                        $userRole = $user_role ?? '';
                        $userName = $user_name ?? 'User';
                        ?>
                        
                        <!-- Role-specific navigation -->
                        <?php if ($userRole === 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Config::getAppUrl() ?>/">
                                    <i class="fas fa-home me-1"></i>Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Config::getAppUrl() ?>/customer/tickets">
                                    <i class="fas fa-ticket-alt me-1"></i>My Tickets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Config::getAppUrl() ?>/customer/tickets/create">
                                    <i class="fas fa-plus me-1"></i>Raise Ticket
                                </a>
                            </li>
                            
                        <?php elseif (in_array($userRole, ['controller', 'controller_nodal'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Config::getAppUrl() ?>/controller/dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Config::getAppUrl() ?>/controller/tickets">
                                    <i class="fas fa-ticket-alt me-1"></i>Support Hub
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Config::getAppUrl() ?>/controller/reports">
                                    <i class="fas fa-chart-line me-1"></i>Reports
                                </a>
                            </li>
                            
                        <?php elseif (in_array($userRole, ['admin', 'superadmin'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Config::getAppUrl() ?>/admin/dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i>Management
                                </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-apple">
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/users">Users</a></li>
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/customers">Customers</a></li>
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/categories">Categories</a></li>
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/content">Content</a></li>
                            </ul>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Common authenticated links -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= Config::getAppUrl() ?>/help">
                                <i class="fas fa-question-circle me-1"></i>Help
                            </a>
                        </li>
                        
                        <!-- User Profile Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="d-flex align-items-center">
                                    <div class="bg-apple-blue rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <span class="d-none d-lg-inline"><?= htmlspecialchars($userName) ?></span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-apple">
                                <li class="dropdown-header">
                                    <div class="fw-semibold"><?= htmlspecialchars($userName) ?></div>
                                    <small class="text-muted"><?= ucfirst($userRole) ?></small>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/<?= $userRole === 'customer' ? 'customer' : ($userRole === 'admin' || $userRole === 'superadmin' ? 'admin' : 'controller') ?>/profile">
                                    <i class="fas fa-user-circle me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="showNotifications()">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                    <span class="badge bg-danger ms-auto" id="notificationCount" style="display: none;">0</span>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= Config::getAppUrl() ?>/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Marquee for announcements (only on home page) -->
    <?php if (isset($marquee_content) && !empty($marquee_content)): ?>
        <div class="marquee-container" style="margin-top: 90px;">
            <div class="marquee-content">
                <i class="fas fa-bullhorn me-2"></i>
                <?= htmlspecialchars($marquee_content) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?= isset($marquee_content) && !empty($marquee_content) ? 'mt-0' : 'mt-5' ?> pt-4">
        
        <!-- Flash Messages -->
        <?php if (isset($flash_messages)): ?>
            <?php foreach ($flash_messages as $type => $message): ?>
                <?php if ($message): ?>
                    <div class="alert alert-apple alert-apple-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show m-3" role="alert">
                        <i class="fas fa-<?= $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?> me-2"></i>
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
        
    </main>
    
    <!-- Footer -->
    <footer class="bg-light mt-auto py-4">
        <div class="container-xl">
            <!-- <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3">SAMPARK</h6>
                            <p class="text-muted small">Support and Mediation Portal for All Rail Cargo - Streamlining freight customer support for Indian Railways.</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3">Quick Links</h6>
                            <ul class="list-unstyled small">
                                <li><a href="<?= Config::getAppUrl() ?>/help" class="text-decoration-none">Help & Support</a></li>
                                <li><a href="<?= Config::getAppUrl() ?>/privacy-policy" class="text-decoration-none">Privacy Policy</a></li>
                                <li><a href="#" class="text-decoration-none">Terms of Service</a></li>
                                <li><a href="#" class="text-decoration-none">Contact Us</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-semibold mb-3">Contact Information</h6>
                    <div class="text-muted small">
                        <p><i class="fas fa-envelope me-2"></i>support@sampark.railway.gov.in</p>
                        <p><i class="fas fa-phone me-2"></i>1800-XXX-XXXX</p>
                        <p><i class="fas fa-clock me-2"></i>Mon-Fri: 9:00 AM - 6:00 PM</p>
                    </div>
                </div>
            </div> -->
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="text-muted small mb-0">
                        &copy; <?= date('Y') ?> Central Railways. All rights reserved.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="text-muted small">Version <?= Config::APP_VERSION ?></span>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="d-none">
        <div class="loading-spinner">
            <div class="loader"></div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- jQuery -->
    <script src="<?= Config::getAppUrl() ?>/libs/jQuery/jquery-3.7.1.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="<?= Config::getAppUrl() ?>/libs/sweetalert2/sweetalert2.min.js"></script>
    <link href="<?= Config::getAppUrl() ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- Background Refresh CSS -->
    <link rel="stylesheet" href="<?= Config::getAppUrl() ?>/assets/css/background-refresh.css">
    
    <!-- DataTable Fixes CSS -->
    <!-- <link rel="stylesheet" href="<?= Config::getAppUrl() ?>/assets/css/datatable-fixes.css"> -->
    
    <!-- Custom Loader CSS -->
    <link rel="stylesheet" href="<?= Config::getAppUrl() ?>/assets/css/loader.css">
    
    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Background Refresh System -->
    <script src="<?= Config::getAppUrl() ?>/assets/js/background-refresh.js"></script>
    <script src="<?= Config::getAppUrl() ?>/assets/js/datatable-config.js"></script>
    
    <!-- Global JavaScript Variables -->
    <script>
        const APP_URL = '<?= Config::getAppUrl() ?>';
        const CSRF_TOKEN = '<?= $csrf_token ?? '' ?>';
    </script>
    
    <!-- Common JavaScript -->
    <script src="<?= Config::getAppUrl() ?>/assets/js/app.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Global JavaScript variables
        window.APP_URL = '<?= Config::getAppUrl() ?>';
        window.CSRF_TOKEN = '<?= $csrf_token ?? '' ?>';
        
        // Common functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('d-none');
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('d-none');
        }
        
        // Notification functions
        function showNotifications() {
            // Implementation for showing notifications
            Swal.fire({
                title: 'Notifications',
                html: '<div id="notificationsContent">Loading notifications...</div>',
                showCloseButton: true,
                showConfirmButton: false,
                width: '600px'
            });
            
            // Load notifications via AJAX
            loadNotifications();
        }
        
        function loadNotifications() {
            // AJAX call to load notifications
            fetch(APP_URL + '/api/notifications')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('notificationsContent').innerHTML = formatNotifications(data);
                })
                .catch(error => {
                    document.getElementById('notificationsContent').innerHTML = '<p class="text-muted">Failed to load notifications.</p>';
                });
        }
        
        function formatNotifications(notifications) {
            if (!notifications || notifications.length === 0) {
                return '<p class="text-muted">No new notifications.</p>';
            }
            
            return notifications.map(notification => `
                <div class="notification-item border-bottom py-2">
                    <div class="d-flex justify-content-between">
                        <strong>${notification.title}</strong>
                        <small class="text-muted">${notification.created_at}</small>
                    </div>
                    <p class="mb-1">${notification.message}</p>
                </div>
            `).join('');
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
    
    <style>
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .loading-spinner {
            text-align: center;
        }
        
        .notification-item {
            max-height: 100px;
            overflow: hidden;
        }
        
        /* Mobile navbar improvements */
        @media (max-width: 991px) {
            .navbar-nav {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: var(--apple-radius-large);
                padding: var(--apple-space-2);
                margin-top: var(--apple-space-2);
                box-shadow: var(--apple-shadow-soft);
            }
            
            .nav-link {
                padding: var(--apple-space-2) var(--apple-space-3) !important;
                border-radius: var(--apple-radius-small);
                margin: 2px 0;
            }
        }
    </style>
    
</body>
</html>
