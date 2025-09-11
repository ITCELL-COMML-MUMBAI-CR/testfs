/**
 * Background Refresh Manager for SAMPARK
 * Handles silent refresh of DataTables and automation
 */

class BackgroundRefreshManager {
    constructor(options = {}) {
        this.options = {
            refreshInterval: options.refreshInterval || 30000, // 30 seconds
            heartbeatInterval: options.heartbeatInterval || 60000, // 1 minute
            enableHeartbeat: options.enableHeartbeat !== false,
            enableDataRefresh: options.enableDataRefresh !== false,
            enableStatsRefresh: options.enableStatsRefresh !== false,
            debug: options.debug || false,
            ...options
        };
        
        this.refreshTimer = null;
        this.heartbeatTimer = null;
        this.dataTables = new Map(); // Store DataTable instances
        this.lastRefreshTime = null;
        this.isRefreshing = false;
        
        this.init();
    }
    
    init() {
        if (this.options.debug) {
            console.log('BackgroundRefreshManager initialized', this.options);
        }
        
        // Start heartbeat for automation
        if (this.options.enableHeartbeat) {
            this.startHeartbeat();
        }
        
        // Start data refresh for DataTables
        if (this.options.enableDataRefresh) {
            this.startDataRefresh();
        }
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseRefresh();
            } else {
                this.resumeRefresh();
            }
        });
        
        // Handle window focus/blur
        window.addEventListener('focus', () => this.onWindowFocus());
        window.addEventListener('blur', () => this.onWindowBlur());
    }
    
    /**
     * Register a DataTable for auto-refresh
     */
    registerDataTable(tableId, dataTableInstance, options = {}) {
        this.dataTables.set(tableId, {
            instance: dataTableInstance,
            lastUpdate: null,
            refreshUrl: options.refreshUrl || '/api/tickets/refresh',
            onBeforeRefresh: options.onBeforeRefresh || null,
            onAfterRefresh: options.onAfterRefresh || null,
            silent: options.silent !== false // Default to silent refresh
        });
        
        if (this.options.debug) {
            console.log(`DataTable registered: ${tableId}`);
        }
    }
    
    /**
     * Start silent heartbeat for automation
     */
    startHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
        }
        
        this.heartbeatTimer = setInterval(() => {
            this.sendHeartbeat();
        }, this.options.heartbeatInterval);
        
        // Send initial heartbeat
        setTimeout(() => this.sendHeartbeat(), 1000);
    }
    
    /**
     * Start DataTable refresh
     */
    startDataRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        this.refreshTimer = setInterval(() => {
            this.refreshDataTables();
        }, this.options.refreshInterval);
    }
    
    /**
     * Send heartbeat to trigger automation
     */
    async sendHeartbeat() {
        try {
            const baseUrl = window.APP_URL || '';
            const response = await fetch(`${baseUrl}/api/heartbeat`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (this.options.debug && data.processed) {
                console.log('Heartbeat processed automation tasks', data);
            }
            
            // Update stats if automation ran
            if (data.processed && this.options.enableStatsRefresh) {
                this.refreshStats();
            }
            
        } catch (error) {
            if (this.options.debug) {
                console.warn('Heartbeat failed:', error);
            }
        }
    }
    
    /**
     * Refresh all registered DataTables
     */
    async refreshDataTables() {
        if (this.isRefreshing || document.hidden) {
            return;
        }
        
        this.isRefreshing = true;
        
        try {
            for (const [tableId, tableConfig] of this.dataTables.entries()) {
                await this.refreshSingleDataTable(tableId, tableConfig);
            }
        } catch (error) {
            if (this.options.debug) {
                console.error('DataTable refresh error:', error);
            }
        } finally {
            this.isRefreshing = false;
            this.lastRefreshTime = new Date();
        }
    }
    
    /**
     * Refresh a single DataTable
     */
    async refreshSingleDataTable(tableId, config) {
        try {
            const table = config.instance;
            
            if (!table || !table.ajax) {
                return;
            }
            
            // Call before refresh callback
            if (config.onBeforeRefresh) {
                config.onBeforeRefresh(tableId, table);
            }
            
            // Get current page and state
            const currentPage = table.page();
            const currentOrder = table.order();
            const currentSearch = table.search();
            
            // Reload data silently
            await new Promise((resolve, reject) => {
                table.ajax.reload((data) => {
                    // Restore state after refresh
                    if (currentPage !== table.page()) {
                        table.page(currentPage);
                    }
                    
                    if (JSON.stringify(currentOrder) !== JSON.stringify(table.order())) {
                        table.order(currentOrder);
                    }
                    
                    if (currentSearch !== table.search()) {
                        table.search(currentSearch);
                    }
                    
                    // Update visual indicators for urgent tickets
                    this.updateVisualIndicators(table, data);
                    
                    // Call after refresh callback
                    if (config.onAfterRefresh) {
                        config.onAfterRefresh(tableId, table, data);
                    }
                    
                    config.lastUpdate = new Date();
                    resolve(data);
                }, false); // false = don't reset paging
            });
            
            if (this.options.debug) {
                console.log(`DataTable refreshed: ${tableId}`);
            }
            
        } catch (error) {
            if (this.options.debug) {
                console.error(`DataTable refresh error for ${tableId}:`, error);
            }
        }
    }
    
    /**
     * Update visual indicators for urgent tickets
     */
    updateVisualIndicators(table, data) {
        if (!data || !data.data) return;
        
        // Update priority and urgency indicators
        data.data.forEach((row, index) => {
            const $row = $(table.row(index).node());
            
            // Remove existing classes
            $row.removeClass('table-warning table-danger urgent-ticket sla-violation');
            
            // Add urgency indicators
            if (row.is_urgent) {
                $row.addClass('urgent-ticket');
            }
            
            if (row.is_sla_violated) {
                $row.addClass('table-danger sla-violation');
            } else if (row.sla_status === 'warning') {
                $row.addClass('table-warning');
            }
            
            // Update priority badges
            const priorityCell = $row.find('.priority-badge');
            if (priorityCell.length && row.priority_class) {
                priorityCell.removeClass('badge-secondary badge-info badge-warning badge-danger')
                          .addClass(row.priority_class);
            }
            
            // Update status badges
            const statusCell = $row.find('.status-badge');
            if (statusCell.length && row.status_class) {
                statusCell.removeClass('badge-primary badge-info badge-warning badge-success badge-dark')
                         .addClass(row.status_class);
            }
        });
    }
    
    /**
     * Refresh system statistics
     */
    async refreshStats() {
        try {
            const baseUrl = window.APP_URL || '';
            const response = await fetch(`${baseUrl}/api/system-stats`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateStatsDisplay(data.stats);
                
                if (this.options.debug) {
                    console.log('Stats refreshed', data.stats);
                }
            }
            
        } catch (error) {
            if (this.options.debug) {
                console.error('Stats refresh error:', error);
            }
        }
    }
    
    /**
     * Update statistics display on page
     */
    updateStatsDisplay(stats) {
        // Update dashboard statistics cards
        Object.keys(stats).forEach(key => {
            const elements = document.querySelectorAll(`[data-stat="${key}"]`);
            elements.forEach(el => {
                if (el.textContent !== stats[key].toString()) {
                    el.textContent = stats[key];
                    
                    // Add visual indicator of change
                    el.classList.add('stat-updated');
                    setTimeout(() => {
                        el.classList.remove('stat-updated');
                    }, 1000);
                }
            });
        });
        
        // Update progress bars if any
        this.updateProgressBars(stats);
    }
    
    /**
     * Update progress bars based on stats
     */
    updateProgressBars(stats) {
        if (stats.total > 0) {
            const closedPercentage = Math.round((stats.closed / stats.total) * 100);
            const pendingPercentage = Math.round((stats.pending / stats.total) * 100);
            
            const progressBars = document.querySelectorAll('[data-progress-type]');
            progressBars.forEach(bar => {
                const type = bar.getAttribute('data-progress-type');
                let percentage = 0;
                
                switch (type) {
                    case 'closed':
                        percentage = closedPercentage;
                        break;
                    case 'pending':
                        percentage = pendingPercentage;
                        break;
                }
                
                bar.style.width = `${percentage}%`;
                bar.setAttribute('aria-valuenow', percentage);
            });
        }
    }
    
    /**
     * Handle window focus event
     */
    onWindowFocus() {
        // Immediate refresh when user focuses window
        if (this.lastRefreshTime) {
            const timeSinceRefresh = new Date() - this.lastRefreshTime;
            if (timeSinceRefresh > 30000) { // 30 seconds
                setTimeout(() => {
                    this.refreshDataTables();
                    this.refreshStats();
                }, 500);
            }
        }
        
        this.resumeRefresh();
    }
    
    /**
     * Handle window blur event
     */
    onWindowBlur() {
        // Continue refreshing even when window is not focused
        // but at a slower rate for mobile battery conservation
    }
    
    /**
     * Pause refresh timers
     */
    pauseRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }
    
    /**
     * Resume refresh timers
     */
    resumeRefresh() {
        if (this.options.enableDataRefresh && !this.refreshTimer) {
            this.startDataRefresh();
        }
    }
    
    /**
     * Force immediate refresh of all components
     */
    forceRefresh() {
        this.refreshDataTables();
        
        if (this.options.enableStatsRefresh) {
            this.refreshStats();
        }
        
        if (this.options.enableHeartbeat) {
            this.sendHeartbeat();
        }
    }
    
    /**
     * Destroy the refresh manager
     */
    destroy() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
        
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }
        
        this.dataTables.clear();
    }
}

// Global instance
window.backgroundRefreshManager = null;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize background refresh manager
    window.backgroundRefreshManager = new BackgroundRefreshManager({
        debug: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BackgroundRefreshManager;
}