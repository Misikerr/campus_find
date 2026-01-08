<?php
include 'auth.php';
include __DIR__ . '/../php/db.php';

// Add Category
if (isset($_POST['add_cat'])) {
    $name = trim($_POST['name']);
    if ($name) $conn->query("INSERT IGNORE INTO categories (name) VALUES ('$name')");
}
// Delete Category
if (isset($_GET['del_cat'])) {
    $id = intval($_GET['del_cat']);
    $conn->query("DELETE FROM categories WHERE id = $id");
    header("Location: categories.php"); exit;
}

// Add Location
if (isset($_POST['add_loc'])) {
    $name = trim($_POST['name']);
    if ($name) $conn->query("INSERT IGNORE INTO locations (name) VALUES ('$name')");
}
// Delete Location
if (isset($_GET['del_loc'])) {
    $id = intval($_GET['del_loc']);
    $conn->query("DELETE FROM locations WHERE id = $id");
    header("Location: categories.php"); exit;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Categories & Locations</h2>

    <div class="row">
        <!-- Categories -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Manage Categories</div>
                <div class="card-body">
                    <form method="POST" class="d-flex mb-3">
                        <input type="text" name="name" class="form-control me-2" placeholder="New Category" required>
                        <button type="submit" name="add_cat" class="btn btn-success">Add</button>
                    </form>
                    <ul class="list-group">
                        <?php
                        $cats = $conn->query("SELECT * FROM categories ORDER BY name");
                        while($c = $cats->fetch_assoc()):
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($c['name']); ?>
                            <a href="?del_cat=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Locations -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">Manage Locations</div>
                <div class="card-body">
                    <form method="POST" class="d-flex mb-3">
                        <input type="text" name="name" class="form-control me-2" placeholder="New Location" required>
                        <button type="submit" name="add_loc" class="btn btn-success">Add</button>
                    </form>
                    <ul class="list-group">
                        <?php
                        $locs = $conn->query("SELECT * FROM locations ORDER BY name");
                        while($l = $locs->fetch_assoc()):
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($l['name']); ?>
                            <a href="?del_loc=<?php echo $l['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
