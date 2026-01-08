<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Campus Find</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; min-height: 100vh; overflow-x: hidden; }
        #sidebar { 
            min-width: 250px; 
            max-width: 250px; 
            background: #343a40; 
            color: #fff; 
            transition: all 0.3s; 
            height: 100vh;
            position: fixed;
            z-index: 999;
        }
        #sidebar.active { margin-left: -250px; }
        #sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 15px 20px; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); }
        #sidebar .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
        
        #content { 
            flex: 1; 
            padding: 20px; 
            background: #f8f9fa; 
            margin-left: 250px; 
            transition: all 0.3s; 
            width: 100%;
        }
        #content.active { margin-left: 0; }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            #sidebar { margin-left: -250px; }
            #sidebar.active { margin-left: 0; }
            #content { margin-left: 0; }
            #content.active { margin-left: 0; } /* Content doesn't move on mobile */
            #sidebarCollapse span { display: none; }
        }

        .card-stat { border-radius: 10px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-stat .icon { font-size: 2rem; opacity: 0.3; }
        
        /* Overlay for mobile */
        #overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            opacity: 0;
            transition: all 0.5s ease-in-out;
        }
        #overlay.active {
            display: block;
            opacity: 1;
        }

        /* Admin table helpers */
        .admin-table td, .admin-table th { vertical-align: middle; word-break: break-word; }
        .admin-table img { width: 56px; height: 56px; object-fit: cover; border-radius: 6px; }
        .table-responsive { overflow-y: hidden; overflow-x: auto; }
        .admin-table { min-width: 760px; }

        @media (max-width: 768px) {
            .filter-actions { flex-wrap: wrap; gap: 0.5rem; }
            .filter-actions .btn { flex: 1 1 calc(50% - 0.5rem); }
            .admin-table td, .admin-table th { padding: 0.6rem; font-size: 0.9rem; }
            .admin-table img { width: 48px; height: 48px; }
        }

        @media (max-width: 576px) {
            .filter-actions .btn { flex: 1 1 100%; }
        }
    </style>
</head>
<body>

<div id="overlay"></div>

<nav id="sidebar">
    <div class="p-4 text-center border-bottom border-secondary">
        <h4>Campus Find</h4>
        <small>Admin Panel</small>
    </div>
    <ul class="list-unstyled components mt-3">
        <li><a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="posts.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>"><i class="fas fa-layer-group"></i> Manage Posts</a></li>
        <li><a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-flag"></i> Reports</a></li>
        <li><a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> User Management</a></li>
        <li><a href="categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>"><i class="fas fa-tags"></i> Categories & Locs</a></li>
        <li><a href="announcements.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Announcements</a></li>
        <li><a href="../index.html" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
        <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div id="content">
    <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-light"><i class="fas fa-bars"></i></button>
            <span class="ms-auto">Welcome, Admin</span>
        </div>
    </nav>
