// Form submission enhancement
function enhanceFormSubmission(formId, successMessage, loadingMessage) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + loadingMessage;
            submitBtn.disabled = true;
        }
        
        // Show success notification after a short delay
        setTimeout(() => {
            if (window.LibrarySystem && window.LibrarySystem.showNotification) {
                window.LibrarySystem.showNotification(successMessage, 'success');
            }
        }, 1000);
    });
}

// Initialize form enhancements on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Acquisition form
    enhanceFormSubmission('acquisitionForm', 
        (window.lang === 'ar' ? 'تم حفظ الاستحواذ بنجاح!' : 'Acquisition enregistrée avec succès!'),
        (window.lang === 'ar' ? 'جاري الحفظ...' : 'Enregistrement...')
    );
    
    // Other forms can be added here
});
