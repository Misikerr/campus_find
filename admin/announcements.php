<?php
include 'auth.php';
include __DIR__ . '/../php/db.php';

if (isset($_POST['add_announcement'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $image_url = null;

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../php/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        $filename = uniqid('ann_', true) . ($safeExt ? ".{$safeExt}" : '');
        $target = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Resize image if too large (max width 800px) AND GD library is available
            if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
                list($width, $height) = getimagesize($target);
                $max_width = 800;
                if ($width > $max_width) {
                    $ratio = $max_width / $width;
                    $new_width = $max_width;
                    $new_height = $height * $ratio;
                    
                    $src = imagecreatefromstring(file_get_contents($target));
                    if ($src !== false) {
                        $dst = imagecreatetruecolor($new_width, $new_height);
                        // Preserve transparency for PNG/WEBP
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                        
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        
                        $extLower = strtolower($ext);
                        if ($extLower == 'jpg' || $extLower == 'jpeg') imagejpeg($dst, $target, 85);
                        elseif ($extLower == 'png') imagepng($dst, $target, 8);
                        elseif ($extLower == 'gif') imagegif($dst, $target);
                        elseif ($extLower == 'webp') imagewebp($dst, $target, 85);
                        
                        imagedestroy($src);
                        imagedestroy($dst);
                    }
                }
            }
            $image_url = 'php/uploads/' . $filename;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO announcements (title, message, image_url,active) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $title, $message, $image_url);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM announcements WHERE id = $id");
    header("Location: announcements.php"); exit;
}

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE announcements SET active = NOT active WHERE id = $id");
    header("Location: announcements.php"); exit;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Announcements</h2>

    <div class="card mb-4">
        <div class="card-header">Create New Announcement</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Message</label>
                    <textarea name="message" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Image (Optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <button type="submit" name="add_announcement" class="btn btn-primary">Post Announcement</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Active Announcements</div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
                    while($row = $res->fetch_assoc()):
                    ?>
                    <tr>
                        <td data-label="Image">
                            <?php if($row['image_url']): ?>
                                <img src="../php/<?php echo htmlspecialchars($row['image_url']); ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <span class="text-muted">No Img</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Title"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td data-label="Message"><?php echo htmlspecialchars($row['message']); ?></td>
                        <td data-label="Date"><?php echo $row['created_at']; ?></td>
                        <td data-label="Status">
                            <span class="badge bg-<?php echo $row['active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $row['active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td data-label="Actions" class="text-end text-md-start">
                            <a href="?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                <?php echo $row['active'] ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
