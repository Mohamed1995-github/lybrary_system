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
    'items' => ['add_book', 'add_magazine', 'add_newspaper', 'add_edit', 'edit', 'delete', 'list', 'list_grouped'],
    'loans' => ['add', 'borrow', 'return', 'list'],
    'borrowers' => ['add', 'edit', 'list'],
    'administration' => ['add', 'edit', 'delete', 'list', 'permissions_guide'],
    'members' => ['add', 'edit', 'list']
];

// Vérifier si le module existe
if (!isset($allowed_modules[$module])) {
    http_response_code(404);
    echo '<h1>Module non trouvé</h1>';
    echo '<p>Module demandé: ' . htmlspecialchars($module) . '</p>';
    echo '<p>Modules disponibles: ' . implode(', ', array_keys($allowed_modules)) . '</p>';
    echo '<p><a href="dashboard.php">Retour au tableau de bord</a></p>';
    exit;
}

// Vérifier si l'action est autorisée pour ce module
if (!in_array($action, $allowed_modules[$module])) {
    http_response_code(403);
    echo '<h1>Action non autorisée</h1>';
    echo '<p>Action demandée: ' . htmlspecialchars($action) . '</p>';
    echo '<p>Actions disponibles: ' . implode(', ', $allowed_modules[$module]) . '</p>';
    echo '<p><a href="dashboard.php">Retour au tableau de bord</a></p>';
    exit;
}

// Construire le chemin du fichier
$file_path = __DIR__ . "/../modules/{$module}/{$action}.php";

// Vérifier si le fichier existe
if (!file_exists($file_path)) {
    http_response_code(404);
    echo '<h1>Page non trouvée</h1>';
    echo '<p>Fichier demandé: ' . htmlspecialchars($file_path) . '</p>';
    echo '<p><a href="dashboard.php">Retour au tableau de bord</a></p>';
    exit;
}

// Log de l'accès pour la sécurité
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - FULL_ACCESS - Module: {$module} - Action: {$action} - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../logs/access.log', $log_entry, FILE_APPEND | LOCK_EX);

// Inclure le fichier du module
include $file_path;
?> 