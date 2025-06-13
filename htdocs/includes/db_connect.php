<?php
$host = "sql213.infinityfree.com";  
$user = "if0_39157374";    
$pass = "Joohyeon";     
$db = "if0_39157374_pariahtech";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
