<?php
require_once 'db_config.php';
$conn = get_db_connection();
$sql = "UPDATE persona_educacion SET nivel_educacion = titulo WHERE nivel_educacion IS NULL OR nivel_educacion = ''";
if ($conn->query($sql)) {
    echo $conn->affected_rows . " rows updated.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
