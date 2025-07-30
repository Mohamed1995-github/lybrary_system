<?php
/* test_system.php - Script de test pour vérifier le système */
require_once 'config/db.php';

echo "<h2>🧪 Test du Système de Gestion de Bibliothèque</h2>";

try {
    // Test 1: Connexion à la base de données
    echo "<h3>✅ Test 1: Connexion à la base de données</h3>";
    $pdo->query("SELECT 1");
    echo "✓ Connexion réussie<br>";
    
    // Test 2: Vérification de la structure de la table items
    echo "<h3>✅ Test 2: Structure de la table items</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM items")->fetchAll(PDO::FETCH_ASSOC);
    $column_names = array_column($columns, 'Field');
    
    $required_columns = [
        'id', 'lang', 'type', 'title', 'author', 'publisher', 'year_pub', 
        'classification', 'copies_total', 'copies_in', 'book_type', 
        'magazine_name', 'volume_number', 'newspaper_number', 'edition'
    ];
    
    foreach ($required_columns as $column) {
        if (in_array($column, $column_names)) {
            echo "✓ Colonne '$column' existe<br>";
        } else {
            echo "❌ Colonne '$column' manquante<br>";
        }
    }
    
    // Test 3: Vérification des tables
    echo "<h3>✅ Test 3: Tables du système</h3>";
    $tables = ['items', 'borrowers', 'loans'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' existe<br>";
        } else {
            echo "❌ Table '$table' manquante<br>";
        }
    }
    
    // Test 4: Test d'insertion d'un livre de test
    echo "<h3>✅ Test 4: Test d'insertion</h3>";
    
    // Supprimer l'ancien test s'il existe
    $pdo->exec("DELETE FROM items WHERE title = 'Livre de Test'");
    
    $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, author, publisher, year_pub, classification, copies_total, copies_in, book_type, edition) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute(['fr', 'book', 'Livre de Test', 'Auteur Test', 'Éditeur Test', 2023, 'TEST001', 1, 1, 'reference', '1ère édition']);
    
    if ($result) {
        echo "✓ Insertion réussie<br>";
        
        // Test de récupération
        $stmt = $pdo->prepare("SELECT * FROM items WHERE title = ?");
        $stmt->execute(['Livre de Test']);
        $book = $stmt->fetch();
        
        if ($book) {
            echo "✓ Récupération réussie - ID: {$book['id']}<br>";
            
            // Test de mise à jour
            $stmt = $pdo->prepare("UPDATE items SET author = ? WHERE id = ?");
            $result = $stmt->execute(['Auteur Modifié', $book['id']]);
            
            if ($result) {
                echo "✓ Mise à jour réussie<br>";
            } else {
                echo "❌ Échec de la mise à jour<br>";
            }
            
            // Nettoyer le test
            $pdo->exec("DELETE FROM items WHERE id = {$book['id']}");
            echo "✓ Nettoyage effectué<br>";
        } else {
            echo "❌ Échec de la récupération<br>";
        }
    } else {
        echo "❌ Échec de l'insertion<br>";
    }
    
    // Test 5: Vérification des fichiers PHP
    echo "<h3>✅ Test 5: Fichiers PHP</h3>";
    $files = [
        'modules/items/add_book.php',
        'modules/items/add_magazine.php', 
        'modules/items/add_newspaper.php',
        'modules/items/list.php',
        'modules/items/add_edit.php',
        'config/db.php'
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $syntax_check = shell_exec("php -l $file 2>&1");
            if (strpos($syntax_check, 'No syntax errors') !== false) {
                echo "✓ $file - Syntaxe OK<br>";
            } else {
                echo "❌ $file - Erreur de syntaxe<br>";
            }
        } else {
            echo "❌ $file - Fichier manquant<br>";
        }
    }
    
    echo "<h3>🎉 Tests terminés avec succès !</h3>";
    echo "<p>Le système est prêt à être utilisé.</p>";
    echo "<p><a href='public/login.php'>Se connecter au système</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erreur de base de données</h3>";
    echo "Erreur: " . $e->getMessage();
} catch (Exception $e) {
    echo "<h3>❌ Erreur générale</h3>";
    echo "Erreur: " . $e->getMessage();
}
?> 