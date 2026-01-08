</div> <!-- End Content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const overlay = document.getElementById('overlay');
        const sidebarCollapse = document.getElementById('sidebarCollapse');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
            
            // Only show overlay on mobile when sidebar is active (shown)
            if (window.innerWidth <= 768) {
                overlay.classList.toggle('active');
            }
        }

        sidebarCollapse.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking overlay on mobile
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    });
</script>
</body>
</html>
