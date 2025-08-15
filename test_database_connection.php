<?php
echo "=== Test de connexion à la base de données ===\n";

try {
    // Test 1: Connexion PDO
    echo "1. Test de connexion PDO...\n";
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion PDO réussie\n";
    
    // Test 2: Vérifier les tables
    echo "\n2. Vérification des tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables trouvées: " . implode(', ', $tables) . "\n";
    
    // Test 3: Vérifier la structure de la table items
    echo "\n3. Structure de la table 'items'...\n";
    $stmt = $pdo->query("DESCRIBE items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Colonnes de la table items:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Test 4: Vérifier les données dans items
    echo "\n4. Données dans la table 'items'...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items");
    $result = $stmt->fetch();
    echo "Nombre total d'éléments: " . $result['total'] . "\n";
    
    // Test 5: Vérifier les colonnes copies et available_copies
    echo "\n5. Test des colonnes copies et available_copies...\n";
    $stmt = $pdo->query("SELECT id, title, copies, available_copies FROM items LIMIT 3");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Exemples d'éléments:\n";
    foreach ($items as $item) {
        echo "- ID: {$item['id']}, Titre: {$item['title']}, Copies: {$item['copies']}, Disponibles: {$item['available_copies']}\n";
    }
    
    // Test 6: Test d'insertion
    echo "\n6. Test d'insertion...\n";
    try {
        $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, classification, copies, available_copies) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['fr', 'test', 'Test Item', 'TEST', 1, 1]);
        echo "✅ Test d'insertion réussi\n";
        
        // Supprimer l'élément de test
        $pdo->exec("DELETE FROM items WHERE title = 'Test Item'");
        echo "✅ Élément de test supprimé\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors du test d'insertion: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    
    // Suggestions de résolution
    echo "\n=== Suggestions de résolution ===\n";
    echo "1. Vérifiez que MySQL est démarré\n";
    echo "2. Vérifiez que la base 'library_system' existe\n";
    echo "3. Vérifiez les identifiants (root, mot de passe vide)\n";
    echo "4. Vérifiez le fichier config/db.php\n";
}

echo "\n=== Test terminé ===\n";
?>
