# Background Refresh System Implementation Guide

## Overview

This system provides seamless, automatic refresh of ticket lists and automated processing of escalations, priority handling, and other background tasks without user disruption. The implementation leverages DataTables for smooth updates and runs all automation silently.

## Key Features

✅ **Silent Background Processing**
- Automatic priority escalations (Normal→Medium→High→Critical)
- SLA violation monitoring and alerts
- Auto-close of awaiting feedback tickets
- Background automation every 30 seconds

✅ **Seamless DataTable Refresh**
- Tables refresh every 30 seconds without user notice
- Maintains current page, sorting, and filters
- Visual indicators for urgent tickets and SLA violations
- No page reloads or interruptions

✅ **Visual Enhancements**
- Priority-based color coding
- SLA violation warnings
- Urgent ticket highlighting
- Real-time status updates

✅ **Smart Performance**
- Pauses when page is hidden
- Immediate refresh on window focus
- Efficient caching system
- Mobile-optimized refresh rates

## Files Added/Modified

### 1. Core Backend Files

**`src/utils/BackgroundRefreshService.php`**
- Main service handling all background automation
- Processes priority escalations, SLA violations, auto-close
- Provides updated ticket data for DataTables
- Manages system statistics and caching

**`src/controllers/ApiController.php`** (Enhanced)
- Added `/api/background-automation` - Runs automation tasks
- Added `/api/tickets/refresh` - Gets updated ticket data for DataTables
- Added `/api/heartbeat` - Silent automation trigger (no auth required)
- Added `/api/system-stats` - Dashboard statistics updates

**`src/controllers/CustomerController.php`** (Enhanced)
- Fixed initial ticket assignment to controller_nodal
- Enhanced with proper workflow compliance

**`src/utils/WorkflowEngine.php`** (Enhanced)
- Added priority reset logic for forwarding/reverting
- Implemented escalation stop after approval
- Enhanced SLA deadline management

### 2. Frontend Implementation

**`public/assets/js/background-refresh.js`**
- Core refresh manager class
- Handles DataTable registration and refresh
- Manages heartbeat for automation
- Provides silent background processing

**`public/assets/js/datatable-config.js`**
- Pre-configured DataTable setups for tickets
- Customer and Controller table configurations
- Filter handling and visual enhancements
- Notification system for updates

**`public/assets/css/background-refresh.css`**
- Visual enhancements for urgent tickets
- SLA violation highlighting
- Priority and status badges
- Responsive design improvements

### 3. Database Schema

**`database_migration_system_cache.sql`**
- Creates `system_cache` table for automation state
- Stores heartbeat timestamps and system statistics

**`database_migration_escalation_stop.sql`**
- Adds `escalation_stopped` column to complaints table
- Implements requirement for permanent escalation stop after approval

### 4. Configuration

**`src/config/routes.php`** (Updated)
- Added new API routes for background processing

## Implementation Steps

### Step 1: Database Setup

```sql
-- Run these migrations
SOURCE database_migration_system_cache.sql;
SOURCE database_migration_escalation_stop.sql;
```

### Step 2: Include Scripts in Your Views

```html
<!-- Add to your layout template -->
<link rel="stylesheet" href="/assets/css/background-refresh.css">
<script src="/assets/js/background-refresh.js"></script>
<script src="/assets/js/datatable-config.js"></script>
```

### Step 3: Initialize DataTables

```javascript
// For Customer Tickets
const customerTable = initializeCustomerTicketsTable('customerTicketsTable');

// For Controller Tickets
const controllerTable = initializeControllerTicketsTable('controllerTicketsTable');
```

### Step 4: Configure Background Refresh

```javascript
// Auto-initialized on page load, or manual configuration:
window.backgroundRefreshManager = new BackgroundRefreshManager({
    refreshInterval: 30000, // 30 seconds
    heartbeatInterval: 60000, // 1 minute
    enableHeartbeat: true,
    enableDataRefresh: true,
    enableStatsRefresh: true,
    debug: false
});
```

## Usage Examples

### Customer Tickets Page

```php
// See src/views/customer/tickets-with-refresh.php for complete example
?>
<table id="customerTicketsTable" class="table table-striped">
    <thead>
        <tr>
            <th>Ticket ID</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Location</th>
            <th>Created</th>
            <th>Age</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>

<script>
$(document).ready(function() {
    // This automatically enables refresh
    const table = initializeCustomerTicketsTable('customerTicketsTable');
});
</script>
```

### Controller Dashboard

```javascript
// Initialize with custom refresh settings
const controllerTable = initializeControllerTicketsTable('controllerTicketsTable');

// Register additional refresh callbacks
window.backgroundRefreshManager.registerDataTable('controllerTicketsTable', controllerTable, {
    onBeforeRefresh: function(id, table) {
        console.log('Refreshing controller tickets...');
    },
    onAfterRefresh: function(id, table, data) {
        updateDashboardStats(data);
    }
});
```

