<?php
require 'db.php';

$userid = $_GET['userid'] ?? '';
header('Content-Type: application/json');

if (!$userid) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT filename, uploaded_at FROM uploads WHERE userid = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

echo json_encode($images);
?>
