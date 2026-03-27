<?php
$host="srv1220.hstgr.io";
$user="u949171480_sumapaz_admin";
$password="Somossumapaz2026*";
$database="u949171480_somos_sumapaz";
$port=3306;

$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("DESCRIBE persona_educacion");
while($row = $res->fetch_assoc()){
    print_r($row);
}
?>
