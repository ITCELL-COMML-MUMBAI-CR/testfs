<?php
/**
 * Email Test Script for SAMPARK
 * This script tests the email functionality
 */

// Include necessary files
require_once 'src/config/Config.php';
require_once 'src/utils/database.php';
require_once 'src/utils/EmailService.php';
require_once 'src/utils/NotificationService.php';

echo "<h1>SAMPARK Email System Test</h1>";

// Test 1: SMTP Connection Test
echo "<h2>Test 1: SMTP Connection Test</h2>";
$emailService = new EmailService();
$connectionTest = $emailService->testConnection();

if ($connectionTest['success']) {
    echo "<p style='color: green;'>✓ SMTP Connection: SUCCESS - " . $connectionTest['message'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ SMTP Connection: FAILED - " . $connectionTest['error'] . "</p>";
}

// Test 2: Simple Email Test
echo "<h2>Test 2: Simple Email Test</h2>";
$testEmail = "test@example.com"; // Change this to your test email
$testResult = $emailService->sendEmail(
    $testEmail,
    "SAMPARK Email Test - " . date('Y-m-d H:i:s'),
    "<h3>Test Email from SAMPARK</h3><p>This is a test email to verify the email system is working correctly.</p><p>Sent at: " . date('Y-m-d H:i:s') . "</p>",
    true
);

if ($testResult['success']) {
    echo "<p style='color: green;'>✓ Simple Email Test: SUCCESS - Email sent to {$testEmail}</p>";
} else {
    echo "<p style='color: red;'>✗ Simple Email Test: FAILED - " . $testResult['error'] . "</p>";
}

// Test 3: Template-based Notification Test
echo "<h2>Test 3: Template-based Notification Test</h2>";
try {
    $notificationService = new NotificationService();

    // Test customer data (fake)
    $testCustomer = [
        'customer_id' => 'TEST001',
        'name' => 'Test Customer',
        'email' => $testEmail, // Change this to your test email
        'mobile' => '9876543210',
        'company_name' => 'Test Company Ltd.'
    ];

    // Test ticket created notification
    echo "<h3>Test 3a: Ticket Created Notification</h3>";
    $ticketResult = $notificationService->sendTicketCreated('TEST202412160001', $testCustomer);
    if (!empty($ticketResult) && $ticketResult[0]['email_sent']) {
        echo "<p style='color: green;'>✓ Ticket Created Notification: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>✗ Ticket Created Notification: FAILED</p>";
        if (!empty($ticketResult[0]['errors'])) {
            echo "<p style='color: red;'>Errors: " . implode(', ', $ticketResult[0]['errors']) . "</p>";
        }
    }

    // Test signup approved notification
    echo "<h3>Test 3b: Signup Approved Notification</h3>";
    $signupResult = $notificationService->sendSignupApproved($testCustomer);
    if (!empty($signupResult) && $signupResult[0]['email_sent']) {
        echo "<p style='color: green;'>✓ Signup Approved Notification: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>✗ Signup Approved Notification: FAILED</p>";
        if (!empty($signupResult[0]['errors'])) {
            echo "<p style='color: red;'>Errors: " . implode(', ', $signupResult[0]['errors']) . "</p>";
        }
    }

    // Test awaiting info notification
    echo "<h3>Test 3c: Awaiting Info Notification</h3>";
    $infoResult = $notificationService->sendTicketAwaitingInfo('TEST202412160001', $testCustomer, 'Please provide additional details about the issue.');
    if (!empty($infoResult) && $infoResult[0]['email_sent']) {
        echo "<p style='color: green;'>✓ Awaiting Info Notification: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>✗ Awaiting Info Notification: FAILED</p>";
        if (!empty($infoResult[0]['errors'])) {
            echo "<p style='color: red;'>Errors: " . implode(', ', $infoResult[0]['errors']) . "</p>";
        }
    }

    // Test awaiting feedback notification
    echo "<h3>Test 3d: Awaiting Feedback Notification</h3>";
    $feedbackResult = $notificationService->sendTicketAwaitingFeedback('TEST202412160001', $testCustomer, 'We have resolved your issue. Please provide your feedback.');
    if (!empty($feedbackResult) && $feedbackResult[0]['email_sent']) {
        echo "<p style='color: green;'>✓ Awaiting Feedback Notification: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>✗ Awaiting Feedback Notification: FAILED</p>";
        if (!empty($feedbackResult[0]['errors'])) {
            echo "<p style='color: red;'>Errors: " . implode(', ', $feedbackResult[0]['errors']) . "</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Template Test Error: " . $e->getMessage() . "</p>";
}

// Test 4: Configuration Check
echo "<h2>Test 4: Configuration Check</h2>";
echo "<p><strong>SMTP Host:</strong> " . Config::SMTP_HOST . "</p>";
echo "<p><strong>SMTP Port:</strong> " . Config::SMTP_PORT . "</p>";
echo "<p><strong>SMTP Username:</strong> " . Config::SMTP_USERNAME . "</p>";
echo "<p><strong>SMTP Encryption:</strong> " . Config::SMTP_ENCRYPTION . "</p>";
echo "<p><strong>From Email:</strong> " . Config::FROM_EMAIL . "</p>";
echo "<p><strong>From Name:</strong> " . Config::FROM_NAME . "</p>";
echo "<p><strong>App URL:</strong> " . Config::getAppUrl() . "</p>";

echo "<hr>";
echo "<p><strong>Note:</strong> Change the \$testEmail variable in this script to your email address to receive test emails.</p>";
echo "<p><strong>File Location:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>