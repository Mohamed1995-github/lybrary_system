# 🛡️ Guide Utilisateur - Système de Sécurité

## 📋 Vue d'ensemble

Le système de bibliothèque a été sécurisé pour protéger l'accès aux modules tout en permettant aux utilisateurs autorisés d'y accéder de manière sécurisée.

## 🔐 Comment ça fonctionne

### ❌ Accès bloqué
- **Avant** : `http://localhost/library_system/modules/items/list.php?lang=ar&type=book`
- **Maintenant** : Cette URL affiche une page d'erreur 403 (Accès interdit)

### ✅ Accès autorisé
- **Nouveau** : `http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book`

## 🚀 Comment accéder aux modules

### 1. Via le tableau de bord
Connectez-vous à votre compte et utilisez les liens du tableau de bord qui passent automatiquement par le routeur sécurisé.

### 2. URLs directes (pour les développeurs)
Utilisez le format suivant :
```
http://localhost/library_system/public/router.php?module=MODULE&action=ACTION&PARAMETRES
```

#### Exemples d'URLs :

**📚 Gestion des livres :**
- Liste des livres : `router.php?module=items&action=list&lang=ar&type=book`
- Ajouter un livre : `router.php?module=items&action=add_edit&lang=ar&type=book`

**📰 Gestion des magazines :**
- Liste des magazines : `router.php?module=items&action=list&lang=ar&type=magazine`
- Ajouter un magazine : `router.php?module=items&action=add_edit&lang=ar&type=magazine`

**📰 Gestion des journaux :**
- Liste des journaux : `router.php?module=items&action=list&lang=ar&type=newspaper`
- Ajouter un journal : `router.php?module=items&action=add_edit&lang=ar&type=newspaper`

**📖 Gestion des acquisitions :**
- Liste des acquisitions : `router.php?module=acquisitions&action=list`
- Ajouter une acquisition : `router.php?module=acquisitions&action=add`

**👥 Gestion des emprunteurs :**
- Liste des emprunteurs : `router.php?module=borrowers&action=list`
- Ajouter un emprunteur : `router.php?module=borrowers&action=add`

**📋 Gestion des prêts :**
- Liste des prêts : `router.php?module=loans&action=list`
- Créer un prêt : `router.php?module=loans&action=borrow`
- Retourner un livre : `router.php?module=loans&action=return`

**👨‍💼 Administration :**
- Liste des employés : `router.php?module=administration&action=list`
- Ajouter un employé : `router.php?module=administration&action=add`

## 🔑 Système de permissions

Le routeur vérifie automatiquement vos permissions :

- **Admin** : Accès complet à tous les modules
- **Librarian** : Accès à la plupart des modules (sauf administration)
- **Assistant** : Accès en lecture seule à certains modules

## 🛡️ Fonctionnalités de sécurité

### ✅ Protection CSRF
Tous les formulaires sont protégés contre les attaques CSRF.

### ✅ Validation des données
Toutes les entrées utilisateur sont validées et nettoyées.

### ✅ Journalisation
Tous les accès sont enregistrés dans les logs pour la sécurité.

### ✅ Gestion des erreurs
Pages d'erreur personnalisées sans exposition d'informations sensibles.

## 🚨 En cas de problème

### Erreur 403 (Accès interdit)
- Vérifiez que vous êtes connecté
- Vérifiez vos permissions
- Contactez l'administrateur si nécessaire

### Erreur 404 (Page non trouvée)
- Vérifiez l'URL
- Assurez-vous que le module et l'action existent

## 📞 Support

En cas de problème, contactez l'équipe technique avec :
- L'URL que vous essayez d'accéder
- Le message d'erreur exact
- Votre nom d'utilisateur et rôle

---

**🎉 Félicitations ! Votre système est maintenant sécurisé et protégé !** 