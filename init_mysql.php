<?php
require_once 'database_functions.php';
try {
    init_db();
    echo "Database initialized successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>