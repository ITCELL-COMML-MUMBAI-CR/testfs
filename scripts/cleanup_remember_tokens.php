<?php
/**
 * Cleanup script for expired remember tokens
 * This script should be run via cron job daily to clean up expired tokens
 */

// Set up include path
require_once dirname(__DIR__) . '/src/config/database.php';
require_once dirname(__DIR__) . '/src/config/Config.php';

try {
    $db = Database::getInstance();
    
    // Delete expired remember tokens
    $deletedTokens = $db->query("DELETE FROM remember_tokens WHERE expires_at <= NOW()");
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] INFO: Cleaned up {$deletedTokens} expired remember tokens\n";
    
    // Log to app log
    file_put_contents(dirname(__DIR__) . '/logs/app.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    // Output for cron log
    echo "Cleaned up {$deletedTokens} expired remember tokens\n";
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    $errorMessage = "[{$timestamp}] ERROR: Failed to clean up remember tokens: " . $e->getMessage() . "\n";
    
    // Log error
    file_put_contents(dirname(__DIR__) . '/logs/error.log', $errorMessage, FILE_APPEND | LOCK_EX);
    
    // Output error for cron log
    echo "Error cleaning up remember tokens: " . $e->getMessage() . "\n";
    exit(1);
}
?>
