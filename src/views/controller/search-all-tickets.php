<?php
/**
 * Controller Tickets Search - SAMPARK
 * Search across all tickets without RBAC restrictions
 */

ob_start();
$page_title = 'Search All Tickets - SAMPARK';
$user = $data['user'] ?? [];

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
];
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Search All Tickets</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/controller/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/controller/tickets">My Department</a></li>
                    <li class="breadcrumb-item active">Search All</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= Config::getAppUrl() ?>/controller/my-department" class="btn btn-secondary">
                <i class="fas fa-list"></i> My Department Tickets
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search"></i> Search Tickets (All System Tickets)
            </h6>
        </div>
        <div class="card-body">
            <form id="searchForm" class="row g-3">
                <div class="col-md-3">
                    <label for="complaint_number" class="form-label">Complaint Number</label>
                    <input type="text" name="complaint_number" id="complaint_number" class="form-control"
                           placeholder="Enter complaint number">
                    <div class="form-text">Partial matches are supported</div>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="customer_mobile" class="form-label">Customer Mobile</label>
                    <input type="text" name="customer_mobile" id="customer_mobile" class="form-control"
                           placeholder="Enter mobile number">
                    <div class="form-text">Partial matches are supported</div>
                </div>
                <div class="col-md-3">
                    <label for="customer_email" class="form-label">Customer Email</label>
                    <input type="email" name="customer_email" id="customer_email" class="form-control"
                           placeholder="Enter email address">
                    <div class="form-text">Partial matches are supported</div>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="awaiting_info">Awaiting Info</option>
                        <option value="awaiting_approval">Awaiting Approval</option>
                        <option value="awaiting_feedback">Awaiting Feedback</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-control">
                        <option value="">All Priorities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" id="performSearch" class="btn btn-primary flex-fill">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" id="clearSearch" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Info -->
    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Unrestricted Search:</strong> This search shows all tickets in the system regardless of assignment or department.
        You can search by complaint number, date range, customer details, status, or priority.
        At least one search parameter must be provided.
    </div>

    <!-- Search Results -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Search Results
            </h6>
        </div>
        <div class="card-body">
            <div id="noSearchMessage" class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Enter search criteria</h5>
                <p class="text-muted">
                    Use the form above to search for tickets across the entire system. You can search by complaint number,
                    date range, customer mobile, email, status, or priority.
                </p>
            </div>

            <div id="searchResultsTable" style="display: none;">
                <div class="table-responsive">
                    <table id="searchTicketsTable" class="table table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Description</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                                <th>Date</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<script>
let searchTicketsTable;

