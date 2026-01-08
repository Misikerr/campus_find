<?php
include 'auth.php';
include __DIR__ . '/../php/db.php';
include 'includes/header.php';

// Fetch stats
$totalPosts = $conn->query("SELECT COUNT(*) FROM items")->fetch_row()[0];
$pendingPosts = $conn->query("SELECT COUNT(*) FROM items WHERE status = 'pending'")->fetch_row()[0];
$resolvedPosts = $conn->query("SELECT COUNT(*) FROM items WHERE resolved = 1")->fetch_row()[0];
$totalReports = $conn->query("SELECT COUNT(*) FROM item_reports WHERE status = 'pending'")->fetch_row()[0];
?>

<div class="container-fluid">
    <h2 class="mb-4">Admin Dashboard</h2>
    
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card card-stat bg-primary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $totalPosts; ?></h3>
                        <small>Total Posts</small>
                    </div>
                    <i class="fas fa-list icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-warning text-dark p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $pendingPosts; ?></h3>
                        <small>Pending Approval</small>
                    </div>
                    <i class="fas fa-clock icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-success text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $resolvedPosts; ?></h3>
                        <small>Resolved Items</small>
                    </div>
                    <i class="fas fa-check-circle icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-danger text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $totalReports; ?></h3>
                        <small>Pending Reports</small>
                    </div>
                    <i class="fas fa-exclamation-triangle icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $conn->query("SELECT title, created_at, status FROM items ORDER BY created_at DESC LIMIT 5");
                            while($row = $recent->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] == 'approved' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="posts.php" class="btn btn-outline-primary">Review Pending Posts</a>
                        <a href="announcements.php" class="btn btn-outline-info">Post Announcement</a>
                        <a href="users.php" class="btn btn-outline-secondary">Add New Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
