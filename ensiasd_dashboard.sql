-- ============================================================
--  Dashboard Statistique École — ENSIASD
--  Fichier : ensiasd_dashboard.sql
--  Filières : MGSI | GL | SDBDIA | SITCN
--  Export   : Structure + Données enrichies (prêt à importer)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `if0_41814251_dashboard_ecole`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `if0_41814251_dashboard_ecole`;

-- ============================================================
-- TABLE : utilisateurs
-- ============================================================
DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE `utilisateurs` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `login`        VARCHAR(50)  NOT NULL UNIQUE,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `nom`          VARCHAR(100) NOT NULL,
  `prenom`       VARCHAR(100) NOT NULL,
  `role`         ENUM('admin','enseignant','secretaire') NOT NULL DEFAULT 'secretaire',
  `actif`        TINYINT(1) NOT NULL DEFAULT 1,
  `cree_le`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mot de passe par défaut pour tous : ENSIASD2026
INSERT INTO `utilisateurs` (`login`, `mot_de_passe`, `nom`, `prenom`, `role`) VALUES
('ENSIASD',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'Système',   'admin'),
('secretaire1',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BENALI',         'Fatima',    'secretaire'),
('secretaire2',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'RAHIMI',         'Sanae',     'secretaire');

-- ============================================================
-- TABLE : filieres
-- ============================================================
DROP TABLE IF EXISTS `filieres`;
CREATE TABLE `filieres` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code`        VARCHAR(20)  NOT NULL UNIQUE,
  `intitule`    VARCHAR(200) NOT NULL,
  `responsable` VARCHAR(150),
  `actif`       TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- id=1 MGSI | id=2 GL | id=3 SDBDIA | id=4 SITCN
INSERT INTO `filieres` (`code`, `intitule`, `responsable`) VALUES
('MGSI',   'Management et Gouvernance des Systèmes d\'Information', 'Dr. Khalid OURZAG'),
('GL',     'Ingénierie Logicielle',                                 'Dr. Nadia TAZI'),
('SDBDIA', 'Sciences des Données, Big Data & IA',                   'Dr. Karima ELHAJJI'),
('SITCN',  'Sécurité IT et Confiance Numérique',                    'Dr. Hamid ZAHIR');

-- ============================================================
-- TABLE : annees_academiques
-- ============================================================
DROP TABLE IF EXISTS `annees_academiques`;
CREATE TABLE `annees_academiques` (
  `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `libelle`  VARCHAR(20) NOT NULL UNIQUE,
  `en_cours` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- id=1 → 2022-2023 | id=2 → 2023-2024 | id=3 → 2024-2025 | id=4 → 2025-2026
INSERT INTO `annees_academiques` (`libelle`, `en_cours`) VALUES
('2022-2023', 0),
('2023-2024', 0),
('2024-2025', 0),
('2025-2026', 1);

-- ============================================================
-- TABLE : enseignants
-- ============================================================
DROP TABLE IF EXISTS `enseignants`;
CREATE TABLE `enseignants` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `matricule` VARCHAR(20)  NOT NULL UNIQUE,
  `nom`       VARCHAR(100) NOT NULL,
  `prenom`    VARCHAR(100) NOT NULL,
  `grade`     ENUM('PES','PA','PH','Vacataire') NOT NULL DEFAULT 'PA',
  `email`     VARCHAR(150),
  `actif`     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ENS001..ENS016
INSERT INTO `enseignants` (`matricule`, `nom`, `prenom`, `grade`, `email`) VALUES
('ENS001', 'LAASSEM',    'Brahim',    'PES',       'b.laassem@ensiasd.ma'),
('ENS002', 'OURZAG',     'Khalid',    'PES',       'k.ourzag@ensiasd.ma'),
('ENS003', 'TAZI',       'Nadia',     'PH',        'n.tazi@ensiasd.ma'),
('ENS004', 'ELHAJJI',    'Karima',    'PES',       'k.elhajji@ensiasd.ma'),
('ENS005', 'ZAHIR',      'Hamid',     'PH',        'h.zahir@ensiasd.ma'),
('ENS006', 'CHERKAOUI',  'Driss',     'PA',        'd.cherkaoui@ensiasd.ma'),
('ENS007', 'ALAMI',      'Mohamed',   'Vacataire', 'm.alami@ensiasd.ma'),
('ENS008', 'BENALI',     'Aicha',     'Vacataire', 'a.benali@ensiasd.ma'),
('ENS009', 'FILALI',     'Souad',     'PA',        's.filali@ensiasd.ma'),
('ENS010', 'IDRISSI',    'Yassir',    'PH',        'y.idrissi@ensiasd.ma'),
('ENS011', 'MOUSSAOUI',  'Sara',      'PA',        's.moussaoui@ensiasd.ma'),
('ENS012', 'BENNIS',     'Youssef',   'PH',        'y.bennis@ensiasd.ma'),
('ENS013', 'RADI',       'Khalil',    'PA',        'k.radi@ensiasd.ma'),
('ENS014', 'KETTANI',    'Hind',      'PES',       'h.kettani@ensiasd.ma'),
('ENS015', 'SOUSSI',     'Amine',     'Vacataire', 'a.soussi@ensiasd.ma'),
('ENS016', 'BERRADA',    'Loubna',    'PA',        'l.berrada@ensiasd.ma');

-- ============================================================
-- TABLE : modules
-- ============================================================
DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code`          VARCHAR(30)  NOT NULL UNIQUE,
  `intitule`      VARCHAR(200) NOT NULL,
  `coefficient`   DECIMAL(4,2) NOT NULL DEFAULT 1.00,
  `filiere_id`    INT UNSIGNED NOT NULL,
  `semestre`      TINYINT(1)   NOT NULL DEFAULT 1,
  `enseignant_id` INT UNSIGNED,
  FOREIGN KEY (`filiere_id`)    REFERENCES `filieres`(`id`),
  FOREIGN KEY (`enseignant_id`) REFERENCES `enseignants`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `modules` (`code`, `intitule`, `coefficient`, `filiere_id`, `semestre`, `enseignant_id`) VALUES
-- ─────────────── MGSI — filiere_id=1 ───────────────
-- S1
('MGSI-S1-INFO',   'Informatique Fondamentale',                    2.00, 1, 1,  1),
('MGSI-S1-MATHS',  'Mathématiques Appliquées',                     2.50, 1, 1,  2),
('MGSI-S1-WEB',    'Développement Web',                            2.00, 1, 1,  7),
('MGSI-S1-BDD',    'Bases de Données',                             2.00, 1, 1,  6),
('MGSI-S1-ALGO',   'Algorithmique et Structures de Données',       1.50, 1, 1,  3),
('MGSI-S1-COMPTA', 'Comptabilité & Contrôle de Gestion',           1.50, 1, 1,  8),
-- S2
('MGSI-S2-POO',    'Programmation Orientée Objet',                 2.00, 1, 2,  1),
('MGSI-S2-RESEAU', 'Réseaux Informatiques',                        2.00, 1, 2, 12),
('MGSI-S2-SI',     'Systèmes d\'Information',                      2.50, 1, 2,  2),
('MGSI-S2-ERP',    'ERP & Progiciels de Gestion',                  2.00, 1, 2,  9),
('MGSI-S2-DROIT',  'Droit Numérique & RGPD',                       1.50, 1, 2,  5),

-- ─────────────── GL — filiere_id=2 ───────────────
-- S1
('GL-S1-ALGO',     'Algorithmique Avancée',                        2.50, 2, 1,  3),
('GL-S1-UML',      'Modélisation UML & Patterns',                  2.00, 2, 1,  6),
('GL-S1-JAVA',     'Développement Java/Spring',                    2.00, 2, 1, 11),
('GL-S1-TEST',     'Tests et Qualité Logicielle',                  1.50, 2, 1, 10),
('GL-S1-GIT',      'DevOps & Gestion de Version',                  1.50, 2, 1, 15),
-- S2
('GL-S2-ARCHI',    'Architecture Logicielle & Microservices',      2.50, 2, 2,  1),
('GL-S2-MOBILE',   'Développement Mobile (Android/Flutter)',        2.00, 2, 2,  7),
('GL-S2-API',      'APIs REST & GraphQL',                          2.00, 2, 2, 11),
('GL-S2-AGILE',    'Gestion de Projet Agile (Scrum/Kanban)',        1.50, 2, 2,  9),
('GL-S2-SECU',     'Sécurité des Applications',                    1.50, 2, 2,  5),

-- ─────────────── SDBDIA — filiere_id=3 ───────────────
-- S1
('SDB-S1-STATS',   'Statistiques & Probabilités',                  2.50, 3, 1,  4),
('SDB-S1-PYTHON',  'Python pour la Data Science',                  2.00, 3, 1, 13),
('SDB-S1-SQL',     'SQL Avancé & Entrepôts de Données',            2.00, 3, 1,  6),
('SDB-S1-HADOOP',  'Big Data : Hadoop & Spark',                    2.00, 3, 1, 16),
('SDB-S1-VISU',    'Visualisation de Données',                     1.50, 3, 1,  7),
-- S2
('SDB-S2-ML',      'Machine Learning',                             2.50, 3, 2,  4),
('SDB-S2-DL',      'Deep Learning & Réseaux de Neurones',          2.50, 3, 2, 13),
('SDB-S2-NLP',     'Traitement du Langage Naturel (NLP)',          2.00, 3, 2, 14),
('SDB-S2-CLOUD',   'Cloud Computing & DataOps',                    2.00, 3, 2, 16),
('SDB-S2-ETHIA',   'Éthique de l\'IA & Biais Algorithmiques',      1.50, 3, 2,  5),

-- ─────────────── SITCN — filiere_id=4 ───────────────
-- S1
('SIT-S1-CRYPTO',  'Cryptographie & Théorie des Codes',            2.50, 4, 1,  5),
('SIT-S1-RESEAU',  'Réseaux & Protocoles de Sécurité',             2.00, 4, 1, 12),
('SIT-S1-OS',      'Sécurité des Systèmes d\'Exploitation',        2.00, 4, 1, 10),
('SIT-S1-WEB',     'Sécurité Web (OWASP & Pentesting)',            2.00, 4, 1, 15),
('SIT-S1-DROIT',   'Droit de la Cybersécurité & Conformité',       1.50, 4, 1,  8),
-- S2
('SIT-S2-FORENSIC','Forensique Numérique & Analyse des Incidents',  2.50, 4, 2,  5),
('SIT-S2-SOC',     'SOC, SIEM & Détection des Menaces',            2.50, 4, 2, 16),
('SIT-S2-CLOUD',   'Sécurité Cloud & Zero Trust',                  2.00, 4, 2, 13),
('SIT-S2-IOT',     'Sécurité IoT & Systèmes Embarqués',            2.00, 4, 2, 10),
('SIT-S2-AUDIT',   'Audit & Gouvernance de la Sécurité (ISO27001)',  1.50, 4, 2,  2);

-- ============================================================
-- TABLE : etudiants
-- ============================================================
DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE `etudiants` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cne`            VARCHAR(20)  NOT NULL UNIQUE,
  `nom`            VARCHAR(100) NOT NULL,
  `prenom`         VARCHAR(100) NOT NULL,
  `date_naissance` DATE,
  `email`          VARCHAR(150),
  `filiere_id`     INT UNSIGNED NOT NULL,
  `annee_id`       INT UNSIGNED NOT NULL,
  `semestre`       TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1 ou 2',
  `inscrit_le`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`filiere_id`) REFERENCES `filieres`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`annee_id`)   REFERENCES `annees_academiques`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `etudiants` (`cne`, `nom`, `prenom`, `date_naissance`, `email`, `filiere_id`, `annee_id`, `semestre`) VALUES
