<?php
/**
 * Controller Ticket Details View - SAMPARK
 * Comprehensive ticket management interface with all actions and details
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'Ticket Details - SAMPARK';
?>

<div class="container-xl py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= Config::getAppUrl() ?>/controller/dashboard" class="text-decoration-none">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= Config::getAppUrl() ?>/controller/tickets" class="text-decoration-none">Support Hub</a>
            </li>
            <li class="breadcrumb-item active">Ticket #<?= $ticket['complaint_id'] ?></li>
        </ol>
    </nav>

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
                        Created <?= date('M d, Y \a\t H:i', strtotime($ticket['created_at'])) ?> • 
                        <?= $ticket['hours_elapsed'] ?> hours ago
                    </p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-toolbar gap-2">
                <div class="btn-group">
                    <button class="btn btn-apple-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-file-export me-2"></i>Print & Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="printTicket()">
                            <i class="fas fa-print me-2"></i>Print Ticket
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportTicket()">
                            <i class="fas fa-download me-2"></i>Export PDF
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-info" href="#" onclick="viewHistory()">
                            <i class="fas fa-history me-2"></i>View History
                        </a></li>
                    </ul>
                </div>
                <a href="<?= Config::getAppUrl() ?>/controller/tickets" class="btn btn-apple-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Department Access Alert for Controller Nodal -->
    <?php if ($is_viewing_other_dept ?? false): ?>
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-eye me-2"></i>
        <div>
            <strong>View Only Mode</strong> - You are viewing a ticket assigned to <?= htmlspecialchars($ticket['assigned_department_name'] ?? $ticket['assigned_to_department']) ?> department. 
            <?php if ($is_forwarded_ticket ?? false): ?>
                This ticket was forwarded and no actions can be taken.
            <?php else: ?>
                You can only view this ticket, actions are restricted to the assigned department.
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Awaiting Customer Info Alert for Controller Nodal -->
    <?php if ($is_awaiting_customer_info ?? false): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-clock me-2"></i>
        <div>
            <strong>Awaiting Customer Response</strong> - This ticket is waiting for additional information from the customer. 
            No actions can be taken until the customer provides the requested information.
        </div>
    </div>
    <?php endif; ?>

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
                <p class="mb-2 fs-6"><?= nl2br(htmlspecialchars($latest_important_remark['remarks'] ?? '')) ?></p>
                <div class="text-muted small">
                    <i class="fas fa-user me-1"></i>
                    <?= htmlspecialchars($latest_important_remark['user_name'] ?? $latest_important_remark['customer_name'] ?? 'System') ?>
                    <?php if ($latest_important_remark['user_department'] || $latest_important_remark['user_division']): ?>
                    <span class="ms-2">
                        <?php if ($latest_important_remark['user_department']): ?>
                        <i class="fas fa-building me-1"></i>
                        <?= htmlspecialchars($latest_important_remark['user_department']) ?>
                        <?php endif; ?>
                        <?php if ($latest_important_remark['user_division']): ?>
                        <span class="ms-2">
                            <i class="fas fa-sitemap me-1"></i>
                            <?= htmlspecialchars($latest_important_remark['user_division']) ?>
                        </span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
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
                    
                    <?php if ($ticket['wagon_code']): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Wagon Details:</strong>
                            <div><?= htmlspecialchars($ticket['wagon_code']) ?></div>
                            <?php if ($ticket['wagon_type']): ?>
                            <small class="text-muted"><?= htmlspecialchars($ticket['wagon_type']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($ticket['complaint_message'] ?? 'No description provided')) ?>
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
                                    $fileIcon = 'fa-file';
                                    if (str_contains($file['file_type'], 'image')) $fileIcon = 'fa-file-image';
                                    elseif (str_contains($file['file_type'], 'pdf')) $fileIcon = 'fa-file-pdf';
                                    elseif (str_contains($file['file_type'], 'video')) $fileIcon = 'fa-file-video';
                                    ?>
                                    <i class="fas <?= $fileIcon ?> fa-2x text-muted"></i>
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
                                        <p class="mb-2"><?= nl2br(htmlspecialchars($transaction['remarks'] ?? '')) ?></p>
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


            <!-- Action Panel -->
            <?php if ($permissions['can_reply'] || $permissions['can_approve']): ?>
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Ticket Actions
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Primary Actions Section -->
                    <?php if ($permissions['can_reply'] || $permissions['can_forward']): ?>
                    <div class="action-section mb-4">
                        <h6 class="text-muted mb-3"><i class="fas fa-star me-2"></i>Primary Actions</h6>
                        <div class="row g-2">
                            <?php if ($permissions['can_reply']): ?>
                            <div class="col-md-6">
                                <button class="btn btn-success w-100 action-btn-primary" onclick="showReplyModal()">
                                    <i class="fas fa-check me-2"></i>Close Ticket
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($permissions['can_forward']): ?>
                            <div class="col-md-6">
                                <button class="btn btn-warning w-100 action-btn-primary" onclick="showForwardModal()">
                                    <i class="fas fa-share me-2"></i>Forward Ticket
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Secondary Actions Section -->
                    <?php if ($permissions['can_revert_to_customer'] || $permissions['can_interim_remarks'] || $permissions['can_revert']): ?>
                    <div class="action-section mb-4">
                        <h6 class="text-muted mb-3"><i class="fas fa-tools me-2"></i>Additional Actions</h6>
                        <div class="row g-2">
                            <?php if ($permissions['can_revert_to_customer']): ?>
                            <div class="col-md-6">
                                <button class="btn btn-info w-100 action-btn-secondary" onclick="revertBackToCustomer()">
                                    <i class="fas fa-question-circle me-2"></i>Request Info
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($permissions['can_interim_remarks']): ?>
                            <div class="col-md-6">
                                <button class="btn btn-primary w-100 action-btn-secondary" onclick="addInterimRemarks()">
                                    <i class="fas fa-comment-dots me-2"></i>Interim Update
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($permissions['can_revert']): ?>
                            <div class="col-md-6">
                                <button class="btn btn-outline-warning w-100 action-btn-secondary" onclick="revertTicket()">
                                    <i class="fas fa-undo me-2"></i>Revert Ticket
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Approval Actions Section -->
                    <?php if ($permissions['can_approve']): ?>
                    <div class="action-section">
                        <h6 class="text-muted mb-3"><i class="fas fa-check-double me-2"></i>Approval Actions</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <button class="btn btn-success w-100 action-btn-approval" onclick="approveReply()">
                                    <i class="fas fa-check me-2"></i>Approve Reply
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-danger w-100 action-btn-approval" onclick="showRejectModal()">
                                    <i class="fas fa-times me-2"></i>Reject Reply
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
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
                            <?= htmlspecialchars($ticket['assigned_user_name'] ?? 'Unassigned') ?>
                            <?php if (isset($ticket['assigned_user_role']) && $ticket['assigned_user_role']): ?>
                            <small class="text-muted d-block"><?= ucfirst($ticket['assigned_user_role']) ?></small>
                            <?php endif; ?>
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
                    <?php if ($ticket['sla_deadline']): ?>
                    <div class="mb-3">
                        <strong>SLA Deadline:</strong>
                        <div><?= date('M d, Y H:i', strtotime($ticket['sla_deadline'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Reply Modal -->
<?php if ($permissions['can_reply']): ?>
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reply to Ticket #<?= $ticket['complaint_id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="replyForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Reply Message *</label>
                        <textarea class="form-control-apple" name="reply" rows="6" required 
                                  placeholder="Enter your detailed reply to the customer..."></textarea>
                        <div class="form-text">This message will be sent to the customer.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Action Taken *</label>
                        <textarea class="form-control-apple" name="action_taken" rows="4" required 
                                  placeholder="Describe the specific actions taken to resolve this issue..."></textarea>
                        <div class="form-text">Internal record of actions taken.</div>
                    </div>
                    <?php if ($user['role'] === 'controller'): ?>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="needs_approval" id="needsApproval">
                            <label class="form-check-label" for="needsApproval">
                                This reply requires nodal controller approval
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label-apple">Attach Files (Optional)</label>
                        <input type="file" class="form-control-apple" name="attachments[]" multiple 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="form-text">Maximum 5 files, 10MB each. Supported: JPG, PNG, PDF, DOC, DOCX</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Forward Modal -->
<?php if ($permissions['can_forward']): ?>
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Forward Ticket #<?= $ticket['complaint_id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardForm">
                <div class="modal-body">
                    <?php if ($user['role'] === 'controller_nodal'): ?>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Zone *</label>
                        <select class="form-control-apple" name="zone" id="forwardZone" required>
                            <option value="">Select Zone...</option>
                            <!-- Zones will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Division *</label>
                        <select class="form-control-apple" name="division" id="forwardDivision" required>
                            <option value="">Select Division...</option>
                            <!-- Divisions will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Department *</label>
                        <select class="form-control-apple" name="department" id="forwardDepartment" required>
                            <option value="">Select Department...</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Department *</label>
                        <select class="form-control-apple" name="department" id="forwardDepartmentController" required>
                            <option value="">Select Department...</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <?php endif; ?>
                    <!-- Priority will be auto-reset by system -->
                    <div class="mb-3">
                        <label class="form-label-apple">Forwarding Remarks *</label>
                        <textarea class="form-control-apple" name="remarks" rows="4" required 
                                  placeholder="Add remarks for forwarding this ticket..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-share me-2"></i>Forward Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Reject Modal -->
<?php if ($permissions['can_approve']): ?>
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Reply</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Rejection Reason *</label>
                        <textarea class="form-control-apple" name="rejection_reason" rows="4" required 
                                  placeholder="Explain why this reply is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Reject Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Ticket actions JavaScript
const ticketId = <?= $ticket['complaint_id'] ?>;

// Modal functions
function showReplyModal() {
    new bootstrap.Modal(document.getElementById('replyModal')).show();
}

function showForwardModal() {
    // Load zones and divisions for nodal controllers, or just departments for controllers
    if (document.getElementById('forwardZone')) {
        // Nodal controller modal
        loadZonesAndDivisions().then(() => {
            new bootstrap.Modal(document.getElementById('forwardModal')).show();
        });
    } else {
        // Regular controller modal - load departments only
        loadDepartments().then(() => {
            new bootstrap.Modal(document.getElementById('forwardModal')).show();
        });
    }
}

async function loadZonesAndDivisions() {
    try {
        // Get current user data (from PHP)
        const userZone = '<?= $user['zone'] ?? '' ?>';
        const userDivision = '<?= $user['division'] ?? '' ?>';
        
        // Load zones
        const zonesResponse = await fetch(`${APP_URL}/api/zones`);
        const zonesData = await zonesResponse.json();
        
        const zoneSelect = document.getElementById('forwardZone');
        if (zoneSelect && zonesData.success) {
            zoneSelect.innerHTML = '<option value="">Select Zone...</option>';
            zonesData.zones.forEach(zone => {
                const option = document.createElement('option');
                option.value = zone.zone;
                option.textContent = `${zone.zone} - ${zone.zone_name}`;
                // Pre-select user's zone
                if (zone.zone === userZone) {
                    option.selected = true;
                }
                zoneSelect.appendChild(option);
            });
        }
        
        // Load divisions based on selected/user's zone
        await loadDivisionsForZone(userZone || '');
        
        // Load departments for all users
        await loadDepartments();
        
        // Set up zone change handler to filter divisions
        if (zoneSelect) {
            zoneSelect.addEventListener('change', async function() {
                const selectedZone = this.value;
                await loadDivisionsForZone(selectedZone);
            });
        }
        
        // Set up division change handler to update department visibility
        const divisionSelect = document.getElementById('forwardDivision');
        if (divisionSelect) {
            divisionSelect.addEventListener('change', function() {
                updateDepartmentVisibility();
            });
        }
    } catch (error) {
        console.error('Error loading zones and divisions:', error);
    }
}

async function loadDivisionsForZone(zoneCode) {
    try {
        const divisionSelect = document.getElementById('forwardDivision');
        if (!divisionSelect) return;
        
        const userDivision = '<?= $user['division'] ?? '' ?>';
        
        divisionSelect.innerHTML = '<option value="">Select Division...</option>';
        
        if (zoneCode) {
            const response = await fetch(`${APP_URL}/api/divisions?zone=${zoneCode}`);
            const data = await response.json();
            
            if (data.success) {
                data.divisions.forEach(division => {
                    const option = document.createElement('option');
                    option.value = division.division;
                    option.textContent = `${division.division} - ${division.division_name}`;
                    // Pre-select user's division
                    if (division.division === userDivision) {
                        option.selected = true;
                    }
                    divisionSelect.appendChild(option);
                });
                
                // Update department visibility after divisions are loaded
                setTimeout(() => updateDepartmentVisibility(), 100);
            }
        }
    } catch (error) {
        console.error('Error loading divisions for zone:', error);
    }
}

// Store all departments globally for filtering
let allDepartments = [];

async function loadDepartments() {
    try {
        const response = await fetch(`${APP_URL}/api/departments`);
        const data = await response.json();
        
        if (data.success) {
            allDepartments = data.departments; // Store for filtering
            
            // Load departments for nodal controller
            const nodalDeptSelect = document.getElementById('forwardDepartment');
            if (nodalDeptSelect) {
                populateDepartmentSelect(nodalDeptSelect, allDepartments);
            }
            
            // Load departments for regular controller
            const controllerDeptSelect = document.getElementById('forwardDepartmentController');
            if (controllerDeptSelect) {
                populateDepartmentSelect(controllerDeptSelect, allDepartments);
            }
            
            // Initial department visibility update
            updateDepartmentVisibility();
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

function populateDepartmentSelect(selectElement, departments) {
    selectElement.innerHTML = '<option value="">Select Department...</option>';
    departments.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept.department_code;
        option.textContent = dept.department_name;
        option.dataset.departmentCode = dept.department_code;
        selectElement.appendChild(option);
    });
}

function updateDepartmentVisibility() {
    const userRole = '<?= $user['role'] ?? '' ?>';
    const userDivision = '<?= $user['division'] ?? '' ?>';
    const selectedDivision = document.getElementById('forwardDivision')?.value || userDivision;
    const nodalDeptSelect = document.getElementById('forwardDepartment');
    const controllerDeptSelect = document.getElementById('forwardDepartmentController');
    
    console.log('updateDepartmentVisibility called:', {
        userRole,
        userDivision,
        selectedDivision,
        allDepartments: allDepartments.length,
        nodalDeptSelect: !!nodalDeptSelect,
        controllerDeptSelect: !!controllerDeptSelect
    });
    
    // Filter for Commercial departments
    const commercialDepts = allDepartments.filter(dept => {
        // Check multiple possible codes for Commercial department
        return dept.department_code === 'COMM' || 
               dept.department_code === 'CML' ||
               dept.department_code === 'Commercial' || 
               dept.department_name.toLowerCase().includes('commercial');
    });
    
    if (controllerDeptSelect && userRole === 'controller') {
        // Regular controllers can only forward to Commercial departments of same division
        console.log('Controller role: showing only Commercial departments');
        
        if (commercialDepts.length === 0) {
            // Fallback: if no commercial dept found, show a manual option
            controllerDeptSelect.innerHTML = '<option value="">Select Department...</option><option value="COMM">Commercial</option>';
        } else {
            populateDepartmentSelect(controllerDeptSelect, commercialDepts);
        }
    }
    
    if (nodalDeptSelect && userRole === 'controller_nodal') {
        // For controller_nodal: if forwarding outside their division, only show Commercial
        if (selectedDivision && selectedDivision !== userDivision && selectedDivision !== '') {
            // Forwarding outside division - only Commercial
            console.log('Nodal Controller forwarding outside division, filtering for Commercial departments');
            
            if (commercialDepts.length === 0) {
                // Fallback: if no commercial dept found, show a manual option
                nodalDeptSelect.innerHTML = '<option value="">Select Department...</option><option value="COMM">Commercial</option>';
            } else {
                populateDepartmentSelect(nodalDeptSelect, commercialDepts);
            }
        } else {
            // Forwarding within division - all departments
            console.log('Nodal Controller forwarding within division, showing all departments');
            populateDepartmentSelect(nodalDeptSelect, allDepartments);
        }
    }
}

function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

// Form submissions
<?php if ($permissions['can_reply']): ?>
document.getElementById('replyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/reply`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', result.message, 'success').then(() => {
                location.reload();
            });
        } else {
            if (result.errors) {
                const errors = Object.values(result.errors).join('\n');
                Swal.fire('Validation Error', errors, 'error');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to send reply', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('replyModal')).hide();
});
<?php endif; ?>

<?php if ($permissions['can_forward']): ?>
document.getElementById('forwardForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/forward`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', result.message, 'success').then(() => {
                location.reload();
            });
        } else {
            if (result.errors) {
                const errors = Object.values(result.errors).join('\n');
                Swal.fire('Validation Error', errors, 'error');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to forward ticket', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('forwardModal')).hide();
});
<?php endif; ?>

<?php if ($permissions['can_approve']): ?>
function approveReply() {
    Swal.fire({
        title: 'Approve Reply',
        text: 'Are you sure you want to approve this reply?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Add approval remarks (optional)...',
        inputAttributes: {
            'aria-label': 'Approval remarks'
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('approval_remarks', result.value || '');
            
            try {
                showLoading();
                const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/approve`, {
                    method: 'POST',
                    body: formData
                });
                
                const apiResult = await response.json();
                hideLoading();
                
                if (apiResult.success) {
                    Swal.fire('Success', apiResult.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', apiResult.message, 'error');
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to approve reply', 'error');
            }
        }
    });
}

document.getElementById('rejectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/reject`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', result.message, 'success').then(() => {
                location.reload();
            });
        } else {
            if (result.errors) {
                const errors = Object.values(result.errors).join('\n');
                Swal.fire('Validation Error', errors, 'error');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to reject reply', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
});
<?php endif; ?>

function revertTicket() {
    Swal.fire({
        title: 'Revert Ticket',
        text: 'This will revert the ticket status and request additional information from the customer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Revert',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Reason for reverting...',
        inputAttributes: {
            'aria-label': 'Revert reason'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason for reverting!'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('revert_reason', result.value);
            
            try {
                showLoading();
                const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/revert`, {
                    method: 'POST',
                    body: formData
                });
                
                const apiResult = await response.json();
                hideLoading();
                
                if (apiResult.success) {
                    Swal.fire('Success', apiResult.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', apiResult.message, 'error');
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to revert ticket', 'error');
            }
        }
    });
}

function revertBackToCustomer() {
    Swal.fire({
        title: 'Revert back to Customer',
        text: 'This will ask the customer for more information about their complaint.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Revert back',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'What additional information do you need from the customer?',
        inputAttributes: {
            'aria-label': 'Information request'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'You need to specify what information you need from the customer!'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('info_request', result.value);
            
            try {
                showLoading();
                const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/revert-to-customer`, {
                    method: 'POST',
                    body: formData
                });
                
                const apiResult = await response.json();
                hideLoading();
                
                if (apiResult.success) {
                    Swal.fire('Success', apiResult.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', apiResult.message, 'error');
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to revert to customer', 'error');
            }
        }
    });
}

function addInterimRemarks() {
    Swal.fire({
        title: 'Add Interim Remarks',
        text: 'This will send an update to the customer without changing the ticket status.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Add Remarks',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Enter interim remarks for the customer about work in progress...',
        inputAttributes: {
            'aria-label': 'Interim remarks'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide interim remarks!'
            }
            if (value.length < 10) {
                return 'Interim remarks must be at least 10 characters long!'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('interim_remarks', result.value);
            
            try {
                showLoading();
                const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/interim-remarks`, {
                    method: 'POST',
                    body: formData
                });
                
                const apiResult = await response.json();
                hideLoading();
                
                if (apiResult.success) {
                    Swal.fire('Success', apiResult.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    if (apiResult.errors) {
                        const errors = Object.values(apiResult.errors).join('\n');
                        Swal.fire('Validation Error', errors, 'error');
                    } else {
                        Swal.fire('Error', apiResult.message, 'error');
                    }
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to add interim remarks', 'error');
            }
        }
    });
}

// Utility functions

function printTicket() {
    const printWindow = window.open(`${APP_URL}/controller/tickets/${ticketId}/print`, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}

function exportTicket() {
    window.location.href = `${APP_URL}/controller/tickets/${ticketId}/export`;
}

function viewHistory() {
    // Implementation for viewing detailed history
    Swal.fire({
        title: 'Ticket History',
        text: 'Detailed history view coming soon',
        icon: 'info'
    });
}

</script>

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

/* Print styles */
@media print {
    .btn, .btn-group, .btn-toolbar,
    .modal, .navbar, .breadcrumb,
    .alert, .card-header .dropdown {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .timeline {
        padding-left: 1.5rem;
    }
    
    .timeline-marker {
        left: -1.75rem;
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.75rem;
    }
    
    .timeline-content {
        padding: 0.75rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-toolbar {
        flex-direction: column;
    }
    
    .btn-toolbar .btn-group,
    .btn-toolbar .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

/* File preview enhancements */
.evidence-file {
    transition: transform 0.2s ease;
}

.evidence-file:hover {
    transform: scale(1.02);
}

/* Status-specific styling */
.alert.alert-danger {
    border-left: 4px solid var(--bs-danger);
}

.alert.alert-warning {
    border-left: 4px solid var(--bs-warning);
}

.alert.alert-success {
    border-left: 4px solid var(--bs-success);
}

.alert.alert-info {
    border-left: 4px solid var(--bs-info);
}

/* Action Button Styling */
.action-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    background: #fafbfc;
}

.action-section h6 {
    font-weight: 600;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
    margin-bottom: 15px !important;
}

.action-btn-primary, .action-btn-secondary, .action-btn-approval {
    font-weight: 500;
    padding: 12px 20px;
    border-radius: 6px;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.action-btn-primary::before, .action-btn-secondary::before, .action-btn-approval::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.action-btn-primary:hover::before, .action-btn-secondary:hover::before, .action-btn-approval:hover::before {
    left: 100%;
}

.action-btn-primary:hover, .action-btn-secondary:hover, .action-btn-approval:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-btn-primary:active, .action-btn-secondary:active, .action-btn-approval:active {
    transform: translateY(0);
}

/* Primary action buttons */
.action-btn-primary.btn-success {
    background: linear-gradient(135deg, #28a745, #34d058);
    border-color: #28a745;
}

.action-btn-primary.btn-warning {
    background: linear-gradient(135deg, #ffc107, #ffed4a);
    border-color: #ffc107;
    color: #212529;
}

/* Secondary action buttons */
.action-btn-secondary.btn-info {
    background: linear-gradient(135deg, #17a2b8, #20c9e7);
    border-color: #17a2b8;
}

.action-btn-secondary.btn-primary {
    background: linear-gradient(135deg, #007bff, #4ea5d9);
    border-color: #007bff;
}

.action-btn-secondary.btn-outline-warning {
    border: 2px solid #ffc107;
    color: #ffc107;
    background: transparent;
}

.action-btn-secondary.btn-outline-warning:hover {
    background: #ffc107;
    color: #212529;
}

/* Approval action buttons */
.action-btn-approval.btn-success {
    background: linear-gradient(135deg, #28a745, #34d058);
    border-color: #28a745;
}

.action-btn-approval.btn-danger {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    border-color: #dc3545;
}

/* Remark Type Styling */
.badge.bg-primary { background-color: #007bff !important; }
.badge.bg-info { background-color: #17a2b8 !important; }
.badge.bg-warning { background-color: #ffc107 !important; color: #212529 !important; }
.badge.bg-success { background-color: #28a745 !important; }
.badge.bg-secondary { background-color: #6c757d !important; }
.badge.bg-danger { background-color: #dc3545 !important; }
.badge.bg-dark { background-color: #343a40 !important; }

/* Timeline content highlighting based on remark type */
.timeline-item:has(.badge.bg-danger) .timeline-icon {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    animation: pulse 2s infinite;
}

.timeline-item:has(.badge.bg-warning) .timeline-icon {
    background: linear-gradient(135deg, #ffc107, #ffed4a);
}

.timeline-item:has(.badge.bg-info) .timeline-icon {
    background: linear-gradient(135deg, #17a2b8, #20c9e7);
}

.timeline-item:has(.badge.bg-success) .timeline-icon {
    background: linear-gradient(135deg, #28a745, #34d058);
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
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
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>