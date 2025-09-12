<?php
/**
 * Ticket Print View - SAMPARK
 * Print-friendly view for tickets with evidence
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: white;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .section {
            margin: 20px 0;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 18px;
            color: #007bff;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 150px;
            color: #666;
        }
        
        .info-value {
            flex: 1;
        }
        
        .description-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .transaction {
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .transaction-type {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 8px;
        }
        
        .transaction-meta {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        
        .evidence-item {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #cce7f0; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .print-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <button class="print-btn" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div class="header">
        <div class="logo">SAMPARK - Support Ticket System</div>
        <h2>Ticket #<?= htmlspecialchars($ticket['complaint_id']) ?></h2>
        <div style="margin-top: 10px;">
            <span class="badge badge-<?= 
                $ticket['status'] === 'closed' ? 'success' : 
                ($ticket['status'] === 'pending' ? 'warning' : 
                ($ticket['status'] === 'awaiting_info' ? 'info' : 'secondary')) ?>">
                <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
            </span>
            <span class="badge badge-<?= 
                $ticket['priority'] === 'critical' ? 'danger' : 
                ($ticket['priority'] === 'high' ? 'warning' : 
                ($ticket['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                <?= ucfirst($ticket['priority']) ?> Priority
            </span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Ticket Information</div>
        <div class="info-row">
            <div class="info-label">Created:</div>
            <div class="info-value"><?= date('M d, Y H:i', strtotime($ticket['created_at'])) ?></div>
        </div>
        <?php if ($ticket['updated_at'] !== $ticket['created_at']): ?>
        <div class="info-row">
            <div class="info-label">Last Updated:</div>
            <div class="info-value"><?= date('M d, Y H:i', strtotime($ticket['updated_at'])) ?></div>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <div class="info-label">Category:</div>
            <div class="info-value"><?= htmlspecialchars($ticket['category'] ?? 'N/A') ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Location:</div>
            <div class="info-value">
                <?= htmlspecialchars($ticket['shed_name'] ?? 'N/A') ?>
                <?php if ($ticket['shed_code']): ?>
                (<?= htmlspecialchars($ticket['shed_code']) ?>)
                <?php endif; ?>
            </div>
        </div>
        <?php if ($ticket['division'] || $ticket['zone']): ?>
        <div class="info-row">
            <div class="info-label">Division/Zone:</div>
            <div class="info-value">
                <?= htmlspecialchars($ticket['division'] ?? 'N/A') ?> / <?= htmlspecialchars($ticket['zone'] ?? 'N/A') ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">Customer Information</div>
        <div class="info-row">
            <div class="info-label">Name:</div>
            <div class="info-value"><?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value"><?= htmlspecialchars($ticket['customer_email'] ?? 'N/A') ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Mobile:</div>
            <div class="info-value"><?= htmlspecialchars($ticket['customer_mobile'] ?? 'N/A') ?></div>
        </div>
        <?php if ($ticket['company_name']): ?>
        <div class="info-row">
            <div class="info-label">Company:</div>
            <div class="info-value"><?= htmlspecialchars($ticket['company_name']) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">Issue Description</div>
        <div class="description-box">
            <?= nl2br(htmlspecialchars($ticket['description'] ?? $ticket['complaint_message'] ?? 'No description provided')) ?>
        </div>
    </div>

    <?php if ($ticket['action_taken']): ?>
    <div class="section">
        <div class="section-title">Action Taken</div>
        <div class="description-box">
            <?= nl2br(htmlspecialchars($ticket['action_taken'])) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($evidence)): ?>
    <div class="section">
        <div class="section-title">Evidence Files (<?= count($evidence) ?>)</div>
        <?php foreach ($evidence as $file): ?>
        <div class="evidence-item">
            <strong><?= htmlspecialchars($file['original_name']) ?></strong>
            <br>
            <small>
                Size: <?= number_format($file['file_size'] / 1024, 1) ?> KB •
                Type: <?= htmlspecialchars($file['file_type'] ?? 'Unknown') ?> •
                Uploaded: <?= date('M d, Y', strtotime($file['uploaded_at'])) ?>
            </small>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($transactions)): ?>
    <div class="section page-break">
        <div class="section-title">Transaction History</div>
        <?php foreach ($transactions as $transaction): ?>
        <div class="transaction">
            <div class="transaction-type"><?= ucfirst(str_replace('_', ' ', $transaction['transaction_type'])) ?></div>
            <div><?= nl2br(htmlspecialchars($transaction['remarks'] ?? '')) ?></div>
            <div class="transaction-meta">
                By: <?= htmlspecialchars($transaction['user_name'] ?? $transaction['customer_name'] ?? 'System') ?>
                <?php if ($transaction['user_department']): ?>
                (<?= htmlspecialchars($transaction['user_department']) ?>
                <?php if ($transaction['user_division']): ?>
                - <?= htmlspecialchars($transaction['user_division']) ?>
                <?php endif; ?>
                <?php if ($transaction['user_zone']): ?>
                - <?= htmlspecialchars($transaction['user_zone']) ?>
                <?php endif; ?>)
                <?php endif; ?>
                • <?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        <div>Generated on <?= date('M d, Y H:i') ?> by <?= htmlspecialchars($user['name']) ?></div>
        <div>SAMPARK - Railway Customer Support System</div>
    </div>

    <script>
        // Auto-print when page loads if requested
        if (window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>