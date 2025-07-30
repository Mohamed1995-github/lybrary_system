<?php
/* add_edition_column.php - Ajouter la colonne edition manquante */
require_once 'config/db.php';

echo "<h2>🔧 Ajout de la colonne 'edition'</h2>";

try {
    // Vérifier si la colonne edition existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM items LIKE 'edition'");
    if ($stmt->rowCount() == 0) {
        // Ajouter la colonne edition
        $pdo->exec("ALTER TABLE items ADD COLUMN edition VARCHAR(100) NULL AFTER year_pub");
        echo "✅ Colonne 'edition' ajoutée avec succès!<br>";
    } else {
        echo "ℹ️ Colonne 'edition' existe déjà.<br>";
    }
    
    // Vérifier la structure finale
    echo "<h3>Structure finale de la table items:</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM items")->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>{$column['Field']}</strong> - {$column['Type']} - " . 
             ($column['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . 
             ($column['Key'] == 'PRI' ? ' - PRIMARY KEY' : '') . "</li>";
    }
    echo "</ul>";
    
    echo "<h3>🎉 Mise à jour terminée!</h3>";
    echo "<p>La colonne 'edition' est maintenant disponible.</p>";
    echo "<p><a href='modules/items/list.php?lang=ar&type=book'>Retourner à la liste des livres</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?> 