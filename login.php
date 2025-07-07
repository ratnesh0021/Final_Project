<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hash);
        $stmt->fetch();
        
        if (password_verify($password, $hash)) {
            $_SESSION['user_id'] = $id;
            header("Location: dashboard.php");
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }
}
?>
