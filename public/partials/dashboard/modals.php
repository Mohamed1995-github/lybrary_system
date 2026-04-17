<?php
/**
 * Dashboard Modals - Centralized Modal Definitions
 * All modal windows used in the dashboard
 */

// Get language and config
$lang = $lang ?? 'ar';
$menuConfig = $menuConfig ?? require __DIR__ . '/menu-config.php';
$texts = $menuConfig['menu_texts'][$lang];
$specializations = $menuConfig['specializations'];
?>

<!-- ============================================
     Resources Menu Modal
     ============================================ -->
<div id="resourcesMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 class="modal-title"><?= $texts['collections_section'] ?></h2>
        <p class="modal-subtitle"><?= $texts['choose_type'] ?></p>
    </div>

    <div class="modal-content">
        <!-- Main Units Grid -->
        <div class="modal-grid">
            <!-- Arabic Unit -->
            <div class="modal-item" onclick="showArabicUnitMenu()">
                <div class="modal-item-icon">📚</div>
                <h3 class="modal-item-title">
                    <?= $texts['arabic_unit'] ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $texts['arabic_unit_desc'] ?>
                </p>
            </div>

            <!-- French Unit -->
            <div class="modal-item" onclick="showFrenchUnitMenu()">
                <div class="modal-item-icon">📚</div>
                <h3 class="modal-item-title">
                    <?= $texts['french_unit'] ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $texts['french_unit_desc'] ?>
                </p>
            </div>

            <!-- Periodicals and Research Section -->
            <div class="modal-item" onclick="showPeriodiquesMenu()">
                <div class="modal-item-icon">📰</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'وحدة الدوريات والبحوث' : 'Unité Périodiques et Recherches' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'مجلات + بحوث (عربي وفرنسي)' : 'Revues + Recherches (Arabe et Français)' ?>
                </p>
            </div>

            <!-- Newspapers Unit -->
            <div class="modal-item" onclick="showNewspapersMenu()">
                <div class="modal-item-icon">🗞️</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'وحدة الجرائد' : 'Unité Journaux' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'جريدة الشعب + الجريدة الرسمية' : 'Journal El Chaab + Journal Officiel' ?>
                </p>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideResourcesMenu()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Books Menu Modal
     ============================================ -->
<div id="booksMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 id="booksMenuTitle" class="modal-title"></h2>
        <p class="modal-subtitle"><?= $texts['choose_type'] ?></p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <div id="specializedLink" class="modal-item">
                <div class="modal-item-icon">📚</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'الكتب المتخصصة' : 'Livres Spécialisés' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'أكاديمية ومتخصصة' : 'Académiques et spécialisés' ?>
                </p>
            </div>

            <a id="generalLink" href="#" class="modal-item">
                <div class="modal-item-icon">📖</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'الكتب العامة' : 'Livres Généraux' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'ثقافية وعامة' : 'Culturels et généraux' ?>
                </p>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideBooksMenu()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Specialized Books Menu Modal
     ============================================ -->
<div id="specializedBooksMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 id="specializedBooksMenuTitle" class="modal-title"></h2>
        <p class="modal-subtitle"><?= $texts['choose_specialization'] ?></p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <?php foreach ($specializations as $key => $spec): ?>
                <a id="<?= strtolower($key) ?>Link" href="#" class="modal-item">
                    <div class="modal-item-icon"><?= $spec['icon'] ?></div>
                    <h3 class="modal-item-title"><?= $spec[$lang] ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideSpecializedBooksMenu()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Arabic Periodiques Menu Modal
     ============================================ -->
<div id="arabicPeriodiquesMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📰</div>
        <h2 class="modal-title">
            <?= $lang == 'ar' ? 'وحدة الدوريات والبحوث العربية' : 'Périodiques et Recherches Arabes' ?>
        </h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'جميع المصادر باللغة العربية' : 'Toutes les ressources en langue arabe' ?>
        </p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <a href="modules/items/magazines-arabic-cards.php?lang=ar" class="modal-item">
                <div class="modal-item-icon">📰</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'الدوريات' : 'Revues Arabes' ?>
                </h3>
            </a>

            <a href="modules/items/listrecherche.php?lang=ar" class="modal-item">
                <div class="modal-item-icon">🎓📚</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'البحوث الأكاديمية العربية' : 'Recherches Académiques Arabes' ?>
                </h3>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideArabicPeriodiquesMenu()" class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     French Periodiques Menu Modal
     ============================================ -->
