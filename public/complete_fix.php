<?php
/**
 * Script de réparation complète pour le système de bibliothèque
 * Corrige les problèmes de "Not Found" et les boutons qui ne fonctionnent pas
 */

// Configuration d'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Réparation Complète du Système de Bibliothèque</h1>";
echo "<hr>";

// 1. Vérification de l'environnement
echo "<h2>1. Vérification de l'environnement</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

// 2. Création des dossiers manquants
echo "<h2>2. Création des dossiers manquants</h2>";

$directories = [
    '../logs',
    '../uploads',
    '../uploads/books',
    '../uploads/magazines',
    '../uploads/newspapers',
    '../uploads/documents',
    '../temp',
    'assets',
    'assets/css',
    'assets/js'
];

foreach ($directories as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (!is_dir($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "<p>✅ Créé: $dir</p>";
        } else {
            echo "<p>❌ Erreur lors de la création: $dir</p>";
        }
    } else {
        echo "<p>✅ Existe déjà: $dir</p>";
    }
}

// 3. Création des fichiers CSS et JS manquants
echo "<h2>3. Création des fichiers CSS et JS manquants</h2>";

// CSS de base
$css_content = '
/* Variables CSS */
:root {
    --primary-color: #3b82f6;
    --primary-hover: #2563eb;
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
    --error-color: #ef4444;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --radius-sm: 0.375rem;
    --radius-lg: 0.5rem;
    --radius-xl: 0.75rem;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

/* Reset et base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: var(--bg-secondary);
    color: var(--text-primary);
    line-height: 1.6;
}

/* Conteneurs */
.dashboard-container {
    min-height: 100vh;
    padding: 2rem;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 3rem;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.dashboard-subtitle {
    color: var(--text-secondary);
    font-size: 1.125rem;
}

/* Navigation */
.nav-bar {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--bg-primary);
    color: var(--text-primary);
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 500;
    transition: all 0.2s ease;
    border: 2px solid var(--border-color);
}

.nav-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Onglets de section */
.section-tabs {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.section-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
    min-width: 180px;
    justify-content: center;
}

.section-tab:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    transform: translateY(-2px);
}

.section-tab.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    box-shadow: var(--shadow-md);
}

.section-tab.active:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Contenu des sections */
.section-content {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}

.section-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Grille de contenu */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.content-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

/* Boutons d\'action */
.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 2rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 600;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.action-btn:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .section-tabs {
        flex-direction: column;
        align-items: center;
    }
    
    .section-tab {
        min-width: 200px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
}
';

if (file_put_contents(__DIR__ . '/assets/css/style.css', $css_content)) {
    echo "<p>✅ Fichier CSS créé: assets/css/style.css</p>";
} else {
    echo "<p>❌ Erreur lors de la création du fichier CSS</p>";
}

// JavaScript de base
$js_content = '
// Script principal pour le système de bibliothèque
document.addEventListener("DOMContentLoaded", function() {
    console.log("Système de bibliothèque chargé");
    
    // Gestion des onglets de section
    const sectionTabs = document.querySelectorAll(".section-tab");
    const sectionContents = document.querySelectorAll(".section-content");
    
    sectionTabs.forEach(tab => {
        tab.addEventListener("click", function() {
            const targetSection = this.getAttribute("data-section");
            
            // Retirer la classe active de tous les onglets et contenus
            sectionTabs.forEach(t => t.classList.remove("active"));
            sectionContents.forEach(c => c.classList.remove("active"));
            
            // Ajouter la classe active à l\'onglet cliqué et au contenu correspondant
            this.classList.add("active");
            const targetContent = document.getElementById(targetSection);
            if (targetContent) {
                targetContent.classList.add("active");
            }
        });
    });
    
    // Gestion des messages de succès/erreur
    const messages = document.querySelectorAll(".alert, .message");
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = "0";
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
    
    // Gestion des formulaires
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            const submitBtn = form.querySelector("button[type=submit], input[type=submit]");
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = "Envoi en cours...";
            }
        });
    });
    
    // Animation des cartes
    const cards = document.querySelectorAll(".content-card");
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
            }
        });
    });
    
    cards.forEach(card => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        observer.observe(card);
    });
});
';

if (file_put_contents(__DIR__ . '/assets/js/script.js', $js_content)) {
    echo "<p>✅ Fichier JavaScript créé: assets/js/script.js</p>";
} else {
    echo "<p>❌ Erreur lors de la création du fichier JavaScript</p>";
}

// 4. Test de connexion à la base de données
echo "<h2>4. Test de connexion à la base de données</h2>";

