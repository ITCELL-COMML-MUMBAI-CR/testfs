<?php
/**
 * Test endpoint to refresh session activity
 */

require_once 'src/config/database.php';
require_once 'src/config/Config.php';
require_once 'src/utils/Session.php';

header('Content-Type: application/json');

$session = new Session();

if (!$session->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Refresh activity
$session->refreshActivity();

echo json_encode([
    'success' => true,
    'message' => 'Activity refreshed',
    'remaining_time' => $session->getTimeRemaining()
]);
?>