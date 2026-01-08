<?php
// Enable verbose errors during local debugging (comment out in production).
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle CORS preflight early and exit.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once 'db.php';

$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
$isJson = stripos($contentType, 'application/json') !== false;
$input = $isJson ? json_decode(file_get_contents("php://input"), true) : $_POST;

$title = isset($input['title']) ? trim($input['title']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$category = isset($input['category']) ? trim($input['category']) : '';
$location = isset($input['location']) ? trim($input['location']) : '';
$date_lost = isset($input['date_lost']) ? trim($input['date_lost']) : '';
$contact_name = isset($input['contact_name']) ? trim($input['contact_name']) : '';
$contact_phone = isset($input['contact_phone']) ? trim($input['contact_phone']) : '';
$type = isset($input['type']) ? trim($input['type']) : '';
$manage_pin = isset($input['manage_pin']) ? trim($input['manage_pin']) : '';
$image_url = '';

// Handle optional image upload (multipart/form-data).
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
    $filename = uniqid('img_', true) . ($safeExt ? ".{$safeExt}" : '');
    $target = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $image_url = 'uploads/' . $filename;
    }
}

// Fallback to URL if sent via JSON (no file).
if (empty($image_url) && !empty($input['image_url'])) {
    $image_url = trim($input['image_url']);
}

// Basic validation for required fields.
if (
    !empty($title) &&
    !empty($description) &&
    !empty($category) &&
    !empty($location) &&
    !empty($date_lost) &&
    !empty($contact_name) &&
    !empty($contact_phone) &&
    !empty($type) &&
    !empty($manage_pin)
) {

    // Use prepared statements to avoid SQL injection.
    // Force new items to start as unresolved and not owner_met to avoid DB defaults marking them as completed.
    $stmt = $conn->prepare("INSERT INTO items (title, description, category, location, date_lost, image_url, contact_name, contact_phone, type, manage_pin, resolved, owner_met) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)");

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(array("message" => "Prepare failed: " . $conn->error));
        $conn->close();
        exit;
    }

    $stmt->bind_param("ssssssssss", $title, $description, $category, $location, $date_lost, $image_url, $contact_name, $contact_phone, $type, $manage_pin);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Item reported successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to report item. Error: " . $stmt->error));
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}

$conn->close();
?>
