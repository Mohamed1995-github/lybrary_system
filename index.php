<?php
/**
 * Page d'accueil simple sans routing
 * Simple homepage without routing
 */

session_start();

// Définir la langue par défaut
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar';
}

// Si l'utilisateur est connecté, aller au dashboard
if (isset($_SESSION['uid'])) {
    header('Location: public/dashboard.php');
    exit;
}

// Sinon afficher la page d'accueil avec liens directs
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام المكتبة - Système de Bibliothèque</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        h1 {
            color: #2563eb;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        
        .subtitle {
            color: #6b7280;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }
        
        .links-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .link-card {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            color: #1f2937;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .link-card:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }
        
        .icon {
            font-size: 24px;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .link-card:hover .icon {
            background: rgba(255,255,255,0.2);
        }
        
        .link-info {
            text-align: right;
            flex: 1;
        }
        
        .link-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .link-desc {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 30px 0;
        }
        
        .section-title {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        
        .quick-links {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .quick-link {
            padding: 8px 16px;
            background: #f3f4f6;
            border-radius: 8px;
            text-decoration: none;
            color: #4b5563;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .quick-link:hover {
            background: #e5e7eb;
            color: #1f2937;
        }
        
        .footer {
            margin-top: 30px;
            color: #9ca3af;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 نظام المكتبة</h1>
        <p class="subtitle">Système de Gestion de Bibliothèque</p>
        
        <div class="links-grid">
            <a href="public/employee_login.php" class="link-card">
                <div class="icon">🔐</div>
                <div class="link-info">
                    <div class="link-title">تسجيل الدخول</div>
                    <div class="link-desc">Connexion au système</div>
                </div>
            </a>
            
            <a href="public/auto_login.php" class="link-card">
                <div class="icon">⚡</div>
                <div class="link-info">
                    <div class="link-title">دخول سريع</div>
                    <div class="link-desc">Connexion automatique (Test)</div>
                </div>
            </a>
            
            <a href="public/dashboard.php" class="link-card">
                <div class="icon">📊</div>
                <div class="link-info">
                    <div class="link-title">لوحة التحكم</div>
                    <div class="link-desc">Tableau de bord principal</div>
                </div>
            </a>
        </div>
        
        <div class="divider"></div>
        
        <div class="section-title">ACCÈS DIRECT AUX MODULES</div>
        
        <div class="quick-links">
            <a href="modules/items/list.php?type=book" class="quick-link">📖 الكتب</a>
            <a href="modules/items/list.php?type=magazine" class="quick-link">📰 المجلات</a>
            <a href="modules/items/list.php?type=newspaper" class="quick-link">📄 الصحف</a>
            <a href="modules/borrowers/list.php" class="quick-link">👥 المستعيرين</a>
            <a href="modules/loans/list.php" class="quick-link">📋 الإعارات</a>
            <a href="modules/administration/employees.php" class="quick-link">👤 الموظفين</a>
        </div>
        
        <div class="divider"></div>
        
        <div class="section-title">OUTILS DE MAINTENANCE</div>
        
        <div class="quick-links">
            <a href="test_database_connection.php" class="quick-link">🔧 Test DB</a>
            <a href="fix_login_issue.php" class="quick-link">🔨 Réparer Login</a>
            <a href="check_login_status.php" class="quick-link">✅ Vérifier État</a>
        </div>
        
        <div class="footer">
            <p>© 2024 Library Management System</p>
            <p>Version Simple - Sans Routing</p>
        </div>
    </div>
</body>
</html>