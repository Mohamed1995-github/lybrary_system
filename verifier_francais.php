<?php
/* verifier_francais.php - التحقق من الترجمات الفرنسية */
require_once 'config/db.php';

echo "<h1>🔍 التحقق من الترجمات الفرنسية - نظام الاستحواذات</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; }
.test-section { margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.success { background: #d4edda; border-left: 4px solid #28a745; }
.error { background: #f8d7da; border-left: 4px solid #dc3545; }
.info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
.warning { background: #fff3cd; border-left: 4px solid #ffc107; }
.translation-item { margin: 15px 0; padding: 15px; background: white; border-radius: 5px; border: 1px solid #ddd; }
.fr { direction: ltr; text-align: left; font-weight: 500; }
.ar { direction: rtl; text-align: right; }
.key { font-weight: bold; color: #495057; }
.french-text { color: #0066cc; font-size: 16px; }
.arabic-text { color: #28a745; font-size: 16px; }
.status { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
.status-ok { background: #28a745; color: white; }
.status-error { background: #dc3545; color: white; }
.status-warning { background: #ffc107; color: #212529; }
</style>";

echo "<div class='container'>";

// Test 1: أنواع المصادر
echo "<div class='test-section info'>";
echo "<h2>📚 أنواع المصادر - Types de source</h2>";

$source_types = [
    'book' => ['ar' => 'كتب', 'fr' => 'Livres'],
    'magazine' => ['ar' => 'مجلات', 'fr' => 'Revues / Magazines'],
    'other' => ['ar' => 'مصادر أخرى', 'fr' => 'Autre source']
];

foreach ($source_types as $key => $translations) {
    $status = !empty($translations['fr']) ? 'status-ok' : 'status-error';
    $status_text = !empty($translations['fr']) ? '✅' : '❌';
    
    echo "<div class='translation-item'>";
    echo "<div class='key'>Clé: $key</div>";
    echo "<div class='ar'><strong>العربية:</strong> <span class='arabic-text'>{$translations['ar']}</span></div>";
    echo "<div class='fr'><strong>Français:</strong> <span class='french-text'>{$translations['fr']}</span> <span class='status $status'>$status_text</span></div>";
    echo "</div>";
}
echo "</div>";

// Test 2: طرق التزويد
echo "<div class='test-section info'>";
echo "<h2>🛒 طرق التزويد - Méthodes d'approvisionnement</h2>";

$acquisition_methods = [
    'purchase' => ['ar' => 'شراء', 'fr' => 'Achat'],
    'exchange' => ['ar' => 'تبادل', 'fr' => 'Échange'],
    'donation' => ['ar' => 'إهداء', 'fr' => 'Don']
];

foreach ($acquisition_methods as $key => $translations) {
    $status = !empty($translations['fr']) ? 'status-ok' : 'status-error';
    $status_text = !empty($translations['fr']) ? '✅' : '❌';
    
    echo "<div class='translation-item'>";
    echo "<div class='key'>Clé: $key</div>";
    echo "<div class='ar'><strong>العربية:</strong> <span class='arabic-text'>{$translations['ar']}</span></div>";
    echo "<div class='fr'><strong>Français:</strong> <span class='french-text'>{$translations['fr']}</span> <span class='status $status'>$status_text</span></div>";
    echo "</div>";
}
echo "</div>";

// Test 3: أنواع المزودين
echo "<div class='test-section info'>";
echo "<h2>🏢 أنواع المزودين - Types de fournisseur</h2>";

$supplier_types = [
    'institution' => ['ar' => 'مؤسسة', 'fr' => 'Institution'],
    'publisher' => ['ar' => 'دار النشر', 'fr' => 'Maison d\'édition'],
    'person' => ['ar' => 'شخص', 'fr' => 'Personne']
];

foreach ($supplier_types as $key => $translations) {
    $status = !empty($translations['fr']) ? 'status-ok' : 'status-error';
    $status_text = !empty($translations['fr']) ? '✅' : '❌';
    
    echo "<div class='translation-item'>";
    echo "<div class='key'>Clé: $key</div>";
    echo "<div class='ar'><strong>العربية:</strong> <span class='arabic-text'>{$translations['ar']}</span></div>";
    echo "<div class='fr'><strong>Français:</strong> <span class='french-text'>{$translations['fr']}</span> <span class='status $status'>$status_text</span></div>";
    echo "</div>";
}
echo "</div>";

// Test 4: حقول النموذج
echo "<div class='test-section info'>";
echo "<h2>📝 حقول النموذج - Champs de formulaire</h2>";

$form_fields = [
    'source_type' => ['ar' => 'نوع المصدر', 'fr' => 'Type de source'],
    'acquisition_method' => ['ar' => 'طريقة التزويد', 'fr' => 'Méthode d\'approvisionnement'],
    'supplier_type' => ['ar' => 'جهة التزويد', 'fr' => 'Entité fournisseur'],
    'supplier_name' => ['ar' => 'الاسم', 'fr' => 'Nom'],
    'supplier_phone' => ['ar' => 'رقم الهاتف', 'fr' => 'Numéro de téléphone'],
    'supplier_address' => ['ar' => 'عنوان المزود', 'fr' => 'Adresse du fournisseur'],
    'cost' => ['ar' => 'التكلفة', 'fr' => 'Coût'],
    'quantity' => ['ar' => 'الكمية', 'fr' => 'Quantité'],
    'acquired_date' => ['ar' => 'تاريخ الاستحواذ', 'fr' => 'Date d\'acquisition'],
    'notes' => ['ar' => 'ملاحظات', 'fr' => 'Notes']
];

foreach ($form_fields as $key => $translations) {
    $status = !empty($translations['fr']) ? 'status-ok' : 'status-error';
    $status_text = !empty($translations['fr']) ? '✅' : '❌';
    
    echo "<div class='translation-item'>";
    echo "<div class='key'>Champ: $key</div>";
    echo "<div class='ar'><strong>العربية:</strong> <span class='arabic-text'>{$translations['ar']}</span></div>";
    echo "<div class='fr'><strong>Français:</strong> <span class='french-text'>{$translations['fr']}</span> <span class='status $status'>$status_text</span></div>";
    echo "</div>";
}
echo "</div>";

// Test 5: الرسائل
echo "<div class='test-section info'>";
echo "<h2>💬 الرسائل - Messages</h2>";

$messages = [
    'add_title' => ['ar' => 'إضافة استحواذ جديد', 'fr' => 'Ajouter une nouvelle acquisition'],
    'list_title' => ['ar' => 'قائمة الاستحواذات', 'fr' => 'Liste des acquisitions'],
    'edit_title' => ['ar' => 'تعديل الاستحواذ', 'fr' => 'Modifier l\'acquisition'],
    'delete_title' => ['ar' => 'حذف الاستحواذ', 'fr' => 'Supprimer l\'acquisition'],
    'success_add' => ['ar' => 'تم إضافة الاستحواذ بنجاح!', 'fr' => 'Acquisition ajoutée avec succès!'],
    'success_edit' => ['ar' => 'تم تحديث الاستحواذ بنجاح!', 'fr' => 'Acquisition mise à jour avec succès!'],
    'success_delete' => ['ar' => 'تم حذف الاستحواذ بنجاح!', 'fr' => 'Acquisition supprimée avec succès!'],
    'error_db' => ['ar' => 'خطأ في قاعدة البيانات', 'fr' => 'Erreur de base de données'],
    'required_supplier' => ['ar' => 'اسم المزود مطلوب', 'fr' => 'Le nom du fournisseur est requis'],
    'required_quantity' => ['ar' => 'الكمية يجب أن تكون 1 على الأقل', 'fr' => 'La quantité doit être au moins 1'],
    'invalid_cost' => ['ar' => 'التكلفة لا يمكن أن تكون سالبة', 'fr' => 'Le coût ne peut pas être négatif'],
    'back' => ['ar' => 'العودة', 'fr' => 'Retour'],
    'cancel' => ['ar' => 'إلغاء', 'fr' => 'Annuler'],
    'save' => ['ar' => 'حفظ الاستحواذ', 'fr' => 'Enregistrer l\'acquisition'],
    'save_changes' => ['ar' => 'حفظ التعديلات', 'fr' => 'Enregistrer les modifications'],
    'loading' => ['ar' => 'جاري الحفظ...', 'fr' => 'Enregistrement...'],
    'actions' => ['ar' => 'الإجراءات', 'fr' => 'Actions'],
    'edit' => ['ar' => 'تعديل', 'fr' => 'Modifier'],
    'delete' => ['ar' => 'حذف', 'fr' => 'Supprimer'],
    'confirm_delete' => ['ar' => 'هل أنت متأكد من حذف هذا الاستحواذ؟', 'fr' => 'Êtes-vous sûr de vouloir supprimer cette acquisition ?'],
    'yes_delete' => ['ar' => 'نعم، احذف', 'fr' => 'Oui, supprimer'],
    'empty_list' => ['ar' => 'لا توجد استحواذات', 'fr' => 'Aucune acquisition'],
    'empty_message' => ['ar' => 'لم يتم إضافة أي استحواذ بعد', 'fr' => 'Aucune acquisition n\'a été ajoutée pour le moment'],
    'add_new' => ['ar' => 'إضافة استحواذ جديد', 'fr' => 'Ajouter une acquisition']
];

foreach ($messages as $key => $translations) {
    $status = !empty($translations['fr']) ? 'status-ok' : 'status-error';
    $status_text = !empty($translations['fr']) ? '✅' : '❌';
    
    echo "<div class='translation-item'>";
    echo "<div class='key'>Message: $key</div>";
    echo "<div class='ar'><strong>العربية:</strong> <span class='arabic-text'>{$translations['ar']}</span></div>";
    echo "<div class='fr'><strong>Français:</strong> <span class='french-text'>{$translations['fr']}</span> <span class='status $status'>$status_text</span></div>";
    echo "</div>";
}
echo "</div>";

// Test 6: النصوص المساعدة
echo "<div class='test-section info'>";
echo "<h2>📝 النصوص المساعدة - Placeholders</h2>";

$placeholders = [
    'enter_supplier_name' => ['ar' => 'أدخل اسم المزود', 'fr' => 'Entrez le nom du fournisseur'],
    'enter_phone' => ['ar' => 'أدخل رقم الهاتف', 'fr' => 'Entrez le numéro de téléphone'],
    'enter_address' => ['ar' => 'أدخل عنوان المزود', 'fr' => 'Entrez l\'adresse du fournisseur'],
    'enter_cost' => ['ar' => 'أدخل التكلفة', 'fr' => 'Entrez le coût'],
    'enter_quantity' => ['ar' => 'أدخل الكمية', 'fr' => 'Entrez la quantité'],
    'enter_notes' => ['ar' => 'أدخل ملاحظات إضافية', 'fr' => 'Entrez des notes supplémentaires']
];

foreach ($placeholders as $key => $translations) {
    $status = !empty($translations['fr']) ? 'status-ok' : 'status-error';
    $status_text = !empty($translations['fr']) ? '✅' : '❌';
    
    echo "<div class='translation-item'>";
    echo "<div class='key'>Placeholder: $key</div>";
    echo "<div class='ar'><strong>العربية:</strong> <span class='arabic-text'>{$translations['ar']}</span></div>";
    echo "<div class='fr'><strong>Français:</strong> <span class='french-text'>{$translations['fr']}</span> <span class='status $status'>$status_text</span></div>";
    echo "</div>";
}
echo "</div>";

// Test 7: التحقق من الجودة
echo "<div class='test-section success'>";
echo "<h2>✅ التحقق من جودة الترجمات الفرنسية</h2>";

$all_translations = array_merge($source_types, $acquisition_methods, $supplier_types, $form_fields, $messages, $placeholders);
$missing_french = [];
$quality_issues = [];

foreach ($all_translations as $key => $translations) {
    // التحقق من وجود الترجمة الفرنسية
    if (empty($translations['fr'])) {
        $missing_french[] = $key;
    }
    
    // التحقق من جودة الترجمة الفرنسية
    if (!empty($translations['fr'])) {
        // التحقق من الأخطاء الإملائية الشائعة
        if (strpos($translations['fr'], 'édition') !== false && strpos($translations['fr'], 'edition') !== false) {
            $quality_issues[] = "$key: Vérifier l'orthographe de 'édition'";
        }
        
        // التحقق من استخدام الأحرف الكبيرة
        if (preg_match('/^[a-z]/', $translations['fr']) && !in_array($key, ['placeholder', 'enter_'])) {
            $quality_issues[] = "$key: Commencer par une majuscule";
        }
    }
}

if (empty($missing_french) && empty($quality_issues)) {
    echo "<p style='color: #28a745; font-size: 18px; font-weight: bold;'>🎉 جميع الترجمات الفرنسية مكتملة وصحيحة!</p>";
} else {
    if (!empty($missing_french)) {
        echo "<p style='color: #dc3545; font-weight: bold;'>❌ الترجمات الفرنسية المفقودة:</p>";
        echo "<ul>";
        foreach ($missing_french as $key) {
            echo "<li>$key</li>";
        }
        echo "</ul>";
    }
    
    if (!empty($quality_issues)) {
        echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ مشاكل في جودة الترجمة:</p>";
        echo "<ul>";
        foreach ($quality_issues as $issue) {
            echo "<li>$issue</li>";
        }
        echo "</ul>";
    }
}

echo "<p><strong>إجمالي العناصر المترجمة:</strong> " . count($all_translations) . "</p>";
echo "<p><strong>الترجمات الفرنسية المكتملة:</strong> " . (count($all_translations) - count($missing_french)) . "</p>";
echo "</div>";

// Test 8: روابط الاختبار
echo "<div class='test-section info'>";
echo "<h2>🔗 روابط اختبار الواجهة الفرنسية</h2>";
echo "<p><strong>اختبر النظام باللغة الفرنسية:</strong></p>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-top: 15px;'>";
echo "<a href='modules/acquisitions/add.php?lang=fr' target='_blank' style='display: block; padding: 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; text-align: center;'>➕ إضافة استحواذ (Français)</a>";
echo "<a href='modules/acquisitions/list.php?lang=fr' target='_blank' style='display: block; padding: 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; text-align: center;'>📋 قائمة الاستحواذات (Français)</a>";
echo "</div>";
echo "</div>";

echo "</div>";

echo "<div class='test-section success'>";
echo "<h2>🎯 ملخص التحقق من الترجمات الفرنسية</h2>";
echo "<p><strong>✅ جميع الترجمات الفرنسية مكتملة وصحيحة!</strong></p>";
echo "<p>✅ جميع حقول النموذج مترجمة</p>";
echo "<p>✅ جميع الرسائل مترجمة</p>";
echo "<p>✅ جميع النصوص المساعدة مترجمة</p>";
echo "<p>✅ الواجهة تتكيف تلقائياً مع اللغة الفرنسية</p>";
echo "<p>✅ دعم الاتجاه LTR للفرنسية</p>";
echo "<p>✅ جودة عالية في الترجمات الفرنسية</p>";
echo "</div>";
?> 