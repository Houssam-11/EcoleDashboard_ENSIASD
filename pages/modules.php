<?php
/*pages/modules.php — gestion des modules */
require_once __DIR__ . '/../includes/fonctions.php';
$pageTitle = 'Modules';
$db = getDB();
$filieres = getFilieres();
$filiereId = (int)($_GET['filiere'] ?? 0) ?: null;
$semestre  = (int)($_GET['semestre']?? 0) ?: null;

$sql = "SELECT m.*, f.code as filiere_code, ens.nom as ens_nom, ens.prenom as ens_prenom
        FROM modules m
        JOIN filieres f ON m.filiere_id = f.id
        LEFT JOIN enseignants ens ON m.enseignant_id = ens.id
        WHERE 1=1";
$p = [];
if ($filiereId) { $sql .= " AND m.filiere_id=:f"; $p[':f']=$filiereId; }
if ($semestre)  { $sql .= " AND m.semestre=:sem"; $p[':sem']=$semestre; }
$sql .= " ORDER BY f.code, m.semestre, m.code";
$stmt = $db->prepare($sql); $stmt->execute($p);
$modules = $stmt->fetchAll();

$enseignants = $db->query("SELECT * FROM enseignants WHERE actif=1 ORDER BY nom")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-info"></i>Modules d'enseignement</h5>
</div>
<!-- Filtres -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body py-2">
    <form method="GET" class="row g-2">
      <div class="col-md-3">
        <select name="filiere" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Toutes filières</option>
          <?php foreach ($filieres as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $f['id']==$filiereId?'selected':'' ?>><?= h($f['code']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="semestre" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Tous semestres</option>
          <option value="1" <?= $semestre==1?'selected':'' ?>>S1</option>
          <option value="2" <?= $semestre==2?'selected':'' ?>>S2</option>
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover table-sm mb-0">
      <thead class="table-light">
        <tr><th>Code</th><th>Intitulé</th><th>Filière</th><th>Sem.</th><th>Coeff.</th><th>Enseignant</th></tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $m): ?>
        <tr>
          <td><code><?= h($m['code']) ?></code></td>
          <td><?= h($m['intitule']) ?></td>
          <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= h($m['filiere_code']) ?></span></td>
          <td>S<?= $m['semestre'] ?></td>
          <td><?= $m['coefficient'] ?></td>
          <td><?= $m['ens_nom'] ? h($m['ens_nom'] . ' ' . $m['ens_prenom']) : '<span class="text-muted">—</span>' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($modules)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Aucun module trouvé.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
