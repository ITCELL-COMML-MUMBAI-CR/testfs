<?php
/**
 * Test script for session management functionality
 */

require_once 'src/config/database.php';
require_once 'src/config/Config.php';
require_once 'src/utils/Session.php';

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Session Management Test</h2>";
echo "<p>Session timeout: " . Config::SESSION_TIMEOUT . " seconds (" . (Config::SESSION_TIMEOUT / 60) . " minutes)</p>";

// Initialize session
$session = new Session();

if (!$session->isLoggedIn()) {
    echo "<p><strong>Status:</strong> Not logged in</p>";
    echo "<p>To test session management, please log in first at: <a href='/testfs/login'>Login Page</a></p>";
} else {
    echo "<p><strong>Status:</strong> Logged in</p>";
    echo "<p><strong>User:</strong> " . $session->get('user_name') . " (" . $session->get('user_email') . ")</p>";
    echo "<p><strong>User Type:</strong> " . $session->get('user_type') . "</p>";
    echo "<p><strong>Role:</strong> " . $session->get('user_role') . "</p>";

    $sessionInfo = $session->getSessionInfo();
    echo "<h3>Session Information:</h3>";
    echo "<ul>";
    echo "<li>Session ID: " . $sessionInfo['id'] . "</li>";
    echo "<li>Login Time: " . ($sessionInfo['login_time'] ? date('Y-m-d H:i:s', $sessionInfo['login_time']) : 'Not set') . "</li>";
    echo "<li>Last Activity: " . ($sessionInfo['last_activity'] ? date('Y-m-d H:i:s', $sessionInfo['last_activity']) : 'Not set') . "</li>";
    echo "<li>Remaining Time: " . $sessionInfo['remaining_time'] . " seconds (" . round($sessionInfo['remaining_time'] / 60, 1) . " minutes)</li>";
    echo "<li>Is Expired: " . ($sessionInfo['is_expired'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";

    // Test refresh activity
    echo "<h3>Test Actions:</h3>";
    echo '<p><button onclick="refreshActivity()">Refresh Activity</button></p>';
    echo '<p><button onclick="testHeartbeat()">Test Session Heartbeat</button></p>';
    echo '<p><button onclick="extendSession()">Extend Session</button></p>';
}

?>

<script>
// Add some JavaScript for testing
console.log('Session test page loaded');

function refreshActivity() {
    fetch('/testfs/test_session_refresh.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert('Activity refreshed! Remaining time: ' + data.remaining_time + ' seconds');
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error refreshing activity');
    });
}

function testHeartbeat() {
    fetch('/testfs/api/session-heartbeat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Session heartbeat successful! Remaining: ' + data.remaining_time + ' seconds');
        } else {
            alert('Session heartbeat failed: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error testing heartbeat');
    });
}

function extendSession() {
    fetch('/testfs/api/extend-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Session extended! Remaining time: ' + data.remaining_time + ' seconds');
            location.reload();
        } else {
            alert('Failed to extend session: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error extending session');
    });
}

// Auto-refresh every 30 seconds to show updated session info
setInterval(() => {
    location.reload();
}, 30000);
</script>

<p><small><em>This page auto-refreshes every 30 seconds to show updated session information.</em></small></p>