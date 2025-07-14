<?php
require 'db.php';

$userid = $_POST['userid'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id FROM users WHERE userid = ? AND password = ?");
$stmt->bind_param("ss", $userid, $password);
$stmt->execute();
$stmt->store_result();

echo $stmt->num_rows > 0 ? "success" : "fail";

$stmt->close();
$conn->close();
?>