-- ══════════════════════════════════════════════
-- MGSI (filiere_id=1) — Année courante 2025-2026 (annee_id=4)
-- ══════════════════════════════════════════════
-- S1
('M001', 'BAKZIZ',      'Yahia',       '2003-04-12', 'yahia.bakziz@etud.ensiasd.ma',       1, 4, 1),
('M002', 'ELBERROUHI',  'Houssam',     '2003-07-22', 'houssam.elberrouhi@etud.ensiasd.ma', 1, 4, 1),
('M003', 'AMRANI',      'Sara',        '2002-11-05', 'sara.amrani@etud.ensiasd.ma',         1, 4, 1),
('M004', 'FILALI',      'Imane',       '2003-06-18', 'imane.filali@etud.ensiasd.ma',        1, 4, 1),
('M005', 'BOUAZZA',     'Rachid',      '2002-09-25', 'rachid.bouazza@etud.ensiasd.ma',      1, 4, 1),
('M006', 'RAMI',        'Chaima',      '2003-03-14', 'chaima.rami@etud.ensiasd.ma',         1, 4, 1),
('M007', 'OUAHABI',     'Youssef',     '2002-12-01', 'youssef.ouahabi@etud.ensiasd.ma',     1, 4, 1),
('M008', 'LAHLOU',      'Kaouthar',    '2003-05-29', 'kaouthar.lahlou@etud.ensiasd.ma',     1, 4, 1),
('M009', 'BENKIRANE',   'Othmane',     '2002-08-17', 'othmane.benkirane@etud.ensiasd.ma',   1, 4, 1),
('M010', 'HAJJI',       'Zineb',       '2003-01-08', 'zineb.hajji@etud.ensiasd.ma',         1, 4, 1),
-- S2
('M011', 'TAZI',        'Karim',       '2003-01-30', 'karim.tazi@etud.ensiasd.ma',          1, 4, 2),
('M012', 'OUALI',       'Nadia',       '2002-08-14', 'nadia.ouali@etud.ensiasd.ma',         1, 4, 2),
('M013', 'RHAZALI',     'Dounia',      '2003-02-11', 'dounia.rhazali@etud.ensiasd.ma',      1, 4, 2),
('M014', 'BERRADA',     'Saad',        '2002-07-03', 'saad.berrada@etud.ensiasd.ma',        1, 4, 2),
('M015', 'KABBAJ',      'Hiba',        '2003-10-22', 'hiba.kabbaj@etud.ensiasd.ma',         1, 4, 2),
('M016', 'MANSOURI',    'Ilyas',       '2002-04-05', 'ilyas.mansouri@etud.ensiasd.ma',      1, 4, 2),
-- ══════════════════════════════════════════════
-- GL (filiere_id=2) — Année courante 2025-2026 (annee_id=4)
-- ══════════════════════════════════════════════
-- S1
('G001', 'CHERKAOUI',   'Hind',        '2002-12-03', 'hind.cherkaoui@etud.ensiasd.ma',      2, 4, 1),
('G002', 'BENSOUDA',    'Asmaa',       '2003-01-07', 'asmaa.bensouda@etud.ensiasd.ma',      2, 4, 1),
('G003', 'MOUSSAOUI',   'Amine',       '2003-03-17', 'amine.moussaoui@etud.ensiasd.ma',     2, 4, 1),
('G004', 'SOUSSI',      'Yassine',     '2003-05-03', 'yassine.soussi@etud.ensiasd.ma',      2, 4, 1),
('G005', 'OUBELLA',     'Hajar',       '2002-12-20', 'hajar.oubella@etud.ensiasd.ma',       2, 4, 1),
('G006', 'BENNIS',      'Leila',       '2002-09-09', 'leila.bennis@etud.ensiasd.ma',        2, 4, 1),
('G007', 'OUCHANE',     'Mehdi',       '2003-07-15', 'mehdi.ouchane@etud.ensiasd.ma',       2, 4, 1),
('G008', 'GHANNAM',     'Rania',       '2002-11-28', 'rania.ghannam@etud.ensiasd.ma',       2, 4, 1),
('G009', 'ZOUBEIR',     'Adam',        '2003-02-19', 'adam.zoubeir@etud.ensiasd.ma',        2, 4, 1),
('G010', 'LAAZIZI',     'Fatima-Ezzahra','2002-06-30','fe.laazizi@etud.ensiasd.ma',         2, 4, 1),
-- S2
('G011', 'IDRISSI',     'Salma',       '2003-07-09', 'salma.idrissi@etud.ensiasd.ma',       2, 4, 2),
('G012', 'KADIRI',      'Meryem',      '2002-01-15', 'meryem.kadiri@etud.ensiasd.ma',       2, 4, 2),
('G013', 'ZIANI',       'Omar',        '2003-02-28', 'omar.ziani@etud.ensiasd.ma',          2, 4, 2),
('G014', 'HAKIMI',      'Anas',        '2002-05-17', 'anas.hakimi@etud.ensiasd.ma',         2, 4, 2),
('G015', 'KETTANI',     'Samira',      '2003-09-11', 'samira.kettani@etud.ensiasd.ma',      2, 4, 2),
-- ══════════════════════════════════════════════
-- SDBDIA (filiere_id=3) — Année courante 2025-2026 (annee_id=4)
-- ══════════════════════════════════════════════
-- S1
('S001', 'ELBAZ',       'Younes',      '2002-10-14', 'younes.elbaz@etud.ensiasd.ma',        3, 4, 1),
('S002', 'NACIRI',      'Chaimaa',     '2003-04-27', 'chaimaa.naciri@etud.ensiasd.ma',      3, 4, 1),
('S003', 'ZAHRANI',     'Soufiane',    '2002-07-08', 'soufiane.zahrani@etud.ensiasd.ma',    3, 4, 1),
('S004', 'BENHAMMOU',   'Amira',       '2003-11-30', 'amira.benhammou@etud.ensiasd.ma',     3, 4, 1),
('S005', 'OUCHEN',      'Tarik',       '2002-03-22', 'tarik.ouchen@etud.ensiasd.ma',        3, 4, 1),
('S006', 'ELASRI',      'Ghita',       '2003-06-05', 'ghita.elasri@etud.ensiasd.ma',        3, 4, 1),
('S007', 'LAABIDI',     'Ayoub',       '2002-09-16', 'ayoub.laabidi@etud.ensiasd.ma',       3, 4, 1),
('S008', 'BENCHEKROUN', 'Kenza',       '2003-12-09', 'kenza.benchekroun@etud.ensiasd.ma',   3, 4, 1),
-- S2
('S009', 'RADI',        'Ilyas',       '2001-10-25', 'ilyas.radi@etud.ensiasd.ma',          3, 4, 2),
('S010', 'HAKIMI',      'Fatima',      '2002-06-11', 'fatima.hakimi@etud.ensiasd.ma',       3, 4, 2),
('S011', 'ERRACHIDI',   'Houda',       '2002-02-14', 'houda.errachidi@etud.ensiasd.ma',     3, 4, 2),
('S012', 'BENOMAR',     'Walid',       '2003-08-20', 'walid.benomar@etud.ensiasd.ma',       3, 4, 2),
-- ══════════════════════════════════════════════
-- SITCN (filiere_id=4) — Année courante 2025-2026 (annee_id=4)
-- ══════════════════════════════════════════════
-- S1
('C001', 'ALAOUI',      'Hamza',       '2002-05-03', 'hamza.alaoui@etud.ensiasd.ma',        4, 4, 1),
('C002', 'BENCHRIF',    'Safaa',       '2003-08-24', 'safaa.benchrif@etud.ensiasd.ma',      4, 4, 1),
('C003', 'SEKKAT',      'Noureddine',  '2002-01-11', 'noureddine.sekkat@etud.ensiasd.ma',   4, 4, 1),
('C004', 'BAKKALI',     'Loubna',      '2003-03-06', 'loubna.bakkali@etud.ensiasd.ma',      4, 4, 1),
('C005', 'TABBAA',      'Yassir',      '2002-11-19', 'yassir.tabbaa@etud.ensiasd.ma',       4, 4, 1),
('C006', 'OUAZZANI',    'Rim',         '2003-07-31', 'rim.ouazzani@etud.ensiasd.ma',        4, 4, 1),
('C007', 'ELGHARBI',    'Karim',       '2002-04-23', 'karim.elgharbi@etud.ensiasd.ma',      4, 4, 1),
('C008', 'MANSOURI',    'Zakaria',     '2003-09-14', 'zakaria.mansouri@etud.ensiasd.ma',    4, 4, 1),
-- S2
('C009', 'BENKIRANE',   'Ismail',      '2001-12-07', 'ismail.benkirane@etud.ensiasd.ma',    4, 4, 2),
('C010', 'DRISSI',      'Maroua',      '2002-07-25', 'maroua.drissi@etud.ensiasd.ma',       4, 4, 2),
('C011', 'CHORFI',      'Badr',        '2003-05-18', 'badr.chorfi@etud.ensiasd.ma',         4, 4, 2),
('C012', 'ELMOURABIT',  'Jihad',       '2002-10-02', 'jihad.elmourabit@etud.ensiasd.ma',    4, 4, 2),
-- ══════════════════════════════════════════════
-- ANCIENS 2024-2025 (annee_id=3)
-- ══════════════════════════════════════════════
('A001', 'ZAKI',        'Amine',       '2001-04-15', 'amine.zaki@etud.ensiasd.ma',          1, 3, 1),
('A002', 'BENALI',      'Houria',      '2001-09-22', 'houria.benali@etud.ensiasd.ma',       1, 3, 2),
('A003', 'ROUISSI',     'Khalid',      '2001-06-10', 'khalid.rouissi@etud.ensiasd.ma',      2, 3, 1),
('A004', 'LACHKAR',     'Samia',       '2001-11-03', 'samia.lachkar@etud.ensiasd.ma',       3, 3, 2),
('A005', 'KARIMI',      'Abdelaziz',   '2001-02-28', 'aziz.karimi@etud.ensiasd.ma',         4, 3, 1),
-- ANCIENS 2023-2024 (annee_id=2)
('B001', 'OUMAROU',     'Siham',       '2000-05-19', 'siham.oumarou@etud.ensiasd.ma',       1, 2, 1),
('B002', 'TAHIRI',      'Reda',        '2000-12-08', 'reda.tahiri@etud.ensiasd.ma',         2, 2, 2);

