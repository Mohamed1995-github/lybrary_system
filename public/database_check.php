<?php
/**
 * Script de vérification de la base de données et des permissions
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<h1>✅ Connexion à la base de données réussie</h1>";
} catch (PDOException $e) {
    die("<h1>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</h1>");
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Base de Données - Permissions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
            color: #1e293b;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .section h2 {
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .success { color: #059669; }
        .error { color: #dc2626; }
        .warning { color: #d97706; }
        .info { color: #2563eb; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
        }
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-ok { background: #dcfce7; color: #166534; }
        .status-error { background: #fef2f2; color: #dc2626; }
        .status-warning { background: #fef3c7; color: #d97706; }
        .code {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section">
            <h2><i class="fas fa-database"></i> Informations de la Base de Données</h2>
            <?php
            // Informations sur la base de données
            $stmt = $pdo->query("SELECT DATABASE() as db_name, VERSION() as version");
            $db_info = $stmt->fetch();
            echo "<p><strong>Base de données:</strong> " . $db_info['db_name'] . "</p>";
            echo "<p><strong>Version MySQL:</strong> " . $db_info['version'] . "</p>";
            ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-table"></i> Tables de la Base de Données</h2>
            <?php
            // Lister toutes les tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                echo "<p class='error'>❌ Aucune table trouvée dans la base de données</p>";
            } else {
                echo "<table>";
                echo "<tr><th>Table</th><th>Type</th><th>Lignes</th><th>Statut</th></tr>";
                
                foreach ($tables as $table) {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                    $count = $stmt->fetch()['count'];
                    
                    echo "<tr>";
                    echo "<td><strong>$table</strong></td>";
                    echo "<td>Table</td>";
                    echo "<td>$count</td>";
                    echo "<td><span class='status status-ok'>OK</span></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-users"></i> Utilisateurs et Rôles</h2>
            <?php
            // Vérifier la table users
            if (in_array('users', $tables)) {
                $stmt = $pdo->query("SELECT * FROM users ORDER BY id");
                $users = $stmt->fetchAll();
                
                if (empty($users)) {
                    echo "<p class='warning'>⚠️ Aucun utilisateur trouvé dans la table 'users'</p>";
                } else {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Nom d'utilisateur</th><th>Nom complet</th><th>Rôle</th><th>Téléphone</th></tr>";
                    
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>{$user['id']}</td>";
                        echo "<td>{$user['username']}</td>";
                        echo "<td>{$user['full_name']}</td>";
                        echo "<td><span class='status status-ok'>{$user['role']}</span></td>";
                        echo "<td>{$user['phone']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p class='error'>❌ Table 'users' non trouvée</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-user-cog"></i> Employés et Permissions</h2>
            <?php
            // Vérifier la table employees
            if (in_array('employees', $tables)) {
                $stmt = $pdo->query("SELECT * FROM employees ORDER BY id");
                $employees = $stmt->fetchAll();
                
                if (empty($employees)) {
                    echo "<p class='warning'>⚠️ Aucun employé trouvé dans la table 'employees'</p>";
                } else {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Nom</th><th>Fonction</th><th>Droits d'accès</th><th>Statut</th></tr>";
                    
                    foreach ($employees as $employee) {
                        $rights = explode(',', $employee['access_rights']);
                        $rights_display = implode(', ', array_slice($rights, 0, 3));
                        if (count($rights) > 3) {
                            $rights_display .= '...';
                        }
                        
                        echo "<tr>";
                        echo "<td>{$employee['id']}</td>";
                        echo "<td>{$employee['name']}</td>";
                        echo "<td>{$employee['function']}</td>";
                        echo "<td>$rights_display</td>";
                        echo "<td><span class='status status-ok'>Actif</span></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p class='error'>❌ Table 'employees' non trouvée</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-shield-alt"></i> Permissions et Droits d'Accès</h2>
            <?php
            // Vérifier les permissions
            if (in_array('permissions', $tables)) {
                $stmt = $pdo->query("SELECT * FROM permissions ORDER BY id");
                $permissions = $stmt->fetchAll();
                
                if (empty($permissions)) {
                    echo "<p class='warning'>⚠️ Aucune permission trouvée</p>";
                } else {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Nom de la Permission</th><th>Description</th></tr>";
                    
                    foreach ($permissions as $permission) {
                        echo "<tr>";
                        echo "<td>{$permission['id']}</td>";
                        echo "<td>{$permission['permission_name']}</td>";
                        echo "<td>{$permission['description']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p class='error'>❌ Table 'permissions' non trouvée</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-book"></i> Items et Contenu</h2>
            <?php
            // Vérifier la table items
            if (in_array('items', $tables)) {
                $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM items GROUP BY type");
                $items_by_type = $stmt->fetchAll();
                
                if (empty($items_by_type)) {
                    echo "<p class='warning'>⚠️ Aucun item trouvé dans la table 'items'</p>";
                } else {
                    echo "<table>";
                    echo "<tr><th>Type</th><th>Nombre</th></tr>";
                    
                    foreach ($items_by_type as $item) {
                        echo "<tr>";
                        echo "<td><strong>{$item['type']}</strong></td>";
                        echo "<td>{$item['count']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p class='error'>❌ Table 'items' non trouvée</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-tools"></i> Actions Recommandées</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <div style="background: #f0f9ff; padding: 1rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <h3><i class="fas fa-user-plus"></i> Créer un Utilisateur Admin</h3>
                    <p>Si aucun utilisateur admin n'existe, créez-en un pour avoir accès complet au système.</p>
                    <a href="create_admin_account.php" class="btn">Créer Admin</a>
                </div>
                
                <div style="background: #f0fdf4; padding: 1rem; border-radius: 8px; border-left: 4px solid #22c55e;">
                    <h3><i class="fas fa-database"></i> Vérifier les Permissions</h3>
                    <p>Vérifiez que les permissions sont correctement configurées pour chaque rôle.</p>
                    <a href="test_permissions_system.php" class="btn">Tester Permissions</a>
                </div>
                
                <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <h3><i class="fas fa-cog"></i> Configuration Système</h3>
                    <p>Configurez les paramètres du système et les droits d'accès par défaut.</p>
                    <a href="setup_environment.php" class="btn">Configurer</a>
                </div>
            </div>
        </div>

        <div class="section">
            <h2><i class="fas fa-code"></i> Code SQL pour Vérifier les Permissions</h2>
            <div class="code">
-- Vérifier les utilisateurs et leurs rôles
SELECT id, username, full_name, role FROM users;

-- Vérifier les employés et leurs droits
SELECT id, name, function, access_rights FROM employees;

-- Vérifier les permissions disponibles
SELECT * FROM permissions;

-- Vérifier les items par type
SELECT type, COUNT(*) as count FROM items GROUP BY type;

-- Vérifier les utilisateurs avec rôle admin
SELECT * FROM users WHERE role = 'admin';

-- Vérifier les employés avec fonction de directeur
SELECT * FROM employees WHERE function LIKE '%مدير%' OR function LIKE '%Directeur%';
            </div>
        </div>

        <div class="section">
            <h2><i class="fas fa-link"></i> Liens Utiles</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="dashboard.php" class="btn">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de Bord
                </a>
                <a href="add_pages.php" class="btn">
                    <i class="fas fa-plus"></i>
                    Pages d'Ajout
                </a>
                <a href="login.php" class="btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Connexion
                </a>
                <a href="simple_router.php?module=items&action=list&type=book&lang=ar" class="btn">
                    <i class="fas fa-list"></i>
                    Liste des Livres
                </a>
            </div>
        </div>
    </div>
</body>
</html> 