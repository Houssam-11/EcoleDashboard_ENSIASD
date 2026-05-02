<?php
/*includes/fonctions.php — Fonctions utilitaires partagées */
require_once __DIR__ . '/../config.php';

/* retourne les filières actives*/
function getFilieres(): array {
    $stmt = getDB()->query("SELECT * FROM filieres WHERE actif = 1 ORDER BY code");
    return $stmt->fetchAll();
}

/*retourne les années académiques*/
function getAnnees(): array {
    $stmt = getDB()->query("SELECT * FROM annees_academiques ORDER BY libelle DESC");
    return $stmt->fetchAll();
}

/* retourne l'année académique en cours */
function getAnneeEnCours(): array|false {
    $stmt = getDB()->query("SELECT * FROM annees_academiques WHERE en_cours = 1 LIMIT 1");
    return $stmt->fetch();
}

/* indicateurs clés pour le dashboard */
function getKPIs(int $anneeId, ?int $filiereId = null, ?int $semestre = null): array {
    $db = getDB();

    // nombre total d'étudiants
    $sql = "SELECT COUNT(*) FROM etudiants WHERE annee_id = :annee";
    $params = [':annee' => $anneeId];
    if ($filiereId) { $sql .= " AND filiere_id = :filiere"; $params[':filiere'] = $filiereId; }
    if ($semestre)  { $sql .= " AND semestre = :sem";     $params[':sem']     = $semestre; }
    $nbEtudiants = (int)$db->prepare($sql)->execute($params) ? $db->prepare($sql) : null;
    $stmtEt = $db->prepare($sql); $stmtEt->execute($params);
    $nbEtudiants = (int)$stmtEt->fetchColumn();

    // nombre d'enseignants actifs
    $nbEnseignants = (int)$db->query("SELECT COUNT(*) FROM enseignants WHERE actif = 1")->fetchColumn();

    // taux d'absences (absences non justifiées / total présences théoriques)
    $sqlAbs = "SELECT COUNT(*) FROM absences a
               JOIN etudiants e ON a.etudiant_id = e.id
               WHERE a.annee_id = :annee AND a.justifiee = 0";
    $pAbs = [':annee' => $anneeId];
    if ($filiereId) { $sqlAbs .= " AND e.filiere_id = :filiere"; $pAbs[':filiere'] = $filiereId; }
    $stmtAbs = $db->prepare($sqlAbs); $stmtAbs->execute($pAbs);
    $nbAbsences = (int)$stmtAbs->fetchColumn();
    $tauxAbsences = $nbEtudiants > 0 ? round(($nbAbsences / ($nbEtudiants * 20)) * 100, 1) : 0;

    // moyenne générale (session normale)
    $sqlMoy = "SELECT AVG(n.note_finale) FROM notes n
               JOIN etudiants e ON n.etudiant_id = e.id
               WHERE n.annee_id = :annee AND n.session = 'normale' AND n.note_finale IS NOT NULL";
    $pMoy = [':annee' => $anneeId];
    if ($filiereId) { $sqlMoy .= " AND e.filiere_id = :filiere"; $pMoy[':filiere'] = $filiereId; }
    $stmtMoy = $db->prepare($sqlMoy); $stmtMoy->execute($pMoy);
    $moyenneGenerale = round((float)$stmtMoy->fetchColumn(), 2);

    // taux d'admission (note_finale >= 10 en session normale ou rattrapage)
    $sqlAdmis = "SELECT COUNT(DISTINCT e.id) FROM etudiants e
                 WHERE e.annee_id = :annee
                 AND (
                   SELECT AVG(n2.note_finale)
                   FROM notes n2
                   WHERE n2.etudiant_id = e.id AND n2.annee_id = :annee2
                   AND n2.session IN ('normale','rattrapage')
                 ) >= 10";
    $pAdmis = [':annee' => $anneeId, ':annee2' => $anneeId];
    if ($filiereId) { $sqlAdmis .= " AND e.filiere_id = :filiere"; $pAdmis[':filiere'] = $filiereId; }
    $stmtAdmis = $db->prepare($sqlAdmis); $stmtAdmis->execute($pAdmis);
    $nbAdmis = (int)$stmtAdmis->fetchColumn();
    $tauxAdmission = $nbEtudiants > 0 ? round(($nbAdmis / $nbEtudiants) * 100, 1) : 0;

    // nombre d'étudiants en rattrapage
    $sqlRatt = "SELECT COUNT(DISTINCT etudiant_id) FROM notes
                WHERE annee_id = :annee AND session = 'rattrapage'";
    $pRatt = [':annee' => $anneeId];
    $stmtRatt = $db->prepare($sqlRatt); $stmtRatt->execute($pRatt);
    $nbRattrapage = (int)$stmtRatt->fetchColumn();

    return compact('nbEtudiants','nbEnseignants','tauxAbsences',
                   'moyenneGenerale','tauxAdmission','nbAdmis','nbRattrapage');
}