-- ============================================================
-- TABLE : notes
-- ============================================================
DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `etudiant_id` INT UNSIGNED NOT NULL,
  `module_id`   INT UNSIGNED NOT NULL,
  `note_cc`     DECIMAL(5,2) COMMENT 'Contrôle continu',
  `note_examen` DECIMAL(5,2) COMMENT 'Examen final',
  `note_finale` DECIMAL(5,2) GENERATED ALWAYS AS (
                  COALESCE((`note_cc` * 0.40 + `note_examen` * 0.60), `note_examen`, `note_cc`)
                ) STORED,
  `session`     ENUM('normale','rattrapage') NOT NULL DEFAULT 'normale',
  `annee_id`    INT UNSIGNED NOT NULL,
  UNIQUE KEY `uk_note` (`etudiant_id`, `module_id`, `session`, `annee_id`),
  FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants`(`id`),
  FOREIGN KEY (`module_id`)   REFERENCES `modules`(`id`),
  FOREIGN KEY (`annee_id`)    REFERENCES `annees_academiques`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notes` (`etudiant_id`, `module_id`, `note_cc`, `note_examen`, `session`, `annee_id`) VALUES
-- ═══════ MGSI S1 — étudiants id 1..10 ═══════
-- M001 BAKZIZ — modules MGSI-S1 (id 1..6)
(1,  1, 14.00, 15.50, 'normale', 4),
(1,  2, 12.00, 10.50, 'normale', 4),
(1,  3, 18.00, 17.00, 'normale', 4),
(1,  4, 13.00, 14.00, 'normale', 4),
(1,  5,  9.00,  8.50, 'normale', 4),
(1,  6, 11.00, 12.00, 'normale', 4),
-- M002 ELBERROUHI
(2,  1, 11.00, 12.00, 'normale', 4),
(2,  2,  7.00,  6.00, 'normale', 4),
(2,  3, 15.00, 16.00, 'normale', 4),
(2,  4, 10.00, 11.00, 'normale', 4),
(2,  5,  8.00,  7.50, 'normale', 4),
(2,  6,  9.00,  8.00, 'normale', 4),
-- M003 AMRANI
(3,  1, 16.50, 17.00, 'normale', 4),
(3,  2, 14.00, 15.00, 'normale', 4),
(3,  3, 12.00, 13.00, 'normale', 4),
(3,  4, 11.00, 10.00, 'normale', 4),
(3,  5, 13.00, 14.00, 'normale', 4),
(3,  6, 15.00, 16.00, 'normale', 4),
-- M004 FILALI
(4,  1, 17.00, 18.00, 'normale', 4),
(4,  2, 15.00, 16.00, 'normale', 4),
(4,  3, 19.00, 18.50, 'normale', 4),
(4,  4, 14.00, 15.00, 'normale', 4),
(4,  5, 16.00, 15.50, 'normale', 4),
(4,  6, 14.00, 13.50, 'normale', 4),
-- M005 BOUAZZA
(5,  1, 10.00, 11.00, 'normale', 4),
(5,  2,  8.50,  9.00, 'normale', 4),
(5,  3, 13.00, 12.00, 'normale', 4),
(5,  4,  9.00, 10.00, 'normale', 4),
(5,  5,  7.00,  6.50, 'normale', 4),
(5,  6, 10.00, 11.00, 'normale', 4),
-- M006 RAMI
(6,  1, 13.00, 14.00, 'normale', 4),
(6,  2, 12.50, 13.50, 'normale', 4),
(6,  3, 11.00, 10.00, 'normale', 4),
(6,  4, 14.00, 15.00, 'normale', 4),
(6,  5, 10.00, 11.00, 'normale', 4),
(6,  6, 12.00, 13.00, 'normale', 4),
-- M007 OUAHABI
(7,  1, 15.00, 14.00, 'normale', 4),
(7,  2, 11.00, 12.00, 'normale', 4),
(7,  3, 16.00, 17.00, 'normale', 4),
(7,  4, 13.50, 12.50, 'normale', 4),
(7,  5, 14.00, 15.00, 'normale', 4),
(7,  6, 12.00, 11.00, 'normale', 4),
-- M008 LAHLOU
(8,  1,  9.00,  8.00, 'normale', 4),
(8,  2, 11.00, 12.00, 'normale', 4),
(8,  3, 10.00,  9.50, 'normale', 4),
(8,  4,  7.00,  6.00, 'normale', 4),
(8,  5,  8.00,  9.00, 'normale', 4),
(8,  6, 10.00, 10.50, 'normale', 4),
-- M009 BENKIRANE
(9,  1, 18.00, 18.50, 'normale', 4),
(9,  2, 17.00, 16.00, 'normale', 4),
(9,  3, 19.00, 19.50, 'normale', 4),
(9,  4, 16.00, 17.00, 'normale', 4),
(9,  5, 15.00, 16.00, 'normale', 4),
(9,  6, 18.00, 17.50, 'normale', 4),
-- M010 HAJJI
(10, 1, 12.00, 13.00, 'normale', 4),
(10, 2, 10.00, 11.00, 'normale', 4),
(10, 3, 14.00, 15.00, 'normale', 4),
(10, 4, 11.00, 12.00, 'normale', 4),
(10, 5, 13.00, 12.50, 'normale', 4),
(10, 6, 11.00, 10.50, 'normale', 4),

