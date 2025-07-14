<?php
require 'db.php';

$userid = $_POST['userid'] ?? '';

if (!$userid || !isset($_FILES['image'])) {
    die("Invalid request");
}

$filename = uniqid() . "_" . basename($_FILES["image"]["name"]);
$target = "uploads/" . $filename;

move_uploaded_file($_FILES["image"]["tmp_name"], $target);

$stmt = $conn->prepare("INSERT INTO uploads (userid, filename, uploaded_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $userid, $filename);
$stmt->execute();

$conn->close();

header("Location: upload_page.html?userid=" . urlencode($userid));
?>
