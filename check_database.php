<?php
require_once 'config/db.php';

echo "<h2>🔍 Vérification de la base de données</h2>";

try {
    // Vérifier si la table items existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'items'");
    if ($stmt->rowCount() == 0) {
        echo "❌ La table 'items' n'existe pas. Création en cours...<br>";
        
        // Créer la table items avec tous les champs nécessaires
        $sql = "CREATE TABLE items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lang VARCHAR(2) NOT NULL DEFAULT 'ar',
            type VARCHAR(20) NOT NULL,
            title VARCHAR(255) NOT NULL,
            author VARCHAR(255),
            publisher VARCHAR(255),
            year_pub INT,
            classification VARCHAR(100),
            field VARCHAR(50),
            copies_total INT DEFAULT 1,
            copies_in INT DEFAULT 1,
            format VARCHAR(50),
            isbn VARCHAR(20),
            pages INT,
            language VARCHAR(10),
            description TEXT,
            edition VARCHAR(100),
            series VARCHAR(255),
            frequency VARCHAR(50),
            issn VARCHAR(20),
            volume VARCHAR(50),
            issue VARCHAR(50),
            circulation VARCHAR(100),
            founded_year INT,
            book_type VARCHAR(100),
            magazine_name VARCHAR(255),
            volume_number VARCHAR(50),
            newspaper_number VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "✅ Table 'items' créée avec succès!<br>";
    } else {
        echo "ℹ️ La table 'items' existe déjà.<br>";
        
        // Afficher la structure actuelle
        $columns = $pdo->query("SHOW COLUMNS FROM items")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Structure actuelle de la table items:</h3><ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> - {$col['Type']} - {$col['Null']} - {$col['Key']} - {$col['Default']}</li>";
        }
        echo "</ul>";
        
        // Vérifier et ajouter les nouveaux champs nécessaires
        $required_columns = [
            'book_type' => 'VARCHAR(100)',
            'magazine_name' => 'VARCHAR(255)',
            'volume_number' => 'VARCHAR(50)',
            'newspaper_number' => 'VARCHAR(50)'
        ];
        
        foreach ($required_columns as $column => $type) {
            $stmt = $pdo->query("SHOW COLUMNS FROM items LIKE '$column'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE items ADD COLUMN $column $type");
                echo "✅ Colonne '$column' ajoutée à la table items!<br>";
            } else {
                echo "ℹ️ Colonne '$column' existe déjà.<br>";
            }
        }
    }
    
    echo "<br><h3>🎉 Vérification terminée!</h3>";
    echo "<p>La base de données est prête pour les modifications demandées.</p>";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?> 