-- ═══════ MGSI S2 — étudiants id 11..16 ═══════
-- modules MGSI-S2 : id 7=POO, 8=RESEAU, 9=SI, 10=ERP, 11=DROIT
-- M011 TAZI
(11, 7, 10.00, 11.00, 'normale', 4),
(11, 8,  7.00,  6.00, 'normale', 4),
(11, 9, 15.00, 14.00, 'normale', 4),
(11,10, 12.00, 13.00, 'normale', 4),
(11,11, 11.00, 10.00, 'normale', 4),
-- M012 OUALI
(12, 7, 12.00, 13.00, 'normale', 4),
(12, 8, 16.00, 17.00, 'normale', 4),
(12, 9,  9.00,  8.00, 'normale', 4),
(12,10, 14.00, 15.00, 'normale', 4),
(12,11, 13.00, 12.50, 'normale', 4),
-- M013 RHAZALI
(13, 7, 11.00, 12.00, 'normale', 4),
(13, 8, 14.00, 13.00, 'normale', 4),
(13, 9, 10.00, 11.00, 'normale', 4),
(13,10,  8.00,  9.00, 'normale', 4),
(13,11, 15.00, 14.50, 'normale', 4),
-- M014 BERRADA
(14, 7, 16.00, 17.00, 'normale', 4),
(14, 8, 15.00, 14.00, 'normale', 4),
(14, 9, 18.00, 17.50, 'normale', 4),
(14,10, 13.00, 14.00, 'normale', 4),
(14,11, 12.00, 11.50, 'normale', 4),
-- M015 KABBAJ
(15, 7,  9.00, 10.00, 'normale', 4),
(15, 8, 11.00, 10.50, 'normale', 4),
(15, 9, 13.00, 14.00, 'normale', 4),
(15,10, 10.00, 11.00, 'normale', 4),
(15,11,  7.00,  8.00, 'normale', 4),
-- M016 MANSOURI
(16, 7, 14.00, 15.00, 'normale', 4),
(16, 8, 12.00, 13.00, 'normale', 4),
(16, 9, 11.00, 10.00, 'normale', 4),
(16,10, 16.00, 17.00, 'normale', 4),
(16,11, 13.50, 14.00, 'normale', 4),

