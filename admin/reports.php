<?php
include 'auth.php';
include __DIR__ . '/../php/db.php';

if (isset($_POST['action'])) {
    $report_id = intval($_POST['report_id']);
    $item_id = intval($_POST['item_id']);
    $action = $_POST['action'];

    if ($action == 'dismiss') {
        $conn->query("UPDATE item_reports SET status = 'reviewed' WHERE id = $report_id");
    } elseif ($action == 'delete_item') {
        $conn->query("DELETE FROM items WHERE id = $item_id");
        $conn->query("UPDATE item_reports SET status = 'reviewed' WHERE id = $report_id");
    }
    header("Location: reports.php");
    exit;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Report Handling</h2>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Item</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT r.id as report_id, r.reason, r.created_at, r.status, i.id as item_id, i.title, i.image_url 
                            FROM item_reports r 
                            JOIN items i ON r.item_id = i.id 
                            WHERE r.status = 'pending' 
                            ORDER BY r.created_at DESC";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td data-label="Report ID">#<?php echo $row['report_id']; ?></td>
                        <td data-label="Item">
                            <a href="#" target="_blank"><?php echo htmlspecialchars($row['title']); ?></a>
                            <br>
                            <small>ID: <?php echo $row['item_id']; ?></small>
                        </td>
                        <td data-label="Reason" class="text-danger"><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td data-label="Date"><?php echo $row['created_at']; ?></td>
                        <td data-label="Status"><span class="badge bg-warning">Pending</span></td>
                        <td data-label="Actions" class="text-end text-md-start">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="report_id" value="<?php echo $row['report_id']; ?>">
                                <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                                <button type="submit" name="action" value="dismiss" class="btn btn-sm btn-secondary">Dismiss</button>
                                <button type="submit" name="action" value="delete_item" class="btn btn-sm btn-danger" onclick="return confirm('Delete item and close report?')">Delete Item</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                    <tr><td colspan="6" class="text-center">No pending reports.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
