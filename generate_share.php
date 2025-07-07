<?php
/* generate_share.php?file=ID
 * – no limit: creates a fresh token every click
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';

if (!isset($_SESSION['user_id'])) die('Not logged in.');
$userId = $_SESSION['user_id'];
$fileId = intval($_GET['file'] ?? 0);
if ($fileId <= 0) die('Bad ID.');

/* confirm ownership */
$chk = $conn->prepare("SELECT 1 FROM files WHERE id = ? AND user_id = ? LIMIT 1");
$chk->bind_param('ii', $fileId, $userId);
$chk->execute();
$chk->store_result();
if ($chk->num_rows !== 1) die('File not found.');
$chk->close();

/* always insert a new token */
$token = bin2hex(random_bytes(32));
$ins   = $conn->prepare("INSERT INTO shares (file_id, token) VALUES (?, ?)");
$ins->bind_param('is', $fileId, $token);
$ins->execute();
$ins->close();

/* back to dashboard so user sees the new link */
header('Location: dashboard.php');
exit;
?>
