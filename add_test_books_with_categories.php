<?php
echo "=== Ajout de livres de test avec catégories ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    
    // Livres de test avec différentes catégories
    $test_books = [
        ['title' => 'كتاب الرياضيات', 'category' => 'رياضيات', 'publisher' => 'دار النشر العلمية', 'lang' => 'ar'],
        ['title' => 'كتاب الفيزياء', 'category' => 'فيزياء', 'publisher' => 'دار النشر العلمية', 'lang' => 'ar'],
        ['title' => 'كتاب الكيمياء', 'category' => 'كيمياء', 'publisher' => 'دار النشر العلمية', 'lang' => 'ar'],
        ['title' => 'كتاب التاريخ', 'category' => 'تاريخ', 'publisher' => 'دار النشر الأدبية', 'lang' => 'ar'],
        ['title' => 'كتاب الأدب', 'category' => 'أدب', 'publisher' => 'دار النشر الأدبية', 'lang' => 'ar'],
        ['title' => 'كتاب الفلسفة', 'category' => 'فلسفة', 'publisher' => 'دار النشر الأدبية', 'lang' => 'ar'],
        ['title' => 'Livre de Mathématiques', 'category' => 'Mathématiques', 'publisher' => 'Éditions Scientifiques', 'lang' => 'fr'],
        ['title' => 'Livre de Physique', 'category' => 'Physique', 'publisher' => 'Éditions Scientifiques', 'lang' => 'fr'],
        ['title' => 'Livre de Chimie', 'category' => 'Chimie', 'publisher' => 'Éditions Scientifiques', 'lang' => 'fr'],
        ['title' => 'Livre d\'Histoire', 'category' => 'Histoire', 'publisher' => 'Éditions Littéraires', 'lang' => 'fr'],
        ['title' => 'Livre de Littérature', 'category' => 'Littérature', 'publisher' => 'Éditions Littéraires', 'lang' => 'fr'],
        ['title' => 'Livre de Philosophie', 'category' => 'Philosophie', 'publisher' => 'Éditions Littéraires', 'lang' => 'fr']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, category, publisher, copies, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($test_books as $book) {
        $stmt->execute([
            $book['lang'],
            'book',
            $book['title'],
            $book['category'],
            $book['publisher'],
            2, // copies
            2  // available_copies
        ]);
        echo "✅ Ajouté: {$book['title']} (Catégorie: {$book['category']})\n";
    }
    
    echo "\n✅ Tous les livres de test ont été ajoutés avec succès!\n";
    
    // Afficher les catégories disponibles
    echo "\nCatégories disponibles:\n";
    $stmt = $pdo->query("SELECT DISTINCT category FROM items WHERE type = 'book' AND category IS NOT NULL AND category != '' ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($categories as $category) {
        echo "- $category\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
?>