-- ═══════ GL S1 — étudiants id 17..26 ═══════
-- modules GL-S1 : id 12=ALGO, 13=UML, 14=JAVA, 15=TEST, 16=GIT
-- G001 CHERKAOUI
(17,12, 14.00, 15.00, 'normale', 4),
(17,13, 12.00, 13.00, 'normale', 4),
(17,14, 16.00, 17.00, 'normale', 4),
(17,15, 11.00, 10.00, 'normale', 4),
(17,16, 13.00, 14.00, 'normale', 4),
-- G002 BENSOUDA
(18,12, 16.00, 17.00, 'normale', 4),
(18,13, 15.00, 14.00, 'normale', 4),
(18,14, 18.00, 19.00, 'normale', 4),
(18,15, 13.00, 14.00, 'normale', 4),
(18,16, 15.00, 16.00, 'normale', 4),
-- G003 MOUSSAOUI
(19,12, 10.00, 11.00, 'normale', 4),
(19,13,  9.00,  8.50, 'normale', 4),
(19,14, 12.00, 13.00, 'normale', 4),
(19,15,  7.00,  6.50, 'normale', 4),
(19,16, 11.00, 10.50, 'normale', 4),
-- G004 SOUSSI
(20,12, 17.00, 16.50, 'normale', 4),
(20,13, 18.00, 17.00, 'normale', 4),
(20,14, 15.00, 16.00, 'normale', 4),
(20,15, 14.00, 13.50, 'normale', 4),
(20,16, 16.00, 17.00, 'normale', 4),
-- G005 OUBELLA
(21,12,  9.00,  8.00, 'normale', 4),
(21,13, 11.00, 12.00, 'normale', 4),
(21,14,  7.00,  6.00, 'normale', 4),
(21,15, 10.00, 11.00, 'normale', 4),
(21,16,  8.00,  9.00, 'normale', 4),
-- G006 BENNIS
(22,12, 13.00, 14.00, 'normale', 4),
(22,13, 12.00, 11.00, 'normale', 4),
(22,14, 14.00, 15.00, 'normale', 4),
(22,15, 11.50, 12.00, 'normale', 4),
(22,16, 13.00, 12.50, 'normale', 4),
-- G007 OUCHANE
(23,12, 15.00, 16.00, 'normale', 4),
(23,13, 14.00, 13.50, 'normale', 4),
(23,14, 17.00, 16.00, 'normale', 4),
(23,15, 12.00, 13.00, 'normale', 4),
(23,16, 14.00, 15.00, 'normale', 4),
-- G008 GHANNAM
(24,12, 11.00, 12.00, 'normale', 4),
(24,13, 13.00, 14.00, 'normale', 4),
(24,14, 10.00,  9.50, 'normale', 4),
(24,15,  9.00,  8.00, 'normale', 4),
(24,16, 12.00, 11.50, 'normale', 4),
-- G009 ZOUBEIR
(25,12, 19.00, 18.50, 'normale', 4),
(25,13, 17.00, 18.00, 'normale', 4),
(25,14, 20.00, 19.50, 'normale', 4),
(25,15, 16.00, 17.00, 'normale', 4),
(25,16, 18.00, 19.00, 'normale', 4),
-- G010 LAAZIZI
(26,12, 12.00, 13.00, 'normale', 4),
(26,13, 11.00, 10.00, 'normale', 4),
(26,14, 13.00, 14.00, 'normale', 4),
(26,15, 10.50, 11.50, 'normale', 4),
(26,16, 11.00, 12.00, 'normale', 4),

