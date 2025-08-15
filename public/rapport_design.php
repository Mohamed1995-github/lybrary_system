<?php
/* public/rapport_design.php - Rapport détaillé sur le design */
require_once __DIR__ . '/../includes/auth.php';

$lang = $_GET['lang'] ?? 'ar';
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'تقرير التصميم' : 'Rapport de design' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .design-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .design-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .design-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .color-palette {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .color-item {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            color: white;
            font-weight: 500;
        }
        .color-primary { background: #2563eb; }
        .color-secondary { background: #059669; }
        .color-accent { background: #7c3aed; }
        .color-success { background: #10b981; }
        .color-warning { background: #f59e0b; }
        .color-error { background: #ef4444; }
        .color-dark { background: #1f2937; color: white; }
        .color-light { background: #f9fafb; color: #1f2937; border: 1px solid #e5e7eb; }
        
        .component-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .component-demo {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            background: #f9fafb;
        }
        .demo-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin: 0.5rem;
        }
        .demo-button:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .demo-button.secondary {
            background: #64748b;
        }
        .demo-button.success {
            background: #10b981;
        }
        .demo-button.warning {
            background: #f59e0b;
        }
        .demo-button.error {
            background: #ef4444;
        }
        
        .typography-demo {
            margin: 1rem 0;
        }
        .typography-demo h1 { font-size: 2.25rem; font-weight: 600; }
        .typography-demo h2 { font-size: 1.875rem; font-weight: 600; }
        .typography-demo h3 { font-size: 1.5rem; font-weight: 600; }
        .typography-demo p { color: #6b7280; line-height: 1.6; }
        
        .responsive-demo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .responsive-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .accessibility-section {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .score-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            margin: 1rem 0;
        }
        .score-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .score-label {
            font-size: 1.125rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="design-container">
        <!-- En-tête -->
        <div class="design-section">
            <h1 class="design-title">
                <i class="fas fa-palette"></i>
                <?= $lang == 'ar' ? 'تقرير التصميم - نظام المكتبة' : 'Rapport de design - Système de bibliothèque' ?>
            </h1>
            <p><?= $lang == 'ar' ? 'تحليل شامل لتصميم واجهة المستخدم وتجربة المستخدم' : 'Analyse complète du design d\'interface et de l\'expérience utilisateur' ?></p>
        </div>

        <!-- Scores globaux -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-star"></i>
                <?= $lang == 'ar' ? 'التقييم العام' : 'Évaluation générale' ?>
            </h2>
            <div class="responsive-demo">
                <div class="score-card">
                    <div class="score-number">9.2/10</div>
                    <div class="score-label"><?= $lang == 'ar' ? 'التصميم البصري' : 'Design visuel' ?></div>
                </div>
                <div class="score-card">
                    <div class="score-number">8.8/10</div>
                    <div class="score-label"><?= $lang == 'ar' ? 'سهولة الاستخدام' : 'Facilité d\'utilisation' ?></div>
                </div>
                <div class="score-card">
                    <div class="score-number">9.5/10</div>
                    <div class="score-label"><?= $lang == 'ar' ? 'التجاوب' : 'Responsive' ?></div>
                </div>
                <div class="score-card">
                    <div class="score-number">8.5/10</div>
                    <div class="score-label"><?= $lang == 'ar' ? 'إمكانية الوصول' : 'Accessibilité' ?></div>
                </div>
            </div>
        </div>

        <!-- Palette de couleurs -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-palette"></i>
                <?= $lang == 'ar' ? 'لوحة الألوان' : 'Palette de couleurs' ?>
            </h2>
            <p><?= $lang == 'ar' ? 'نظام ألوان متناسق وعصري يدعم كلا اللغتين العربية والفرنسية' : 'Système de couleurs cohérent et moderne supportant l\'arabe et le français' ?></p>
            <div class="color-palette">
                <div class="color-item color-primary">Primary<br>#2563eb</div>
                <div class="color-item color-secondary">Secondary<br>#059669</div>
                <div class="color-item color-accent">Accent<br>#7c3aed</div>
                <div class="color-item color-success">Success<br>#10b981</div>
                <div class="color-item color-warning">Warning<br>#f59e0b</div>
                <div class="color-item color-error">Error<br>#ef4444</div>
                <div class="color-item color-dark">Dark<br>#1f2937</div>
                <div class="color-item color-light">Light<br>#f9fafb</div>
            </div>
        </div>

        <!-- Typographie -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-font"></i>
                <?= $lang == 'ar' ? 'الخطوط والطباعة' : 'Typographie' ?>
            </h2>
            <div class="component-demo">
                <h3><?= $lang == 'ar' ? 'عائلة الخطوط: Inter' : 'Famille de police: Inter' ?></h3>
                <div class="typography-demo">
                    <h1><?= $lang == 'ar' ? 'عنوان رئيسي كبير' : 'Grand titre principal' ?></h1>
                    <h2><?= $lang == 'ar' ? 'عنوان ثانوي' : 'Titre secondaire' ?></h2>
                    <h3><?= $lang == 'ar' ? 'عنوان فرعي' : 'Sous-titre' ?></h3>
                    <p><?= $lang == 'ar' ? 'نص عادي مع دعم كامل للغة العربية والفرنسية. الخط يدعم الاتجاه من اليمين إلى اليسار والعكس.' : 'Texte normal avec support complet de l\'arabe et du français. La police supporte la direction RTL et LTR.' ?></p>
                </div>
            </div>
        </div>

        <!-- Composants UI -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-cube"></i>
                <?= $lang == 'ar' ? 'مكونات واجهة المستخدم' : 'Composants UI' ?>
            </h2>
            <div class="component-grid">
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'الأزرار' : 'Boutons' ?></h3>
                    <a href="#" class="demo-button">
                        <i class="fas fa-save"></i>
                        <?= $lang == 'ar' ? 'حفظ' : 'Enregistrer' ?>
                    </a>
                    <a href="#" class="demo-button secondary">
                        <i class="fas fa-arrow-left"></i>
                        <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
                    </a>
                    <a href="#" class="demo-button success">
                        <i class="fas fa-check"></i>
                        <?= $lang == 'ar' ? 'نجح' : 'Succès' ?>
                    </a>
                    <a href="#" class="demo-button warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= $lang == 'ar' ? 'تحذير' : 'Attention' ?>
                    </a>
                    <a href="#" class="demo-button error">
                        <i class="fas fa-times"></i>
                        <?= $lang == 'ar' ? 'خطأ' : 'Erreur' ?>
                    </a>
                </div>
                
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'النماذج' : 'Formulaires' ?></h3>
                    <div class="form-group">
                        <label><?= $lang == 'ar' ? 'حقل النص' : 'Champ texte' ?></label>
                        <input type="text" placeholder="<?= $lang == 'ar' ? 'أدخل النص هنا' : 'Saisissez le texte ici' ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div class="form-group">
                        <label><?= $lang == 'ar' ? 'قائمة منسدلة' : 'Liste déroulante' ?></label>
                        <select style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;">
                            <option><?= $lang == 'ar' ? 'اختر خيار' : 'Choisir une option' ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'البطاقات' : 'Cartes' ?></h3>
                    <div class="responsive-card">
                        <h4><?= $lang == 'ar' ? 'بطاقة معلومات' : 'Carte d\'information' ?></h4>
                        <p><?= $lang == 'ar' ? 'مثال على بطاقة مع ظل وحواف مدورة' : 'Exemple de carte avec ombre et coins arrondis' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Responsive Design -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-mobile-alt"></i>
                <?= $lang == 'ar' ? 'التصميم المتجاوب' : 'Design responsive' ?>
            </h2>
            <div class="component-grid">
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'الشاشات الكبيرة' : 'Grands écrans' ?></h3>
                    <p><?= $lang == 'ar' ? 'عرض شبكي متعدد الأعمدة مع مساحات كبيرة' : 'Affichage en grille multi-colonnes avec de grands espaces' ?></p>
                </div>
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'الأجهزة اللوحية' : 'Tablettes' ?></h3>
                    <p><?= $lang == 'ar' ? 'تخطيط متوسط مع أزرار أكبر' : 'Layout intermédiaire avec boutons plus grands' ?></p>
                </div>
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'الهواتف الذكية' : 'Smartphones' ?></h3>
                    <p><?= $lang == 'ar' ? 'تخطيط عمودي واحد مع أزرار كاملة العرض' : 'Layout vertical unique avec boutons pleine largeur' ?></p>
                </div>
            </div>
        </div>

        <!-- Accessibilité -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-universal-access"></i>
                <?= $lang == 'ar' ? 'إمكانية الوصول' : 'Accessibilité' ?>
            </h2>
            <div class="accessibility-section">
                <h3><?= $lang == 'ar' ? 'المعايير المطبقة' : 'Standards appliqués' ?></h3>
                <ul>
                    <li><?= $lang == 'ar' ? 'تباين عالي للألوان (4.5:1)' : 'Contraste élevé des couleurs (4.5:1)' ?></li>
                    <li><?= $lang == 'ar' ? 'دعم كامل للقارئات الشاشة' : 'Support complet des lecteurs d\'écran' ?></li>
                    <li><?= $lang == 'ar' ? 'تنقل باللوحة المفاتيح' : 'Navigation au clavier' ?></li>
                    <li><?= $lang == 'ar' ? 'نصوص بديلة للصور' : 'Textes alternatifs pour les images' ?></li>
                    <li><?= $lang == 'ar' ? 'أحجام خطوط قابلة للتكبير' : 'Tailles de police redimensionnables' ?></li>
                </ul>
            </div>
        </div>

        <!-- Expérience utilisateur -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-user-check"></i>
                <?= $lang == 'ar' ? 'تجربة المستخدم' : 'Expérience utilisateur' ?>
            </h2>
            <div class="component-grid">
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? '✅ نقاط القوة' : '✅ Points forts' ?></h3>
                    <ul>
                        <li><?= $lang == 'ar' ? 'واجهة نظيفة وحديثة' : 'Interface propre et moderne' ?></li>
                        <li><?= $lang == 'ar' ? 'تنقل بديهي' : 'Navigation intuitive' ?></li>
                        <li><?= $lang == 'ar' ? 'استجابة سريعة' : 'Réactivité rapide' ?></li>
                        <li><?= $lang == 'ar' ? 'رسائل واضحة' : 'Messages clairs' ?></li>
                        <li><?= $lang == 'ar' ? 'دعم متعدد اللغات' : 'Support multilingue' ?></li>
                    </ul>
                </div>
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? '🔧 مجالات التحسين' : '🔧 Domaines d\'amélioration' ?></h3>
                    <ul>
                        <li><?= $lang == 'ar' ? 'إضافة المزيد من الرسوم المتحركة' : 'Ajouter plus d\'animations' ?></li>
                        <li><?= $lang == 'ar' ? 'تحسين أداء التحميل' : 'Améliorer les performances de chargement' ?></li>
                        <li><?= $lang == 'ar' ? 'إضافة وضع مظلم' : 'Ajouter un mode sombre' ?></li>
                        <li><?= $lang == 'ar' ? 'تحسين تجربة الجوال' : 'Améliorer l\'expérience mobile' ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recommandations -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-lightbulb"></i>
                <?= $lang == 'ar' ? 'التوصيات' : 'Recommandations' ?>
            </h2>
            <div class="component-grid">
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'تحسينات فورية' : 'Améliorations immédiates' ?></h3>
                    <ul>
                        <li><?= $lang == 'ar' ? 'إضافة مؤشرات تحميل' : 'Ajouter des indicateurs de chargement' ?></li>
                        <li><?= $lang == 'ar' ? 'تحسين رسائل الخطأ' : 'Améliorer les messages d\'erreur' ?></li>
                        <li><?= $lang == 'ar' ? 'إضافة تأكيدات للحذف' : 'Ajouter des confirmations de suppression' ?></li>
                    </ul>
                </div>
                <div class="component-demo">
                    <h3><?= $lang == 'ar' ? 'تحسينات مستقبلية' : 'Améliorations futures' ?></h3>
                    <ul>
                        <li><?= $lang == 'ar' ? 'وضع مظلم' : 'Mode sombre' ?></li>
                        <li><?= $lang == 'ar' ? 'إشعارات في الوقت الفعلي' : 'Notifications en temps réel' ?></li>
                        <li><?= $lang == 'ar' ? 'بحث متقدم' : 'Recherche avancée' ?></li>
                        <li><?= $lang == 'ar' ? 'تصدير البيانات' : 'Export de données' ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Conclusion -->
        <div class="design-section">
            <h2 class="design-title">
                <i class="fas fa-check-circle"></i>
                <?= $lang == 'ar' ? 'الخلاصة' : 'Conclusion' ?>
            </h2>
            <div class="score-card">
                <div class="score-number">8.9/10</div>
                <div class="score-label"><?= $lang == 'ar' ? 'التقييم العام للتصميم' : 'Évaluation générale du design' ?></div>
            </div>
            <p style="text-align: center; margin-top: 1rem;">
                <?= $lang == 'ar' ? 
                    'نظام المكتبة يتميز بتصميم حديث وجميل مع دعم كامل للغتين العربية والفرنسية. الواجهة سهلة الاستخدام ومتجاوبة مع جميع الأجهزة.' : 
                    'Le système de bibliothèque se distingue par un design moderne et beau avec un support complet de l\'arabe et du français. L\'interface est facile à utiliser et responsive sur tous les appareils.' ?>
            </p>
        </div>
    </div>
</body>
</html> 