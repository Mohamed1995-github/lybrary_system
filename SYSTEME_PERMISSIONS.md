# 🔐 Système de Contrôle d'Accès par Permissions - Module Items

## Vue d'ensemble

Le système de contrôle d'accès par permissions pour le module Items permet un contrôle granulaire de l'accès aux différentes fonctionnalités selon les droits spécifiques des utilisateurs.

## 🛡️ Fonctionnalités de Sécurité

### 1. **Vérification d'Authentification**
- ✅ Contrôle de session utilisateur obligatoire
- ✅ Redirection automatique vers la page de connexion
- ✅ Protection contre l'accès direct aux fichiers

### 2. **Système de Permissions Granulaire**
- ✅ Vérification des droits spécifiques par type d'item
- ✅ Contrôle des niveaux d'accès (admin, librarian, assistant, etc.)
- ✅ Permissions différenciées pour l'ajout, modification et suppression

### 3. **Interface Adaptative**
- ✅ Affichage conditionnel des boutons selon les permissions
- ✅ Désactivation visuelle des actions non autorisées
- ✅ Indicateur de statut des permissions

### 4. **Logging de Sécurité**
- ✅ Enregistrement des tentatives d'accès non autorisées
- ✅ Traçabilité des actions autorisées
- ✅ Logs détaillés avec horodatage et adresse IP

## 🔑 Permissions par Type d'Item

### **Livres (Books)**
```php
$requiredPermissions = [
    'إدارة الكتب',           // Gestion des livres
    'Gestion des livres',    // Gestion des livres (FR)
    'عرض الكتب',            // Consultation des livres
    'Consultation des livres' // Consultation des livres (FR)
];
```

### **Magazines**
```php
$requiredPermissions = [
    'إدارة المجلات',         // Gestion des magazines
    'Gestion des magazines', // Gestion des magazines (FR)
    'عرض المجلات',          // Consultation des magazines
    'Consultation des revues' // Consultation des revues (FR)
];
```

### **Journaux (Newspapers)**
```php
$requiredPermissions = [
    'إدارة الصحف',           // Gestion des journaux
    'Gestion des journaux',  // Gestion des journaux (FR)
    'عرض الصحف',            // Consultation des journaux
    'Consultation des journaux' // Consultation des journaux (FR)
];
```

## 🎯 Niveaux d'Accès

### **Administrateur (admin)**
- ✅ Accès complet à tous les types d'items
- ✅ Ajout, modification, suppression
- ✅ Contournement automatique des vérifications de permissions

### **Bibliothécaire (librarian)**
- ✅ Ajout et modification d'items (selon permissions)
- ✅ Gestion des livres, magazines, journaux
- ❌ Suppression (réservée aux admins)

### **Assistant (assistant)**
- ✅ Consultation des items (selon permissions)
- ✅ Modification limitée (selon permissions)
- ❌ Ajout et suppression

### **Réception (reception)**
- ✅ Consultation uniquement (selon permissions)
- ❌ Aucune modification

## 🔍 Fonctions de Contrôle

### **Vérification des Permissions**
```php
// Vérifier si l'utilisateur a au moins une des permissions requises
if (!hasAnyPermission($requiredPermissions) && $accessLevel !== 'admin') {
    // Accès refusé
    header('Location: ../../public/error.php?code=403&lang=' . $lang);
    exit;
}
```

### **Contrôle des Actions**
```php
// Déterminer les actions disponibles
$canAdd = canAdd() && hasAnyPermission($requiredPermissions);
$canEdit = canEdit() && hasAnyPermission($requiredPermissions);
$canDelete = canDelete() && hasAnyPermission($requiredPermissions);
$canView = canView();
```

### **Affichage Conditionnel**
```php
<?php if ($canAdd): ?>
    <a href="..." class="action-btn btn-primary">
        <i class="fas fa-plus"></i>
        Ajouter
    </a>
<?php else: ?>
    <span class="action-btn btn-disabled" title="Permission requise">
        <i class="fas fa-plus"></i>
        Ajouter
    </span>
<?php endif; ?>
```

## 📊 Interface Utilisateur

### **Barre d'Information d'Accès**
- Affichage du niveau d'accès actuel
- Liste des permissions disponibles
- **Indicateur de statut des permissions** (accordée/refusée)

### **Boutons d'Action**
- **Actifs** : Actions autorisées (couleur normale)
- **Désactivés** : Actions non autorisées (grisées avec tooltip)

