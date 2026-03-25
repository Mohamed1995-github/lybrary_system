<?php
function get_lang(): string {
    $lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'ar');
    $lang = in_array($lang, ['ar','fr'], true) ? $lang : 'ar';
    $_SESSION['lang'] = $lang;
    return $lang;
}

function translations(string $lang): array {
    $tr = [
        'ar' => [
            'title' => 'لوحة التحكم',
            'library_system' => ' النظام الإلكتروني للمكتبة',
            'hello' => 'مرحباً',
            'logout' => 'تسجيل الخروج',
            'welcome' => 'مرحباً بك في النظام',
            'collections' => 'قسم المجموعات والوثائق',
            'it' => 'قسم المعلوماتية',
            'employees' => 'إدارة الموظفين',
            'change_lang' => 'تغيير اللغة',
        ],
        'fr' => [
            'title' => 'Tableau de bord',
            'library_system' => 'SEBIL–ENAJM : Système Électronique de la Bibliothèque',
            'hello' => 'Bonjour',
            'logout' => 'Déconnexion',
            'welcome' => 'Bienvenue dans le système',
            'collections' => 'Section Collections et Documents',
            'it' => 'Section Informatique',
            'employees' => 'Gestion des Employés',
            'change_lang' => 'Changer de Langue',
        ],
    ];
    return $tr[$lang];
}
