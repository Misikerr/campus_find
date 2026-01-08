<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

$sql = "SELECT id, title, message, image_url, created_at FROM announcements WHERE active = 1 ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    // If query fails (e.g. table missing), return empty array to prevent crash
    echo json_encode([]);
    exit;
}

$announcements = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

echo json_encode($announcements);

$conn->close();
?>
