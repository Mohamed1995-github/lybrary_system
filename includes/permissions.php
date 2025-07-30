<?php
/* includes/permissions.php */
require_once __DIR__ . '/auth.php';

/**
 * الحصول على حقوق الوصول للموظف الحالي
 * Obtenir les droits d'accès de l'employé actuel
 */
function getCurrentEmployeeRights() {
    global $pdo;
    
    if (!isset($_SESSION['uid'])) {
        return [];
    }
    
    try {
        // البحث عن الموظف بواسطة معرف المستخدم
        $stmt = $pdo->prepare("SELECT access_rights, function FROM employees WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $employee = $stmt->fetch();
        
        if ($employee) {
            $rights = explode(',', $employee['access_rights']);
            return array_map('trim', $rights);
        }
    } catch (PDOException $e) {
        // في حالة الخطأ، إرجاع مصفوفة فارغة
    }
    
    return [];
}

/**
 * الحصول على مستوى الوصول للموظف
 * Obtenir le niveau d'accès de l'employé
 */
function getEmployeeAccessLevel() {
    global $pdo;
    
    if (!isset($_SESSION['uid'])) {
        return 'none';
    }
    
    try {
        $stmt = $pdo->prepare("SELECT function FROM employees WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $employee = $stmt->fetch();
        
        if ($employee) {
            $function = $employee['function'];
            
            // تحديد مستوى الوصول حسب الوظيفة
            if (strpos($function, 'مدير') !== false || strpos($function, 'Directeur') !== false) {
                return 'admin';
            } elseif (strpos($function, 'أمين') !== false || strpos($function, 'Bibliothécaire') !== false) {
                return 'librarian';
            } elseif (strpos($function, 'مساعد') !== false || strpos($function, 'Assistant') !== false) {
                return 'assistant';
            } elseif (strpos($function, 'استقبال') !== false || strpos($function, 'accueil') !== false) {
                return 'reception';
            } elseif (strpos($function, 'تقني') !== false || strpos($function, 'technique') !== false) {
                return 'technical';
            }
        }
    } catch (PDOException $e) {
        // في حالة الخطأ، إرجاع مستوى منخفض
    }
    
    return 'limited';
}

/**
 * التحقق من وجود حق وصول معين
 * Vérifier si l'employé a un droit d'accès spécifique
 */
function hasPermission($permission) {
    $rights = getCurrentEmployeeRights();
    $accessLevel = getEmployeeAccessLevel();
    
    // التحقق من مستوى الوصول أولاً
    if ($accessLevel === 'none') {
        return false;
    }
    
    // المدير لديه جميع الصلاحيات
    if ($accessLevel === 'admin') {
        return true;
    }
    
    // التحقق من الصلاحية المحددة
    return in_array($permission, $rights);
}

/**
 * التحقق من وجود أي من الحقوق المحددة
 * Vérifier si l'employé a au moins un des droits spécifiés
 */
function hasAnyPermission($permissions) {
    $rights = getCurrentEmployeeRights();
    $accessLevel = getEmployeeAccessLevel();
    
    // المدير لديه جميع الصلاحيات
    if ($accessLevel === 'admin') {
        return true;
    }
    
    foreach ($permissions as $permission) {
        if (in_array($permission, $rights)) {
            return true;
        }
    }
    return false;
}

/**
 * التحقق من وجود جميع الحقوق المحددة
 * Vérifier si l'employé a tous les droits spécifiés
 */
function hasAllPermissions($permissions) {
    $rights = getCurrentEmployeeRights();
    $accessLevel = getEmployeeAccessLevel();
    
    // المدير لديه جميع الصلاحيات
    if ($accessLevel === 'admin') {
        return true;
    }
    
    foreach ($permissions as $permission) {
        if (!in_array($permission, $rights)) {
            return false;
        }
    }
    return true;
}

/**
 * التحقق من صلاحية الوصول إلى الصفحة
 * Vérifier la permission d'accès à une page
 */
function checkPageAccess($requiredPermissions = []) {
    $accessLevel = getEmployeeAccessLevel();
    
    // المدير لديه جميع الصلاحيات
    if ($accessLevel === 'admin') {
        return true;
    }
    
    // إذا لم تكن هناك صلاحيات مطلوبة، التحقق من مستوى الوصول الأساسي
    if (empty($requiredPermissions)) {
        return $accessLevel !== 'none';
    }
    
    return hasAnyPermission($requiredPermissions);
}

/**
 * الحصول على القوائم المتاحة حسب الحقوق
 * Obtenir les menus disponibles selon les droits
 */
function getAvailableMenus($lang = 'ar') {
    $menus = [];
    $rights = getCurrentEmployeeRights();
    $accessLevel = getEmployeeAccessLevel();
    
    // تعريف القوائم والحقوق المطلوبة مع قيود إضافية
    $menuDefinitions = [
        'books' => [
            'ar' => ['title' => 'الكتب', 'icon' => 'fas fa-book', 'url' => '../modules/items/list.php?type=book'],
            'fr' => ['title' => 'Livres', 'icon' => 'fas fa-book', 'url' => '../modules/items/list.php?type=book'],
            'permissions' => ['إدارة الكتب', 'Gestion des livres'],
            'min_level' => 'assistant'
        ],
        'magazines' => [
            'ar' => ['title' => 'المجلات', 'icon' => 'fas fa-newspaper', 'url' => '../modules/items/list.php?type=magazine'],
            'fr' => ['title' => 'Revues', 'icon' => 'fas fa-newspaper', 'url' => '../modules/items/list.php?type=magazine'],
            'permissions' => ['إدارة المجلات', 'Gestion des magazines'],
            'min_level' => 'assistant'
        ],
        'newspapers' => [
            'ar' => ['title' => 'الصحف', 'icon' => 'fas fa-newspaper', 'url' => '../modules/items/list.php?type=newspaper'],
            'fr' => ['title' => 'Journaux', 'icon' => 'fas fa-newspaper', 'url' => '../modules/items/list.php?type=newspaper'],
            'permissions' => ['إدارة الصحف', 'Gestion des journaux'],
            'min_level' => 'assistant'
        ],
        'acquisitions' => [
            'ar' => ['title' => 'التزويد', 'icon' => 'fas fa-truck', 'url' => '../modules/acquisitions/list.php'],
            'fr' => ['title' => 'Approvisionnements', 'icon' => 'fas fa-truck', 'url' => '../modules/acquisitions/list.php'],
            'permissions' => ['إدارة التزويد', 'Gestion des approvisionnements'],
            'min_level' => 'librarian'
        ],
        'borrowers' => [
            'ar' => ['title' => 'المستعيرين', 'icon' => 'fas fa-users', 'url' => '../modules/borrowers/list.php'],
            'fr' => ['title' => 'Emprunteurs', 'icon' => 'fas fa-users', 'url' => '../modules/borrowers/list.php'],
            'permissions' => ['إدارة المستعيرين', 'Gestion des emprunteurs'],
            'min_level' => 'reception'
        ],
        'loans' => [
            'ar' => ['title' => 'الإعارة', 'icon' => 'fas fa-handshake', 'url' => '../modules/loans/list.php'],
            'fr' => ['title' => 'Prêts', 'icon' => 'fas fa-handshake', 'url' => '../modules/loans/list.php'],
            'permissions' => ['إدارة الإعارة', 'Gestion des prêts'],
            'min_level' => 'reception'
        ],
        'administration' => [
            'ar' => ['title' => 'الإدارة', 'icon' => 'fas fa-users-cog', 'url' => '../modules/administration/list.php'],
            'fr' => ['title' => 'Administration', 'icon' => 'fas fa-users-cog', 'url' => '../modules/administration/list.php'],
            'permissions' => ['إدارة الموظفين', 'Gestion des employés'],
            'min_level' => 'admin'
        ]
    ];
    
    // التحقق من الحقوق وإضافة القوائم المتاحة
    foreach ($menuDefinitions as $key => $menu) {
        // التحقق من مستوى الوصول الأدنى
        if (canAccessLevel($menu['min_level'])) {
            if (hasAnyPermission($menu['permissions'])) {
                $menus[$key] = $menu[$lang];
            }
        }
    }
    
    return $menus;
}

/**
 * التحقق من إمكانية الوصول لمستوى معين
 * Vérifier si l'employé peut accéder à un niveau donné
 */
function canAccessLevel($requiredLevel) {
    $accessLevel = getEmployeeAccessLevel();
    
    $levelHierarchy = [
        'none' => 0,
        'limited' => 1,
        'technical' => 2,
        'reception' => 3,
        'assistant' => 4,
        'librarian' => 5,
        'admin' => 6
    ];
    
    $currentLevel = $levelHierarchy[$accessLevel] ?? 0;
    $requiredLevelValue = $levelHierarchy[$requiredLevel] ?? 0;
    
    return $currentLevel >= $requiredLevelValue;
}

/**
 * الحصول على الإجراءات المتاحة حسب الحقوق
 * Obtenir les actions disponibles selon les droits
 */
function getAvailableActions($lang = 'ar') {
    $actions = [];
    $rights = getCurrentEmployeeRights();
    $accessLevel = getEmployeeAccessLevel();
    
    // تعريف الإجراءات والحقوق المطلوبة مع قيود إضافية
    $actionDefinitions = [
        'add_book' => [
            'ar' => ['title' => 'إضافة كتاب', 'icon' => 'fas fa-plus', 'url' => '../modules/items/add_book.php'],
            'fr' => ['title' => 'Ajouter un livre', 'icon' => 'fas fa-plus', 'url' => '../modules/items/add_book.php'],
            'permissions' => ['إدارة الكتب', 'Gestion des livres'],
            'min_level' => 'librarian'
        ],
        'add_magazine' => [
            'ar' => ['title' => 'إضافة مجلة', 'icon' => 'fas fa-plus', 'url' => '../modules/items/add_magazine.php'],
            'fr' => ['title' => 'Ajouter une revue', 'icon' => 'fas fa-plus', 'url' => '../modules/items/add_magazine.php'],
            'permissions' => ['إدارة المجلات', 'Gestion des magazines'],
            'min_level' => 'librarian'
        ],
        'add_newspaper' => [
            'ar' => ['title' => 'إضافة صحيفة', 'icon' => 'fas fa-plus', 'url' => '../modules/items/add_newspaper.php'],
            'fr' => ['title' => 'Ajouter un journal', 'icon' => 'fas fa-plus', 'url' => '../modules/items/add_newspaper.php'],
            'permissions' => ['إدارة الصحف', 'Gestion des journaux'],
            'min_level' => 'librarian'
        ],
        'add_acquisition' => [
            'ar' => ['title' => 'إضافة تزويد', 'icon' => 'fas fa-plus', 'url' => '../modules/acquisitions/add.php'],
            'fr' => ['title' => 'Ajouter un approvisionnement', 'icon' => 'fas fa-plus', 'url' => '../modules/acquisitions/add.php'],
            'permissions' => ['إدارة التزويد', 'Gestion des approvisionnements'],
            'min_level' => 'librarian'
        ],
        'add_borrower' => [
            'ar' => ['title' => 'إضافة مستعير', 'icon' => 'fas fa-user-plus', 'url' => '../modules/borrowers/add.php'],
            'fr' => ['title' => 'Ajouter un emprunteur', 'icon' => 'fas fa-user-plus', 'url' => '../modules/borrowers/add.php'],
            'permissions' => ['إدارة المستعيرين', 'Gestion des emprunteurs'],
            'min_level' => 'reception'
        ],
        'new_loan' => [
            'ar' => ['title' => 'إعارة جديدة', 'icon' => 'fas fa-handshake', 'url' => '../modules/loans/borrow.php'],
            'fr' => ['title' => 'Nouvel emprunt', 'icon' => 'fas fa-handshake', 'url' => '../modules/loans/borrow.php'],
            'permissions' => ['إدارة الإعارة', 'Gestion des prêts'],
            'min_level' => 'reception'
        ],
        'add_employee' => [
            'ar' => ['title' => 'إضافة موظف', 'icon' => 'fas fa-user-plus', 'url' => '../modules/administration/add.php'],
            'fr' => ['title' => 'Ajouter un employé', 'icon' => 'fas fa-user-plus', 'url' => '../modules/administration/add.php'],
            'permissions' => ['إدارة الموظفين', 'Gestion des employés'],
            'min_level' => 'admin'
        ]
    ];
    
    // التحقق من الحقوق وإضافة الإجراءات المتاحة
    foreach ($actionDefinitions as $key => $action) {
        // التحقق من مستوى الوصول الأدنى
        if (canAccessLevel($action['min_level'])) {
            if (hasAnyPermission($action['permissions'])) {
                $actions[$key] = $action[$lang];
            }
        }
    }
    
    return $actions;
}

/**
 * الحصول على معلومات الموظف الحالي
 * Obtenir les informations de l'employé actuel
 */
function getCurrentEmployee() {
    global $pdo;
    
    if (!isset($_SESSION['uid'])) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * التحقق من صلاحية الحذف
 * Vérifier la permission de suppression
 */
function canDelete() {
    $accessLevel = getEmployeeAccessLevel();
    
    // فقط المدير يمكنه الحذف
    if ($accessLevel === 'admin') {
        return true;
    }
    
    // أمين المكتبة يمكنه حذف بعض العناصر
    if ($accessLevel === 'librarian') {
        return hasAnyPermission(['إدارة الكتب', 'إدارة المجلات', 'إدارة الصحف', 'Gestion des livres', 'Gestion des magazines', 'Gestion des journaux']);
    }
    
    return false;
}

/**
 * التحقق من صلاحية التعديل
 * Vérifier la permission de modification
 */
function canEdit() {
    $accessLevel = getEmployeeAccessLevel();
    
    // المدير وأمين المكتبة يمكنهما التعديل
    if ($accessLevel === 'admin' || $accessLevel === 'librarian') {
        return true;
    }
    
    // المساعد يمكنه تعديل بعض العناصر
    if ($accessLevel === 'assistant') {
        return hasAnyPermission(['إدارة الكتب', 'إدارة المجلات', 'إدارة الصحف', 'Gestion des livres', 'Gestion des magazines', 'Gestion des journaux']);
    }
    
    // موظف الاستقبال يمكنه تعديل المستعيرين والإعارات
    if ($accessLevel === 'reception') {
        return hasAnyPermission(['إدارة المستعيرين', 'إدارة الإعارة', 'Gestion des emprunteurs', 'Gestion des prêts']);
    }
    
    return false;
}

/**
 * التحقق من صلاحية الإضافة
 * Vérifier la permission d'ajout
 */
function canAdd() {
    $accessLevel = getEmployeeAccessLevel();
    
    // المدير وأمين المكتبة يمكنهما الإضافة
    if ($accessLevel === 'admin' || $accessLevel === 'librarian') {
        return true;
    }
    
    // المساعد يمكنه إضافة بعض العناصر
    if ($accessLevel === 'assistant') {
        return hasAnyPermission(['إدارة الكتب', 'إدارة المجلات', 'إدارة الصحف', 'Gestion des livres', 'Gestion des magazines', 'Gestion des journaux']);
    }
    
    // موظف الاستقبال يمكنه إضافة المستعيرين والإعارات
    if ($accessLevel === 'reception') {
        return hasAnyPermission(['إدارة المستعيرين', 'إدارة الإعارة', 'Gestion des emprunteurs', 'Gestion des prêts']);
    }
    
    return false;
}

/**
 * التحقق من صلاحية العرض فقط
 * Vérifier la permission de consultation uniquement
 */
function canView() {
    $accessLevel = getEmployeeAccessLevel();
    
    // جميع المستويات يمكنها العرض
    return $accessLevel !== 'none';
}

/**
 * الحصول على قائمة الصلاحيات المقيدة حسب المستوى
 * Obtenir la liste des permissions restreintes selon le niveau
 */
function getRestrictedPermissions($accessLevel) {
    $restrictions = [
        'limited' => [
            'ar' => ['عرض الكتب', 'عرض المجلات', 'عرض الصحف'],
            'fr' => ['Consultation des livres', 'Consultation des revues', 'Consultation des journaux']
        ],
        'technical' => [
            'ar' => ['عرض الكتب', 'عرض المجلات', 'عرض الصحف', 'إدارة النظام'],
            'fr' => ['Consultation des livres', 'Consultation des revues', 'Consultation des journaux', 'Administration du système']
        ],
        'reception' => [
            'ar' => ['إدارة المستعيرين', 'إدارة الإعارة', 'عرض الكتب', 'عرض المجلات', 'عرض الصحف'],
            'fr' => ['Gestion des emprunteurs', 'Gestion des prêts', 'Consultation des livres', 'Consultation des revues', 'Consultation des journaux']
        ],
        'assistant' => [
            'ar' => ['إدارة الكتب', 'إدارة المجلات', 'إدارة الصحف', 'إدارة المستعيرين', 'إدارة الإعارة'],
            'fr' => ['Gestion des livres', 'Gestion des magazines', 'Gestion des journaux', 'Gestion des emprunteurs', 'Gestion des prêts']
        ],
        'librarian' => [
            'ar' => ['إدارة الكتب', 'إدارة المجلات', 'إدارة الصحف', 'إدارة التزويد', 'إدارة المستعيرين', 'إدارة الإعارة'],
            'fr' => ['Gestion des livres', 'Gestion des magazines', 'Gestion des journaux', 'Gestion des approvisionnements', 'Gestion des emprunteurs', 'Gestion des prêts']
        ],
        'admin' => [
            'ar' => ['إدارة الكتب', 'إدارة المجلات', 'إدارة الصحف', 'إدارة التزويد', 'إدارة المستعيرين', 'إدارة الإعارة', 'إدارة الموظفين', 'إدارة النظام'],
            'fr' => ['Gestion des livres', 'Gestion des magazines', 'Gestion des journaux', 'Gestion des approvisionnements', 'Gestion des emprunteurs', 'Gestion des prêts', 'Gestion des employés', 'Administration du système']
        ]
    ];
    
    return $restrictions[$accessLevel] ?? [];
}
?> 