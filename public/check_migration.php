<?php
/**
 * Quick Migration Check
 * Verifies if the database migration completed successfully
 */

require_once '../src/config/database.php';

echo "<h1>Migration Status Check</h1>\n";

try {
    $db = Database::getInstance();

    echo "<h2>1. Check notifications table structure</h2>\n";
    $columns = $db->fetchAll("DESCRIBE notifications");

    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>\n";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Status</th></tr>\n";

    $requiredColumns = [
        'id', 'user_id', 'customer_id', 'user_type', 'title', 'message', 'type',
        'priority', 'related_id', 'related_type', 'complaint_id', 'is_read',
        'action_url', 'created_at', 'read_at', 'expires_at', 'metadata',
        'dismissed_at', 'updated_at'
    ];

    $existingColumns = array_column($columns, 'Field');
    $missingColumns = [];

    foreach ($requiredColumns as $required) {
        $exists = in_array($required, $existingColumns);
        if (!$exists) {
            $missingColumns[] = $required;
        }
    }

    foreach ($columns as $column) {
        $isRequired = in_array($column['Field'], $requiredColumns);
        $status = $isRequired ? "✅ REQUIRED" : "ℹ️ EXTRA";

        echo "<tr>";
        echo "<td><strong>{$column['Field']}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";

    if (!empty($missingColumns)) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 5px;'>\n";
        echo "<h3>❌ Migration Incomplete</h3>\n";
        echo "<p><strong>Missing columns:</strong> " . implode(', ', $missingColumns) . "</p>\n";
        echo "<p>The database migration did not complete successfully. Please re-run the migration script.</p>\n";
        echo "</div>\n";

        // Generate a quick fix SQL
        echo "<h3>🔧 Quick Fix SQL:</h3>\n";
        echo "<textarea style='width: 100%; height: 200px; font-family: monospace; padding: 10px;'>";

        $alterStatements = [];
        foreach ($missingColumns as $column) {
            switch ($column) {
                case 'user_type':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN user_type ENUM('customer', 'controller', 'controller_nodal', 'admin', 'superadmin') DEFAULT NULL AFTER customer_id;";
                    break;
                case 'priority':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN priority ENUM('low', 'medium', 'high', 'urgent', 'critical') DEFAULT 'medium' AFTER type;";
                    break;
                case 'related_id':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN related_id VARCHAR(50) DEFAULT NULL AFTER complaint_id;";
                    break;
                case 'related_type':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN related_type VARCHAR(50) DEFAULT NULL AFTER related_id;";
                    break;
                case 'expires_at':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN expires_at TIMESTAMP NULL DEFAULT NULL AFTER read_at;";
                    break;
                case 'metadata':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN metadata JSON DEFAULT NULL AFTER expires_at;";
                    break;
                case 'dismissed_at':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN dismissed_at TIMESTAMP NULL DEFAULT NULL AFTER metadata;";
                    break;
                case 'updated_at':
                    $alterStatements[] = "ALTER TABLE notifications ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER dismissed_at;";
                    break;
            }
        }

        echo implode("\n", $alterStatements);
        echo "</textarea>\n";
    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 5px;'>\n";
        echo "<h3>✅ All Required Columns Present</h3>\n";
        echo "<p>The notifications table has all required columns.</p>\n";
        echo "</div>\n";
    }

    echo "<h2>2. Check other tables</h2>\n";
    $requiredTables = ['notification_settings', 'notification_templates', 'notification_logs'];
    $missingTables = [];

    foreach ($requiredTables as $table) {
        $result = $db->fetchAll("SHOW TABLES LIKE '{$table}'");
        $exists = !empty($result);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";
        echo "- {$table}: {$status}<br>\n";
        if (!$exists) {
            $missingTables[] = $table;
        }
    }

    echo "<h2>3. Test simple query</h2>\n";
    try {
        $testQuery = "SELECT COUNT(*) as count FROM notifications";
        $result = $db->fetch($testQuery);
        echo "✅ Basic query works - found {$result['count']} notifications<br>\n";
    } catch (Exception $e) {
        echo "❌ Basic query failed: " . $e->getMessage() . "<br>\n";
    }

    echo "<h2>4. Test enhanced query</h2>\n";
    try {
        if (empty($missingColumns)) {
            $enhancedQuery = "SELECT COUNT(*) as total,
                                     COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority
                              FROM notifications";
            $result = $db->fetch($enhancedQuery);
            echo "✅ Enhanced query works - {$result['total']} total, {$result['high_priority']} high priority<br>\n";
        } else {
            echo "⏸️ Skipping enhanced query test due to missing columns<br>\n";
        }
    } catch (Exception $e) {
        echo "❌ Enhanced query failed: " . $e->getMessage() . "<br>\n";
    }

    if (empty($missingColumns) && empty($missingTables)) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; margin: 20px 0; border-radius: 10px;'>\n";
        echo "<h3>🎉 Migration Successful!</h3>\n";
        echo "<p>All tables and columns are present. The notification system should work correctly.</p>\n";
        echo "<p><a href='test_full_notifications.php' class='btn btn-primary'>Run Full Tests</a></p>\n";
        echo "</div>\n";
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; margin: 20px 0; border-radius: 10px;'>\n";
        echo "<h3>⚠️ Migration Issues Detected</h3>\n";
        echo "<p>Please complete the migration by:</p>\n";
        echo "<ol><li>Running the SQL statements shown above</li><li>Or re-running the complete migration script</li></ol>\n";
        echo "</div>\n";
    }

} catch (Exception $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
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
    font-size: 14px;
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

textarea {
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.btn:hover {
    background: #0056b3;
    color: white;
}
</style>