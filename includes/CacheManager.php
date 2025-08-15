<?php
/**
 * Comprehensive Cache Manager
 * Handles page caching, Redis/Memcached integration, and cache strategies
 */
class CacheManager {
    private static $instance = null;
    private $adapter;
    private $config;
    private $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0
    ];
    
    private function __construct() {
        $this->config = $this->loadConfig();
        $this->adapter = $this->createAdapter();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get value from cache
     */
    public function get($key, $default = null) {
        $result = $this->adapter->get($this->prefixKey($key));
        
        if ($result !== null) {
            $this->stats['hits']++;
            return $result;
        }
        
        $this->stats['misses']++;
        return $default;
    }
    
    /**
     * Set value in cache
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->config['default_ttl'];
        $result = $this->adapter->set($this->prefixKey($key), $value, $ttl);
        
        if ($result) {
            $this->stats['sets']++;
        }
        
        return $result;
    }
    
    /**
     * Delete value from cache
     */
    public function delete($key) {
        $result = $this->adapter->delete($this->prefixKey($key));
        
        if ($result) {
            $this->stats['deletes']++;
        }
        
        return $result;
    }
    
    /**
     * Cache a callback result
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
     * Cache page output
     */
    public function cachePage($key, $content, $ttl = 3600) {
        $cacheData = [
            'content' => $content,
            'headers' => $this->getCurrentHeaders(),
            'timestamp' => time(),
            'ttl' => $ttl
        ];
        
        return $this->set('page_' . $key, $cacheData, $ttl);
    }
    
    /**
     * Get cached page
     */
    public function getCachedPage($key) {
        $cacheData = $this->get('page_' . $key);
        
        if ($cacheData && isset($cacheData['content'])) {
            // Restore headers
            if (isset($cacheData['headers'])) {
                foreach ($cacheData['headers'] as $header) {
                    header($header);
                }
            }
            
            // Add cache headers
            header('X-Cache: HIT');
            header('X-Cache-Time: ' . date('Y-m-d H:i:s', $cacheData['timestamp']));
            
            return $cacheData['content'];
        }
        
        return null;
    }
    
    /**
     * Invalidate cache by pattern
     */
    public function invalidatePattern($pattern) {
        return $this->adapter->deletePattern($this->prefixKey($pattern));
    }
    
    /**
     * Invalidate cache by tags
     */
    public function invalidateTags($tags) {
        if (!is_array($tags)) {
            $tags = [$tags];
        }
        
        foreach ($tags as $tag) {
            $keys = $this->get('tag_' . $tag, []);
            foreach ($keys as $key) {
                $this->delete($key);
            }
            $this->delete('tag_' . $tag);
        }
    }
    
    /**
     * Set cache with tags
     */
    public function setWithTags($key, $value, $tags = [], $ttl = null) {
        $result = $this->set($key, $value, $ttl);
        
        if ($result && !empty($tags)) {
            foreach ($tags as $tag) {
                $tagKeys = $this->get('tag_' . $tag, []);
                $tagKeys[] = $key;
                $this->set('tag_' . $tag, array_unique($tagKeys), $ttl);
            }
        }
        
        return $result;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        $adapterStats = $this->adapter->getStats();
        
        return array_merge($this->stats, $adapterStats, [
            'hit_ratio' => $this->stats['hits'] > 0 ? 
                round($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses']) * 100, 2) : 0
        ]);
    }
    
    /**
     * Clear all cache
     */
    public function flush() {
        return $this->adapter->flush();
    }
    
    /**
     * Load cache configuration
     */
    private function loadConfig() {
        return [
            'prefix' => 'library_',
            'default_ttl' => 3600,
            'adapter' => $_ENV['CACHE_ADAPTER'] ?? 'file',
            'redis' => [
                'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => $_ENV['REDIS_PASSWORD'] ?? null,
                'database' => $_ENV['REDIS_DB'] ?? 0
            ],
            'memcached' => [
                'servers' => [
                    [$_ENV['MEMCACHED_HOST'] ?? 'localhost', $_ENV['MEMCACHED_PORT'] ?? 11211]
                ]
            ],
            'file' => [
                'path' => __DIR__ . '/../cache/app'
            ]
        ];
    }
    
    /**
     * Create cache adapter
     */
    private function createAdapter() {
        switch ($this->config['adapter']) {
            case 'redis':
                return new RedisCacheAdapter($this->config['redis']);
            case 'memcached':
                return new MemcachedCacheAdapter($this->config['memcached']);
            default:
                return new FileCacheAdapter($this->config['file']);
        }
    }
    
    /**
     * Add prefix to cache key
     */
    private function prefixKey($key) {
        return $this->config['prefix'] . $key;
    }
    
    /**
     * Get current response headers
     */
    private function getCurrentHeaders() {
        return headers_list();
    }
}

/**
 * File-based cache adapter
 */
class FileCacheAdapter {
    private $cachePath;
    
    public function __construct($config) {
        $this->cachePath = $config['path'];
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    public function get($key) {
        $file = $this->getFilePath($key);
        
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
    
    public function set($key, $value, $ttl) {
        $file = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    public function delete($key) {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    public function deletePattern($pattern) {
        $files = glob($this->cachePath . '/' . md5($pattern) . '*');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        return $deleted > 0;
    }
    
    public function flush() {
        $files = glob($this->cachePath . '/*');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        return $deleted > 0;
    }
    
    public function getStats() {
        $files = glob($this->cachePath . '/*');
        $totalSize = 0;
        $expired = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                $expired++;
            }
        }
        
        return [
            'total_keys' => count($files),
            'total_size' => $totalSize,
            'expired_keys' => $expired
        ];
    }
    
    private function getFilePath($key) {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}

/**
 * Redis cache adapter
 */
class RedisCacheAdapter {
    private $redis;
    
    public function __construct($config) {
        if (!class_exists('Redis')) {
            throw new Exception('Redis extension not installed');
        }
        
        $this->redis = new Redis();
        $this->redis->connect($config['host'], $config['port']);
        
        if ($config['password']) {
            $this->redis->auth($config['password']);
        }
        
        $this->redis->select($config['database']);
    }
    
    public function get($key) {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : null;
    }
    
    public function set($key, $value, $ttl) {
        return $this->redis->setex($key, $ttl, serialize($value));
    }
    
    public function delete($key) {
        return $this->redis->del($key) > 0;
    }
    
    public function deletePattern($pattern) {
        $keys = $this->redis->keys($pattern . '*');
        if (!empty($keys)) {
            return $this->redis->del($keys) > 0;
        }
        return true;
    }
    
    public function flush() {
        return $this->redis->flushDB();
    }
    
    public function getStats() {
        $info = $this->redis->info();
        
        return [
            'total_keys' => $this->redis->dbSize(),
            'memory_usage' => $info['used_memory'] ?? 0,
            'hits' => $info['keyspace_hits'] ?? 0,
            'misses' => $info['keyspace_misses'] ?? 0
        ];
    }
}

/**
 * Memcached cache adapter
 */
class MemcachedCacheAdapter {
    private $memcached;
    
    public function __construct($config) {
        if (!class_exists('Memcached')) {
            throw new Exception('Memcached extension not installed');
        }
        
        $this->memcached = new Memcached();
        $this->memcached->addServers($config['servers']);
    }
    
    public function get($key) {
        $value = $this->memcached->get($key);
        return $this->memcached->getResultCode() === Memcached::RES_SUCCESS ? $value : null;
    }
    
    public function set($key, $value, $ttl) {
        return $this->memcached->set($key, $value, $ttl);
    }
    
    public function delete($key) {
        return $this->memcached->delete($key);
    }
    
    public function deletePattern($pattern) {
        // Memcached doesn't support pattern deletion
        return false;
    }
    
    public function flush() {
        return $this->memcached->flush();
    }
    
    public function getStats() {
        $stats = $this->memcached->getStats();
        $serverStats = reset($stats);
        
        return [
            'total_keys' => $serverStats['curr_items'] ?? 0,
            'memory_usage' => $serverStats['bytes'] ?? 0,
            'hits' => $serverStats['get_hits'] ?? 0,
            'misses' => $serverStats['get_misses'] ?? 0
        ];
    }
}
?>