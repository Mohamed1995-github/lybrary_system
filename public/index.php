<?php
/**
 * Point d'entrée sécurisé pour le système de bibliothèque
 * Toutes les requêtes passent par ce fichier
 */

// Configuration de base
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Configuration des sessions sécurisées
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../config/database.php';

// Initialiser les classes de sécurité
$security = Security::getInstance();
$logger = new Logger();

// Fonction pour nettoyer les entrées
function sanitizeInput($input) {
    return Security::sanitizeInput($input);
}

// Fonction pour échapper les sorties
function escapeOutput($output) {
    return Security::escape($output);
}

// Router simple
$request = $_SERVER['REQUEST_URI'] ?? '';
$request = parse_url($request, PHP_URL_PATH);
$request = trim($request, '/');

// Si pas de requête, rediriger vers la page de connexion
if (empty($request)) {
    header('Location: login.php');
    exit;
}

// Liste des pages autorisées
$allowedPages = [
    'login' => 'login.php',
    'employee_login' => 'employee_login.php',
    'dashboard' => 'dashboard.php',
    'employee_dashboard' => 'employee_dashboard.php',
    'logout' => 'logout.php',
    'assets' => 'assets/'
];

// Vérifier si la requête est autorisée
$page = null;
foreach ($allowedPages as $key => $file) {
    if (strpos($request, $key) === 0) {
        $page = $file;
        break;
    }
}

// Si c'est un fichier asset, servir directement
if (strpos($request, 'assets/') === 0) {
    $filePath = __DIR__ . '/' . $request;
    if (file_exists($filePath) && is_file($filePath)) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
            header('Cache-Control: public, max-age=31536000'); // 1 an
            readfile($filePath);
            exit;
        }
    }
}

// Si la page n'est pas trouvée, afficher une erreur 404
if (!$page) {
    http_response_code(404);
    $logger->logError('Page not found', ['request' => $request]);
    
    // Afficher une page d'erreur 404
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page non trouvée - Système de Bibliothèque</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <style>
            .error-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 2rem;
            }
            .error-code {
                font-size: 6rem;
                font-weight: bold;
                color: #e53e3e;
                margin-bottom: 1rem;
            }
            .error-message {
                font-size: 1.5rem;
                color: #4a5568;
                margin-bottom: 2rem;
            }
            .back-link {
                color: #3182ce;
                text-decoration: none;
                font-weight: 500;
            }
            .back-link:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div>
                <div class="error-code">404</div>
                <div class="error-message">Page non trouvée</div>
                <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
                <a href="login.php" class="back-link">Retour à l'accueil</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Vérifier l'authentification pour les pages protégées
$protectedPages = ['dashboard', 'employee_dashboard'];
$isProtected = false;

foreach ($protectedPages as $protectedPage) {
    if (strpos($request, $protectedPage) === 0) {
        $isProtected = true;
        break;
    }
}

if ($isProtected) {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['uid'])) {
        $logger->logSecurity('Unauthorized access attempt', [
            'page' => $request,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        header('Location: login.php');
        exit;
    }
    
    // Vérifier l'expiration de la session (1 heure)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) {
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
    
    // Mettre à jour le temps de connexion
    $_SESSION['login_time'] = time();
}

// Inclure la page demandée
$filePath = __DIR__ . '/' . $page;
if (file_exists($filePath) && is_file($filePath)) {
    // Logger l'accès à la page
    $logger->logInfo('Page accessed', [
        'page' => $request,
        'user_id' => $_SESSION['uid'] ?? 'guest',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    include $filePath;
} else {
    http_response_code(404);
    $logger->logError('File not found', ['file' => $filePath]);
    echo "Erreur: Fichier non trouvé";
}
?> 