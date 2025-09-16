<?php
/**
 * Routes Configuration for SAMPARK
 * Defines all application routes
 */

// Public Routes
$router->get('/', 'Home@index');
$router->get('/login', 'Auth@showLogin');
$router->post('/login', 'Auth@login');
$router->get('/change-password', 'Auth@showChangePassword');
$router->post('/change-password', 'Auth@changePassword');
$router->get('/signup', 'Auth@showSignup');
$router->post('/signup', 'Auth@signup');
$router->get('/logout', 'Auth@logout');
$router->get('/privacy-policy', 'Public@privacyPolicy');
$router->get('/help', 'Public@help');
$router->get('/help-standalone', 'Public@helpStandalone');

// Customer Routes
$router->get('/customer/dashboard', 'Customer@dashboard', ['auth', 'role:customer']);
$router->get('/customer/tickets', 'Customer@tickets', ['auth', 'role:customer']);
$router->get('/customer/tickets/create', 'Customer@createTicket', ['auth', 'role:customer']);
$router->post('/customer/tickets/create', 'Customer@storeTicket', ['auth', 'role:customer']);
$router->get('/customer/tickets/{id}', 'Customer@viewTicket', ['auth', 'role:customer']);
$router->post('/customer/tickets/{id}/feedback', 'Customer@submitFeedback', ['auth', 'role:customer']);
$router->post('/customer/tickets/{id}/provide-info', 'Customer@provideAdditionalInfo', ['auth', 'role:customer']);
$router->post('/customer/tickets/{id}/evidence', 'Customer@uploadEvidence', ['auth', 'role:customer']);
$router->delete('/customer/tickets/{id}/evidence/{evidenceId}', 'Customer@deleteEvidence', ['auth', 'role:customer']);
$router->get('/customer/profile', 'Customer@profile', ['auth', 'role:customer']);
$router->post('/customer/profile', 'Customer@updateProfile', ['auth', 'role:customer']);
$router->post('/customer/change-password', 'Customer@changePassword', ['auth', 'role:customer']);
$router->get('/customer/help', 'Customer@help', ['auth', 'role:customer']);

// Controller Routes
$router->get('/controller/dashboard', 'Controller@dashboard', ['auth', 'role:controller,controller_nodal']);
$router->get('/controller/tickets', 'Controller@tickets', ['auth', 'role:controller,controller_nodal']);
$router->get('/controller/forwarded-tickets', 'Controller@forwardedTickets', ['auth', 'role:controller_nodal']);
$router->get('/controller/tickets/{id}', 'Controller@viewTicket', ['auth', 'role:controller,controller_nodal']);
$router->post('/controller/tickets/{id}/forward', 'Controller@forwardTicket', ['auth', 'role:controller,controller_nodal']);
$router->post('/controller/tickets/{id}/reply', 'Controller@replyTicket', ['auth', 'role:controller,controller_nodal']);
$router->post('/controller/tickets/{id}/approve', 'Controller@approveReply', ['auth', 'role:controller_nodal']);
$router->post('/controller/tickets/{id}/reject', 'Controller@rejectReply', ['auth', 'role:controller_nodal']);
$router->post('/controller/tickets/{id}/revert', 'Controller@revertTicket', ['auth', 'role:controller_nodal']);
$router->post('/controller/tickets/{id}/revert-to-customer', 'Controller@revertToCustomer', ['auth', 'role:controller_nodal']);
$router->post('/controller/tickets/{id}/interim-remarks', 'Controller@addInterimRemarks', ['auth', 'role:controller_nodal']);
$router->post('/controller/tickets/{id}/internal-remarks', 'Controller@addInternalRemarks', ['auth', 'role:controller,controller_nodal']);
$router->get('/controller/tickets/{id}/print', 'Controller@printTicket', ['auth', 'role:controller,controller_nodal']);
$router->get('/controller/tickets/{id}/export', 'Controller@exportTicket', ['auth', 'role:controller,controller_nodal']);
$router->get('/controller/reports', 'Controller@reports', ['auth', 'role:controller,controller_nodal']);
$router->get('/controller/help', 'Controller@help', ['auth', 'role:controller,controller_nodal']);
$router->get('/controller/profile', 'Controller@profile', ['auth', 'role:controller,controller_nodal']);

