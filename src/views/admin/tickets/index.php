<?php
ob_start();
$page_title = $data['page_title'] ?? 'Admin Tickets - SAMPARK';
$user = $data['user'];
$categories = $data['categories'] ?? [];
$status_options = $data['status_options'] ?? [];
$priority_options = $data['priority_options'] ?? [];
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Admin Tickets</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tickets</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= Config::getAppUrl() ?>/admin/tickets/search" class="btn btn-primary">
                <i class="fas fa-search"></i> Search Tickets
            </a>
        </div>
    </div>

    <!-- Access Info -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        <strong>Access Level:</strong>
        <?php if ($user['department'] === 'ADM'): ?>
            <?php if ($user['division'] === 'HQ'): ?>
                You can view all tickets in zone <strong><?= htmlspecialchars($user['zone']) ?></strong>
            <?php else: ?>
                You can view all tickets in division <strong><?= htmlspecialchars($user['division']) ?></strong>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($user['division'] === 'HQ'): ?>
                You can view tickets in zone <strong><?= htmlspecialchars($user['zone']) ?></strong> for department <strong><?= htmlspecialchars($user['department']) ?></strong>
            <?php else: ?>
                You can view tickets in division <strong><?= htmlspecialchars($user['division']) ?></strong> for department <strong><?= htmlspecialchars($user['department']) ?></strong>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Filter Tickets</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <?php foreach ($status_options as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <?php foreach ($priority_options as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['category']) ?>">
                                <?= htmlspecialchars($category['category']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="applyFilters" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <button type="button" id="clearFilters" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-ticket-alt me-2"></i>Admin Tickets
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div id="loadingMessage" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <h5>Loading tickets...</h5>
                    <p class="text-muted">Initializing DataTables...</p>
                </div>

                <div id="errorMessage" class="text-center py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                    <h5>Error Loading Tickets</h5>
                    <p class="text-muted">Please refresh the page to try again.</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-refresh"></i> Refresh Page
                    </button>
                </div>

                <table id="ticketsTable" class="table table-hover" width="100%" style="display: none;">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Description</th>
                            <th>Customer</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Date</th>
                            <th>Admin Remarks</th>
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

<!-- Admin Remarks Modal -->
<div class="modal fade" id="remarksModal" tabindex="-1" aria-labelledby="remarksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="remarksModalLabel">Add Admin Remarks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="remarksForm" onsubmit="submitRemarks(event)">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                    <input type="hidden" id="ticketId" name="ticket_id">

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea id="remarks" name="remarks" class="form-control" rows="4"
                                  placeholder="Enter your admin remarks here..." required minlength="10" maxlength="1000"></textarea>
                        <div class="form-text">Minimum 10 characters, maximum 1000 characters.</div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> Admin remarks are internal and will not be visible to customers.
                        They will be shown separately on the controller's ticket details page.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-comment"></i> Add Remarks
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include DataTables CSS (JS is loaded in layout) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<script>
let ticketsTable;

// Wait for jQuery and DataTables to be loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
        document.getElementById('loadingMessage').style.display = 'none';
        document.getElementById('errorMessage').style.display = 'block';
        return;
    }

    jQuery(document).ready(function($) {
        // Check if DataTables is loaded
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables is not loaded');
            $('#loadingMessage').hide();
            $('#errorMessage').show();
            return;
        }

        try {
            // Initialize DataTable
            ticketsTable = $('#ticketsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?= Config::getAppUrl() ?>/admin/tickets/data',
            type: 'POST',
            data: function(d) {
                // Add filter data
                d.status = $('#status').val();
                d.priority = $('#priority').val();
                d.category = $('#category').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, thrown);
                alert('Failed to load tickets. Please refresh the page.');
            }
        },
        columns: [
            { data: 0, name: 'complaint_id', orderable: true },
            { data: 1, name: 'description', orderable: true },
            { data: 2, name: 'customer_name', orderable: true },
            { data: 3, name: 'shed_name', orderable: true },
            { data: 4, name: 'category', orderable: true },
            { data: 5, name: 'status', orderable: true },
            { data: 6, name: 'priority', orderable: true },
            { data: 7, name: 'date', orderable: true },
            { data: 8, name: 'admin_remarks_count', orderable: false },
            { data: 9, name: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']], // Order by date column
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading tickets...',
            emptyTable: 'No tickets found for your access level',
            zeroRecords: 'No tickets match your search criteria'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function(settings, json) {
            // Hide loading message and show table
            $('#loadingMessage').hide();
            $('#ticketsTable').show();
        },
        drawCallback: function(settings) {
            // Re-initialize tooltips if any
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Filter functionality
    $('#applyFilters').click(function() {
        ticketsTable.ajax.reload();
    });

    $('#clearFilters').click(function() {
        $('#filterForm')[0].reset();
        ticketsTable.ajax.reload();
    });

    // Enter key on filter inputs
    $('#filterForm input, #filterForm select').on('keypress change', function(e) {
        if (e.type === 'change' || e.which === 13) {
            ticketsTable.ajax.reload();
        }
    });

        } catch (error) {
            console.error('DataTables initialization error:', error);
            $('#loadingMessage').hide();
            $('#errorMessage').show();
        }
    }); // End jQuery ready
}); // End DOMContentLoaded

function showRemarksModal(ticketId) {
    document.getElementById('ticketId').value = ticketId;
    document.getElementById('remarks').value = '';
    document.getElementById('remarksModalLabel').textContent = 'Add Admin Remarks - ' + ticketId;

    const modal = new bootstrap.Modal(document.getElementById('remarksModal'));
    modal.show();
}

function submitRemarks(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const ticketId = formData.get('ticket_id');

    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    submitButton.disabled = true;

    fetch(`<?= Config::getAppUrl() ?>/admin/tickets/${ticketId}/remarks`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('remarksModal'));
            modal.hide();

            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-xl').insertBefore(alert, document.querySelector('.container-xl').firstElementChild);

            // Refresh DataTable
            ticketsTable.ajax.reload(null, false);

            // Remove alert after 3 seconds
            setTimeout(() => alert.remove(), 3000);
        } else {
            // Show error message
            let errorMessage = data.message || 'Failed to add remarks';
            if (data.errors) {
                errorMessage = Object.values(data.errors).join(', ');
            }

            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            form.insertBefore(alert, form.firstElementChild);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        form.insertBefore(alert, form.firstElementChild);
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}
</script>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>