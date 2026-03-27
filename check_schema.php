<?php
require_once 'database_functions.php';
$conn = get_db_connection();
$res = $conn->query("DESCRIBE persona_datos_personales");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
echo "---\n";
$res = $conn->query("DESCRIBE persona_educacion");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
echo "---\n";
$res = $conn->query("DESCRIBE persona_experiencia");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
echo "---\n";
$res = $conn->query("DESCRIBE persona_referencia");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