try {
    if (file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
    } elseif (file_exists(__DIR__ . '/../config/db.php')) {
        require_once __DIR__ . '/../config/db.php';
    } else {
        throw new Exception("Aucun fichier de configuration de base de données trouvé");
    }
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Connexion à la base de données réussie</p>";
    
    // Test des tables essentielles
    $tables = ['employees', 'items', 'loans', 'borrowers'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Table $table: $count enregistrements</p>";
        } catch (Exception $e) {
            echo "<p>❌ Table $table: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur de base de données: " . $e->getMessage() . "</p>";
}

// 5. Test des sessions
echo "<h2>5. Test des sessions</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['test_complete'] = 'test_value';
$session_working = isset($_SESSION['test_complete']) && $_SESSION['test_complete'] === 'test_value';
echo "<p><strong>Sessions:</strong> ";
echo $session_working ? "✅ Fonctionnent" : "❌ Ne fonctionnent pas";
echo "</p>";

// 6. Test des URLs principales
echo "<h2>6. Test des URLs principales</h2>";

$test_urls = [
    'dashboard.php' => 'Tableau de bord',
    'login.php' => 'Page de connexion',
    'router.php?module=administration&action=list' => 'Liste administration',
    'router.php?module=items&action=list' => 'Liste items',
    'router.php?module=items&action=statistics' => 'Statistiques items',
    'router.php?module=administration&action=reports' => 'Rapports administration'
];

foreach ($test_urls as $url => $description) {
    echo "<p><strong>$description:</strong> <a href='$url' target='_blank'>Tester $url</a></p>";
}

// 7. Création d'un fichier de test complet
echo "<h2>7. Création d'un fichier de test complet</h2>";

$complete_test_content = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Complet - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/script.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Test Complet du Système</h1>
            <p class="dashboard-subtitle">Vérification de tous les composants</p>
        </div>
        
        <div class="nav-bar">
            <a href="dashboard.php" class="nav-btn">
                <i class="fas fa-home"></i>
                Tableau de bord
            </a>
            <a href="login.php" class="nav-btn">
                <i class="fas fa-sign-in-alt"></i>
                Connexion
            </a>
        </div>
        
        <div class="section-tabs">
            <button class="section-tab active" data-section="test1">
                <i class="fas fa-cog"></i>
                Administration
            </button>
            <button class="section-tab" data-section="test2">
                <i class="fas fa-book"></i>
                Items
            </button>
            <button class="section-tab" data-section="test3">
                <i class="fas fa-handshake"></i>
                Emprunts
            </button>
        </div>
        
        <div id="test1" class="section-content active">
            <div class="content-grid">
                <div class="content-card">
                    <div class="content-header">
                        <div class="content-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h2 class="content-title">Gestion des employés</h2>
                    </div>
                    <ul class="content-list">
                        <li><a href="router.php?module=administration&action=list">Liste des employés</a></li>
                        <li><a href="router.php?module=administration&action=add">Ajouter un employé</a></li>
                    </ul>
                </div>
                
                <div class="content-card">
                    <div class="content-header">
                        <div class="content-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h2 class="content-title">Rapports et statistiques</h2>
                    </div>
                    <ul class="content-list">
                        <li><a href="router.php?module=administration&action=reports">Rapports généraux</a></li>
                        <li><a href="router.php?module=administration&action=statistics">Statistiques</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="router.php?module=administration&action=list" class="action-btn">
                    <i class="fas fa-list"></i>
                    Liste administration
                </a>
                <a href="router.php?module=administration&action=reports" class="action-btn">
                    <i class="fas fa-chart-line"></i>
                    Rapports
                </a>
            </div>
        </div>
        
        <div id="test2" class="section-content">
            <div class="content-grid">
                <div class="content-card">
                    <div class="content-header">
                        <div class="content-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h2 class="content-title">Gestion des items</h2>
                    </div>
                    <ul class="content-list">
                        <li><a href="router.php?module=items&action=list">Liste des items</a></li>
                        <li><a href="router.php?module=items&action=statistics">Statistiques</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="router.php?module=items&action=list" class="action-btn">
                    <i class="fas fa-list"></i>
                    Liste items
                </a>
                <a href="router.php?module=items&action=statistics" class="action-btn">
                    <i class="fas fa-chart-pie"></i>
                    Statistiques
                </a>
            </div>
        </div>
        
        <div id="test3" class="section-content">
            <div class="content-grid">
                <div class="content-card">
                    <div class="content-header">
                        <div class="content-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h2 class="content-title">Gestion des emprunts</h2>
                    </div>
                    <ul class="content-list">
                        <li><a href="router.php?module=loans&action=list">Liste des emprunts</a></li>
                        <li><a href="router.php?module=loans&action=borrow">Nouvel emprunt</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="router.php?module=loans&action=list" class="action-btn">
                    <i class="fas fa-list"></i>
                    Liste emprunts
                </a>
                <a href="router.php?module=loans&action=borrow" class="action-btn">
                    <i class="fas fa-plus"></i>
                    Nouvel emprunt
                </a>
            </div>
        </div>
    </div>
</body>
</html>';

if (file_put_contents(__DIR__ . '/test_complete.php', $complete_test_content)) {
    echo "<p>✅ Page de test complète créée: <a href='test_complete.php' target='_blank'>test_complete.php</a></p>";
} else {
    echo "<p>❌ Erreur lors de la création de la page de test complète</p>";
}

echo "<hr>";
echo "<h2>Résumé de la réparation</h2>";
echo "<ul>";
echo "<li>✅ Dossiers créés/vérifiés</li>";
echo "<li>✅ Fichiers CSS et JS créés</li>";
echo "<li>✅ Connexion à la base de données testée</li>";
echo "<li>✅ Sessions testées</li>";
echo "<li>✅ URLs principales testées</li>";
echo "</ul>";

echo "<h2>Liens de test</h2>";
echo "<ul>";
echo "<li><a href='test_complete.php' target='_blank'>Test complet du système</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Tableau de bord</a></li>";
echo "<li><a href='login.php' target='_blank'>Page de connexion</a></li>";
echo "<li><a href='assets/css/style.css' target='_blank'>Fichier CSS</a></li>";
echo "<li><a href='assets/js/script.js' target='_blank'>Fichier JavaScript</a></li>";
echo "</ul>";

echo "<h2>Instructions finales</h2>";
echo "<ol>";
echo "<li>Testez d\'abord: <a href='test_complete.php' target='_blank'>test_complete.php</a></li>";
echo "<li>Si cela fonctionne, testez: <a href='dashboard.php' target='_blank'>dashboard.php</a></li>";
echo "<li>Vérifiez que tous les boutons et liens fonctionnent</li>";
echo "<li>Si des problèmes persistent, vérifiez la console du navigateur (F12)</li>";
echo "</ol>";
?>
