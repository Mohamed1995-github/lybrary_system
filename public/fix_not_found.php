<?php
/**
 * Script de réparation rapide pour le problème "Not Found"
 * Ce script corrige les problèmes de configuration courants
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Réparation du Système de Bibliothèque</h1>";
echo "<hr>";

// 1. Vérification et création des dossiers nécessaires
echo "<h2>1. Création des dossiers nécessaires</h2>";

$directories = [
    '../logs',
    '../uploads',
    '../uploads/books',
    '../uploads/magazines',
    '../uploads/newspapers',
    '../uploads/documents',
    '../temp'
];

foreach ($directories as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (!is_dir($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "<p>✅ Créé: $dir</p>";
        } else {
            echo "<p>❌ Erreur lors de la création: $dir</p>";
        }
    } else {
        echo "<p>✅ Existe déjà: $dir</p>";
    }
}

// 2. Vérification des fichiers de configuration
echo "<h2>2. Vérification des fichiers de configuration</h2>";

$config_files = [
    '../config/database.php',
    '../config/db.php'
];

foreach ($config_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "<p>✅ Existe: $file</p>";
    } else {
        echo "<p>❌ Manquant: $file</p>";
    }
}

// 3. Test de connexion à la base de données
echo "<h2>3. Test de connexion à la base de données</h2>";

try {
    if (file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
    } elseif (file_exists(__DIR__ . '/../config/db.php')) {
        require_once __DIR__ . '/../config/db.php';
    } else {
        throw new Exception("Aucun fichier de configuration de base de données trouvé");
    }
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Connexion à la base de données réussie</p>";
    
    // Test des tables essentielles
    $tables = ['employees', 'items', 'loans', 'borrowers'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Table $table: $count enregistrements</p>";
        } catch (Exception $e) {
            echo "<p>❌ Table $table: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur de base de données: " . $e->getMessage() . "</p>";
}

// 4. Vérification des fichiers .htaccess
echo "<h2>4. Vérification des fichiers .htaccess</h2>";

$htaccess_files = [
    '../.htaccess',
    '.htaccess'
];

foreach ($htaccess_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "<p>✅ Existe: $file</p>";
    } else {
        echo "<p>❌ Manquant: $file</p>";
    }
}

// 5. Test des URLs principales
echo "<h2>5. Test des URLs principales</h2>";

$test_urls = [
    'dashboard.php' => 'Tableau de bord',
    'login.php' => 'Page de connexion',
    'router.php?module=administration&action=list' => 'Liste administration',
    'router.php?module=items&action=list' => 'Liste items'
];

foreach ($test_urls as $url => $description) {
    echo "<p><strong>$description:</strong> <a href='$url' target='_blank'>Tester $url</a></p>";
}

// 6. Création d'un fichier de test simple
echo "<h2>6. Création d'un fichier de test</h2>";

$test_content = '<?php
echo "Test de fonctionnement PHP";
echo "<br>Date: " . date("Y-m-d H:i:s");
echo "<br>PHP Version: " . phpversion();
?>';

if (file_put_contents(__DIR__ . '/test_simple.php', $test_content)) {
    echo "<p>✅ Fichier de test créé: <a href='test_simple.php' target='_blank'>test_simple.php</a></p>";
} else {
    echo "<p>❌ Erreur lors de la création du fichier de test</p>";
}

// 7. Vérification des permissions
echo "<h2>7. Vérification des permissions</h2>";

$test_files = [
    'dashboard.php',
    'router.php',
    'login.php'
];

foreach ($test_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $readable = is_readable($full_path);
        $executable = is_executable($full_path);
        echo "<p><strong>$file:</strong> ";
        echo $readable ? "✅ Lisible" : "❌ Non lisible";
        echo $executable ? " ✅ Exécutable" : " ❌ Non exécutable";
        echo "</p>";
    } else {
        echo "<p><strong>$file:</strong> ❌ N'existe pas</p>";
    }
}

echo "<hr>";
echo "<h2>Instructions de résolution</h2>";
echo "<ol>";
echo "<li>Vérifiez que Apache est démarré dans XAMPP</li>";
echo "<li>Vérifiez que le module mod_rewrite est activé</li>";
echo "<li>Vérifiez que le port 80 n'est pas utilisé par un autre service</li>";
echo "<li>Essayez d'accéder à: <a href='test_simple.php' target='_blank'>test_simple.php</a></li>";
echo "<li>Si test_simple.php fonctionne, le problème vient de la configuration des routes</li>";
echo "<li>Si test_simple.php ne fonctionne pas, le problème vient d'Apache/PHP</li>";
echo "</ol>";

echo "<h2>Liens de test</h2>";
echo "<ul>";
echo "<li><a href='test_simple.php' target='_blank'>Test PHP simple</a></li>";
echo "<li><a href='diagnostic.php' target='_blank'>Diagnostic complet</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Tableau de bord</a></li>";
echo "<li><a href='login.php' target='_blank'>Page de connexion</a></li>";
echo "</ul>";
?>
