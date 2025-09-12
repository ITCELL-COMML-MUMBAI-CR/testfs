/**
 * Background Manager
 * Handles automatic background tasks and real-time updates
 */

class BackgroundManager {
    constructor() {
        this.isRunning = false;
        this.interval = null;
        this.lastUpdate = null;
        this.updateInterval = 30000; // 30 seconds
        this.retryCount = 0;
        this.maxRetries = 3;
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }
    
    init() {
        console.log('Background Manager initialized');
        this.start();
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else {
                this.resume();
            }
        });
        
        // Handle beforeunload
        window.addEventListener('beforeunload', () => {
            this.stop();
        });
    }
    
    start() {
        if (this.isRunning) return;
        
        console.log('Starting background tasks...');
        this.isRunning = true;
        
        // Run immediately
        this.processBackgroundTasks();
        
        // Set interval
        this.interval = setInterval(() => {
            this.processBackgroundTasks();
        }, this.updateInterval);
    }
    
    stop() {
        if (!this.isRunning) return;
        
        console.log('Stopping background tasks...');
        this.isRunning = false;
        
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }
    
    pause() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        console.log('Background tasks paused (tab hidden)');
    }
    
    resume() {
        if (this.isRunning && !this.interval) {
            this.interval = setInterval(() => {
                this.processBackgroundTasks();
            }, this.updateInterval);
            console.log('Background tasks resumed (tab visible)');
        }
    }
    
    async processBackgroundTasks() {
        try {
            // Check if we have APP_URL defined
            if (!window.APP_URL && window.location.hostname !== 'localhost') {
                console.warn('APP_URL not defined, skipping background tasks');
                return;
            }
            
            // Create timeout signal (with fallback for older browsers)
            let timeoutSignal;
            if (typeof AbortSignal !== 'undefined' && AbortSignal.timeout) {
                timeoutSignal = AbortSignal.timeout(10000); // 10 second timeout
            }
            
            const response = await fetch(`${window.APP_URL || ''}/api/background-tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                ...(timeoutSignal && { signal: timeoutSignal })
            });
            
            if (!response.ok) {
                // Handle specific HTTP status codes
                if (response.status === 404) {
                    console.warn('Background tasks API endpoint not found, disabling background processing');
                    this.stop();
                    return;
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.handleBackgroundTasksSuccess(data);
                this.retryCount = 0; // Reset retry count on success
            } else {
                throw new Error(data.error || 'Background task processing failed');
            }
            
        } catch (error) {
            // Don't show errors for timeout or network issues in development
            if (error.name === 'TimeoutError' || error.name === 'AbortError') {
                console.warn('Background task request timeout, will retry...');
            } else if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                console.warn('Network error for background tasks, will retry...');
            } else {
                this.handleBackgroundTasksError(error);
            }
        }
    }
    
    handleBackgroundTasksSuccess(data) {
        this.lastUpdate = new Date();
        
        // Log escalation results
        if (data.escalation_results && data.escalation_results.escalated_tickets > 0) {
            console.log(`Priority escalated for ${data.escalation_results.escalated_tickets} tickets`);
            
            // Show notification for escalated tickets
            this.showEscalationNotification(data.escalation_results);
        }
        
        // Update UI elements
        this.updateDashboardElements(data);
        
        // Refresh data tables if present
        this.refreshDataTables();
        
        // Update status indicator
        this.updateStatusIndicator('connected');
    }
    
    handleBackgroundTasksError(error) {
        console.error('Background tasks error:', error);
        this.retryCount++;
        
        // Update status indicator
        this.updateStatusIndicator('error');
        
        if (this.retryCount >= this.maxRetries) {
            console.warn('Max retries reached. Stopping background tasks.');
            this.stop();
            this.showErrorNotification('Background sync stopped due to repeated failures');
        }
    }
    
    showEscalationNotification(results) {
        // Only show if there are critical escalations
        const criticalEscalations = results.escalations.filter(e => e.new_priority === 'critical');
        
        if (criticalEscalations.length > 0) {
            const message = `⚠️ ${criticalEscalations.length} ticket(s) escalated to CRITICAL priority`;
            this.showToast(message, 'warning');
        }
    }
    
    showErrorNotification(message) {
        this.showToast(message, 'error');
    }
    
    showToast(message, type = 'info') {
        // Create toast if Bootstrap is available
        if (window.bootstrap) {
            const toastContainer = this.getOrCreateToastContainer();
            const toast = this.createToast(message, type);
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove after hide
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        } else {
            // Fallback to console
            console.info(`Background Manager: ${message}`);
        }
    }
    
    getOrCreateToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    }
    
    createToast(message, type) {
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'error' ? 'bg-danger' : (type === 'warning' ? 'bg-warning' : 'bg-primary');
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast ${bgClass} text-white`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="toast-header ${bgClass} text-white border-0">
                <strong class="me-auto">System Update</strong>
                <small>just now</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        
        return toast;
    }
    
    updateDashboardElements(data) {
        // Update priority counts if elements exist
        if (data.escalation_stats) {
            const stats = data.escalation_stats;
            
            // Update priority badges
            Object.keys(stats.by_priority || {}).forEach(priority => {
                const element = document.getElementById(`priority-${priority}-count`);
                if (element) {
                    element.textContent = stats.by_priority[priority];
                }
            });
            
            // Update escalation stopped count
            const stoppedElement = document.getElementById('escalation-stopped-count');
            if (stoppedElement && stats.escalation_stopped) {
                stoppedElement.textContent = stats.escalation_stopped;
            }
        }
    }
    
    refreshDataTables() {
        // Refresh DataTables if present
        if (window.jQuery && window.jQuery.fn.DataTable) {
            try {
                // Get all DataTable instances
                window.jQuery('.dataTable').each(function() {
                    if (window.jQuery.fn.DataTable.isDataTable(this)) {
                        const table = window.jQuery(this).DataTable();
                        const settings = table.settings()[0];
                        
                        // Only reload if the table uses AJAX
                        if (settings.ajax && typeof settings.ajax === 'object' && settings.ajax.url) {
                            table.ajax.reload(null, false);
                        }
                    }
                });
            } catch (error) {
                console.warn('Error refreshing DataTables:', error.message);
                // Continue execution - don't break background processing
            }
        }
        
        // Refresh custom data tables
        if (window.refreshTicketsList) {
            window.refreshTicketsList();
        }
    }
    
    updateStatusIndicator(status) {
        const indicator = document.getElementById('background-status-indicator');
        if (!indicator) return;
        
        const statusConfig = {
            'connected': { class: 'bg-success', title: 'Background sync active', text: '●' },
            'error': { class: 'bg-warning', title: 'Background sync issues', text: '●' },
            'stopped': { class: 'bg-danger', title: 'Background sync stopped', text: '●' }
        };
        
        const config = statusConfig[status] || statusConfig['stopped'];
        
        indicator.className = `badge ${config.class}`;
        indicator.title = config.title;
        indicator.textContent = config.text;
    }
    
    // Public methods
    forceRefresh() {
        console.log('Force refreshing background tasks...');
        this.processBackgroundTasks();
    }
    
    getStatus() {
        return {
            isRunning: this.isRunning,
            lastUpdate: this.lastUpdate,
            retryCount: this.retryCount,
            updateInterval: this.updateInterval
        };
    }
    
    setUpdateInterval(milliseconds) {
        this.updateInterval = Math.max(10000, milliseconds); // Minimum 10 seconds
        
        if (this.isRunning) {
            this.stop();
            this.start();
        }
    }
}

// Initialize global instance
window.backgroundManager = new BackgroundManager();

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BackgroundManager;
}