<?php
/*pages/export.php — export des rapports statistiques (CSV) */
require_once __DIR__ . '/../includes/fonctions.php';

$type      = $_GET['type']     ?? 'resultats';
$anneeId   = (int)($_GET['annee']   ?? 0);
$filiereId = (int)($_GET['filiere'] ?? 0) ?: null;
$semestre  = (int)($_GET['semestre']?? 0) ?: null;

// si export CSV demandé via ? format=csv
if (isset($_GET['format']) && $_GET['format'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="export_' . $type . '_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');
    echo "\xEF\xBB\xBF"; 

    $out = fopen('php://output', 'w');

    switch ($type) {
        case 'etudiants':
            fputcsv($out, ['CNE','Nom','Prénom','Filière','Semestre','Email','Année'], ';');
            $db = getDB();
            $sql = "SELECT e.cne,e.nom,e.prenom,f.code,e.semestre,e.email,a.libelle
                    FROM etudiants e JOIN filieres f ON e.filiere_id=f.id
                    JOIN annees_academiques a ON e.annee_id=a.id
                    WHERE e.annee_id=:annee";
            $p = [':annee' => $anneeId];
            if ($filiereId) { $sql .= " AND e.filiere_id=:f"; $p[':f'] = $filiereId; }
            $stmt = $db->prepare($sql); $stmt->execute($p);
            foreach ($stmt->fetchAll() as $r) fputcsv($out, $r, ';');
            break;

        case 'absences':
            fputcsv($out, ['CNE','Nom','Prénom','Filière','Module','Date','Justifiée'], ';');
            $db = getDB();
            $sql = "SELECT e.cne,e.nom,e.prenom,f.code,m.intitule,a.date_absence,
                           IF(a.justifiee,'Oui','Non')
                    FROM absences a
                    JOIN etudiants e ON a.etudiant_id=e.id
                    JOIN filieres f ON e.filiere_id=f.id
                    JOIN modules m ON a.module_id=m.id
                    WHERE a.annee_id=:annee";
            $p = [':annee' => $anneeId];
            if ($filiereId) { $sql .= " AND e.filiere_id=:f"; $p[':f'] = $filiereId; }
            $stmt = $db->prepare($sql); $stmt->execute($p);
            foreach ($stmt->fetchAll() as $r) fputcsv($out, $r, ';');
            break;

        case 'resultats':
        default:
            fputcsv($out, ['CNE','Nom','Prénom','Filière','Sem.','Moy. Normale','Moy. Ratt.','Absences','Statut'], ';');
            $resultats = getResultatsEtudiants($anneeId, $filiereId, $semestre);
            foreach ($resultats as $r) {
                $moy = $r['moy_ratt'] ?? $r['moy_normale'];
                $statut = $moy >= 10 ? 'Admis' : ($r['moy_ratt'] >= 10 ? 'Admis Ratt.' : 'Ajourné');
                fputcsv($out, [
                    $r['cne'],$r['nom'],$r['prenom'],$r['filiere'],
                    'S'.$r['semestre'],$r['moy_normale']??'',$r['moy_ratt']??'',
                    $r['nb_absences'],$statut
                ], ';');
            }
            break;
    }
    fclose($out);
    exit;
}

// page d'export (interface)
$pageTitle = 'Export des rapports';
$annees   = getAnnees();
$filieres = getFilieres();
$anneeEnCours = getAnneeEnCours();
if (!$anneeId) $anneeId = $anneeEnCours['id'] ?? 0;

include __DIR__ . '/../includes/header.php';
?>
<div class="mb-3">
  <h5 class="fw-bold"><i class="bi bi-file-earmark-arrow-down-fill me-2 text-success"></i>Export des rapports statistiques</h5>
</div>

<div class="row g-3">
  <?php
  $exports = [
    ['type'=>'resultats',  'icon'=>'card-checklist',   'color'=>'primary',
     'titre'=>'Résultats des étudiants',
     'desc' =>'Moyennes, rattrapage, absences et statut d\'admission par étudiant.'],
    ['type'=>'etudiants',  'icon'=>'people-fill',       'color'=>'info',
     'titre'=>'Liste des étudiants',
     'desc' =>'CNE, noms, filière, semestre et email de tous les étudiants inscrits.'],
    ['type'=>'absences',   'icon'=>'calendar-x-fill',   'color'=>'warning',
     'titre'=>'Journal des absences',
     'desc' =>'Toutes les absences avec module, date et statut (justifiée ou non).'],
  ];
  foreach ($exports as $ex): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-3">
          <div class="bg-<?= $ex['color'] ?> bg-opacity-10 rounded-circle p-3">
            <i class="bi bi-<?= $ex['icon'] ?> fs-4 text-<?= $ex['color'] ?>"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1"><?= $ex['titre'] ?></h6>
            <p class="text-muted small mb-0"><?= $ex['desc'] ?></p>
          </div>
        </div>

        <form method="GET" action="export.php" class="row g-2">
          <input type="hidden" name="type" value="<?= $ex['type'] ?>">
          <input type="hidden" name="format" value="csv">
          <div class="col-12">
            <select name="annee" class="form-select form-select-sm">
              <?php foreach ($annees as $a): ?>
              <option value="<?= $a['id'] ?>" <?= $a['id'] == $anneeId ? 'selected' : '' ?>>
                <?= h($a['libelle']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-8">
            <select name="filiere" class="form-select form-select-sm">
              <option value="">Toutes filières</option>
              <?php foreach ($filieres as $f): ?>
              <option value="<?= $f['id'] ?>"><?= h($f['code']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if ($ex['type'] === 'resultats'): ?>
          <div class="col-4">
            <select name="semestre" class="form-select form-select-sm">
              <option value="">Tous</option>
              <option value="1">S1</option>
              <option value="2">S2</option>
            </select>
          </div>
          <?php endif; ?>
          <div class="col-12 mt-1">
            <button type="submit" class="btn btn-<?= $ex['color'] ?> btn-sm w-100">
              <i class="bi bi-download me-1"></i>Télécharger CSV
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card mt-4 border-0 shadow-sm border-start border-4 border-info">
  <div class="card-body">
    <h6 class="fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Format d'export</h6>
    <p class="mb-0 text-muted small">
      Les fichiers exportés sont au format <strong>CSV (séparateur point-virgule)</strong>,
      encodé <strong>UTF-8 avec BOM</strong> pour une ouverture correcte sous Microsoft Excel.
      Chaque export est nommé automatiquement avec la date et l'heure de génération.
    </p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
