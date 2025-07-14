<?php
require 'db.php';

$sql = "
SELECT u.userid, u.name, u.address, up.filename, up.uploaded_at
FROM users u
LEFT JOIN uploads up ON u.userid = up.userid
ORDER BY u.name ASC, up.uploaded_at ASC
";

$result = $conn->query($sql);
$users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uid = $row['userid'];
        if (!isset($users[$uid])) {
            $users[$uid] = [
                'userid' => $uid,
                'name' => $row['name'],
                'address' => $row['address'],
                'images' => [],
            ];
        }

        if (!empty($row['filename'])) {
            $users[$uid]['images'][] = [
                'filename' => $row['filename'],
                'uploaded_at' => $row['uploaded_at'],
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode(array_values($users), JSON_PRETTY_PRINT);
?>
