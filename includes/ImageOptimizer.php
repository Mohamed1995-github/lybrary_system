<?php
/**
 * Image Optimizer Class
 * Handles WebP conversion, lazy loading, and responsive image optimization
 */
class ImageOptimizer {
    private static $instance = null;
    private $cacheDir;
    private $uploadsDir;
    private $supportedFormats = ['jpg', 'jpeg', 'png', 'gif'];
    
    private function __construct() {
        $this->cacheDir = __DIR__ . '/../cache/images';
        $this->uploadsDir = __DIR__ . '/../public/uploads';
        
        // Create directories if they don't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Optimize and convert image to WebP format
     */
    public function optimizeImage($sourcePath, $quality = 85) {
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        $pathInfo = pathinfo($sourcePath);
        $extension = strtolower($pathInfo['extension']);
        
        if (!in_array($extension, $this->supportedFormats)) {
            return false;
        }
        
        $filename = $pathInfo['filename'];
        $webpPath = $this->cacheDir . '/' . $filename . '.webp';
        
        // Check if WebP version already exists and is newer
        if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
            return $webpPath;
        }
        
        // Create image resource based on format
        $image = $this->createImageResource($sourcePath, $extension);
        if (!$image) {
            return false;
        }
        
        // Convert to WebP if supported
        if (function_exists('imagewebp')) {
            if (imagewebp($image, $webpPath, $quality)) {
                imagedestroy($image);
                return $webpPath;
            }
        }
        
        // Fallback: optimize original format
        $optimizedPath = $this->cacheDir . '/' . $pathInfo['basename'];
        $this->optimizeOriginalFormat($image, $optimizedPath, $extension, $quality);
        imagedestroy($image);
        
        return $optimizedPath;
    }
    
    /**
     * Generate responsive image variants
     */
    public function generateResponsiveImages($sourcePath, $sizes = [320, 640, 960, 1280]) {
        if (!file_exists($sourcePath)) {
            return [];
        }
        
        $pathInfo = pathinfo($sourcePath);
        $filename = $pathInfo['filename'];
        $extension = strtolower($pathInfo['extension']);
        
        $variants = [];
        
        // Get original dimensions
        list($originalWidth, $originalHeight) = getimagesize($sourcePath);
        
        foreach ($sizes as $width) {
            if ($width >= $originalWidth) {
                continue; // Don't upscale
            }
            
            $height = intval(($originalHeight / $originalWidth) * $width);
            $variantPath = $this->cacheDir . '/' . $filename . '_' . $width . 'w.webp';
            
            if (!file_exists($variantPath) || filemtime($variantPath) < filemtime($sourcePath)) {
                $this->resizeImage($sourcePath, $variantPath, $width, $height);
            }
            
            if (file_exists($variantPath)) {
                $variants[$width] = $variantPath;
            }
        }
        
        return $variants;
    }
    
    /**
     * Generate lazy loading HTML for images
     */
    public function generateLazyImage($src, $alt = '', $class = '', $sizes = []) {
        $optimizedPath = $this->optimizeImage($src);
        $responsiveImages = empty($sizes) ? [] : $this->generateResponsiveImages($src, $sizes);
        
        $webpSupported = $this->isWebPSupported();
        $srcset = '';
        
        if (!empty($responsiveImages)) {
            $srcsetParts = [];
            foreach ($responsiveImages as $width => $path) {
                $url = str_replace(__DIR__ . '/../public', '', $path);
                $srcsetParts[] = $url . ' ' . $width . 'w';
            }
            $srcset = implode(', ', $srcsetParts);
        }
        
        // Generate picture element with WebP support
        $html = '<picture>';
        
        if ($webpSupported && $optimizedPath && pathinfo($optimizedPath, PATHINFO_EXTENSION) === 'webp') {
            $webpUrl = str_replace(__DIR__ . '/../public', '', $optimizedPath);
            $html .= '<source type="image/webp" src="' . htmlspecialchars($webpUrl) . '"';
            if ($srcset) {
                $html .= ' srcset="' . htmlspecialchars($srcset) . '"';
            }
            $html .= '>';
        }
        
        $originalUrl = str_replace(__DIR__ . '/../public', '', $src);
        $html .= '<img loading="lazy" src="' . htmlspecialchars($originalUrl) . '"';
        
        if ($alt) {
            $html .= ' alt="' . htmlspecialchars($alt) . '"';
        }
        
        if ($class) {
            $html .= ' class="' . htmlspecialchars($class) . '"';
        }
        
        if ($srcset && !$webpSupported) {
            $html .= ' srcset="' . htmlspecialchars($srcset) . '"';
        }
        
        $html .= '>';
        $html .= '</picture>';
        
        return $html;
    }
    
