<?php
/**
 * Script de configuration de l'environnement
 * À exécuter une seule fois lors de l'installation
 */

echo "🔧 Configuration de l'environnement - Système de Bibliothèque\n";
echo "============================================================\n\n";

// Vérifier les prérequis
echo "📋 Vérification des prérequis...\n";

$requirements = [
    'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO Extension' => extension_loaded('pdo'),
    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
    'OpenSSL Extension' => extension_loaded('openssl'),
    'JSON Extension' => extension_loaded('json'),
    'Fileinfo Extension' => extension_loaded('fileinfo'),
    'Session Support' => function_exists('session_start'),
];

$allRequirementsMet = true;
foreach ($requirements as $requirement => $met) {
    $status = $met ? '✅' : '❌';
    echo "  $status $requirement\n";
    if (!$met) $allRequirementsMet = false;
}

if (!$allRequirementsMet) {
    echo "\n❌ Certains prérequis ne sont pas satisfaits. Veuillez les installer avant de continuer.\n";
    exit(1);
}

echo "\n✅ Tous les prérequis sont satisfaits!\n\n";

// Créer les dossiers nécessaires
echo "📁 Création des dossiers...\n";

$directories = [
    'logs',
    'uploads',
    'uploads/books',
    'uploads/magazines',
    'uploads/newspapers',
    'uploads/documents',
    'temp'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0700, true)) {
            echo "  ✅ Créé: $dir\n";
        } else {
            echo "  ❌ Erreur lors de la création de: $dir\n";
        }
    } else {
        echo "  ℹ️  Existe déjà: $dir\n";
    }
}

// Créer le fichier .env s'il n'existe pas
echo "\n🔐 Configuration de l'environnement...\n";

if (!file_exists('.env')) {
    $envContent = <<<ENV
# Configuration de l'environnement - Système de Bibliothèque
# Modifiez ces valeurs selon votre configuration

# Environnement (development/production)
APP_ENV=development
APP_DEBUG=true

# Base de données
DB_HOST=localhost
DB_NAME=library_db
DB_USER=root
DB_PASS=

# Sécurité
SESSION_SECRET=change_this_to_a_secure_random_string
CSRF_SECRET=change_this_to_another_secure_random_string

# Configuration des logs
LOG_LEVEL=INFO
LOG_FILE=logs/app.log

# Configuration des uploads
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=image/jpeg,image/png,image/gif,application/pdf,text/plain

# Configuration des sessions
SESSION_LIFETIME=3600
SESSION_REFRESH_TIME=1800

ENV;

    if (file_put_contents('.env', $envContent)) {
        echo "  ✅ Fichier .env créé\n";
        echo "  ⚠️  IMPORTANT: Modifiez les valeurs dans le fichier .env avant de continuer!\n";
    } else {
        echo "  ❌ Erreur lors de la création du fichier .env\n";
    }
} else {
    echo "  ℹ️  Fichier .env existe déjà\n";
}

// Créer le fichier .htaccess pour la protection
echo "\n🛡️  Configuration de la sécurité...\n";

if (!file_exists('.htaccess')) {
    $htaccessContent = <<<HTACCESS
# Protection du dossier racine
# Empêcher l'accès direct aux fichiers PHP en dehors de public/

# Bloquer l'accès à tous les fichiers PHP
<Files "*.php">
    Order Deny,Allow
    Deny from all
</Files>

# Autoriser seulement index.php dans public/
<Files "public/index.php">
    Order Allow,Deny
    Allow from all
</Files>

# Bloquer l'accès aux fichiers sensibles
<FilesMatch "\.(env|config|sql|log|md|txt)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Bloquer l'accès aux dossiers sensibles
<DirectoryMatch "^/(config|includes|modules|lang|logs)/">
    Order Deny,Allow
    Deny from all
</DirectoryMatch>

# Protection contre l'énumération de répertoires
Options -Indexes

# Headers de sécurité
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Protection contre les attaques par injection
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Bloquer les tentatives d'accès aux fichiers de configuration
    RewriteRule ^(config|includes|modules|lang)/ - [F,L]
    
    # Bloquer les tentatives d'accès aux fichiers de sauvegarde
    RewriteRule \.(bak|backup|old|tmp|temp)$ - [F,L]
    
    # Bloquer les tentatives d'accès aux fichiers de base de données
    RewriteRule \.(sql|db|sqlite)$ - [F,L]
</IfModule>

HTACCESS;

    if (file_put_contents('.htaccess', $htaccessContent)) {
        echo "  ✅ Fichier .htaccess créé\n";
    } else {
        echo "  ❌ Erreur lors de la création du fichier .htaccess\n";
    }
} else {
    echo "  ℹ️  Fichier .htaccess existe déjà\n";
}

