/**
 * SAMPARK - Common JavaScript Functions
 * Global utilities and common functionality
 */

// Global configuration
window.SAMPARK = {
    config: {
        app_url: window.APP_URL || '',
        csrf_token: window.CSRF_TOKEN || '',
        debug: true
    },
    
    // Utility functions
    utils: {
        // Format date
        formatDate: function(date, format = 'short') {
            const d = new Date(date);
            if (format === 'short') {
                return d.toLocaleDateString();
            } else if (format === 'long') {
                return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
            }
            return d.toString();
        },
        
        // Format currency
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR'
            }).format(amount);
        },
        
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Validate email
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        // Validate phone
        isValidPhone: function(phone) {
            const re = /^[6-9]\d{9}$/;
            return re.test(phone);
        },
        
        // Generate random ID
        generateId: function() {
            return Math.random().toString(36).substr(2, 9);
        },
        
        // Get URL parameter
        getUrlParameter: function(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        },
        
        // Copy to clipboard
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                return navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    return Promise.resolve();
                } catch (err) {
                    document.body.removeChild(textArea);
                    return Promise.reject(err);
                }
            }
        }
    },
    
    // API functions
    api: {
        // Make API request
        request: function(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.SAMPARK.config.csrf_token
                }
            };
            
            const mergedOptions = Object.assign({}, defaultOptions, options);
            
            if (mergedOptions.headers['Content-Type'] === 'application/json' && mergedOptions.body) {
                mergedOptions.body = JSON.stringify(mergedOptions.body);
            }

            return fetch(window.APP_URL + url, mergedOptions)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                });
        },
        
        // GET request
        get: function(url) {
            return this.request(url, { method: 'GET' });
        },
        
        // POST request
        post: function(url, data) {
            return this.request(url, {
                method: 'POST',
                body: data
            });
        },
        
        // PUT request
        put: function(url, data) {
            return this.request(url, {
                method: 'PUT',
                body: data
            });
        },
        
        // DELETE request
        delete: function(url) {
            return this.request(url, { method: 'DELETE' });
        }
    },
    
    // UI functions
    ui: {
        // Show loading
        showLoading: function(element = null) {
            if (element) {
                element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                element.disabled = true;
            } else {
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    overlay.classList.remove('d-none');
                }
            }
        },
        
        // Hide loading
        hideLoading: function(element = null, originalText = '') {
            if (element) {
                element.innerHTML = originalText;
                element.disabled = false;
            } else {
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    overlay.classList.add('d-none');
                }
            }
        },
        
        // Show toast notification
        showToast: function(message, type = 'info') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        },
        
        // Show confirmation dialog
        confirm: function(title, text, confirmText = 'Yes', cancelText = 'No') {
            return Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                customClass: {
                    confirmButton: 'btn btn-apple-primary',
                    cancelButton: 'btn btn-apple-glass'
                }
            });
        },
        
        // Show success message
        showSuccess: function(title, text) {
            return Swal.fire({
                icon: 'success',
                title: title,
                html: text,
                customClass: {
                    confirmButton: 'btn btn-apple-primary'
                }
            });
        },
        
        // Show error message
        showError: function(title, text) {
            return Swal.fire({
                icon: 'error',
                title: title,
                html: text,
                customClass: {
                    confirmButton: 'btn btn-apple-primary'
                }
            });
        },
        
        // Show info message
        showInfo: function(title, text) {
            return Swal.fire({
                icon: 'info',
                title: title,
                html: text,
                customClass: {
                    confirmButton: 'btn btn-apple-primary'
                }
            });
        },
        
        // Initialize tooltips
        initTooltips: function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        },
        
        // Initialize popovers
        initPopovers: function() {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        },
        
        // Auto-resize textarea
        autoResizeTextarea: function(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
    },
    
    // Form utilities
    form: {
        // Serialize form data
        serialize: function(form) {
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            return data;
        },
        
        // Validate form
        validate: function(form) {
            let isValid = true;
            const fields = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            fields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        },
        
        // Reset form validation
        resetValidation: function(form) {
            const fields = form.querySelectorAll('.is-invalid, .is-valid');
            fields.forEach(field => {
                field.classList.remove('is-invalid', 'is-valid');
            });
        },
        
        // Submit form with loading
        submitWithLoading: function(form, onSuccess, onError) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            window.SAMPARK.ui.showLoading(submitBtn);
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                return response.json();
            })
            .then(data => {
                window.SAMPARK.ui.hideLoading(submitBtn, originalText);
                if (data && data.success) {
                    if (onSuccess) onSuccess(data);
                } else {
                    if (onError) onError(data);
                }
            })
            .catch(error => {
                window.SAMPARK.ui.hideLoading(submitBtn, originalText);
                if (onError) onError(error);
            });
        }
    },
    
    // Data table utilities
    dataTable: {
        // Initialize DataTable with common settings
        init: function(selector, options = {}) {
            const defaultOptions = {
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No entries available",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            };
            
            const mergedOptions = Object.assign({}, defaultOptions, options);
            
            if ($.fn.DataTable) {
                return $(selector).DataTable(mergedOptions);
            } else {
                console.warn('DataTables library not loaded');
                return null;
            }
        }
    },
    
    // File upload utilities
    fileUpload: {
        // Validate file
        validateFile: function(file, maxSize = 2097152, allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']) {
            const errors = [];
            
            if (file.size > maxSize) {
                errors.push(`File size exceeds ${(maxSize / 1024 / 1024)}MB limit`);
            }
            
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(extension)) {
                errors.push(`File type .${extension} is not allowed`);
            }
            
            return {
                valid: errors.length === 0,
                errors: errors
            };
        },
        
        // Format file size
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        // Create file preview
        createPreview: function(file, container) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'file-preview';
                
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="${file.name}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                        <div class="file-info">
                            <small>${file.name}</small><br>
                            <small class="text-muted">${window.SAMPARK.fileUpload.formatFileSize(file.size)}</small>
                        </div>
                    `;
                } else {
                    preview.innerHTML = `
                        <div class="file-icon">
                            <i class="fas fa-file-alt fa-3x text-muted"></i>
                        </div>
                        <div class="file-info">
                            <small>${file.name}</small><br>
                            <small class="text-muted">${window.SAMPARK.fileUpload.formatFileSize(file.size)}</small>
                        </div>
                    `;
                }
                
                container.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    },

    // Session management
    session: {
        heartbeatInterval: 60000, // 1 minute
        warningThreshold: 300, // 5 minutes before expiry
        heartbeatTimer: null,
        isActive: true,
        lastActivity: Date.now(),
        warningShown: false,

        init: function() {
            if (!window.USER_LOGGED_IN && !document.body.classList.contains('logged-in')) {
                return; // Don't initialize session management for non-authenticated users
            }

            console.log('Session management initialized');
            this.setupActivityTracking();
            this.startHeartbeat();

            // Handle page visibility changes
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.pauseHeartbeat();
                } else {
                    this.resumeHeartbeat();
                    this.refreshActivity();
                }
            });
        },

        setupActivityTracking: function() {
            const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
            const activityHandler = window.SAMPARK.utils.debounce(() => {
                this.refreshActivity();
            }, 1000);

            events.forEach(event => {
                document.addEventListener(event, activityHandler, true);
            });
        },

        refreshActivity: function() {
            this.lastActivity = Date.now();
            this.isActive = true;

            if (this.warningShown) {
                this.hideWarning();
            }
        },

        startHeartbeat: function() {
            if (this.heartbeatTimer) return;

            this.heartbeatTimer = setInterval(() => {
                this.sendHeartbeat();
            }, this.heartbeatInterval);

            // Send initial heartbeat
            this.sendHeartbeat();
        },

        stopHeartbeat: function() {
            if (this.heartbeatTimer) {
                clearInterval(this.heartbeatTimer);
                this.heartbeatTimer = null;
            }
        },

        pauseHeartbeat: function() {
            this.isActive = false;
            console.log('Session heartbeat paused (tab hidden)');
        },

        resumeHeartbeat: function() {
            this.isActive = true;
            console.log('Session heartbeat resumed (tab visible)');
        },

        sendHeartbeat: function() {
            if (!this.isActive || document.hidden) {
                return;
            }

            window.SAMPARK.api.post('/api/session-heartbeat', {})
                .then(data => {
                    if (data.success) {
                        this.handleHeartbeatSuccess(data);
                    } else {
                        if (data.expired) {
                            this.handleSessionExpired();
                        } else {
                            console.warn('Session heartbeat failed:', data.error);
                        }
                    }
                })
                .catch(error => {
                    if (error.message.includes('401') || error.message.includes('403')) {
                        this.handleSessionExpired();
                    } else {
                        console.warn('Session heartbeat error:', error);
                    }
                });
        },

        handleHeartbeatSuccess: function(data) {
            if (data.remaining_time !== undefined) {
                const remainingTime = data.remaining_time;

                if (remainingTime <= this.warningThreshold && !this.warningShown) {
                    this.showExpiryWarning(remainingTime);
                }
            }
        },

        showExpiryWarning: function(remainingTime) {
            this.warningShown = true;
            const minutes = Math.ceil(remainingTime / 60);

            window.SAMPARK.ui.confirm(
                'Session Expiring Soon',
                `Your session will expire in ${minutes} minute(s). Would you like to extend it?`,
                'Extend Session',
                'Logout'
            ).then((result) => {
                if (result.isConfirmed) {
                    this.extendSession();
                } else if (result.isDismissed) {
                    // Check if user is recently active before auto-extending
                    if (this.isUserRecentlyActive()) {
                        this.extendSession();
                    } else {
                        this.logout();
                    }
                }
            });
        },

        hideWarning: function() {
            this.warningShown = false;
            if (window.Swal) {
                Swal.close();
            }
        },

        isUserRecentlyActive: function() {
            const inactiveTime = Date.now() - this.lastActivity;
            return inactiveTime < (5 * 60 * 1000); // Active within last 5 minutes
        },

        extendSession: function() {
            window.SAMPARK.api.post('/api/extend-session', {})
                .then(data => {
                    if (data.success) {
                        this.warningShown = false;
                        window.SAMPARK.ui.showToast('Session extended successfully', 'success');
                        console.log('Session extended successfully');
                    } else {
                        throw new Error(data.error || 'Failed to extend session');
                    }
                })
                .catch(error => {
                    console.error('Failed to extend session:', error);
                    window.SAMPARK.ui.showToast('Failed to extend session', 'error');
                });
        },

        handleSessionExpired: function() {
            this.stopHeartbeat();

            window.SAMPARK.ui.showInfo(
                'Session Expired',
                'Your session has expired. You will be redirected to the login page.'
            ).then(() => {
                this.redirectToLogin();
            });
        },

        logout: function() {
            this.stopHeartbeat();

            fetch(`${window.SAMPARK.config.app_url}/logout`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.SAMPARK.config.csrf_token
                },
                credentials: 'same-origin'
            }).finally(() => {
                this.redirectToLogin();
            });
        },

        redirectToLogin: function() {
            window.location.href = `${window.SAMPARK.config.app_url}/login`;
        },

        getStatus: function() {
            return {
                isActive: this.isActive,
                lastActivity: this.lastActivity,
                warningShown: this.warningShown,
                heartbeatRunning: !!this.heartbeatTimer
            };
        }
    }
};

// Initialize common functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips and popovers
    window.SAMPARK.ui.initTooltips();
    window.SAMPARK.ui.initPopovers();

    // Initialize session management
    window.SAMPARK.session.init();
    
    // Auto-resize textareas
    document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
        textarea.addEventListener('input', function() {
            window.SAMPARK.ui.autoResizeTextarea(this);
        });
    });
    
    // Add CSRF token to all AJAX requests
    if (window.jQuery) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.SAMPARK.config.csrf_token
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert.show');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            // Skip if href is just "#" (not a valid selector)
            if (href === '#') {
                return;
            }
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Handle back to top button
    const backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.SAMPARK;
}
