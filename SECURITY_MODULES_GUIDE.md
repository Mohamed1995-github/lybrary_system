# Guide de Sécurisation des Modules - Système de Bibliothèque

## 🔒 Vue d'ensemble de la Sécurisation

Ce guide explique comment sécuriser l'accès aux modules du système de bibliothèque et les bonnes pratiques à suivre.

## 🛡️ Système de Sécurisation Implémenté

### 1. **Routeur Sécurisé** (`public/router.php`)
- **Fonction** : Point d'entrée unique pour tous les modules
- **Sécurité** : Vérification des permissions, authentification, logging
- **URLs** : `http://localhost/library_system/public/router.php?module=acquisitions&action=add`

### 2. **Protection .htaccess** (`modules/.htaccess`)
- **Fonction** : Bloque l'accès direct aux fichiers PHP des modules
- **Sécurité** : Redirection vers la page d'erreur 403

### 3. **Classe de Sécurité** (`includes/module_security.php`)
- **Fonction** : Gestion centralisée de la sécurité
- **Fonctionnalités** : CSRF, validation, permissions, logging

### 4. **Page d'Erreur Personnalisée** (`public/error.php`)
- **Fonction** : Gestion sécurisée des erreurs 403/404
- **Sécurité** : Pas d'informations sensibles exposées

## 🔐 Comment Utiliser le Système de Sécurisation

### Accès aux Modules

**❌ Accès direct (bloqué) :**
```
http://localhost/library_system/modules/acquisitions/add.php
```

**✅ Accès sécurisé (recommandé) :**
```
http://localhost/library_system/public/router.php?module=acquisitions&action=add
```

### Structure d'un Module Sécurisé

```php
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/module_security.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) {
    header('Location: ../../public/login.php');
    exit;
}

// Initialiser la sécurité
$security = new ModuleSecurity($pdo, $_SESSION['uid'], $_SESSION['role'] ?? 'guest');

// Vérifier les permissions
if (!$security->checkModuleAccess('module_name', 'action_name')) {
    $security->logSecurityEvent('UNAUTHORIZED_ACCESS', 'module_name/action_name');
    header('Location: ../../public/error.php?code=403');
    exit;
}

// Votre code de module ici...
```

## 🎯 Système de Permissions

### Rôles Définis
- **admin** : Accès complet à tous les modules
- **librarian** : Accès aux modules de gestion courante
- **assistant** : Accès en lecture seule à la plupart des modules

### Matrice des Permissions

| Module | Action | Admin | Librarian | Assistant |
|--------|--------|-------|-----------|-----------|
| acquisitions | add | ✅ | ✅ | ❌ |
| acquisitions | list | ✅ | ✅ | ✅ |
| acquisitions | delete | ✅ | ❌ | ❌ |
| administration | all | ✅ | ❌ | ❌ |
| items | add_book | ✅ | ✅ | ❌ |
| items | list | ✅ | ✅ | ✅ |

## 🛡️ Protection CSRF

### Génération du Token
```php
$csrf_token = $security->generateCSRFToken();
```

### Dans le Formulaire
```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
    <!-- autres champs -->
</form>
```

### Vérification
```php
if (!$security->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $security->logSecurityEvent('CSRF_ATTEMPT', 'module/action');
    $errors[] = 'Erreur de vérification de sécurité';
}
```

## 🔍 Validation des Données

### Nettoyage des Entrées
```php
$clean_data = $security->sanitizeInput($_POST['field_name']);
```

### Validation Spécifique
```php
// Email
if (!$security->validateEmail($email)) {
    $errors[] = 'Email invalide';
}

// Téléphone
if (!$security->validatePhone($phone)) {
    $errors[] = 'Numéro de téléphone invalide';
}

// ID numérique
if (!$security->validateNumericId($id)) {
    $errors[] = 'ID invalide';
}
```

## 📝 Logging de Sécurité

### Événements Loggés
- Tentatives d'accès non autorisées
- Tentatives CSRF
- Erreurs de base de données
- Actions sensibles (ajout, modification, suppression)

### Format du Log
```
2025-01-23 15:30:45 - User: 123 (admin) - Event: ACQUISITION_ADDED - Details: ID: 456 - IP: 192.168.1.100
```

## 🚀 Migration des Modules Existants

### Étape 1 : Créer la Version Sécurisée
1. Copier le module existant
2. Ajouter les vérifications de sécurité
3. Implémenter la protection CSRF
4. Ajouter la validation des données

### Étape 2 : Mettre à Jour les Liens
Remplacer tous les liens directs par des liens via le routeur :

**Avant :**
```html
<a href="../../modules/acquisitions/add.php">Ajouter</a>
```

**Après :**
```html
<a href="../../public/router.php?module=acquisitions&action=add">Ajouter</a>
```

### Étape 3 : Tester
1. Vérifier l'accès avec différents rôles
2. Tester la protection CSRF
3. Valider les formulaires
4. Vérifier les logs de sécurité

## 🔧 Configuration Avancée

### Ajouter un Nouveau Module
1. Ajouter les permissions dans `ModuleSecurity::checkModuleAccess()`
2. Créer le fichier du module dans `modules/`
3. Ajouter les règles dans `public/router.php`

### Personnaliser les Messages d'Erreur
Modifier `public/error.php` pour ajouter de nouveaux codes d'erreur.

### Configurer le Logging
Les logs sont stockés dans :
- `logs/access.log` : Accès aux modules
- `logs/security.log` : Événements de sécurité

## ⚠️ Bonnes Pratiques

### ✅ À Faire
- Toujours utiliser le routeur pour accéder aux modules
- Implémenter la protection CSRF sur tous les formulaires
- Valider et nettoyer toutes les données d'entrée
- Logger les actions sensibles
- Vérifier les permissions avant chaque action

### ❌ À Éviter
- Accès direct aux fichiers des modules
- Stockage de données sensibles dans les logs
- Exposition d'informations d'erreur détaillées
- Confiance aveugle dans les données utilisateur
- Omission de la vérification des permissions

## 🧪 Tests de Sécurité

### Test d'Accès Non Autorisé
```
http://localhost/library_system/modules/administration/add.php
```
**Résultat attendu :** Erreur 403

### Test CSRF
Soumettre un formulaire sans token CSRF
**Résultat attendu :** Erreur de validation

### Test de Permissions
Se connecter avec un rôle assistant et essayer d'accéder à un module admin
**Résultat attendu :** Redirection vers la page d'erreur

## 📞 Support

Pour toute question sur la sécurisation des modules, consultez :
1. Ce guide
2. Les commentaires dans le code
3. Les logs de sécurité pour diagnostiquer les problèmes

---

**Note :** Ce système de sécurisation est conçu pour être évolutif. N'hésitez pas à l'adapter selon vos besoins spécifiques tout en maintenant les principes de sécurité fondamentaux. 