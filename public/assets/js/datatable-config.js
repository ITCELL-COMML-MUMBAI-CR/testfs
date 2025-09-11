/**
 * DataTable Configuration for SAMPARK with Auto-refresh
 * Provides consistent DataTable setup with background refresh
 */

// Default DataTable configuration
const DATATABLE_DEFAULTS = {
    processing: true,
    serverSide: false, // We handle data refresh manually
    responsive: true,
    autoWidth: false,
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    order: [[0, 'desc']], // Default order by first column descending
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
         '<"row"<"col-sm-12"tr>>' +
         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    // Ensure proper styling
    className: 'table table-striped table-hover',
    language: {
        processing: '<div class="d-flex justify-content-center"><div class="loader"></div></div>',
        emptyTable: 'No tickets found',
        info: 'Showing _START_ to _END_ of _TOTAL_ tickets',
        infoEmpty: 'Showing 0 to 0 of 0 tickets',
        infoFiltered: '(filtered from _MAX_ total tickets)',
        lengthMenu: 'Show _MENU_ tickets',
        search: 'Search tickets:',
        zeroRecords: 'No matching tickets found',
        paginate: {
            first: 'First',
            last: 'Last',
            next: 'Next',
            previous: 'Previous'
        }
    },
    drawCallback: function(settings) {
        // Re-initialize tooltips and popovers after draw (Bootstrap 5)
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"], [data-toggle="popover"]'));
        const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Apply visual enhancements - get the API instance from settings
        const api = new $.fn.dataTable.Api(settings);
        applyTableEnhancements(api);
    }
};

/**
 * Initialize DataTable with auto-refresh capability
 */
function initializeDataTable(tableId, config = {}) {
    const $table = $(`#${tableId}`);
    if ($table.length === 0) {
        console.error(`Table with ID '${tableId}' not found`);
        return null;
    }
    
    // Merge default config
    const finalConfig = $.extend(true, {}, DATATABLE_DEFAULTS, config);
    
    // Initialize DataTable
    const dataTable = $table.DataTable(finalConfig);
    
    // Register with background refresh manager if available
    if (window.backgroundRefreshManager) {
        window.backgroundRefreshManager.registerDataTable(tableId, dataTable, {
            refreshUrl: config.ajax?.url || `${window.APP_URL || ''}/api/tickets/refresh`,
            onBeforeRefresh: function(id, table) {
                // Show subtle loading indicator
                $(`#${id}_wrapper`).addClass('refreshing');
            },
            onAfterRefresh: function(id, table, data) {
                // Hide loading indicator
                $(`#${id}_wrapper`).removeClass('refreshing');
                
                // Update last refresh time display
                updateLastRefreshTime();
                
                // Show notification for important changes
                checkForImportantUpdates(data);
            }
        });
    }
    
    return dataTable;
}

/**
 * Customer Tickets DataTable Configuration
 */
function initializeCustomerTicketsTable(tableId = 'customerTicketsTable') {
    const config = {
        ajax: {
            url: `${window.APP_URL || ''}/api/tickets/refresh`,
            type: 'GET',
            data: function(d) {
                // Add filters from form
                const filters = getActiveFilters();
                return $.extend({}, d, filters);
            }
        },
        columns: [
            {
                data: 'complaint_id',
                title: 'Ticket ID',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const urgentClass = row.is_urgent ? 'text-danger font-weight-bold' : '';
                        return `<a href="${window.APP_URL || ''}/customer/tickets/${data}" class="ticket-link ${urgentClass}">${data}</a>`;
                    }
                    return data;
                }
            },
            {
                data: 'category',
                title: 'Category',
                render: function(data, type, row) {
                    return `<span class="category-label">${data}</span><br><small class="text-muted">${row.type}</small><br><small class="text-info">${row.subtype || 'N/A'}</small>`;
                }
            },
            {
                data: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    const statusBadgeClass = getStatusBadgeClass(data);
                    return `<span class="badge ${statusBadgeClass}">${formatStatus(data)}</span>`;
                }
            },
            {
                data: 'shed_name',
                title: 'Location',
                render: function(data, type, row) {
                    return `${data}<br><small class="text-muted">${row.shed_code}</small>`;
                }
            },
            {
                data: 'created_at',
                title: 'Date',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const date = new Date(data);
                        return date.toLocaleDateString();
                    }
                    return data;
                }
            },
            {
                data: 'created_at',
                title: 'Time',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const date = new Date(data);
                        return date.toLocaleTimeString();
                    }
                    return data;
                }
            },
            {
                data: 'description',
                title: 'Description',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const truncated = data && data.length > 50 ? data.substring(0, 50) + '...' : (data || 'N/A');
                        return `<span title="${data || ''}">${truncated}</span>`;
                    }
                    return data;
                }
            },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    let actions = `<a href="${window.APP_URL || ''}/customer/tickets/${row.complaint_id}" class="btn btn-sm btn-primary text-white">View</a>`;
                    
                    if (row.status === 'awaiting_feedback') {
                        actions += ` <button class="btn btn-sm btn-success text-white" onclick="provideFeedback('${row.complaint_id}')">Feedback</button>`;
                    }
                    
                    if (row.status === 'awaiting_info') {
                        actions += ` <button class="btn btn-sm btn-info text-white" onclick="provideAdditionalInfo('${row.complaint_id}')">Provide Info</button>`;
                    }
                    
                    return actions;
                }
            }
        ],
        order: [[4, 'desc']], // Order by created date
        rowCallback: function(row, data) {
            // Add visual indicators for urgent tickets
            if (data.is_urgent) {
                $(row).addClass('urgent-ticket');
            }
            
            if (data.is_sla_violated) {
                $(row).addClass('sla-violation');
            }
            
            // Add tooltip for SLA status
            if (data.sla_status && data.sla_status !== 'no_sla') {
                $(row).attr('data-bs-toggle', 'tooltip')
                     .attr('title', `SLA Status: ${data.sla_status.replace('_', ' ').toUpperCase()}`);
            }
        }
    };
    
    return initializeDataTable(tableId, config);
}

