<?php include '../src/views/layouts/header.php'; ?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="hero-section bg-light py-5">
        <div class="container-xl">
            <div class="row justify-content-center text-center">
                <div class="col-12 col-lg-8">
                    <h1 class="display-3 mb-3">Privacy Policy</h1>
                    <p class="lead text-muted mb-0">
                        Your privacy and data security are fundamental to our service
                    </p>
                    <p class="text-muted">
                        <small>Last updated: <?= htmlspecialchars($last_updated ?? '2024-01-01') ?></small>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Privacy Policy Content -->
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    
                    <!-- Introduction -->
                    <div class="privacy-content">
                        <div class="mb-5">
                            <p class="lead">
                                SAMPARK (Support and Mediation Portal for All Rail Cargo) is committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our railway freight customer support portal.
                            </p>
                            <p>
                                By using SAMPARK, you consent to the data practices described in this policy. This policy applies to all users of the SAMPARK platform, including freight customers, railway staff, and administrators.
                            </p>
                        </div>

                        <!-- Information We Collect -->
                        <div class="mb-5">
                            <h3 class="h4 mb-3">Information We Collect</h3>
                            
                            <h5 class="h6 mb-2">Personal Information</h5>
                            <p>When you register and use SAMPARK, we collect:</p>
                            <ul>
                                <li>Name, email address, phone number</li>
                                <li>Company/organization details and business address</li>
                                <li>Government identification numbers (as required for railway operations)</li>
                                <li>Job title and department information (for railway staff)</li>
                                <li>Login credentials and authentication information</li>
                            </ul>

                            <h5 class="h6 mb-2 mt-4">Operational Information</h5>
                            <p>In the course of using our services, we collect:</p>
                            <ul>
                                <li>Ticket information including descriptions, locations, and wagon details</li>
                                <li>Communication records and correspondence</li>
                                <li>Evidence files including images and photo attachments</li>
                                <li>Transaction and service history</li>
                                <li>Feedback and survey responses</li>
                            </ul>

                            <h5 class="h6 mb-2 mt-4">Technical Information</h5>
                            <p>We automatically collect certain technical information:</p>
                            <ul>
                                <li>IP address, browser type, and operating system</li>
                                <li>Device information and screen resolution</li>
                                <li>Usage patterns and navigation data</li>
                                <li>Session information and timestamps</li>
                                <li>Error logs and performance metrics</li>
                            </ul>
                        </div>

                        <!-- How We Use Information -->
                        <div class="mb-5">
                            <h3 class="h4 mb-3">How We Use Information</h3>
                            <ul>
                                <li>Process and respond to support tickets and inquiries</li>
                                <li>Facilitate communication between customers and railway staff</li>
                                <li>Provide account management and authentication services</li>
                                <li>Send service notifications and status updates</li>
                                <li>Generate reports and analytics for operational improvement</li>
                                <li>Comply with applicable railway regulations and government requirements</li>
                                <li>Maintain audit trails and operational records</li>
                            </ul>
                        </div>

                        <!-- Data Security -->
                        <div class="mb-5">
                            <h3 class="h4 mb-3">Data Security</h3>
                            <p>We implement comprehensive security measures including:</p>
                            <ul>
                                <li>Encryption of data in transit and at rest</li>
                                <li>Multi-factor authentication for user accounts</li>
                                <li>Regular security audits and vulnerability assessments</li>
                                <li>Access controls and role-based permissions</li>
                                <li>Network security and intrusion detection systems</li>
                            </ul>
                        </div>

                        <!-- Your Rights -->
                        <div class="mb-5">
                            <h3 class="h4 mb-3">Your Rights</h3>
                            <p>You have the right to:</p>
                            <ul>
                                <li>Access and review your personal information</li>
                                <li>Update or correct inaccurate information</li>
                                <li>Request deletion of information (subject to retention requirements)</li>
                                <li>Download your data in a portable format</li>
                                <li>Object to certain types of data processing</li>
                                <li>Opt out of non-essential communications</li>
                            </ul>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-5">
                            <h3 class="h4 mb-3">Contact Information</h3>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Privacy Officer</h6>
                                            <p class="card-text small mb-2">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                <a href="mailto:<?= htmlspecialchars($contact_email ?? 'privacy@railway.gov.in') ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($contact_email ?? 'privacy@railway.gov.in') ?>
                                                </a>
                                            </p>
                                            <p class="card-text small mb-2">
                                                <i class="fas fa-phone text-primary me-2"></i>
                                                1800-111-324 (Privacy Helpline)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Mailing Address</h6>
                                            <p class="card-text small mb-0">
                                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                SAMPARK Privacy Office<br>
                                                Railway Board<br>
                                                Rail Bhavan, New Delhi - 110001
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Note -->
                        <div class="border-top pt-4 mt-5">
                            <p class="text-muted small text-center">
                                This Privacy Policy is effective as of <?= htmlspecialchars($last_updated ?? '2024-01-01') ?> and applies to all information collected by SAMPARK.
                                <br>
                                <strong>Government of India | Ministry of Railways | Indian Railways</strong>
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../src/views/layouts/footer.php'; ?>