<?php
require_once __DIR__ . '/config/db.php';


$username = 'admin';
$password_plain = 'admin123';
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
$full_name = 'Administrateur';
$role = 'admin';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    $update = $pdo->prepare("UPDATE users SET password = ?, full_name = ?, role = ? WHERE username = ?");
    $update->execute([$password_hash, $full_name, $role, $username]);
    echo "✅ Utilisateur 'admin' mis à jour avec succès. Mot de passe = admin123";
} else {
    $insert = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
    $insert->execute([$username, $password_hash, $full_name, $role]);
    echo "✅ Utilisateur 'admin' créé avec succès. Mot de passe = admin123";
}
?>
