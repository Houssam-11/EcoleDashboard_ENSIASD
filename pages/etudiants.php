<?php
/* pages/etudiants.php — gestion des étudiants */
require_once __DIR__ . '/../includes/fonctions.php';
$pageTitle = 'Étudiants';

$db       = getDB();
$filieres = getFilieres();
$annees   = getAnnees();
$anneeEnCours = getAnneeEnCours();

$anneeId   = (int)($_GET['annee']   ?? $anneeEnCours['id'] ?? 0);
$filiereId = (int)($_GET['filiere'] ?? 0) ?: null;
$semestre  = (int)($_GET['semestre']?? 0) ?: null;
$search    = trim($_GET['search']   ?? '');

//CRUD 
$msg = ''; $msgType = 'success';

// Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter') {
    $stmt = $db->prepare("INSERT INTO etudiants (cne,nom,prenom,date_naissance,email,filiere_id,annee_id,semestre)
                          VALUES (:cne,:nom,:prenom,:dn,:email,:fid,:aid,:sem)");
    try {
        $stmt->execute([
            ':cne'=>trim($_POST['cne']),':nom'=>trim($_POST['nom']),
            ':prenom'=>trim($_POST['prenom']),':dn'=>$_POST['date_naissance']??null,
            ':email'=>trim($_POST['email'])??null,':fid'=>(int)$_POST['filiere_id'],
            ':aid'=>(int)$_POST['annee_id'],':sem'=>(int)$_POST['semestre']
        ]);
        $msg = 'Étudiant ajouté avec succès.';
    } catch (PDOException $e) {
        $msg = 'Erreur : CNE déjà existant ou données invalides.'; $msgType = 'danger';
    }
}
// suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $db->prepare("DELETE FROM etudiants WHERE id = :id")->execute([':id'=>(int)$_POST['id']]);
    $msg = 'Étudiant supprimé.';
}

// requête liste
$sql = "SELECT e.*, f.code as filiere_code, a.libelle as annee
        FROM etudiants e
        JOIN filieres f ON e.filiere_id = f.id
        JOIN annees_academiques a ON e.annee_id = a.id
        WHERE e.annee_id = :annee";
$p = [':annee' => $anneeId];
if ($filiereId) { $sql .= " AND e.filiere_id = :f";   $p[':f']   = $filiereId; }
if ($semestre)  { $sql .= " AND e.semestre   = :sem"; $p[':sem'] = $semestre; }
if ($search)    { $sql .= " AND (e.nom LIKE :s OR e.prenom LIKE :s2 OR e.cne LIKE :s3)";
    $p[':s'] = "%$search%"; $p[':s2'] = "%$search%"; $p[':s3'] = "%$search%"; }
$sql .= " ORDER BY e.nom, e.prenom";
$stmt = $db->prepare($sql); $stmt->execute($p);
$etudiants = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Gestion des Étudiants</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjout">
    <i class="bi bi-plus-circle me-1"></i>Ajouter
  </button>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2">
  <?= h($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- filtres -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-2">
        <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($annees as $a): ?>
          <option value="<?= $a['id'] ?>" <?= $a['id'] == $anneeId ? 'selected' : '' ?>><?= h($a['libelle']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="filiere" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Toutes les filières</option>
          <?php foreach ($filieres as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $f['id'] == $filiereId ? 'selected' : '' ?>><?= h($f['code']) ?></option>
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
      <div class="col-md-3">
        <div class="input-group input-group-sm">
          <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= h($search) ?>">
          <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </div>
      <div class="col-md-2">
        <a href="etudiants.php" class="btn btn-outline-secondary btn-sm w-100">Réinitialiser</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
    <span class="fw-semibold">Total : <strong><?= count($etudiants) ?></strong> étudiant(s)</span>
    <a href="export.php?type=etudiants&annee=<?= $anneeId ?>&filiere=<?= $filiereId ?>"
       class="btn btn-outline-success btn-sm">
      <i class="bi bi-file-earmark-excel me-1"></i>CSV
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th><th>CNE</th><th>Nom</th><th>Prénom</th>
            <th>Filière</th><th>Sem.</th><th>Email</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($etudiants as $i => $e): ?>
          <tr>
            <td class="text-muted small"><?= $i+1 ?></td>
            <td><code><?= h($e['cne']) ?></code></td>
            <td><?= h($e['nom']) ?></td>
            <td><?= h($e['prenom']) ?></td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= h($e['filiere_code']) ?></span></td>
            <td>S<?= $e['semestre'] ?></td>
            <td class="small text-muted"><?= h($e['email'] ?? '') ?></td>
            <td>
              <form method="POST" class="d-inline"
                    onsubmit="return confirm('Supprimer cet étudiant ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                <button class="btn btn-danger btn-xs py-0 px-1 btn-sm">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($etudiants)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Aucun étudiant trouvé.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- modal ajout -->
<div class="modal fade" id="modalAjout" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Ajouter un étudiant</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-body row g-3">
          <div class="col-6">
            <label class="form-label small fw-semibold">CNE *</label>
            <input type="text" name="cne" class="form-control form-control-sm" required>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Email</label>
            <input type="email" name="email" class="form-control form-control-sm">
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Nom *</label>
            <input type="text" name="nom" class="form-control form-control-sm" required>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Prénom *</label>
            <input type="text" name="prenom" class="form-control form-control-sm" required>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Date de naissance</label>
            <input type="date" name="date_naissance" class="form-control form-control-sm">
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Semestre *</label>
            <select name="semestre" class="form-select form-select-sm" required>
              <option value="1">Semestre 1</option>
              <option value="2">Semestre 2</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Filière *</label>
            <select name="filiere_id" class="form-select form-select-sm" required>
              <?php foreach ($filieres as $f): ?>
              <option value="<?= $f['id'] ?>"><?= h($f['code']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Année *</label>
            <select name="annee_id" class="form-select form-select-sm" required>
              <?php foreach ($annees as $a): ?>
              <option value="<?= $a['id'] ?>" <?= $a['en_cours'] ? 'selected' : '' ?>><?= h($a['libelle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-save me-1"></i>Enregistrer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
