<?php
include 'auth.php';
include __DIR__ . '/../php/db.php';

$msg = '';

// Add Admin
if (isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Admin added successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error adding admin. Username might exist.</div>';
    }
    $stmt->close();
}

// Delete Admin
if (isset($_POST['delete_admin'])) {
    $id = intval($_POST['id']);
    if ($id != $_SESSION['admin_id']) { // Prevent self-delete
        $conn->query("DELETE FROM admins WHERE id = $id");
        $msg = '<div class="alert alert-success">Admin deleted.</div>';
    } else {
        $msg = '<div class="alert alert-danger">You cannot delete yourself.</div>';
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">User Management</h2>
    <?php echo $msg; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Add New Admin</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="add_admin" class="btn btn-primary w-100">Add Admin</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Existing Admins</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $admins = $conn->query("SELECT * FROM admins");
                            while($row = $admins->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <?php if($row['id'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_admin" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin?')">Delete</button>
                                    </form>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Current</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