    /**
     * Create image resource from file
     */
    private function createImageResource($path, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'png':
                return imagecreatefrompng($path);
            case 'gif':
                return imagecreatefromgif($path);
            default:
                return false;
        }
    }
    
    /**
     * Optimize image in original format
     */
    private function optimizeOriginalFormat($image, $outputPath, $extension, $quality) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image, $outputPath, $quality);
            case 'png':
                // PNG compression level (0-9)
                $pngQuality = intval((100 - $quality) / 10);
                return imagepng($image, $outputPath, $pngQuality);
            case 'gif':
                return imagegif($image, $outputPath);
            default:
                return false;
        }
    }
    
    /**
     * Resize image to specific dimensions
     */
    private function resizeImage($sourcePath, $outputPath, $width, $height) {
        $sourceImage = $this->createImageResource($sourcePath, pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (!$sourceImage) {
            return false;
        }
        
        $resizedImage = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG and GIF
        if (pathinfo($sourcePath, PATHINFO_EXTENSION) === 'png') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefill($resizedImage, 0, 0, $transparent);
        }
        
        list($sourceWidth, $sourceHeight) = getimagesize($sourcePath);
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        
        // Save as WebP if possible
        if (function_exists('imagewebp')) {
            $result = imagewebp($resizedImage, $outputPath, 85);
        } else {
            $result = imagejpeg($resizedImage, $outputPath, 85);
        }
        
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        
        return $result;
    }
    
    /**
     * Check if WebP is supported by client
     */
    private function isWebPSupported() {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'image/webp') !== false;
    }
    
    /**
     * Get image metadata
     */
    public function getImageMetadata($path) {
        if (!file_exists($path)) {
            return null;
        }
        
        $info = getimagesize($path);
        if (!$info) {
            return null;
        }
        
        return [
            'width' => $info[0],
            'height' => $info[1],
            'mime' => $info['mime'],
            'size' => filesize($path),
            'aspect_ratio' => $info[0] / $info[1]
        ];
    }
    
    /**
     * Clean old cached images
     */
    public function cleanCache($maxAge = 604800) { // 1 week
        $files = glob($this->cacheDir . '/*');
        $cutoff = time() - $maxAge;
        $cleaned = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Generate CSS for lazy loading
     */
    public function getLazyLoadingCSS() {
        return '
        img[loading="lazy"] {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        img[loading="lazy"].loaded {
            opacity: 1;
        }
        
        .lazy-placeholder {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        ';
    }
    
    /**
     * Generate JavaScript for lazy loading
     */
    public function getLazyLoadingJS() {
        return '
        (function() {
            if ("loading" in HTMLImageElement.prototype) {
                // Native lazy loading supported
                const images = document.querySelectorAll("img[loading=lazy]");
                images.forEach(img => {
                    img.addEventListener("load", function() {
                        this.classList.add("loaded");
                    });
                });
            } else {
                // Fallback for browsers without native lazy loading
                const script = document.createElement("script");
                script.src = "https://cdn.jsdelivr.net/npm/intersection-observer@0.12.0/intersection-observer.js";
                script.onload = function() {
                    const imageObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src;
                                img.classList.remove("lazy");
                                img.classList.add("loaded");
                                imageObserver.unobserve(img);
                            }
                        });
                    });
                    
                    const images = document.querySelectorAll("img[loading=lazy]");
                    images.forEach(img => imageObserver.observe(img));
                };
                document.head.appendChild(script);
            }
        })();
        ';
    }
}
?>