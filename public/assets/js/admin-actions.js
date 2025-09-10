/**
 * Admin Actions JavaScript
 * Handles user actions in admin sections
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Action buttons
    initializeActionButtons();
    
    // Make sure APP_URL is defined
    if (typeof APP_URL === 'undefined') {
        // Try to get it from a meta tag or set a default
        const appUrlMeta = document.querySelector('meta[name="app-url"]');
        window.APP_URL = appUrlMeta ? appUrlMeta.getAttribute('content') : '';
    }
    
    // Make sure CSRF_TOKEN is defined
    if (typeof CSRF_TOKEN === 'undefined') {
        // Try to get it from a meta tag
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        window.CSRF_TOKEN = csrfMeta ? csrfMeta.getAttribute('content') : '';
    }
});

/**
 * Initialize all action buttons with dropdown menus
 */
function initializeActionButtons() {
    // For Actions dropdown buttons in table rows
    const actionButtons = document.querySelectorAll('.actions-dropdown');
    
    if (actionButtons) {
        actionButtons.forEach(button => {
            // Make sure dropdowns are properly initialized with Bootstrap
            new bootstrap.Dropdown(button);
        });
    }
    
    // Set up global event delegation for any action that might be added dynamically
    document.body.addEventListener('click', function(e) {
        // Handle deactivate user
        if (e.target.matches('[data-action="deactivate"]') || e.target.closest('[data-action="deactivate"]')) {
            e.preventDefault();
            const userId = e.target.closest('[data-user-id]').dataset.userId;
            confirmStatusChange(userId, 'inactive');
        }
        
        // Handle activate user
        if (e.target.matches('[data-action="activate"]') || e.target.closest('[data-action="activate"]')) {
            e.preventDefault();
            const userId = e.target.closest('[data-user-id]').dataset.userId;
            confirmStatusChange(userId, 'active');
        }
        
        // Handle password reset
        if (e.target.matches('[data-action="reset-password"]') || e.target.closest('[data-action="reset-password"]')) {
            e.preventDefault();
            const userId = e.target.closest('[data-user-id]').dataset.userId;
            sendPasswordReset(userId);
        }
        
        // Handle edit user
        if (e.target.matches('[data-action="edit"]') || e.target.closest('[data-action="edit"]')) {
            e.preventDefault();
            const userId = e.target.closest('[data-user-id]').dataset.userId;
            window.location.href = `${APP_URL}/admin/users/${userId}/edit`;
        }
        
        // Handle view user
        if (e.target.matches('[data-action="view"]') || e.target.closest('[data-action="view"]')) {
            e.preventDefault();
            const userId = e.target.closest('[data-user-id]').dataset.userId;
            window.location.href = `${APP_URL}/admin/users/${userId}`;
        }
    });
}

/**
 * Confirm and change user status
 * @param {string} userId - User ID
 * @param {string} status - New status (active/inactive)
 */
function confirmStatusChange(userId, status) {
    const actionText = status === 'active' ? 'activate' : 'deactivate';
    const actionColor = status === 'active' ? '#28a745' : '#dc3545';
    
    Swal.fire({
        title: `${status === 'active' ? 'Activate' : 'Deactivate'} User?`,
        text: `Are you sure you want to ${actionText} this user?`,
        icon: status === 'active' ? 'info' : 'warning',
        showCancelButton: true,
        confirmButtonColor: actionColor,
        confirmButtonText: `Yes, ${actionText} user`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request to change status
            fetch(`${APP_URL}/admin/users/${userId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: `User has been ${status === 'active' ? 'activated' : 'deactivated'}.`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to show updated status
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to update user status.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred.', 'error');
            });
        }
    });
}

/**
 * Send password reset for user
 * @param {string} userId - User ID
 */
function sendPasswordReset(userId) {
    Swal.fire({
        title: 'Reset User Password',
        text: 'Enter a new password for this user:',
        input: 'password',
        inputAttributes: {
            autocapitalize: 'off',
            autocorrect: 'off',
            minlength: 8
        },
        showCancelButton: true,
        confirmButtonText: 'Reset Password',
        showLoaderOnConfirm: true,
        preConfirm: (password) => {
            if (!password || password.length < 8) {
                Swal.showValidationMessage('Password must be at least 8 characters long');
                return false;
            }
            
            return fetch(`${APP_URL}/admin/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ new_password: password })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Password has been reset successfully.',
                icon: 'success'
            });
        } else if (result.isConfirmed) {
            Swal.fire({
                title: 'Error!',
                text: result.value?.message || 'Failed to reset password.',
                icon: 'error'
            });
        }
    });
}
