# Bulk Email System Improvements

## Summary
The email template creation/editing functionality has been removed from the admin interface. Admins can now send raw HTML emails directly to customers with advanced filtering options. Pre-made templates are reserved exclusively for system-generated emails (ticket notifications, registration emails, etc.).

## Changes Made

### 1. **Removed Email Template Management UI**

#### Routes Removed:
- `/admin/email-templates/editor` - Template editor page
- `/api/email-templates` - List all templates API
- `/api/email-templates/{id}` - Get template API
- `/api/email-templates/save` - Save template API

#### Files Updated:
- `src/config/routes.php` - Removed template routes
- `src/views/layouts/app.php` - Removed "Email Templates" link from navbar, renamed "Email Management" to "Bulk Email"
- `src/views/admin/emails.php` - Removed "Manage Templates" button and template selector

#### Controllers Updated:
- `src/controllers/AdminController.php`:
  - Removed `getEmailTemplatesList()` method
  - Removed `getAllEmailTemplates()` method
  - Deprecated `emailTemplates()` method (redirects to bulk email page)
  - Removed `email_templates` data from `emails()` method

### 2. **Enhanced Bulk Email Interface**

#### New Features:

**Recipient Filtering Options:**
- ✅ **All Customers** - Send to every approved customer
- ✅ **By Division** - Filter by division (dynamically loaded from customer database)
- ✅ **By Zone** - Filter by zone (dynamically loaded from customer database)
- ✅ **By Division & Zone** - Combine both filters for precise targeting
- ✅ **Specific Customers** - Select individual customers manually

**Removed:**
- ❌ Template selection dropdown
- ❌ CC email field (simplified interface)
- ❌ Staff/Admin recipient options (customers only)

**Email Personalization Placeholders:**
```
{{name}} or $customer_name    - Customer's name
{{email}} or $customer_email  - Customer's email
{{division}}                  - Customer's division
{{zone}}                      - Customer's zone
```

### 3. **Backend Improvements**

#### Updated `AdminController.php`:

**Modified `sendBulkEmail()` method:**
- Added `filter_division` and `filter_zone` parameters
- Removed `template_id` and `cc_emails` support
- Updated validation rules for new recipient types

**Modified `getBulkEmailRecipients()` method:**
- Now accepts division and zone filters
- Returns only customer recipients (no staff/admin)
- Supports filtering combinations:
  - All customers
  - Division only
  - Zone only
  - Division + Zone

**Modified `createBulkEmailJob()` method:**
- Stores filter information in `recipient_data` JSON
- Sets `template_id` to NULL for admin bulk emails

**Modified `processBulkEmailJob()` method:**
- Removed template loading logic
- Uses raw HTML message directly
- Added personalization for all placeholders
- Added 0.1 second delay between emails to prevent server overload
- Improved error logging for failed emails

### 4. **Dynamic Division & Zone Loading**

**Division and zone filters are now loaded dynamically from the customer database:**

**AdminController.php methods:**
- `getUniqueDivisions()` - Fetches distinct divisions from approved customers
- `getUniqueZones()` - Fetches distinct zones from approved customers

**SQL Queries:**
```sql
-- Get unique divisions
SELECT DISTINCT division FROM customers
WHERE division IS NOT NULL AND division != '' AND status = 'approved'
ORDER BY division ASC;

-- Get unique zones
SELECT DISTINCT zone FROM customers
WHERE zone IS NOT NULL AND zone != '' AND status = 'approved'
ORDER BY zone ASC;
```

**Benefits:**
- ✅ Always up-to-date with actual customer data
- ✅ No hardcoded values to maintain
- ✅ Automatically shows only divisions/zones that have customers
- ✅ Warning message displayed if no divisions/zones found

### 5. **Database Schema**

The `bulk_email_jobs` table now stores:
```json
{
  "recipients": [...],
  "filter_division": "BB",
  "filter_zone": "CR"
}
```

### 6. **System Email Templates (Unchanged)**

These remain code-based in `CustomerEmailService.php`:
1. **Ticket Created** - `sendTicketCreated()`
2. **Ticket Reverted** - `sendTicketReverted()`
3. **Feedback Requested** - `sendFeedbackRequested()`
4. **Customer Registration** - `sendRegistrationReceived()`
5. **Registration Approved** - `sendRegistrationApproved()`

## How to Use Bulk Email

### Step 1: Access Bulk Email
1. Login as Admin or Superadmin
2. Navigate to **Management → Bulk Email**
3. Click **"Send Bulk Email"** button

### Step 2: Select Recipients
Choose one of the following options:

**Option A: All Customers**
- Select "All Customers" from dropdown
- No additional filters needed

**Option B: By Division**
- Select "Customers by Division"
- Choose division from dropdown (divisions are loaded from customer database)

**Option C: By Zone**
- Select "Customers by Zone"
- Choose zone from dropdown (zones are loaded from customer database)

**Option D: By Division & Zone**
- Select "Customers by Division & Zone"
- Choose both division and zone

**Option E: Specific Customers**
- Select "Specific Customers"
- Check individual customers from the list

