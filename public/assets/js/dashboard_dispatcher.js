/* Dashboard dispatcher moved to external module
   Expects `window._DashboardConfig` object with:
     - lang: current page language
     - texts: { books: {ar,fr}, magazines: {ar,fr}, specializedBooks: {ar,fr}, specializedMagazines: {ar,fr} }
*/
(function () {
    if (!window._DashboardConfig) window._DashboardConfig = { lang: 'ar', texts: {} };
    const cfg = window._DashboardConfig;

    function openMenu(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'block';
        const overlay = document.getElementById('menuOverlay');
        if (overlay) overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeMenu(id) {
        const el = document.getElementById(id);
        
        if (!el) return;
        el.style.display = 'none';
        const overlay = document.getElementById('menuOverlay');
        if (overlay) overlay.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.showResourcesMenu = function () { openMenu('resourcesMenu'); };
    window.hideResourcesMenu = function () { closeMenu('resourcesMenu'); };

    window.showArabicPeriodiquesMenu = function () { openMenu('arabicPeriodiquesMenu'); };
    window.hideArabicPeriodiquesMenu = function () { closeMenu('arabicPeriodiquesMenu'); };

    window.showFrenchPeriodiquesMenu = function () { openMenu('frenchPeriodiquesMenu'); };
    window.hideFrenchPeriodiquesMenu = function () { closeMenu('frenchPeriodiquesMenu'); };

    window.showITMenu = function () { openMenu('itMenu'); };
    window.hideITMenu = function () { closeMenu('itMenu'); };

    window.showAddResourcesMenu = function () { openMenu('addResourcesMenu'); };
    window.hideAddResourcesMenu = function () { closeMenu('addResourcesMenu'); };

    window.showBooksMenu = function (language) {
        const booksMenu = document.getElementById('booksMenu');
        const booksMenuTitle = document.getElementById('booksMenuTitle');
        const specializedLink = document.getElementById('specializedLink');
        const generalLink = document.getElementById('generalLink');

        window.currentLanguage = language;

        const texts = cfg.texts || {};
        const books = texts.books || { ar: 'وحدة الكتب العربية', fr: 'Unité Livres Français' };

        booksMenuTitle.textContent = language === 'ar' ? books.ar : books.fr;

        specializedLink.href = `modules/items/manage_books.php?category=specialized&lang=${language}`;
        generalLink.href = `modules/items/list_general_books.php?category=general&lang=${language}`;

        booksMenu.style.display = 'block';
        const overlay = document.getElementById('menuOverlay'); if (overlay) overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    window.hideBooksMenu = function () { closeMenu('booksMenu'); };

    window.showMagazinesMenu = function (language) {
        const magazinesMenu = document.getElementById('magazinesMenu');
        const magazinesMenuTitle = document.getElementById('magazinesMenuTitle');
        const magSpecializedLink = document.getElementById('magSpecializedLink');
        const magGeneralLink = document.getElementById('magGeneralLink');

        window.currentLanguage = language;
        const texts = cfg.texts || {};
        const mags = texts.magazines || { ar: 'المجلات العربية', fr: 'Revues Françaises' };

        magazinesMenuTitle.textContent = language === 'ar' ? mags.ar : mags.fr;

        magSpecializedLink.href = `modules/items/manage_books.php?category=specialized&lang=${language}`;
        magGeneralLink.href = `modules/items/list_general_magazines.php?category=general&lang=${language}`;

        magazinesMenu.style.display = 'block';
        const overlay = document.getElementById('menuOverlay'); if (overlay) overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    window.hideMagazinesMenu = function () { closeMenu('magazinesMenu'); };

    window.showSpecializedBooksMenu = function (language) {
        const specializedMenu = document.getElementById('specializedBooksMenu');
        const specializedMenuTitle = document.getElementById('specializedBooksMenuTitle');
        const adminLink = document.getElementById('adminLink');
        const lawLink = document.getElementById('lawLink');
        const economicsLink = document.getElementById('economicsLink');
        const diplomacyLink = document.getElementById('diplomacyLink');
        const mediaLink = document.getElementById('mediaLink');

        const texts = cfg.texts || {};
        const sBooks = texts.specializedBooks || { ar: 'الكتب المتخصصة العربية', fr: 'Livres Spécialisés Français' };
        specializedMenuTitle.textContent = language === 'ar' ? sBooks.ar : sBooks.fr;

        const baseUrl = `modules/items/manage_books.php?category=specialized&lang=${language}`;
        adminLink.href = `${baseUrl}&specialization=administration`;
        lawLink.href = `${baseUrl}&specialization=law`;
        economicsLink.href = `${baseUrl}&specialization=economics`;
        diplomacyLink.href = `${baseUrl}&specialization=diplomacy`;
        if (mediaLink) mediaLink.href = `${baseUrl}&specialization=media`;

        specializedMenu.style.display = 'block';
        const overlay = document.getElementById('menuOverlay'); if (overlay) overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    window.hideSpecializedBooksMenu = function () { closeMenu('specializedBooksMenu'); };

    window.showSpecializedMagazinesMenu = function (language) {
        const specializedMagazinesMenu = document.getElementById('specializedMagazinesMenu');
        const specializedMagazinesMenuTitle = document.getElementById('specializedMagazinesMenuTitle');
        const magAdminLink = document.getElementById('magAdminLink');
        const magLawLink = document.getElementById('magLawLink');
        const magEconomicsLink = document.getElementById('magEconomicsLink');
        const magDiplomacyLink = document.getElementById('magDiplomacyLink');
        const magMediaLink = document.getElementById('magMediaLink');

        const texts = cfg.texts || {};
        const sMags = texts.specializedMagazines || { ar: 'المجلات المتخصصة العربية', fr: 'Revues Spécialisées Françaises' };
        specializedMagazinesMenuTitle.textContent = language === 'ar' ? sMags.ar : sMags.fr;

        const baseUrl = `modules/items/list_magazines_specialization.php?category=specialized&lang=${language}`;
        if (magAdminLink) magAdminLink.href = `${baseUrl}&specialization=administration`;
        if (magLawLink) magLawLink.href = `${baseUrl}&specialization=law`;
        if (magEconomicsLink) magEconomicsLink.href = `${baseUrl}&specialization=economics`;
        if (magDiplomacyLink) magDiplomacyLink.href = `${baseUrl}&specialization=diplomacy`;
        if (magMediaLink) magMediaLink.href = `${baseUrl}&specialization=media`;

        specializedMagazinesMenu.style.display = 'block';
        const overlay = document.getElementById('menuOverlay'); if (overlay) overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    window.hideSpecializedMagazinesMenu = function () { closeMenu('specializedMagazinesMenu'); };

    // Generic overlay handler to hide everything
    const overlay = document.getElementById('menuOverlay');
    if (overlay) {
        overlay.addEventListener('click', function () {
            // hide known menus
            ['resourcesMenu','arabicPeriodiquesMenu','frenchPeriodiquesMenu','booksMenu','magazinesMenu','specializedBooksMenu','specializedMagazinesMenu','itMenu','addResourcesMenu'].forEach(id => {
                const el = document.getElementById(id); if (el) el.style.display = 'none';
            });
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.module-card, .stat-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        const menuLinks = document.querySelectorAll('#resourcesMenu a, #servicesMenu a, #periodiquesMenu a');
        menuLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            });
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });

})();
