<?php
/**
 * Admin Ticket Details View - SAMPARK
 * Read-only ticket viewing interface for admins
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'View Ticket Details - SAMPARK Admin';
?>

<div class="container-xl py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= Config::getAppUrl() ?>/admin/dashboard" class="text-decoration-none">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= Config::getAppUrl() ?>/admin/tickets/search" class="text-decoration-none">Ticket Search</a>
            </li>
            <li class="breadcrumb-item active">Ticket #<?= $ticket['complaint_id'] ?></li>
        </ol>
    </nav>

    <!-- View-Only Notice -->
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-eye me-2"></i>
        <div>
            <strong>View Only Mode</strong> - You are viewing this ticket in read-only mode.
            This interface is for viewing ticket details and transaction history only.
        </div>
    </div>

    <!-- Ticket Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-ticket-alt text-dark fa-lg"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h1 class="h3 mb-1 fw-semibold">Ticket #<?= $ticket['complaint_id'] ?></h1>
                        <?php
                        $priorityClass = [
                            'critical' => 'danger',
                            'high' => 'warning',
                            'medium' => 'info',
                            'normal' => 'secondary'
                        ][$ticket['priority']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $priorityClass ?> fs-6">
                            <i class="fas fa-<?= $ticket['priority'] === 'critical' ? 'exclamation-circle' :
                                              ($ticket['priority'] === 'high' ? 'exclamation-triangle' :
                                               ($ticket['priority'] === 'medium' ? 'info-circle' : 'circle')) ?>"></i>
                            <?= ucfirst($ticket['priority']) ?> Priority
                        </span>
                        <?php if ($ticket['is_sla_violated']): ?>
                        <span class="badge bg-danger fs-6">
                            <i class="fas fa-clock me-1"></i>SLA Overdue
                        </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted mb-0">
                        Created <?= date('M d, Y \\a\\t H:i', strtotime($ticket['created_at'])) ?> •
                        <?= $ticket['hours_elapsed'] ?> hours ago
                    </p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-toolbar gap-2">
                <a href="<?= Config::getAppUrl() ?>/admin/tickets/search" class="btn btn-apple-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Search
                </a>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    <?php
    $statusInfo = [
        'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'This ticket is pending action'],
        'awaiting_info' => ['class' => 'info', 'icon' => 'info-circle', 'text' => 'Waiting for additional information from customer'],
        'awaiting_approval' => ['class' => 'primary', 'icon' => 'check-circle', 'text' => 'Reply is awaiting nodal controller approval'],
        'awaiting_feedback' => ['class' => 'success', 'icon' => 'comment', 'text' => 'Reply sent, waiting for customer feedback'],
        'closed' => ['class' => 'dark', 'icon' => 'check', 'text' => 'This ticket has been resolved and closed']
    ][$ticket['status']] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Status unknown'];
    ?>

    <div class="alert alert-<?= $statusInfo['class'] ?> d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-<?= $statusInfo['icon'] ?> me-2"></i>
        <strong>Status: <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?></strong> - <?= $statusInfo['text'] ?>
    </div>

    <!-- Latest Important Remark -->
    <?php if ($latest_important_remark): ?>
    <div class="card card-apple mb-4 latest-remark-highlight">
        <div class="card-header bg-gradient-primary">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-bullhorn me-2"></i>
                Latest Important Update
                <?php
                $remarkTypeLabel = '';
                $remarkTypeBadgeClass = 'bg-light text-primary';

                switch($latest_important_remark['remarks_type']) {
                    case 'admin_remarks':
                        $remarkTypeLabel = 'Admin Instructions';
                        $remarkTypeBadgeClass = 'bg-warning text-dark';
                        break;
                    case 'forwarding_remarks':
                        $remarkTypeLabel = 'Forwarding Notice';
                        $remarkTypeBadgeClass = 'bg-info text-white';
                        break;
                    case 'interim_remarks':
                        $remarkTypeLabel = 'Progress Update';
                        $remarkTypeBadgeClass = 'bg-success text-white';
                        break;
                    case 'internal_remarks':
                        $remarkTypeLabel = 'Internal Note';
                        $remarkTypeBadgeClass = 'bg-secondary text-white';
                        break;
                    default:
                        $remarkTypeLabel = ucfirst(str_replace('_', ' ', $latest_important_remark['remarks_type']));
                        break;
                }
                ?>
                <span class="badge <?= $remarkTypeBadgeClass ?> ms-2"><?= $remarkTypeLabel ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-light border-start border-primary border-4 mb-3">
                <div class="fw-semibold mb-2">
                    <?= ucfirst(str_replace('_', ' ', $latest_important_remark['transaction_type'])) ?>
                    <span class="text-muted">
                        - <?= date('M d, Y H:i', strtotime($latest_important_remark['created_at'])) ?>
                    </span>
                </div>
                <?php
                $latestDisplayRemarks = '';
                if (!empty($latest_important_remark['remarks'])) {
                    $latestDisplayRemarks = $latest_important_remark['remarks'];
                } elseif (!empty($latest_important_remark['internal_remarks'])) {
                    $latestDisplayRemarks = $latest_important_remark['internal_remarks'];
                }
                ?>
                <?php if (!empty(trim($latestDisplayRemarks))): ?>
                <div class="bg-white p-3 rounded border mb-2">
                    <?= nl2br(htmlspecialchars($latestDisplayRemarks)) ?>
                </div>
                <?php endif; ?>
                <div class="text-muted small">
                    <i class="fas fa-user me-1"></i>
                    <strong><?= htmlspecialchars($latest_important_remark['user_name'] ?? $latest_important_remark['customer_name'] ?? 'System') ?></strong>
                    <?php if ($latest_important_remark['user_role'] || $latest_important_remark['user_department'] || $latest_important_remark['user_division'] || $latest_important_remark['user_zone']): ?>
                    <br>
                    <span class="ms-3">
                        <?php if ($latest_important_remark['user_role']): ?>
                        <i class="fas fa-user-tag me-1"></i>
                        Role: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $latest_important_remark['user_role']))) ?>
                        <?php endif; ?>
                        <?php if ($latest_important_remark['user_department']): ?>
                        <span class="ms-2">
                            <i class="fas fa-building me-1"></i>
                            Dept: <?= htmlspecialchars($latest_important_remark['user_department']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($latest_important_remark['user_division']): ?>
                        <span class="ms-2">
                            <i class="fas fa-sitemap me-1"></i>
                            Div: <?= htmlspecialchars($latest_important_remark['user_division']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($latest_important_remark['user_zone']): ?>
                        <span class="ms-2">
                            <i class="fas fa-map me-1"></i>
                            Zone: <?= htmlspecialchars($latest_important_remark['user_zone']) ?>
                        </span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Admin Remarks History -->
    <?php if (!empty($admin_remarks)): ?>
    <div class="card card-apple mb-4">
        <div class="card-header bg-gradient-warning">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-user-shield me-2"></i>
                Admin Remarks History
                <span class="badge bg-dark ms-2"><?= count($admin_remarks) ?> remark(s)</span>
            </h5>
        </div>
        <div class="card-body">
            <?php foreach ($admin_remarks as $index => $remark): ?>
            <div class="admin-remark-item <?= $index === 0 ? 'latest-admin-remark' : '' ?>">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-primary">
                            <i class="fas fa-comment me-1"></i>
                            Admin Remark #<?= count($admin_remarks) - $index ?>
                            <?php if ($index === 0): ?>
                            <span class="badge bg-success ms-2">Latest</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            <?= date('M d, Y H:i', strtotime($remark['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <div class="bg-warning bg-opacity-10 p-3 rounded border-start border-warning border-4 mb-3">
                    <?= nl2br(htmlspecialchars($remark['remarks'])) ?>
                </div>

                <div class="text-muted small border-top pt-2">
                    <i class="fas fa-user me-1"></i>
                    <strong><?= htmlspecialchars($remark['user_name'] ?? 'System') ?></strong>
                    <?php if ($remark['user_role'] || $remark['user_department'] || $remark['user_division'] || $remark['user_zone']): ?>
                    <br>
                    <span class="ms-3">
                        <?php if ($remark['user_role']): ?>
                        <i class="fas fa-user-tag me-1"></i>
                        Role: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $remark['user_role']))) ?>
                        <?php endif; ?>
                        <?php if ($remark['user_department']): ?>
                        <span class="ms-2">
                            <i class="fas fa-building me-1"></i>
                            Dept: <?= htmlspecialchars($remark['user_department']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($remark['user_division']): ?>
                        <span class="ms-2">
                            <i class="fas fa-sitemap me-1"></i>
                            Div: <?= htmlspecialchars($remark['user_division']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($remark['user_zone']): ?>
                        <span class="ms-2">
                            <i class="fas fa-map me-1"></i>
                            Zone: <?= htmlspecialchars($remark['user_zone']) ?>
                        </span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($index < count($admin_remarks) - 1): ?>
            <hr class="my-3">
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Details Card -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Ticket Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Category:</strong>
                            <div><?= htmlspecialchars($ticket['category'] ?? 'N/A') ?></div>
                            <?php if ($ticket['subtype']): ?>
                            <small class="text-muted"><?= htmlspecialchars($ticket['subtype']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Location:</strong>
                            <div><?= htmlspecialchars($ticket['shed_name'] ?? 'N/A') ?></div>
                            <?php if ($ticket['shed_code']): ?>
                            <small class="text-muted">Code: <?= htmlspecialchars($ticket['shed_code']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($ticket['wagon_code']) && $ticket['wagon_code']): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Wagon Details:</strong>
                            <div><?= htmlspecialchars($ticket['wagon_code']) ?></div>
                            <?php if (isset($ticket['wagon_type']) && $ticket['wagon_type']): ?>
                            <small class="text-muted"><?= htmlspecialchars($ticket['wagon_type']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <strong>Description:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($ticket['description'] ?? 'No description provided')) ?>
                        </div>
                    </div>

                    <?php if ($ticket['action_taken']): ?>
                    <div class="mb-3">
                        <strong>Action Taken:</strong>
                        <div class="mt-2 p-3 bg-success bg-opacity-10 rounded">
                            <?= nl2br(htmlspecialchars($ticket['action_taken'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Evidence Files -->
            <?php if (!empty($evidence)): ?>
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip me-2"></i>Evidence Files
                        <span class="badge bg-primary ms-2"><?= count($evidence) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($evidence as $file): ?>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="me-3">
                                    <?php
                                    $extension = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION));
                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                    $fileIcon = 'fa-file';
                                    if ($isImage) $fileIcon = 'fa-file-image';
                                    elseif (str_contains($file['file_type'], 'pdf')) $fileIcon = 'fa-file-pdf';
                                    elseif (str_contains($file['file_type'], 'video')) $fileIcon = 'fa-file-video';
                                    ?>
                                    <?php if ($isImage): ?>
                                        <img src="<?= Config::getAppUrl() ?>/api/tickets/<?= $ticket['complaint_id'] ?>/evidence/<?= urlencode($file['file_name']) ?>"
                                             alt="Evidence Image"
                                             class="img-thumbnail"
                                             style="max-height: 80px; max-width: 80px; cursor: pointer;"
                                             onclick="viewImage('<?= Config::getAppUrl() ?>/api/tickets/<?= $ticket['complaint_id'] ?>/evidence/<?= urlencode($file['file_name']) ?>', '<?= htmlspecialchars($file['original_name']) ?>')">
                                    <?php else: ?>
                                        <i class="fas <?= $fileIcon ?> fa-2x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= htmlspecialchars($file['original_name']) ?></div>
                                    <small class="text-muted">
                                        <?= number_format($file['file_size'] / 1024, 1) ?> KB •
                                        <?= date('M d, Y', strtotime($file['uploaded_at'])) ?>
                                    </small>
                                    <div class="mt-2">
                                        <a href="<?= Config::getAppUrl() ?>/api/tickets/<?= $ticket['complaint_id'] ?>/evidence/<?= urlencode($file['file_name']) ?>"
                                           class="btn btn-sm btn-apple-primary me-2" target="_blank">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="<?= Config::getAppUrl() ?>/api/tickets/<?= $ticket['complaint_id'] ?>/evidence/<?= urlencode($file['file_name']) ?>?download=1"
                                           class="btn btn-sm btn-apple-secondary">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Transaction History -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Transaction History
                        <span class="badge bg-primary ms-2"><?= count($transactions) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <h5>No transaction history</h5>
                        <p>This ticket has no transaction history yet.</p>
                    </div>
                    <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($transactions as $index => $transaction): ?>
                        <?php
                        // Determine if this transaction should be highlighted
                        $isAdminInstruction = $transaction['remarks_type'] === 'admin_remarks';
                        $isForwardingRemark = $transaction['remarks_type'] === 'forwarding_remarks';
                        $isInfoRequest = $transaction['transaction_type'] === 'info_requested';
                        $highlightClass = '';

                        if ($isAdminInstruction) {
                            $highlightClass = 'admin-instruction-highlight';
                        } elseif ($isForwardingRemark || $isInfoRequest) {
                            $highlightClass = 'forwarding-highlight';
                        }
                        ?>
                        <div class="timeline-item <?= $index === 0 ? 'active' : '' ?> <?= $highlightClass ?>">
                            <div class="timeline-marker <?= $isAdminInstruction ? 'admin-marker' : '' ?> <?= $isForwardingRemark || $isInfoRequest ? 'forwarding-marker' : '' ?>">
                                <?php
                                $icon = [
                                    'created' => 'fa-plus-circle',
                                    'forwarded' => 'fa-share',
                                    'replied' => 'fa-reply',
                                    'approved' => 'fa-check-circle',
                                    'rejected' => 'fa-times-circle',
                                    'reverted' => 'fa-undo',
                                    'closed' => 'fa-check',
                                    'info_requested' => 'fa-question-circle'
                                ][$transaction['transaction_type']] ?? 'fa-circle';

                                // Override icon for admin remarks
                                if ($isAdminInstruction) {
                                    $icon = 'fa-user-shield';
                                }
                                ?>
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div class="timeline-content<?= $isAdminInstruction ? ' admin-content' : '' ?><?= $isForwardingRemark || $isInfoRequest ? ' forwarding-content' : '' ?>" data-remark-type="<?= $transaction['remarks_type'] ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <?php
                                        // Get remark type styling
                                        $remarkTypeLabel = '';
                                        $remarkTypeBadgeClass = 'bg-secondary';

                                        switch($transaction['remarks_type']) {
                                            case 'internal_remarks':
                                                $remarkTypeLabel = 'Internal';
                                                $remarkTypeBadgeClass = 'bg-primary';
                                                break;
                                            case 'interim_remarks':
                                                $remarkTypeLabel = 'Interim Update';
                                                $remarkTypeBadgeClass = 'bg-info';
                                                break;
                                            case 'forwarding_remarks':
                                                $remarkTypeLabel = 'Forwarding';
                                                $remarkTypeBadgeClass = 'bg-warning';
                                                break;
                                            case 'admin_remarks':
                                                $remarkTypeLabel = 'Admin';
                                                $remarkTypeBadgeClass = 'bg-success';
                                                break;
                                            case 'customer_remarks':
                                                $remarkTypeLabel = 'Customer';
                                                $remarkTypeBadgeClass = 'bg-secondary';
                                                break;
                                            case 'priority_escalation':
                                                $remarkTypeLabel = 'Priority Escalation';
                                                $remarkTypeBadgeClass = 'bg-danger';
                                                break;
                                            default:
                                                $remarkTypeLabel = 'System';
                                                $remarkTypeBadgeClass = 'bg-dark';
                                        }
                                        ?>
                                        <h6 class="mb-1">
                                            <?= ucfirst(str_replace('_', ' ', $transaction['transaction_type'])) ?>
                                            <?php if ($remarkTypeLabel): ?>
                                            <span class="badge <?= $remarkTypeBadgeClass ?> ms-2"><?= $remarkTypeLabel ?></span>
                                            <?php endif; ?>
                                        </h6>
                                        <?php
                                        $displayRemarks = '';
                                        if (!empty($transaction['remarks'])) {
                                            $displayRemarks = $transaction['remarks'];
                                        } elseif (!empty($transaction['internal_remarks'])) {
                                            $displayRemarks = $transaction['internal_remarks'];
                                        }
                                        ?>
                                        <?php if (!empty(trim($displayRemarks))): ?>
                                        <div class="bg-light p-3 rounded mb-2">
                                            <?= nl2br(htmlspecialchars($displayRemarks)) ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($transaction['user_name'] ?? $transaction['customer_name'] ?? 'System') ?>
                                            <?php if ($transaction['user_department'] || $transaction['user_division'] || $transaction['user_zone']): ?>
                                            <br>
                                            <span class="ms-3">
                                                <?php if ($transaction['user_department']): ?>
                                                <i class="fas fa-building me-1"></i>
                                                Dept: <?= htmlspecialchars($transaction['user_department']) ?>
                                                <?php endif; ?>
                                                <?php if ($transaction['user_division']): ?>
                                                <span class="ms-2">
                                                    <i class="fas fa-sitemap me-1"></i>
                                                    Div: <?= htmlspecialchars($transaction['user_division']) ?>
                                                </span>
                                                <?php endif; ?>
                                                <?php if ($transaction['user_zone']): ?>
                                                <span class="ms-2">
                                                    <i class="fas fa-map me-1"></i>
                                                    Zone: <?= htmlspecialchars($transaction['user_zone']) ?>
                                                </span>
                                                <?php endif; ?>
                                            </span>
                                            <?php endif; ?>
                                            <span class="ms-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong>
                        <div><?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <div>
                            <?php if ($ticket['customer_email']): ?>
                            <a href="mailto:<?= htmlspecialchars($ticket['customer_email']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($ticket['customer_email']) ?>
                            </a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Mobile:</strong>
                        <div>
                            <?php if ($ticket['customer_mobile']): ?>
                            <a href="tel:<?= htmlspecialchars($ticket['customer_mobile']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($ticket['customer_mobile']) ?>
                            </a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($ticket['company_name']): ?>
                    <div class="mb-3">
                        <strong>Company:</strong>
                        <div><?= htmlspecialchars($ticket['company_name']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assignment Info -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-cog me-2"></i>Assignment
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Assigned To:</strong>
                        <div>
                            <?= htmlspecialchars($ticket['assigned_to_department'] ?? 'Unassigned') ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Division:</strong>
                        <div><?= htmlspecialchars($ticket['division'] ?? 'N/A') ?></div>
                    </div>
                    <div class="mb-3">
                        <strong>Zone:</strong>
                        <div><?= htmlspecialchars($ticket['zone'] ?? 'N/A') ?></div>
                    </div>
                </div>
            </div>

            <!-- SLA Information -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>SLA Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Resolution Time:</strong>
                        <div class="d-flex align-items-center">
                            <?php if ($ticket['is_sla_violated']): ?>
                            <span class="badge bg-danger me-2">Overdue</span>
                            <?php else: ?>
                            <span class="badge bg-success me-2">On Time</span>
                            <?php endif; ?>
                            <?= $ticket['hours_elapsed'] ?> hours
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Utility function to view images
function viewImage(imageUrl, imageName) {
    Swal.fire({
        title: imageName,
        imageUrl: imageUrl,
        imageAlt: imageName,
        showCloseButton: true,
        showConfirmButton: false,
        width: '80%',
        padding: '1rem'
    });
}
</script>

<!-- Include the same styles as the controller view -->
<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--bs-border-color);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -2.25rem;
    top: 0.25rem;
    width: 2rem;
    height: 2rem;
    background: white;
    border: 2px solid var(--bs-border-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.timeline-item.active .timeline-marker {
    background: var(--apple-primary);
    border-color: var(--apple-primary);
    color: white;
}

.timeline-content {
    background: var(--bs-gray-50);
    border-radius: var(--apple-radius-medium);
    padding: 1rem;
    border-left: 3px solid var(--bs-border-color);
}

.timeline-item.active .timeline-content {
    border-left-color: var(--apple-primary);
}

/* Card enhancements */
.card-apple {
    transition: all 0.2s ease;
}