-- ═══════ GL S2 — étudiants id 27..31 ═══════
-- modules GL-S2 : id 17=ARCHI, 18=MOBILE, 19=API, 20=AGILE, 21=SECU
-- G011 IDRISSI
(27,17, 18.00, 19.00, 'normale', 4),
(27,18, 16.00, 17.00, 'normale', 4),
(27,19, 15.00, 16.00, 'normale', 4),
(27,20, 14.00, 13.50, 'normale', 4),
(27,21, 17.00, 18.00, 'normale', 4),
-- G012 KADIRI
(28,17, 10.00, 11.00, 'normale', 4),
(28,18, 12.00, 13.00, 'normale', 4),
(28,19,  9.00,  8.50, 'normale', 4),
(28,20, 11.00, 10.00, 'normale', 4),
(28,21, 10.50, 11.50, 'normale', 4),
-- G013 ZIANI
(29,17, 14.00, 15.00, 'normale', 4),
(29,18, 13.00, 12.50, 'normale', 4),
(29,19, 15.00, 16.00, 'normale', 4),
(29,20, 12.00, 13.00, 'normale', 4),
(29,21, 14.00, 13.50, 'normale', 4),
-- G014 HAKIMI
(30,17, 16.00, 17.00, 'normale', 4),
(30,18, 18.00, 17.50, 'normale', 4),
(30,19, 14.00, 15.00, 'normale', 4),
(30,20, 15.00, 16.00, 'normale', 4),
(30,21, 13.00, 14.00, 'normale', 4),
-- G015 KETTANI
(31,17,  8.00,  7.50, 'normale', 4),
(31,18,  9.00, 10.00, 'normale', 4),
(31,19, 11.00, 10.50, 'normale', 4),
(31,20,  7.00,  8.00, 'normale', 4),
(31,21,  9.00,  8.50, 'normale', 4),

-- ═══════ SDBDIA S1 — étudiants id 32..39 ═══════
-- modules SDBDIA-S1 : id 22=STATS, 23=PYTHON, 24=SQL, 25=HADOOP, 26=VISU
-- S001 ELBAZ
(32,22, 15.00, 16.00, 'normale', 4),
(32,23, 17.00, 18.00, 'normale', 4),
(32,24, 14.00, 15.00, 'normale', 4),
(32,25, 12.00, 13.00, 'normale', 4),
(32,26, 16.00, 15.50, 'normale', 4),
-- S002 NACIRI
(33,22, 10.00, 11.00, 'normale', 4),
(33,23, 12.00, 13.00, 'normale', 4),
(33,24,  9.00,  8.50, 'normale', 4),
(33,25, 11.00, 10.00, 'normale', 4),
(33,26, 13.00, 14.00, 'normale', 4),
-- S003 ZAHRANI
(34,22, 18.00, 17.50, 'normale', 4),
(34,23, 16.00, 17.00, 'normale', 4),
(34,24, 19.00, 18.00, 'normale', 4),
(34,25, 15.00, 16.00, 'normale', 4),
(34,26, 17.00, 18.00, 'normale', 4),
-- S004 BENHAMMOU
(35,22, 11.00, 12.00, 'normale', 4),
(35,23,  9.00,  8.00, 'normale', 4),
(35,24, 13.00, 14.00, 'normale', 4),
(35,25,  7.00,  6.50, 'normale', 4),
(35,26, 10.00, 11.00, 'normale', 4),
-- S005 OUCHEN
(36,22, 14.00, 15.00, 'normale', 4),
(36,23, 16.00, 15.50, 'normale', 4),
(36,24, 12.00, 13.00, 'normale', 4),
(36,25, 14.00, 15.00, 'normale', 4),
(36,26, 11.00, 12.00, 'normale', 4),
-- S006 ELASRI
(37,22, 13.00, 14.00, 'normale', 4),
(37,23, 15.00, 16.00, 'normale', 4),
(37,24, 11.00, 10.50, 'normale', 4),
(37,25, 12.00, 13.00, 'normale', 4),
(37,26, 14.00, 13.50, 'normale', 4),
-- S007 LAABIDI
(38,22, 17.00, 16.00, 'normale', 4),
(38,23, 19.00, 18.50, 'normale', 4),
(38,24, 15.00, 16.00, 'normale', 4),
(38,25, 18.00, 17.00, 'normale', 4),
(38,26, 16.00, 17.00, 'normale', 4),
-- S008 BENCHEKROUN
(39,22,  8.00,  7.00, 'normale', 4),
(39,23, 10.00, 11.00, 'normale', 4),
(39,24,  9.00,  8.00, 'normale', 4),
(39,25,  6.00,  7.00, 'normale', 4),
(39,26,  8.50,  9.00, 'normale', 4),

