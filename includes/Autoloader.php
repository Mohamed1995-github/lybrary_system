<?php
/**
 * Optimized Autoloader for Library System
 * Handles class loading with caching and performance optimization
 */
class Autoloader {
    private static $instance = null;
    private $classMap = [];
    private $cacheFile;
    private $basePath;
    
    private function __construct() {
        $this->basePath = __DIR__;
        $this->cacheFile = $this->basePath . '/../cache/classmap.cache';
        $this->loadClassMap();
        $this->registerAutoloader();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Register the autoloader
     */
    private function registerAutoloader() {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }
    
    /**
     * Load a class
     */
    public function loadClass($className) {
        // Check if class is already loaded
        if (class_exists($className, false)) {
            return true;
        }
        
        // Try to find in class map
        if (isset($this->classMap[$className])) {
            $file = $this->classMap[$className];
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        
        // Try to find using naming conventions
        $file = $this->findClassFile($className);
        if ($file && file_exists($file)) {
            require_once $file;
            $this->classMap[$className] = $file;
            $this->saveClassMap();
            return true;
        }
        
        return false;
    }
    
    /**
     * Find class file using naming conventions
     */
    private function findClassFile($className) {
        // Standard locations to search
        $searchPaths = [
            $this->basePath . '/',
            $this->basePath . '/../config/',
            $this->basePath . '/../modules/',
        ];
        
        foreach ($searchPaths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                return $file;
            }
        }
        
        return null;
    }
    
    /**
     * Load class map from cache
     */
    private function loadClassMap() {
        if (file_exists($this->cacheFile)) {
            $this->classMap = unserialize(file_get_contents($this->cacheFile));
        } else {
            $this->buildClassMap();
        }
    }
    
    /**
     * Save class map to cache
     */
    private function saveClassMap() {
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        file_put_contents($this->cacheFile, serialize($this->classMap), LOCK_EX);
    }
    
    /**
     * Build class map by scanning directories
     */
    private function buildClassMap() {
        $directories = [
            $this->basePath,
            $this->basePath . '/../config',
            $this->basePath . '/../modules'
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->scanDirectory($dir);
            }
        }
        
        $this->saveClassMap();
    }
    
    /**
     * Scan directory for PHP files
     */
    private function scanDirectory($dir) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $className = $this->extractClassName($file->getPathname());
                if ($className) {
                    $this->classMap[$className] = $file->getPathname();
                }
            }
        }
    }
    
    /**
     * Extract class name from PHP file
     */
    private function extractClassName($file) {
        $content = file_get_contents($file);
        
        // Simple regex to find class declaration
        if (preg_match('/^class\s+(\w+)/m', $content, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Preload critical classes
     */
    public function preloadCriticalClasses() {
        $criticalClasses = [
            'Database',
            'Security',
            'Logger',
            'AssetOptimizer',
            'DatabaseOptimizer'
        ];
        
        foreach ($criticalClasses as $className) {
            $this->loadClass($className);
        }
    }
    
    /**
     * Get class map statistics
     */
    public function getStats() {
        return [
            'total_classes' => count($this->classMap),
            'cache_file' => $this->cacheFile,
            'cache_exists' => file_exists($this->cacheFile),
            'cache_size' => file_exists($this->cacheFile) ? filesize($this->cacheFile) : 0
        ];
    }
    
    /**
     * Clear class map cache
     */
    public function clearCache() {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        $this->classMap = [];
        $this->buildClassMap();
    }
}

// Initialize autoloader
Autoloader::getInstance();
?>