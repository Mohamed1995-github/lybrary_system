<?php
/**
 * Gestionnaire de cache pour optimiser les performances
 * Supporte le cache en mémoire, fichier et Redis
 */

class CacheManager {
    private static $instance = null;
    private $cache_dir;
    private $default_ttl = 3600; // 1 heure par défaut
    private $memory_cache = [];
    private $logger;
    
    private function __construct() {
        $this->cache_dir = __DIR__ . '/../temp/cache';
        
        // Initialiser le logger si disponible
        if (class_exists('Logger')) {
            $this->logger = new Logger();
        } else {
            $this->logger = null;
        }
        
        // Créer le dossier de cache s'il n'existe pas
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Récupérer une valeur du cache
     */
    public function get($key, $default = null) {
        // Vérifier d'abord le cache mémoire
        if (isset($this->memory_cache[$key])) {
            $data = $this->memory_cache[$key];
            if ($data['expires'] > time()) {
                if ($this->logger) {
            $this->logger->logInfo("Cache hit (memory): $key");
        }
                return $data['value'];
            } else {
                unset($this->memory_cache[$key]);
            }
        }
        
        // Vérifier le cache fichier
        $file_path = $this->getFilePath($key);
        if (file_exists($file_path)) {
            $data = unserialize(file_get_contents($file_path));
            if ($data['expires'] > time()) {
                // Mettre en cache mémoire pour les accès futurs
                $this->memory_cache[$key] = $data;
                if ($this->logger) {
                    $this->logger->logInfo("Cache hit (file): $key");
                }
                return $data['value'];
            } else {
                unlink($file_path);
            }
        }
        
        if ($this->logger) {
            $this->logger->logInfo("Cache miss: $key");
        }
        return $default;
    }
    
    /**
     * Stocker une valeur dans le cache
     */
    public function set($key, $value, $ttl = null) {
        if ($ttl === null) {
            $ttl = $this->default_ttl;
        }
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        // Stocker en mémoire
        $this->memory_cache[$key] = $data;
        
        // Stocker en fichier
        $file_path = $this->getFilePath($key);
        file_put_contents($file_path, serialize($data), LOCK_EX);
        
        if ($this->logger) {
            $this->logger->logInfo("Cache set: $key (TTL: {$ttl}s)");
        }
    }
    
    /**
     * Supprimer une clé du cache
     */
    public function delete($key) {
        // Supprimer de la mémoire
        unset($this->memory_cache[$key]);
        
        // Supprimer le fichier
        $file_path = $this->getFilePath($key);
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        if ($this->logger) {
            $this->logger->logInfo("Cache delete: $key");
        }
    }
    
    /**
     * Vider tout le cache
     */
    public function flush() {
        // Vider la mémoire
        $this->memory_cache = [];
        
        // Supprimer tous les fichiers de cache
        $files = glob($this->cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        if ($this->logger) {
            $this->logger->logInfo("Cache flushed");
        }
    }
    
    /**
     * Nettoyer le cache expiré
     */
    public function cleanup() {
        $cleaned = 0;
        
        // Nettoyer la mémoire
        foreach ($this->memory_cache as $key => $data) {
            if ($data['expires'] <= time()) {
                unset($this->memory_cache[$key]);
                $cleaned++;
            }
        }
        
        // Nettoyer les fichiers
        $files = glob($this->cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));
                if ($data['expires'] <= time()) {
                    unlink($file);
                    $cleaned++;
                }
            }
        }
        
        if ($this->logger) {
            $this->logger->logInfo("Cache cleanup: $cleaned items removed");
        }
        return $cleaned;
    }
    
    /**
     * Obtenir les statistiques du cache
     */
    public function getStats() {
        $memory_count = count($this->memory_cache);
        $file_count = count(glob($this->cache_dir . '/*'));
        
        return [
            'memory_items' => $memory_count,
            'file_items' => $file_count,
            'total_size' => $this->getCacheSize(),
            'hit_rate' => $this->calculateHitRate()
        ];
    }
    
    /**
     * Cache avec callback (cache-aside pattern)
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Invalider le cache par pattern
     */
    public function invalidatePattern($pattern) {
        $cleaned = 0;
        
        // Nettoyer la mémoire
        foreach ($this->memory_cache as $key => $data) {
            if (fnmatch($pattern, $key)) {
                unset($this->memory_cache[$key]);
                $cleaned++;
            }
        }
        
        // Nettoyer les fichiers
        $files = glob($this->cache_dir . '/*');
        foreach ($files as $file) {
            $filename = basename($file);
            if (fnmatch($pattern, $filename)) {
            unlink($file);
                $cleaned++;
            }
        }
        
        if ($this->logger) {
            $this->logger->logInfo("Cache invalidated by pattern: $pattern ($cleaned items)");
        }
        return $cleaned;
    }
    
    private function getFilePath($key) {
        $hash = md5($key);
        return $this->cache_dir . '/' . $hash . '.cache';
    }
    
    private function getCacheSize() {
        $size = 0;
        $files = glob($this->cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }
        return $size;
    }
    
    private function calculateHitRate() {
        // Cette méthode nécessiterait un système de comptage plus sophistiqué
        // Pour l'instant, retourner 0
        return 0;
    }
    
    /**
     * Cache spécialisé pour les requêtes de base de données
     */
    public function cacheQuery($sql, $params, $callback, $ttl = 1800) {
        $key = 'query_' . md5($sql . serialize($params));
        return $this->remember($key, $callback, $ttl);
    }
    
    /**
     * Cache pour les listes paginées
     */
    public function cachePaginatedList($module, $filters, $page, $callback, $ttl = 900) {
        $key = "list_{$module}_" . md5(serialize($filters) . $page);
        return $this->remember($key, $callback, $ttl);
    }
}
?>