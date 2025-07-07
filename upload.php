<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$targetDir = "uploads/";
$filename = basename($_FILES["file"]["name"]);
$targetFile = $targetDir . time() . "_" . $filename;

if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
    $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['user_id'], $filename, $targetFile);
    $stmt->execute();

    header("Location: dashboard.php");
} else {
    echo "Upload failed.";
}
?>
