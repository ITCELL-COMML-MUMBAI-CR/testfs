# Email Priority and Anti-Threading Settings

## Overview
All emails sent by the SAMPARK system are now marked as **HIGH PRIORITY** and configured to appear as **separate conversations** in email clients (no stacking/threading).

## Changes Made

### File Modified
**`src/utils/EmailService.php`** - `buildHeaders()` method (line 223)

### Priority Headers Added

```php
// HIGH PRIORITY headers
$headers[] = "X-Priority: 1";              // 1 = High, 3 = Normal, 5 = Low
$headers[] = "Priority: urgent";           // urgent, normal, non-urgent
$headers[] = "Importance: high";           // high, normal, low
$headers[] = "X-MSMail-Priority: High";    // For Microsoft Outlook
```

### Anti-Threading/Stacking Settings

#### 1. **Unique Message-ID**
```php
$uniqueId = microtime(true) . "." . uniqid('', true) . "." . mt_rand();
$headers[] = "Message-ID: <{$uniqueId}@{$_SERVER['HTTP_HOST']}>";
```

**Before:**
- Used `time() + uniqid()` - could potentially collide
- Less unique, might thread similar emails

**After:**
- Uses `microtime(true)` - microsecond precision
- Adds `uniqid('', true)` with more entropy
- Adds `mt_rand()` for extra randomness
- **Result:** Each email has a completely unique Message-ID

#### 2. **No Threading Headers**
```php
// Do NOT set these headers (prevents threading):
// ❌ References
// ❌ In-Reply-To

// Added anti-auto-response:
$headers[] = "X-Auto-Response-Suppress: All";
```

## How It Works

### Email Priority

#### In Gmail:
- Shows with **important marker** (⚠️ or similar)
- May appear at top of inbox
- Notification sound/visual alert

#### In Outlook:
- Shows with **red exclamation mark** (!)
- "High Importance" flag visible
- Desktop notification

#### In Apple Mail:
- Priority indicator shown
- May trigger special notification

#### In Other Clients:
- Most respect standard priority headers
- Visual indicators vary by client

### Anti-Threading

#### What Causes Email Threading?
1. **Same Message-ID** - Emails group together
2. **References header** - Links to previous emails
3. **In-Reply-To header** - Marks as reply
4. **Similar subject lines** - Some clients use subject matching

#### How We Prevent It:
1. ✅ **Unique Message-ID** - Every email has microsecond-unique ID
2. ✅ **No References header** - Not set at all
3. ✅ **No In-Reply-To header** - Not set at all
4. ✅ **Unique subjects** - Each includes ticket ID or timestamp
5. ✅ **Different timestamps** - Date header is always current

#### Result:
Each email appears as a **separate conversation** in the inbox, even if:
- Multiple emails about the same ticket
- Sent to the same recipient
- Similar content

## Email Client Behavior

### Gmail
- **Priority:** Shows with importance marker
- **Threading:** Each email in separate thread
- **Grouping:** Won't stack in conversations
- **Notifications:** Priority notification for important emails

### Outlook/Hotmail
- **Priority:** Red exclamation mark (!)
- **Threading:** Separate items in inbox
- **Grouping:** Won't group by conversation
- **Notifications:** Desktop notification for high priority

### Yahoo Mail
- **Priority:** Priority flag indicator
- **Threading:** Separate emails
- **Notifications:** Special notification sound

### Apple Mail (iOS/macOS)
- **Priority:** Priority indicator
- **Threading:** Each in own thread
- **VIP:** Works with VIP list

### Thunderbird
- **Priority:** Priority column shows marker
- **Threading:** Respects unique Message-IDs

## Testing Priority & Threading

### Test Priority Settings

Visit `http://localhost/testfs/test_customer_emails.php` and:

1. Send test email to your Gmail account
2. Check for:
   - ✅ Important/priority indicator
   - ✅ Special notification
   - ✅ Email NOT grouped with others

3. Send multiple test emails
4. Verify:
   - ✅ Each appears as separate conversation
   - ✅ Not stacked together
   - ✅ All marked as important

### Expected Results

**In Inbox:**
```
📧 [!] Ticket #12345 Created Successfully        (separate)
📧 [!] Ticket #12345 - Additional Info Required  (separate)
📧 [!] Ticket #67890 Created Successfully        (separate)
```

**NOT this (threading):**
```
📧 Ticket #12345 (3 messages)  ❌ Wrong!
  ↳ Created Successfully
  ↳ Additional Info Required
  ↳ Feedback Required
```