<div id="frenchPeriodiquesMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📰</div>
        <h2 class="modal-title">
            <?= $lang == 'ar' ? 'الدوريات والبحوث الفرنسية' : 'Périodiques et Recherches Français' ?>
        </h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'جميع المصادر باللغة الفرنسية' : 'Toutes les ressources en langue française' ?>
        </p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <a href="modules/items/magazines-french-cards.php?lang=fr" class="modal-item">
                <div class="modal-item-icon">📄</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'المجلات الفرنسية' : 'Revues Françaises' ?>
                </h3>
            </a>

            <a href="modules/items/listrecherche.php?lang=fr" class="modal-item">
                <div class="modal-item-icon">🎓📚</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'البحوث الأكاديمية الفرنسية' : 'Recherches Académiques Françaises' ?>
                </h3>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideFrenchPeriodiquesMenu()" class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Periodiques Menu Modal (Combined Arabic & French)
     ============================================ -->
<div id="periodiquesMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📰</div>
        <h2 class="modal-title">
            <?= $lang == 'ar' ? 'وحدة الدوريات والبحوث' : 'Unité Périodiques et Recherches' ?>
        </h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'مجلات وبحوث بجميع اللغات' : 'Revues et recherches en toutes langues' ?>
        </p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <a href="modules/items/magazines-arabic-cards.php?lang=ar" class="modal-item">
                <div class="modal-item-icon">📰</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'الدوريات العربية' : 'Revues Arabes' ?>
                </h3>
            </a>

            <a href="modules/items/magazines-french-cards.php?lang=fr" class="modal-item">
                <div class="modal-item-icon">📰</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'الدوريات الفرنسية' : 'Revues Françaises' ?>
                </h3>
            </a>

            <a href="modules/items/listrecherche.php?lang=ar" class="modal-item">
                <div class="modal-item-icon">🎓</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'البحوث العربية' : 'Recherches Arabes' ?>
                </h3>
            </a>

            <a href="modules/items/listrecherche.php?lang=fr" class="modal-item">
                <div class="modal-item-icon">🎓📚</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'البحوث الفرنسية' : 'Recherches Françaises' ?>
                </h3>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hidePeriodiquesMenu()" class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Newspapers Menu Modal
     ============================================ -->
<div id="newspapersMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">🗞️</div>
        <h2 class="modal-title">
            <?= $lang == 'ar' ? 'وحدة الجرائد' : 'Unité Journaux' ?>
        </h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'جميع الجرائد المتوفرة' : 'Tous les journaux disponibles' ?>
        </p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <a href="modules/items/list_newspapers.php?newspaper_type=popular&lang=<?= $lang ?>" class="modal-item">
                <div class="modal-item-icon">📰</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'جريدة الشعب' : 'Journal El Chaab' ?>
                </h3>
            </a>

            <a href="modules/items/list_newspapers.php?newspaper_type=official&lang=<?= $lang ?>" class="modal-item">
                <div class="modal-item-icon">🗞️</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'الجريدة الرسمية' : 'Journal Officiel' ?>
                </h3>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideNewspapersMenu()" class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     IT Menu Modal
     ============================================ -->
<div id="itMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">💻</div>
        <h2 class="modal-title"><?= $texts['it_section'] ?></h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'اختر القسم المطلوب' : 'Choisissez la section souhaitée' ?>
        </p>
    </div>

    <div class="modal-content">
        <!-- Registration Section -->
        <div class="section-arabic mb-4">
            <h3 class="section-title section-title-arabic">
                <span style="font-size: 2rem;">📋</span>
                <?= $lang == 'ar' ? 'قسم تسجيل المصادر' : 'Section Enregistrement des Ressources' ?>
            </h3>
            <p style="color: #1e40af; margin-bottom: 1.5rem; font-size: 0.9rem;">
                <?= $lang == 'ar' ? 'إضافة وتسجيل المصادر الجديدة (كتب، مجلات، جرائد، بحوث)' : 'Ajout et enregistrement de nouvelles ressources' ?>
            </p>
            
            <div class="text-center">
                <div class="modal-item" onclick="showAddResourcesMenu()" style="display: inline-block; min-width: 200px;">
                    <div class="modal-item-icon" style="font-size: 2rem;">📚</div>
                    <div style="font-size: 1rem;"><?= $texts['add_resources'] ?></div>
                </div>
            </div>
        </div>

        <!-- Library Services Section -->
        <div class="section-french">
            <h3 class="section-title section-title-french">
                <span style="font-size: 2rem;">🏛️</span>
                <?= $texts['library_services'] ?>
            </h3>
            <p style="color: #16a34a; margin-bottom: 1.5rem; font-size: 0.9rem;">
                <?= $lang == 'ar' ? 'التزويد + الإعارة والاسترجاع + إدارة المستفيدين' : 'Acquisitions + Prêts et retours + Gestion des utilisateurs' ?>
            </p>
            
            <div class="modal-grid">
                <a href="modules/acquisitions/list.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">📦</div>
                    <div class="modal-item-title"><?= $texts['acquisitions'] ?></div>
                </a>
                
                <a href="modules/loans/dashboard.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">🔄</div>
                    <div class="modal-item-title"><?= $texts['loans_returns'] ?></div>
                </a>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideITMenu()" class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Add Resources Menu Modal
     ============================================ -->