/**
 * Controller Tickets DataTable Configuration
 */
function initializeControllerTicketsTable(tableId = 'controllerTicketsTable') {
    const config = {
        ajax: {
            url: `${window.APP_URL || ''}/api/tickets/refresh`,
            type: 'GET',
            data: function(d) {
                const filters = getActiveFilters();
                return $.extend({}, d, filters);
            }
        },
        columns: [
            {
                data: 'complaint_id',
                title: 'Ticket ID',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const urgentClass = row.is_urgent ? 'text-danger font-weight-bold' : '';
                        return `<a href="${window.APP_URL || ''}/controller/tickets/${data}" class="ticket-link ${urgentClass}">${data}</a>`;
                    }
                    return data;
                }
            },
            {
                data: 'customer_name',
                title: 'Customer',
                render: function(data, type, row) {
                    return `<strong>${data}</strong><br><small class="text-muted">${row.company_name || ''}</small>`;
                }
            },
            {
                data: 'category',
                title: 'Category',
                render: function(data, type, row) {
                    return `<span class="category-label">${data}</span><br><small class="text-muted">${row.type}</small><br><small class="text-info">${row.subtype || 'N/A'}</small>`;
                }
            },
            {
                data: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    const statusBadgeClass = getStatusBadgeClass(data);
                    let statusText = formatStatus(data);
                    
                    if (row.assigned_user_name && data === 'pending') {
                        statusText += `<br><small>Assigned to: ${row.assigned_user_name}</small>`;
                    }
                    
                    return `<span class="badge ${statusBadgeClass}">${statusText}</span>`;
                }
            },
            {
                data: 'assigned_user_name',
                title: 'Assigned To',
                render: function(data, type, row) {
                    return data || 'Unassigned';
                }
            },
            {
                data: 'created_at',
                title: 'Date',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const date = new Date(data);
                        return date.toLocaleDateString();
                    }
                    return data;
                }
            },
            {
                data: 'created_at',
                title: 'Time',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const date = new Date(data);
                        return date.toLocaleTimeString();
                    }
                    return data;
                }
            },
            {
                data: 'description',
                title: 'Description',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const truncated = data && data.length > 50 ? data.substring(0, 50) + '...' : (data || 'N/A');
                        return `<span title="${data || ''}">${truncated}</span>`;
                    }
                    return data;
                }
            },
            {
                data: 'sla_status',
                title: 'SLA',
                render: function(data, type, row) {
                    if (row.is_sla_violated) {
                        return `<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Violated</span>`;
                    } else if (data === 'warning') {
                        return `<span class="text-warning"><i class="fas fa-clock"></i> Warning</span>`;
                    } else if (data === 'ok') {
                        return `<span class="text-success"><i class="fas fa-check"></i> OK</span>`;
                    }
                    return 'N/A';
                }
            },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `<a href="${window.APP_URL || ''}/controller/tickets/${row.complaint_id}" class="btn btn-sm btn-primary">Manage</a>`;
                }
            }
        ],
        order: [[5, 'desc']], // Order by created date
        rowCallback: function(row, data) {
            // Add visual indicators
            if (data.is_urgent) {
                $(row).addClass('urgent-ticket');
            }
            
            if (data.is_sla_violated) {
                $(row).addClass('table-danger sla-violation');
            } else if (data.sla_status === 'warning') {
                $(row).addClass('table-warning');
            }
        }
    };
    
    return initializeDataTable(tableId, config);
}

/**
 * Get active filters from form
 */
