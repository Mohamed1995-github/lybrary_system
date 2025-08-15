<?php
// Script pour corriger tous les noms de colonnes dans le système
echo "Correction des noms de colonnes dans tous les fichiers...\n";

$files_to_fix = [
    'modules/items/statistics.php',
    'modules/items/list_grouped.php',
    'modules/items/add_newspaper.php',
    'modules/items/add_magazine.php',
    'modules/items/add_book.php',
    'modules/items/add_edit.php',
    'public/add_magazine_direct.php',
    'public/add_magazine.php',
    'public/add_book.php'
];

foreach ($files_to_fix as $file) {
    if (file_exists($file)) {
        echo "Fixing: $file\n";
        
        $content = file_get_contents($file);
        
        // Remplacer les anciens noms de colonnes
        $content = str_replace('copies_total', 'copies', $content);
        $content = str_replace('copies_in', 'available_copies', $content);
        
        // Écrire le contenu corrigé
        file_put_contents($file, $content);
        
        echo "✅ Fixed: $file\n";
    } else {
        echo "❌ File not found: $file\n";
    }
}

echo "\nCorrection terminée!\n";
?>
