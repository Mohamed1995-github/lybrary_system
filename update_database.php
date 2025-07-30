<?php
require_once 'config/db.php';

echo "<h2>🔧 Mise à jour de la base de données</h2>";

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
    
    // Vérifier si la table borrowers existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'borrowers'");
    if ($stmt->rowCount() == 0) {
        echo "❌ La table 'borrowers' n'existe pas. Création en cours...<br>";
        
        $sql = "CREATE TABLE borrowers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            national_id VARCHAR(20) NOT NULL UNIQUE,
            phone VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "✅ Table 'borrowers' créée avec succès!<br>";
    } else {
        echo "ℹ️ La table 'borrowers' existe déjà.<br>";
    }
    
    // Vérifier si la table loans existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'loans'");
    if ($stmt->rowCount() == 0) {
        echo "❌ La table 'loans' n'existe pas. Création en cours...<br>";
        
        $sql = "CREATE TABLE loans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_id INT NOT NULL,
            borrower_id INT,
            loan_date DATE NOT NULL,
            return_date DATE,
            status ENUM('active', 'returned', 'overdue') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (item_id) REFERENCES items(id),
            FOREIGN KEY (borrower_id) REFERENCES borrowers(id)
        )";
        $pdo->exec($sql);
        echo "✅ Table 'loans' créée avec succès!<br>";
    } else {
        echo "ℹ️ La table 'loans' existe déjà.<br>";
        
        // Vérifier les colonnes de la table loans
        $columns = $pdo->query("SHOW COLUMNS FROM loans")->fetchAll(PDO::FETCH_ASSOC);
        $column_names = array_column($columns, 'Field');
        
        if (!in_array('borrower_id', $column_names)) {
            $pdo->exec("ALTER TABLE loans ADD COLUMN borrower_id INT NULL");
            echo "✅ Colonne 'borrower_id' ajoutée à la table loans!<br>";
        }
        
        if (!in_array('return_date', $column_names)) {
            $pdo->exec("ALTER TABLE loans ADD COLUMN return_date DATE NULL");
            echo "✅ Colonne 'return_date' ajoutée à la table loans!<br>";
        }
    }
    
    echo "<br><h3>🎉 Mise à jour terminée!</h3>";
    echo "<p>La base de données est maintenant prête pour les nouvelles fonctionnalités.</p>";
    
    echo "<h4>Résumé des modifications:</h4>";
    echo "<ul>";
    echo "<li>✅ Table 'items' avec tous les champs nécessaires</li>";
    echo "<li>✅ Table 'borrowers' pour les emprunteurs</li>";
    echo "<li>✅ Table 'loans' pour les prêts</li>";
    echo "<li>✅ Nouvelles colonnes pour les livres, revues et journaux</li>";
    echo "</ul>";
    
    echo "<h4>Prochaines étapes:</h4>";
    echo "<ul>";
    echo "<li><a href='public/login.php'>Se connecter au système</a></li>";
    echo "<li><a href='modules/items/add_book.php?lang=ar'>Ajouter des livres</a></li>";
    echo "<li><a href='modules/items/add_magazine.php?lang=ar'>Ajouter des revues</a></li>";
    echo "<li><a href='modules/items/add_newspaper.php?lang=ar'>Ajouter des journaux</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?> 