<?php
/**
 * Optimized Entry Point for Library System
 * Includes performance optimizations, caching, and asset optimization
 */

// Start output buffering with gzip compression
if (!ob_get_level() && extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

// Performance monitoring
$start_time = microtime(true);
$start_memory = memory_get_usage();

// Essential configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Optimized session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cache_limiter', 'nocache');
ini_set('session.cache_expire', 180);

// Start session with optimization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers (set early for maximum protection)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Include optimized autoloader first
require_once __DIR__ . '/../includes/Autoloader.php';

// Preload critical classes for better performance
$autoloader = Autoloader::getInstance();
$autoloader->preloadCriticalClasses();

// Initialize optimizers
$assetOptimizer = AssetOptimizer::getInstance();
$dbOptimizer = DatabaseOptimizer::getInstance();

// Set preload headers for critical resources
$preloadHeaders = $assetOptimizer->getPreloadHeaders();
foreach ($preloadHeaders as $header) {
    header('Link: ' . $header);
}

// Router with caching
$request = $_SERVER['REQUEST_URI'] ?? '';
$request = parse_url($request, PHP_URL_PATH);
$request = trim($request, '/');

// Handle asset requests with optimization
if (strpos($request, 'assets/') === 0) {
    handleAssetRequest($request, $assetOptimizer);
    exit;
}

// Handle cached asset requests
if (strpos($request, 'cache/assets/') === 0) {
    $filename = basename($request);
    if ($assetOptimizer->serveAsset($filename)) {
        exit;
    }
}

// Default route handling
if (empty($request)) {
    header('Location: login.php');
    exit;
}

// Page routing with caching
$allowedPages = [
    'login' => 'login.php',
    'employee_login' => 'employee_login.php',
    'dashboard' => 'dashboard.php',
    'employee_dashboard' => 'employee_dashboard.php',
    'logout' => 'logout.php'
];

$page = null;
foreach ($allowedPages as $key => $file) {
    if (strpos($request, $key) === 0) {
        $page = $file;
        break;
    }
}

// 404 handling with optimized response
if (!$page) {
    handle404($request, $assetOptimizer);
    exit;
}

// Authentication check for protected pages
$protectedPages = ['dashboard', 'employee_dashboard'];
$isProtected = false;

foreach ($protectedPages as $protectedPage) {
    if (strpos($request, $protectedPage) === 0) {
        $isProtected = true;
        break;
    }
}

if ($isProtected) {
    if (!isset($_SESSION['uid'])) {
        $logger = new Logger();
        $logger->logSecurity('Unauthorized access attempt', [
            'page' => $request,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        header('Location: login.php');
        exit;
    }
    
    // Session timeout check (1 hour)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) {
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
    
    $_SESSION['login_time'] = time();
}

// Include the requested page with optimization
$filePath = __DIR__ . '/' . $page;
if (file_exists($filePath) && is_file($filePath)) {
    // Log page access
    $logger = new Logger();
    $logger->logInfo('Page accessed', [
        'page' => $request,
        'user_id' => $_SESSION['uid'] ?? 'guest',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'load_time' => round((microtime(true) - $start_time) * 1000, 2) . 'ms',
        'memory_usage' => round((memory_get_usage() - $start_memory) / 1024, 2) . 'KB'
    ]);
    
    include $filePath;
} else {
    handle404($request, $assetOptimizer);
}

/**
 * Handle asset requests with optimization
 */
function handleAssetRequest($request, $assetOptimizer) {
    $filePath = __DIR__ . '/' . $request;
    
    if (!file_exists($filePath) || !is_file($filePath)) {
        http_response_code(404);
        return;
    }
    
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'webp' => 'image/webp'
    ];
    
    if (!isset($mimeTypes[$extension])) {
        http_response_code(403);
        return;
    }
    
    // Set appropriate headers
    header('Content-Type: ' . $mimeTypes[$extension]);
    
    // Aggressive caching for assets
    $maxAge = 31536000; // 1 year
    header('Cache-Control: public, max-age=' . $maxAge . ', immutable');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
    
    // ETag for cache validation
    $etag = '"' . md5_file($filePath) . '"';
    header('ETag: ' . $etag);
    
    // Check if client has cached version
    $clientEtag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    if ($clientEtag === $etag) {
        http_response_code(304);
        return;
    }
    
    // Serve compressed content if possible
    if (in_array($extension, ['css', 'js', 'svg']) && 
        function_exists('gzencode') && 
        strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
        
        header('Content-Encoding: gzip');
        echo gzencode(file_get_contents($filePath));
    } else {
        readfile($filePath);
    }
}

/**
 * Handle 404 errors with optimized response
 */
function handle404($request, $assetOptimizer) {
    http_response_code(404);
    
    $logger = new Logger();
    $logger->logError('Page not found', ['request' => $request]);
    
    // Get critical CSS for fast rendering
    $criticalCSS = $assetOptimizer->getCriticalCSS();
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page non trouvée - Système de Bibliothèque</title>
        <style><?= $criticalCSS ?></style>
        <link rel="preload" href="<?= $assetOptimizer->getOptimizedCSS() ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="<?= $assetOptimizer->getOptimizedCSS() ?>"></noscript>
    </head>
    <body>
        <div class="container">
            <div class="card text-center">
                <h1 style="font-size: 6rem; color: #ef4444; margin-bottom: 1rem;">404</h1>
                <h2>Page non trouvée</h2>
                <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
                <a href="login.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        </div>
        
        <script>
        // Lazy load non-critical CSS
        (function() {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = '<?= $assetOptimizer->getOptimizedCSS() ?>';
            document.head.appendChild(link);
        })();
        </script>
    </body>
    </html>
    <?php
}

// Performance monitoring output (development only)
if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
    $end_time = microtime(true);
    $end_memory = memory_get_usage();
    
    echo "<!-- Performance Stats: ";
    echo "Execution Time: " . round(($end_time - $start_time) * 1000, 2) . "ms, ";
    echo "Memory Usage: " . round(($end_memory - $start_memory) / 1024, 2) . "KB, ";
    echo "Peak Memory: " . round(memory_get_peak_usage() / 1024, 2) . "KB ";
    echo "-->";
}

// Clean up optimizers periodically
if (rand(1, 100) === 1) { // 1% chance
    $assetOptimizer->cleanCache();
    $dbOptimizer->cleanExpiredCache();
}
?>