<?php
include 'auth.php';
include __DIR__ . '/../php/db.php';

// Handle Actions
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $conn->query("UPDATE items SET status = 'approved' WHERE id = $id");
    } elseif ($action == 'reject') {
        $conn->query("UPDATE items SET status = 'rejected' WHERE id = $id");
    } elseif ($action == 'delete') {
        $conn->query("DELETE FROM items WHERE id = $id");
    } elseif ($action == 'resolve') {
        $conn->query("UPDATE items SET resolved = 1 WHERE id = $id");
    }
    header("Location: posts.php");
    exit;
}

include 'includes/header.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = "1";
if ($filter == 'pending') $where = "status = 'pending'";
if ($filter == 'approved') $where = "status = 'approved'";
if ($filter == 'rejected') $where = "status = 'rejected'";

$sql = "SELECT * FROM items WHERE $where ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 filter-actions flex-wrap">
        <h2>Manage Posts</h2>
        <div class="btn-group">
            <a href="?filter=all" class="btn btn-outline-secondary <?php echo $filter=='all'?'active':''; ?>">All</a>
            <a href="?filter=pending" class="btn btn-outline-warning <?php echo $filter=='pending'?'active':''; ?>">Pending</a>
            <a href="?filter=approved" class="btn btn-outline-success <?php echo $filter=='approved'?'active':''; ?>">Approved</a>
            <a href="?filter=rejected" class="btn btn-outline-danger <?php echo $filter=='rejected'?'active':''; ?>">Rejected</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title / Desc</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Resolved</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID">#<?php echo $row['id']; ?></td>
                            <td data-label="Image">
                                <?php if($row['image_url']): ?>
                                    <img src="../php/<?php echo htmlspecialchars($row['image_url']); ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <span class="text-muted">No Img</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Title / Desc" style="max-width: 300px;">
                                <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                <small class="text-muted"><?php echo substr(htmlspecialchars($row['description']), 0, 50); ?>...</small>
                            </td>
                            <td data-label="Contact">
                                <?php echo htmlspecialchars($row['contact_name']); ?><br>
                                <small><?php echo htmlspecialchars($row['contact_phone']); ?></small>
                            </td>
                            <td data-label="Status">
                                <span class="badge bg-<?php echo $row['status'] == 'approved' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td data-label="Resolved">
                                <?php if($row['resolved']): ?>
                                    <span class="badge bg-success">Resolved</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Open</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions" class="text-end text-md-start">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <?php if($row['status'] == 'pending'): ?>
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-warning" title="Reject"><i class="fas fa-times"></i></button>
                                    <?php endif; ?>
                                    <?php if(!$row['resolved']): ?>
                                        <button type="submit" name="action" value="resolve" class="btn btn-sm btn-info text-white" title="Mark Resolved"><i class="fas fa-check-double"></i></button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
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
