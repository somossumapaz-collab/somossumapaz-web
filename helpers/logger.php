<?php
// helpers/logger.php

/**
 * Log a message to logs/resume_system.log
 * 
 * @param string $message The message to log
 * @param string $level INFO, ERROR, WARNING, DEBUG
 */
function log_resume_event($message, $level = 'INFO')
{
    $log_dir = __DIR__ . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    $log_file = $log_dir . '/resume_system.log';
    $timestamp = date('Y-m-d H:i:s');
    $formatted_message = "[$timestamp] [$level] $message" . PHP_EOL;

    file_put_contents($log_file, $formatted_message, FILE_APPEND);
}

/**
 * Helper to log database errors specifically
 */
function log_db_error($query, $error)
{
    log_resume_event("SQL Error in query: $query | Error: $error", 'ERROR');
}
