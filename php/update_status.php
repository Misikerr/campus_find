<?php
// Enable verbose errors during local debugging (comment out in production).
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once 'db.php';

$input = json_decode(file_get_contents("php://input"), true);
$id = isset($input['id']) ? intval($input['id']) : 0;
$field = isset($input['field']) ? $input['field'] : '';
$pin = isset($input['manage_pin']) ? $input['manage_pin'] : '';

if ($id <= 0 || !in_array($field, ['resolved', 'owner_met'], true) || empty($pin)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid request']);
    exit;
}

// Verify pin
$stmt = $conn->prepare("SELECT manage_pin FROM items WHERE id = ?");
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row || $row['manage_pin'] !== $pin) {
    http_response_code(403);
    echo json_encode(['message' => 'Wrong PIN for this item']);
    $conn->close();
    exit;
}

$sql = "UPDATE items SET {$field} = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['message' => 'Updated']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Update failed: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?>
