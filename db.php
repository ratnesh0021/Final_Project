<?php
$host = 'sql111.infinityfree.com';
$db = 'if0_39411626_Secure_Share';
$user = 'if0_39411626';
$pass = 'Rat267482nesh'; // XAMPP default

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

