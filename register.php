<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';   // ✅ Corrected path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']    ?? '');
    $email    = trim($_POST['email']   ?? '');
    $password = $_POST['password']     ?? '';

    if ($name === '' || $email === '' || $password === '') {
        die('All fields are required.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
    );
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);   
    }

    $stmt->bind_param('sss', $name, $email, $hash);

    if ($stmt->execute()) {
        header('Location: login.html');
        exit;
    } else {
        echo 'Registration failed: ' . $stmt->error;
    }
}
?>

