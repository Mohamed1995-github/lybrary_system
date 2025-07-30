<?php
/**
 * Classe de sécurité pour le système de bibliothèque
 * Gère la protection contre XSS, CSRF, et autres attaques
 */

class Security {
    private static $instance = null;
    private $logger;
    
    private function __construct() {
        $this->logger = new Logger();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Échapper les données pour éviter les attaques XSS
     */
    public static function escape($data, $encoding = 'UTF-8') {
        if (is_array($data)) {
            return array_map([self::class, 'escape'], $data);
        }
        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, $encoding);
        }
        return $data;
    }
    
    /**
     * Nettoyer les entrées utilisateur
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return strip_tags(trim($input));
    }
    
    /**
     * Valider une adresse email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valider un numéro de téléphone
     */
    public static function validatePhone($phone) {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 8 && strlen($phone) <= 15;
    }
    
    /**
     * Valider une URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Générer un token CSRF
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifier un token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Vérifier l'expiration (1 heure)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Valider et nettoyer un nom de fichier
     */
    public static function sanitizeFilename($filename) {
        // Supprimer les caractères dangereux
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Empêcher les noms de fichiers dangereux
        $dangerous = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'com'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($extension, $dangerous)) {
            return false;
        }
        
        return $filename;
    }
    
    /**
     * Valider le type MIME d'un fichier
     */
    public static function validateFileType($file, $allowedTypes = []) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (empty($allowedTypes)) {
            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf',
                'text/plain',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
        }
        
        return in_array($mimeType, $allowedTypes);
    }
    
    /**
     * Limiter la taille d'un fichier
     */
    public static function validateFileSize($file, $maxSize = 5242880) { // 5MB par défaut
        return isset($file['size']) && $file['size'] <= $maxSize;
    }
    
    /**
     * Valider une date
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Valider un code postal
     */
    public static function validatePostalCode($code) {
        return preg_match('/^[0-9]{5}$/', $code);
    }
    
    /**
     * Valider un numéro ISBN
     */
    public static function validateISBN($isbn) {
        // Supprimer les tirets et espaces
        $isbn = preg_replace('/[-\s]/', '', $isbn);
        
        // Vérifier la longueur (ISBN-10 ou ISBN-13)
        if (strlen($isbn) !== 10 && strlen($isbn) !== 13) {
            return false;
        }
        
        // Validation basique (peut être améliorée)
        return preg_match('/^[0-9X]{10}$|^[0-9]{13}$/', $isbn);
    }
    
    /**
     * Protéger contre les attaques par force brute
     */
    public static function checkBruteForce($identifier, $maxAttempts = 5, $timeWindow = 900) { // 15 minutes
        $attempts = $_SESSION['login_attempts'][$identifier] ?? [];
        $now = time();
        
        // Nettoyer les tentatives anciennes
        $attempts = array_filter($attempts, function($time) use ($now, $timeWindow) {
            return $now - $time < $timeWindow;
        });
        
        $_SESSION['login_attempts'][$identifier] = $attempts;
        
        return count($attempts) < $maxAttempts;
    }
    
    /**
     * Enregistrer une tentative de connexion
     */
    public static function logLoginAttempt($identifier, $success = false) {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        if (!isset($_SESSION['login_attempts'][$identifier])) {
            $_SESSION['login_attempts'][$identifier] = [];
        }
        
        $_SESSION['login_attempts'][$identifier][] = time();
        
        // Logger l'événement
        $logger = new Logger();
        $logger->logSecurity('login_attempt', [
            'identifier' => $identifier,
            'success' => $success,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    /**
     * Valider un mot de passe fort
     */
    public static function validatePassword($password) {
        // Au moins 8 caractères
        if (strlen($password) < 8) {
            return false;
        }
        
        // Au moins une lettre majuscule
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Au moins une lettre minuscule
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Au moins un chiffre
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Au moins un caractère spécial
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Générer un mot de passe sécurisé
     */
    public static function generateSecurePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        
        // S'assurer d'avoir au moins un de chaque type
        $password .= chr(rand(65, 90)); // Majuscule
        $password .= chr(rand(97, 122)); // Minuscule
        $password .= chr(rand(48, 57)); // Chiffre
        $password .= '!@#$%^&*'[rand(0, 7)]; // Caractère spécial
        
        // Remplir le reste
        for ($i = 4; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return str_shuffle($password);
    }
    
    /**
     * Valider une entrée JSON
     */
    public static function validateJSON($json) {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Nettoyer les données JSON
     */
    public static function sanitizeJSON($json) {
        if (!self::validateJSON($json)) {
            return null;
        }
        
        $data = json_decode($json, true);
        return self::escape($data);
    }
}

/**
 * Classe Logger pour enregistrer les événements de sécurité
 */
class Logger {
    private $logFile;
    
    public function __construct($logFile = null) {
        $this->logFile = $logFile ?? __DIR__ . '/../logs/security.log';
        
        // Créer le dossier de logs s'il n'existe pas
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0700, true);
        }
    }
    
    public function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = sprintf(
            "[%s] [%s] [IP: %s] [UA: %s] %s %s\n",
            $timestamp,
            strtoupper($level),
            $ip,
            $userAgent,
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function logSecurity($event, $details = []) {
        $this->log('SECURITY', $event, $details);
    }
    
    public function logError($message, $details = []) {
        $this->log('ERROR', $message, $details);
    }
    
    public function logInfo($message, $details = []) {
        $this->log('INFO', $message, $details);
    }
}
?> 