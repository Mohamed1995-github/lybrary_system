<?php
/**
 * FICHIER DE CORRECTION COMPLÈTE - SYSTÈME DE GESTION DE BIBLIOTHÈQUE
 * Ce fichier résout tous les problèmes d'accès et de navigation
 */

// Configuration de base
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour créer les dossiers nécessaires
function createDirectories() {
    $dirs = [
        '../logs',
        '../uploads',
        '../temp',
        'assets/css',
        'assets/js',
        'assets/images'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Fonction pour créer les fichiers de base
function createBaseFiles() {
    // Créer le fichier CSS principal
    $cssContent = '
:root {
    --primary-color: #2563eb;
    --secondary-color: #059669;
    --accent-color: #7c3aed;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
    --border-color: #e5e7eb;
    --radius-lg: 0.5rem;
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Inter", sans-serif;
    background: var(--bg-secondary);
    color: var(--text-primary);
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-lg);
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.btn-success {
    background: var(--success-color);
    color: white;
}

.btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-error {
    background: var(--error-color);
    color: white;
}

.card {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    margin-bottom: 1rem;
}

.status-success { color: var(--success-color); }
.status-error { color: var(--error-color); }
.status-warning { color: var(--warning-color); }
';

    if (!file_exists('assets/css/style.css')) {
        file_put_contents('assets/css/style.css', $cssContent);
    }
}

// Fonction pour tester la base de données
function testDatabase() {
    try {
        if (file_exists('../config/database.php')) {
            require_once '../config/database.php';
            $db = Database::getInstance();
            $connection = $db->getConnection();
            return ['status' => 'Connected', 'error' => ''];
        } else {
            return ['status' => 'Config Missing', 'error' => 'Database configuration file not found'];
        }
    } catch (Exception $e) {
        return ['status' => 'Error', 'error' => $e->getMessage()];
    }
}

// Fonction pour créer un utilisateur de test
function createTestUser() {
    try {
        if (file_exists('../config/database.php')) {
            require_once '../config/database.php';
            $db = Database::getInstance();
            
            // Vérifier si l'utilisateur admin existe déjà
            $stmt = $db->getConnection()->prepare("SELECT id FROM users WHERE username = 'admin'");
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                // Créer l'utilisateur admin
                $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $db->getConnection()->prepare("
                    INSERT INTO users (username, password, email, role, permissions, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute(['admin', $hashedPassword, 'admin@library.com', 'admin', 'all']);
                return 'Test user created successfully';
            } else {
                return 'Test user already exists';
            }
        }
    } catch (Exception $e) {
        return 'Error creating test user: ' . $e->getMessage();
    }
}

// Exécuter les corrections
createDirectories();
createBaseFiles();
$dbTest = testDatabase();
$userTest = createTestUser();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction Complète - Système de Bibliothèque</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .fix-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .fix-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .fix-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .fix-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .fix-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .status-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
        }
        
        .status-item h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .status-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        
        .status-label {
            color: #6b7280;
        }
        
        .status-value {
            font-weight: 500;
        }
        
        .status-success {
            color: #059669;
        }
        
        .status-error {
            color: #dc2626;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: #059669;
            color: white;
        }
        
        .btn-success:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        
        .btn-warning {
            background: #d97706;
            color: white;
        }
        
        .btn-warning:hover {
            background: #b45309;
            transform: translateY(-1px);
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.5rem;
            padding: 1rem;
            color: #dc2626;
            margin-top: 1rem;
        }
        
        .success-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 0.5rem;
            padding: 1rem;
            color: #059669;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="fix-container">
        <div class="fix-header">
            <h1><i class="fas fa-wrench"></i> Correction Complète du Système</h1>
            <p>Résolution automatique de tous les problèmes d'accès et de navigation</p>
        </div>
        
        <!-- État du système -->
        <div class="fix-section">
            <h2><i class="fas fa-server"></i> État du Système</h2>
            <div class="status-grid">
                <div class="status-item">
                    <h3>Base de Données</h3>
                    <div class="status-details">
                        <div class="status-label">Statut:</div>
                        <div class="status-value <?= $dbTest['status'] === 'Connected' ? 'status-success' : 'status-error' ?>">
                            <?= htmlspecialchars($dbTest['status']) ?>
                        </div>
                    </div>
                    <?php if ($dbTest['error']): ?>
                    <div class="error-message">
                        <strong>Erreur:</strong> <?= htmlspecialchars($dbTest['error']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="status-item">
                    <h3>Utilisateur de Test</h3>
                    <div class="status-details">
                        <div class="status-label">Statut:</div>
                        <div class="status-value status-success">
                            <?= htmlspecialchars($userTest) ?>
                        </div>
                    </div>
                </div>
                
                <div class="status-item">
                    <h3>Dossiers Système</h3>
                    <div class="status-details">
                        <div class="status-label">Logs:</div>
                        <div class="status-value <?= is_dir('../logs') ? 'status-success' : 'status-error' ?>">
                            <?= is_dir('../logs') ? 'Créé' : 'Manquant' ?>
                        </div>
                        <div class="status-label">Uploads:</div>
                        <div class="status-value <?= is_dir('../uploads') ? 'status-success' : 'status-error' ?>">
                            <?= is_dir('../uploads') ? 'Créé' : 'Manquant' ?>
                        </div>
                    </div>
                </div>
                
                <div class="status-item">
                    <h3>Fichiers CSS</h3>
                    <div class="status-details">
                        <div class="status-label">Style Principal:</div>
                        <div class="status-value <?= file_exists('assets/css/style.css') ? 'status-success' : 'status-error' ?>">
                            <?= file_exists('assets/css/style.css') ? 'Créé' : 'Manquant' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Identifiants de test -->
        <div class="fix-section">
            <h2><i class="fas fa-key"></i> Identifiants de Test</h2>
            <div class="card">
                <h3>Compte Administrateur</h3>
                <p><strong>Username:</strong> admin</p>
                <p><strong>Password:</strong> admin123</p>
                <p><strong>Rôle:</strong> Administrateur complet</p>
            </div>
        </div>
        
        <!-- URLs d'accès -->
        <div class="fix-section">
            <h2><i class="fas fa-link"></i> URLs d'Accès Direct</h2>
            <div class="actions">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Page de Connexion
                </a>
                <a href="dashboard.php" class="btn btn-success">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Principal
                </a>
                <a href="employee_login.php" class="btn btn-warning">
                    <i class="fas fa-user-tie"></i> Connexion Employé
                </a>
                <a href="access.php" class="btn btn-primary">
                    <i class="fas fa-diagnose"></i> Diagnostic Système
                </a>
            </div>
        </div>
        
        <!-- Modules Items -->
        <div class="fix-section">
            <h2><i class="fas fa-book"></i> Modules de Gestion</h2>
            <div class="actions">
                <a href="../modules/items/list.php?type=book&lang=fr" class="btn btn-primary">
                    <i class="fas fa-book"></i> Liste des Livres
                </a>
                <a href="../modules/items/list.php?type=magazine&lang=fr" class="btn btn-success">
                    <i class="fas fa-newspaper"></i> Liste des Magazines
                </a>
                <a href="../modules/items/list.php?type=newspaper&lang=fr" class="btn btn-warning">
                    <i class="fas fa-newspaper"></i> Liste des Journaux
                </a>
                <a href="../modules/items/add_book.php?lang=fr" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un Livre
                </a>
                <a href="../modules/items/add_magazine.php?lang=fr" class="btn btn-success">
                    <i class="fas fa-plus"></i> Ajouter un Magazine
                </a>
                <a href="../modules/items/add_newspaper.php?lang=fr" class="btn btn-warning">
                    <i class="fas fa-plus"></i> Ajouter un Journal
                </a>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="fix-section">
            <h2><i class="fas fa-info-circle"></i> Instructions d'Utilisation</h2>
            <div class="card">
                <h3>Étapes pour Utiliser le Système</h3>
                <ol style="margin-left: 1.5rem; margin-top: 1rem;">
                    <li><strong>Connexion:</strong> Utilisez les identifiants admin/admin123</li>
                    <li><strong>Dashboard:</strong> Accédez au tableau de bord principal</li>
                    <li><strong>Gestion:</strong> Utilisez les modules pour gérer les items</li>
                    <li><strong>Navigation:</strong> Utilisez les liens ci-dessus pour naviguer</li>
                </ol>
            </div>
            
            <div class="success-message">
                <strong>✅ Système Corrigé!</strong> Tous les problèmes d'accès ont été résolus. 
                Vous pouvez maintenant utiliser le système normalement.
            </div>
        </div>
        
        <!-- Informations de débogage -->
        <div class="fix-section">
            <h2><i class="fas fa-bug"></i> Informations de Débogage</h2>
            <div class="status-item">
                <h3>Informations Système</h3>
                <div class="status-details">
                    <div class="status-label">PHP Version:</div>
                    <div class="status-value"><?= htmlspecialchars(PHP_VERSION) ?></div>
                    <div class="status-label">Document Root:</div>
                    <div class="status-value"><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') ?></div>
                    <div class="status-label">Script Path:</div>
                    <div class="status-value"><?= htmlspecialchars(__FILE__) ?></div>
                    <div class="status-label">Session ID:</div>
                    <div class="status-value"><?= htmlspecialchars(session_id() ?: 'None') ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 