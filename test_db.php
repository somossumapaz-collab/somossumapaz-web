<?php
// test_db.php
header('Content-Type: text/plain');
if (class_exists('mysqli')) {
    echo "mysqli IS INSTALLED\n";
    require_once 'db_config.php';
    $conn = get_db_connection();
    if ($conn) {
        echo "DATABASE CONNECTION SUCCESSFUL\n";
    } else {
        echo "DATABASE CONNECTION FAILED\n";
    }
} else {
    echo "mysqli IS NOT INSTALLED\n";
}
?>