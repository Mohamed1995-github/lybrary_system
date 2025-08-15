<?php
echo "=== Test du filtrage par catégorie ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    
    // Test 1: Vérifier les catégories arabes
    echo "\n1. Catégories arabes disponibles:\n";
    $categories_ar = ['إدارة', 'اقتصاد', 'قانون', 'دبلوماسية'];
    foreach ($categories_ar as $cat) {
        echo "- $cat\n";
    }
    
    // Test 2: Vérifier les catégories françaises
    echo "\n2. Catégories françaises disponibles:\n";
    $categories_fr = ['Administration', 'Économie', 'Droit', 'Diplomatie'];
    foreach ($categories_fr as $cat) {
        echo "- $cat\n";
    }
    
    // Test 3: Vérifier les livres par catégorie arabe
    echo "\n3. Livres par catégorie (Arabe):\n";
    foreach ($categories_ar as $category) {
        $stmt = $pdo->prepare("SELECT title, category FROM items WHERE lang = 'ar' AND type = 'book' AND category = ?");
        $stmt->execute([$category]);
        $books = $stmt->fetchAll();
        
        echo "\nCatégorie: $category\n";
        if (empty($books)) {
            echo "  - Aucun livre trouvé\n";
        } else {
            foreach ($books as $book) {
                echo "  - {$book['title']}\n";
            }
        }
    }
    
    // Test 4: Vérifier les livres par catégorie française
    echo "\n4. Livres par catégorie (Français):\n";
    foreach ($categories_fr as $category) {
        $stmt = $pdo->prepare("SELECT title, category FROM items WHERE lang = 'fr' AND type = 'book' AND category = ?");
        $stmt->execute([$category]);
        $books = $stmt->fetchAll();
        
        echo "\nCatégorie: $category\n";
        if (empty($books)) {
            echo "  - Aucun livre trouvé\n";
        } else {
            foreach ($books as $book) {
                echo "  - {$book['title']}\n";
            }
        }
    }
    
    // Test 5: Simuler le filtrage
    echo "\n5. Test de simulation du filtrage:\n";
    $test_categories = ['إدارة', 'Administration'];
    foreach ($test_categories as $test_cat) {
        $lang = ($test_cat == 'إدارة') ? 'ar' : 'fr';
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE lang = ? AND type = 'book' AND category = ?");
        $stmt->execute([$lang, $test_cat]);
        $result = $stmt->fetch();
        echo "- Catégorie '$test_cat' ($lang): {$result['count']} livre(s)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
?>





