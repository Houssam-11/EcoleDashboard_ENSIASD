====================================================
  Dashboard Statistique École — MGSI
  README — Instructions d'installation
====================================================

MEMBRES DU GROUPE
-----------------
  1. BAKZIZ Yahia
  2. ELBERROUHI Houssam

FILIÈRE : MGSI — S6 — 2025-2026
ENCADRANT : LAASSEM BRAHIM

====================================================
PRÉREQUIS
====================================================
  - XAMPP (ou WAMP) avec PHP >= 8.0 et MySQL/MariaDB
  - Navigateur moderne (Chrome, Firefox, Edge)

====================================================
INSTALLATION
====================================================

1. COPIER LE PROJET
   -----------------
   Copiez le dossier "DashboardEcole_MGSI" dans :
     XAMPP : C:\xampp\htdocs\
     WAMP  : C:\wamp64\www\

2. CRÉER LA BASE DE DONNÉES
   -------------------------
   a. Ouvrez phpMyAdmin : http://localhost/phpmyadmin
   b. Cliquez sur "Importer"
   c. Sélectionnez le fichier : ensiasd_dashboard.sql
   d. Cliquez "Exécuter"

   OU via ligne de commande :
     mysql -u root -p < ensiasd_dashboard.sql

3. CONFIGURER LA CONNEXION
   ------------------------
   Ouvrez config.php et vérifiez/modifiez :
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'dashboard_ecole');
     define('DB_USER', 'root');
     define('DB_PASS', '');

4. LANCER L'APPLICATION
   ----------------------
   Ouvrez dans votre navigateur :
     http://localhost/DashboardEcole_MGSI/

====================================================
COMPTE D'ACCÈS
====================================================

  Login      : ENSIASD
  Mot passe  : ENSIASD2026
  Rôle       : Administrateur (accès complet)

Comptes supplémentaires (insérés dans ensiasd_dashboard.sql) :
  Login: prof1        MDP: ENSIASD2026  Rôle: Enseignant
  Login: secretaire1  MDP: ENSIASD2026  Rôle: Secrétaire

====================================================
FONCTIONNALITÉS
====================================================

  ✔ Tableau de bord avec indicateurs KPI
  ✔ Graphiques : distribution moyennes, absences,
    résultats par filière (Chart.js)
  ✔ Filtres : filière, année académique, semestre
  ✔ Gestion étudiants (ajout, suppression, recherche)
  ✔ Gestion enseignants et modules
  ✔ Saisie et affichage des notes (session normale + rattrapage)
  ✔ Journal des absences (justifiées / non justifiées)
  ✔ Statistiques avancées avec graphiques
  ✔ Export CSV (résultats, étudiants, absences)
  ✔ Gestion des utilisateurs (admin)
  ✔ Sécurité : PDO préparé, sessions, hashage bcrypt

====================================================
STACK TECHNIQUE
====================================================

  Front-end  : HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons
  Back-end   : PHP 8 natif (PDO)
  BDD        : MySQL/MariaDB
  Graphiques : Chart.js 4
  Serveur    : XAMPP/WAMP (Apache)

====================================================
STRUCTURE DES DOSSIERS
====================================================

  DashboardEcole_MGSI/
  ├── index.php          (page de connexion)
  ├── config.php         (connexion BDD + constantes)
  ├── ensiasd_dashboard.sql         (export BDD complet)
  ├── css/style.css
  ├── js/main.js
  ├── images/
  ├── pages/
  │   ├── dashboard.php
  │   ├── etudiants.php
  │   ├── enseignants.php
  │   ├── modules.php
  │   ├── notes.php
  │   ├── absences.php
  │   ├── statistiques.php
  │   ├── export.php
  │   ├── utilisateurs.php
  │   └── logout.php
  ├── includes/
  │   ├── header.php
  │   ├── footer.php
  │   ├── navbar.php
  │   ├── sidebar.php
  │   └── fonctions.php
  └── doc/
      ├── README.txt (ce fichier)
      ├── captures/
      └── diagrammes/

====================================================