### **Tableau des Items**
- Colonne "Actions" masquée si aucune action autorisée
- Boutons d'édition/suppression conditionnels
- Messages d'aide sur les permissions

### **Indicateur de Statut**
```html
<div class="permission-status permission-granted">
    ✅ Permission d'accès accordée
</div>

<div class="permission-status permission-denied">
    ❌ Permission d'accès refusée
</div>
```

## 🔍 Logging et Surveillance

### **Logs d'Accès Autorisés**
```php
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} ({$accessLevel}) - AUTHORIZED_ACCESS to items list (type: {$type}) - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../../logs/access.log', $log_entry, FILE_APPEND | LOCK_EX);
```

### **Logs de Tentatives Non Autorisées**
```php
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - UNAUTHORIZED_ACCESS to items list (type: {$type}) - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../../logs/security.log', $log_entry, FILE_APPEND | LOCK_EX);
```

## 🚀 Utilisation

### **Accès via le Routeur Sécurisé**
```
http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book
```

### **Paramètres d'URL**
- `module=items` : Module des items
- `action=list` : Action d'affichage
- `lang=ar|fr` : Langue d'interface
- `type=book|magazine|newspaper` : Type d'item

## 🛠️ Configuration

### **Ajout de Nouvelles Permissions**
1. Modifier le fichier `includes/permissions.php`
2. Ajouter les nouvelles permissions dans la base de données
3. Mettre à jour les vérifications dans le code

### **Modification des Niveaux d'Accès**
1. Ajuster les fonctions `canAdd()`, `canEdit()`, `canDelete()`
2. Mettre à jour la hiérarchie des rôles
3. Tester les permissions

## 📝 Bonnes Pratiques

### **Sécurité**
- ✅ Toujours vérifier les permissions avant d'afficher du contenu
- ✅ Utiliser des tokens CSRF pour les formulaires
- ✅ Valider et nettoyer toutes les entrées utilisateur

### **Performance**
- ✅ Mettre en cache les permissions utilisateur
- ✅ Optimiser les requêtes de vérification
- ✅ Limiter les appels de base de données

### **Maintenance**
- ✅ Documenter les changements de permissions
- ✅ Maintenir les logs de sécurité
- ✅ Tester régulièrement les contrôles d'accès

## 🔧 Dépannage

### **Problèmes Courants**

1. **Accès Refusé (403)**
   - Vérifier les permissions de l'utilisateur
   - Contrôler le niveau d'accès
   - Consulter les logs de sécurité

2. **Boutons Désactivés**
   - Vérifier les droits spécifiques
   - Contrôler les permissions du type d'item
   - Tester avec un compte admin

3. **Erreurs de Logging**
   - Vérifier les permissions d'écriture des fichiers
   - Contrôler l'existence des dossiers de logs
   - Tester la création de fichiers

### **Commandes de Test**
```bash
# Vérifier les logs d'accès
tail -f logs/access.log

# Vérifier les logs de sécurité
tail -f logs/security.log

# Tester les permissions
php test_permissions_system.php
```

## 🎯 Avantages du Système

### **Sécurité**
- ✅ Contrôle granulaire des accès
- ✅ Protection contre les accès non autorisés
- ✅ Traçabilité complète des actions

### **Flexibilité**
- ✅ Permissions spécifiques par type d'item
- ✅ Interface adaptative selon les droits
- ✅ Gestion fine des niveaux d'accès

### **Maintenance**
- ✅ Code modulaire et réutilisable
- ✅ Logs détaillés pour le débogage
- ✅ Configuration centralisée

## 📚 Ressources Additionnelles

- `CONTROLE_ACCES_ITEMS.md` : Guide détaillé du contrôle d'accès
- `includes/permissions.php` : Système de permissions
- `includes/module_security.php` : Classe de sécurité
- `public/router.php` : Routeur sécurisé
- `test_permissions_system.php` : Script de test des permissions

## 🔄 Comparaison avec l'Accès Complet

| Fonctionnalité | Accès Complet | Permissions |
|----------------|---------------|-------------|
| **Sécurité** | Authentification uniquement | Authentification + Permissions |
| **Contrôle** | Aucun | Granulaire |
| **Interface** | Tous les boutons actifs | Boutons conditionnels |
| **Flexibilité** | Limitée | Élevée |
| **Maintenance** | Simple | Modérée |

Le système de permissions offre un contrôle d'accès plus sécurisé et flexible, adapté aux environnements nécessitant une gestion fine des droits utilisateur. 