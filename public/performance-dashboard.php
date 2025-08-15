<?php
/**
 * Performance Monitoring Dashboard
 * Displays optimization metrics, bundle sizes, and performance statistics
 */

require_once __DIR__ . '/../includes/Autoloader.php';

// Check if user has admin access
session_start();
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied');
}

// Initialize performance classes
$assetOptimizer = AssetOptimizer::getInstance();
$dbOptimizer = DatabaseOptimizer::getInstance();
$cacheManager = CacheManager::getInstance();
$imageOptimizer = ImageOptimizer::getInstance();

// Collect performance metrics
$metrics = [
    'assets' => [
        'css_original_size' => file_exists(__DIR__ . '/assets/css/style.css') ? filesize(__DIR__ . '/assets/css/style.css') : 0,
        'css_minified_size' => file_exists(__DIR__ . '/assets/css/style.min.css') ? filesize(__DIR__ . '/assets/css/style.min.css') : 0,
        'js_original_size' => file_exists(__DIR__ . '/assets/js/script.js') ? filesize(__DIR__ . '/assets/js/script.js') : 0,
        'js_minified_size' => file_exists(__DIR__ . '/assets/js/script.min.js') ? filesize(__DIR__ . '/assets/js/script.min.js') : 0,
    ],
    'database' => $dbOptimizer->getDatabaseStats(),
    'cache' => $cacheManager->getStats(),
    'autoloader' => Autoloader::getInstance()->getStats(),
    'server' => [
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'opcache_enabled' => function_exists('opcache_get_status') ? opcache_get_status()['opcache_enabled'] : false,
        'current_memory' => memory_get_usage(true),
        'peak_memory' => memory_get_peak_usage(true)
    ]
];

// Calculate savings
$cssSavings = $metrics['assets']['css_original_size'] > 0 ? 
    round((1 - $metrics['assets']['css_minified_size'] / $metrics['assets']['css_original_size']) * 100, 2) : 0;
