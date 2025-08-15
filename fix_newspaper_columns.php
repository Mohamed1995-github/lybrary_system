<?php
echo "=== Correction de l'insertion des journaux ===\n";

// Vérifier la structure exacte de la table items
try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    
    echo "Structure de la table items:\n";
    $stmt = $pdo->query("DESCRIBE items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\nTest d'insertion avec les bonnes colonnes...\n";
    
    // Test avec les colonnes qui existent réellement
    $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, publisher, copies, available_copies) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['fr', 'newspaper', 'Test Journal', 'Test Publisher', 1, 1]);
    echo "✅ Insertion réussie avec les bonnes colonnes\n";
    
    // Supprimer l'élément de test
    $pdo->exec("DELETE FROM items WHERE title = 'Test Journal'");
    echo "✅ Élément de test supprimé\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
