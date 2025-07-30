<?php
/**
 * Fichier de test pour diagnostiquer le routeur
 */

echo "<h1>Test du Routeur</h1>";

// Test 1: Vérifier les sessions
echo "<h2>1. Test des Sessions</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "UID: " . ($_SESSION['uid'] ?? 'Non défini') . "<br>";

// Test 2: Vérifier les paramètres GET
echo "<h2>2. Test des Paramètres GET</h2>";
$module = $_GET['module'] ?? 'non défini';
$action = $_GET['action'] ?? 'non défini';
$lang = $_GET['lang'] ?? 'non défini';
echo "Module: $module<br>";
echo "Action: $action<br>";
echo "Lang: $lang<br>";

// Test 3: Vérifier les fichiers
echo "<h2>3. Test des Fichiers</h2>";
$file_path = __DIR__ . "/../modules/{$module}/{$action}.php";
echo "Chemin du fichier: $file_path<br>";
echo "Fichier existe: " . (file_exists($file_path) ? 'OUI' : 'NON') . "<br>";

// Test 4: Vérifier les modules autorisés
echo "<h2>4. Test des Modules Autorisés</h2>";
$allowed_modules = [
    'acquisitions' => ['add', 'edit', 'delete', 'list'],
    'items' => ['add_book', 'add_magazine', 'add_newspaper', 'add_edit', 'edit', 'delete', 'list', 'list_grouped'],
    'loans' => ['add', 'borrow', 'return', 'list'],
    'borrowers' => ['add', 'edit', 'list'],
    'administration' => ['add', 'edit', 'delete', 'list', 'permissions_guide'],
    'members' => ['add', 'edit', 'list']
];

echo "Module autorisé: " . (isset($allowed_modules[$module]) ? 'OUI' : 'NON') . "<br>";
if (isset($allowed_modules[$module])) {
    echo "Action autorisée: " . (in_array($action, $allowed_modules[$module]) ? 'OUI' : 'NON') . "<br>";
}

// Test 5: Vérifier la base de données
echo "<h2>5. Test de la Base de Données</h2>";
try {
    require_once __DIR__ . '/../config/db.php';
    echo "Connexion DB: OK<br>";
    
    // Test d'une requête simple
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
    $result = $stmt->fetch();
    echo "Nombre d'items dans la DB: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "Erreur DB: " . $e->getMessage() . "<br>";
}

// Test 6: Liens de test
echo "<h2>6. Liens de Test</h2>";
echo '<a href="test_router.php?module=items&action=list&type=book&lang=ar">Test Liste Livres</a><br>';
echo '<a href="test_router.php?module=items&action=add_magazine&lang=ar">Test Ajout Magazine</a><br>';
echo '<a href="login.php">Page de Login</a><br>';
echo '<a href="dashboard.php">Dashboard</a><br>';

echo "<hr>";
echo "<p><strong>URL actuelle:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
?> 