### Custom DataTable Configuration

```javascript
// Create custom DataTable with refresh
function initializeCustomTable(tableId) {
    const config = {
        ajax: {
            url: '/api/tickets/refresh',
            type: 'GET'
        },
        columns: [
            // Define your columns
        ],
        // Add your custom options
    };
    
    const table = initializeDataTable(tableId, config);
    return table;
}
```

## Configuration Options

### BackgroundRefreshManager Options

```javascript
{
    refreshInterval: 30000,     // DataTable refresh interval (ms)
    heartbeatInterval: 60000,   // Automation heartbeat interval (ms)
    enableHeartbeat: true,      // Enable background automation
    enableDataRefresh: true,    // Enable DataTable refresh
    enableStatsRefresh: true,   // Enable statistics refresh
    debug: false               // Console logging for debugging
}
```

### Visual Indicators

The system automatically applies these CSS classes:

- `.urgent-ticket` - Tickets requiring immediate attention
- `.sla-violation` - SLA deadline exceeded
- `.table-warning` - SLA warning (close to deadline)
- `.priority-badge` - Priority level indicators
- `.status-badge` - Status indicators

## Automation Features

### Priority Escalation Rules (As Per Requirements)

- **Normal → Medium**: After 3 hours
- **Medium → High**: After 12 hours  
- **High → Critical**: After 24 hours

**Reset Conditions:**
- Priority resets to Normal when ticket is reverted to customer
- Priority resets to Normal when forwarded to different division
- Escalation stops permanently when reply approved by controller_nodal

### SLA Management

- Automatic SLA deadline calculation based on priority
- Real-time violation monitoring
- Visual warnings before deadline
- Escalation notifications

### Auto-Close Process

- Awaiting feedback tickets auto-close after 3 days (configurable)
- Notification sent to customer before auto-close
- Proper transaction logging

## Monitoring and Debugging

### Debug Mode

```javascript
// Enable debug mode for development
window.backgroundRefreshManager = new BackgroundRefreshManager({
    debug: true
});
```

### API Endpoints for Testing

```bash
# Test automation manually
curl -X POST /api/background-automation

# Check system statistics
curl -X GET /api/system-stats

# Test heartbeat
curl -X GET /api/heartbeat

# Get refreshed ticket data
curl -X GET /api/tickets/refresh
```

### Console Monitoring

With debug mode enabled, you'll see:
- DataTable refresh events
- Automation task results
- Heartbeat status
- Error notifications

## Performance Considerations

### Optimization Features

- **Page Visibility API**: Pauses refresh when tab is hidden
- **Focus Events**: Immediate refresh when user returns to tab
- **Efficient Queries**: Optimized database queries for large datasets
- **Smart Caching**: Results cached to minimize database load
- **Mobile Friendly**: Slower refresh rates on mobile devices

### Browser Compatibility

- Chrome 60+
- Firefox 55+  
- Safari 12+
- Edge 79+

## Security Features

- CSRF protection on all endpoints
- Authentication required for data endpoints
- Role-based access control
- Input validation and sanitization
- SQL injection prevention

## Troubleshooting

### Common Issues

1. **Refresh not working**
   - Check browser console for errors
   - Verify API endpoints are accessible
   - Enable debug mode to see detailed logs

2. **Authentication errors**
   - Ensure user is logged in
   - Check CSRF token validity
   - Verify session is active

3. **Performance issues**
   - Increase refresh intervals
   - Check database query performance
   - Monitor network requests in browser

### Error Handling

The system includes comprehensive error handling:
- Failed API calls don't break functionality
- Graceful degradation when services are unavailable
- User-friendly error notifications
- Detailed logging for administrators

## Customization

### Custom Refresh Logic

```javascript
// Override refresh behavior
window.backgroundRefreshManager.registerDataTable('myTable', table, {
    onBeforeRefresh: function(id, table) {
        // Custom pre-refresh logic
        showCustomLoader();
    },
    onAfterRefresh: function(id, table, data) {
        // Custom post-refresh logic
        hideCustomLoader();
        updateCustomIndicators(data);
    }
});
```

### Custom Visual Indicators

```css
/* Add custom styling */
.my-custom-urgent {
    background-color: #ff6b6b !important;
    animation: customPulse 2s infinite;
}

@keyframes customPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}
```

## Support

For issues or questions:
1. Check browser console for error messages
2. Enable debug mode for detailed logging
3. Review API endpoint responses
4. Check database logs for query issues

---

**Note**: This system fully complies with SAMPARK requirements including proper workflow routing, priority escalation rules, and user role permissions as specified in System Requirement.md.