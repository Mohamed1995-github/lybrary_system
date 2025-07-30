# نظام إدارة الموظفين - Administration System

## نظرة عامة - Vue d'ensemble

نظام إدارة الموظفين يسمح بإدارة مسيري المكتبة مع إدارة حقوق الوصول والوظائف المختلفة.

Le système d'administration des employés permet de gérer les responsables de la bibliothèque avec la gestion des droits d'accès et des fonctions diverses.

## الميزات - Fonctionnalités

### 🔸 قائمة الموظفين - Liste des employés
- عرض جميع الموظفين المسجلين
- عرض معلومات كل موظف (الاسم، الرقم، الوظيفة، حقوق الوصول)
- إمكانية التعديل والحذف

### 🔸 إضافة موظف جديد - Ajouter un employé
- نموذج إضافة مع الحقول التالية:
  - **الاسم** (Nom)
  - **الرقم** (Numéro) - فريد لكل موظف
  - **الوظيفة** (Fonction) - قائمة منسدلة
  - **حقوق الوصول** (Droits d'accès) - خانات اختيار متعددة

### 🔸 تعديل الموظف - Modifier l'employé
- تعديل جميع معلومات الموظف
- التحقق من عدم تكرار الرقم

### 🔸 حذف الموظف - Supprimer l'employé
- تأكيد الحذف مع عرض معلومات الموظف
- حماية من الحذف العرضي

## الوظائف المتاحة - Fonctions disponibles

### العربية - Arabe
- مدير المكتبة
- أمين المكتبة
- مساعد أمين المكتبة
- موظف استقبال
- موظف تقني

### Français
- Directeur de bibliothèque
- Bibliothécaire
- Assistant bibliothécaire
- Employé d'accueil
- Employé technique

## حقوق الوصول - Droits d'accès

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

## الملفات - Fichiers

### PHP Files
- `modules/administration/list.php` - قائمة الموظفين
- `modules/administration/add.php` - إضافة موظف جديد
- `modules/administration/edit.php` - تعديل الموظف
- `modules/administration/delete.php` - حذف الموظف
- `create_employees_table.php` - إنشاء جدول قاعدة البيانات

### Database
- جدول `employees` يحتوي على:
  - `id` - المعرف الفريد
  - `lang` - اللغة (ar/fr)
  - `name` - اسم الموظف
  - `number` - رقم الموظف (فريد)
  - `function` - الوظيفة
  - `access_rights` - حقوق الوصول
  - `created_at` - تاريخ الإنشاء
  - `updated_at` - تاريخ التحديث

## التثبيت - Installation

1. تشغيل سكريبت إنشاء الجدول:
```bash
php create_employees_table.php
```

2. الوصول إلى النظام عبر لوحة التحكم:
   - اختر قسم "الإدارة" / "Administration"
   - ابدأ بإضافة موظفين جدد

## الأمان - Sécurité

- التحقق من تسجيل الدخول في جميع الصفحات
- التحقق من صحة البيانات المدخلة
- منع تكرار أرقام الموظفين
- تأكيد الحذف لمنع الحذف العرضي

## الترجمة - Traduction

النظام يدعم اللغتين العربية والفرنسية مع:
- واجهة مستخدم متعددة اللغات
- حفظ البيانات حسب اللغة
- عرض النصوص باللغة المختارة

## الاستخدام - Utilisation

1. **إضافة موظف جديد**:
   - املأ النموذج بالمعلومات المطلوبة
   - اختر الوظيفة من القائمة المنسدلة
   - حدد حقوق الوصول المطلوبة
   - احفظ البيانات

2. **تعديل موظف**:
   - اختر الموظف من القائمة
   - عدل المعلومات المطلوبة
   - احفظ التغييرات

3. **حذف موظف**:
   - اختر الموظف من القائمة
   - تأكد من المعلومات
   - أكد الحذف

## الدعم - Support

للحصول على المساعدة أو الإبلاغ عن مشاكل، يرجى التواصل مع فريق التطوير.

Pour obtenir de l'aide ou signaler des problèmes, veuillez contacter l'équipe de développement. 