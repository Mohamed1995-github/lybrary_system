# Guide de Réparation Rapide - Système de Bibliothèque

## Problèmes Mentionnés et Solutions

### 1. Erreur "Not Found"
**Problème:** La page n'existe pas sur le serveur

**Solutions:**
1. Vérifiez qu'Apache est démarré dans XAMPP
2. Vérifiez que mod_rewrite est activé
3. Vérifiez les chemins corrects

**Fichiers de diagnostic:**
- `diagnostic.php` - Diagnostic complet du système
- `fix_not_found.php` - Réparation rapide du problème "Not Found"
- `test_simple.php` - Test simple de PHP

### 2. Les boutons ne fonctionnent pas
**Problème:** Les boutons ne répondent pas au clic

**Solutions:**
1. Vérifiez que JavaScript est activé dans le navigateur
2. Vérifiez l'existence des fichiers CSS et JS
3. Consultez la console du navigateur pour les erreurs

**Fichiers de diagnostic:**
- `fix_buttons.php` - Réparation des problèmes de boutons
- `test_buttons_page.php` - Page de test des boutons
- `assets/js/test_buttons.js` - JavaScript de test

### 3. Réparation complète
**Fichier de réparation complète:**
- `complete_fix.php` - Réparation de tous les problèmes
- `test_complete.php` - Test complet du système

## Étapes de Réparation Rapide

### Étape 1: Diagnostic du problème
```bash
# Naviguez vers le dossier du système
cd C:\xampp1\htdocs\library_system\public

# Ouvrez le navigateur et allez à:
http://localhost/library_system/public/diagnostic.php
```

### Étape 2: Réparation complète
```bash
# Ouvrez le navigateur et allez à:
http://localhost/library_system/public/complete_fix.php
```

### Étape 3: Test du système
```bash
# Test complet:
http://localhost/library_system/public/test_complete.php

# Test des boutons:
http://localhost/library_system/public/test_buttons_page.php

# Test simple:
http://localhost/library_system/public/test_simple.php
```

## Vérification des Erreurs

### 1. Vérification de la console du navigateur
1. Appuyez sur F12 pour ouvrir les outils de développement
2. Allez à l'onglet "Console"
3. Recherchez les erreurs en rouge

### 2. Vérification des logs PHP
```bash
# Emplacement des logs PHP dans XAMPP:
C:\xampp1\php\logs\php_error_log
```

### 3. Vérification des logs Apache
```bash
# Emplacement des logs Apache dans XAMPP:
C:\xampp1\apache\logs\error.log
```

## Problèmes Courants et Solutions

### Problème 1: Apache ne fonctionne pas
**Solution:**
1. Ouvrez XAMPP Control Panel
2. Cliquez sur "Start" à côté d'Apache
3. Vérifiez qu'aucun autre service n'utilise le port 80

### Problème 2: La base de données ne se connecte pas
**Solution:**
1. Vérifiez que MySQL est démarré dans XAMPP
2. Vérifiez les paramètres de connexion dans `config/database.php`
3. Vérifiez l'existence de la base de données

### Problème 3: Les sessions ne fonctionnent pas
**Solution:**
1. Vérifiez les paramètres PHP pour les sessions
2. Vérifiez l'existence d'un dossier temp accessible en écriture
3. Vérifiez les paramètres des cookies

### Problème 4: Les fichiers n'existent pas
**Solution:**
1. Vérifiez l'existence de tous les fichiers requis
2. Vérifiez les permissions correctes
3. Vérifiez les chemins des fichiers

## Liens de Test

### Tests de base:
- [Diagnostic du système](diagnostic.php)
- [Réparation complète](complete_fix.php)
- [Test des boutons](test_buttons_page.php)
- [Test simple](test_simple.php)

### Tests du système:
- [Tableau de bord](dashboard.php)
- [Page de connexion](login.php)
- [Gestion des employés](router.php?module=administration&action=list)
- [Liste des items](router.php?module=items&action=list)
- [Statistiques des items](router.php?module=items&action=statistics)
- [Rapports d'administration](router.php?module=administration&action=reports)

## Conseils Supplémentaires

1. **Sauvegardez** avant d'effectuer des modifications
2. **Testez dans un environnement séparé** d'abord
3. **Consultez les logs** régulièrement pour détecter les problèmes
4. **Maintenez à jour** XAMPP et PHP

## Support

Si les problèmes persistent:
1. Consultez les logs d'erreur
2. Vérifiez les paramètres du serveur
3. Testez dans un navigateur différent
4. Vérifiez les paramètres de sécurité
