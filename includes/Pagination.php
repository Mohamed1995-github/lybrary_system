<?php
/**
 * Système de pagination pour optimiser l'affichage des listes
 */

class Pagination {
    private $current_page;
    private $total_items;
    private $items_per_page;
    private $total_pages;
    private $offset;
    private $cache_manager;
    private $logger;
    
    public function __construct($total_items, $items_per_page = 20, $current_page = 1) {
        $this->total_items = (int)$total_items;
        $this->items_per_page = (int)$items_per_page;
        $this->current_page = max(1, (int)$current_page);
        $this->total_pages = ceil($this->total_items / $this->items_per_page);
        $this->offset = ($this->current_page - 1) * $this->items_per_page;
        $this->cache_manager = CacheManager::getInstance();
        $this->logger = new Logger();
    }
    
    /**
     * Obtenir les données paginées
     */
    public function paginate($callback, $cache_key = null, $cache_ttl = 300) {
        if ($cache_key) {
            $cache_key .= "_page_{$this->current_page}_limit_{$this->items_per_page}";
            return $this->cache_manager->remember($cache_key, $callback, $cache_ttl);
        }
        
        return $callback();
    }
    
    /**
     * Obtenir les informations de pagination
     */
    public function getInfo() {
        return [
            'current_page' => $this->current_page,
            'total_pages' => $this->total_pages,
            'total_items' => $this->total_items,
            'items_per_page' => $this->items_per_page,
            'offset' => $this->offset,
            'has_previous' => $this->current_page > 1,
            'has_next' => $this->current_page < $this->total_pages,
            'previous_page' => max(1, $this->current_page - 1),
            'next_page' => min($this->total_pages, $this->current_page + 1),
            'start_item' => $this->offset + 1,
            'end_item' => min($this->offset + $this->items_per_page, $this->total_items)
        ];
    }
    
    /**
     * Générer les liens de pagination
     */
    public function generateLinks($base_url, $params = []) {
        $info = $this->getInfo();
        $links = [];
        
        // Lien précédent
        if ($info['has_previous']) {
            $links['previous'] = $this->buildUrl($base_url, $info['previous_page'], $params);
        }
        
        // Lien suivant
        if ($info['has_next']) {
            $links['next'] = $this->buildUrl($base_url, $info['next_page'], $params);
        }
        
        // Pages numérotées
        $links['pages'] = $this->generatePageNumbers($base_url, $params);
        
        return $links;
    }
    
    /**
     * Générer les numéros de page
     */
    private function generatePageNumbers($base_url, $params = []) {
        $pages = [];
        $start = max(1, $this->current_page - 2);
        $end = min($this->total_pages, $this->current_page + 2);
        
        // Toujours inclure la première page
        if ($start > 1) {
            $pages[] = [
                'number' => 1,
                'url' => $this->buildUrl($base_url, 1, $params),
                'active' => false
            ];
            
            if ($start > 2) {
                $pages[] = ['number' => '...', 'url' => null, 'active' => false];
            }
        }
        
        // Pages centrales
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = [
                'number' => $i,
                'url' => $this->buildUrl($base_url, $i, $params),
                'active' => $i == $this->current_page
            ];
        }
        
        // Toujours inclure la dernière page
        if ($end < $this->total_pages) {
            if ($end < $this->total_pages - 1) {
                $pages[] = ['number' => '...', 'url' => null, 'active' => false];
            }
            
            $pages[] = [
                'number' => $this->total_pages,
                'url' => $this->buildUrl($base_url, $this->total_pages, $params),
                'active' => false
            ];
        }
        
        return $pages;
    }
    
    /**
     * Construire une URL avec les paramètres
     */
    private function buildUrl($base_url, $page, $params = []) {
        $params['page'] = $page;
        $query = http_build_query($params);
        return $base_url . ($query ? '?' . $query : '');
    }
    
    /**
     * Obtenir le HTML de pagination
     */
    public function render($base_url, $params = [], $lang = 'ar') {
        $info = $this->getInfo();
        $links = $this->generateLinks($base_url, $params);
        
        if ($info['total_pages'] <= 1) {
            return '';
        }
        
        $html = '<div class="pagination-container">';
        $html .= '<div class="pagination-info">';
        $html .= $lang == 'ar' 
            ? "عرض {$info['start_item']} - {$info['end_item']} من {$info['total_items']} عنصر"
            : "Affichage de {$info['start_item']} - {$info['end_item']} sur {$info['total_items']} éléments";
        $html .= '</div>';
        
        $html .= '<div class="pagination-links">';
        
        // Bouton précédent
        if (isset($links['previous'])) {
            $html .= '<a href="' . $links['previous'] . '" class="pagination-btn prev">';
            $html .= '<i class="fas fa-chevron-left"></i>';
            $html .= $lang == 'ar' ? 'السابق' : 'Précédent';
            $html .= '</a>';
        }
        
        // Numéros de page
        foreach ($links['pages'] as $page) {
            if ($page['number'] === '...') {
                $html .= '<span class="pagination-ellipsis">...</span>';
            } else {
                $class = 'pagination-btn';
                if ($page['active']) {
                    $class .= ' active';
                }
                $html .= '<a href="' . $page['url'] . '" class="' . $class . '">';
                $html .= $page['number'];
                $html .= '</a>';
            }
        }
        
        // Bouton suivant
        if (isset($links['next'])) {
            $html .= '<a href="' . $links['next'] . '" class="pagination-btn next">';
            $html .= $lang == 'ar' ? 'التالي' : 'Suivant';
            $html .= '<i class="fas fa-chevron-right"></i>';
            $html .= '</a>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obtenir les options de pagination
     */
    public static function getPageSizeOptions($lang = 'ar') {
        return [
            10 => $lang == 'ar' ? '10 عناصر' : '10 éléments',
            20 => $lang == 'ar' ? '20 عنصر' : '20 éléments',
            50 => $lang == 'ar' ? '50 عنصر' : '50 éléments',
            100 => $lang == 'ar' ? '100 عنصر' : '100 éléments'
        ];
    }
    
    /**
     * Valider les paramètres de pagination
     */
    public static function validateParams($page, $limit) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit)); // Limiter à 100 éléments max
        
        return ['page' => $page, 'limit' => $limit];
    }
    
    /**
     * Obtenir les statistiques de pagination
     */
    public function getStats() {
        return [
            'total_items' => $this->total_items,
            'items_per_page' => $this->items_per_page,
            'total_pages' => $this->total_pages,
            'current_page' => $this->current_page,
            'offset' => $this->offset,
            'has_data' => $this->total_items > 0
        ];
    }
}
?>
