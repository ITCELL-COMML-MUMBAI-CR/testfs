<?php
// Capture the content
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container-xl">
        <div class="row justify-content-center text-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <h1 class="display-1 mb-4">
                    Welcome to <span class="text-apple-blue">SAMPARK</span>
                </h1>
                <p class="lead mb-5 col-12 col-md-10 mx-auto">
                    Support and Mediation Portal for All Rail Cargo - Your gateway to streamlined freight customer support with Indian Railways
                </p>
                
                <?php if (!$is_logged_in): ?>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                        <a href="<?= Config::getAppUrl() ?>/login" class="btn btn-apple-primary btn-lg">
                            <i class="fas fa-ticket-alt me-2"></i>Raise Support Ticket
                        </a>
                        <a href="<?= Config::getAppUrl() ?>/signup" class="btn btn-apple-glass btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Customer Registration
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                        <?php if ($user_role === 'customer'): ?>
                            <a href="<?= Config::getAppUrl() ?>/customer/tickets/create" class="btn btn-apple-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Create New Ticket
                            </a>
                            <a href="<?= Config::getAppUrl() ?>/customer/tickets" class="btn btn-apple-glass btn-lg">
                                <i class="fas fa-list me-2"></i>My Tickets
                            </a>
                        <?php else: ?>
                            <a href="<?= Config::getAppUrl() ?>/<?= $user_role === 'admin' || $user_role === 'superadmin' ? 'admin' : 'controller' ?>/dashboard" class="btn btn-apple-primary btn-lg">
                                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Stats -->
                <div class="row g-4 mt-4">
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-4 fw-light text-apple-blue">24/7</div>
                            <div class="small text-muted">Support Available</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-4 fw-light text-apple-blue">&lt;48h</div>
                            <div class="small text-muted">Response Time</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-4 fw-light text-apple-blue">100%</div>
                            <div class="small text-muted">Digital Process</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-4 fw-light text-apple-blue">24x7</div>
                            <div class="small text-muted">Tracking</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content Cards -->
