<?php
/**
 * Database Optimizer Class
 * Handles query caching, connection pooling, and database performance optimization
 */
class DatabaseOptimizer {
    private static $instance = null;
    private $cache;
    private $queryCache = [];
    private $cacheDir;
    private $defaultCacheTime = 300; // 5 minutes
    
    private function __construct() {
        $this->cacheDir = __DIR__ . '/../cache/db';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        // Initialize simple file-based cache
        $this->initializeCache();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Execute cached query
     */
    public function cachedQuery($sql, $params = [], $cacheTime = null) {
        $cacheTime = $cacheTime ?? $this->defaultCacheTime;
        $cacheKey = $this->generateCacheKey($sql, $params);
        
        // Try to get from cache first
        $cachedResult = $this->getFromCache($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
        
        // Execute query and cache result
        $db = Database::getInstance();
        $result = $db->fetchAll($sql, $params);
        
        $this->setCache($cacheKey, $result, $cacheTime);
        
        return $result;
    }
    
    /**
     * Execute cached single row query
     */
    public function cachedQueryOne($sql, $params = [], $cacheTime = null) {
        $cacheTime = $cacheTime ?? $this->defaultCacheTime;
        $cacheKey = $this->generateCacheKey($sql . '_ONE', $params);
        
        // Try to get from cache first
        $cachedResult = $this->getFromCache($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
        
        // Execute query and cache result
        $db = Database::getInstance();
        $result = $db->fetchOne($sql, $params);
        
        $this->setCache($cacheKey, $result, $cacheTime);
        
        return $result;
    }
    
    /**
     * Invalidate cache for specific patterns
     */
    public function invalidateCache($pattern = null) {
        if ($pattern === null) {
            // Clear all cache
            $files = glob($this->cacheDir . '/*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            // Clear cache matching pattern
            $files = glob($this->cacheDir . '/*' . $pattern . '*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get database statistics and optimization suggestions
     */
    public function getDatabaseStats() {
        $db = Database::getInstance();
        $stats = [];
        
        try {
            // Get table sizes
            $tableSizes = $db->fetchAll("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb',
                    table_rows
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
            ");
            
            $stats['table_sizes'] = $tableSizes;
            
            // Get slow queries (if enabled)
            $slowQueries = $db->fetchAll("
                SELECT * FROM information_schema.PROCESSLIST 
                WHERE TIME > 1 AND COMMAND != 'Sleep'
                ORDER BY TIME DESC LIMIT 10
            ");
            
            $stats['slow_queries'] = $slowQueries;
            
            // Get index usage
            $indexStats = $db->fetchAll("
                SELECT 
                    TABLE_NAME,
                    INDEX_NAME,
                    CARDINALITY,
                    NULLABLE
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY TABLE_NAME, INDEX_NAME
            ");
            
            $stats['index_stats'] = $indexStats;
            
        } catch (Exception $e) {
            $stats['error'] = $e->getMessage();
        }
        
        return $stats;
    }
    
    /**
     * Generate optimization recommendations
     */
    public function getOptimizationRecommendations() {
        $recommendations = [];
        $db = Database::getInstance();
        
        try {
            // Check for tables without primary keys
            $tablesWithoutPK = $db->fetchAll("
                SELECT TABLE_NAME 
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.KEY_COLUMN_USAGE k 
                    ON t.TABLE_NAME = k.TABLE_NAME 
                    AND k.CONSTRAINT_NAME = 'PRIMARY'
                WHERE t.TABLE_SCHEMA = DATABASE() 
                    AND k.TABLE_NAME IS NULL
                    AND t.TABLE_TYPE = 'BASE TABLE'
            ");
            
            if (!empty($tablesWithoutPK)) {
                $recommendations[] = [
                    'type' => 'missing_primary_key',
                    'severity' => 'high',
                    'message' => 'Tables without primary keys detected',
                    'tables' => array_column($tablesWithoutPK, 'TABLE_NAME')
                ];
            }
            
            // Check for unused indexes
            $unusedIndexes = $db->fetchAll("
                SELECT 
                    s.TABLE_NAME,
                    s.INDEX_NAME
                FROM information_schema.STATISTICS s
                LEFT JOIN information_schema.INDEX_STATISTICS i 
                    ON s.TABLE_SCHEMA = i.TABLE_SCHEMA 
                    AND s.TABLE_NAME = i.TABLE_NAME 
                    AND s.INDEX_NAME = i.INDEX_NAME
                WHERE s.TABLE_SCHEMA = DATABASE()
                    AND s.INDEX_NAME != 'PRIMARY'
                    AND i.INDEX_NAME IS NULL
            ");
            
            if (!empty($unusedIndexes)) {
                $recommendations[] = [
                    'type' => 'unused_indexes',
                    'severity' => 'medium',
                    'message' => 'Unused indexes detected',
                    'indexes' => $unusedIndexes
                ];
            }
            
            // Check for large tables that might need partitioning
            $largeTables = $db->fetchAll("
                SELECT 
                    TABLE_NAME,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                    AND ((data_length + index_length) / 1024 / 1024) > 100
                ORDER BY size_mb DESC
            ");
            
            if (!empty($largeTables)) {
                $recommendations[] = [
                    'type' => 'large_tables',
                    'severity' => 'medium',
                    'message' => 'Large tables that might benefit from optimization',
                    'tables' => $largeTables
                ];
            }
            
        } catch (Exception $e) {
            $recommendations[] = [
                'type' => 'error',
                'severity' => 'high',
                'message' => 'Error analyzing database: ' . $e->getMessage()
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Create recommended indexes
     */
    public function createRecommendedIndexes() {
        $db = Database::getInstance();
        $indexesCreated = [];
        
        try {
            // Common indexes for library system
            $recommendedIndexes = [
                'books' => [
                    'idx_title' => 'title',
                    'idx_author' => 'author',
                    'idx_category' => 'category_id',
                    'idx_year' => 'year_pub',
                    'idx_isbn' => 'isbn'
                ],
                'loans' => [
                    'idx_borrower' => 'borrower_id',
                    'idx_book' => 'book_id',
                    'idx_loan_date' => 'loan_date',
                    'idx_return_date' => 'return_date',
                    'idx_status' => 'status'
                ],
                'borrowers' => [
                    'idx_email' => 'email',
                    'idx_phone' => 'phone',
                    'idx_membership' => 'membership_date'
                ],
                'employees' => [
                    'idx_username' => 'username',
                    'idx_email' => 'email',
                    'idx_role' => 'role'
                ]
            ];
            
            foreach ($recommendedIndexes as $table => $indexes) {
                // Check if table exists
                if ($db->tableExists($table)) {
                    foreach ($indexes as $indexName => $column) {
                        try {
                            // Check if index already exists
                            $existingIndex = $db->fetchOne("
                                SELECT INDEX_NAME 
                                FROM information_schema.STATISTICS 
                                WHERE TABLE_SCHEMA = DATABASE() 
                                    AND TABLE_NAME = ? 
                                    AND INDEX_NAME = ?
                            ", [$table, $indexName]);
                            
                            if (!$existingIndex) {
                                $sql = "CREATE INDEX {$indexName} ON {$table} ({$column})";
                                $db->executeQuery($sql);
                                $indexesCreated[] = "{$table}.{$indexName}";
                            }
                        } catch (Exception $e) {
                            // Log error but continue with other indexes
                            error_log("Failed to create index {$indexName} on {$table}: " . $e->getMessage());
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error creating recommended indexes: " . $e->getMessage());
        }
        
        return $indexesCreated;
    }
    
    /**
     * Initialize cache system
     */
    private function initializeCache() {
        // Simple file-based cache for now
        // In production, consider Redis or Memcached
        $this->cache = new class($this->cacheDir) {
            private $cacheDir;
            
            public function __construct($cacheDir) {
                $this->cacheDir = $cacheDir;
            }
            
            public function get($key) {
                $file = $this->cacheDir . '/' . md5($key) . '.cache';
                
                if (!file_exists($file)) {
                    return null;
                }
                
                $data = unserialize(file_get_contents($file));
                
                if ($data['expires'] < time()) {
                    unlink($file);
                    return null;
                }
                
                return $data['value'];
            }
            
            public function set($key, $value, $ttl = 300) {
                $file = $this->cacheDir . '/' . md5($key) . '.cache';
                $data = [
                    'value' => $value,
                    'expires' => time() + $ttl
                ];
                
                file_put_contents($file, serialize($data), LOCK_EX);
            }
            
            public function delete($key) {
                $file = $this->cacheDir . '/' . md5($key) . '.cache';
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        };
    }
    
    /**
     * Generate cache key for query
     */
    private function generateCacheKey($sql, $params) {
        return 'query_' . md5($sql . serialize($params));
    }
    
    /**
     * Get from cache
     */
    private function getFromCache($key) {
        return $this->cache->get($key);
    }
    
    /**
     * Set cache
     */
    private function setCache($key, $value, $ttl) {
        $this->cache->set($key, $value, $ttl);
    }
    
    /**
     * Clean expired cache files
     */
    public function cleanExpiredCache() {
        $files = glob($this->cacheDir . '/*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if (isset($data['expires']) && $data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}
?>