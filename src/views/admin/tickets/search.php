<?php
ob_start();
$page_title = $data['page_title'] ?? 'Search Tickets - SAMPARK Admin';
$user = $data['user'];
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Search Tickets</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/admin/tickets">Tickets</a></li>
                    <li class="breadcrumb-item active">Search</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= Config::getAppUrl() ?>/admin/tickets" class="btn btn-secondary">
                <i class="fas fa-list"></i> View All Tickets
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search"></i> Search Tickets
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
                <div class="col-md-9 d-flex align-items-end">
                    <button type="button" id="performSearch" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" id="clearSearch" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Info -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        <strong>Search Scope:</strong> This search shows all tickets in the system regardless of status.
        You can search by complaint number, date range, customer mobile, or customer email.
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
                    Use the form above to search for tickets by complaint number, date range, customer mobile, or email.
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


<!-- Include DataTables CSS (JS is loaded in layout) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<script>
let searchTicketsTable;

// Wait for jQuery and DataTables to be loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
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
            url: '<?= Config::getAppUrl() ?>/admin/tickets/search/data',
            type: 'POST',
            data: function(d) {
                // Add search criteria data
                d.complaint_number = $('#complaint_number').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.customer_mobile = $('#customer_mobile').val();
                d.customer_email = $('#customer_email').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, thrown);
                alert('Failed to search tickets. Please try again.');
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
            { data: 8, name: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']], // Order by date column
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
            // Re-initialize tooltips if any
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Show results count in header
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
                                  $('#customer_email').val();

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
    $('#searchForm input').on('keypress', function(e) {
        if (e.which === 13) {
            $('#performSearch').click();
        }
    });
    }); // End jQuery ready
}); // End DOMContentLoaded

</script>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>