<?php
/**
 * Verify Migration Script
 * Checks if the notification system migration was successful
 */

require_once 'src/config/database.php';

echo "<h1>Migration Verification</h1>\n";

try {
    $db = Database::getInstance();

    // Check notifications table structure
    echo "<h2>1. Checking notifications table structure</h2>\n";
    $columns = $db->fetchAll("DESCRIBE notifications");

    $requiredColumns = ['user_type', 'priority', 'related_id', 'related_type', 'expires_at', 'metadata', 'dismissed_at', 'updated_at'];
    $existingColumns = array_column($columns, 'Field');

    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr><th>Column</th><th>Type</th><th>Status</th></tr>\n";

    foreach ($requiredColumns as $column) {
        $exists = in_array($column, $existingColumns);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";
        $columnInfo = $exists ? array_filter($columns, function($c) use ($column) { return $c['Field'] === $column; }) : null;
        $type = $columnInfo ? array_values($columnInfo)[0]['Type'] : 'N/A';

        echo "<tr><td>{$column}</td><td>{$type}</td><td>{$status}</td></tr>\n";
    }
    echo "</table>\n";

    // Check new tables
    echo "<h2>2. Checking new tables</h2>\n";
    $tables = $db->fetchAll("SHOW TABLES LIKE 'notification_%'");

    $requiredTables = ['notification_settings', 'notification_templates', 'notification_logs'];
    $existingTables = array_column($tables, 'Tables_in_sampark_db (notification_%)');

    foreach ($requiredTables as $table) {
        $exists = in_array($table, $existingTables);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";
        echo "- {$table}: {$status}<br>\n";
    }

    // Check template data
    echo "<h2>3. Checking notification templates</h2>\n";
    $templates = $db->fetchAll("SELECT template_code, name FROM notification_templates");

    if (count($templates) > 0) {
        echo "✅ Found " . count($templates) . " notification templates:<br>\n";
        foreach ($templates as $template) {
            echo "- {$template['template_code']}: {$template['name']}<br>\n";
        }
    } else {
        echo "❌ No notification templates found<br>\n";
    }

    // Check complaints table updates
    echo "<h2>4. Checking complaints table updates</h2>\n";
    $complaintsColumns = $db->fetchAll("DESCRIBE complaints");
    $complaintsColumnNames = array_column($complaintsColumns, 'Field');

    $escalationColumns = ['escalated_at', 'escalation_stopped'];
    foreach ($escalationColumns as $column) {
        $exists = in_array($column, $complaintsColumnNames);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";
        echo "- {$column}: {$status}<br>\n";
    }

    // Check indexes
    echo "<h2>5. Checking indexes</h2>\n";
    $indexes = $db->fetchAll("SHOW INDEX FROM notifications WHERE Key_name LIKE 'idx_notifications_%'");

    if (count($indexes) > 0) {
        echo "✅ Found " . count($indexes) . " notification indexes<br>\n";
        $indexNames = array_unique(array_column($indexes, 'Key_name'));
        foreach ($indexNames as $index) {
            echo "- {$index}<br>\n";
        }
    } else {
        echo "❌ No notification indexes found<br>\n";
    }

    echo "<h2>✅ Migration Verification Complete!</h2>\n";
    echo "<p>If all items show ✅ EXISTS, the migration was successful.</p>\n";
    echo "<p>Next step: <a href='test_notifications.php'>Run Notification System Tests</a></p>\n";

} catch (Exception $e) {
    echo "<h2>❌ Verification Failed</h2>\n";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
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

a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}
</style>