<?php
/**
 * Script de réparation pour les boutons qui ne fonctionnent pas
 * Ce script corrige les problèmes de navigation et de JavaScript
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Réparation des Boutons - Système de Bibliothèque</h1>";
echo "<hr>";

// 1. Vérification des fichiers JavaScript
echo "<h2>1. Vérification des fichiers JavaScript</h2>";

$js_files = [
    'assets/js/script.js'
];

foreach ($js_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        echo "<p>✅ Existe: $file ($size bytes)</p>";
    } else {
        echo "<p>❌ Manquant: $file</p>";
    }
}

// 2. Vérification des fichiers CSS
echo "<h2>2. Vérification des fichiers CSS</h2>";

$css_files = [
    'assets/css/style.css'
];

foreach ($css_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        echo "<p>✅ Existe: $file ($size bytes)</p>";
    } else {
        echo "<p>❌ Manquant: $file</p>";
    }
}

// 3. Test des liens de navigation
echo "<h2>3. Test des liens de navigation</h2>";

$navigation_links = [
    'dashboard.php' => 'Tableau de bord',
    'router.php?module=administration&action=list' => 'Administration',
    'router.php?module=items&action=list' => 'Items',
    'router.php?module=loans&action=list' => 'Emprunts',
    'router.php?module=borrowers&action=list' => 'Emprunteurs'
];

foreach ($navigation_links as $url => $description) {
    echo "<p><strong>$description:</strong> <a href='$url' target='_blank'>Tester $url</a></p>";
}

// 4. Création d'un fichier de test JavaScript
echo "<h2>4. Création d'un fichier de test JavaScript</h2>";

$js_test_content = '
// Test JavaScript pour les boutons
document.addEventListener("DOMContentLoaded", function() {
    console.log("JavaScript fonctionne!");
    
    // Test des boutons de section
    const sectionTabs = document.querySelectorAll(".section-tab");
    console.log("Boutons de section trouvés:", sectionTabs.length);
    
    // Test des liens de navigation
    const navLinks = document.querySelectorAll("a[href]");
    console.log("Liens de navigation trouvés:", navLinks.length);
    
    // Test des boutons d\'action
    const actionButtons = document.querySelectorAll(".action-btn");
    console.log("Boutons d\'action trouvés:", actionButtons.length);
    
    // Ajouter des événements de test
    sectionTabs.forEach(function(tab, index) {
        tab.addEventListener("click", function() {
            console.log("Clic sur l\'onglet:", index);
            alert("Clic détecté sur l\'onglet " + index);
        });
    });
    
    // Test des liens
    navLinks.forEach(function(link, index) {
        link.addEventListener("click", function(e) {
            console.log("Clic sur le lien:", link.href);
        });
    });
});
';

if (file_put_contents(__DIR__ . '/assets/js/test_buttons.js', $js_test_content)) {
    echo "<p>✅ Fichier JavaScript de test créé: <a href='assets/js/test_buttons.js' target='_blank'>test_buttons.js</a></p>";
} else {
    echo "<p>❌ Erreur lors de la création du fichier JavaScript de test</p>";
}

// 5. Création d'une page de test pour les boutons
echo "<h2>5. Création d'une page de test pour les boutons</h2>";

$test_page_content = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des Boutons - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/test_buttons.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Test des Boutons</h1>
            <p class="dashboard-subtitle">Page de test pour vérifier le fonctionnement des boutons</p>
        </div>
        
        <div class="section-tabs">
            <button class="section-tab active" data-section="test1">
                <i class="fas fa-cog"></i>
                Test 1
            </button>
            <button class="section-tab" data-section="test2">
                <i class="fas fa-book"></i>
                Test 2
            </button>
            <button class="section-tab" data-section="test3">
                <i class="fas fa-users"></i>
                Test 3
            </button>
        </div>
        
        <div id="test1" class="section-content active">
            <h2>Section Test 1</h2>
            <p>Cette section teste les boutons de navigation.</p>
            <div class="action-buttons">
                <a href="dashboard.php" class="action-btn">
                    <i class="fas fa-home"></i>
                    Retour au tableau de bord
                </a>
                <a href="router.php?module=administration&action=list" class="action-btn">
                    <i class="fas fa-cog"></i>
                    Administration
                </a>
            </div>
        </div>
        
        <div id="test2" class="section-content">
            <h2>Section Test 2</h2>
            <p>Cette section teste les liens vers les modules.</p>
            <div class="action-buttons">
                <a href="router.php?module=items&action=list" class="action-btn">
                    <i class="fas fa-book"></i>
                    Liste des items
                </a>
                <a href="router.php?module=items&action=statistics" class="action-btn">
                    <i class="fas fa-chart-bar"></i>
                    Statistiques
                </a>
            </div>
        </div>
        
        <div id="test3" class="section-content">
            <h2>Section Test 3</h2>
            <p>Cette section teste les liens vers les autres modules.</p>
            <div class="action-buttons">
                <a href="router.php?module=loans&action=list" class="action-btn">
                    <i class="fas fa-handshake"></i>
                    Emprunts
                </a>
                <a href="router.php?module=borrowers&action=list" class="action-btn">
                    <i class="fas fa-users"></i>
                    Emprunteurs
                </a>
            </div>
        </div>
    </div>
    
    <script>
    // Script de test pour les boutons
    document.addEventListener("DOMContentLoaded", function() {
        console.log("Page de test chargée");
        
        const sectionTabs = document.querySelectorAll(".section-tab");
        const sectionContents = document.querySelectorAll(".section-content");
        
        sectionTabs.forEach(tab => {
            tab.addEventListener("click", function() {
                console.log("Clic sur l\'onglet:", this.textContent.trim());
                
                const targetSection = this.getAttribute("data-section");
                
                // Retirer la classe active de tous les onglets et contenus
                sectionTabs.forEach(t => t.classList.remove("active"));
                sectionContents.forEach(c => c.classList.remove("active"));
                
                // Ajouter la classe active à l\'onglet cliqué et au contenu correspondant
                this.classList.add("active");
                document.getElementById(targetSection).classList.add("active");
                
                alert("Onglet changé vers: " + targetSection);
            });
        });
        
        // Test des liens
        const links = document.querySelectorAll("a");
        links.forEach(link => {
            link.addEventListener("click", function(e) {
                console.log("Clic sur le lien:", this.href);
            });
        });
    });
    </script>
</body>
</html>';

if (file_put_contents(__DIR__ . '/test_buttons_page.php', $test_page_content)) {
    echo "<p>✅ Page de test des boutons créée: <a href='test_buttons_page.php' target='_blank'>test_buttons_page.php</a></p>";
} else {
    echo "<p>❌ Erreur lors de la création de la page de test</p>";
}

// 6. Vérification des erreurs JavaScript dans la console
echo "<h2>6. Instructions pour déboguer les boutons</h2>";
echo "<ol>";
echo "<li>Ouvrez la console du navigateur (F12)</li>";
echo "<li>Allez sur la page: <a href='test_buttons_page.php' target='_blank'>test_buttons_page.php</a></li>";
echo "<li>Vérifiez s'il y a des erreurs JavaScript dans la console</li>";
echo "<li>Cliquez sur les boutons et observez les messages dans la console</li>";
echo "<li>Si les boutons ne répondent pas, vérifiez que JavaScript est activé</li>";
echo "</ol>";

// 7. Test des sessions
echo "<h2>7. Test des sessions</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['test_button'] = 'test_value';
$session_working = isset($_SESSION['test_button']) && $_SESSION['test_button'] === 'test_value';
echo "<p><strong>Sessions:</strong> ";
echo $session_working ? "✅ Fonctionnent" : "❌ Ne fonctionnent pas";
echo "</p>";

echo "<hr>";
echo "<h2>Liens de test</h2>";
echo "<ul>";
echo "<li><a href='test_buttons_page.php' target='_blank'>Page de test des boutons</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Tableau de bord</a></li>";
echo "<li><a href='assets/js/test_buttons.js' target='_blank'>JavaScript de test</a></li>";
echo "</ul>";

echo "<h2>Problèmes courants et solutions</h2>";
echo "<ul>";
echo "<li><strong>Boutons ne répondent pas:</strong> Vérifiez que JavaScript est activé</li>";
echo "<li><strong>Erreurs dans la console:</strong> Vérifiez les chemins des fichiers CSS/JS</li>";
echo "<li><strong>Liens ne fonctionnent pas:</strong> Vérifiez la configuration Apache</li>";
echo "<li><strong>Sessions perdues:</strong> Vérifiez la configuration PHP</li>";
echo "</ul>";
?>
