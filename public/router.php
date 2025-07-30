<?php
/**
 * Routeur sécurisé pour le système de bibliothèque
 * Gère l'accès aux modules via des URLs propres
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

// Obtenir les paramètres de la requête
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? 'list';
$lang = $_GET['lang'] ?? 'ar';

// Définir les permissions requises pour chaque module et action
$required_permissions = [
    'acquisitions' => [
        'list' => ['Gestion des approvisionnements', 'إدارة التزويد'],
        'add' => ['Gestion des approvisionnements', 'إدارة التزويد'],
        'edit' => ['Gestion des approvisionnements', 'إدارة التزويد'],
        'delete' => ['Gestion des approvisionnements', 'إدارة التزويد', 'admin'],
    ],
    'items' => [
        'list' => ['Gestion des livres', 'Gestion des magazines', 'Gestion des journaux', 'إدارة الكتب', 'إدارة المجلات', 'إدارة الصحف'],
        'add_book' => ['Gestion des livres', 'إدارة الكتب'],
        'add_magazine' => ['Gestion des magazines', 'إدارة المجلات'],
        'add_newspaper' => ['Gestion des journaux', 'إدارة الصحف'],
        'add_edit' => ['Gestion des livres', 'إدارة الكتب'],
        'edit' => ['Gestion des livres', 'إدارة الكتب'],
        'delete' => ['Gestion des livres', 'إدارة الكتب', 'admin'],
        'list_grouped' => ['Gestion des livres', 'إدارة الكتب'],
    ],
    'loans' => [
        'list' => ['Gestion des prêts', 'إدارة الإعارة'],
        'add' => ['Gestion des prêts', 'إدارة الإعارة'],
        'borrow' => ['Gestion des prêts', 'إدارة الإعارة'],
        'return' => ['Gestion des prêts', 'إدارة الإعارة'],
    ],
    'borrowers' => [
        'list' => ['Gestion des emprunteurs', 'إدارة المستعيرين'],
        'add' => ['Gestion des emprunteurs', 'إدارة المستعيرين'],
        'edit' => ['Gestion des emprunteurs', 'إدارة المستعيرين'],
    ],
    'administration' => [
        'list' => ['Gestion des employés', 'إدارة الموظفين', 'admin'],
        'add' => ['Gestion des employés', 'إدارة الموظفين', 'admin'],
        'edit' => ['Gestion des employés', 'إدارة الموظفين', 'admin'],
        'delete' => ['Gestion des employés', 'إدارة الموظفين', 'admin'],
        'permissions_guide' => ['Gestion des employés', 'إدارة الموظفين', 'admin'],
    ],
    'members' => [
        'list' => [], // Accès public
        'add' => ['admin'],
        'edit' => ['admin'],
    ]
];

// Vérification de l'existence du module et de l'action
if (!isset($required_permissions[$module]) || !array_key_exists($action, $required_permissions[$module])) {
    http_response_code(404);
    echo '<h1>Module ou action non trouvé</h1>';
    exit;
}

// Vérification des permissions
$permissions_for_action = $required_permissions[$module][$action];
if (empty($permissions_for_action)) {
    // Si aucune permission n'est requise, l'accès est public pour les utilisateurs connectés
    $has_permission = true;
} else {
    $has_permission = hasAnyPermission($permissions_for_action);
}

if (!$has_permission) {
    // Log de la tentative d'accès non autorisé
    $log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - UNAUTHORIZED_ACCESS - Module: {$module} - Action: {$action} - IP: {$_SERVER['REMOTE_ADDR']}\n";
    file_put_contents(__DIR__ . '/../logs/security.log', $log_entry, FILE_APPEND | LOCK_EX);

    // Redirection vers la page d'erreur
    header("Location: error.php?code=403&lang={$lang}");
    exit;
}

// Construire le chemin du fichier
$file_path = __DIR__ . "/../modules/{$module}/{$action}.php";

// Vérifier si le fichier existe
if (!file_exists($file_path)) {
    http_response_code(404);
    echo '<h1>Page non trouvée</h1>';
    exit;
}

// Log de l'accès autorisé
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - AUTHORIZED_ACCESS - Module: {$module} - Action: {$action} - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../logs/access.log', $log_entry, FILE_APPEND | LOCK_EX);

// Inclure le fichier du module
include $file_path;
?> 