<?php
/* includes/navbar.php — barre de navigation supérieure*/
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>pages/dashboard.php">
      <i class="bi bi-bar-chart-fill fs-5"></i>
      <?= APP_NAME ?>
    </a>
    <div class="d-flex align-items-center gap-3 ms-auto">
      <span class="text-white-50 small d-none d-md-inline">
        <i class="bi bi-person-circle me-1"></i>
        <?= h($_SESSION['nom'] ?? 'Utilisateur') ?>
        <span class="badge bg-light text-primary ms-1"><?= h(ucfirst(getUserRole())) ?></span>
      </span>
      <a href="<?= BASE_URL ?>pages/logout.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
      </a>
    </div>
  </div>
</nav>