-- ═══════ SDBDIA S2 — étudiants id 40..43 ═══════
-- modules SDBDIA-S2 : id 27=ML, 28=DL, 29=NLP, 30=CLOUD, 31=ETHIA
-- S009 RADI
(40,27, 16.00, 17.00, 'normale', 4),
(40,28, 14.00, 15.00, 'normale', 4),
(40,29, 18.00, 17.50, 'normale', 4),
(40,30, 12.00, 13.00, 'normale', 4),
(40,31, 15.00, 16.00, 'normale', 4),
-- S010 HAKIMI
(41,27, 10.00, 11.00, 'normale', 4),
(41,28, 12.00, 13.00, 'normale', 4),
(41,29,  9.00,  8.50, 'normale', 4),
(41,30, 11.00, 10.00, 'normale', 4),
(41,31, 13.00, 14.00, 'normale', 4),
-- S011 ERRACHIDI
(42,27, 19.00, 18.50, 'normale', 4),
(42,28, 17.00, 18.00, 'normale', 4),
(42,29, 20.00, 19.50, 'normale', 4),
(42,30, 16.00, 17.00, 'normale', 4),
(42,31, 18.00, 19.00, 'normale', 4),
-- S012 BENOMAR
(43,27, 13.00, 14.00, 'normale', 4),
(43,28, 11.00, 12.00, 'normale', 4),
(43,29, 15.00, 14.50, 'normale', 4),
(43,30,  9.00, 10.00, 'normale', 4),
(43,31, 12.00, 11.50, 'normale', 4),

-- ═══════ SITCN S1 — étudiants id 44..51 ═══════
-- modules SITCN-S1 : id 32=CRYPTO, 33=RESEAU, 34=OS, 35=WEB, 36=DROIT
-- C001 ALAOUI
(44,32, 15.00, 16.00, 'normale', 4),
(44,33, 14.00, 15.00, 'normale', 4),
(44,34, 17.00, 18.00, 'normale', 4),
(44,35, 13.00, 12.50, 'normale', 4),
(44,36, 12.00, 13.00, 'normale', 4),
-- C002 BENCHRIF
(45,32, 11.00, 12.00, 'normale', 4),
(45,33,  9.00,  8.50, 'normale', 4),
(45,34, 13.00, 14.00, 'normale', 4),
(45,35, 10.00, 11.00, 'normale', 4),
(45,36,  8.00,  9.00, 'normale', 4),
-- C003 SEKKAT
(46,32, 18.00, 17.50, 'normale', 4),
(46,33, 19.00, 18.00, 'normale', 4),
(46,34, 16.00, 17.00, 'normale', 4),
(46,35, 20.00, 19.50, 'normale', 4),
(46,36, 15.00, 16.00, 'normale', 4),
-- C004 BAKKALI
(47,32, 12.00, 13.00, 'normale', 4),
(47,33, 14.00, 15.00, 'normale', 4),
(47,34, 10.00, 11.00, 'normale', 4),
(47,35, 11.00, 10.50, 'normale', 4),
(47,36, 13.00, 14.00, 'normale', 4),
-- C005 TABBAA
(48,32, 16.00, 17.00, 'normale', 4),
(48,33, 15.00, 14.50, 'normale', 4),
(48,34, 14.00, 15.00, 'normale', 4),
(48,35, 17.00, 16.00, 'normale', 4),
(48,36, 14.00, 13.50, 'normale', 4),
-- C006 OUAZZANI
(49,32,  9.00,  8.00, 'normale', 4),
(49,33, 10.00, 11.00, 'normale', 4),
(49,34,  7.00,  6.50, 'normale', 4),
(49,35,  8.00,  9.00, 'normale', 4),
(49,36, 11.00, 12.00, 'normale', 4),
-- C007 ELGHARBI
(50,32, 14.00, 15.00, 'normale', 4),
(50,33, 12.00, 13.00, 'normale', 4),
(50,34, 16.00, 15.50, 'normale', 4),
(50,35, 13.00, 14.00, 'normale', 4),
(50,36, 10.00, 11.00, 'normale', 4),
-- C008 MANSOURI
(51,32, 13.00, 12.00, 'normale', 4),
(51,33, 11.00, 12.00, 'normale', 4),
(51,34, 15.00, 14.00, 'normale', 4),
(51,35, 12.00, 13.00, 'normale', 4),
(51,36,  9.00, 10.00, 'normale', 4),

-- ═══════ SITCN S2 — étudiants id 52..55 ═══════
-- modules SITCN-S2 : id 37=FORENSIC, 38=SOC, 39=CLOUD, 40=IOT, 41=AUDIT
-- C009 BENKIRANE
(52,37, 17.00, 18.00, 'normale', 4),
(52,38, 15.00, 16.00, 'normale', 4),
(52,39, 14.00, 15.00, 'normale', 4),
(52,40, 13.00, 14.00, 'normale', 4),
(52,41, 16.00, 17.00, 'normale', 4),
-- C010 DRISSI
(53,37, 10.00, 11.00, 'normale', 4),
(53,38, 12.00, 13.00, 'normale', 4),
(53,39,  9.00,  8.50, 'normale', 4),
(53,40, 11.00, 10.50, 'normale', 4),
(53,41,  8.00,  9.00, 'normale', 4),
-- C011 CHORFI
(54,37, 19.00, 18.50, 'normale', 4),
(54,38, 20.00, 19.50, 'normale', 4),
(54,39, 17.00, 18.00, 'normale', 4),
(54,40, 16.00, 17.00, 'normale', 4),
(54,41, 18.00, 17.50, 'normale', 4),
-- C012 ELMOURABIT
(55,37, 13.00, 14.00, 'normale', 4),
(55,38, 11.00, 12.00, 'normale', 4),
(55,39, 15.00, 14.50, 'normale', 4),
(55,40,  9.00, 10.00, 'normale', 4),
(55,41, 12.00, 11.50, 'normale', 4),

