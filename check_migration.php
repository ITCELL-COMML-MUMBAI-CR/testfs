<?php
/**
 * Quick Migration Check - Root Level
 * Verifies if the database migration completed successfully
 */

require_once 'src/config/database.php';

echo "<h1>🔧 Notification System Migration Check</h1>\n";

try {
    $db = Database::getInstance();

    echo "<h2>1. Database Connection</h2>\n";
    echo "✅ Database connection successful<br>\n";

    echo "<h2>2. Check notifications table structure</h2>\n";
    $columns = $db->fetchAll("DESCRIBE notifications");

    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>\n";
    echo "<tr><th>Column</th><th>Type</th><th>Status</th></tr>\n";

    $requiredColumns = [
        'id' => 'Base column',
        'user_id' => 'Base column',
        'customer_id' => 'Base column',
        'user_type' => '🆕 NEW - Required for RBAC',
        'title' => 'Base column',
        'message' => 'Base column',
        'type' => 'Base column',
        'priority' => '🆕 NEW - Required for escalation',
        'related_id' => '🆕 NEW - For ticket linking',
        'related_type' => '🆕 NEW - For ticket linking',
        'complaint_id' => 'Base column',
        'is_read' => 'Base column',
        'action_url' => 'Base column',
        'created_at' => 'Base column',
        'read_at' => 'Base column',
        'expires_at' => '🆕 NEW - For expiring notifications',
        'metadata' => '🆕 NEW - For additional data',
        'dismissed_at' => '🆕 NEW - For persistent notifications',
        'updated_at' => '🆕 NEW - For tracking changes'
    ];

    $existingColumns = array_column($columns, 'Field');
    $missingColumns = [];

    foreach ($requiredColumns as $column => $description) {
        $exists = in_array($column, $existingColumns);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";

        if (!$exists) {
            $missingColumns[] = $column;
        }

        echo "<tr>";
        echo "<td><strong>{$column}</strong><br><small>{$description}</small></td>";

        if ($exists) {
            $columnInfo = array_filter($columns, function($c) use ($column) {
                return $c['Field'] === $column;
            });
            $columnData = array_values($columnInfo)[0];
            echo "<td>{$columnData['Type']}</td>";
        } else {
            echo "<td><em>Missing</em></td>";
        }

        echo "<td style='text-align: center;'>{$status}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";

    $migrationComplete = empty($missingColumns);

    if (!$migrationComplete) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px 0; border-radius: 8px;'>\n";
        echo "<h3>❌ Migration Incomplete!</h3>\n";
        echo "<p><strong>Missing " . count($missingColumns) . " columns:</strong> " . implode(', ', $missingColumns) . "</p>\n";
        echo "<p><strong>Impact:</strong> The enhanced notification system cannot function without these columns.</p>\n";
        echo "</div>\n";

        echo "<h3>🛠️ Quick Fix SQL</h3>\n";
        echo "<p>Copy and run this SQL in phpMyAdmin to complete the migration:</p>\n";
        echo "<textarea style='width: 100%; height: 300px; font-family: monospace; padding: 10px; border-radius: 5px;'>";

        $alterStatements = [];
        foreach ($missingColumns as $column) {
            switch ($column) {
                case 'user_type':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `user_type` ENUM('customer', 'controller', 'controller_nodal', 'admin', 'superadmin') DEFAULT NULL AFTER `customer_id`;";
                    break;
                case 'priority':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `priority` ENUM('low', 'medium', 'high', 'urgent', 'critical') DEFAULT 'medium' AFTER `type`;";
                    break;
                case 'related_id':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `related_id` VARCHAR(50) DEFAULT NULL AFTER `complaint_id`;";
                    break;
                case 'related_type':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `related_type` VARCHAR(50) DEFAULT NULL AFTER `related_id`;";
                    break;
                case 'expires_at':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `read_at`;";
                    break;
                case 'metadata':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `metadata` JSON DEFAULT NULL AFTER `expires_at`;";
                    break;
                case 'dismissed_at':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `dismissed_at` TIMESTAMP NULL DEFAULT NULL AFTER `metadata`;";
                    break;
                case 'updated_at':
                    $alterStatements[] = "ALTER TABLE `notifications` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `dismissed_at`;";
                    break;
            }
        }

        echo implode("\n\n", $alterStatements);
        echo "\n\n-- After running the above, refresh this page to verify";
        echo "</textarea>\n";

        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 5px;'>\n";
        echo "<h4>📋 Instructions:</h4>\n";
        echo "<ol>\n";
        echo "<li>Select and copy all the SQL from the box above</li>\n";
        echo "<li>Open phpMyAdmin</li>\n";
        echo "<li>Select your database (sampark_db)</li>\n";
        echo "<li>Go to the SQL tab</li>\n";
        echo "<li>Paste and execute the SQL</li>\n";
        echo "<li>Refresh this page to verify the fix</li>\n";
        echo "</ol>\n";
        echo "</div>\n";

    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; margin: 20px 0; border-radius: 8px;'>\n";
        echo "<h3>✅ Migration Complete!</h3>\n";
        echo "<p>All required columns are present in the notifications table.</p>\n";
        echo "</div>\n";

        echo "<h3>3. Test Enhanced Queries</h3>\n";
        try {
            $testQuery = "SELECT
                            COUNT(*) as total,
                            COUNT(CASE WHEN priority = 'high' THEN 1 END) as `high_priority`,
                            COUNT(CASE WHEN dismissed_at IS NULL THEN 1 END) as active
                          FROM notifications";
            $result = $db->fetch($testQuery);
            echo "✅ Enhanced queries work correctly<br>\n";
            echo "📊 Stats: {$result['total']} total, {$result['high_priority']} high priority, {$result['active']} active<br>\n";
        } catch (Exception $e) {
            echo "❌ Enhanced query test failed: " . $e->getMessage() . "<br>\n";
        }
    }

    echo "<h3>4. Check Additional Tables</h3>\n";
    $additionalTables = ['notification_settings', 'notification_templates', 'notification_logs'];
    foreach ($additionalTables as $table) {
        try {
            $result = $db->fetchAll("SHOW TABLES LIKE '{$table}'");
            $exists = !empty($result);
            $status = $exists ? "✅ EXISTS" : "⚠️ MISSING (Optional)";
            echo "- {$table}: {$status}<br>\n";
        } catch (Exception $e) {
            echo "- {$table}: ❌ ERROR checking<br>\n";
        }
    }

    if ($migrationComplete) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; margin: 20px 0; border-radius: 8px;'>\n";
        echo "<h3>🎉 Ready to Activate!</h3>\n";
        echo "<p>The database migration is complete. Click the button below to activate the full notification system:</p>\n";
        echo "<a href='#' onclick='activateNotificationSystem()' style='display: inline-block; background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0;'>🚀 Activate Notification System</a>\n";
        echo "</div>\n";
    }

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px 0; border-radius: 8px;'>\n";
    echo "<h3>❌ Database Connection Failed</h3>\n";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration.</p>\n";
    echo "</div>\n";
}
?>

<script>
function activateNotificationSystem() {
    if (confirm('This will reactivate the full notification system. Continue?')) {
        // Send AJAX request to reactivate
        fetch(window.location.href + '?action=activate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.text())
        .then(data => {
            alert('✅ Notification system activated! Please refresh your main site.');
            window.location.reload();
        })
        .catch(error => {
            alert('❌ Error activating system. Please manually reactivate.');
        });
    }
}
</script>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
    background: #f8f9fa;
}

h1 {
    color: #007bff;
    border-bottom: 3px solid #007bff;
    padding-bottom: 15px;
    margin-bottom: 30px;
}

h2, h3 {
    color: #495057;
    margin-top: 30px;
}

table {
    width: 100%;
    margin: 15px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

th {
    background-color: #007bff;
    color: white;
    font-weight: 600;
}

tr:hover {
    background-color: #f8f9fa;
}

textarea {
    border: 2px solid #007bff;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.4;
}

.alert {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

ol {
    background: white;
    padding: 20px 40px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>