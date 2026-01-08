<?php
include __DIR__ . '/../php/db.php';

// 1. Add 'status' column to items if not exists
$check = $conn->query("SHOW COLUMNS FROM items LIKE 'status'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE items ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    echo "Added 'status' column to items table.<br>";
}

// 2. Create admins table
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'admins' created successfully.<br>";
    // Insert default admin if not exists
    $checkAdmin = $conn->query("SELECT * FROM admins WHERE username = 'admin'");
    if ($checkAdmin->num_rows == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$pass')");
        echo "Default admin user created (admin / admin123).<br>";
    }
} else {
    echo "Error creating table admins: " . $conn->error . "<br>";
}

// 3. Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'categories' created successfully.<br>";
    // Seed categories
    $cats = ['Electronics', 'Clothing', 'Books', 'Keys', 'Wallet/ID', 'Other'];
    foreach ($cats as $cat) {
        $conn->query("INSERT IGNORE INTO categories (name) VALUES ('$cat')");
    }
}

// 4. Create locations table
$sql = "CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'locations' created successfully.<br>";
    // Seed locations
    $locs = ['Library', 'Cafeteria', 'Gym', 'Main Hall', 'Science Block', 'Parking Lot'];
    foreach ($locs as $loc) {
        $conn->query("INSERT IGNORE INTO locations (name) VALUES ('$loc')");
    }
}

// 5. Create announcements table
$sql = "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'announcements' created successfully.<br>";
    // Add image_url column if it doesn't exist (for existing tables)
    $check = $conn->query("SHOW COLUMNS FROM announcements LIKE 'image_url'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE announcements ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER message");
        echo "Added 'image_url' column to announcements table.<br>";
    }
}

// 6. Create item_reports table (for flagging items)
$sql = "CREATE TABLE IF NOT EXISTS item_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    status ENUM('pending', 'reviewed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'item_reports' created successfully.<br>";
}

$conn->close();
?>
