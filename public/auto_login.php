<?php
/**
 * Script de connexion automatique pour faciliter les tests
 * Auto-login script for testing
 */

session_start();

// Configuration de la base de données
require_once '../config/db.php';

try {
    // Rechercher le compte admin
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE number = 'ADMIN001' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Configurer la session
        $_SESSION['uid'] = $admin['id'];
        $_SESSION['name'] = $admin['name'];
        $_SESSION['email'] = $admin['email'] ?: 'admin@library.com';
        $_SESSION['role'] = 'admin';
        $_SESSION['employee_name'] = $admin['name'];
        $_SESSION['employee_function'] = $admin['function'];
        $_SESSION['employee_rights'] = $admin['access_rights'] ?: 'all';
        $_SESSION['lang'] = $_GET['lang'] ?? 'ar';
        $_SESSION['login_time'] = time();
        
        // Message de succès
        $message = "✅ Connexion automatique réussie!";
        $details = [
            'ID' => $admin['id'],
            'Nom' => $admin['name'],
            'Fonction' => $admin['function'],
            'Droits' => $_SESSION['employee_rights']
        ];
        
    } else {
        $message = "❌ Compte admin non trouvé. Création en cours...";
        
        // Créer un compte admin
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO employees (number, name, lang, function, access_rights, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'ADMIN001',
            'مدير النظام',
            'ar',
            'مدير النظام',
            'all',
            'admin@library.com',
            $hashedPassword
        ]);
        
        $adminId = $pdo->lastInsertId();
        
        // Configurer la session
        $_SESSION['uid'] = $adminId;
        $_SESSION['name'] = 'مدير النظام';
        $_SESSION['email'] = 'admin@library.com';
        $_SESSION['role'] = 'admin';
        $_SESSION['employee_name'] = 'مدير النظام';
        $_SESSION['employee_function'] = 'مدير النظام';
        $_SESSION['employee_rights'] = 'all';
        $_SESSION['lang'] = $_GET['lang'] ?? 'ar';
        $_SESSION['login_time'] = time();
        
        $message = "✅ Compte admin créé et connexion réussie!";
        $details = [
            'ID' => $adminId,
            'Nom' => 'مدير النظام',
            'Fonction' => 'مدير النظام',
            'Numéro' => 'ADMIN001',
            'Mot de passe' => 'admin123'
        ];
    }
    
} catch (PDOException $e) {
    $message = "❌ Erreur de base de données: " . $e->getMessage();
    $details = [];
}

$lang = $_SESSION['lang'] ?? 'ar';
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Automatique - Système de Bibliothèque</title>
    <link rel="stylesheet" href="assets/css/style.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, sans-serif;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            margin: 2rem;
        }
        h1 {
            color: #2563eb;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .success {
            background: #10b981;
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
        }
        .error {
            background: #ef4444;
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
        }
        .details {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .details p {
            margin: 0.5rem 0;
            color: #374151;
        }
        .details strong {
            color: #1f2937;
            display: inline-block;
            min-width: 120px;
        }
        .buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }
        .countdown {
            text-align: center;
            color: #6b7280;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Connexion Automatique</h1>
        
        <?php if (strpos($message, '✅') !== false): ?>
            <div class="success"><?= $message ?></div>
        <?php else: ?>
            <div class="error"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if (!empty($details)): ?>
            <div class="details">
                <h3>📋 Informations de connexion:</h3>
                <?php foreach ($details as $key => $value): ?>
                    <p><strong><?= $key ?>:</strong> <?= htmlspecialchars($value) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="buttons">
            <a href="dashboard.php?lang=ar" class="btn btn-primary">
                <span>📚</span>
                <span>Aller au Dashboard (AR)</span>
            </a>
            <a href="dashboard.php?lang=fr" class="btn btn-primary">
                <span>📚</span>
                <span>Aller au Dashboard (FR)</span>
            </a>
            <a href="employee_login.php" class="btn btn-secondary">
                <span>🔑</span>
                <span>Page de connexion</span>
            </a>
        </div>
        
        <div class="countdown" id="countdown">
            Redirection automatique dans <span id="timer">5</span> secondes...
        </div>
    </div>
    
    <script>
        // Redirection automatique après 5 secondes
        let seconds = 5;
        const timer = document.getElementById('timer');
        const countdown = setInterval(() => {
            seconds--;
            if (timer) timer.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = 'dashboard.php?lang=<?= $lang ?>';
            }
        }, 1000);
    </script>
</body>
</html>