<div id="addResourcesMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 class="modal-title"><?= $texts['add_resources'] ?></h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'اختر نوع المصدر المراد إضافته' : 'Choisissez le type de ressource à ajouter' ?>
        </p>
    </div>

    <div class="modal-content">
        <!-- Books -->
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0;">
            <h3 style="color: #4f46e5; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">📚</span>
                <?= $lang == 'ar' ? 'إضافة الكتب' : 'Ajouter des Livres' ?>
            </h3>
            
            <div class="modal-grid">
                <a href="modules/items/add_book_simple.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">📖</div>
                    <div class="modal-item-title">
                        <?= $lang == 'ar' ? 'كتاب متخصص' : 'Livre Spécialisé' ?>
                    </div>
                </a>
                
                <a href="modules/items/add_general_book.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">📚</div>
                    <div class="modal-item-title">
                        <?= $lang == 'ar' ? 'كتاب عام' : 'Livre Général' ?>
                    </div>
                </a>
            </div>
        </div>

        <!-- Magazines -->
        <div style="background: #f0fdf4; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0;">
            <h3 style="color: #16a34a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">📰</span>
                <?= $lang == 'ar' ? 'إضافة الدوريات' : 'Ajouter des Revues' ?>
            </h3>
            
            <div class="modal-grid">
                <a href="modules/items/add_magazine.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">📰</div>
                    <div class="modal-item-title">
                        <?= $lang == 'ar' ? 'اضافة دوريات' : 'Revue Spécialisée' ?>
                    </div>
                </a>
                
                <a href="modules/items/addrecherche.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">🎓</div>
                    <div class="modal-item-title">
                        <?= $lang == 'ar' ? 'إضافة بحث' : 'Ajouter Recherche' ?>
                    </div>
                </a>
            </div>
        </div>

        <!-- Newspapers -->
        <div style="background: #fffbeb; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; border: 1px solid #fde68a;">
            <h3 style="color: #92400e; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">🎯</span>
                <?= $lang == 'ar' ? 'الجرائد' : 'Journaux' ?>
            </h3>
            
            <div class="modal-grid">
                <a href="modules/items/add_newspaper.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">🗞️</div>
                    <div class="modal-item-title">
                        <?= $lang == 'ar' ? 'إضافة جريدة الشعب' : 'Ajouter Journal' ?>
                    </div>
                </a>
                
                <a href="modules/items/add_newspaper_r.php?lang=<?= $lang ?>" class="modal-item">
                    <div class="modal-item-icon">🗞️</div>
                    <div class="modal-item-title">
                        <?= $lang == 'ar' ? 'إضافة الجريدة الرسمية' : 'Ajouter Journal Officiel' ?>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideAddResourcesMenu(); showITMenu();" class="btn btn-secondary">
            <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
            <?= $lang == 'ar' ? 'العودة لقسم المعلوماتية' : 'Retour à l\'informatique' ?>
        </button>
    </div>
</div>

<!-- ============================================
     Magazines Menu Modal (if needed)
     ============================================ -->
<div id="magazinesMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📰</div>
        <h2 id="magazinesMenuTitle" class="modal-title"></h2>
        <p class="modal-subtitle"><?= $texts['choose_type'] ?></p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <div id="magSpecializedLink" class="modal-item">
                <div class="modal-item-icon">📰</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'المجلات المتخصصة' : 'Revues Spécialisées' ?>
                </h3>
            </div>

            <a id="magGeneralLink" href="#" class="modal-item">
                <div class="modal-item-icon">📄</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'المجلات العامة' : 'Revues Générales' ?>
                </h3>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideMagazinesMenu()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Specialized Magazines Menu Modal
     ============================================ -->
