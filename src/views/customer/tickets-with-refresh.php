<?php
/**
 * Customer Tickets View with Background Refresh
 * Example implementation showing how to use the refresh system
 */

// This would typically be included from your main layout
$pageTitle = 'My Support Tickets - SAMPARK';
$currentPage = 'tickets';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/background-refresh.css">
    <link rel="stylesheet" href="/assets/css/controller-views.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar would go here -->
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom datatable-header">
                    <h1 class="h2">
                        <i class="fas fa-ticket-alt me-2"></i>My Support Tickets
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="/customer/tickets/create" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Create New Ticket
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="forceRefresh()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clipboard-list fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title mb-0" data-stat="total">0</h5>
                                        <small>Total Tickets</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title mb-0" data-stat="pending">0</h5>
                                        <small>Pending</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-comment-dots fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title mb-0" data-stat="awaiting_feedback">0</h5>
                                        <small>Awaiting Feedback</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title mb-0" data-stat="closed">0</h5>
                                        <small>Closed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select class="form-select ticket-filter" id="statusFilter" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="awaiting_feedback">Awaiting Feedback</option>
                                    <option value="awaiting_info">Need More Info</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="priorityFilter" class="form-label">Priority</label>
                                <select class="form-select ticket-filter" id="priorityFilter" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="normal">Normal</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="dateFromFilter" class="form-label">From Date</label>
                                <input type="date" class="form-control ticket-filter" id="dateFromFilter" name="date_from">
                            </div>
                            <div class="col-md-3">
                                <label for="dateToFilter" class="form-label">To Date</label>
                                <input type="date" class="form-control ticket-filter" id="dateToFilter" name="date_to">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Support Tickets</h5>
                        <small class="text-muted last-refresh-time">Last updated: --</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="customerTicketsTable" class="table table-striped table-hover" width="100%">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                        <th>Created</th>
                                        <th>Age</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
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
                <div class="modal-body">
                    <form id="feedbackForm">
                        <input type="hidden" id="feedbackTicketId" name="ticket_id">
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="">Select Rating</option>
                                <option value="excellent">Excellent</option>
                                <option value="satisfactory">Satisfactory</option>
                                <option value="unsatisfactory">Unsatisfactory</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" 
                                      placeholder="Please provide your feedback..."></textarea>
                            <div class="form-text">Required if rating is "Unsatisfactory"</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitFeedbackForm()">Submit Feedback</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="/assets/js/background-refresh.js"></script>
    <script src="/assets/js/datatable-config.js"></script>
    
    <script>
        // Initialize page
        $(document).ready(function() {
            // Initialize customer tickets table with auto-refresh
            const ticketsTable = initializeCustomerTicketsTable('customerTicketsTable');
            
            // Load initial stats
            loadSystemStats();
            
            console.log('Customer tickets page initialized with background refresh');
        });
        
        /**
         * Load system statistics
         */
        function loadSystemStats() {
            fetch('/api/system-stats', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatsDisplay(data.stats);
                }
            })
            .catch(error => {
                console.warn('Failed to load stats:', error);
            });
        }
        
        /**
         * Update statistics display
         */
        function updateStatsDisplay(stats) {
            Object.keys(stats).forEach(key => {
                const elements = document.querySelectorAll(`[data-stat="${key}"]`);
                elements.forEach(el => {
                    if (el.textContent !== stats[key].toString()) {
                        el.textContent = stats[key];
                        el.classList.add('stat-updated');
                        setTimeout(() => {
                            el.classList.remove('stat-updated');
                        }, 1000);
                    }
                });
            });
        }
        
        /**
         * Force refresh all data
         */
        function forceRefresh() {
            if (window.backgroundRefreshManager) {
                window.backgroundRefreshManager.forceRefresh();
                loadSystemStats();
                
                // Show brief confirmation
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';
                button.disabled = true;
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            }
        }
        
        /**
         * Submit feedback for ticket
         */
        function submitFeedback(ticketId) {
            document.getElementById('feedbackTicketId').value = ticketId;
            const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
            modal.show();
        }
        
        /**
         * Submit feedback form
         */
        function submitFeedbackForm() {
            const form = document.getElementById('feedbackForm');
            const formData = new FormData(form);
            const ticketId = formData.get('ticket_id');
            const rating = formData.get('rating');
            const remarks = formData.get('remarks');
            
            // Validate
            if (!rating) {
                alert('Please select a rating');
                return;
            }
            
            if (rating === 'unsatisfactory' && !remarks.trim()) {
                alert('Remarks are required for unsatisfactory rating');
                return;
            }
            
            // Submit
            fetch(`/customer/tickets/${ticketId}/feedback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '<?= $csrf_token ?>'
                },
                body: new URLSearchParams({
                    rating: rating,
                    remarks: remarks
                }),
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for your feedback!');
                    bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
                    
                    // Refresh table
                    if (window.backgroundRefreshManager) {
                        window.backgroundRefreshManager.forceRefresh();
                    }
                } else {
                    alert('Error submitting feedback: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Feedback submission error:', error);
                alert('Failed to submit feedback. Please try again.');
            });
        }
        
        // Handle rating change for form validation
        document.getElementById('rating').addEventListener('change', function() {
            const remarksField = document.getElementById('remarks');
            const remarksLabel = document.querySelector('label[for="remarks"]');
            
            if (this.value === 'unsatisfactory') {
                remarksField.required = true;
                remarksLabel.innerHTML = 'Remarks (Required) <span class="text-danger">*</span>';
                remarksField.placeholder = 'Please explain your concerns...';
            } else {
                remarksField.required = false;
                remarksLabel.innerHTML = 'Remarks (Optional)';
                remarksField.placeholder = 'Please provide your feedback...';
            }
        });
    </script>
</body>
</html>