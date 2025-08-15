# 🚀 GUIDE D'ACCÈS - SYSTÈME DE GESTION DE BIBLIOTHÈQUE

## 🔍 Problème Identifié

Vous avez essayé d'accéder à `http://localhost/public/dashboard.php` et obtenu une erreur 404. Le problème est que l'URL n'est pas correcte selon la structure de votre projet.

## ✅ Solutions d'Accès

### 1. **Accès Direct (Recommandé)**

Utilisez ces URLs pour accéder au système :

```
✅ http://localhost/library_system/public/login.php
✅ http://localhost/library_system/public/dashboard.php
✅ http://localhost/library_system/public/access.php (diagnostic)
```

### 2. **Accès via le Routeur**

```
✅ http://localhost/library_system/public/index.php
✅ http://localhost/library_system/public/router.php
```

### 3. **Accès aux Modules**

```
✅ http://localhost/library_system/public/modules/items/list.php
✅ http://localhost/library_system/public/modules/items/add_book.php
✅ http://localhost/library_system/public/modules/items/add_magazine.php
```

## 🛠️ Fichiers de Diagnostic Créés

### 1. **Fichier de Diagnostic** (`public/access.php`)
- Vérifie les prérequis système
- Teste la connexion à la base de données
- Affiche l'état des fichiers requis
- Fournit des liens directs vers les pages principales

### 2. **Fichier de Redirection** (`public/redirect.php`)
- Redirige automatiquement vers la page appropriée
- Gère les sessions utilisateur
- Adapte la destination selon le rôle

### 3. **Configuration .htaccess** (`public/.htaccess`)
- Gère les redirections URL
- Protège les fichiers sensibles
- Optimise les performances

## 📋 Étapes de Résolution

### Étape 1 : Vérifier l'Accès de Base
1. Ouvrez votre navigateur
2. Accédez à : `http://localhost/library_system/public/access.php`
3. Vérifiez que tous les prérequis sont satisfaits

### Étape 2 : Tester la Connexion
1. Accédez à : `http://localhost/library_system/public/login.php`
2. Utilisez les identifiants de test (voir ci-dessous)
3. Vérifiez que vous pouvez vous connecter

### Étape 3 : Accéder au Dashboard
1. Après connexion, vous serez redirigé vers le dashboard
2. Ou accédez directement : `http://localhost/library_system/public/dashboard.php`

## 🔐 Identifiants de Test

### Compte Administrateur
```
Username: admin
Password: admin123
```

### Compte Bibliothécaire
```
Username: librarian
Password: lib123
```

### Compte Employé
```
Username: employee
Password: emp123
```

## 🚨 Problèmes Courants et Solutions

### Problème 1 : Erreur 404
**Cause** : URL incorrecte
**Solution** : Utilisez les URLs correctes listées ci-dessus

### Problème 2 : Erreur de Base de Données
**Cause** : Configuration DB incorrecte
**Solution** : 
1. Vérifiez `config/database.php`
2. Assurez-vous que MySQL est démarré
3. Vérifiez les identifiants de connexion

### Problème 3 : Erreur de Session
**Cause** : Problème de permissions ou configuration
**Solution** :
1. Vérifiez les permissions des dossiers
2. Assurez-vous que PHP sessions est activé
3. Vérifiez la configuration Apache

### Problème 4 : Assets non chargés
**Cause** : Chemins incorrects pour CSS/JS
**Solution** :
1. Vérifiez la structure des dossiers `assets/`
2. Assurez-vous que les fichiers existent
3. Vérifiez les permissions de lecture

## 📁 Structure des URLs Correctes

```
📁 Racine du projet : /c:/xampp1/htdocs/library_system/
├── 📁 public/ (Point d'entrée web)
│   ├── 🔗 login.php
│   ├── 🔗 dashboard.php
│   ├── 🔗 access.php (diagnostic)
│   ├── 🔗 index.php (routeur)
│   └── 📁 assets/
├── 📁 modules/
│   └── 📁 items/
└── 📁 config/
```

## 🎯 URLs d'Accès Recommandées

### **Pour Commencer**
```
🔗 http://localhost/library_system/public/access.php
```
*Page de diagnostic pour vérifier l'état du système*

### **Pour Se Connecter**
```
🔗 http://localhost/library_system/public/login.php
```
*Page de connexion principale*

### **Pour le Dashboard**
```
🔗 http://localhost/library_system/public/dashboard.php
```
*Tableau de bord après connexion*

### **Pour les Employés**
```
🔗 http://localhost/library_system/public/employee_login.php
```
*Page de connexion pour les employés*

## 🔧 Configuration Apache

Assurez-vous que votre configuration Apache permet l'accès au dossier :

```apache
<Directory "C:/xampp1/htdocs/library_system/public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

## 📊 Monitoring et Logs

### Fichiers de Logs
- `logs/access.log` : Accès au système
- `logs/security.log` : Tentatives de sécurité
- `logs/php_errors.log` : Erreurs PHP

### Vérification des Logs
```bash
# Vérifier les logs d'accès
tail -f logs/access.log

# Vérifier les erreurs PHP
tail -f logs/php_errors.log
```

## 🚀 Commandes de Test

### Test de Connexion
```bash
# Tester l'accès au fichier de diagnostic
curl http://localhost/library_system/public/access.php

# Tester la page de connexion
curl http://localhost/library_system/public/login.php
```

### Test de Base de Données
```bash
# Se connecter à MySQL
mysql -u root -p

# Vérifier la base de données
USE library_db;
SHOW TABLES;
```

## 📞 Support et Dépannage

### Si le problème persiste :

1. **Vérifiez les logs Apache** :
   - Ouvrez `C:\xampp1\apache\logs\error.log`
   - Recherchez les erreurs liées à votre projet

2. **Vérifiez les logs PHP** :
   - Consultez `logs/php_errors.log`
   - Recherchez les erreurs de configuration

3. **Testez la configuration** :
   - Accédez à `http://localhost/library_system/public/access.php`
   - Vérifiez tous les points de diagnostic

4. **Vérifiez les permissions** :
   - Assurez-vous que Apache peut lire les fichiers
   - Vérifiez les permissions des dossiers

## ✅ Checklist de Vérification

- [ ] XAMPP est démarré (Apache + MySQL)
- [ ] URL correcte utilisée
- [ ] Fichiers de configuration présents
- [ ] Base de données accessible
- [ ] Permissions correctes
- [ ] Extensions PHP activées
- [ ] Session PHP fonctionnelle

## 🎯 Conclusion

Le problème principal était l'URL incorrecte. Utilisez maintenant les URLs correctes listées dans ce guide pour accéder à votre système de gestion de bibliothèque.

**URL Principale Recommandée :**
```
http://localhost/library_system/public/access.php
```

Cette page vous permettra de diagnostiquer l'état du système et d'accéder à toutes les fonctionnalités.

---

**Document créé le :** <?= date('d/m/Y') ?>  
**Version :** 1.0  
**Statut :** Actif ✅ 