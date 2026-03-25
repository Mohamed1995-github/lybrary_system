/**
 * Dashboard JavaScript - Optimized and Modular
 * Version: 2.0
 * Description: Handles all dashboard interactions and modal management
 */

(function() {
    'use strict';

    // ============================================
    // Configuration
    // ============================================
    const CONFIG = {
        animationDuration: 300,
        modalZIndex: 1000,
        overlayZIndex: 999
    };

    // ============================================
    // State Management
    // ============================================
    const state = {
        currentLanguage: null,
        activeModal: null,
        modalsStack: []
    };

    // ============================================
    // Modal Manager
    // ============================================
    class ModalManager {
        constructor() {
            this.overlay = document.getElementById('menuOverlay');
            this.modals = {};
            this.init();
        }

        init() {
            // Register all modals
            const modalElements = document.querySelectorAll('.modal');
            modalElements.forEach(modal => {
                this.modals[modal.id] = modal;
            });

            // Setup overlay click handler
            if (this.overlay) {
                this.overlay.addEventListener('click', () => this.hideAll());
            }

            // Setup ESC key handler
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.hideAll();
                }
            });
        }

        show(modalId) {
            const modal = this.modals[modalId];
            if (!modal) {
                console.error(`Modal ${modalId} not found`);
                return;
            }

            // Hide current modal if exists
            if (state.activeModal) {
                this.hide(state.activeModal);
            }

            // Show new modal
            modal.style.display = 'block';
            modal.classList.add('active');
            
            if (this.overlay) {
                this.overlay.style.display = 'block';
                this.overlay.classList.add('active');
            }

            document.body.style.overflow = 'hidden';
            state.activeModal = modalId;
            state.modalsStack.push(modalId);

            // Trigger animation
            setTimeout(() => {
                modal.style.opacity = '1';
            }, 10);
        }

        hide(modalId) {
            const modal = this.modals[modalId];
            if (!modal) return;

            modal.style.opacity = '0';
            
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('active');
                
                // Remove from stack
                state.modalsStack = state.modalsStack.filter(id => id !== modalId);
                
                // If no more modals, hide overlay
                if (state.modalsStack.length === 0) {
                    if (this.overlay) {
                        this.overlay.style.display = 'none';
                        this.overlay.classList.remove('active');
                    }
                    document.body.style.overflow = 'auto';
                    state.activeModal = null;
                } else {
                    // Show previous modal
                    const previousModal = state.modalsStack[state.modalsStack.length - 1];
                    state.activeModal = previousModal;
                }
            }, CONFIG.animationDuration);
        }

        hideAll() {
            Object.keys(this.modals).forEach(modalId => {
                this.hide(modalId);
            });
            
            if (this.overlay) {
                this.overlay.style.display = 'none';
                this.overlay.classList.remove('active');
            }
            
            document.body.style.overflow = 'auto';
            state.activeModal = null;
            state.modalsStack = [];
        }

        toggle(modalId) {
            const modal = this.modals[modalId];
            if (!modal) return;

            if (modal.style.display === 'block') {
                this.hide(modalId);
            } else {
                this.show(modalId);
            }
        }
    }

    // ============================================
    // Menu Functions
    // ============================================
    const MenuFunctions = {
        // Resources Menu
        showResourcesMenu() {
            modalManager.show('resourcesMenu');
        },

        hideResourcesMenu() {
            modalManager.hide('resourcesMenu');
        },

        // Books Menu
        showBooksMenu(language) {
            state.currentLanguage = language;
            const title = document.getElementById('booksMenuTitle');
            const specializedLink = document.getElementById('specializedLink');
            const generalLink = document.getElementById('generalLink');

            if (title) {
                title.textContent = language === 'ar' 
                    ? 'وحدة الكتب العربية' 
                    : 'Unité Livres Français';
            }

            if (specializedLink) {
                specializedLink.onclick = () => this.showSpecializedBooksMenu(language);
            }

            if (generalLink) {
                generalLink.href = `modules/items/list_general_books.php?category=general&lang=${language}`;
            }

            modalManager.show('booksMenu');
        },

        hideBooksMenu() {
            modalManager.hide('booksMenu');
        },

        // Specialized Books Menu
        showSpecializedBooksMenu(language) {
            state.currentLanguage = language;
            const title = document.getElementById('specializedBooksMenuTitle');
            
            if (title) {
                title.textContent = language === 'ar' 
                    ? 'الكتب المتخصصة العربية' 
                    : 'Livres Spécialisés Français';
            }

            const baseUrl = `modules/items/manage_books.php?category=specialized&lang=${language}`;
            const specializations = ['administration', 'law', 'economics', 'diplomacy', 'media'];
            
            specializations.forEach(spec => {
                const link = document.getElementById(`${spec}Link`);
                if (link) {
                    link.href = `${baseUrl}&specialization=${spec}`;
                }
            });

            modalManager.show('specializedBooksMenu');
        },

        hideSpecializedBooksMenu() {
            modalManager.hide('specializedBooksMenu');
        },

        // Magazines Menu
        showMagazinesMenu(language) {
            state.currentLanguage = language;
            const title = document.getElementById('magazinesMenuTitle');
            
            if (title) {
                title.textContent = language === 'ar' 
                    ? 'المجلات العربية' 
                    : 'Revues Françaises';
            }

            const magSpecializedLink = document.getElementById('magSpecializedLink');
            const magGeneralLink = document.getElementById('magGeneralLink');

            if (magSpecializedLink) {
                magSpecializedLink.onclick = () => this.showSpecializedMagazinesMenu(language);
            }

            if (magGeneralLink) {
                magGeneralLink.href = `modules/items/list_general_magazines.php?category=general&lang=${language}`;
            }

            modalManager.show('magazinesMenu');
        },

        hideMagazinesMenu() {
            modalManager.hide('magazinesMenu');
        },

        // Specialized Magazines Menu
        showSpecializedMagazinesMenu(language) {
            state.currentLanguage = language;
            const title = document.getElementById('specializedMagazinesMenuTitle');
            
            if (title) {
                title.textContent = language === 'ar' 
                    ? 'المجلات المتخصصة العربية' 
                    : 'Revues Spécialisées Françaises';
            }

            const baseUrl = `modules/items/list_magazines_specialization.php?category=specialized&lang=${language}`;
            const specializations = ['administration', 'law', 'economics', 'diplomacy', 'media'];
            
            specializations.forEach(spec => {
                const link = document.getElementById(`mag${spec.charAt(0).toUpperCase() + spec.slice(1)}Link`);
                if (link) {
                    link.href = `${baseUrl}&specialization=${spec}`;
                }
            });

            modalManager.show('specializedMagazinesMenu');
        },

        hideSpecializedMagazinesMenu() {
            modalManager.hide('specializedMagazinesMenu');
        },

        // Arabic Periodiques Menu
        showArabicPeriodiquesMenu() {
            modalManager.show('arabicPeriodiquesMenu');
        },

        hideArabicPeriodiquesMenu() {
            modalManager.hide('arabicPeriodiquesMenu');
        },

        // French Periodiques Menu
        showFrenchPeriodiquesMenu() {
            modalManager.show('frenchPeriodiquesMenu');
        },

        hideFrenchPeriodiquesMenu() {
            modalManager.hide('frenchPeriodiquesMenu');
        },

        // Periodiques Menu (Combined)
        showPeriodiquesMenu() {
            modalManager.show('periodiquesMenu');
        },

        hidePeriodiquesMenu() {
            modalManager.hide('periodiquesMenu');
        },

        // IT Menu
        showITMenu() {
            modalManager.show('itMenu');
        },

        hideITMenu() {
            modalManager.hide('itMenu');
        },

        // Add Resources Menu
        showAddResourcesMenu() {
            modalManager.show('addResourcesMenu');
        },

        hideAddResourcesMenu() {
            modalManager.hide('addResourcesMenu');
        },

        // Newspapers Menu
        showNewspapersMenu() {
            modalManager.show('newspapersMenu');
        },

        hideNewspapersMenu() {
            modalManager.hide('newspapersMenu');
        },

        // Arabic Unit Menu
        showArabicUnitMenu() {
            modalManager.show('arabicUnitMenu');
        },

        hideArabicUnitMenu() {
            modalManager.hide('arabicUnitMenu');
        },

        // French Unit Menu
        showFrenchUnitMenu() {
            modalManager.show('frenchUnitMenu');
        },

        hideFrenchUnitMenu() {
            modalManager.hide('frenchUnitMenu');
        },

        // Arabic Specialized Books Menu
        showArabicSpecializedBooksMenu() {
            modalManager.show('arabicSpecializedBooksMenu');
        },

        hideArabicSpecializedBooksMenu() {
            modalManager.hide('arabicSpecializedBooksMenu');
        },

        // French Specialized Books Menu
        showFrenchSpecializedBooksMenu() {
            modalManager.show('frenchSpecializedBooksMenu');
        },

        hideFrenchSpecializedBooksMenu() {
            modalManager.hide('frenchSpecializedBooksMenu');
        }
    };

    // ============================================
    // Animation Utilities
    // ============================================
    const AnimationUtils = {
        fadeIn(element, duration = 300) {
            element.style.opacity = '0';
            element.style.display = 'block';
            
            setTimeout(() => {
                element.style.transition = `opacity ${duration}ms ease`;
                element.style.opacity = '1';
            }, 10);
        },

        fadeOut(element, duration = 300) {
            element.style.transition = `opacity ${duration}ms ease`;
            element.style.opacity = '0';
            
            setTimeout(() => {
                element.style.display = 'none';
            }, duration);
        },

        slideUp(element, duration = 600) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = `all ${duration}ms ease`;
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 10);
        }
    };

    // ============================================
    // Card Animations
    // ============================================
    function animateCards() {
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
    }

    // ============================================
    // Menu Link Hover Effects
    // ============================================
    function setupMenuLinkEffects() {
        const menuLinks = document.querySelectorAll('.modal-item');
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
    }

    // ============================================
    // Initialize
    // ============================================
    let modalManager;

    function init() {
        // Initialize modal manager
        modalManager = new ModalManager();

        // Expose menu functions globally
        window.showResourcesMenu = MenuFunctions.showResourcesMenu.bind(MenuFunctions);
        window.hideResourcesMenu = MenuFunctions.hideResourcesMenu.bind(MenuFunctions);
        window.showBooksMenu = MenuFunctions.showBooksMenu.bind(MenuFunctions);
        window.hideBooksMenu = MenuFunctions.hideBooksMenu.bind(MenuFunctions);
        window.showSpecializedBooksMenu = MenuFunctions.showSpecializedBooksMenu.bind(MenuFunctions);
        window.hideSpecializedBooksMenu = MenuFunctions.hideSpecializedBooksMenu.bind(MenuFunctions);
        window.showMagazinesMenu = MenuFunctions.showMagazinesMenu.bind(MenuFunctions);
        window.hideMagazinesMenu = MenuFunctions.hideMagazinesMenu.bind(MenuFunctions);
        window.showSpecializedMagazinesMenu = MenuFunctions.showSpecializedMagazinesMenu.bind(MenuFunctions);
        window.hideSpecializedMagazinesMenu = MenuFunctions.hideSpecializedMagazinesMenu.bind(MenuFunctions);
        window.showArabicPeriodiquesMenu = MenuFunctions.showArabicPeriodiquesMenu.bind(MenuFunctions);
        window.hideArabicPeriodiquesMenu = MenuFunctions.hideArabicPeriodiquesMenu.bind(MenuFunctions);
        window.showFrenchPeriodiquesMenu = MenuFunctions.showFrenchPeriodiquesMenu.bind(MenuFunctions);
        window.hideFrenchPeriodiquesMenu = MenuFunctions.hideFrenchPeriodiquesMenu.bind(MenuFunctions);
        window.showPeriodiquesMenu = MenuFunctions.showPeriodiquesMenu.bind(MenuFunctions);
        window.hidePeriodiquesMenu = MenuFunctions.hidePeriodiquesMenu.bind(MenuFunctions);
        window.showITMenu = MenuFunctions.showITMenu.bind(MenuFunctions);
        window.hideITMenu = MenuFunctions.hideITMenu.bind(MenuFunctions);
        window.showAddResourcesMenu = MenuFunctions.showAddResourcesMenu.bind(MenuFunctions);
        window.hideAddResourcesMenu = MenuFunctions.hideAddResourcesMenu.bind(MenuFunctions);
        window.showNewspapersMenu = MenuFunctions.showNewspapersMenu.bind(MenuFunctions);
        window.hideNewspapersMenu = MenuFunctions.hideNewspapersMenu.bind(MenuFunctions);
        window.showArabicUnitMenu = MenuFunctions.showArabicUnitMenu.bind(MenuFunctions);
        window.hideArabicUnitMenu = MenuFunctions.hideArabicUnitMenu.bind(MenuFunctions);
        window.showFrenchUnitMenu = MenuFunctions.showFrenchUnitMenu.bind(MenuFunctions);
        window.hideFrenchUnitMenu = MenuFunctions.hideFrenchUnitMenu.bind(MenuFunctions);
        window.showArabicSpecializedBooksMenu = MenuFunctions.showArabicSpecializedBooksMenu.bind(MenuFunctions);
        window.hideArabicSpecializedBooksMenu = MenuFunctions.hideArabicSpecializedBooksMenu.bind(MenuFunctions);
        window.showFrenchSpecializedBooksMenu = MenuFunctions.showFrenchSpecializedBooksMenu.bind(MenuFunctions);
        window.hideFrenchSpecializedBooksMenu = MenuFunctions.hideFrenchSpecializedBooksMenu.bind(MenuFunctions);

        // Animate cards on load
        animateCards();

        // Setup menu link effects
        setupMenuLinkEffects();

        // Log initialization
        console.log('✅ Dashboard JavaScript initialized successfully');
    }

    // ============================================
    // DOM Ready
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ============================================
    // Export for testing (optional)
    // ============================================
    window.DashboardJS = {
        version: '2.0',
        modalManager: () => modalManager,
        state: () => state,
        AnimationUtils
    };

})();
