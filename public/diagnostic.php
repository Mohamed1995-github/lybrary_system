<?php
/**
 * Diagnostic complet pour le système de bibliothèque
 * Aide à identifier les problèmes de configuration
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic du Système de Bibliothèque</h1>";
echo "<hr>";

// 1. Informations de base
echo "<h2>1. Informations de base</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Filename:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";

// 2. Vérification des fichiers essentiels
echo "<h2>2. Vérification des fichiers essentiels</h2>";

$essential_files = [
    '../config/database.php',
    '../includes/auth.php',
    '../includes/security.php',
    'router.php',
    'dashboard.php',
    'login.php'
];

foreach ($essential_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    $exists = file_exists($full_path);
    $readable = is_readable($full_path);
    echo "<p><strong>$file:</strong> ";
    echo $exists ? "✅ Existe" : "❌ N'existe pas";
    echo $readable ? " ✅ Lisible" : " ❌ Non lisible";
    echo "</p>";
}

// 3. Vérification des modules
echo "<h2>3. Vérification des modules</h2>";

$modules = [
    'administration' => ['list.php', 'add.php', 'edit.php', 'delete.php', 'reports.php'],
    'items' => ['list.php', 'add_book.php', 'add_magazine.php', 'add_newspaper.php', 'statistics.php'],
    'loans' => ['list.php', 'borrow.php', 'return.php'],
    'borrowers' => ['list.php', 'add.php', 'edit.php']
];

foreach ($modules as $module => $files) {
    echo "<h3>Module: $module</h3>";
    foreach ($files as $file) {
        $full_path = __DIR__ . "/../modules/$module/$file";
        $exists = file_exists($full_path);
        echo "<p><strong>$file:</strong> ";
        echo $exists ? "✅ Existe" : "❌ N'existe pas";
        echo "</p>";
    }
}

// 4. Test de connexion à la base de données
echo "<h2>4. Test de connexion à la base de données</h2>";

try {
    require_once __DIR__ . '/../config/database.php';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Connexion à la base de données réussie</p>";
    
    // Test des tables
    $tables = ['employees', 'items', 'loans', 'borrowers'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p><strong>Table $table:</strong> ✅ Existe ($count enregistrements)</p>";
        } catch (Exception $e) {
            echo "<p><strong>Table $table:</strong> ❌ Erreur: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}

// 5. Test des permissions
echo "<h2>5. Test des permissions</h2>";

$directories = [
    '../logs',
    '../uploads',
    '../temp'
];

foreach ($directories as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    $exists = is_dir($full_path);
    $writable = is_writable($full_path);
    echo "<p><strong>$dir:</strong> ";
    echo $exists ? "✅ Existe" : "❌ N'existe pas";
    echo $writable ? " ✅ Écriture autorisée" : " ❌ Écriture non autorisée";
    echo "</p>";
}

// 6. Test des sessions
echo "<h2>6. Test des sessions</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['test'] = 'test_value';
$session_working = isset($_SESSION['test']) && $_SESSION['test'] === 'test_value';
echo "<p><strong>Sessions:</strong> ";
echo $session_working ? "✅ Fonctionnent" : "❌ Ne fonctionnent pas";
echo "</p>";

// 7. Test des modules Apache
echo "<h2>7. Modules Apache</h2>";

$apache_modules = ['mod_rewrite', 'mod_headers'];
foreach ($apache_modules as $module) {
    $loaded = function_exists('apache_get_modules') ? in_array($module, apache_get_modules()) : 'Inconnu';
    echo "<p><strong>$module:</strong> ";
    echo $loaded === true ? "✅ Chargé" : ($loaded === false ? "❌ Non chargé" : "❓ Inconnu");
    echo "</p>";
}

// 8. Test des URLs
echo "<h2>8. Test des URLs</h2>";

$test_urls = [
    'dashboard.php',
    'router.php?module=administration&action=list',
    'router.php?module=items&action=list',
    'login.php'
];

foreach ($test_urls as $url) {
    $full_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/$url";
    echo "<p><strong>$url:</strong> <a href='$url' target='_blank'>Tester</a></p>";
}

echo "<hr>";
echo "<h2>Résumé</h2>";
echo "<p>Si vous voyez des ❌ dans les vérifications ci-dessus, cela peut expliquer le problème 'Not Found'.</p>";
echo "<p>Vérifiez particulièrement:</p>";
echo "<ul>";
echo "<li>La configuration Apache (mod_rewrite activé)</li>";
echo "<li>Les permissions des fichiers</li>";
echo "<li>La connexion à la base de données</li>";
echo "<li>L'existence des fichiers essentiels</li>";
echo "</ul>";
?>
