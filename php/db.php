
<?php
// Basic MySQL connection used by the API endpoints.
//
// Hosting note (InfinityFree/etc): create `php/db_config.php` with your production
// credentials (see `php/db_config.example.php`). This keeps secrets out of git and
// avoids breaking local XAMPP defaults.

$config = null;
$configPath = __DIR__ . '/db_config.php';
if (is_file($configPath)) {
    $loaded = require $configPath;
    if (is_array($loaded)) {
        $config = $loaded;
    }
}

$servername = $config['host'] ?? getenv('DB_HOST') ?: 'localhost';
$username = $config['user'] ?? getenv('DB_USER') ?: 'root';
$password = $config['pass'] ?? getenv('DB_PASS') ?: '';
$dbname = $config['name'] ?? getenv('DB_NAME') ?: 'campus_find';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // Return JSON error instead of plain text to avoid breaking frontend
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Ensure proper character set for emojis and accents.
$conn->set_charset('utf8mb4');
?>
