<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT filename, filepath, uploaded_at FROM files WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="logo"><h1>SecureShare</h1></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="upload.html">Upload</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="dashboard">
    <h2>Your Files</h2>
    <ul class="file-list">
        <?php while ($row = $result->fetch_assoc()) : ?>
            <li>
                <?php echo htmlspecialchars($row['filename']); ?>
                - <a href="<?php echo $row['filepath']; ?>" download>[Download]</a>
                <small>(<?php echo $row['uploaded_at']; ?>)</small>
            </li>
        <?php endwhile; ?>
    </ul>
</main>
</body>
</html>
