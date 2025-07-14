<?php
require 'db.php';

$userid = $_GET['userid'] ?? '';
header('Content-Type: application/json');

if (!$userid) {
    echo json_encode(['filename' => '']);
    exit;
}

$stmt = $conn->prepare("SELECT filename FROM uploads WHERE userid = ? ORDER BY uploaded_at DESC LIMIT 1");
$stmt->bind_param("s", $userid);
$stmt->execute();
$stmt->bind_result($filename);
$stmt->fetch();

echo json_encode(['filename' => $filename ?? '']);

$stmt->close();
$conn->close();
?>
