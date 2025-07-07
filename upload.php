<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
    die("No file uploaded or upload error.");
}

$targetDir = "uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true); // Create the folder if it doesn't exist
}

$filename = basename($_FILES["file"]["name"]);
$targetFile = $targetDir . time() . "_" . $filename;

if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
    // 1. Insert the uploaded file info into 'files' table
    $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['user_id'], $filename, $targetFile);
    $stmt->execute();

    // 2. Get inserted file ID
    $fileId = $stmt->insert_id;

    // 3. Generate a unique 64-character share token
    $token = bin2hex(random_bytes(32));

    // 4. Insert token into 'shares' table
    $shareStmt = $conn->prepare("INSERT INTO shares (file_id, token) VALUES (?, ?)");
    $shareStmt->bind_param("is", $fileId, $token);
    $shareStmt->execute();

    // 5. Redirect to dashboard
    header("Location: dashboard.php");
    exit;
} else {
    echo "Upload failed. Check folder permissions and file size.";
}
?>
