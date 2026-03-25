<?php
/**
 * Classe Logger pour enregistrer les événements et erreurs
 * Gère les logs de sécurité, d'erreurs et d'informations
 */

class Logger {
    private $logFile;
    private $securityLogFile;
    private $errorLogFile;
    
    public function __construct($logFile = null) {
        $this->logFile = $logFile ?? __DIR__ . '/../logs/access.log';
        $this->securityLogFile = __DIR__ . '/../logs/security.log';
        $this->errorLogFile = __DIR__ . '/../logs/error.log';
        
        // Créer les dossiers de logs s'ils n'existent pas
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        if (!is_dir(dirname($this->securityLogFile))) {
            mkdir(dirname($this->securityLogFile), 0755, true);
        }
        
        if (!is_dir(dirname($this->errorLogFile))) {
            mkdir(dirname($this->errorLogFile), 0755, true);
        }
    }
    
    /**
     * Enregistrer un log général
     */
    public function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $_SESSION['uid'] ?? 'anonymous';
        
        $logEntry = sprintf(
            "[%s] [%s] [User: %s] [IP: %s] [UA: %s] %s %s\n",
            $timestamp,
            strtoupper($level),
            $userId,
            $ip,
            $userAgent,
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Enregistrer un log de sécurité
     */
    public function logSecurity($event, $details = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $_SESSION['uid'] ?? 'anonymous';
        
        $logEntry = sprintf(
            "[%s] [SECURITY] [User: %s] [IP: %s] [UA: %s] %s %s\n",
            $timestamp,
            $userId,
            $ip,
            $userAgent,
            $event,
            !empty($details) ? json_encode($details) : ''
        );
        
        file_put_contents($this->securityLogFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Enregistrer un log d'erreur
     */
    public function logError($message, $details = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $_SESSION['uid'] ?? 'anonymous';
        
        $logEntry = sprintf(
            "[%s] [ERROR] [User: %s] [IP: %s] [UA: %s] %s %s\n",
            $timestamp,
            $userId,
            $ip,
            $userAgent,
            $message,
            !empty($details) ? json_encode($details) : ''
        );
        
        file_put_contents($this->errorLogFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Enregistrer un log d'information
     */
    public function logInfo($message, $details = []) {
        $this->log('INFO', $message, $details);
    }
    
    /**
     * Enregistrer un log d'avertissement
     */
    public function logWarning($message, $details = []) {
        $this->log('WARNING', $message, $details);
    }
    
    /**
     * Nettoyer les logs anciens
     */
    public function cleanupLogs($maxAgeDays = 30) {
        $maxAge = $maxAgeDays * 86400; // Convertir en secondes
        $cleaned = 0;
        
        $logFiles = [$this->logFile, $this->securityLogFile, $this->errorLogFile];
        
        foreach ($logFiles as $logFile) {
            if (file_exists($logFile) && (time() - filemtime($logFile)) > $maxAge) {
                // Créer une sauvegarde avant de nettoyer
                $backupFile = $logFile . '.backup.' . date('Y-m-d');
                copy($logFile, $backupFile);
                
                // Vider le fichier
                file_put_contents($logFile, '');
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtenir les statistiques des logs
     */
    public function getLogStats() {
        $stats = [
            'access_log_size' => file_exists($this->logFile) ? filesize($this->logFile) : 0,
            'security_log_size' => file_exists($this->securityLogFile) ? filesize($this->securityLogFile) : 0,
            'error_log_size' => file_exists($this->errorLogFile) ? filesize($this->errorLogFile) : 0,
            'total_logs' => 0
        ];
        
        // Compter les lignes dans chaque fichier
        if (file_exists($this->logFile)) {
            $stats['total_logs'] += count(file($this->logFile));
        }
        
        return $stats;
    }
    
    /**
     * Lire les logs récents
     */
    public function getRecentLogs($lines = 50) {
        $logs = [];
        
        if (file_exists($this->logFile)) {
            $fileLines = file($this->logFile);
            $logs = array_slice($fileLines, -$lines);
        }
        
        return $logs;
    }
    
    /**
     * Rechercher dans les logs
     */
    public function searchLogs($query, $logType = 'all') {
        $results = [];
        $files = [];
        
        switch ($logType) {
            case 'security':
                $files = [$this->securityLogFile];
                break;
            case 'error':
                $files = [$this->errorLogFile];
                break;
            case 'access':
                $files = [$this->logFile];
                break;
            default:
                $files = [$this->logFile, $this->securityLogFile, $this->errorLogFile];
        }
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $lines = file($file);
                foreach ($lines as $line) {
                    if (stripos($line, $query) !== false) {
                        $results[] = $line;
                    }
                }
            }
        }
        
        return $results;
    }
}
?>
