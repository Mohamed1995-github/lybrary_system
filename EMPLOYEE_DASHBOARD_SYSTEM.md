# لوحة التحكم المخصصة للموظفين - Employee Dashboard System

## نظرة عامة - Vue d'ensemble

لوحة التحكم المخصصة للموظفين تسمح لكل موظف بالوصول إلى وظائف محددة حسب حقوق الوصول الممنوحة له. النظام يعرض فقط القوائم والإجراءات التي يمكن للموظف الوصول إليها.

Le tableau de bord personnalisé des employés permet à chaque employé d'accéder à des fonctionnalités spécifiques selon ses droits d'accès. Le système affiche uniquement les menus et actions auxquels l'employé peut accéder.

## الميزات الرئيسية - Fonctionnalités principales

### 🔐 نظام تسجيل دخول مخصص
- **صفحة تسجيل دخول منفصلة للموظفين**: `public/employee_login.php`
- **استخدام رقم الموظف وكلمة المرور**
- **التحقق من وجود الموظف في قاعدة البيانات**
- **توجيه تلقائي إلى لوحة التحكم المخصصة**

### 🎯 لوحة تحكم مخصصة
- **عرض معلومات الموظف**: الاسم، الوظيفة، الرقم
- **عرض حقوق الوصول الممنوحة**
- **إحصائيات سريعة حسب الصلاحيات**
- **قوائم متاحة حسب الحقوق**
- **إجراءات سريعة حسب الصلاحيات**

### 🔒 نظام إدارة الحقوق
- **التحقق من الحقوق الفردية**
- **التحقق من وجود أي من الحقوق المحددة**
- **التحقق من وجود جميع الحقوق المحددة**
- **إدارة الصلاحيات (إضافة، تعديل، حذف)**

## الملفات الرئيسية - Fichiers principaux

### إدارة الحقوق - Gestion des permissions
- `includes/permissions.php` - نظام إدارة الحقوق والصلاحيات

### تسجيل الدخول - Connexion
- `public/employee_login.php` - صفحة تسجيل دخول الموظفين
- `public/login.php` - صفحة تسجيل الدخول العامة (محدثة)

### لوحة التحكم - Tableau de bord
- `public/employee_dashboard.php` - لوحة التحكم المخصصة للموظفين

### الاختبار - Test
- `test_employee_dashboard.php` - سكريبت اختبار النظام

## وظائف إدارة الحقوق - Fonctions de gestion des permissions

### الحصول على الحقوق - Obtenir les droits
```php
// الحصول على حقوق الموظف الحالي
$rights = getCurrentEmployeeRights();

// التحقق من وجود حق معين
if (hasPermission('إدارة الكتب')) {
    // يمكن إضافة كتب
}

// التحقق من وجود أي من الحقوق
if (hasAnyPermission(['إدارة الكتب', 'إدارة المجلات'])) {
    // يمكن إدارة الكتب أو المجلات
}

// التحقق من وجود جميع الحقوق
if (hasAllPermissions(['إدارة الكتب', 'إدارة النظام'])) {
    // يمكن إدارة الكتب والنظام
}
```

### القوائم والإجراءات - Menus et actions
```php
// الحصول على القوائم المتاحة
$menus = getAvailableMenus($lang);

// الحصول على الإجراءات المتاحة
$actions = getAvailableActions($lang);

// التحقق من الصلاحيات
$canAdd = canAdd();
$canEdit = canEdit();
$canDelete = canDelete();
```

## حقوق الوصول المتاحة - Droits d'accès disponibles

### العربية - Arabe
- إدارة الكتب
- إدارة المجلات
- إدارة الصحف
- إدارة التزويد
- إدارة المستعيرين
- إدارة الإعارة
- إدارة الموظفين
- إدارة النظام

### Français
- Gestion des livres
- Gestion des magazines
- Gestion des journaux
- Gestion des approvisionnements
- Gestion des emprunteurs
- Gestion des prêts
- Gestion des employés
- Administration du système

## أمثلة على الاستخدام - Exemples d'utilisation

### موظف بسيط (أمين مكتبة)
**الحقوق**: إدارة الكتب، إدارة المجلات، إدارة المستعيرين، إدارة الإعارة

**ما يراه**:
- قائمة الكتب والمجلات
- إضافة كتب ومجلات جديدة
- إدارة المستعيرين
- إدارة الإعارة
- إحصائيات الكتب والمجلات والمستعيرين

**ما لا يراه**:
- إدارة الموظفين
- إدارة النظام
- إدارة التزويد

### مدير المكتبة
**الحقوق**: جميع الحقوق

**ما يراه**:
- جميع القوائم والإجراءات
- جميع الإحصائيات
- إمكانية الحذف والتعديل

## كيفية الاستخدام - Comment utiliser

### 1. تسجيل الدخول
```
1. اذهب إلى: public/employee_login.php
2. أدخل رقم الموظف: EMP001
3. أدخل كلمة المرور: 123456
4. انقر على "تسجيل الدخول"
```

### 2. استكشاف لوحة التحكم
```
1. ستظهر معلومات الموظف في الأعلى
2. ستظهر حقوق الوصول الممنوحة
3. ستظهر الإحصائيات المتاحة
4. ستظهر القوائم المتاحة حسب الحقوق
5. ستظهر الإجراءات السريعة المتاحة
```

### 3. التنقل في النظام
```
- انقر على أي قائمة للوصول إليها
- استخدم الإجراءات السريعة للوظائف الشائعة
- ستظهر فقط الوظائف المسموح بها
```

## الأمان - Sécurité

### التحقق من تسجيل الدخول
- جميع الصفحات تتحقق من وجود `$_SESSION['uid']`
- التوجيه التلقائي لصفحة تسجيل الدخول عند الحاجة

### التحقق من الحقوق
- كل وظيفة تتحقق من الحقوق المطلوبة
- عرض فقط القوائم والإجراءات المسموح بها
- حماية من الوصول غير المصرح به

### إدارة الجلسات
- تخزين معلومات الموظف في الجلسة
- التحقق من صحة البيانات في كل طلب

## التخصيص - Personnalisation

### إضافة حقوق جديدة
```php
// في includes/permissions.php
$menuDefinitions['new_module'] = [
    'ar' => ['title' => 'الوحدة الجديدة', 'icon' => 'fas fa-star', 'url' => '../modules/new_module/list.php'],
    'fr' => ['title' => 'Nouveau module', 'icon' => 'fas fa-star', 'url' => '../modules/new_module/list.php'],
    'permissions' => ['إدارة الوحدة الجديدة', 'Gestion du nouveau module']
];
```

### تعديل الصلاحيات
```php
// تعديل دالة canDelete()
function canDelete() {
    return hasAnyPermission(['إدارة النظام', 'إدارة الموظفين', 'Administration du système', 'Gestion des employés']);
}
```

## الاختبار - Test

### تشغيل الاختبار
```bash
php test_employee_dashboard.php
```

### اختبار يدوي
1. اذهب إلى `public/employee_login.php`
2. سجل دخول باستخدام بيانات تجريبية
3. تحقق من ظهور القوائم الصحيحة
4. اختبر الوظائف المختلفة

## الدعم - Support

للحصول على المساعدة أو الإبلاغ عن مشاكل:
- تحقق من سجلات الأخطاء
- اختبر النظام باستخدام `test_employee_dashboard.php`
- تأكد من صحة بيانات الموظفين في قاعدة البيانات

Pour obtenir de l'aide ou signaler des problèmes:
- Vérifiez les logs d'erreurs
- Testez le système avec `test_employee_dashboard.php`
- Assurez-vous de la validité des données des employés dans la base de données 