document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    jQuery(document).ready(function($) {
    // Initialize DataTable (but don't load data initially)
    searchTicketsTable = $('#searchTicketsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: '<?= Config::getAppUrl() ?>/controller/search-all/data',
            type: 'POST',
            data: function(d) {
                // Add search criteria data
                d.complaint_number = $('#complaint_number').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.customer_mobile = $('#customer_mobile').val();
                d.customer_email = $('#customer_email').val();
                d.status = $('#status').val();
                d.priority = $('#priority').val();
                d.csrf_token = CSRF_TOKEN;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, thrown);
                alert('Failed to search tickets. Please try again.');
            }
        },
        columns: [
            {
                data: 0,
                name: 'complaint_id',
                orderable: true,
                render: function(data, type, row) {
                    return `<a href="${APP_URL}/controller/tickets/${data}" class="fw-semibold text-decoration-none text-primary">#${data}</a>`;
                }
            },
            {
                data: 1,
                name: 'description',
                orderable: true,
                render: function(data, type, row) {
                    if (data.length > 50) {
                        return '<span title="' + data + '">' + data.substring(0, 50) + '...</span>';
                    }
                    return data;
                }
            },
            { data: 2, name: 'customer_name', orderable: true },
            { data: 3, name: 'shed_name', orderable: true },
            { data: 4, name: 'category', orderable: true },
            {
                data: 5,
                name: 'status',
                orderable: true,
                render: function(data, type, row) {
                    const statusClasses = {
                        'pending': 'warning',
                        'awaiting_info': 'info',
                        'awaiting_approval': 'primary',
                        'awaiting_feedback': 'success',
                        'closed': 'dark'
                    };
                    const badgeClass = statusClasses[data] || 'secondary';
                    const displayStatus = data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    return `<span class="badge bg-${badgeClass}">${displayStatus}</span>`;
                }
            },
            {
                data: 6,
                name: 'priority',
                orderable: true,
                render: function(data, type, row) {
                    const priorityClasses = {
                        'critical': 'danger',
                        'high': 'warning',
                        'medium': 'info',
                        'normal': 'secondary'
                    };
                    const badgeClass = priorityClasses[data] || 'secondary';
                    const displayPriority = data.charAt(0).toUpperCase() + data.slice(1);
                    return `<span class="badge bg-${badgeClass}">${displayPriority}</span>`;
                }
            },
            {
                data: 7,
                name: 'assigned_to',
                orderable: true,
                render: function(data, type, row) {
                    return data || 'Unassigned';
                }
            },
            { data: 8, name: 'created_at', orderable: true },
            {
                data: 9,
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const ticketId = row[0];
                    return `
                        <div class="btn-group" role="group">
                            <a href="${APP_URL}/controller/tickets/${ticketId}" class="btn btn-sm btn-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        order: [[8, 'desc']], // Order by date column
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Searching tickets...',
            emptyTable: 'No tickets found matching your search criteria',
            zeroRecords: 'No tickets found matching your search criteria'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function(settings) {
            $('[data-bs-toggle="tooltip"]').tooltip();

            const api = this.api();
            const info = api.page.info();
            $('.card-header h6').html('<i class="fas fa-list"></i> Search Results (' + info.recordsTotal + ' found)');
        }
    });

    // Search functionality
    $('#performSearch').click(function() {
        const hasSearchCriteria = $('#complaint_number').val() ||
                                  $('#date_from').val() ||
                                  $('#date_to').val() ||
                                  $('#customer_mobile').val() ||
                                  $('#customer_email').val() ||
                                  $('#status').val() ||
                                  $('#priority').val();

        if (!hasSearchCriteria) {
            alert('Please enter at least one search criterion.');
            return;
        }

        $('#noSearchMessage').hide();
        $('#searchResultsTable').show();
        searchTicketsTable.ajax.reload();
    });

    $('#clearSearch').click(function() {
        $('#searchForm')[0].reset();
        $('#searchResultsTable').hide();
        $('#noSearchMessage').show();
        $('.card-header h6').html('<i class="fas fa-list"></i> Search Results');
    });

    // Enter key on search inputs
    $('#searchForm input, #searchForm select').on('keypress', function(e) {
        if (e.which === 13) {
            $('#performSearch').click();
        }
    });
    }); // End jQuery ready
}); // End DOMContentLoaded
</script>

<style>
/* Custom styles for search page */
.card {
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.badge {
    font-size: 0.75em;
    font-weight: 500;
}

.table th {
    background-color: #f8f9fc;
    border-bottom: 2px solid #e3e6f0;
    color: #5a5c69;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f1f1;
}

.table tbody tr:hover {
    background-color: #f8f9fc;
}

/* Status badges */
.badge.bg-danger { background-color: #e74a3b !important; }
.badge.bg-warning { background-color: #f39c12 !important; color: #212529 !important; }
.badge.bg-info { background-color: #3498db !important; }
.badge.bg-secondary { background-color: #95a5a6 !important; }
.badge.bg-success { background-color: #27ae60 !important; }
.badge.bg-primary { background-color: #3498db !important; }
.badge.bg-dark { background-color: #2c3e50 !important; }

/* Search form styling */
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background-color: #667eea;
    border-color: #667eea;
}

.btn-primary:hover {
    background-color: #5a6fd8;
    border-color: #5a6fd8;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

    .card-body {
        padding: 1rem;
    }
}

/* Loading states */
.dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    margin-left: -100px;
    margin-top: -26px;
    text-align: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>