-- ═══════ ANCIENS 2024-2025 (annee_id=3) ═══════
-- A001 ZAKI (MGSI S1)
(56, 1, 13.00, 12.00, 'normale', 3),
(56, 2, 11.00, 10.00, 'normale', 3),
(56, 3, 14.00, 15.00, 'normale', 3),
-- A002 BENALI (MGSI S2)
(57, 7, 10.00, 11.00, 'normale', 3),
(57, 9, 12.00, 13.00, 'normale', 3),
-- A003 ROUISSI (GL S1)
(58,12, 15.00, 14.50, 'normale', 3),
(58,14, 16.00, 17.00, 'normale', 3),
-- A004 LACHKAR (SDBDIA S2)
(59,27, 14.00, 15.00, 'normale', 3),
(59,28, 12.00, 13.00, 'normale', 3),
-- A005 KARIMI (SITCN S1)
(60,32, 17.00, 16.00, 'normale', 3),
(60,34, 15.00, 16.00, 'normale', 3),

-- ═══════ ANCIENS 2023-2024 (annee_id=2) ═══════
-- B001 OUMAROU (MGSI S1)
(61, 1, 12.00, 11.00, 'normale', 2),
(61, 2, 10.00,  9.50, 'normale', 2),
-- B002 TAHIRI (GL S2)
(62,17, 13.00, 14.00, 'normale', 2),
(62,19, 11.00, 12.00, 'normale', 2),

-- ═══════ RATTRAPAGES 2025-2026 ═══════
(2,  2, NULL, 11.00, 'rattrapage', 4),
(2,  5, NULL, 10.00, 'rattrapage', 4),
(5,  5, NULL,  9.50, 'rattrapage', 4),
(8,  4, NULL, 11.00, 'rattrapage', 4),
(11, 8, NULL, 12.00, 'rattrapage', 4),
(15, 7, NULL,  9.50, 'rattrapage', 4),
(19,12, NULL, 11.50, 'rattrapage', 4),
(21,14, NULL, 10.00, 'rattrapage', 4),
(33,24, NULL, 10.50, 'rattrapage', 4),
(35,25, NULL,  9.00, 'rattrapage', 4),
(39,25, NULL, 10.00, 'rattrapage', 4),
(45,33, NULL, 10.00, 'rattrapage', 4),
(49,34, NULL,  9.50, 'rattrapage', 4),
(53,39, NULL, 10.00, 'rattrapage', 4);

-- ============================================================
-- TABLE : absences
-- ============================================================
DROP TABLE IF EXISTS `absences`;
CREATE TABLE `absences` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `etudiant_id`  INT UNSIGNED NOT NULL,
  `module_id`    INT UNSIGNED NOT NULL,
  `date_absence` DATE NOT NULL,
  `justifiee`    TINYINT(1) NOT NULL DEFAULT 0,
  `annee_id`     INT UNSIGNED NOT NULL,
  FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants`(`id`),
  FOREIGN KEY (`module_id`)   REFERENCES `modules`(`id`),
  FOREIGN KEY (`annee_id`)    REFERENCES `annees_academiques`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `absences` (`etudiant_id`, `module_id`, `date_absence`, `justifiee`, `annee_id`) VALUES
-- ── Octobre 2025 ──
(1,  2, '2025-10-05', 1, 4),
(1,  5, '2025-10-12', 0, 4),
(2,  1, '2025-10-08', 0, 4),
(2,  2, '2025-10-15', 0, 4),
(2,  4, '2025-10-22', 1, 4),
(3,  3, '2025-10-22', 0, 4),
(5,  1, '2025-10-10', 0, 4),
(5,  5, '2025-10-17', 0, 4),
(17,12, '2025-10-14', 0, 4),
(18,13, '2025-10-28', 0, 4),
(19,12, '2025-10-07', 0, 4),
(32,22, '2025-10-09', 1, 4),
(33,23, '2025-10-21', 0, 4),
(44,32, '2025-10-16', 0, 4),
(45,33, '2025-10-23', 0, 4),
-- ── Novembre 2025 ──
(11, 7, '2025-11-03', 0, 4),
(11, 8, '2025-11-10', 1, 4),
(12, 9, '2025-11-17', 0, 4),
(4,  2, '2025-11-05', 1, 4),
(13, 7, '2025-11-12', 0, 4),
(20,14, '2025-11-19', 0, 4),
(21,12, '2025-11-26', 0, 4),
(34,22, '2025-11-04', 0, 4),
(35,25, '2025-11-11', 0, 4),
(39,26, '2025-11-18', 1, 4),
(46,34, '2025-11-06', 0, 4),
(47,35, '2025-11-13', 1, 4),
(48,32, '2025-11-20', 0, 4),
-- ── Décembre 2025 ──
(11, 7, '2025-12-01', 0, 4),
(12, 9, '2025-12-10', 0, 4),
(5,  3, '2025-12-08', 0, 4),
(22,13, '2025-12-15', 0, 4),
(27,17, '2025-12-04', 1, 4),
(28,18, '2025-12-09', 0, 4),
(40,27, '2025-12-11', 0, 4),
(41,28, '2025-12-18', 1, 4),
(52,37, '2025-12-03', 0, 4),
(53,38, '2025-12-17', 0, 4),
-- ── Janvier 2026 ──
(2,  3, '2026-01-07', 0, 4),
(3,  4, '2026-01-14', 1, 4),
(7,  2, '2026-01-09', 0, 4),
(13, 8, '2026-01-16', 0, 4),
(15, 9, '2026-01-21', 0, 4),
(25,14, '2026-01-13', 1, 4),
(29,19, '2026-01-08', 0, 4),
(36,24, '2026-01-22', 0, 4),
(42,29, '2026-01-15', 1, 4),
(50,35, '2026-01-20', 0, 4),
(54,38, '2026-01-06', 1, 4),
-- ── Février 2026 ──
(1,  4, '2026-02-04', 0, 4),
(4,  6, '2026-02-11', 0, 4),
(6,  1, '2026-02-18', 1, 4),
(14, 8, '2026-02-06', 0, 4),
(23,16, '2026-02-13', 0, 4),
(30,20, '2026-02-10', 0, 4),
(37,23, '2026-02-17', 1, 4),
(43,30, '2026-02-05', 0, 4),
(49,32, '2026-02-12', 0, 4),
(51,34, '2026-02-19', 0, 4),
(55,40, '2026-02-03', 1, 4),
-- ── Anciens 2024-2025 ──
(56, 1, '2024-10-10', 0, 3),
(56, 2, '2024-11-07', 1, 3),
(58,12, '2024-10-22', 0, 3),
(59,27, '2024-12-05', 0, 3),
(60,32, '2024-11-14', 0, 3);

-- ============================================================
-- Fin du script — Base : ensiasd_dashboard
-- ============================================================
