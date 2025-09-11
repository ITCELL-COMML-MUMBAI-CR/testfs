<?php
// Capture the content
ob_start();
?>

<!-- Admin User View -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/admin/users">Users</a></li>
                                <li class="breadcrumb-item active">User Details</li>
                            </ol>
                        </nav>
                        <h1 class="display-3 mb-0"><?= htmlspecialchars($user_to_view['name']) ?></h1>
                        <div class="mt-2">
                            <span class="badge <?= $user_to_view['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?> me-2">
                                <?= ucfirst($user_to_view['status']) ?>
                            </span>
                            <span class="badge <?= getRoleBadgeClass($user_to_view['role']) ?>">
                                <?= $roles[$user_to_view['role']] ?? ucfirst($user_to_view['role']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex gap-2">
                        <a href="<?= Config::getAppUrl() ?>/admin/users/<?= $user_to_view['id'] ?>/edit" class="btn btn-apple-primary">
                            <i class="fas fa-edit me-2"></i>Edit User
                        </a>
                        <button type="button" class="btn btn-apple-glass" onclick="confirmToggleStatus()">
                            <?php if ($user_to_view['status'] === 'active'): ?>
                                <i class="fas fa-ban me-2"></i>Deactivate
                            <?php else: ?>
                                <i class="fas fa-check-circle me-2"></i>Activate
                            <?php endif; ?>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-apple-glass dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-2"></i>Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item" onclick="showPasswordResetModal()">
                                        <i class="fas fa-key me-2"></i>Reset Password
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a href="<?= Config::getAppUrl() ?>/admin/users" class="dropdown-item">
                                        <i class="fas fa-list me-2"></i>All Users
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Details -->
        <div class="row">
            <!-- Left Column - Basic Info -->
            <div class="col-md-4">
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-<?= $user_to_view['status'] === 'active' ? 'success' : 'secondary' ?> rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                <i class="fas fa-user text-white" style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <h4 class="mb-1"><?= htmlspecialchars($user_to_view['name']) ?></h4>
                                <div class="text-apple-blue"><?= htmlspecialchars($user_to_view['login_id']) ?></div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Contact Information</h5>
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-envelope"></i></div>
                                <div><?= htmlspecialchars($user_to_view['email']) ?></div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-phone"></i></div>
                                <div><?= htmlspecialchars($user_to_view['mobile']) ?></div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Account Status</h5>
                        <div class="d-flex align-items-center mb-2">
                            <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-shield-alt"></i></div>
                            <div><?= $roles[$user_to_view['role']] ?? ucfirst($user_to_view['role']) ?></div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-circle"></i></div>
                            <div><?= ucfirst($user_to_view['status']) ?></div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-calendar"></i></div>
                            <div>Created: <?= date('d M Y', strtotime($user_to_view['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="card-apple-glass">
                    <div class="card-body">
                        <h5 class="mb-3">Organization</h5>
                        <div class="d-flex align-items-center mb-2">
                            <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-building"></i></div>
                            <div><?= htmlspecialchars($user_to_view['department']) ?></div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-map-marker-alt"></i></div>
                            <div><?= htmlspecialchars($user_to_view['division']) ?></div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="text-apple-blue me-2" style="width: 24px;"><i class="fas fa-globe"></i></div>
                            <div><?= htmlspecialchars($user_to_view['zone']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Additional Info -->
            <div class="col-md-8">
                <!-- User Activity -->
                <div class="card-apple mb-4">
                    <div class="card-header-apple d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Activity</h5>
                        <a href="#" class="btn btn-sm btn-apple-glass">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="timeline-activity">
                            <?php 
                            // This would typically be populated from a query
                            $hasActivity = false;
                            ?>
                            
                            <?php if (!$hasActivity): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-history mb-3" style="font-size: 24px;"></i>
                                    <p class="mb-0">No recent activity found for this user.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Tickets Handled -->
                <div class="card-apple">
                    <div class="card-header-apple d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tickets Handled</h5>
                        <a href="#" class="btn btn-sm btn-apple-glass">View All</a>
                    </div>
                    <div class="card-body">
                        <?php 
                        // This would typically be populated from a query
                        $hasTickets = false;
                        ?>
                        
                        <?php if (!$hasTickets): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-ticket-alt mb-3" style="font-size: 24px;"></i>
                                <p class="mb-0">No tickets have been handled by this user yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Password Reset Modal -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordResetModalLabel">Reset User Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Enter a new password for <strong><?= htmlspecialchars($user_to_view['name']) ?></strong> or generate a random one:</p>
                
                <div class="mb-3">
                    <label for="newPassword" class="form-label-apple">New Password</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-apple" id="newPassword" placeholder="Enter or generate password">
                        <button class="btn btn-apple-glass" type="button" id="generatePasswordBtn">
                            <i class="fas fa-dice me-1"></i>Generate
                        </button>
                    </div>
                    <div class="form-text">
                        Password should be at least 8 characters long with a mix of letters, numbers, and symbols.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-apple-glass" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-apple-primary" id="confirmResetBtn">Reset Password</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize components
document.addEventListener('DOMContentLoaded', function() {
    // Generate random password
    document.getElementById('generatePasswordBtn').addEventListener('click', function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('newPassword').value = password;
    });
    
    // Reset password confirmation
    document.getElementById('confirmResetBtn').addEventListener('click', function() {
        const password = document.getElementById('newPassword').value;
        
        if (!password) {
            Swal.fire('Error', 'Please enter or generate a password', 'error');
            return;
        }
        
        resetPassword(password);
    });
});

// Show password reset modal
function showPasswordResetModal() {
    const modal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
    modal.show();
}

// Reset user password
function resetPassword(password = null) {
    const requestData = password ? { password: password } : {};
    
    fetch(`<?= Config::getAppUrl() ?>/admin/users/<?= $user_to_view['id'] ?>/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?= $csrf_token ?>'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('passwordResetModal'));
            if (modal) {
                modal.hide();
            }
            
            // Show success message with password if provided
            if (data.new_password) {
                Swal.fire({
                    title: 'Password Reset Successful',
                    html: `
                        <p>The user password has been reset to:</p>
                        <div class="alert alert-info mt-3 mb-0">
                            <code class="fs-5">${data.new_password}</code>
                        </div>
                        <p class="mt-3 small">Please provide this password to the user securely.</p>
                    `,
                    icon: 'success'
                });
            } else {
                Swal.fire('Success', data.message, 'success');
            }
        } else {
            Swal.fire('Error', data.message || 'Failed to reset password', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'An unexpected error occurred', 'error');
    });
}

// Toggle user status
function confirmToggleStatus() {
    const currentStatus = '<?= $user_to_view['status'] ?>';
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const actionText = currentStatus === 'active' ? 'deactivate' : 'activate';
    
    Swal.fire({
        title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} User?`,
        text: `Are you sure you want to ${actionText} this user?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: currentStatus === 'active' ? '#dc3545' : '#28a745',
        confirmButtonText: `Yes, ${actionText} user`
    }).then((result) => {
        if (result.isConfirmed) {
            toggleUserStatus(newStatus);
        }
    });
}

function toggleUserStatus(newStatus) {
    fetch(`<?= Config::getAppUrl() ?>/admin/users/<?= $user_to_view['id'] ?>/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?= $csrf_token ?>'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Updated!',
                text: data.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Reload the page to reflect changes
                window.location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to update user status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'An unexpected error occurred', 'error');
    });
}
</script>

<style>
.timeline-activity {
    position: relative;
    padding-left: 30px;
}

.timeline-activity::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: rgba(0,0,0,0.1);
}
</style>

<?php
// Helper function to get role badge class
function getRoleBadgeClass($role) {
    switch ($role) {
        case 'admin':
        case 'superadmin':
            return 'bg-danger';
        case 'controller':
            return 'bg-primary';
        case 'controller_nodal':
            return 'bg-warning text-dark';
        case 'customer_support':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>