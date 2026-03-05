<?php
require_once 'database_functions.php';
$conn = get_db_connection();
if (!$conn)
    die("Connection failed");

echo "Usuarios count: " . $conn->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0] . "\n";
echo "Hoja Vida count: " . $conn->query("SELECT COUNT(*) FROM hoja_vida")->fetch_row()[0] . "\n";

$res = $conn->query("SELECT hv.id, u.usuario FROM hoja_vida hv JOIN usuarios u ON hv.usuario_id = u.id");
echo "Joined results:\n";
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>