$jsSavings = $metrics['assets']['js_original_size'] > 0 ? 
    round((1 - $metrics['assets']['js_minified_size'] / $metrics['assets']['js_original_size']) * 100, 2) : 0;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Dashboard - Library System</title>
    <style><?= $assetOptimizer->getCriticalCSS() ?></style>
    <link rel="preload" href="<?= $assetOptimizer->getOptimizedCSS() ?>" as="style" onload="this.rel='stylesheet'">
    <style>
        .dashboard { padding: 2rem; max-width: 1400px; margin: 0 auto; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .metric-card { background: var(--bg-primary); padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); }
        .metric-title { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary); }
        .metric-value { font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem; }
        .metric-label { color: var(--text-secondary); font-size: 0.875rem; }
        .progress-bar { width: 100%; height: 8px; background: var(--bg-secondary); border-radius: 4px; overflow: hidden; margin-top: 0.5rem; }
        .progress-fill { height: 100%; background: var(--success-color); transition: width 0.3s ease; }
        .status-good { color: var(--success-color); }
        .status-warning { color: var(--warning-color); }
        .status-error { color: var(--error-color); }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        .table th { background: var(--bg-secondary); font-weight: 600; }
        .recommendations { background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-lg); margin-top: 2rem; }
        .recommendation { padding: 1rem; margin-bottom: 1rem; border-left: 4px solid var(--primary-color); background: var(--bg-primary); border-radius: var(--radius-md); }
        .recommendation.warning { border-left-color: var(--warning-color); }
        .recommendation.error { border-left-color: var(--error-color); }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Performance Dashboard</h1>
        <p>Monitor and analyze system performance metrics and optimizations</p>
        
        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-title">CSS Optimization</div>
                <div class="metric-value"><?= $cssSavings ?>%</div>
                <div class="metric-label">Size Reduction</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $cssSavings ?>%"></div>
                </div>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary);">
                    <?= number_format($metrics['assets']['css_original_size']) ?> → <?= number_format($metrics['assets']['css_minified_size']) ?> bytes
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-title">JavaScript Optimization</div>
                <div class="metric-value"><?= $jsSavings ?>%</div>
                <div class="metric-label">Size Reduction</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $jsSavings ?>%"></div>
                </div>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary);">
                    <?= number_format($metrics['assets']['js_original_size']) ?> → <?= number_format($metrics['assets']['js_minified_size']) ?> bytes
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-title">Cache Hit Ratio</div>
                <div class="metric-value"><?= $metrics['cache']['hit_ratio'] ?? 0 ?>%</div>
                <div class="metric-label">Cache Performance</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $metrics['cache']['hit_ratio'] ?? 0 ?>%"></div>
                </div>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary);">
                    <?= $metrics['cache']['hits'] ?? 0 ?> hits, <?= $metrics['cache']['misses'] ?? 0 ?> misses
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-title">Memory Usage</div>
                <div class="metric-value"><?= round($metrics['server']['current_memory'] / 1024 / 1024, 1) ?>MB</div>
                <div class="metric-label">Current / Peak: <?= round($metrics['server']['peak_memory'] / 1024 / 1024, 1) ?>MB</div>
                <?php
                $memoryLimit = ini_get('memory_limit');
                $memoryLimitBytes = $memoryLimit === '-1' ? PHP_INT_MAX : 
                    (int)$memoryLimit * (strpos($memoryLimit, 'G') ? 1024*1024*1024 : 1024*1024);
                $memoryUsagePercent = round($metrics['server']['current_memory'] / $memoryLimitBytes * 100, 1);
                ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $memoryUsagePercent ?>%"></div>
                </div>
            </div>
        </div>
        
        <!-- Database Performance -->
        <div class="metric-card">
            <div class="metric-title">Database Performance</div>
            
            <?php if (isset($metrics['database']['table_sizes'])): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Size (MB)</th>
                        <th>Rows</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metrics['database']['table_sizes'] as $table): ?>
                    <tr>
                        <td><?= htmlspecialchars($table['table_name']) ?></td>
                        <td><?= $table['size_mb'] ?></td>
                        <td><?= number_format($table['table_rows']) ?></td>
                        <td>
                            <?php if ($table['size_mb'] > 100): ?>
                                <span class="status-warning">Large</span>
                            <?php elseif ($table['size_mb'] > 10): ?>
                                <span class="status-warning">Medium</span>
                            <?php else: ?>
                                <span class="status-good">Good</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <!-- Server Information -->
        <div class="metric-card">
            <div class="metric-title">Server Configuration</div>
            <table class="table">
                <tbody>
                    <tr>
                        <td>PHP Version</td>
                        <td><?= $metrics['server']['php_version'] ?></td>
                    </tr>
                    <tr>
                        <td>Memory Limit</td>
                        <td><?= $metrics['server']['memory_limit'] ?></td>
                    </tr>
                    <tr>
                        <td>Max Execution Time</td>
                        <td><?= $metrics['server']['max_execution_time'] ?>s</td>
                    </tr>
                    <tr>
                        <td>OpCache</td>
                        <td>
                            <?php if ($metrics['server']['opcache_enabled']): ?>
                                <span class="status-good">Enabled</span>
                            <?php else: ?>
                                <span class="status-error">Disabled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Autoloader Classes</td>
                        <td><?= $metrics['autoloader']['total_classes'] ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Optimization Recommendations -->
        <div class="recommendations">
            <h2>Optimization Recommendations</h2>
            
            <?php
            $recommendations = $dbOptimizer->getOptimizationRecommendations();
            foreach ($recommendations as $rec): ?>
                <div class="recommendation <?= $rec['severity'] ?>">
                    <strong><?= htmlspecialchars($rec['message']) ?></strong>
                    <?php if (isset($rec['tables'])): ?>
                        <ul style="margin-top: 0.5rem;">
                            <?php foreach ($rec['tables'] as $table): ?>
                                <li><?= is_array($table) ? htmlspecialchars($table['TABLE_NAME'] ?? $table) : htmlspecialchars($table) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$metrics['server']['opcache_enabled']): ?>
                <div class="recommendation error">
                    <strong>Enable OpCache</strong>
                    <p>OpCache is disabled. Enable it to significantly improve PHP performance.</p>
                </div>
            <?php endif; ?>
            
            <?php if ($cssSavings < 30): ?>
                <div class="recommendation warning">
                    <strong>CSS Optimization</strong>
                    <p>CSS minification savings are below 30%. Consider removing unused CSS rules.</p>
                </div>
            <?php endif; ?>
            
            <?php if (($metrics['cache']['hit_ratio'] ?? 0) < 70): ?>
                <div class="recommendation warning">
                    <strong>Cache Performance</strong>
                    <p>Cache hit ratio is below 70%. Consider implementing more aggressive caching strategies.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Actions -->
        <div style="margin-top: 2rem; text-align: center;">
            <a href="?action=clear_cache" class="btn btn-primary" onclick="return confirm('Clear all cache?')">Clear Cache</a>
            <a href="?action=optimize_db" class="btn btn-secondary" onclick="return confirm('Create recommended database indexes?')">Optimize Database</a>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
    </div>
    
    <script>
    // Auto-refresh every 30 seconds
    setTimeout(function() {
        window.location.reload();
    }, 30000);
    
    // Animate progress bars
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('.progress-fill');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 500);
        });
    });
    </script>
</body>
</html>

<?php
// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'clear_cache':
            $cacheManager->flush();
            $assetOptimizer->cleanCache();
            $imageOptimizer->cleanCache();
            header('Location: performance-dashboard.php?message=cache_cleared');
            exit;
            
        case 'optimize_db':
            $indexesCreated = $dbOptimizer->createRecommendedIndexes();
            header('Location: performance-dashboard.php?message=db_optimized&count=' . count($indexesCreated));
            exit;
    }
}

if (isset($_GET['message'])) {
    echo '<script>alert("' . htmlspecialchars($_GET['message']) . '");</script>';
}
?>