<div id="specializedMagazinesMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📰</div>
        <h2 id="specializedMagazinesMenuTitle" class="modal-title"></h2>
        <p class="modal-subtitle"><?= $texts['choose_specialization'] ?></p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <?php foreach ($specializations as $key => $spec): ?>
                <a id="mag<?= ucfirst(strtolower($key)) ?>Link" href="#" class="modal-item">
                    <div class="modal-item-icon"><?= $spec['icon'] ?></div>
                    <h3 class="modal-item-title"><?= $spec[$lang] ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideSpecializedMagazinesMenu()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Arabic Unit Menu Modal
     ============================================ -->
<div id="arabicUnitMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 class="modal-title"><?= $texts['arabic_unit'] ?></h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'اختر نوع الكتب' : 'Choisissez le type de livres' ?>
        </p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <!-- Specialized Arabic Books -->
            <div class="modal-item" onclick="showArabicSpecializedBooksMenu()">
                <div class="modal-item-icon">📚</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'كتب متخصصة' : 'Livres Spécialisés' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'أكاديمية ومتخصصة' : 'Académiques et spécialisés' ?>
                </p>
            </div>

            <!-- General Arabic Books -->
            <a href="modules/items/list_general_books.php?category=general&lang=ar" class="modal-item">
                <div class="modal-item-icon">📖</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'كتب عامة' : 'Livres Généraux' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'ثقافية وعامة' : 'Culturels et généraux' ?>
                </p>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideArabicUnitMenu()" class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     French Unit Menu Modal
     ============================================ -->
<div id="frenchUnitMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 class="modal-title"><?= $texts['french_unit'] ?></h2>
        <p class="modal-subtitle">
            <?= $lang == 'ar' ? 'اختر نوع الكتب' : 'Choisissez le type de livres' ?>
        </p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <!-- Specialized French Books -->
            <div class="modal-item" onclick="showFrenchSpecializedBooksMenu()">
                <div class="modal-item-icon">📚</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'كتب متخصصة' : 'Livres Spécialisés' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'أكاديمية ومتخصصة' : 'Académiques et spécialisés' ?>
                </p>
            </div>

            <!-- General French Books -->
            <a href="modules/items/list_general_books.php?category=general&lang=fr" class="modal-item">
                <div class="modal-item-icon">📖</div>
                <h3 class="modal-item-title">
                    <?= $lang == 'ar' ? 'كتب عامة' : 'Livres Généraux' ?>
                </h3>
                <p class="modal-item-desc">
                    <?= $lang == 'ar' ? 'ثقافية وعامة' : 'Culturels et généraux' ?>
                </p>
            </a>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideFrenchUnitMenu()" class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     Arabic Specialized Books Menu Modal
     ============================================ -->
<div id="arabicSpecializedBooksMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 class="modal-title">
            <?= $lang == 'ar' ? 'الكتب المتخصصة العربية' : 'Livres Spécialisés Arabes' ?>
        </h2>
        <p class="modal-subtitle"><?= $texts['choose_specialization'] ?></p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <?php foreach ($specializations as $key => $spec): ?>
                <a href="modules/items/manage_books.php?category=specialized&lang=ar&specialization=<?= strtolower($key) ?>" class="modal-item">
                    <div class="modal-item-icon"><?= $spec['icon'] ?></div>
                    <h3 class="modal-item-title"><?= $spec['ar'] ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideArabicSpecializedBooksMenu()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>

<!-- ============================================
     French Specialized Books Menu Modal
     ============================================ -->
<div id="frenchSpecializedBooksMenu" class="modal">
    <div class="modal-header">
        <div class="modal-icon">📚</div>
        <h2 class="modal-title">
            <?= $lang == 'ar' ? 'الكتب المتخصصة الفرنسية' : 'Livres Spécialisés Français' ?>
        </h2>
        <p class="modal-subtitle"><?= $texts['choose_specialization'] ?></p>
    </div>

    <div class="modal-content">
        <div class="modal-grid">
            <?php foreach ($specializations as $key => $spec): ?>
                <a href="modules/items/manage_books.php?category=specialized&lang=fr&specialization=<?= strtolower($key) ?>" class="modal-item">
                    <div class="modal-item-icon"><?= $spec['icon'] ?></div>
                    <h3 class="modal-item-title"><?= $spec['fr'] ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal-footer">
        <button onclick="hideFrenchSpecializedBooksMenu()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            <?= $texts['close'] ?>
        </button>
    </div>
</div>