.card-apple:hover {
    transform: translateY(-1px);
    box-shadow: var(--apple-shadow-medium);
}

/* Priority badge animations */
.badge.bg-danger {
    animation: pulse 2s infinite;
}

/* Latest Important Remark Styling */
.latest-remark-highlight {
    border: 2px solid var(--apple-primary) !important;
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.2);
    animation: highlightPulse 3s ease-in-out;
}

.latest-remark-highlight .card-header {
    background: linear-gradient(135deg, var(--apple-primary), #0056b3) !important;
}

@keyframes highlightPulse {
    0%, 100% {
        box-shadow: 0 0 20px rgba(0, 123, 255, 0.2);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        transform: scale(1.01);
    }
}

/* Enhanced remark highlighting based on type */
.timeline-content[data-remark-type="admin_remarks"] {
    border-left: 4px solid #28a745 !important;
    background: rgba(40, 167, 69, 0.05) !important;
}

.timeline-content[data-remark-type="forwarding_remarks"] {
    border-left: 4px solid #17a2b8 !important;
    background: rgba(23, 162, 184, 0.05) !important;
}

.timeline-content[data-remark-type="interim_remarks"] {
    border-left: 4px solid #007bff !important;
    background: rgba(0, 123, 255, 0.05) !important;
}

/* Special styling for admin instructions */
.admin-instruction-highlight {
    border: 2px solid #28a745 !important;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05)) !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

.admin-instruction-highlight .timeline-marker {
    background: linear-gradient(135deg, #28a745, #1e7e34) !important;
    animation: pulseGreen 2s infinite;
}

@keyframes pulseGreen {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
}

/* Forwarding and Info Request Highlights */
.forwarding-highlight {
    border: 1px solid #17a2b8 !important;
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.08), rgba(23, 162, 184, 0.03)) !important;
    box-shadow: 0 2px 8px rgba(23, 162, 184, 0.15);
}

.forwarding-highlight .timeline-marker.forwarding-marker {
    background: linear-gradient(135deg, #17a2b8, #117a8b) !important;
    border-color: #17a2b8;
    animation: pulseBlue 2s infinite;
}

@keyframes pulseBlue {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(23, 162, 184, 0);
    }
}

/* Admin marker special styling */
.timeline-marker.admin-marker {
    background: linear-gradient(135deg, #28a745, #1e7e34) !important;
    border-color: #28a745;
    animation: pulseGreen 2s infinite;
}

/* Content type specific styling */
.timeline-content.admin-content {
    border: 2px solid #28a745 !important;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05)) !important;
}

.timeline-content.forwarding-content {
    border: 2px solid #17a2b8 !important;
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(23, 162, 184, 0.05)) !important;
}

/* Remark Type Styling */
.badge.bg-primary { background-color: #007bff !important; }
.badge.bg-info { background-color: #17a2b8 !important; }
.badge.bg-warning { background-color: #ffc107 !important; color: #212529 !important; }
.badge.bg-success { background-color: #28a745 !important; }
.badge.bg-secondary { background-color: #6c757d !important; }
.badge.bg-danger { background-color: #dc3545 !important; }
.badge.bg-dark { background-color: #343a40 !important; }

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

/* Admin Remarks Styling */
.admin-remark-item {
    position: relative;
}

.latest-admin-remark {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
    border-radius: 8px;
    padding: 15px;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.admin-remark-item .bg-warning {
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.1);
}

.card-header.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107, #ffed4a) !important;
    color: #212529;
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>