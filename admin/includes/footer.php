</main>
</div><!-- /main-panel -->
</div><!-- /app-shell -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../assets/js/Manila City Hall.js?v=<?= time() ?>"></script>
<script src="../assets/js/admin.js?v=<?= time() ?>"></script>
<script>
  // Logout
  document.getElementById('topbarAdminLogoutBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    localStorage.removeItem('admin_logged_in');
    window.location.href = '../frontend/welcome.php';
  });
</script>