<section class="py-apple-8">
    <div class="container-xl">
        <div class="row g-4 mb-5">
            
            <!-- Latest News Card -->
            <div class="col-12 col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-newspaper text-apple-blue fs-4 me-3"></i>
                            <h3 class="card-title mb-0">Latest News</h3>
                        </div>
                        
                        <?php if (!empty($latest_news)): ?>
                            <div class="list-group list-group-apple">
                                <?php foreach (array_slice($latest_news, 0, 3) as $news): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-semibold mb-1">
                                                <a href="#" class="text-decoration-none text-dark" onclick="showNewsDetails(<?= $news['id'] ?>)">
                                                    <?= htmlspecialchars($news['title']) ?>
                                                </a>
                                            </h6>
                                            <span class="badge badge-apple small"><?= ucfirst($news['type']) ?></span>
                                        </div>
                                        <p class="text-muted small mb-2"><?= htmlspecialchars($news['short_description']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('M d, Y', strtotime($news['publish_date'])) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (count($latest_news) > 3): ?>
                                <div class="text-center">
                                    <button class="btn btn-apple-glass btn-sm">
                                        <i class="fas fa-plus me-1"></i>View More News
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-newspaper text-muted fs-1 mb-3"></i>
                                <p class="text-muted">No news available at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Announcements Card -->
            <div class="col-12 col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-bullhorn text-apple-blue fs-4 me-3"></i>
                            <h3 class="card-title mb-0">Announcements</h3>
                        </div>
                        
                        <?php if (!empty($announcements)): ?>
                            <div class="list-group list-group-apple">
                                <?php foreach (array_slice($announcements, 0, 3) as $announcement): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-semibold mb-1">
                                                <a href="#" class="text-decoration-none text-dark" onclick="showNewsDetails(<?= $announcement['id'] ?>)">
                                                    <?= htmlspecialchars($announcement['title']) ?>
                                                </a>
                                            </h6>
                                            <span class="badge badge-priority-<?= $announcement['priority'] ?> small">
                                                <?= ucfirst($announcement['priority']) ?>
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-2"><?= htmlspecialchars($announcement['short_description']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('M d, Y', strtotime($announcement['publish_date'])) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (count($announcements) > 3): ?>
                                <div class="text-center">
                                    <button class="btn btn-apple-glass btn-sm">
                                        <i class="fas fa-plus me-1"></i>View More Announcements
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-bullhorn text-muted fs-1 mb-3"></i>
                                <p class="text-muted">No announcements available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Links Card -->
        <?php if (!empty($quick_links)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card-apple">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-external-link-alt text-apple-blue fs-4 me-3"></i>
                                <h3 class="card-title mb-0">Quick Links</h3>
                            </div>
                            
                            <div class="row g-3">
                                <?php foreach ($quick_links as $link): ?>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <a href="<?= htmlspecialchars($link['url']) ?>" 
                                           target="<?= htmlspecialchars($link['target']) ?>" 
                                           class="card-apple-glass text-decoration-none h-100 d-block">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start">
                                                    <?php if ($link['icon']): ?>
                                                        <i class="<?= htmlspecialchars($link['icon']) ?> text-apple-blue fs-5 me-3 mt-1"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="fw-semibold mb-1 text-dark">
                                                            <?= htmlspecialchars($link['title']) ?>
                                                        </h6>
                                                        <?php if ($link['description']): ?>
                                                            <p class="text-muted small mb-0">
                                                                <?= htmlspecialchars($link['description']) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <i class="fas fa-external-link-alt text-muted ms-auto"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-apple-8 bg-apple-off-white">
    <div class="container-xl">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-3 mb-3">How SAMPARK Works</h2>
                <p class="lead text-muted">Simple steps to get your freight support resolved quickly</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="bg-apple-blue rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-user-plus fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">1. Register</h5>
                    <p class="text-muted">Create your customer account with company details for approval</p>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="bg-apple-blue rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-ticket-alt fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">2. Submit Ticket</h5>
                    <p class="text-muted">Raise your freight support ticket with detailed information and evidence</p>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="bg-apple-blue rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-cogs fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">3. Processing</h5>
                    <p class="text-muted">Our team reviews and processes your ticket through appropriate channels</p>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="bg-apple-blue rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-check-circle fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">4. Resolution</h5>
                    <p class="text-muted">Get timely resolution with complete transparency and feedback</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-apple-8">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-3 mb-4">Built for Excellence</h2>
                <p class="lead mb-4">SAMPARK brings modern technology to freight support with features designed for efficiency and transparency.</p>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-shield-alt text-apple-blue fs-5 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-semibold mb-1">Secure & Reliable</h6>
                                <p class="text-muted mb-0">Enterprise-grade security with reliable data protection</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-mobile-alt text-apple-blue fs-5 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-semibold mb-1">Mobile Responsive</h6>
                                <p class="text-muted mb-0">Access from any device, anywhere, anytime</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-clock text-apple-blue fs-5 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-semibold mb-1">Real-time Tracking</h6>
                                <p class="text-muted mb-0">Track your ticket status in real-time with notifications</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-chart-line text-apple-blue fs-5 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-semibold mb-1">Analytics & Reports</h6>
                                <p class="text-muted mb-0">Comprehensive reporting for better insights</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-6">
                <div class="card-apple-glass p-4">
                    <div class="text-center">
                        <i class="fas fa-train text-apple-blue" style="font-size: 120px; opacity: 0.1;"></i>
                        <div class="mt-n5">
                            <h4 class="fw-semibold mb-3">Railway Freight Support</h4>
                            <p class="text-muted">Connecting freight customers with railway administration for seamless cargo operations across India</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function showNewsDetails(newsId) {
    showLoading();
    
    fetch(APP_URL + '/api/news/' + newsId)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                Swal.fire({
                    title: data.data.title,
                    html: `
                        <div class="text-start">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-calendar me-1"></i>
                                ${new Date(data.data.publish_date).toLocaleDateString()}
                            </p>
                            <div>${data.data.content}</div>
                        </div>
                    `,
                    showCloseButton: true,
                    showConfirmButton: false,
                    width: '600px'
                });
            } else {
                Swal.fire('Error', data.error || 'Failed to load news details', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            Swal.fire('Error', 'Failed to load news details', 'error');
        });
}
</script>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
