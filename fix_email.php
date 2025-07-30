<?php
/**
 * Script pour corriger l'email de l'admin
 * Script to fix admin email
 */

require_once 'includes/auth.php';

echo "=== CORRECTION EMAIL ADMIN ===\n\n";

if (!isset($_SESSION['uid'])) {
    echo "❌ Pas connecté\n";
    exit;
}

try {
    // Mettre à jour l'email
    $updateStmt = $pdo->prepare("UPDATE employees SET email = ? WHERE id = ?");
    $result = $updateStmt->execute(['admin@library.com', $_SESSION['uid']]);

    if ($result) {
        echo "✅ Email corrigé: admin@library.com\n";
        $_SESSION['email'] = 'admin@library.com';
    } else {
        echo "❌ Erreur lors de la mise à jour\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== CORRECTION TERMINÉE ===\n";
?> 