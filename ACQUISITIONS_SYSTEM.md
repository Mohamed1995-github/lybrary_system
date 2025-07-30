# Système de Gestion des Acquisitions - Library System

## 📋 Vue d'ensemble

Le système de gestion des acquisitions permet de gérer l'approvisionnement de la bibliothèque en livres, magazines et autres sources. Il offre une interface complète pour enregistrer, consulter, modifier et supprimer les acquisitions.

## 🗂️ Structure de la base de données

### Table `acquisitions`

```sql
CREATE TABLE acquisitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lang VARCHAR(2) NOT NULL DEFAULT 'ar',
    source_type ENUM('book', 'magazine', 'other') NOT NULL,
    acquisition_method ENUM('purchase', 'exchange', 'donation') NOT NULL,
    supplier_type ENUM('institution', 'publisher', 'person') NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    supplier_phone VARCHAR(50),
    supplier_address TEXT,
    cost DECIMAL(10,2) DEFAULT 0.00,
    quantity INT DEFAULT 1,
    acquired_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 📁 Fichiers du système

### 1. `modules/acquisitions/add.php`
- **Fonction** : Ajouter une nouvelle acquisition
- **URL** : `http://localhost/library_system/modules/acquisitions/add.php?lang=ar`
- **Fonctionnalités** :
  - Formulaire complet avec tous les champs requis
  - Validation des données
  - Interface bilingue (ar/fr)
  - Design responsive

### 2. `modules/acquisitions/list.php`
- **Fonction** : Afficher la liste des acquisitions
- **URL** : `http://localhost/library_system/modules/acquisitions/list.php?lang=ar`
- **Fonctionnalités** :
  - Tableau avec toutes les acquisitions
  - Actions d'édition et suppression
  - Badges colorés pour les types et méthodes
  - État vide avec bouton d'ajout

### 3. `modules/acquisitions/edit.php`
- **Fonction** : Modifier une acquisition existante
- **URL** : `http://localhost/library_system/modules/acquisitions/edit.php?lang=ar&id=1`
- **Fonctionnalités** :
  - Formulaire pré-rempli avec les données existantes
  - Validation et mise à jour
  - Redirection après succès

### 4. `modules/acquisitions/delete.php`
- **Fonction** : Supprimer une acquisition
- **URL** : `http://localhost/library_system/modules/acquisitions/delete.php?lang=ar&id=1`
- **Fonctionnalités** :
  - Page de confirmation
  - Affichage des détails avant suppression
  - Redirection automatique après suppression

## 🔧 Champs du système

### Type de source (نوع المصدر)
- **Livre (كتب)** - `book`
- **Revue / Magazine (مجلات)** - `magazine`
- **Autre source (مصادر أخرى)** - `other`

### Méthode d'approvisionnement (طريقة التزويد)
- **Achat (شراء)** - `purchase`
- **Échange (تبادل)** - `exchange`
- **Don (إهداء)** - `donation`

### Entité fournisseur (جهة التزويد)
- **Institution (مؤسسة)** - `institution`
- **Maison d'édition (دار النشر)** - `publisher`
- **Personne (شخص)** - `person`

### Détails du fournisseur
- **Nom (الاسم)** - `supplier_name` (obligatoire)
- **Numéro de téléphone (رقم الهاتف)** - `supplier_phone`
- **Adresse (عنوان المزود)** - `supplier_address`

### Informations supplémentaires
- **Coût** - `cost` (optionnel)
- **Quantité** - `quantity` (obligatoire, minimum 1)
- **Date d'acquisition** - `acquired_date` (obligatoire)
- **Notes** - `notes` (optionnel)

## 🎨 Interface utilisateur

### Design
- Interface moderne et responsive
- Support bilingue (arabe/français)
- Badges colorés pour différencier les types
- Animations et transitions fluides
- Design adaptatif pour mobile

### Couleurs des badges
- **Achat** : Vert (`#22c55e`)
- **Échange** : Bleu (`#3b82f6`)
- **Don** : Violet (`#9333ea`)

## 🔒 Sécurité

- Authentification requise pour toutes les pages
- Validation des données côté serveur
- Protection contre les injections SQL (PDO)
- Échappement des données affichées
- Confirmation pour les suppressions

## 📱 Responsive Design

Le système s'adapte automatiquement aux différentes tailles d'écran :
- **Desktop** : Affichage en grille avec colonnes multiples
- **Tablet** : Adaptation de la grille
- **Mobile** : Affichage en colonne unique

## 🚀 Installation et utilisation

### 1. Vérifier la base de données
Assurez-vous que la table `acquisitions` existe avec la structure correcte.

### 2. Accéder au système
- **Ajouter** : `http://localhost/library_system/modules/acquisitions/add.php?lang=ar`
- **Lister** : `http://localhost/library_system/modules/acquisitions/list.php?lang=ar`

### 3. Navigation
- Utilisez les boutons de navigation pour passer entre les pages
- Le paramètre `lang` contrôle la langue (ar/fr)
- Le paramètre `id` est utilisé pour l'édition et la suppression

## 🔄 Workflow typique

1. **Ajouter une acquisition** : Remplir le formulaire avec les informations du fournisseur
2. **Consulter la liste** : Voir toutes les acquisitions avec leurs détails
3. **Modifier** : Cliquer sur l'icône d'édition pour modifier une acquisition
4. **Supprimer** : Cliquer sur l'icône de suppression et confirmer

## 📊 Fonctionnalités avancées

- **Tri automatique** : Les acquisitions sont triées par date (plus récentes en premier)
- **Validation en temps réel** : Les champs obligatoires sont validés
- **Messages de confirmation** : Feedback utilisateur pour toutes les actions
- **Redirection intelligente** : Retour automatique à la liste après les actions

## 🛠️ Maintenance

### Ajouter de nouveaux types
Pour ajouter de nouveaux types de sources ou méthodes, modifiez les tableaux dans les fichiers PHP :

```php
$source_types = [
    'book' => ['ar' => 'كتب', 'fr' => 'Livres'],
    'magazine' => ['ar' => 'مجلات', 'fr' => 'Revues / Magazines'],
    'other' => ['ar' => 'مصادر أخرى', 'fr' => 'Autre source'],
    'new_type' => ['ar' => 'نوع جديد', 'fr' => 'Nouveau type'] // Ajouter ici
];
```

### Modifier la base de données
Si vous devez ajouter de nouveaux champs, mettez à jour la structure de la table et les formulaires correspondants.

## 📞 Support

Pour toute question ou problème avec le système d'acquisitions, consultez la documentation ou contactez l'équipe de développement. 