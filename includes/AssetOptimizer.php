<?php
/**
 * Asset Optimizer Class
 * Handles compression, caching, and optimization of CSS/JS assets
 */
class AssetOptimizer {
    private static $instance = null;
    private $cacheDir;
    private $assetsDir;
    private $version;
    
    private function __construct() {
        $this->assetsDir = __DIR__ . '/../public/assets';
        $this->cacheDir = __DIR__ . '/../cache/assets';
        $this->version = $this->getAssetVersion();
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get optimized CSS with cache busting
     */
    public function getOptimizedCSS($files = []) {
        if (empty($files)) {
            $files = ['css/style.min.css'];
        }
        
        $cacheKey = 'css_' . md5(implode('|', $files) . $this->version);
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.css';
        
        // Check if cached version exists and is fresh
        if (file_exists($cacheFile) && $this->isCacheFresh($cacheFile, $files)) {
            return $this->generateAssetUrl($cacheKey . '.css');
        }
        
        // Combine and optimize CSS files
        $combinedCSS = $this->combineCSS($files);
        $optimizedCSS = $this->optimizeCSS($combinedCSS);
        
        // Save to cache
        file_put_contents($cacheFile, $optimizedCSS);
        
        return $this->generateAssetUrl($cacheKey . '.css');
    }
    
    /**
     * Get optimized JavaScript with cache busting
     */
    public function getOptimizedJS($files = []) {
        if (empty($files)) {
            $files = ['js/script.min.js'];
        }
        
        $cacheKey = 'js_' . md5(implode('|', $files) . $this->version);
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.js';
        
        // Check if cached version exists and is fresh
        if (file_exists($cacheFile) && $this->isCacheFresh($cacheFile, $files)) {
            return $this->generateAssetUrl($cacheKey . '.js');
        }
        
        // Combine and optimize JS files
        $combinedJS = $this->combineJS($files);
        
        // Save to cache
        file_put_contents($cacheFile, $combinedJS);
        
        return $this->generateAssetUrl($cacheKey . '.js');
    }
    
    /**
     * Generate critical CSS for above-the-fold content
     */
    public function getCriticalCSS() {
        $criticalCSS = '
        :root{--primary-color:#2563eb;--primary-hover:#1d4ed8;--bg-primary:#fff;--text-primary:#1f2937;--shadow-lg:0 10px 15px -3px rgb(0 0 0/.1)}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:var(--text-primary);line-height:1.6;direction:rtl;min-height:100vh}
        .container{max-width:1200px;margin:0 auto;padding:2rem}
        .card{background:var(--bg-primary);border-radius:.75rem;box-shadow:var(--shadow-lg);padding:2rem;margin-bottom:2rem}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:.75rem 1.5rem;border:none;border-radius:.5rem;font-weight:500;text-decoration:none;cursor:pointer;transition:all .2s ease}
        .btn-primary{background:var(--primary-color);color:#fff}
        ';
        
        return $criticalCSS;
    }
    
    /**
     * Combine multiple CSS files
     */
    private function combineCSS($files) {
        $combined = '';
        
        foreach ($files as $file) {
            $filePath = $this->assetsDir . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $combined .= $content . "\n";
            }
        }
        
        return $combined;
    }
    
    /**
     * Combine multiple JS files
     */
    private function combineJS($files) {
        $combined = '';
        
        foreach ($files as $file) {
            $filePath = $this->assetsDir . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $combined .= $content . "\n";
            }
        }
        
        return $combined;
    }
    
    /**
     * Optimize CSS content
     */
    private function optimizeCSS($css) {
        // Remove comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        
        // Remove unnecessary whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove trailing semicolons before closing braces
        $css = str_replace(';}', '}', $css);
        
        // Remove spaces around specific characters
        $css = str_replace([' {', '{ ', ' }', '} ', '; ', ' ;', ': ', ' :', ', ', ' ,'], 
                          ['{', '{', '}', '}', ';', ';', ':', ':', ',', ','], $css);
        
        return trim($css);
    }
    
    /**
     * Check if cache file is fresh
     */
    private function isCacheFresh($cacheFile, $sourceFiles) {
        $cacheTime = filemtime($cacheFile);
        
        foreach ($sourceFiles as $file) {
            $filePath = $this->assetsDir . '/' . $file;
            if (file_exists($filePath) && filemtime($filePath) > $cacheTime) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Generate asset URL with cache busting
     */
    private function generateAssetUrl($filename) {
        return '/cache/assets/' . $filename . '?v=' . $this->version;
    }
    
    /**
     * Get asset version for cache busting
     */
    private function getAssetVersion() {
        // Use file modification time or git commit hash
        $versionFile = __DIR__ . '/../version.txt';
        
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        
        // Fallback to current timestamp
        return date('YmdHis');
    }
    
    /**
     * Serve cached asset with proper headers
     */
    public function serveAsset($filename) {
        $filePath = $this->cacheDir . '/' . $filename;
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            return false;
        }
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript'
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        // Set aggressive caching headers
        header('Cache-Control: public, max-age=31536000, immutable'); // 1 year
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        header('ETag: "' . md5_file($filePath) . '"');
        
        // Enable gzip compression
        if (function_exists('gzencode') && strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
            header('Content-Encoding: gzip');
            echo gzencode(file_get_contents($filePath));
        } else {
            readfile($filePath);
        }
        
        return true;
    }
    
    /**
     * Preload critical resources
     */
    public function getPreloadHeaders() {
        $headers = [];
        
        // Preload critical CSS
        $headers[] = '<' . $this->getOptimizedCSS() . '>; rel=preload; as=style';
        
        // Preload critical JS
        $headers[] = '<' . $this->getOptimizedJS() . '>; rel=preload; as=script';
        
        // Preload fonts
        $headers[] = '<https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap>; rel=preload; as=style';
        
        return $headers;
    }
    
    /**
     * Clean old cache files
     */
    public function cleanCache($maxAge = 86400) { // 24 hours
        $files = glob($this->cacheDir . '/*');
        $cutoff = time() - $maxAge;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
?>