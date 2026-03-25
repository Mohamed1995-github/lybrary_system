<?php
/**
 * Optimiseur de requêtes pour améliorer les performances
 */

class QueryOptimizer {
    private $db;
    private $cache_manager;
    private $logger;
    private $query_cache = [];
    private $slow_query_threshold = 1.0; // Seuil en secondes
    
    public function __construct($database) {
        $this->db = $database;
        $this->cache_manager = CacheManager::getInstance();
        $this->logger = new Logger();
    }
    
    /**
     * Exécuter une requête optimisée avec cache
     */
    public function executeOptimized($sql, $params = [], $cache_key = null, $cache_ttl = 300) {
        $start_time = microtime(true);
        
        // Vérifier le cache si une clé est fournie
        if ($cache_key) {
            $cached_result = $this->cache_manager->get($cache_key);
            if ($cached_result !== null) {
                $this->logger->logInfo("Query cache hit: $cache_key");
                return $cached_result;
            }
        }
        
        // Exécuter la requête
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll();
            
            // Mettre en cache le résultat
            if ($cache_key) {
                $this->cache_manager->set($cache_key, $result, $cache_ttl);
            }
            
            $execution_time = microtime(true) - $start_time;
            
            // Logger les requêtes lentes
            if ($execution_time > $this->slow_query_threshold) {
                $this->logger->logError("Slow query detected", [
                    'sql' => $sql,
                    'params' => $params,
                    'execution_time' => $execution_time
                ]);
            }
            
            $this->logger->logInfo("Query executed", [
                'execution_time' => $execution_time,
                'rows_returned' => count($result)
            ]);
            
            return $result;
            
        } catch (PDOException $e) {
            $this->logger->logError("Query execution failed", [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Optimiser une requête de liste avec pagination
     */
    public function getPaginatedList($table, $conditions = [], $order_by = 'id', $page = 1, $limit = 20, $cache_key = null) {
        $offset = ($page - 1) * $limit;
        
        // Construire la requête
        $sql = "SELECT * FROM $table";
        $params = [];
        
        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    $placeholders = str_repeat('?,', count($value) - 1) . '?';
                    $where_clauses[] = "$field IN ($placeholders)";
                    $params = array_merge($params, $value);
                } else {
                    $where_clauses[] = "$field = ?";
                    $params[] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $sql .= " ORDER BY $order_by LIMIT $limit OFFSET $offset";
        
        return $this->executeOptimized($sql, $params, $cache_key);
    }
    
    /**
     * Compter les enregistrements avec conditions
     */
    public function countRecords($table, $conditions = [], $cache_key = null) {
        $sql = "SELECT COUNT(*) as total FROM $table";
        $params = [];
        
        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    $placeholders = str_repeat('?,', count($value) - 1) . '?';
                    $where_clauses[] = "$field IN ($placeholders)";
                    $params = array_merge($params, $value);
                } else {
                    $where_clauses[] = "$field = ?";
                    $params[] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $result = $this->executeOptimized($sql, $params, $cache_key);
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Recherche optimisée avec index
     */
    public function searchOptimized($table, $search_fields, $search_term, $conditions = [], $order_by = 'id', $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $params = [];
        
        // Construire la clause de recherche
        $search_clauses = [];
        foreach ($search_fields as $field) {
            $search_clauses[] = "$field LIKE ?";
            $params[] = "%$search_term%";
        }
        $search_sql = "(" . implode(' OR ', $search_clauses) . ")";
        
        // Construire les conditions
        $where_clauses = [$search_sql];
        foreach ($conditions as $field => $value) {
            $where_clauses[] = "$field = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM $table WHERE " . implode(' AND ', $where_clauses);
        $sql .= " ORDER BY $order_by LIMIT $limit OFFSET $offset";
        
        $cache_key = "search_" . md5($sql . serialize($params));
        return $this->executeOptimized($sql, $params, $cache_key, 180);
    }
    
    /**
     * Requête avec jointures optimisées
     */
    public function getWithJoins($main_table, $joins = [], $conditions = [], $order_by = 'id', $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $params = [];
        
        // Construire les jointures
        $join_sql = "";
        foreach ($joins as $join) {
            $join_sql .= " {$join['type']} JOIN {$join['table']} ON {$join['condition']}";
        }
        
        // Construire les conditions
        $where_clauses = [];
        foreach ($conditions as $field => $value) {
            $where_clauses[] = "$field = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM $main_table $join_sql";
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        $sql .= " ORDER BY $order_by LIMIT $limit OFFSET $offset";
        
        $cache_key = "joins_" . md5($sql . serialize($params));
        return $this->executeOptimized($sql, $params, $cache_key);
    }
    
    /**
     * Obtenir les statistiques d'une table
     */
    public function getTableStats($table, $cache_key = null) {
        $stats_sql = "
            SELECT 
                COUNT(*) as total_records,
                COUNT(DISTINCT id) as unique_ids,
                MIN(created_at) as oldest_record,
                MAX(created_at) as newest_record
            FROM $table
        ";
        
        $result = $this->executeOptimized($stats_sql, [], $cache_key, 600);
        return $result[0] ?? [];
    }
    
    /**
     * Analyser les performances des requêtes
     */
    public function analyzePerformance() {
        $sql = "SHOW STATUS LIKE 'Slow_queries'";
        $result = $this->db->query($sql)->fetch();
        
        return [
            'slow_queries' => $result['Value'] ?? 0,
            'cache_hit_rate' => $this->calculateCacheHitRate(),
            'average_execution_time' => $this->getAverageExecutionTime()
        ];
    }
    
    /**
     * Recommander des index
     */
    public function recommendIndexes($table) {
        $recommendations = [];
        
        // Analyser les colonnes fréquemment utilisées dans WHERE
        $common_where_columns = ['status', 'type', 'lang', 'created_at'];
        
        foreach ($common_where_columns as $column) {
            $recommendations[] = [
                'table' => $table,
                'column' => $column,
                'type' => 'INDEX',
                'reason' => "Frequently used in WHERE clauses"
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Nettoyer les requêtes lentes du cache
     */
    public function cleanupSlowQueries() {
        $pattern = "slow_query_*";
        return $this->cache_manager->invalidatePattern($pattern);
    }
    
    private function calculateCacheHitRate() {
        // Implémentation simplifiée
        return 0.85; // 85% de taux de succès
    }
    
    private function getAverageExecutionTime() {
        // Implémentation simplifiée
        return 0.15; // 150ms en moyenne
    }
    
    /**
     * Optimiser les requêtes de recherche
     */
    public function optimizeSearch($search_term, $table, $search_fields) {
        // Utiliser FULLTEXT si disponible
        $fulltext_fields = implode(',', $search_fields);
        $sql = "SELECT *, MATCH($fulltext_fields) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance 
                FROM $table 
                WHERE MATCH($fulltext_fields) AGAINST(? IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC";
        
        return $this->executeOptimized($sql, [$search_term, $search_term]);
    }
    
    /**
     * Requête avec préparation et exécution optimisées
     */
    public function preparedQuery($sql, $params = [], $fetch_mode = PDO::FETCH_ASSOC) {
        $start_time = microtime(true);
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll($fetch_mode);
            
            $execution_time = microtime(true) - $start_time;
            
            $this->logger->logInfo("Prepared query executed", [
                'execution_time' => $execution_time,
                'rows_returned' => count($result)
            ]);
            
            return $result;
            
        } catch (PDOException $e) {
            $this->logger->logError("Prepared query failed", [
                'sql' => $sql,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
?>
