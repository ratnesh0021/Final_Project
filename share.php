<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';

$token = $_GET['t'] ?? '';
if (!$token) die("Invalid or missing token.");

$stmt = $conn->prepare(
    "SELECT s.id, s.downloads, f.filename, f.filepath
     FROM shares s
     JOIN files f ON f.id = s.file_id
     WHERE s.token = ? LIMIT 1"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    die("❌ This link is invalid or expired.");
}

$stmt->bind_result($shareId, $downloads, $filename, $filepath);
$stmt->fetch();
$stmt->close();

$action = $_GET['action'] ?? '';

// File viewer
if ($action === 'view') {
    if (!file_exists($filepath)) die("File not found.");

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ['pdf'])) {
        header("Content-Type: application/pdf");
    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        header("Content-Type: image/$ext");
    } else {
        header("Content-Type: text/plain");
    }
    readfile($filepath);
    exit;
}

// One-time download
if ($action === 'download') {
    if ($downloads > 0) {
        die("❌ This file has already been downloaded.");
    }

    if (!file_exists($filepath)) die("File not found.");

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
    header("Content-Length: " . filesize($filepath));
    readfile($filepath);

    $upd = $conn->prepare("UPDATE shares SET downloads = 1 WHERE id = ?");
    $upd->bind_param("i", $shareId);
    $upd->execute();
    $upd->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Access Shared File</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: "Segoe UI", Roboto, sans-serif;
    background: linear-gradient(145deg, #eef2f3, #ffffff);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    color: #333;
  }

  .card {
    background: #fff;
    border-radius: 12px;
    padding: 40px 30px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    max-width: 460px;
    width: 90%;
    animation: fadeIn 0.3s ease-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  h2 {
    margin-bottom: 20px;
    font-size: 26px;
    color: #2c3e50;
  }

  .filename {
    font-weight: 600;
    font-size: 17px;
    color: #555;
    margin-bottom: 24px;
    word-break: break-word;
  }

  .btn {
    display: inline-block;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    margin: 10px 8px;
    cursor: pointer;
    transition: background 0.25s ease, transform 0.2s ease;
    color: #fff;
  }

  .btn-download {
    background-color: #007BFF;
  }

  .btn-download:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
  }

  .btn-download:disabled,
  .btn-download[disabled] {
    background-color: #a0a0a0;
    cursor: not-allowed;
  }

  .btn-view {
    background-color: #28a745;
  }

  .btn-view:hover {
    background-color: #218838;
    transform: translateY(-1px);
  }

  .note {
    font-size: 0.92em;
    color: #666;
    margin-top: 20px;
  }

  .expired {
    color: #dc3545;
    font-weight: 600;
    font-size: 16px;
    margin: 10px 0;
  }

  @media (max-width: 600px) {
    .card {
      padding: 30px 20px;
    }

    h2 {
      font-size: 22px;
    }

    .btn {
      font-size: 14px;
      padding: 10px 20px;
      margin: 6px 5px;
    }
  }
</style>

</head>
<body>

<div class="card">
  <h2>SecureShare File Access</h2>
  <div class="filename"><?= htmlspecialchars($filename) ?></div>

  <?php if ($downloads == 0): ?>
    <a class="btn btn-download" href="?t=<?= urlencode($token) ?>&action=download">⬇ Download</a>
  <?php else: ?>
    <div class="expired">Download already used</div>
  <?php endif; ?>

  <a class="btn btn-view" href="?t=<?= urlencode($token) ?>&action=view" target="_blank">👁 View File</a>

  <div class="note">You may view this file multiple times, but it can be downloaded only once.</div>
</div>

</body>
</html>
