<?php
/* includes/footer.php — Pied de page HTML commun */
?>
  </main><!-- /.main-content -->
</div><!-- /.wrapper -->

<footer class="footer text-center py-3 mt-auto">
  <small class="text-muted">
    <?= APP_NAME ?> &copy; <?= date('Y') ?> — MGSI S6 2025-2026
  </small>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- scripts personnalisés -->
<script src="<?= BASE_URL ?>js/main.js"></script>
<?php if (isset($extraScripts)): foreach ($extraScripts as $s): ?>
<script src="<?= BASE_URL . h($s) ?>"></script>
<?php endforeach; endif; ?>
<?php if (isset($inlineScript)): ?>
<script><?= $inlineScript ?></script>
<?php endif; ?>
</body>
</html>
