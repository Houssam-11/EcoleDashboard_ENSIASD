<?php
/*pages/enseignants.php — liste des enseignants  */
require_once __DIR__ . '/../includes/fonctions.php';
$pageTitle = 'Enseignants';
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter') {
    try {
        $db->prepare("INSERT INTO enseignants (matricule,nom,prenom,grade,email) VALUES (:mat,:n,:p,:g,:e)")
           ->execute([':mat'=>trim($_POST['matricule']),':n'=>trim($_POST['nom']),
                      ':p'=>trim($_POST['prenom']),':g'=>$_POST['grade'],':e'=>trim($_POST['email']??'')]);
        $msg = 'Enseignant ajouté.';
    } catch (PDOException $e) { $msg = 'Erreur : ' . $e->getMessage(); $msgType = 'danger'; }
}

$enseignants = $db->query("SELECT * FROM enseignants ORDER BY nom, prenom")->fetchAll();
$gradeColors = ['PES'=>'primary','PA'=>'success','PH'=>'info','Vacataire'=>'secondary'];

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge-fill me-2 text-success"></i>Enseignants</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEns">
    <i class="bi bi-plus-circle me-1"></i>Ajouter
  </button>
</div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> py-2"><?= h($msg) ?></div><?php endif; ?>

<div class="row g-3">
  <?php foreach ($enseignants as $e): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-primary bg-opacity-10 rounded-circle p-3">
            <i class="bi bi-person-fill fs-4 text-primary"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold"><?= h($e['nom']) ?> <?= h($e['prenom']) ?></h6>
            <small class="text-muted"><code><?= h($e['matricule']) ?></code></small>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2 flex-wrap">
          <span class="badge bg-<?= $gradeColors[$e['grade']] ?? 'secondary' ?>"><?= h($e['grade']) ?></span>
          <?php if ($e['email']): ?>
          <a href="mailto:<?= h($e['email']) ?>" class="badge bg-light text-dark border text-decoration-none">
            <i class="bi bi-envelope me-1"></i><?= h($e['email']) ?>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- modal -->
<div class="modal fade" id="modalEns" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Ajouter un enseignant</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-body row g-3">
          <div class="col-6"><label class="form-label small fw-semibold">Matricule</label>
            <input type="text" name="matricule" class="form-control form-control-sm" required></div>
          <div class="col-6"><label class="form-label small fw-semibold">Grade</label>
            <select name="grade" class="form-select form-select-sm">
              <option>PES</option><option>PA</option><option>PH</option><option>Vacataire</option>
            </select></div>
          <div class="col-6"><label class="form-label small fw-semibold">Nom</label>
            <input type="text" name="nom" class="form-control form-control-sm" required></div>
          <div class="col-6"><label class="form-label small fw-semibold">Prénom</label>
            <input type="text" name="prenom" class="form-control form-control-sm" required></div>
          <div class="col-12"><label class="form-label small fw-semibold">Email</label>
            <input type="email" name="email" class="form-control form-control-sm"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
