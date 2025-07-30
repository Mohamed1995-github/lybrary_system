# 🛡️ Guide de Sécurisation - Système de Bibliothèque

## 📁 Structure de Fichiers Sécurisée

### Structure Recommandée
```
library_system/
├── public/                    # Dossier accessible publiquement
│   ├── index.php             # Point d'entrée unique
│   ├── assets/               # Ressources statiques
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── .htaccess             # Protection du dossier
├── app/                      # Logique métier (non accessible)
│   ├── config/              # Configuration
│   ├── includes/            # Fonctions et classes
│   ├── modules/             # Modules fonctionnels
│   └── lang/                # Fichiers de traduction
├── logs/                    # Logs système
├── uploads/                 # Fichiers uploadés (sécurisé)
└── vendor/                  # Dépendances (si applicable)
```

## 🔐 Sécurité de Base

### 1. Protection des Dossiers
```apache
# .htaccess dans le dossier racine
<Files "*.php">
    Order Deny,Allow
    Deny from all
</Files>

<Files "index.php">
    Order Allow,Deny
    Allow from all
</Files>

# Empêcher l'accès aux fichiers sensibles
<FilesMatch "\.(env|config|sql|log)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

### 2. Configuration Sécurisée
```php
// config/database.php
<?php
return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'library_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### 3. Gestion des Sessions Sécurisée
```php
// includes/session.php
<?php
// Configuration sécurisée des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Régénération périodique de l'ID de session
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
```

## 🔒 Authentification et Autorisation

### 1. Système d'Authentification
```php
// includes/auth.php
<?php
class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Créer une session sécurisée
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            
            // Log de connexion
            $this->logLogin($user['id'], true);
            return true;
        }
        
        $this->logLogin($username, false);
        return false;
    }
    
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['login_time']) &&
               (time() - $_SESSION['login_time']) < 3600; // 1 heure
    }
    
    public function logout() {
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }
}
```

### 2. Système d'Autorisation
```php
// includes/permissions.php
<?php
class Permissions {
    private $pdo;
    private $userRights = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadUserRights();
    }
    
    private function loadUserRights() {
        if (!isset($_SESSION['user_id'])) return;
        
        $stmt = $this->pdo->prepare("
            SELECT p.permission_name 
            FROM user_permissions up 
            JOIN permissions p ON up.permission_id = p.id 
            WHERE up.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $this->userRights = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function hasPermission($permission) {
        return in_array($permission, $this->userRights);
    }
    
    public function requirePermission($permission) {
        if (!$this->hasPermission($permission)) {
            http_response_code(403);
            die('Accès refusé');
        }
    }
}
```

## 🛡️ Protection contre les Attaques

### 1. Protection CSRF
```php
// includes/csrf.php
<?php
class CSRF {
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function verifyToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
}
```

### 2. Protection XSS
```php
// includes/security.php
<?php
class Security {
    public static function escape($data) {
        if (is_array($data)) {
            return array_map([self::class, 'escape'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeInput($input) {
        return strip_tags(trim($input));
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
```

### 3. Protection SQL Injection
```php
// Toutes les requêtes utilisent des requêtes préparées
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
```

## 📝 Logging et Monitoring

### 1. Système de Logs
```php
// includes/logger.php
<?php
class Logger {
    private $logFile;
    
    public function __construct($logFile = 'logs/app.log') {
        $this->logFile = $logFile;
    }
    
    public function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message " . json_encode($context) . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function logSecurity($event, $details = []) {
        $this->log('SECURITY', $event, $details);
    }
}
```

## 🔧 Configuration Recommandée

### 1. Variables d'Environnement
```env
# .env
DB_HOST=localhost
DB_NAME=library_db
DB_USER=library_user
DB_PASS=strong_password_here
APP_ENV=production
APP_DEBUG=false
SESSION_SECRET=your_secret_key_here
```

### 2. Configuration PHP
```ini
; php.ini recommandations
display_errors = Off
log_errors = On
error_log = /path/to/logs/php_errors.log
max_execution_time = 30
memory_limit = 128M
upload_max_filesize = 10M
post_max_size = 10M
session.cookie_httponly = 1
session.cookie_secure = 1
```

## 🚀 Déploiement Sécurisé

### 1. Checklist de Déploiement
- [ ] Changer tous les mots de passe par défaut
- [ ] Configurer HTTPS
- [ ] Mettre à jour les permissions des fichiers
- [ ] Configurer les sauvegardes automatiques
- [ ] Installer un certificat SSL
- [ ] Configurer un pare-feu
- [ ] Mettre en place la surveillance

### 2. Permissions des Fichiers
```bash
# Dossiers
chmod 755 public/
chmod 755 app/
chmod 700 logs/
chmod 700 uploads/

# Fichiers
chmod 644 public/*.php
chmod 600 app/config/*.php
chmod 600 .env
```

## 📊 Monitoring et Maintenance

### 1. Surveillance Continue
- Monitoring des logs d'erreur
- Surveillance des tentatives de connexion
- Vérification des performances
- Sauvegardes régulières

### 2. Mises à Jour
- Maintenir PHP à jour
- Mettre à jour les dépendances
- Appliquer les correctifs de sécurité
- Tester les mises à jour en environnement de développement

---

**⚠️ Important :** Ce guide doit être adapté à vos besoins spécifiques et testé en environnement de développement avant d'être déployé en production. 