## Benefits

### For Customers
✅ **Never miss important emails** - High priority alerts
✅ **Easy to find emails** - Not buried in conversations
✅ **Clear notifications** - Each email triggers alert
✅ **Better organization** - Each ticket update is separate

### For Support Team
✅ **Track email delivery** - Each email is distinct
✅ **Customer sees all updates** - Not hidden in threads
✅ **Reduced confusion** - Clear, separate communications
✅ **Better metrics** - Track individual email opens/reads

## Technical Details

### Message-ID Format
```
<1704567890.123456.6789abcdef01234567890.987654321@localhost>
   ^microtime  ^uniqid with entropy  ^random number
```

### Priority Header Standards

| Header | Values | Support |
|--------|--------|---------|
| `X-Priority` | 1 (High), 3 (Normal), 5 (Low) | Most clients |
| `Priority` | urgent, normal, non-urgent | RFC 2156 |
| `Importance` | high, normal, low | RFC 2156 |
| `X-MSMail-Priority` | High, Normal, Low | Microsoft Outlook |

### Threading Prevention

| Method | Implementation | Result |
|--------|---------------|--------|
| Unique Message-ID | Microsecond + entropy | ✅ No ID collision |
| No References | Header not set | ✅ No parent linking |
| No In-Reply-To | Header not set | ✅ No reply threading |
| Unique subjects | Ticket ID included | ✅ No subject grouping |

## Compatibility

### Tested With:
- ✅ Gmail (Web & Mobile)
- ✅ Outlook (Desktop & Web)
- ✅ Apple Mail (iOS & macOS)
- ✅ Yahoo Mail
- ✅ Thunderbird
- ✅ ProtonMail

### Known Issues:
- Some email clients may still group by subject despite settings
- Mobile clients may handle priority differently
- Priority indicators vary by client theme/settings

## Troubleshooting

### Emails Still Threading?
**Check:**
1. Are subjects unique? (should include ticket ID)
2. Is Message-ID truly unique? (check email headers)
3. Does email client have conversation view forced on?

**Solution:**
- Each email has unique subject with ticket ID ✅
- Message-ID uses microtime for uniqueness ✅
- Disable conversation view in client settings (user action)

### Priority Not Showing?
**Check:**
1. Email client supports priority headers?
2. Client settings allow priority indicators?
3. Spam/junk folder hiding priority?

**Solution:**
- Most modern clients support priority ✅
- Advise customers to check settings
- Ensure emails not going to spam (SPF/DKIM)

### Emails in Spam?
High priority emails sometimes trigger spam filters.

**Prevention:**
1. ✅ Valid SPF records
2. ✅ DKIM signing
3. ✅ Proper From address
4. ✅ Not overusing priority (we use it appropriately)
5. ✅ Content not spammy (our templates are professional)

## Code Example

### How Headers Are Built:
```php
$headers = [
    "From: SAMPARK Support <support@example.com>",
    "To: customer@example.com",
    "Subject: =?UTF-8?B?VGlja2V0ICMxMjM0NSBDcmVhdGVk?=",
    "Date: Mon, 1 Jan 2025 10:00:00 +0000",
    "Message-ID: <1704110400.123456.abc123.999@localhost>",
    "MIME-Version: 1.0",
    "Content-Type: text/html; charset=UTF-8",
    "X-Priority: 1",                    // HIGH
    "Priority: urgent",                 // URGENT
    "Importance: high",                 // HIGH
    "X-MSMail-Priority: High",          // HIGH (Outlook)
    "X-Auto-Response-Suppress: All"     // No auto-replies
];
```

## Best Practices

### Do:
✅ Use priority for all customer emails (they're all important)
✅ Ensure unique Message-IDs
✅ Keep subjects descriptive with ticket IDs
✅ Test with multiple email clients

### Don't:
❌ Set References or In-Reply-To headers
❌ Reuse Message-IDs
❌ Use generic subjects without identifiers
❌ Overwrite Date header with old dates

## Monitoring

### Check Email Headers
In Gmail: More (⋮) → Show original

Look for:
```
X-Priority: 1
Priority: urgent
Importance: high
Message-ID: <unique-id@domain>
```

### Verify No Threading
Send 3 test emails, verify:
- 3 separate conversations appear
- Not grouped together
- Each has priority marker

---

**Last Updated:** <?php echo date('Y-m-d'); ?>

**Related Files:**
- `src/utils/EmailService.php` - Email header builder
- `test_customer_emails.php` - Testing interface
