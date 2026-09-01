<?php
// staff/includes/footer.php — Shared Staff footer include
?>
</main>
</div>
</div>

<!-- Shared Modals for Staff Portal -->
<?php include __DIR__ . '/modals.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>if (window.pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
<script>
  window.ADMIN_CONFIG = {
    activeSection: '<?= $active_section ?? 'staffDashboardSection' ?>',
    dashboardCharts: <?= json_encode($dashboard_charts_payload ?? []) ?>
  };
</script>
<script src="../assets/js/Manila City Hall.js?v=<?= time() ?>"></script>
<script src="../assets/js/staff.js?v=<?= time() ?>"></script>
<script src="../assets/js/admin.js?v=<?= time() ?>"></script>
</body>

</html>