/* distribution des moyennes par tranche */
function getDistributionMoyennes(int $anneeId, ?int $filiereId = null): array {
    $db = getDB();
    $sql = "SELECT e.id,
                   AVG(n.note_finale) as moy
            FROM etudiants e
            JOIN notes n ON n.etudiant_id = e.id AND n.annee_id = :annee AND n.session = 'normale'
            WHERE e.annee_id = :annee2";
    $p = [':annee' => $anneeId, ':annee2' => $anneeId];
    if ($filiereId) { $sql .= " AND e.filiere_id = :f"; $p[':f'] = $filiereId; }
    $sql .= " GROUP BY e.id";
    $stmt = $db->prepare($sql); $stmt->execute($p);
    $rows = $stmt->fetchAll();

    $tranches = ['0-5' => 0, '5-8' => 0, '8-10' => 0, '10-12' => 0, '12-14' => 0, '14-16' => 0, '16-20' => 0];
    foreach ($rows as $r) {
        $m = (float)$r['moy'];
        if ($m < 5)       $tranches['0-5']++;
        elseif ($m < 8)   $tranches['5-8']++;
        elseif ($m < 10)  $tranches['8-10']++;
        elseif ($m < 12)  $tranches['10-12']++;
        elseif ($m < 14)  $tranches['12-14']++;
        elseif ($m < 16)  $tranches['14-16']++;
        else              $tranches['16-20']++;
    }
    return $tranches;
}

/* moyennes par filière */
function getMoyennesParFiliere(int $anneeId): array {
    $stmt = getDB()->prepare("
        SELECT f.intitule, ROUND(AVG(n.note_finale),2) as moy
        FROM filieres f
        JOIN etudiants e ON e.filiere_id = f.id AND e.annee_id = :annee
        JOIN notes n ON n.etudiant_id = e.id AND n.annee_id = :annee2 AND n.session = 'normale'
        WHERE f.actif = 1
        GROUP BY f.id, f.intitule
        ORDER BY moy DESC
    ");
    $stmt->execute([':annee' => $anneeId, ':annee2' => $anneeId]);
    return $stmt->fetchAll();
}

/* absences par mois pour graphique */
function getAbsencesParMois(int $anneeId, ?int $filiereId = null): array {
    $sql = "SELECT MONTH(a.date_absence) as mois, COUNT(*) as nb
            FROM absences a
            JOIN etudiants e ON a.etudiant_id = e.id
            WHERE a.annee_id = :annee";
    $p = [':annee' => $anneeId];
    if ($filiereId) { $sql .= " AND e.filiere_id = :f"; $p[':f'] = $filiereId; }
    $sql .= " GROUP BY mois ORDER BY mois";
    $stmt = getDB()->prepare($sql); $stmt->execute($p);
    $moisNoms = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
    $result = [];
    foreach ($stmt->fetchAll() as $r) {
        $result[$moisNoms[(int)$r['mois']]] = (int)$r['nb'];
    }
    return $result;
}

/* résultats détaillés des étudiants */
function getResultatsEtudiants(int $anneeId, ?int $filiereId = null, ?int $semestre = null): array {
    $sql = "SELECT e.id, e.cne, e.nom, e.prenom, f.code as filiere,
                   e.semestre,
                   ROUND(AVG(CASE WHEN n.session='normale' THEN n.note_finale END),2) as moy_normale,
                   ROUND(AVG(CASE WHEN n.session='rattrapage' THEN n.note_finale END),2) as moy_ratt,
                   COUNT(CASE WHEN n.session='rattrapage' THEN 1 END) as nb_ratt,
                   COUNT(CASE WHEN a.justifiee=0 THEN 1 END) as nb_absences
            FROM etudiants e
            JOIN filieres f ON e.filiere_id = f.id
            LEFT JOIN notes n ON n.etudiant_id = e.id AND n.annee_id = :annee
            LEFT JOIN absences a ON a.etudiant_id = e.id AND a.annee_id = :annee2
            WHERE e.annee_id = :annee3";
    $p = [':annee' => $anneeId, ':annee2' => $anneeId, ':annee3' => $anneeId];
    if ($filiereId) { $sql .= " AND e.filiere_id = :f";   $p[':f']   = $filiereId; }
    if ($semestre)  { $sql .= " AND e.semestre = :sem";   $p[':sem'] = $semestre; }
    $sql .= " GROUP BY e.id, e.cne, e.nom, e.prenom, f.code, e.semestre
              ORDER BY e.nom, e.prenom";
    $stmt = getDB()->prepare($sql); $stmt->execute($p);
    return $stmt->fetchAll();
}
