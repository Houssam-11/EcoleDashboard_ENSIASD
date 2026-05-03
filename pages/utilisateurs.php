<?php
/* pages/utilisateurs.php — gestion des utilisateurs  */

require_once __DIR__ . '/../includes/fonctions.php';
if (getUserRole() !== 'admin') { header('Location: ' . BASE_URL . 'pages/dashboard.php'); exit; }
$pageTitle = 'Utilisateurs';
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter') {
    $hash = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);
    try {
        $db->prepare("INSERT INTO utilisateurs (login,mot_de_passe,nom,prenom,role) VALUES (:l,:m,:n,:p,:r)")
           ->execute([':l'=>trim($_POST['login']),':m'=>$hash,':n'=>trim($_POST['nom']),
                      ':p'=>trim($_POST['prenom']),':r'=>$_POST['role']]);
        $msg = 'Utilisateur ajouté.';
    } catch (PDOException $e) { $msg = 'Login déjà existant.'; $msgType = 'danger'; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $db->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id=:id AND id != 1")
       ->execute([':id'=>(int)$_POST['id']]);
    $msg = 'Statut mis à jour.';
}

$users = $db->query("SELECT * FROM utilisateurs ORDER BY role, nom")->fetchAll();
$roleColors = ['admin'=>'danger','enseignant'=>'primary','secretaire'=>'success'];

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-bold"><i class="bi bi-gear-fill me-2 text-danger"></i>Gestion des utilisateurs</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUser">
    <i class="bi bi-plus-circle me-1"></i>Ajouter
  </button>
</div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> py-2"><?= h($msg) ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover table-sm mb-0">
      <thead class="table-light">
        <tr><th>Login</th><th>Nom complet</th><th>Rôle</th><th>Statut</th><th>Créé le</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><code><?= h($u['login']) ?></code></td>
          <td><?= h($u['prenom']) ?> <?= h($u['nom']) ?></td>
          <td><span class="badge bg-<?= $roleColors[$u['role']] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td><span class="badge bg-<?= $u['actif'] ? 'success' : 'secondary' ?>">
            <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
          </span></td>
          <td class="small text-muted"><?= date('d/m/Y', strtotime($u['cree_le'])) ?></td>
          <td>
            <?php if ($u['id'] != 1): ?>
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button class="btn btn-sm btn-outline-<?= $u['actif']?'warning':'success' ?> py-0 px-1">
                <i class="bi bi-<?= $u['actif']?'pause':'play' ?>"></i>
              </button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Ajouter un utilisateur</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-body row g-3">
          <div class="col-6"><label class="form-label small fw-semibold">Login</label>
            <input type="text" name="login" class="form-control form-control-sm" required></div>
          <div class="col-6"><label class="form-label small fw-semibold">Mot de passe</label>
            <input type="password" name="mot_de_passe" class="form-control form-control-sm" required></div>
          <div class="col-6"><label class="form-label small fw-semibold">Nom</label>
            <input type="text" name="nom" class="form-control form-control-sm" required></div>
          <div class="col-6"><label class="form-label small fw-semibold">Prénom</label>
            <input type="text" name="prenom" class="form-control form-control-sm" required></div>
          <div class="col-12"><label class="form-label small fw-semibold">Rôle</label>
            <select name="role" class="form-select form-select-sm">
              <option value="secretaire">Secrétaire</option>
              <option value="enseignant">Enseignant</option>
              <option value="admin">Administrateur</option>
            </select></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger btn-sm">Créer</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
