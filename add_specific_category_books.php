<?php
echo "=== Ajout de livres avec catégories spécifiques ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    
    // Livres avec les catégories spécifiques demandées
    $specific_books = [
        // Livres arabes
        ['title' => 'مبادئ الإدارة الحديثة', 'category' => 'إدارة', 'publisher' => 'دار النشر الإدارية', 'lang' => 'ar'],
        ['title' => 'أساسيات الاقتصاد', 'category' => 'اقتصاد', 'publisher' => 'دار النشر الاقتصادية', 'lang' => 'ar'],
        ['title' => 'القانون المدني', 'category' => 'قانون', 'publisher' => 'دار النشر القانونية', 'lang' => 'ar'],
        ['title' => 'الدبلوماسية الدولية', 'category' => 'دبلوماسية', 'publisher' => 'دار النشر الدبلوماسية', 'lang' => 'ar'],
        ['title' => 'إدارة الموارد البشرية', 'category' => 'إدارة', 'publisher' => 'دار النشر الإدارية', 'lang' => 'ar'],
        ['title' => 'الاقتصاد الكلي', 'category' => 'اقتصاد', 'publisher' => 'دار النشر الاقتصادية', 'lang' => 'ar'],
        ['title' => 'القانون الدستوري', 'category' => 'قانون', 'publisher' => 'دار النشر القانونية', 'lang' => 'ar'],
        ['title' => 'العلاقات الدبلوماسية', 'category' => 'دبلوماسية', 'publisher' => 'دار النشر الدبلوماسية', 'lang' => 'ar'],
        
        // Livres français
        ['title' => 'Principes de Gestion Moderne', 'category' => 'Administration', 'publisher' => 'Éditions Administratives', 'lang' => 'fr'],
        ['title' => 'Fondements de l\'Économie', 'category' => 'Économie', 'publisher' => 'Éditions Économiques', 'lang' => 'fr'],
        ['title' => 'Droit Civil', 'category' => 'Droit', 'publisher' => 'Éditions Juridiques', 'lang' => 'fr'],
        ['title' => 'Diplomatie Internationale', 'category' => 'Diplomatie', 'publisher' => 'Éditions Diplomatiques', 'lang' => 'fr'],
        ['title' => 'Gestion des Ressources Humaines', 'category' => 'Administration', 'publisher' => 'Éditions Administratives', 'lang' => 'fr'],
        ['title' => 'Économie Macro', 'category' => 'Économie', 'publisher' => 'Éditions Économiques', 'lang' => 'fr'],
        ['title' => 'Droit Constitutionnel', 'category' => 'Droit', 'publisher' => 'Éditions Juridiques', 'lang' => 'fr'],
        ['title' => 'Relations Diplomatiques', 'category' => 'Diplomatie', 'publisher' => 'Éditions Diplomatiques', 'lang' => 'fr']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, category, publisher, copies, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($specific_books as $book) {
        $stmt->execute([
            $book['lang'],
            'book',
            $book['title'],
            $book['category'],
            $book['publisher'],
            3, // copies
            3  // available_copies
        ]);
        echo "✅ Ajouté: {$book['title']} (Catégorie: {$book['category']})\n";
    }
    
    echo "\n✅ Tous les livres avec catégories spécifiques ont été ajoutés!\n";
    
    // Afficher les catégories disponibles
    echo "\nCatégories disponibles pour les livres:\n";
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





