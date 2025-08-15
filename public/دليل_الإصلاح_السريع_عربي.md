# دليل الإصلاح السريع - نظام المكتبة

## المشاكل المذكورة وحلولها

### 1. خطأ "Not Found"
**المشكلة:** الصفحة لا توجد على الخادم

**الحلول:**
1. تأكد من تشغيل Apache في XAMPP
2. تأكد من تفعيل mod_rewrite
3. تحقق من المسارات الصحيحة

**ملفات التشخيص:**
- `diagnostic.php` - تشخيص شامل للنظام
- `fix_not_found.php` - إصلاح سريع لمشكلة "Not Found"
- `test_simple.php` - اختبار بسيط لـ PHP

### 2. الأزرار لا تعمل
**المشكلة:** الأزرار لا تستجيب للنقر

**الحلول:**
1. تأكد من تفعيل JavaScript في المتصفح
2. تحقق من وجود ملفات CSS و JS
3. راجع وحدة تحكم المتصفح للأخطاء

**ملفات التشخيص:**
- `fix_buttons.php` - إصلاح مشاكل الأزرار
- `test_buttons_page.php` - صفحة اختبار للأزرار
- `assets/js/test_buttons.js` - JavaScript للاختبار

### 3. إصلاح شامل
**ملف الإصلاح الشامل:**
- `complete_fix.php` - إصلاح جميع المشاكل
- `test_complete.php` - اختبار شامل للنظام

## خطوات الإصلاح السريع

### الخطوة 1: تشخيص المشكلة
```bash
# انتقل إلى مجلد النظام
cd C:\xampp1\htdocs\library_system\public

# افتح المتصفح واذهب إلى:
http://localhost/library_system/public/diagnostic.php
```

### الخطوة 2: إصلاح شامل
```bash
# افتح المتصفح واذهب إلى:
http://localhost/library_system/public/complete_fix.php
```

### الخطوة 3: اختبار النظام
```bash
# اختبار شامل:
http://localhost/library_system/public/test_complete.php

# اختبار الأزرار:
http://localhost/library_system/public/test_buttons_page.php

# اختبار بسيط:
http://localhost/library_system/public/test_simple.php
```

## فحص الأخطاء

### 1. فحص وحدة تحكم المتصفح
1. اضغط F12 لفتح أدوات المطور
2. انتقل إلى تبويب "Console"
3. ابحث عن الأخطاء باللون الأحمر

### 2. فحص سجلات PHP
```bash
# موقع سجلات PHP في XAMPP:
C:\xampp1\php\logs\php_error_log
```

### 3. فحص سجلات Apache
```bash
# موقع سجلات Apache في XAMPP:
C:\xampp1\apache\logs\error.log
```

## المشاكل الشائعة وحلولها

### مشكلة 1: Apache لا يعمل
**الحل:**
1. افتح XAMPP Control Panel
2. اضغط "Start" بجانب Apache
3. تأكد من عدم استخدام المنفذ 80 من قبل خدمة أخرى

### مشكلة 2: قاعدة البيانات لا تتصل
**الحل:**
1. تأكد من تشغيل MySQL في XAMPP
2. تحقق من إعدادات الاتصال في `config/database.php`
3. تأكد من وجود قاعدة البيانات

### مشكلة 3: الجلسات لا تعمل
**الحل:**
1. تحقق من إعدادات PHP للجلسات
2. تأكد من وجود مجلد temp قابل للكتابة
3. تحقق من إعدادات الكوكيز

### مشكلة 4: الملفات لا توجد
**الحل:**
1. تحقق من وجود جميع الملفات المطلوبة
2. تأكد من الصلاحيات الصحيحة
3. تحقق من مسارات الملفات

## روابط الاختبار

### اختبارات أساسية:
- [تشخيص النظام](diagnostic.php)
- [إصلاح شامل](complete_fix.php)
- [اختبار الأزرار](test_buttons_page.php)
- [اختبار بسيط](test_simple.php)

### اختبارات النظام:
- [لوحة التحكم](dashboard.php)
- [صفحة تسجيل الدخول](login.php)
- [إدارة الموظفين](router.php?module=administration&action=list)
- [قائمة العناصر](router.php?module=items&action=list)
- [إحصائيات العناصر](router.php?module=items&action=statistics)
- [تقارير الإدارة](router.php?module=administration&action=reports)

## نصائح إضافية

1. **احتفظ بنسخة احتياطية** قبل إجراء أي تغييرات
2. **اختبر في بيئة منفصلة** أولاً
3. **راجع السجلات** بانتظام للكشف عن المشاكل
4. **حافظ على تحديث** XAMPP و PHP

## الدعم

إذا استمرت المشاكل:
1. راجع سجلات الأخطاء
2. تحقق من إعدادات الخادم
3. اختبر في متصفح مختلف
4. تحقق من إعدادات الأمان
