<?php
// Enable verbose errors during local debugging (comment out in production).
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $name = isset($input['name']) ? trim($input['name']) : '';
    $rating = isset($input['rating']) ? intval($input['rating']) : 0;
    $comment = isset($input['comment']) ? trim($input['comment']) : '';

    if (empty($name) || empty($rating) || empty($comment) || $rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid review data']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO reviews (name, rating, comment) VALUES (?, ?, ?)");
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('sis', $name, $rating, $comment);
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['message' => 'Review submitted']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Insert failed: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// GET: list reviews
$result = $conn->query("SELECT id, name, rating, comment, created_at FROM reviews ORDER BY created_at DESC LIMIT 50");
if ($result === false) {
    http_response_code(500);
    echo json_encode(['message' => 'Query failed: ' . $conn->error]);
    $conn->close();
    exit;
}
$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
$conn->close();
echo json_encode($reviews);
?>
