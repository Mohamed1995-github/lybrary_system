<?php /* partial: resources modal */ ?>
<div id="resourcesMenu" class="modal" style="display:none">
  <div class="modal-inner">
    <div class="modal-header">
      <div class="modal-icon">📚</div>
      <h2><?= $t['collections'] ?></h2>
      <p><?= $lang == 'ar' ? 'اختر نوع المجموعة' : 'Choisissez le type de collection' ?></p>
    </div>

    <div class="modal-grid">
      <div class="card" data-modal="booksMenu">
        <div class="card-icon">📖</div>
        <div class="card-title"><?= $lang == 'ar' ? 'وحدة الكتب العربية' : 'Unité Livres Arabes' ?></div>
      </div>

      <div class="card" data-modal="magazinesMenu">
        <div class="card-icon">📚</div>
        <div class="card-title"><?= $lang == 'ar' ? 'وحدة الكتب الفرنسية' : 'Unité Livres Français' ?></div>
      </div>

      <div class="card" data-modal="arabicPeriodiquesMenu">
        <div class="card-icon">📰🇸🇦</div>
        <div class="card-title"><?= $lang == 'ar' ? 'وحدة الدوريات والبحوث العربية' : 'Périodiques et Recherches Arabes' ?></div>
      </div>
    </div>

    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeModal('resourcesMenu')"><?= $lang == 'ar' ? 'إغلاق' : 'Fermer' ?></button>
    </div>
  </div>
</div>
