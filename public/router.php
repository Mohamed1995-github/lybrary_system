<?php
/**
 * Routeur sécurisé pour le système de bibliothèque
 * Gère l'accès aux modules via des URLs propres
 */

require_once __DIR__ . '/../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

// Obtenir le module et l'action depuis l'URL
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? 'list';
$lang = $_GET['lang'] ?? 'ar';

// Définir les modules autorisés
$allowed_modules = [
    'acquisitions' => ['add', 'edit', 'delete', 'list'],
    'items' => ['add_book', 'add_magazine', 'add_newspaper', 'add_edit', 'edit', 'delete', 'list', 'list_grouped', 'statistics'],
    'loans' => ['add', 'borrow', 'return', 'list'],
    'borrowers' => ['add', 'edit', 'list'],
    'administration' => ['add', 'edit', 'delete', 'list', 'permissions_guide', 'reports', 'activity', 'backup'],
    'members' => ['add', 'edit', 'list']
];

// Vérifier si le module existe
if (!isset($allowed_modules[$module])) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Module non trouvé - Library System</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .error-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                background: var(--bg-primary);
            }
            .error-card {
                background: var(--bg-primary);
                border-radius: var(--radius-xl);
                padding: 3rem;
                box-shadow: var(--shadow-xl);
                border: 1px solid var(--border-color);
                max-width: 600px;
                text-align: center;
            }
            .error-icon {
                font-size: 4rem;
                color: var(--error-color);
                margin-bottom: 1rem;
            }
            .error-title {
                font-size: 2rem;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 1rem;
            }
            .error-message {
                color: var(--text-secondary);
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            .error-details {
                background: var(--bg-secondary);
                padding: 1rem;
                border-radius: var(--radius-lg);
                margin-bottom: 2rem;
                text-align: left;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: var(--primary-color);
                color: white;
                text-decoration: none;
                border-radius: var(--radius-lg);
                font-weight: 600;
                transition: all 0.2s ease;
            }
            .back-btn:hover {
                background: var(--primary-hover);
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1 class="error-title">Module non trouvé</h1>
                <p class="error-message">Le module demandé n'existe pas dans le système.</p>
                <div class="error-details">
                    <p><strong>Module demandé:</strong> <?= htmlspecialchars($module) ?></p>
                    <p><strong>Modules disponibles:</strong> <?= implode(', ', array_keys($allowed_modules)) ?></p>
                </div>
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Vérifier si l'action est autorisée pour ce module
if (!in_array($action, $allowed_modules[$module])) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Action non autorisée - Library System</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .error-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                background: var(--bg-primary);
            }
            .error-card {
                background: var(--bg-primary);
                border-radius: var(--radius-xl);
                padding: 3rem;
                box-shadow: var(--shadow-xl);
                border: 1px solid var(--border-color);
                max-width: 600px;
                text-align: center;
            }
            .error-icon {
                font-size: 4rem;
                color: var(--error-color);
                margin-bottom: 1rem;
            }
            .error-title {
                font-size: 2rem;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 1rem;
            }
            .error-message {
                color: var(--text-secondary);
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            .error-details {
                background: var(--bg-secondary);
                padding: 1rem;
                border-radius: var(--radius-lg);
                margin-bottom: 2rem;
                text-align: left;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: var(--primary-color);
                color: white;
                text-decoration: none;
                border-radius: var(--radius-lg);
                font-weight: 600;
                transition: all 0.2s ease;
            }
            .back-btn:hover {
                background: var(--primary-hover);
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <h1 class="error-title">Action non autorisée</h1>
                <p class="error-message">L'action demandée n'est pas autorisée pour ce module.</p>
                <div class="error-details">
                    <p><strong>Action demandée:</strong> <?= htmlspecialchars($action) ?></p>
                    <p><strong>Actions disponibles:</strong> <?= implode(', ', $allowed_modules[$module]) ?></p>
                </div>
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Construire le chemin du fichier
$file_path = __DIR__ . "/../modules/{$module}/{$action}.php";

// Vérifier si le fichier existe
if (!file_exists($file_path)) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page non trouvée - Library System</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .error-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                background: var(--bg-primary);
            }
            .error-card {
                background: var(--bg-primary);
                border-radius: var(--radius-xl);
                padding: 3rem;
                box-shadow: var(--shadow-xl);
                border: 1px solid var(--border-color);
                max-width: 600px;
                text-align: center;
            }
            .error-icon {
                font-size: 4rem;
                color: var(--error-color);
                margin-bottom: 1rem;
            }
            .error-title {
                font-size: 2rem;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 1rem;
            }
            .error-message {
                color: var(--text-secondary);
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            .error-details {
                background: var(--bg-secondary);
                padding: 1rem;
                border-radius: var(--radius-lg);
                margin-bottom: 2rem;
                text-align: left;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: var(--primary-color);
                color: white;
                text-decoration: none;
                border-radius: var(--radius-lg);
                font-weight: 600;
                transition: all 0.2s ease;
            }
            .back-btn:hover {
                background: var(--primary-hover);
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-file-excel"></i>
                </div>
                <h1 class="error-title">Page non trouvée</h1>
                <p class="error-message">Le fichier demandé n'existe pas sur le serveur.</p>
                <div class="error-details">
                    <p><strong>Fichier demandé:</strong> <?= htmlspecialchars($file_path) ?></p>
                </div>
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Log de l'accès pour la sécurité
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - FULL_ACCESS - Module: {$module} - Action: {$action} - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../logs/access.log', $log_entry, FILE_APPEND | LOCK_EX);

// Inclure le fichier du module
include $file_path;
?> 