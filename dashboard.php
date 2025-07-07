<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT id, filename, filepath, uploaded_at FROM files WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SecureShare – Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #f5f7fa;
        }
        header {
            background: #003366;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            font-size: 24px;
        }
        nav a {
            background: #007bff;
            color: #fff;
            padding: 10px 16px;
            margin-left: 10px;
            border-radius: 6px;
            text-decoration: none;
        }
        main {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        h2 {
            color: #003366;
            margin-bottom: 20px;
        }
        ul.file-list {
            list-style: none;
            padding: 0;
        }
        ul.file-list li {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        .share-input {
            width: 60%;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<header>
    <h1>SecureShare – Dashboard</h1>
    <nav>
        <a href="upload.html">Upload New File</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>Your Files</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <ul class="file-list">
        <?php while ($row = $result->fetch_assoc()):

            $fileId = $row['id'];
            $token = '';

            $q = $conn->prepare("SELECT token FROM shares WHERE file_id = ? ORDER BY created_at DESC LIMIT 1");
            $q->bind_param('i', $fileId);
            $q->execute();
            $q->bind_result($token);
            $q->fetch();
            $q->close();

            $hasToken = !empty($token);
            $shareLink = $hasToken
                ? "https://{$_SERVER['HTTP_HOST']}/share.php?t=$token"
                : '';
        ?>
            <li>
                <strong><?= htmlspecialchars($row['filename']) ?></strong><br>
                <small>Uploaded: <?= $row['uploaded_at'] ?></small><br>

                <?php if ($hasToken): ?>
                    <input class="share-input" type="text" value="<?= $shareLink ?>" readonly>
                    <button class="btn" onclick="copyLink('<?= $shareLink ?>')">Copy</button>
                    <a class="btn" href="generate_share.php?file=<?= $fileId ?>">Regenerate</a>
                <?php else: ?>
                    <a class="btn" href="generate_share.php?file=<?= $fileId ?>">Generate Share Link</a>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No files uploaded yet.</p>
    <?php endif; ?>
</main>

<script>
function copyLink(link) {
    navigator.clipboard.writeText(link).then(() => alert("Link copied!"));
}
</script>
</body>
</html>
