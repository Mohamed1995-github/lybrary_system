<?php
/**
 * Configuration sécurisée de la base de données
 * Utilise les variables d'environnement pour les informations sensibles
 */

class Database {
    private static $instance = null;
    private $pdo;
    private $logger;
    
    private function __construct() {
        $this->logger = new Logger();
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    private function connect() {
        try {
            // Configuration de la base de données
            $config = $this->getConfig();
            
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['dbname'],
                $config['charset']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT => false, // Éviter les connexions persistantes pour la sécurité
            ];
            
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
            
            // Configurer le fuseau horaire
            $this->pdo->exec("SET time_zone = '+00:00'");
            
            $this->logger->logInfo('Database connection established successfully');
            
        } catch (PDOException $e) {
            $this->logger->logError('Database connection failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            // En production, ne pas afficher les détails d'erreur
            if ($this->isDevelopment()) {
                throw new Exception("Erreur de base de données: " . $e->getMessage());
            } else {
                throw new Exception("Erreur de base de données. Veuillez contacter l'administrateur.");
            }
        }
    }
    
    private function getConfig() {
        // Charger depuis les variables d'environnement si disponibles
        $config = [
            'host' => $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost',
            'dbname' => $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'library_db',
            'username' => $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
        ];
        
        // Validation de la configuration
        if (empty($config['host']) || empty($config['dbname'])) {
            throw new Exception("Configuration de base de données incomplète");
        }
        
        return $config;
    }
    
    private function isDevelopment() {
        return ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development') === 'development';
    }
    
    /**
     * Exécuter une requête préparée de manière sécurisée
     */
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logger->logError('Query execution failed', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Récupérer une seule ligne
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer toutes les lignes
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insérer une ligne et retourner l'ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $this->executeQuery($sql, $data);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Mettre à jour une ligne
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "$column = :$column";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Supprimer une ligne
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Vérifier si une table existe
     */
    public function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->fetchOne($sql, [$tableName]);
        return !empty($result);
    }
    
    /**
     * Obtenir les informations sur les colonnes d'une table
     */
    public function getTableColumns($tableName) {
        $sql = "DESCRIBE $tableName";
        return $this->fetchAll($sql);
    }
    
    /**
     * Commencer une transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Vérifier la connexion
     */
    public function ping() {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Fermer la connexion
     */
    public function close() {
        $this->pdo = null;
    }
}

// Créer une instance globale pour la compatibilité
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    // Logger l'erreur et afficher un message approprié
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    
    if (php_sapi_name() === 'cli') {
        echo "Erreur de base de données: " . $e->getMessage() . "\n";
    } else {
        http_response_code(500);
        echo "Erreur de base de données. Veuillez réessayer plus tard.";
    }
    exit;
}
?> 