### Step 3: Compose Email
1. **Subject**: Enter email subject (supports placeholders)
2. **Message**: Enter HTML or plain text message
3. Use placeholders for personalization:
   - `{{name}}` - Replaced with customer's name
   - `{{email}}` - Replaced with customer's email
   - `{{division}}` - Replaced with customer's division
   - `{{zone}}` - Replaced with customer's zone

### Step 4: Send
- Check "Send immediately" to send now
- Uncheck to queue for later processing
- Click **"Send Email"** button

## Example Email Template

```html
<h2>Important Notice for {{division}} Division</h2>

<p>Dear {{name}},</p>

<p>This is an important update regarding freight services in your zone ({{zone}}).</p>

<p>We are implementing new changes that will improve our service quality. Please review the following:</p>

<ul>
  <li>Updated delivery schedules</li>
  <li>Enhanced tracking features</li>
  <li>Improved customer support</li>
</ul>

<p>For any queries, please contact us at support@sampark.railway.gov.in</p>

<p>Best regards,<br>
SAMPARK Support Team</p>
```

## Benefits

### For Admins:
✅ **Simplified Interface** - No complex template system, just raw email composition
✅ **Advanced Filtering** - Target specific customer groups by division/zone
✅ **Quick Communication** - Send urgent updates to all or filtered customers
✅ **No Template Management** - Focus on content, not template creation

### For System:
✅ **Code-Based Templates** - System emails use consistent, version-controlled templates
✅ **Reduced Complexity** - Fewer database queries and template processing
✅ **Better Performance** - Direct email sending without template compilation
✅ **Easier Maintenance** - Template changes done in code, not database

### For Customers:
✅ **Personalized Emails** - Receive emails with their name and details
✅ **Relevant Content** - Targeted communications based on division/zone
✅ **Professional Appearance** - Consistent formatting and branding

## Technical Details

### Validation Rules:
```php
'recipient_type' => 'required|in:all_customers,division,zone,division_zone,selected_customers'
'filter_division' => 'optional|string'
'filter_zone' => 'optional|string'
'subject' => 'required|min:5|max:200'
'message' => 'required|min:10'
```

### Email Sending Process:
1. Admin submits bulk email form
2. Recipients filtered based on selection
3. Bulk email job created in database
4. If "send immediately":
   - Process each recipient
   - Personalize message with placeholders
   - Send email via EmailService
   - Log success/failure
   - Update job status
5. If queued:
   - Job stored for later processing
   - Background worker processes when ready

### Performance Optimizations:
- 0.1 second delay between emails (prevents server overload)
- Batch recipient retrieval from database
- JSON storage for recipient data
- Error logging for debugging

## Migration Guide

### For Existing Users:
1. **No action required** - System emails continue working
2. **Bulk emails** - Use new interface without templates
3. **Old templates** - Still in database but not editable via UI

### For Developers:
1. **System emails** - Update `CustomerEmailService.php`
2. **Bulk emails** - Use admin interface, no code needed
3. **New features** - Add to filtering logic in `getBulkEmailRecipients()`

## Files Modified

### Configuration:
- `src/config/routes.php` - Removed template routes

### Controllers:
- `src/controllers/AdminController.php` - Updated bulk email logic

### Views:
- `src/views/admin/emails.php` - Updated UI with filters
- `src/views/layouts/app.php` - Updated navigation

### Services:
- `src/utils/CustomerEmailService.php` - System email templates (unchanged)
- `src/utils/EmailService.php` - Email sending (unchanged)

## Testing

### Test Bulk Email:
1. Login as admin
2. Go to Bulk Email page
3. Select "All Customers" or specific filters
4. Enter test subject and message with placeholders
5. Send email
6. Verify recipients receive personalized emails

### Test System Emails:
1. Create a test ticket
2. Verify ticket creation email sent
3. Revert ticket
4. Verify awaiting info email sent
5. All system emails should work normally

## Known Limitations

1. **No Template Library** - Admins must compose emails from scratch each time
2. **HTML Knowledge** - Basic HTML knowledge helpful for formatting
3. **No Preview** - Cannot preview email before sending (future enhancement)
4. **No Scheduling** - Queue feature exists but no scheduled sending (future enhancement)

## Future Enhancements

Potential improvements:
- [ ] Email preview before sending
- [ ] Save draft emails
- [ ] Email templates library (code-based, not DB)
- [ ] Schedule bulk emails for specific date/time
- [ ] Email analytics (open rates, click rates)
- [ ] A/B testing for email content
- [ ] Rich text editor for email composition
- [ ] Email attachment support

---

**Last Updated:** <?php echo date('Y-m-d'); ?>

**Related Documentation:**
- `EMAIL_SYSTEM_IMPROVEMENTS.md` - Original email system changes
- `DUPLICATE_EMAIL_PREVENTION.md` - Duplicate email prevention
- `EMAIL_PRIORITY_SETTINGS.md` - Email priority and anti-threading

**Related Files:**
- `src/controllers/AdminController.php` - Bulk email logic
- `src/utils/CustomerEmailService.php` - System email templates
- `src/views/admin/emails.php` - Bulk email interface