// Vérifier la connectivité à la base de données
echo "\n🗄️  Test de connexion à la base de données...\n";

try {
    // Charger la configuration temporaire
    $config = [
        'host' => 'localhost',
        'dbname' => 'library_db',
        'username' => 'root',
        'password' => '',
    ];
    
    $dsn = "mysql:host={$config['host']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "  ✅ Connexion MySQL réussie\n";
    
    // Vérifier si la base de données existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['dbname']}'");
    if ($stmt->rowCount() > 0) {
        echo "  ✅ Base de données '{$config['dbname']}' existe\n";
    } else {
        echo "  ⚠️  Base de données '{$config['dbname']}' n'existe pas\n";
        echo "     Créez-la manuellement ou exécutez le script de création de base de données\n";
    }
    
} catch (PDOException $e) {
    echo "  ❌ Erreur de connexion MySQL: " . $e->getMessage() . "\n";
    echo "     Vérifiez vos paramètres de base de données dans le fichier .env\n";
}

// Vérifier les permissions des fichiers
echo "\n🔒 Vérification des permissions...\n";

$filesToCheck = [
    '.env' => '600',
    '.htaccess' => '644',
    'config/' => '755',
    'includes/' => '755',
    'logs/' => '700',
    'uploads/' => '700'
];

foreach ($filesToCheck as $file => $permission) {
    if (file_exists($file)) {
        $currentPerms = substr(sprintf('%o', fileperms($file)), -3);
        if ($currentPerms === $permission) {
            echo "  ✅ $file: $currentPerms\n";
        } else {
            echo "  ⚠️  $file: $currentPerms (recommandé: $permission)\n";
        }
    }
}

// Générer des clés de sécurité
echo "\n🔑 Génération des clés de sécurité...\n";

$sessionSecret = bin2hex(random_bytes(32));
$csrfSecret = bin2hex(random_bytes(32));

echo "  Session Secret: $sessionSecret\n";
echo "  CSRF Secret: $csrfSecret\n";
echo "  ⚠️  Copiez ces clés dans votre fichier .env\n";

// Instructions finales
echo "\n🎉 Configuration terminée!\n";
echo "============================================================\n\n";

echo "📋 Prochaines étapes:\n";
echo "1. Modifiez le fichier .env avec vos paramètres de base de données\n";
echo "2. Créez la base de données si elle n'existe pas\n";
echo "3. Exécutez les scripts de création des tables\n";
echo "4. Configurez votre serveur web pour pointer vers le dossier public/\n";
echo "5. Testez l'application en accédant à l'URL de votre serveur\n\n";

echo "🔒 Recommandations de sécurité:\n";
echo "- Changez les mots de passe par défaut\n";
echo "- Activez HTTPS en production\n";
echo "- Configurez des sauvegardes régulières\n";
echo "- Surveillez les logs d'erreur\n";
echo "- Maintenez PHP et les dépendances à jour\n\n";

echo "📚 Documentation: Consultez SECURITY_GUIDE.md pour plus d'informations\n";
echo "🐛 Support: En cas de problème, vérifiez les logs dans le dossier logs/\n\n";

echo "✅ Installation terminée avec succès!\n";
?> 