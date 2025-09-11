<?php include '../src/views/layouts/header.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            
            <!-- Sidebar -->
            <?php include '../src/views/customer/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        Help & Support
                    </h1>
                </div>

                <!-- System Status -->
                <div class="alert alert-<?= $system_status['overall_status'] === 'operational' ? 'success' : 'warning' ?> alert-dismissible fade show">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-server me-2"></i>
                        <div>
                            <strong>System Status: <?= ucfirst($system_status['overall_status']) ?></strong>
                            <small class="d-block text-muted">Last updated: <?= $system_status['last_updated'] ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h3 class="h5 mb-3">Quick Actions</h3>
                        <div class="row g-3">
                            <?php foreach ($quick_actions as $action): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="<?= $action['icon'] ?> fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="card-title"><?= htmlspecialchars($action['title']) ?></h6>
                                        <p class="card-text small text-muted mb-3">
                                            <?= htmlspecialchars($action['description']) ?>
                                        </p>
                                        <a href="<?= htmlspecialchars($action['url']) ?>" class="btn btn-primary btn-sm">
                                            <?= $action['title'] ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h3 class="h5 mb-3">Contact Support</h3>
                        <div class="row g-3">
                            <?php foreach ($contact_info as $contact): ?>
                            <div class="col-md-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($contact['title']) ?></h6>
                                        <p class="card-text small mb-2">
                                            <i class="fas fa-phone text-primary me-2"></i>
                                            <?= htmlspecialchars($contact['phone']) ?>
                                        </p>
                                        <p class="card-text small mb-2">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($contact['email']) ?>
                                            </a>
                                        </p>
                                        <p class="card-text small text-muted mb-0">
                                            <i class="fas fa-clock text-primary me-2"></i>
                                            <?= htmlspecialchars($contact['hours']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Help Categories -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h3 class="h5 mb-3">Help Categories</h3>
                        <div class="row g-3">
                            <?php foreach ($help_categories as $category): ?>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <i class="<?= $category['icon'] ?> fa-lg text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="card-title"><?= htmlspecialchars($category['name']) ?></h6>
                                                <p class="card-text small text-muted mb-2">
                                                    <?= htmlspecialchars($category['description']) ?>
                                                </p>
                                                <small class="text-primary">
                                                    <?= $category['articles_count'] ?> articles
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Video Tutorials -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h3 class="h5 mb-3">Video Tutorials</h3>
                        <div class="row g-3">
                            <?php foreach ($video_tutorials as $video): ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="card shadow-sm">
                                    <div class="position-relative">
                                        <img src="<?= htmlspecialchars($video['thumbnail']) ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($video['title']) ?>"
                                             style="height: 120px; object-fit: cover;">
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <div class="bg-primary bg-opacity-75 rounded-circle p-2">
                                                <i class="fas fa-play"></i>
                                            </div>
                                        </div>
                                        <span class="badge bg-dark position-absolute bottom-0 end-0 m-2">
                                            <?= htmlspecialchars($video['duration']) ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title small"><?= htmlspecialchars($video['title']) ?></h6>
                                        <p class="card-text small text-muted">
                                            <?= htmlspecialchars($video['description']) ?>
                                        </p>
                                        <a href="<?= htmlspecialchars($video['url']) ?>" class="btn btn-outline-primary btn-sm">
                                            Watch Video
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="row">
                    <div class="col-12">
                        <h3 class="h5 mb-3">Frequently Asked Questions</h3>
                        
                        <div class="accordion" id="helpAccordion">
                            <?php 
                            $sectionIndex = 0;
                            foreach ($faqs as $sectionTitle => $questions): 
                                $sectionIndex++;
                            ?>
                            
                            <!-- Section Header -->
                            <div class="card mb-3">
                                <div class="card-header bg-primary bg-opacity-10">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-folder-open me-2"></i>
                                        <?= htmlspecialchars($sectionTitle) ?>
                                    </h6>
                                </div>
                                
                                <?php 
                                $questionIndex = 0;
                                foreach ($questions as $faq): 
                                    $questionIndex++;
                                    $accordionId = "faq{$sectionIndex}_{$questionIndex}";
                                ?>
                                
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="heading<?= $accordionId ?>">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?= $accordionId ?>" 
                                                aria-expanded="false" 
                                                aria-controls="collapse<?= $accordionId ?>">
                                            <?= htmlspecialchars($faq['question']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $accordionId ?>" 
                                         class="accordion-collapse collapse" 
                                         aria-labelledby="heading<?= $accordionId ?>" 
                                         data-bs-parent="#helpAccordion">
                                        <div class="accordion-body">
                                            <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php endforeach; ?>
                            </div>
                            
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Still Need Help -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-headset fa-2x text-primary mb-3"></i>
                                <h5>Still Need Help?</h5>
                                <p class="text-muted mb-3">
                                    Can't find what you're looking for? Our support team is here to help you 24/7.
                                </p>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="/customer/tickets/create" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create Support Ticket
                                    </a>
                                    <a href="tel:1800111321" class="btn btn-outline-primary">
                                        <i class="fas fa-phone me-2"></i>Call Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</main>

<?php include '../src/views/layouts/footer.php'; ?>