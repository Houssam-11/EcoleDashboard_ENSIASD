<?php
/* pages/absences.php — gestion des absences */
require_once __DIR__ . '/../includes/fonctions.php';
$pageTitle = 'Absences';

$db       = getDB();
$filieres = getFilieres();
$annees   = getAnnees();
$anneeEnCours = getAnneeEnCours();

$anneeId   = (int)($_GET['annee']   ?? $anneeEnCours['id'] ?? 0);
$filiereId = (int)($_GET['filiere'] ?? 0) ?: null;

$msg = ''; $msgType = 'success';

// ajout absence
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter') {
    try {
        $db->prepare("INSERT INTO absences (etudiant_id,module_id,date_absence,justifiee,annee_id)
                      VALUES (:e,:m,:d,:j,:a)")
           ->execute([':e'=>$_POST['etudiant_id'],':m'=>$_POST['module_id'],
                      ':d'=>$_POST['date_absence'],':j'=>isset($_POST['justifiee'])?1:0,
                      ':a'=>$anneeId]);
        $msg = 'Absence enregistrée.';
    } catch (PDOException $ex) { $msg = 'Erreur : ' . $ex->getMessage(); $msgType = 'danger'; }
}
// suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $db->prepare("DELETE FROM absences WHERE id=:id")->execute([':id'=>(int)$_POST['id']]);
    $msg = 'Absence supprimée.';
}
// justifier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'justifier') {
    $db->prepare("UPDATE absences SET justifiee=1 WHERE id=:id")->execute([':id'=>(int)$_POST['id']]);
    $msg = 'Absence justifiée.';
}

$sql = "SELECT a.*, e.nom, e.prenom, e.cne, f.code as filiere, m.intitule as module, m.code as mod_code
        FROM absences a
        JOIN etudiants e ON a.etudiant_id = e.id
        JOIN filieres f ON e.filiere_id = f.id
        JOIN modules m ON a.module_id = m.id
        WHERE a.annee_id = :annee";
$p = [':annee'=>$anneeId];
if ($filiereId) { $sql .= " AND e.filiere_id=:f"; $p[':f']=$filiereId; }
$sql .= " ORDER BY a.date_absence DESC, e.nom";
$stmt = $db->prepare($sql); $stmt->execute($p);
$absences = $stmt->fetchAll();

// etudiants & modules pour formulaire
$stmtEt = $db->prepare("SELECT id,cne,nom,prenom FROM etudiants WHERE annee_id=:a ORDER BY nom");
$stmtEt->execute([':a'=>$anneeId]); $etudiants = $stmtEt->fetchAll();
$stmtMod = $db->query("SELECT id,code,intitule FROM modules ORDER BY code"); $modules = $stmtMod->fetchAll();

$nbInj = count(array_filter($absences, fn($a) => !$a['justifiee']));
$nbJus = count($absences) - $nbInj;

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-x me-2 text-warning"></i>Gestion des absences</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAbs">
    <i class="bi bi-plus-circle me-1"></i>Enregistrer une absence
  </button>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2">
  <?= h($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- stats rapides -->
<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm border-start border-4 border-danger">
      <div class="card-body py-2">
        <div class="text-muted small">Absences non justifiées</div>
        <div class="fs-3 fw-bold text-danger"><?= $nbInj ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm border-start border-4 border-success">
      <div class="card-body py-2">
        <div class="text-muted small">Absences justifiées</div>
        <div class="fs-3 fw-bold text-success"><?= $nbJus ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm border-start border-4 border-secondary">
      <div class="card-body py-2">
        <div class="text-muted small">Total absences</div>
        <div class="fs-3 fw-bold"><?= count($absences) ?></div>
      </div>
    </div>
  </div>
</div>

<!-- filtres -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body py-2">
    <form method="GET" class="row g-2">
      <div class="col-md-3">
        <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($annees as $a): ?>
          <option value="<?= $a['id'] ?>" <?= $a['id']==$anneeId?'selected':'' ?>><?= h($a['libelle']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="filiere" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Toutes filières</option>
          <?php foreach ($filieres as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $f['id']==$filiereId?'selected':'' ?>><?= h($f['code']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
    <span class="fw-semibold"><?= count($absences) ?> absence(s)</span>
    <a href="export.php?type=absences&format=csv&annee=<?= $anneeId ?>&filiere=<?= $filiereId ?>"
       class="btn btn-outline-success btn-sm">
      <i class="bi bi-download me-1"></i>CSV
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr><th>Étudiant</th><th>Filière</th><th>Module</th><th>Date</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($absences as $ab): ?>
          <tr>
            <td>
              <span class="fw-semibold"><?= h($ab['nom']) ?> <?= h($ab['prenom']) ?></span><br>
              <small class="text-muted"><?= h($ab['cne']) ?></small>
            </td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= h($ab['filiere']) ?></span></td>
            <td><small><?= h($ab['mod_code']) ?></small></td>
            <td><?= date('d/m/Y', strtotime($ab['date_absence'])) ?></td>
            <td>
              <?php if ($ab['justifiee']): ?>
              <span class="badge bg-success">Justifiée</span>
              <?php else: ?>
              <span class="badge bg-danger">Non justifiée</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!$ab['justifiee']): ?>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="justifier">
                <input type="hidden" name="id" value="<?= $ab['id'] ?>">
                <button class="btn btn-success btn-sm py-0 px-1" title="Marquer comme justifiée">
                  <i class="bi bi-check2"></i>
                </button>
              </form>
              <?php endif; ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $ab['id'] ?>">
                <button class="btn btn-danger btn-sm py-0 px-1"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($absences)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Aucune absence enregistrée.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- modal ajout -->
<div class="modal fade" id="modalAbs" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="bi bi-calendar-x me-2"></i>Enregistrer une absence</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-body row g-3">
          <div class="col-12">
            <label class="form-label small fw-semibold">Étudiant *</label>
            <select name="etudiant_id" class="form-select form-select-sm" required>
              <option value="">— Sélectionner —</option>
              <?php foreach ($etudiants as $et): ?>
              <option value="<?= $et['id'] ?>"><?= h($et['cne']) ?> — <?= h($et['nom']) ?> <?= h($et['prenom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Module *</label>
            <select name="module_id" class="form-select form-select-sm" required>
              <option value="">— Sélectionner —</option>
              <?php foreach ($modules as $m): ?>
              <option value="<?= $m['id'] ?>"><?= h($m['code']) ?> — <?= h($m['intitule']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-8">
            <label class="form-label small fw-semibold">Date *</label>
            <input type="date" name="date_absence" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-4 d-flex align-items-end pb-1">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="justifiee" id="justifiee">
              <label class="form-check-label small" for="justifiee">Justifiée</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-save me-1"></i>Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
