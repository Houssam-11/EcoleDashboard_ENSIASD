<?php
/* config.php — connexion BDD & constantes globales
 * Dashboard Statistique Ensiasd  */

define('DB_HOST', 'tramway.proxy.rlwy.net');
define('DB_NAME', 'dashboard_ecole');
define('DB_USER', 'root');
define('DB_PASS', 'RMGEaHtBKrphfhmkvFLOyltOiNIhCYyX');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Dashboard Statistique École');
define('APP_VERSION', '1.0');
define('BASE_URL', 'http://localhost/DashboardEcole_MGSI/');

// démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// connexion PDO
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="color:red;padding:20px;font-family:monospace;">
                <strong>Erreur de connexion BDD :</strong> ' . htmlspecialchars($e->getMessage()) . '
                </div>');
        }
    }
    return $pdo;
}

/* vérifie si l'utilisateur est connecté, sinon redirige vers login*/
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/* récupère le rôle de l'utilisateur connecté */
function getUserRole(): string {
    return $_SESSION['role'] ?? 'visiteur';
}

/* échappe une valeur pour affichage HTML sécurisé */
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
