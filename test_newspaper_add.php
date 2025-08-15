<?php
echo "=== Test d'ajout de journal ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    
    // Simuler l'ajout d'un journal
    $lang = 'fr';
    $type = 'newspaper';
    $title = 'Ech-Chaab';
    $publisher = 'Test Publisher';
    
    echo "Tentative d'insertion...\n";
    $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, publisher, copies, available_copies) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$lang, $type, $title, $publisher, 1, 1]);
    
    echo "✅ Journal ajouté avec succès!\n";
    
    // Vérifier l'insertion
    $stmt = $pdo->prepare("SELECT * FROM items WHERE title = ? AND type = 'newspaper' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$title]);
    $item = $stmt->fetch();
    
    echo "Détails du journal ajouté:\n";
    echo "- ID: {$item['id']}\n";
    echo "- Titre: {$item['title']}\n";
    echo "- Type: {$item['type']}\n";
    echo "- Langue: {$item['lang']}\n";
    echo "- Éditeur: {$item['publisher']}\n";
    echo "- Copies: {$item['copies']}\n";
    echo "- Disponibles: {$item['available_copies']}\n";
    
    // Supprimer l'élément de test
    $pdo->exec("DELETE FROM items WHERE id = {$item['id']}");
    echo "✅ Élément de test supprimé\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
?>
