<?php
/* pages/dashboard.php — tableau de bord principal  */
require_once __DIR__ . '/../includes/fonctions.php';
$pageTitle = 'Tableau de Bord';

// filtres GET
$annees    = getAnnees();
$filieres  = getFilieres();
$anneeEnCours = getAnneeEnCours();

$anneeId   = (int)($_GET['annee']    ?? $anneeEnCours['id'] ?? 0);
$filiereId = (int)($_GET['filiere']  ?? 0) ?: null;
$semestre  = (int)($_GET['semestre'] ?? 0) ?: null;

// KPIs
$kpis = getKPIs($anneeId, $filiereId, $semestre);

// données graphiques
$distribution = getDistributionMoyennes($anneeId, $filiereId);
$moyFilieres  = getMoyennesParFiliere($anneeId);
$absParMois   = getAbsencesParMois($anneeId, $filiereId);

include __DIR__ . '/../includes/header.php';
?>

<!-- filtres -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small mb-1 fw-semibold">Année académique</label>
        <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($annees as $a): ?>
          <option value="<?= $a['id'] ?>" <?= $a['id'] == $anneeId ? 'selected' : '' ?>>
            <?= h($a['libelle']) ?> <?= $a['en_cours'] ? '(en cours)' : '' ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1 fw-semibold">Filière</label>
        <select name="filiere" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">— Toutes les filières —</option>
          <?php foreach ($filieres as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $f['id'] == $filiereId ? 'selected' : '' ?>>
            <?= h($f['code']) ?> — <?= h($f['intitule']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1 fw-semibold">Semestre</label>
        <select name="semestre" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">— Tous —</option>
          <option value="1" <?= $semestre == 1 ? 'selected' : '' ?>>Semestre 1</option>
          <option value="2" <?= $semestre == 2 ? 'selected' : '' ?>>Semestre 2</option>
        </select>
      </div>
      <div class="col-md-2">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm w-100">
          <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
        </a>
      </div>
    </form>
  </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
  <?php
  $kpiCards = [
    ['icon'=>'people-fill',    'color'=>'primary',  'label'=>'Étudiants',         'value'=> $kpis['nbEtudiants'],    'unit'=>'inscrits'],
    ['icon'=>'person-badge',   'color'=>'success',  'label'=>'Enseignants',        'value'=> $kpis['nbEnseignants'],  'unit'=>'actifs'],
    ['icon'=>'calendar-x',     'color'=>'warning',  'label'=>'Taux d\'absences',   'value'=> $kpis['tauxAbsences'] . '%', 'unit'=>'non justifiées'],
    ['icon'=>'graph-up',       'color'=>'info',     'label'=>'Moyenne générale',   'value'=> $kpis['moyenneGenerale'] . '/20', 'unit'=>'session normale'],
    ['icon'=>'patch-check',    'color'=>'success',  'label'=>'Taux d\'admission',  'value'=> $kpis['tauxAdmission'] . '%', 'unit'=> $kpis['nbAdmis'] . ' admis'],
    ['icon'=>'arrow-repeat',   'color'=>'danger',   'label'=>'En rattrapage',      'value'=> $kpis['nbRattrapage'],   'unit'=>'étudiants'],
  ];
  foreach ($kpiCards as $card): ?>
  <div class="col-xl-2 col-md-4 col-sm-6">
    <div class="card kpi-card border-0 shadow-sm h-100 border-start border-<?= $card['color'] ?> border-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-muted small"><?= $card['label'] ?></div>
            <div class="fs-3 fw-bold text-<?= $card['color'] ?>"><?= $card['value'] ?></div>
            <div class="text-muted small"><?= $card['unit'] ?></div>
          </div>
          <div class="bg-<?= $card['color'] ?> bg-opacity-10 rounded-circle p-2">
            <i class="bi bi-<?= $card['icon'] ?> fs-4 text-<?= $card['color'] ?>"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- graphiques -->
<div class="row g-3 mb-4">
  <!-- distribution des moyennes -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold border-bottom">
        <i class="bi bi-bar-chart me-2 text-primary"></i>Distribution des moyennes
      </div>
      <div class="card-body">
        <canvas id="chartDistribution" height="200"></canvas>
      </div>
    </div>
  </div>
  <!-- absences par mois -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold border-bottom">
        <i class="bi bi-calendar-week me-2 text-warning"></i>Absences par mois
      </div>
      <div class="card-body">
        <canvas id="chartAbsences" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- moyennes par filière -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold border-bottom">
        <i class="bi bi-diagram-3 me-2 text-success"></i>Moyennes par filière
      </div>
      <div class="card-body">
        <canvas id="chartFilieres" height="220"></canvas>
      </div>
    </div>
  </div>
  <!-- taux admission vs rattrapage -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold border-bottom">
        <i class="bi bi-pie-chart me-2 text-info"></i>Résultats globaux
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="chartResultats" height="220"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- tableau récapitulatif -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold border-bottom d-flex justify-content-between align-items-center">
    <span><i class="bi bi-table me-2 text-secondary"></i>Résultats des étudiants</span>
    <a href="export.php?type=resultats&annee=<?= $anneeId ?>&filiere=<?= $filiereId ?>&semestre=<?= $semestre ?>"
       class="btn btn-outline-success btn-sm">
      <i class="bi bi-file-earmark-excel me-1"></i>Exporter CSV
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>CNE</th><th>Nom</th><th>Prénom</th>
            <th>Filière</th><th>Sem.</th>
            <th class="text-center">Moy. Normale</th>
            <th class="text-center">Moy. Ratt.</th>
            <th class="text-center">Absences</th>
            <th class="text-center">Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $resultats = getResultatsEtudiants($anneeId, $filiereId, $semestre);
          foreach ($resultats as $r):
            $moy = $r['moy_ratt'] ?? $r['moy_normale'];
            $statut = $moy >= 10 ? 'Admis' : ($r['moy_ratt'] >= 10 ? 'Admis (Ratt.)' : ($r['moy_normale'] !== null ? 'Ajourné' : 'Non évalué'));
            $badgeClass = str_contains($statut,'Admis') ? 'success' : ($statut === 'Ajourné' ? 'danger' : 'secondary');
          ?>
          <tr>
            <td><code><?= h($r['cne']) ?></code></td>
            <td><?= h($r['nom']) ?></td>
            <td><?= h($r['prenom']) ?></td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= h($r['filiere']) ?></span></td>
            <td class="text-center">S<?= $r['semestre'] ?></td>
            <td class="text-center fw-semibold <?= $r['moy_normale'] < 10 ? 'text-danger' : 'text-success' ?>">
              <?= $r['moy_normale'] ?? '—' ?>
            </td>
            <td class="text-center text-warning fw-semibold">
              <?= $r['moy_ratt'] ?? '—' ?>
            </td>
            <td class="text-center">
              <?php if ($r['nb_absences'] > 0): ?>
              <span class="badge bg-warning text-dark"><?= $r['nb_absences'] ?></span>
              <?php else: echo '0'; endif; ?>
            </td>
            <td class="text-center">
              <span class="badge bg-<?= $badgeClass ?>"><?= $statut ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($resultats)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Aucun résultat pour les filtres sélectionnés.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
// préparer données JSON pour les graphiques
$distLabels = json_encode(array_keys($distribution));
$distData   = json_encode(array_values($distribution));

$filieresLabels = json_encode(array_column($moyFilieres, 'intitule'));
$filieresMoy    = json_encode(array_column($moyFilieres, 'moy'));

$absLabels = json_encode(array_keys($absParMois));
$absData   = json_encode(array_values($absParMois));

$resultatsData = json_encode([
  'Admis'     => $kpis['nbAdmis'],
  'Rattrapage'=> $kpis['nbRattrapage'],
  'Ajourné'   => max(0, $kpis['nbEtudiants'] - $kpis['nbAdmis'] - $kpis['nbRattrapage']),
]);

$inlineScript = "
const colorPalette = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796'];

// distribution des moyennes
new Chart(document.getElementById('chartDistribution'), {
  type: 'bar',
  data: {
    labels: $distLabels,
    datasets: [{
      label: 'Nb étudiants',
      data: $distData,
      backgroundColor: colorPalette,
      borderRadius: 6
    }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

// absences par mois
new Chart(document.getElementById('chartAbsences'), {
  type: 'line',
  data: {
    labels: $absLabels,
    datasets: [{
      label: 'Absences injustifiées',
      data: $absData,
      borderColor: '#f6c23e',
      backgroundColor: 'rgba(246,194,62,0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 5
    }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

// moyennes par filière
new Chart(document.getElementById('chartFilieres'), {
  type: 'bar',
  data: {
    labels: $filieresLabels,
    datasets: [{
      label: 'Moyenne /20',
      data: $filieresMoy,
      backgroundColor: colorPalette,
      borderRadius: 6
    }]
  },
  options: {
    indexAxis: 'y',
    plugins: { legend: { display: false } },
    scales: { x: { beginAtZero: true, max: 20 } }
  }
});

// résultats globaux 
const rd = $resultatsData;
new Chart(document.getElementById('chartResultats'), {
  type: 'doughnut',
  data: {
    labels: Object.keys(rd),
    datasets: [{ data: Object.values(rd), backgroundColor: ['#1cc88a','#f6c23e','#e74a3b'], borderWidth: 2 }]
  },
  options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
});
";
include __DIR__ . '/../includes/footer.php';
?>
