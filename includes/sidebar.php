<?php
/* includes/sidebar.php — menu lateral */
$currentPage = basename($_SERVER['PHP_SELF']);

function menuItem(string $icon, string $label, string $page, string $current): string {
    $active = ($current === $page) ? 'active' : '';
    return '<li class="nav-item">
      <a class="nav-link ' . $active . '" href="' . BASE_URL . 'pages/' . $page . '">
        <i class="bi bi-' . $icon . ' me-2"></i>' . $label . '
      </a>
    </li>';
}
?>
<aside class="sidebar d-flex flex-column bg-dark text-white" style="min-width:220px;min-height:calc(100vh - 56px);">
  <nav class="mt-3">
    <ul class="nav flex-column px-2">
      <?= menuItem('speedometer2', 'Tableau de bord', 'dashboard.php', $currentPage) ?>
      <?= menuItem('people-fill', 'Étudiants', 'etudiants.php', $currentPage) ?>
      <?= menuItem('person-badge-fill', 'Enseignants', 'enseignants.php', $currentPage) ?>
      <?= menuItem('journal-text', 'Modules', 'modules.php', $currentPage) ?>
      <?= menuItem('card-checklist', 'Notes', 'notes.php', $currentPage) ?>
      <?= menuItem('calendar-x', 'Absences', 'absences.php', $currentPage) ?>
      <?= menuItem('graph-up-arrow', 'Statistiques', 'statistiques.php', $currentPage) ?>
      <?= menuItem('file-earmark-arrow-down-fill', 'Export', 'export.php', $currentPage) ?>
      <?php if (getUserRole() === 'admin'): ?>
      <li class="nav-item mt-3">
        <span class="nav-link text-secondary small text-uppercase px-2">Administration</span>
      </li>
      <?= menuItem('gear-fill', 'Utilisateurs', 'utilisateurs.php', $currentPage) ?>
      <?php endif; ?>
    </ul>
  </nav>
  <div class="mt-auto px-3 py-3 border-top border-secondary">
    <small class="text-muted">v<?= APP_VERSION ?></small>
  </div>
</aside>
