<?php
$host = 'localhost';
$db = 'secure_share';
$user = 'root';
$pass = ''; // XAMPP default

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
