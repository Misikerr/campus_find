<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

// Be defensive if created_at is missing; fall back to id.
$orderColumn = 'created_at';
$check = $conn->query("SHOW COLUMNS FROM items LIKE 'created_at'");
if (!$check || $check->num_rows === 0) {
    $orderColumn = 'id';
}

$sql = "SELECT id, title, description, category, location, date_lost, image_url, contact_name, contact_phone, type,
    CAST(resolved AS UNSIGNED) AS resolved,
    CAST(owner_met AS UNSIGNED) AS owner_met,
    IFNULL(created_at, NOW()) AS created_at
    FROM items WHERE status = 'approved' ORDER BY " . $orderColumn . " DESC";
$result = $conn->query($sql);

if ($result === false) {
    http_response_code(500);
    echo json_encode(array("message" => "Query failed: " . $conn->error));
    $conn->close();
    exit;
}

$items = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

echo json_encode($items);

$conn->close();
?>
