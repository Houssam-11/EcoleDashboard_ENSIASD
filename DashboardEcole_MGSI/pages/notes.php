<?php
/* pages/notes.php — tableau des notes et résultats */
require_once __DIR__ . '/../includes/fonctions.php';
$pageTitle = 'Notes & Résultats';

$db       = getDB();
$filieres = getFilieres();
$annees   = getAnnees();
$anneeEnCours = getAnneeEnCours();

$anneeId   = (int)($_GET['annee']   ?? $anneeEnCours['id'] ?? 0);
$filiereId = (int)($_GET['filiere'] ?? 0) ?: null;
$semestre  = (int)($_GET['semestre']?? 0) ?: null;
$session   = $_GET['session'] ?? 'normale';

$msg = ''; $msgType = 'success';

// ajout/modification note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sauver_note') {
    $stmtCheck = $db->prepare("SELECT id FROM notes WHERE etudiant_id=:e AND module_id=:m AND session=:s AND annee_id=:a");
    $stmtCheck->execute([':e'=>$_POST['etudiant_id'],':m'=>$_POST['module_id'],':s'=>$_POST['session'],':a'=>$_POST['annee_id']]);
    $existing = $stmtCheck->fetchColumn();
    try {
        if ($existing) {
            $db->prepare("UPDATE notes SET note_cc=:cc, note_examen=:ex WHERE id=:id")
               ->execute([':cc'=>$_POST['note_cc']??null,':ex'=>$_POST['note_examen'],':id'=>$existing]);
        } else {
            $db->prepare("INSERT INTO notes (etudiant_id,module_id,note_cc,note_examen,session,annee_id) VALUES (:e,:m,:cc,:ex,:s,:a)")
               ->execute([':e'=>$_POST['etudiant_id'],':m'=>$_POST['module_id'],':cc'=>$_POST['note_cc']??null,':ex'=>$_POST['note_examen'],':s'=>$_POST['session'],':a'=>$_POST['annee_id']]);
        }
        $msg = 'Note enregistrée.';
    } catch (PDOException $e) { $msg = 'Erreur : ' . $e->getMessage(); $msgType = 'danger'; }
}

// récupérer modules selon filtre
$sqlMod = "SELECT m.*, ens.nom as ens_nom FROM modules m LEFT JOIN enseignants ens ON m.enseignant_id=ens.id WHERE 1=1";
$pMod = [];
if ($filiereId) { $sqlMod .= " AND m.filiere_id=:f"; $pMod[':f']=$filiereId; }
if ($semestre)  { $sqlMod .= " AND m.semestre=:sem"; $pMod[':sem']=$semestre; }
$sqlMod .= " ORDER BY m.code";
$stmtMod = $db->prepare($sqlMod); $stmtMod->execute($pMod);
$modules = $stmtMod->fetchAll();

// notes détaillées par module
$sqlNotes = "SELECT n.*, e.nom, e.prenom, e.cne, m.intitule as module_intitule, m.code as module_code
             FROM notes n
             JOIN etudiants e ON n.etudiant_id = e.id
             JOIN modules m ON n.module_id = m.id
             WHERE n.annee_id = :annee AND n.session = :sess";
$pNotes = [':annee'=>$anneeId, ':sess'=>$session];
if ($filiereId) { $sqlNotes .= " AND e.filiere_id=:f"; $pNotes[':f']=$filiereId; }
if ($semestre)  { $sqlNotes .= " AND e.semestre=:sem"; $pNotes[':sem']=$semestre; }
$sqlNotes .= " ORDER BY e.nom, e.prenom, m.code";
$stmtN = $db->prepare($sqlNotes); $stmtN->execute($pNotes);
$notes = $stmtN->fetchAll();

// etudiants pour formulaire
$stmtEt = $db->prepare("SELECT id,cne,nom,prenom FROM etudiants WHERE annee_id=:a ORDER BY nom");
$stmtEt->execute([':a'=>$anneeId]);
$etudiants = $stmtEt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-bold"><i class="bi bi-card-checklist me-2 text-primary"></i>Notes & Résultats</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNote">
    <i class="bi bi-plus-circle me-1"></i>Saisir une note
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
      <div class="col-md-2">
        <select name="semestre" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Tous</option>
          <option value="1" <?= $semestre==1?'selected':'' ?>>S1</option>
          <option value="2" <?= $semestre==2?'selected':'' ?>>S2</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="session" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="normale"    <?= $session==='normale'   ?'selected':'' ?>>Session normale</option>
          <option value="rattrapage" <?= $session==='rattrapage'?'selected':'' ?>>Rattrapage</option>
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold d-flex justify-content-between">
    <span>
      <?= count($notes) ?> note(s) —
      <span class="badge bg-<?= $session==='normale'?'primary':'warning text-dark' ?>">
        Session <?= $session ?>
      </span>
    </span>
    <a href="export.php?type=resultats&format=csv&annee=<?= $anneeId ?>&filiere=<?= $filiereId ?>&semestre=<?= $semestre ?>"
       class="btn btn-outline-success btn-sm">
      <i class="bi bi-download me-1"></i>CSV
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>Étudiant</th><th>Module</th>
            <th class="text-center">CC</th>
            <th class="text-center">Examen</th>
            <th class="text-center">Finale</th>
            <th class="text-center">Résultat</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($notes as $n):
            $finale = $n['note_finale'];
            $resClass = $finale >= 10 ? 'success' : 'danger';
          ?>
          <tr>
            <td>
              <span class="fw-semibold"><?= h($n['nom']) ?> <?= h($n['prenom']) ?></span>
              <br><small class="text-muted"><?= h($n['cne']) ?></small>
            </td>
            <td>
              <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= h($n['module_code']) ?></span>
              <br><small><?= h(mb_substr($n['module_intitule'],0,30)) ?></small>
            </td>
            <td class="text-center"><?= $n['note_cc'] ?? '—' ?></td>
            <td class="text-center"><?= $n['note_examen'] ?? '—' ?></td>
            <td class="text-center fw-bold text-<?= $resClass ?>">
              <?= number_format((float)$finale, 2) ?>
            </td>
            <td class="text-center">
              <span class="badge bg-<?= $resClass ?>">
                <?= $finale >= 10 ? 'Validé' : 'Insuffisant' ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($notes)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Aucune note pour les filtres sélectionnés.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- modal saisie note -->
<div class="modal fade" id="modalNote" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Saisir une note</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="sauver_note">
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
          <div class="col-4">
            <label class="form-label small fw-semibold">CC (/20)</label>
            <input type="number" name="note_cc" class="form-control form-control-sm" min="0" max="20" step="0.25">
          </div>
          <div class="col-4">
            <label class="form-label small fw-semibold">Examen (/20) *</label>
            <input type="number" name="note_examen" class="form-control form-control-sm" min="0" max="20" step="0.25" required>
          </div>
          <div class="col-4">
            <label class="form-label small fw-semibold">Session</label>
            <select name="session" class="form-select form-select-sm">
              <option value="normale">Normale</option>
              <option value="rattrapage">Rattrapage</option>
            </select>
          </div>
          <input type="hidden" name="annee_id" value="<?= $anneeId ?>">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
