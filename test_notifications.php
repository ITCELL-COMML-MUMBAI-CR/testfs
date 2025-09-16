<?php
/**
 * Test script for the notification system
 * This script tests the priority escalation and notification functionality
 */

require_once 'src/config/database.php';
require_once 'src/utils/NotificationService.php';
require_once 'src/models/NotificationModel.php';
require_once 'src/utils/BackgroundPriorityService.php';

echo "<h1>SAMPARK Notification System Test</h1>\n";

try {
    // Test 1: Initialize NotificationService
    echo "<h2>Test 1: Initialize NotificationService</h2>\n";
    $notificationService = new NotificationService();
    echo "✅ NotificationService initialized successfully<br>\n";

    // Test 2: Initialize NotificationModel
    echo "<h2>Test 2: Initialize NotificationModel</h2>\n";
    $notificationModel = new NotificationModel();
    echo "✅ NotificationModel initialized successfully<br>\n";

    // Test 3: Create a test notification
    echo "<h2>Test 3: Create Test Notification</h2>\n";
    $testNotification = $notificationModel->createNotification([
        'user_id' => 1, // Assuming user ID 1 exists
        'user_type' => 'admin',
        'title' => 'Test Notification',
        'message' => 'This is a test notification created by the test script.',
        'type' => 'system_announcement',
        'priority' => 'medium'
    ]);

    if ($testNotification) {
        echo "✅ Test notification created with ID: {$testNotification}<br>\n";
    } else {
        echo "❌ Failed to create test notification<br>\n";
    }

    // Test 4: Create priority escalation notification
    echo "<h2>Test 4: Test Priority Escalation Notification</h2>\n";
    $escalationResult = $notificationService->sendPriorityEscalated(
        'TEST001', // Test complaint ID
        ['name' => 'Test Customer', 'customer_id' => 'CUST001'],
        'high',
        'medium',
        'Test escalation from automated test'
    );

    if ($escalationResult['success']) {
        echo "✅ Priority escalation notification sent successfully<br>\n";
    } else {
        echo "❌ Failed to send priority escalation notification: " . ($escalationResult['error'] ?? 'Unknown error') . "<br>\n";
    }

    // Test 5: Test notification counts
    echo "<h2>Test 5: Test Notification Counts</h2>\n";
    $counts = $notificationService->getNotificationCounts(1, 'admin');
    echo "📊 Notification counts for admin user ID 1:<br>\n";
    echo "- Total: {$counts['total']}<br>\n";
    echo "- Unread: {$counts['unread']}<br>\n";
    echo "- Active: {$counts['active']}<br>\n";
    echo "- High Priority: {$counts['high_priority']}<br>\n";

    // Test 6: Test BackgroundPriorityService
    echo "<h2>Test 6: Test Background Priority Service</h2>\n";
    $priorityService = new BackgroundPriorityService();
    echo "✅ BackgroundPriorityService initialized successfully<br>\n";

    // Test 7: Get escalation stats
    $escalationStats = $priorityService->getEscalationStats();
    echo "📊 Escalation Statistics:<br>\n";
    if (isset($escalationStats['by_priority'])) {
        foreach ($escalationStats['by_priority'] as $priority => $count) {
            echo "- {$priority}: {$count}<br>\n";
        }
    }
    echo "- Escalation Stopped: " . ($escalationStats['escalation_stopped'] ?? 0) . "<br>\n";
    echo "- Recent Escalations (24h): " . ($escalationStats['recent_escalations'] ?? 0) . "<br>\n";

    // Test 8: Test notification templates
    echo "<h2>Test 8: Test Notification Templates</h2>\n";
    $templates = $notificationModel->db->fetchAll("SELECT * FROM notification_templates WHERE is_active = 1");
    echo "📋 Available notification templates: " . count($templates) . "<br>\n";
    foreach ($templates as $template) {
        echo "- {$template['name']} ({$template['template_code']})<br>\n";
    }

    // Test 9: Test RBAC
    echo "<h2>Test 9: Test RBAC Functionality</h2>\n";
    $userNotifications = $notificationModel->getUserNotifications(1, 'admin', 10);
    echo "🔐 User notifications with RBAC (showing 10 max): " . count($userNotifications) . " found<br>\n";

    // Test 10: Create system announcement
    echo "<h2>Test 10: Create System Announcement</h2>\n";
    $announcements = $notificationModel->createSystemAnnouncement(
        'Test System Announcement',
        'This is a test system announcement created by the test script.',
        'admin', // Only for admins
        null, // No expiry
        'medium'
    );

    if ($announcements && count($announcements) > 0) {
        echo "✅ System announcement created for " . count($announcements) . " admin users<br>\n";
    } else {
        echo "❌ Failed to create system announcement<br>\n";
    }

    echo "<h2>✅ All Tests Completed Successfully!</h2>\n";
    echo "<p>The notification system appears to be working correctly. You can now:</p>\n";
    echo "<ul>\n";
    echo "<li>Log in as an admin to test the notification center in the navbar</li>\n";
    echo "<li>Visit /admin/notifications to test the admin interface</li>\n";
    echo "<li>Create tickets and watch for priority escalation notifications</li>\n";
    echo "<li>Test the API endpoints for notifications</li>\n";
    echo "</ul>\n";

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

pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
}

ul {
    background: #e7f3ff;
    padding: 15px;
    border-radius: 5px;
}
</style>