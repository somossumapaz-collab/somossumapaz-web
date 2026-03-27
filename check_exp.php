<?php
require_once 'database_functions.php';
$conn = get_db_connection();
$sql = "SELECT id, persona_id, fecha_inicio, fecha_fin, TIMESTAMPDIFF(DAY, fecha_inicio, fecha_fin) as diff 
        FROM persona_experiencia 
        WHERE fecha_inicio IS NOT NULL AND fecha_fin IS NOT NULL
        ORDER BY diff ASC LIMIT 20";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | P_ID: " . $row['persona_id'] . " | Start: " . $row['fecha_inicio'] . " | End: " . $row['fecha_fin'] . " | Diff: " . $row['diff'] . "\n";
}
