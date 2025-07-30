<?php
/**
 * Classe de sécurité pour les modules
 * Gère l'authentification, les permissions et la protection CSRF
 */

class ModuleSecurity {
    private $pdo;
    private $user_id;
    private $user_role;
    
    public function __construct($pdo, $user_id, $user_role) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
        $this->user_role = $user_role;
    }
    
    /**
     * Vérifier si l'utilisateur a les permissions pour accéder au module
     */
    public function checkModuleAccess($module, $action) {
        $allowed_modules = [
            'acquisitions' => [
                'add' => ['admin', 'librarian'],
                'edit' => ['admin', 'librarian'],
                'delete' => ['admin'],
                'list' => ['admin', 'librarian', 'assistant']
            ],
            'items' => [
                'add_book' => ['admin', 'librarian'],
                'add_magazine' => ['admin', 'librarian'],
                'add_newspaper' => ['admin', 'librarian'],
                'edit' => ['admin', 'librarian'],
                'delete' => ['admin'],
                'list' => ['admin', 'librarian', 'assistant'],
                'list_grouped' => ['admin', 'librarian', 'assistant']
            ],
            'loans' => [
                'add' => ['admin', 'librarian'],
                'borrow' => ['admin', 'librarian'],
                'return' => ['admin', 'librarian'],
                'list' => ['admin', 'librarian', 'assistant']
            ],
            'borrowers' => [
                'add' => ['admin', 'librarian'],
                'edit' => ['admin', 'librarian'],
                'list' => ['admin', 'librarian', 'assistant']
            ],
            'administration' => [
                'add' => ['admin'],
                'edit' => ['admin'],
                'delete' => ['admin'],
                'list' => ['admin'],
                'permissions_guide' => ['admin']
            ],
            'members' => [
                'add' => ['admin', 'librarian'],
                'edit' => ['admin', 'librarian'],
                'list' => ['admin', 'librarian', 'assistant']
            ]
        ];
        
        // Vérifier si le module existe
        if (!isset($allowed_modules[$module])) {
            return false;
        }
        
        // Vérifier si l'action existe pour ce module
        if (!isset($allowed_modules[$module][$action])) {
            return false;
        }
        
        // Vérifier les permissions
        $required_roles = $allowed_modules[$module][$action];
        return in_array($this->user_role, $required_roles);
    }
    
    /**
     * Générer un token CSRF
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifier un token CSRF
     */
    public function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Valider et nettoyer les données d'entrée
     */
    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valider un ID numérique
     */
    public function validateNumericId($id) {
        return is_numeric($id) && $id > 0;
    }
    
    /**
     * Valider une adresse email
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valider un numéro de téléphone
     */
    public function validatePhone($phone) {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 8 && strlen($phone) <= 15;
    }
    
    /**
     * Logger les actions de sécurité
     */
    public function logSecurityEvent($event, $details = '') {
        $log_entry = date('Y-m-d H:i:s') . " - User: {$this->user_id} ({$this->user_role}) - Event: {$event} - Details: {$details} - IP: {$_SERVER['REMOTE_ADDR']}\n";
        file_put_contents(__DIR__ . '/../logs/security.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Vérifier si l'utilisateur peut modifier un enregistrement
     */
    public function canModifyRecord($table, $record_id, $user_field = 'user_id') {
        // Les administrateurs peuvent tout modifier
        if ($this->user_role === 'admin') {
            return true;
        }
        
        // Vérifier si l'enregistrement appartient à l'utilisateur
        $stmt = $this->pdo->prepare("SELECT {$user_field} FROM {$table} WHERE id = ?");
        $stmt->execute([$record_id]);
        $record = $stmt->fetch();
        
        return $record && $record[$user_field] == $this->user_id;
    }
    
    /**
     * Vérifier les tentatives de connexion échouées
     */
    public function checkFailedLoginAttempts($username) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM failed_logins WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$username]);
        $attempts = $stmt->fetchColumn();
        
        return $attempts < 5; // Maximum 5 tentatives en 15 minutes
    }
    
    /**
     * Enregistrer une tentative de connexion échouée
     */
    public function logFailedLogin($username) {
        $stmt = $this->pdo->prepare("INSERT INTO failed_logins (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
        $stmt->execute([$username, $_SERVER['REMOTE_ADDR']]);
    }
    
    /**
     * Nettoyer les anciennes tentatives de connexion échouées
     */
    public function cleanOldFailedLogins() {
        $stmt = $this->pdo->prepare("DELETE FROM failed_logins WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute();
    }
}
?> 