// Admin Routes
$router->get('/admin/dashboard', 'Admin@dashboard', ['auth', 'role:admin,superadmin']);
$router->get('/admin/users', 'Admin@users', ['auth', 'role:admin,superadmin']);
$router->get('/admin/users/create', 'Admin@createUser', ['auth', 'role:admin,superadmin']);
$router->post('/admin/users/create', 'Admin@storeUser', ['auth', 'role:admin,superadmin']);
$router->get('/admin/users/{id}', 'Admin@viewUser', ['auth', 'role:admin,superadmin']);
$router->get('/admin/users/{id}/edit', 'Admin@editUser', ['auth', 'role:admin,superadmin']);
$router->post('/admin/users/{id}/edit', 'Admin@updateUser', ['auth', 'role:admin,superadmin']);
$router->post('/admin/users/{id}/update', 'Admin@updateUser', ['auth', 'role:admin,superadmin']);
$router->post('/admin/users/{id}/toggle', 'Admin@toggleUser', ['auth', 'role:admin,superadmin']);
$router->post('/admin/users/{id}/status', 'Admin@toggleUser', ['auth', 'role:admin,superadmin']);
$router->post('/admin/users/{id}/reset-password', 'Admin@resetUserPassword', ['auth', 'role:admin,superadmin']);

$router->get('/admin/customers', 'Admin@customers', ['auth', 'role:admin,superadmin']);
$router->get('/admin/customers/{id}', 'Admin@viewCustomer', ['auth', 'role:admin,superadmin']);
$router->get('/admin/customers/{id}/edit', 'Admin@editCustomer', ['auth', 'role:admin,superadmin']);
$router->post('/admin/customers/{id}/edit', 'Admin@updateCustomer', ['auth', 'role:admin,superadmin']);
$router->post('/admin/customers/{id}/update', 'Admin@updateCustomer', ['auth', 'role:admin,superadmin']);
$router->post('/admin/customers/{id}/approve', 'Admin@approveCustomer', ['auth', 'role:admin,superadmin']);
$router->post('/admin/customers/{id}/reject', 'Admin@rejectCustomer', ['auth', 'role:admin,superadmin']);
$router->post('/admin/customers/{id}/verify', 'Admin@approveCustomer', ['auth', 'role:admin,superadmin']);
$router->post('/admin/customers/{id}/status', 'Admin@updateCustomerStatus', ['auth', 'role:admin,superadmin']);

$router->get('/admin/categories', 'Admin@categories', ['auth', 'role:admin,superadmin']);
$router->post('/admin/categories', 'Admin@storeCategory', ['auth', 'role:admin,superadmin']);
$router->post('/admin/categories/{id}/edit', 'Admin@updateCategory', ['auth', 'role:admin,superadmin']);
$router->post('/admin/categories/{id}/delete', 'Admin@deleteCategory', ['auth', 'role:admin,superadmin']);

$router->get('/admin/sheds', 'Admin@sheds', ['auth', 'role:admin,superadmin']);
$router->post('/admin/sheds', 'Admin@storeShed', ['auth', 'role:admin,superadmin']);
$router->post('/admin/sheds/{id}/edit', 'Admin@updateShed', ['auth', 'role:admin,superadmin']);

$router->get('/admin/content', 'Admin@content', ['auth', 'role:admin,superadmin']);
$router->post('/admin/content/news', 'Admin@storeNews', ['auth', 'role:admin,superadmin']);
$router->post('/admin/content/announcements', 'Admin@storeAnnouncement', ['auth', 'role:admin,superadmin']);
$router->post('/admin/content/links', 'Admin@storeLink', ['auth', 'role:admin,superadmin']);

$router->get('/admin/emails', 'Admin@emails', ['auth', 'role:admin,superadmin']);
$router->get('/admin/emails/templates', 'Admin@emailTemplates', ['auth', 'role:admin,superadmin']);
$router->post('/admin/emails/send', 'Admin@sendBulkEmail', ['auth', 'role:admin,superadmin']);

