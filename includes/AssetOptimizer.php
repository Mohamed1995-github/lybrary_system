<?php
/**
 * Optimiseur d'assets pour améliorer les performances
 * Compression, lazy loading, et optimisation des images
 */

class AssetOptimizer {
    private $cache_manager;
    private $logger;
    private $upload_dir;
    private $optimized_dir;
    private $max_image_size = 1920; // Largeur maximale
    private $quality = 85; // Qualité JPEG
    
    public function __construct() {
        $this->cache_manager = CacheManager::getInstance();
        $this->logger = new Logger();
        $this->upload_dir = __DIR__ . '/../uploads';
        $this->optimized_dir = __DIR__ . '/../temp/optimized';
        
        // Créer le dossier d'optimisation
        if (!is_dir($this->optimized_dir)) {
            mkdir($this->optimized_dir, 0755, true);
        }
    }
    
    /**
     * Optimiser une image
     */
    public function optimizeImage($source_path, $options = []) {
        if (!file_exists($source_path)) {
            return false;
        }
        
        $cache_key = 'img_' . md5($source_path . serialize($options));
        $cached_path = $this->cache_manager->get($cache_key);
        
        if ($cached_path && file_exists($cached_path)) {
            return $cached_path;
        }
        
        $info = getimagesize($source_path);
        if (!$info) {
            return false;
        }
        
        $width = $options['width'] ?? $this->max_image_size;
        $height = $options['height'] ?? null;
        $quality = $options['quality'] ?? $this->quality;
        
        // Calculer les nouvelles dimensions
        $new_dimensions = $this->calculateDimensions($info[0], $info[1], $width, $height);
        
        // Créer l'image optimisée
        $optimized_path = $this->createOptimizedImage($source_path, $new_dimensions, $quality);
        
        if ($optimized_path) {
            $this->cache_manager->set($cache_key, $optimized_path, 86400); // Cache 24h
            $this->logger->logInfo("Image optimized", [
                'source' => $source_path,
                'optimized' => $optimized_path,
                'size_reduction' => $this->calculateSizeReduction($source_path, $optimized_path)
            ]);
        }
        
        return $optimized_path;
    }
    
    /**
     * Générer des images responsive
     */
    public function generateResponsiveImages($source_path, $sizes = [320, 640, 1024, 1920]) {
        $responsive_images = [];
        
        foreach ($sizes as $size) {
            $optimized = $this->optimizeImage($source_path, [
                'width' => $size,
                'quality' => $this->quality
            ]);
            
            if ($optimized) {
                $responsive_images[$size] = $optimized;
            }
        }
        
        return $responsive_images;
    }
    
    /**
     * Créer un placeholder pour lazy loading
     */
    public function createPlaceholder($width, $height, $color = '#f3f4f6') {
        $cache_key = "placeholder_{$width}x{$height}_{$color}";
        $cached_path = $this->cache_manager->get($cache_key);
        
        if ($cached_path && file_exists($cached_path)) {
            return $cached_path;
        }
        
        $placeholder_path = $this->optimized_dir . "/placeholder_{$width}x{$height}.jpg";
        
        // Créer une image placeholder simple
        $image = imagecreate($width, $height);
        $bg_color = $this->hexToRgb($color);
        $background = imagecolorallocate($image, $bg_color['r'], $bg_color['g'], $bg_color['b']);
        
        imagejpeg($image, $placeholder_path, 90);
        imagedestroy($image);
        
        $this->cache_manager->set($cache_key, $placeholder_path, 86400);
        
        return $placeholder_path;
    }
    
    /**
     * Générer le HTML pour lazy loading
     */
    public function generateLazyImage($src, $alt = '', $class = '', $sizes = []) {
        $placeholder = $this->createPlaceholder(300, 200);
        $placeholder_data = base64_encode(file_get_contents($placeholder));
        
        $html = '<div class="lazy-image-container" style="position: relative; overflow: hidden;">';
        $html .= '<img src="data:image/jpeg;base64,' . $placeholder_data . '" ';
        $html .= 'data-src="' . htmlspecialchars($src) . '" ';
        $html .= 'alt="' . htmlspecialchars($alt) . '" ';
        $html .= 'class="lazy-image ' . htmlspecialchars($class) . '" ';
        $html .= 'loading="lazy" ';
        
        if (!empty($sizes)) {
            $html .= 'data-sizes="' . implode(',', $sizes) . '" ';
        }
        
        $html .= 'style="width: 100%; height: auto; transition: opacity 0.3s ease;">';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Compresser les fichiers CSS
     */
    public function compressCSS($css_content) {
        // Supprimer les commentaires
        $css_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_content);
        
        // Supprimer les espaces inutiles
        $css_content = preg_replace('/\s+/', ' ', $css_content);
        $css_content = str_replace(['; ', ' {', '} ', '{ ', ' }'], [';', '{', '}', '{', '}'], $css_content);
        
        return trim($css_content);
    }
    
