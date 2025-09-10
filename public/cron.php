<?php
/**
 * Cron Job Runner for SAMPARK
 * This script should be called by system cron jobs to run automated tasks
 * 
 * Usage:
 * - Run all tasks: php cron.php
 * - Run specific task: php cron.php sla_monitoring
 * - Run with verbose output: php cron.php --verbose
 * 
 * Recommended cron schedule:
 * # Run every 15 minutes for SLA monitoring
 * 
 * 15 * * * * /usr/bin/php /path/to/sampark/public/cron.php sla_monitoring
 * 
 * # Run every hour for escalations
 * 0 * * * * /usr/bin/php /path/to/sampark/public/cron.php auto_escalation
 * 
 * # Run daily at 2 AM for cleanup
 * 0 2 * * * /usr/bin/php /path/to/sampark/public/cron.php cleanup
 * 
 * # Run all tasks daily at 3 AM
 * 0 3 * * * /usr/bin/php /path/to/sampark/public/cron.php
 */

// Prevent web access
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script can only be run from command line.');
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

// Set time limit for long-running tasks
set_time_limit(300); // 5 minutes

// Include required files
require_once '../src/config/database.php';
require_once '../src/config/Config.php';
require_once '../src/utils/ScheduledTaskRunner.php';

// Set custom error handlers
set_error_handler('Config::errorHandler');
set_exception_handler('Config::exceptionHandler');
register_shutdown_function('Config::shutdownHandler');

// Parse command line arguments
$taskName = $argv[1] ?? null;
$verbose = in_array('--verbose', $argv) || in_array('-v', $argv);

// Initialize
$startTime = microtime(true);
$taskRunner = new ScheduledTaskRunner();

echo "SAMPARK Scheduled Task Runner\n";
echo "============================\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    if ($taskName && $taskName !== '--verbose' && $taskName !== '-v') {
        // Run specific task
        echo "Running task: {$taskName}\n";
        $result = $taskRunner->runTask($taskName);
        
        displayTaskResult($taskName, $result, $verbose);
        
    } else {
        // Run all tasks
        echo "Running all scheduled tasks...\n\n";
        $results = $taskRunner->runAll();
        
        displayAllResults($results, $verbose);
    }
    
    $endTime = microtime(true);
    $totalDuration = round($endTime - $startTime, 2);
    
    echo "\n============================\n";
    echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
    echo "Total duration: {$totalDuration} seconds\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Log error
    Config::logError('Cron job failed: ' . $e->getMessage(), [
        'task' => $taskName,
        'argv' => $argv
    ]);
    
    exit(1);
}

/**
 * Display single task result
 */
function displayTaskResult($taskName, $result, $verbose = false) {
    $status = $result['status'] ?? 'unknown';
    $duration = $result['duration'] ?? 0;
    
    echo "Task: {$taskName}\n";
    echo "Status: " . strtoupper($status) . "\n";
    echo "Duration: {$duration}s\n";
    
    if ($status === 'failed' && isset($result['error'])) {
        echo "Error: " . $result['error'] . "\n";
    }
    
    if ($verbose && isset($result['result'])) {
        echo "Details:\n";
        displayResult($result['result'], 1);
    }
    
    echo "\n";
}

/**
 * Display all task results
 */
function displayAllResults($results, $verbose = false) {
    $status = $results['status'] ?? 'unknown';
    $totalDuration = $results['total_duration'] ?? 0;
    
    echo "Overall Status: " . strtoupper($status) . "\n";
    echo "Total Duration: {$totalDuration}s\n\n";
    
    if (isset($results['tasks'])) {
        echo "Task Results:\n";
        echo "-------------\n";
        
        foreach ($results['tasks'] as $taskName => $result) {
            displayTaskResult($taskName, $result, false);
        }
    }
    
    if ($verbose && isset($results['tasks'])) {
        echo "Detailed Results:\n";
        echo "-----------------\n";
        
        foreach ($results['tasks'] as $taskName => $result) {
            if (isset($result['result'])) {
                echo "\n{$taskName}:\n";
                displayResult($result['result'], 1);
            }
        }
    }
    
    if (!empty($results['errors'])) {
        echo "Errors:\n";
        echo "-------\n";
        foreach ($results['errors'] as $error) {
            echo "- {$error}\n";
        }
    }
}

/**
 * Display result data recursively
 */
function displayResult($data, $indent = 0) {
    $prefix = str_repeat('  ', $indent);
    
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                echo "{$prefix}{$key}:\n";
                displayResult($value, $indent + 1);
            } else {
                echo "{$prefix}{$key}: {$value}\n";
            }
        }
    } else {
        echo "{$prefix}{$data}\n";
    }
}

/**
 * Display help information
 */
function displayHelp() {
    echo "SAMPARK Cron Job Runner\n";
    echo "=======================\n\n";
    echo "Usage: php cron.php [task_name] [options]\n\n";
    echo "Available tasks:\n";
    echo "  sla_monitoring      - Monitor SLA compliance\n";
    echo "  auto_escalation     - Process auto-escalations\n";
    echo "  auto_closure        - Process auto-closures\n";
    echo "  priority_escalation - Process priority escalations\n";
    echo "  cleanup             - Run cleanup tasks\n";
    echo "  reports             - Generate reports\n";
    echo "  digest_notifications - Send digest notifications\n\n";
    echo "Options:\n";
    echo "  --verbose, -v       - Show detailed output\n";
    echo "  --help, -h          - Show this help\n\n";
    echo "Examples:\n";
    echo "  php cron.php                    # Run all tasks\n";
    echo "  php cron.php sla_monitoring     # Run SLA monitoring only\n";
    echo "  php cron.php --verbose          # Run all tasks with detailed output\n\n";
}

// Show help if requested
if (in_array('--help', $argv) || in_array('-h', $argv)) {
    displayHelp();
    exit(0);
}