function getActiveFilters() {
    const filters = {};
    
    // Get filter values from form elements
    const statusFilter = $('#statusFilter').val();
    const priorityFilter = $('#priorityFilter').val();
    const dateFromFilter = $('#dateFromFilter').val();
    const dateToFilter = $('#dateToFilter').val();
    const divisionFilter = $('#divisionFilter').val();
    
    if (statusFilter) filters.status = statusFilter;
    if (priorityFilter) filters.priority = priorityFilter;
    if (dateFromFilter) filters.date_from = dateFromFilter;
    if (dateToFilter) filters.date_to = dateToFilter;
    if (divisionFilter) filters.division = divisionFilter;
    
    return filters;
}

/**
 * Format status for display
 */
function formatStatus(status) {
    const statusLabels = {
        'pending': 'Pending Review',
        'awaiting_feedback': 'Awaiting Feedback',
        'awaiting_info': 'Need More Info',
        'awaiting_approval': 'Pending Approval',
        'closed': 'Closed'
    };
    
    return statusLabels[status] || status;
}

/**
 * Get status badge class for proper coloring
 */
function getStatusBadgeClass(status) {
    const statusClasses = {
        'pending': 'status-pending',
        'awaiting_feedback': 'status-awaiting-feedback',
        'awaiting_info': 'status-awaiting-info',
        'awaiting_approval': 'status-awaiting-approval',
        'closed': 'status-closed'
    };
    
    return statusClasses[status] || 'badge-secondary';
}

/**
 * Format relative time
 */
function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffDays > 0) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    } else if (diffHours > 0) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    } else {
        const diffMins = Math.floor(diffMs / (1000 * 60));
        return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    }
}

/**
 * Format duration in hours to readable format
 */
function formatDuration(hours) {
    const days = Math.floor(hours / 24);
    const remainingHours = hours % 24;
    
    if (days > 0) {
        return `${days}d ${remainingHours}h`;
    } else {
        return `${hours}h`;
    }
}

/**
 * Format date time
 */
function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString();
}

/**
 * Apply visual enhancements to table
 */
function applyTableEnhancements(table) {
    const $wrapper = $(table.table().container());
    
    // Add loading class styling
    if (!$wrapper.find('.refreshing-indicator').length) {
        $wrapper.prepend('<div class="refreshing-indicator" style="display: none;"><i class="fas fa-sync-alt fa-spin"></i> Refreshing...</div>');
    }
    
    // Ensure proper text colors are applied
    $wrapper.find('table.dataTable th, table.dataTable td').each(function() {
        const $this = $(this);
        if ($this.css('color') === 'rgb(255, 255, 255)' || $this.css('color') === 'white') {
            $this.css('color', '#212529');
        }
    });
    
    // Force proper styling on table elements
    $wrapper.find('table.dataTable').addClass('table table-striped table-hover');
}

/**
 * Update last refresh time display
 */
function updateLastRefreshTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    
    $('.last-refresh-time').text(`Last updated: ${timeString}`);
}

/**
 * Check for important updates and show notifications
 */
function checkForImportantUpdates(data) {
    if (!data || !data.data) return;
    
    // Count urgent tickets
    const urgentCount = data.data.filter(ticket => ticket.is_urgent).length;
    const slaViolations = data.data.filter(ticket => ticket.is_sla_violated).length;
    
    // Show notification if there are urgent items
    if (urgentCount > 0 || slaViolations > 0) {
        showSubtleNotification(`${urgentCount} urgent tickets, ${slaViolations} SLA violations`);
    }
}

/**
 * Show subtle notification
 */
function showSubtleNotification(message) {
    // Create or update notification element
    let $notification = $('#refresh-notification');
    
    if ($notification.length === 0) {
        $notification = $('<div id="refresh-notification" class="alert alert-info alert-dismissible fade" style="position: fixed; top: 70px; right: 20px; z-index: 1050; min-width: 300px;"></div>');
        $('body').append($notification);
    }
    
    $notification.html(`
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
        <i class="fas fa-info-circle"></i> ${message}
    `).addClass('show');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $notification.removeClass('show');
    }, 5000);
}

// Initialize filter change handlers
$(document).ready(function() {
    // Refresh DataTable when filters change
    $('.ticket-filter').on('change', function() {
        if (window.backgroundRefreshManager) {
            // Force immediate refresh when user changes filters
            window.backgroundRefreshManager.forceRefresh();
        }
    });
    
    // Add last refresh time display
    if ($('.datatable-header').length && !$('.last-refresh-time').length) {
        $('.datatable-header').append('<small class="text-muted last-refresh-time float-right">Last updated: --</small>');
    }
    
    // Fix any existing DataTables with white text issues
    $('.dataTables_wrapper table.dataTable th, .dataTables_wrapper table.dataTable td').each(function() {
        const $this = $(this);
        if ($this.css('color') === 'rgb(255, 255, 255)' || $this.css('color') === 'white') {
            $this.css('color', '#212529');
        }
    });
    
    // Ensure proper table classes are applied
    $('.dataTables_wrapper table.dataTable').addClass('table table-striped table-hover');
});