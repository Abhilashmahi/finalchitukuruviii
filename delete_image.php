<?php
require 'db.php';

$userid = $_POST['userid'] ?? '';
$filename = $_POST['filename'] ?? '';

if (!$userid || !$filename) {
    die("Invalid request");
}

$stmt = $conn->prepare("DELETE FROM uploads WHERE userid = ? AND filename = ?");
$stmt->bind_param("ss", $userid, $filename);
$stmt->execute();

$filePath = "uploads/" . $filename;
if (file_exists($filePath)) {
    unlink($filePath);
}

$conn->close();
header("Location: upload_page.html?userid=" . urlencode($userid));
?>
