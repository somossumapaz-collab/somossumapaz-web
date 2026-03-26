<?php
$host="15.235.82.117";
$user="somossum_admin";
$password="Talento_suma";
$database="somossum_talento";
$port=3306;

$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("DESCRIBE usuarios");
while($row = $res->fetch_assoc()){
    print_r($row);
}
?>
