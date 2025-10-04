<?php
/**
 * Test Customer Email Service
 * Tests all 5 required customer email scenarios
 */

// Prevent direct access in production
if ($_SERVER['HTTP_HOST'] !== 'localhost' && !defined('ALLOW_EMAIL_TEST')) {
    die('Email testing is only allowed on localhost');
}

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/utils/CustomerEmailService.php';
require_once __DIR__ . '/../src/utils/NotificationService.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Email Service Test - SAMPARK</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e3a8a;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
        }
        h2 {
            color: #059669;
            margin-top: 30px;
        }
        .test-section {
            background: #f9fafb;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .info {
            background: #dbeafe;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        pre {
            background: #1f2937;
            color: #10b981;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 10px 5px;
        }
        button:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        }
        .warning {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 4px;
            color: #92400e;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Email Service Test</h1>

        <div class="info">
            <strong>📧 Testing Centralized Customer Email Service</strong>
            <p>This page tests all 5 required customer email scenarios:</p>
            <ol>
                <li>Ticket created successfully</li>
                <li>Ticket reverted for more information</li>
                <li>Ticket solved and feedback pending</li>
                <li>Customer registration</li>
                <li>Customer registration approved by admin</li>
            </ol>
        </div>

        <div class="warning">
            <strong>⚠️ Important:</strong> Enter a valid test email address. All test emails will be sent to this address.
        </div>

        <form method="POST">
            <label for="test_email"><strong>Test Email Address:</strong></label>
            <input type="email" id="test_email" name="test_email"
                   placeholder="Enter your test email address"
                   value="<?= htmlspecialchars($_POST['test_email'] ?? '') ?>"
                   required>

            <button type="submit" name="test_all">🚀 Test All Email Scenarios</button>
            <button type="submit" name="test_ticket_created">Test: Ticket Created</button>
            <button type="submit" name="test_ticket_reverted">Test: Ticket Reverted</button>
            <button type="submit" name="test_ticket_feedback">Test: Feedback Request</button>
            <button type="submit" name="test_registration">Test: Registration</button>
            <button type="submit" name="test_approval">Test: Approval</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
            $testEmail = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);

            if (!$testEmail) {
                echo '<div class="error">❌ Invalid email address!</div>';
            } else {
                $customerEmailService = new CustomerEmailService();
                $notificationService = new NotificationService();

                $testCustomer = [
                    'customer_id' => 'TEST2025001',
                    'name' => 'Test Customer',
                    'email' => $testEmail,
                    'company_name' => 'Test Company Pvt Ltd',
                    'division' => 'Test Division'
                ];

                echo '<h2>Test Results</h2>';

                // Test 1: Ticket Created
                if (isset($_POST['test_all']) || isset($_POST['test_ticket_created'])) {
                    echo '<div class="test-section">';
                    echo '<h3>1. Ticket Created Email</h3>';
                    $result = $notificationService->sendTicketCreated('TEST202501010001', $testCustomer);
                    if ($result[0]['email_sent']) {
                        echo '<p class="success">✓ SUCCESS: Ticket created email sent!</p>';
                    } else {
                        echo '<p class="error">✗ FAILED: ' . ($result[0]['errors'][0] ?? 'Unknown error') . '</p>';
                    }
                    echo '</div>';
                }

                // Test 2: Ticket Reverted
                if (isset($_POST['test_all']) || isset($_POST['test_ticket_reverted'])) {
                    echo '<div class="test-section">';
                    echo '<h3>2. Ticket Reverted Email (More Info Needed)</h3>';
                    $result = $notificationService->sendTicketAwaitingInfo(
                        'TEST202501010001',
                        $testCustomer,
                        'Please provide additional details about the issue'
                    );
                    if ($result[0]['email_sent']) {
                        echo '<p class="success">✓ SUCCESS: Ticket reverted email sent!</p>';
                    } else {
                        echo '<p class="error">✗ FAILED: ' . ($result[0]['errors'][0] ?? 'Unknown error') . '</p>';
                    }
                    echo '</div>';
                }

                // Test 3: Feedback Request
                if (isset($_POST['test_all']) || isset($_POST['test_ticket_feedback'])) {
                    echo '<div class="test-section">';
                    echo '<h3>3. Feedback Request Email</h3>';
                    $result = $notificationService->sendTicketAwaitingFeedback(
                        'TEST202501010001',
                        $testCustomer,
                        'Your issue has been resolved'
                    );
                    if ($result[0]['email_sent']) {
                        echo '<p class="success">✓ SUCCESS: Feedback request email sent!</p>';
                    } else {
                        echo '<p class="error">✗ FAILED: ' . ($result[0]['errors'][0] ?? 'Unknown error') . '</p>';
                    }
                    echo '</div>';
                }

                // Test 4: Customer Registration
                if (isset($_POST['test_all']) || isset($_POST['test_registration'])) {
                    echo '<div class="test-section">';
                    echo '<h3>4. Customer Registration Email</h3>';
                    $result = $notificationService->sendCustomerRegistration($testCustomer);
                    if ($result[0]['email_sent']) {
                        echo '<p class="success">✓ SUCCESS: Registration email sent!</p>';
                    } else {
                        echo '<p class="error">✗ FAILED: ' . ($result[0]['errors'][0] ?? 'Unknown error') . '</p>';
                    }
                    echo '</div>';
                }

                // Test 5: Registration Approved
                if (isset($_POST['test_all']) || isset($_POST['test_approval'])) {
                    echo '<div class="test-section">';
                    echo '<h3>5. Registration Approved Email</h3>';
                    $result = $notificationService->sendSignupApproved($testCustomer);
                    if ($result[0]['email_sent']) {
                        echo '<p class="success">✓ SUCCESS: Approval email sent!</p>';
                    } else {
                        echo '<p class="error">✗ FAILED: ' . ($result[0]['errors'][0] ?? 'Unknown error') . '</p>';
                    }
                    echo '</div>';
                }

                echo '<div class="info">';
                echo '<strong>📬 Check your inbox at:</strong> ' . htmlspecialchars($testEmail);
                echo '<p>Note: Emails may take a few minutes to arrive. Check your spam folder if you don\'t see them.</p>';
                echo '</div>';
            }
        }
        ?>

        <h2>Email Template Features</h2>
        <div class="test-section">
            <ul>
                <li>✓ No emojis (replaced with proper icons or text)</li>
                <li>✓ Consistent theme across all templates</li>
                <li>✓ No user/department names in emails</li>
                <li>✓ No timelines or ETAs mentioned</li>
                <li>✓ "View Ticket" button with login redirect</li>
                <li>✓ Login ID and login link for registration approval</li>
                <li>✓ Basic ticket information included</li>
                <li>✓ Professional, clean design</li>
            </ul>
        </div>

        <h2>System Configuration</h2>
        <div class="test-section">
            <pre><?php
echo "SMTP Host: " . Config::SMTP_HOST . "\n";
echo "SMTP Port: " . Config::SMTP_PORT . "\n";
echo "SMTP Encryption: " . Config::SMTP_ENCRYPTION . "\n";
echo "From Email: " . Config::FROM_EMAIL . "\n";
echo "From Name: " . Config::FROM_NAME . "\n";
echo "App Name: " . Config::APP_NAME . "\n";
echo "App URL: " . Config::getAppUrl() . "\n";
            ?></pre>
        </div>
    </div>
</body>
</html>
