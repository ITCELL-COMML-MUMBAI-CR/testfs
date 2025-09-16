<?php
/**
 * Basic Notification Test - Works without migration
 * This script tests basic notification functionality before the database migration
 */

require_once '../src/config/database.php';

echo "<h1>Basic Notification System Test (Pre-Migration)</h1>\n";

try {
    $db = Database::getInstance();

    echo "<h2>1. Check current notifications table structure</h2>\n";
    $columns = $db->fetchAll("DESCRIBE notifications");

    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";

    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";

    echo "<h2>2. Test basic notification creation</h2>\n";

    // Create a simple test notification using only existing columns
    $sql = "INSERT INTO notifications (user_id, customer_id, title, message, type, complaint_id, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $result = $db->query($sql, [
        1, // user_id
        null, // customer_id
        'Test Notification',
        'This is a basic test notification before migration.',
        'info',
        null, // complaint_id
        0 // is_read
    ]);

    if ($result) {
        echo "✅ Basic notification created successfully<br>\n";
    } else {
        echo "❌ Failed to create basic notification<br>\n";
    }

    echo "<h2>3. Test basic notification retrieval</h2>\n";

    $notifications = $db->fetchAll("SELECT * FROM notifications WHERE user_id = 1 ORDER BY created_at DESC LIMIT 5");

    if (count($notifications) > 0) {
        echo "✅ Found " . count($notifications) . " notifications for user ID 1:<br>\n";
        foreach ($notifications as $notification) {
            echo "- ID: {$notification['id']} | Title: {$notification['title']} | Created: {$notification['created_at']}<br>\n";
        }
    } else {
        echo "❌ No notifications found for user ID 1<br>\n";
    }

    echo "<h2>4. Test notification count (basic)</h2>\n";

    $countResult = $db->fetch("SELECT
                                COUNT(*) as total,
                                COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread
                               FROM notifications
                               WHERE user_id = 1");

    if ($countResult) {
        echo "✅ Notification counts for user ID 1:<br>\n";
        echo "- Total: {$countResult['total']}<br>\n";
        echo "- Unread: {$countResult['unread']}<br>\n";
    } else {
        echo "❌ Failed to get notification counts<br>\n";
    }

    echo "<h2>5. Test marking notification as read</h2>\n";

    if (count($notifications) > 0) {
        $firstNotification = $notifications[0];
        $markReadResult = $db->query("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?", [$firstNotification['id']]);

        if ($markReadResult) {
            echo "✅ Marked notification ID {$firstNotification['id']} as read<br>\n";
        } else {
            echo "❌ Failed to mark notification as read<br>\n";
        }
    }

    echo "<h2>6. Check for migration readiness</h2>\n";

    // Check if the enhanced columns exist
    $enhancedColumns = ['priority', 'user_type', 'related_id', 'dismissed_at', 'metadata'];
    $existingColumns = array_column($columns, 'Field');
    $needsMigration = false;

    foreach ($enhancedColumns as $column) {
        $exists = in_array($column, $existingColumns);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";
        echo "- {$column}: {$status}<br>\n";
        if (!$exists) $needsMigration = true;
    }

    if ($needsMigration) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 5px;'>\n";
        echo "<h3>⚠️ Migration Required</h3>\n";
        echo "<p>To enable the full notification system with priority escalation, RBAC, and persistent notifications, please run the database migration:</p>\n";
        echo "<ol>\n";
        echo "<li>Open phpMyAdmin or MySQL command line</li>\n";
        echo "<li>Execute the SQL in: <code>database/migrations/update_notifications_simple.sql</code></li>\n";
        echo "<li>Refresh this page to see enhanced features</li>\n";
        echo "</ol>\n";
        echo "</div>\n";
    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 5px;'>\n";
        echo "<h3>✅ Migration Complete</h3>\n";
        echo "<p>All enhanced notification columns are present. You can now test the full notification system!</p>\n";
        echo "<p><a href='test_notifications.php'>Run Full Notification System Tests</a></p>\n";
        echo "</div>\n";
    }

    echo "<h2>✅ Basic Test Complete!</h2>\n";
    echo "<p>The basic notification system is working. " . ($needsMigration ? "Run the migration to unlock all features." : "All features should be available!") . "</p>\n";

} catch (Exception $e) {
    echo "<h2>❌ Test Failed</h2>\n";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Stack trace:</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

h1 {
    color: #007bff;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

h2 {
    color: #28a745;
    margin-top: 30px;
    border-left: 4px solid #28a745;
    padding-left: 10px;
}

table {
    width: 100%;
    margin: 15px 0;
}

th, td {
    padding: 8px 12px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #f8f9fa;
    font-weight: bold;
}

code {
    background-color: #f8f9fa;
    padding: 2px 5px;
    border-radius: 3px;
    font-family: monospace;
}

pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
}

a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}
</style>