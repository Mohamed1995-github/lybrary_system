<?php
/**
 * Fichier d'accès direct pour tester le système
 * Ce fichier permet de diagnostiquer les problèmes d'accès
 */

// Configuration de base
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier les prérequis
$requirements = [
    'PHP Version' => [
        'required' => '7.4+',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ],
    'PDO Extension' => [
        'required' => 'Enabled',
        'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
        'status' => extension_loaded('pdo')
    ],
    'PDO MySQL Extension' => [
        'required' => 'Enabled',
        'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
        'status' => extension_loaded('pdo_mysql')
    ],
    'Session Support' => [
        'required' => 'Enabled',
        'current' => function_exists('session_start') ? 'Enabled' : 'Disabled',
        'status' => function_exists('session_start')
    ]
];

// Vérifier les fichiers requis
$requiredFiles = [
    'config/database.php' => '../config/database.php',
    'includes/auth.php' => '../includes/auth.php',
    'includes/permissions.php' => '../includes/permissions.php',
    'public/login.php' => 'login.php',
    'public/dashboard.php' => 'dashboard.php'
];

$fileStatus = [];
foreach ($requiredFiles as $file => $path) {
    $fileStatus[$file] = [
        'required' => 'Exists',
        'current' => file_exists($path) ? 'Exists' : 'Missing',
        'status' => file_exists($path)
    ];
}

// Tester la connexion à la base de données
$dbStatus = 'Not Tested';
$dbError = '';
try {
    if (file_exists('../config/database.php')) {
        require_once '../config/database.php';
        $db = Database::getInstance();
        $connection = $db->getConnection();
        if ($connection) {
            $dbStatus = 'Connected';
        } else {
            $dbStatus = 'Failed';
            $dbError = 'Unable to establish connection';
        }
    } else {
        $dbStatus = 'Config Missing';
        $dbError = 'Database configuration file not found';
    }
} catch (Exception $e) {
    $dbStatus = 'Error';
    $dbError = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic du Système - Library System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            font-size: 1.125rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 2rem;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section h2 {
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
        
        .status-warning {
            color: #d97706;
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
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
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
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.5rem;
            padding: 1rem;
            color: #dc2626;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-diagnose"></i> Diagnostic du Système</h1>
            <p>Vérification de l'état du système de gestion de bibliothèque</p>
        </div>
        
        <div class="content">
            <!-- Prérequis système -->
            <div class="section">
                <h2><i class="fas fa-server"></i> Prérequis Système</h2>
                <div class="status-grid">
                    <?php foreach ($requirements as $name => $requirement): ?>
                    <div class="status-item">
                        <h3><?= htmlspecialchars($name) ?></h3>
                        <div class="status-details">
                            <div class="status-label">Requis:</div>
                            <div class="status-value"><?= htmlspecialchars($requirement['required']) ?></div>
                            <div class="status-label">Actuel:</div>
                            <div class="status-value <?= $requirement['status'] ? 'status-success' : 'status-error' ?>">
                                <?= htmlspecialchars($requirement['current']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Fichiers requis -->
            <div class="section">
                <h2><i class="fas fa-file-code"></i> Fichiers Requis</h2>
                <div class="status-grid">
                    <?php foreach ($fileStatus as $file => $status): ?>
                    <div class="status-item">
                        <h3><?= htmlspecialchars($file) ?></h3>
                        <div class="status-details">
                            <div class="status-label">Requis:</div>
                            <div class="status-value"><?= htmlspecialchars($status['required']) ?></div>
                            <div class="status-label">Actuel:</div>
                            <div class="status-value <?= $status['status'] ? 'status-success' : 'status-error' ?>">
                                <?= htmlspecialchars($status['current']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Base de données -->
            <div class="section">
                <h2><i class="fas fa-database"></i> Base de Données</h2>
                <div class="status-item">
                    <h3>Connexion à la Base de Données</h3>
                    <div class="status-details">
                        <div class="status-label">Statut:</div>
                        <div class="status-value <?= $dbStatus === 'Connected' ? 'status-success' : 'status-error' ?>">
                            <?= htmlspecialchars($dbStatus) ?>
                        </div>
                    </div>
                    <?php if ($dbError): ?>
                    <div class="error-message">
                        <strong>Erreur:</strong> <?= htmlspecialchars($dbError) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="section">
                <h2><i class="fas fa-tools"></i> Actions</h2>
                <div class="actions">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Page de Connexion
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="employee_login.php" class="btn btn-secondary">
                        <i class="fas fa-user-tie"></i> Connexion Employé
                    </a>
                    <a href="index.php" class="btn btn-success">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </div>
            </div>
            
            <!-- Informations de débogage -->
            <div class="section">
                <h2><i class="fas fa-info-circle"></i> Informations de Débogage</h2>
                <div class="status-item">
                    <h3>Informations Système</h3>
                    <div class="status-details">
                        <div class="status-label">Document Root:</div>
                        <div class="status-value"><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') ?></div>
                        <div class="status-label">Script Path:</div>
                        <div class="status-value"><?= htmlspecialchars(__FILE__) ?></div>
                        <div class="status-label">Session ID:</div>
                        <div class="status-value"><?= htmlspecialchars(session_id() ?: 'None') ?></div>
                        <div class="status-label">User Agent:</div>
                        <div class="status-value"><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 