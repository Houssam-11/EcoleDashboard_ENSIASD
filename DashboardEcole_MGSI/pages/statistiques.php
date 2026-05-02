<?php
/*pages/statistiques.php — visualisations statistiques avancées */
require_once __DIR__ . '/../includes/fonctions.php';
$pageTitle = 'Statistiques';

$annees   = getAnnees();
$filieres = getFilieres();
$anneeEnCours = getAnneeEnCours();

$anneeId   = (int)($_GET['annee']   ?? $anneeEnCours['id'] ?? 0);
$filiereId = (int)($_GET['filiere'] ?? 0) ?: null;

$db = getDB();

// evolution des admissions sur toutes les années
$stmtEvol = $db->query("
    SELECT aa.libelle,
           COUNT(DISTINCT e.id) as total,
           SUM(CASE WHEN (SELECT AVG(n2.note_finale) FROM notes n2
                         WHERE n2.etudiant_id=e.id AND n2.annee_id=aa.id
                         AND n2.session IN ('normale','rattrapage')) >= 10 THEN 1 ELSE 0 END) as admis
    FROM annees_academiques aa
    LEFT JOIN etudiants e ON e.annee_id = aa.id
    GROUP BY aa.id, aa.libelle
    ORDER BY aa.libelle
");
$evolution = $stmtEvol->fetchAll();

// meilleure moyenne
$sqlTopMod = "SELECT m.intitule, ROUND(AVG(n.note_finale),2) as moy, COUNT(n.id) as nb
              FROM modules m
              JOIN notes n ON n.module_id = m.id AND n.session = 'normale'
              JOIN etudiants e ON n.etudiant_id = e.id AND e.annee_id = :annee";
$pTop = [':annee' => $anneeId];
if ($filiereId) { $sqlTopMod .= " AND m.filiere_id = :f"; $pTop[':f'] = $filiereId; }
$sqlTopMod .= " GROUP BY m.id, m.intitule ORDER BY moy DESC LIMIT 8";
$stmtTop = $db->prepare($sqlTopMod); $stmtTop->execute($pTop);
$topModules = $stmtTop->fetchAll();

// taux de réussite par filière
$stmtReuss = $db->query("
    SELECT f.code, f.intitule,
           COUNT(DISTINCT e.id) as total,
           COUNT(DISTINCT CASE WHEN (
             SELECT AVG(n2.note_finale) FROM notes n2
             WHERE n2.etudiant_id=e.id AND n2.annee_id=$anneeId
           ) >= 10 THEN e.id END) as admis
    FROM filieres f
    LEFT JOIN etudiants e ON e.filiere_id=f.id AND e.annee_id=$anneeId
    WHERE f.actif=1
    GROUP BY f.id, f.code, f.intitule
");
$reussiteFiliere = $stmtReuss->fetchAll();

// absences justifiées vs non justifiées
$stmtAbsType = $db->prepare("
    SELECT justifiee, COUNT(*) as nb FROM absences a
    JOIN etudiants e ON a.etudiant_id=e.id
    WHERE a.annee_id = :annee
    GROUP BY justifiee
");
$stmtAbsType->execute([':annee' => $anneeId]);
$absType = ['justifiée' => 0, 'non justifiée' => 0];
foreach ($stmtAbsType->fetchAll() as $r) {
    $absType[$r['justifiee'] ? 'justifiée' : 'non justifiée'] = (int)$r['nb'];
}

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Statistiques avancées</h5>
</div>

<!-- filtres -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3">
        <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($annees as $a): ?>
          <option value="<?= $a['id'] ?>" <?= $a['id'] == $anneeId ? 'selected' : '' ?>>
            <?= h($a['libelle']) ?><?= $a['en_cours'] ? ' (en cours)' : '' ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="filiere" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Toutes filières</option>
          <?php foreach ($filieres as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $f['id'] == $filiereId ? 'selected' : '' ?>><?= h($f['code']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- evolution des admissions -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">
        <i class="bi bi-bar-chart-line me-2 text-primary"></i>Évolution des admissions par année
      </div>
      <div class="card-body"><canvas id="chartEvolution" height="200"></canvas></div>
    </div>
  </div>
  <!-- absences justifiées vs non -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">
        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>Types d'absences
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="chartAbsType" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- taux de réussite par filière  -->
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">
        <i class="bi bi-patch-check me-2 text-success"></i>Taux de réussite par filière
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light"><tr><th>Filière</th><th>Total</th><th>Admis</th><th>Taux</th></tr></thead>
          <tbody>
            <?php foreach ($reussiteFiliere as $r):
              $taux = $r['total'] > 0 ? round($r['admis']/$r['total']*100,1) : 0;
              $barClass = $taux >= 80 ? 'success' : ($taux >= 60 ? 'warning' : 'danger');
            ?>
            <tr>
              <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= h($r['code']) ?></span></td>
              <td><?= $r['total'] ?></td>
              <td><?= $r['admis'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height:6px;">
                    <div class="progress-bar bg-<?= $barClass ?>" style="width:<?= $taux ?>%"></div>
                  </div>
                  <small><?= $taux ?>%</small>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- top modules -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">
        <i class="bi bi-journal-text me-2 text-info"></i>Moyennes par module (Top 8)
      </div>
      <div class="card-body"><canvas id="chartModules" height="230"></canvas></div>
    </div>
  </div>
</div>

<?php
$evolLabels  = json_encode(array_column($evolution, 'libelle'));
$evolTotal   = json_encode(array_column($evolution, 'total'));
$evolAdmis   = json_encode(array_column($evolution, 'admis'));

$absTypeLabels = json_encode(array_keys($absType));
$absTypeData   = json_encode(array_values($absType));

$modLabels = json_encode(array_map(fn($m) => mb_substr($m['intitule'],0,25), $topModules));
$modData   = json_encode(array_column($topModules, 'moy'));

$inlineScript = "
// evolution
new Chart(document.getElementById('chartEvolution'), {
  type:'bar',
  data:{
    labels:$evolLabels,
    datasets:[
      {label:'Total inscrits',data:$evolTotal,backgroundColor:'rgba(78,115,223,0.3)',borderColor:'#4e73df',borderWidth:2,borderRadius:4},
      {label:'Admis',data:$evolAdmis,backgroundColor:'rgba(28,200,138,0.5)',borderColor:'#1cc88a',borderWidth:2,borderRadius:4}
    ]
  },
  options:{scales:{y:{beginAtZero:true}}}
});

// types absences
new Chart(document.getElementById('chartAbsType'),{
  type:'pie',
  data:{labels:$absTypeLabels,datasets:[{data:$absTypeData,backgroundColor:['#1cc88a','#e74a3b']}]},
  options:{plugins:{legend:{position:'bottom'}}}
});

// modules
new Chart(document.getElementById('chartModules'),{
  type:'bar',
  data:{labels:$modLabels,datasets:[{label:'Moy /20',data:$modData,backgroundColor:'rgba(54,185,204,0.7)',borderColor:'#36b9cc',borderWidth:1,borderRadius:4}]},
  options:{indexAxis:'y',scales:{x:{beginAtZero:true,max:20}},plugins:{legend:{display:false}}}
});
";
include __DIR__ . '/../includes/footer.php';
?>