    /**
     * Compresser les fichiers JavaScript
     */
    public function compressJS($js_content) {
        // Supprimer les commentaires
        $js_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js_content);
        $js_content = preg_replace('!//.*$!m', '', $js_content);
        
        // Supprimer les espaces inutiles
        $js_content = preg_replace('/\s+/', ' ', $js_content);
        $js_content = preg_replace('/;\s*}/', '}', $js_content);
        
        return trim($js_content);
    }
    
    /**
     * Minifier le HTML
     */
    public function minifyHTML($html) {
        // Supprimer les commentaires HTML
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        
        // Supprimer les espaces inutiles
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        
        return trim($html);
    }
    
    /**
     * Optimiser les assets en lot
     */
    public function batchOptimize($directory, $file_types = ['jpg', 'jpeg', 'png', 'gif']) {
        $optimized_count = 0;
        $total_size_before = 0;
        $total_size_after = 0;
        
        $files = glob($directory . '/*.{' . implode(',', $file_types) . '}', GLOB_BRACE);
        
        foreach ($files as $file) {
            $original_size = filesize($file);
            $total_size_before += $original_size;
            
            $optimized = $this->optimizeImage($file);
            if ($optimized) {
                $optimized_size = filesize($optimized);
                $total_size_after += $optimized_size;
                $optimized_count++;
                
                $this->logger->logInfo("File optimized", [
                    'file' => basename($file),
                    'original_size' => $original_size,
                    'optimized_size' => $optimized_size,
                    'reduction' => round((1 - $optimized_size / $original_size) * 100, 2) . '%'
                ]);
            }
        }
        
        return [
            'files_optimized' => $optimized_count,
            'total_files' => count($files),
            'size_before' => $total_size_before,
            'size_after' => $total_size_after,
            'total_reduction' => round((1 - $total_size_after / $total_size_before) * 100, 2) . '%'
        ];
    }
    
    /**
     * Nettoyer les fichiers optimisés anciens
     */
    public function cleanupOldOptimized($max_age_days = 7) {
        $max_age = $max_age_days * 86400; // Convertir en secondes
        $cleaned = 0;
        
        $files = glob($this->optimized_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $max_age) {
                unlink($file);
                $cleaned++;
            }
        }
        
        $this->logger->logInfo("Cleaned up old optimized files", ['count' => $cleaned]);
        return $cleaned;
    }
    
    /**
     * Obtenir les statistiques d'optimisation
     */
    public function getOptimizationStats() {
        $stats = [
            'total_optimized' => 0,
            'total_size_saved' => 0,
            'average_reduction' => 0
        ];
        
        $files = glob($this->optimized_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $stats['total_optimized']++;
                // Calculer les économies de taille
                // (Implémentation simplifiée)
            }
        }
        
        return $stats;
    }
    
    private function calculateDimensions($original_width, $original_height, $max_width, $max_height = null) {
        if ($max_height === null) {
            $ratio = $original_height / $original_width;
            $max_height = $max_width * $ratio;
        }
        
        $width_ratio = $max_width / $original_width;
        $height_ratio = $max_height / $original_height;
        $ratio = min($width_ratio, $height_ratio);
        
        return [
            'width' => round($original_width * $ratio),
            'height' => round($original_height * $ratio)
        ];
    }
    
    private function createOptimizedImage($source_path, $dimensions, $quality) {
        $info = getimagesize($source_path);
        $mime_type = $info['mime'];
        
        // Créer l'image source
        switch ($mime_type) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source = imagecreatefrompng($source_path);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }
        
        // Créer l'image de destination
        $destination = imagecreatetruecolor($dimensions['width'], $dimensions['height']);
        
        // Préserver la transparence pour PNG
        if ($mime_type === 'image/png') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $dimensions['width'], $dimensions['height'], $transparent);
        }
        
        // Redimensionner
        imagecopyresampled($destination, $source, 0, 0, 0, 0, 
                          $dimensions['width'], $dimensions['height'], 
                          $info[0], $info[1]);
        
        // Sauvegarder
        $filename = 'optimized_' . md5($source_path) . '_' . $dimensions['width'] . 'x' . $dimensions['height'] . '.jpg';
        $output_path = $this->optimized_dir . '/' . $filename;
        
        $success = imagejpeg($destination, $output_path, $quality);
        
        // Nettoyer
        imagedestroy($source);
        imagedestroy($destination);
        
        return $success ? $output_path : false;
    }
    
    private function calculateSizeReduction($original_path, $optimized_path) {
        $original_size = filesize($original_path);
        $optimized_size = filesize($optimized_path);
        
        return round((1 - $optimized_size / $original_size) * 100, 2);
    }
    
    private function hexToRgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }
}
?>