$router->get('/admin/reports', 'Admin@reports', ['auth', 'role:admin,superadmin']);
$router->post('/admin/reports/generate-scheduled', 'Admin@generateScheduledReport', ['auth', 'role:admin,superadmin']);
$router->post('/admin/reports/preview-scheduled', 'Admin@previewScheduledReport', ['auth', 'role:admin,superadmin']);
$router->post('/admin/reports/export', 'Admin@exportReport', ['auth', 'role:admin,superadmin']);

// Admin ticket management routes
$router->get('/admin/tickets', 'Admin@tickets', ['auth', 'role:admin,superadmin']);
$router->get('/admin/tickets/debug', 'Admin@debug', ['auth', 'role:admin,superadmin']);
$router->get('/admin/tickets/search', 'Admin@searchTickets', ['auth', 'role:admin,superadmin']);
$router->get('/admin/tickets/{id}/view', 'Admin@viewTicket', ['auth', 'role:admin,superadmin']);
$router->post('/admin/tickets/data', 'Admin@getTicketsData', ['auth', 'role:admin,superadmin']);
$router->post('/admin/tickets/search/data', 'Admin@getSearchTicketsData', ['auth', 'role:admin,superadmin']);
$router->post('/admin/tickets/{id}/remarks', 'Admin@addAdminRemarks', ['auth', 'role:admin,superadmin']);

// API Routes
$router->get('/api/sheds/search', 'Api@searchSheds');
$router->get('/api/categories/{type}/subtypes', 'Api@getSubtypes');
$router->get('/api/categories/distinct', 'Admin@getCategoriesDistinct', ['auth', 'role:admin,superadmin']);
$router->get('/api/categories/table-data', 'Admin@getCategoriesTableData', ['auth', 'role:admin,superadmin']);
$router->get('/api/categories/{id}', 'Api@getCategory', ['auth']);
$router->get('/api/zones', 'Api@getZones', ['auth']);
$router->get('/api/divisions', 'Api@getDivisions', ['auth']);
$router->get('/api/departments', 'Api@getDepartments', ['auth']);
$router->get('/api/tickets/stats', 'Api@getTicketStats', ['auth']);
$router->get('/api/customer/stats', 'Api@getCustomerStats', ['auth']);
$router->get('/api/customer/export-data', 'Api@exportCustomerData', ['auth']);
$router->post('/api/tickets/{id}/upload', 'Api@uploadEvidence', ['auth']);
$router->get('/api/tickets/{id}/evidence/{file}', 'Api@getEvidence', ['auth']);
$router->get('/api/tickets/{id}/files', 'Api@getTicketFiles', ['auth']);
$router->get('/api/tickets/{id}/additional-info-modal', 'Api@getAdditionalInfoModal', ['auth', 'role:customer']);
$router->get('/api/tickets/updates', 'Api@getTicketUpdates', ['auth']);
$router->get('/api/notifications', 'Api@getNotifications', ['auth']);
$router->post('/api/notifications/{id}/mark-read', 'Api@markNotificationRead', ['auth']);
$router->post('/api/compress-file', 'FileCompression@compressFile', ['auth']);

// Background automation and refresh endpoints
$router->post('/api/background-automation', 'Api@processBackgroundAutomation', ['auth']);
$router->post('/api/background-tasks', 'Api@processBackgroundTasks', ['auth']);
$router->get('/api/tickets/refresh', 'Api@getRefreshedTickets', ['auth']);
$router->get('/api/heartbeat', 'Api@heartbeat'); // No auth for heartbeat
$router->get('/api/system-stats', 'Api@getSystemStats', ['auth']);

// Session management endpoints
$router->get('/api/session-status', 'Api@getSessionStatus'); // No auth required to check status
$router->post('/api/refresh-session', 'Api@refreshSession', ['auth']);

// File serving
$router->get('/uploads/evidence/{file}', 'FileController@serveEvidence', ['auth']);

// Error routes (handled by Router automatically)
// 404, 500, etc.
?>
