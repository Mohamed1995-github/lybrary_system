# Modifications du Système de Gestion de Bibliothèque

## Résumé des Modifications

Ce document décrit les modifications apportées au système de gestion de bibliothèque selon les spécifications demandées.

## 1. Modifications des Livres

### Catégories Limitées
Les livres sont maintenant limités aux catégories suivantes :
- **Administration** (إدارة)
- **Économie** (اقتصاد)
- **Droit** (قانون)
- **Diplomatie** (دبلوماسية)

### Informations Affichées
Lors de l'affichage des livres, seules les informations suivantes sont montrées :
- Numéro de classification
- Titre du livre
- Auteur
- Maison d'édition
- Année de publication
- Numéro d'édition

### Nouveau Champ
- **Type du livre** : Champ pour préciser la nature de l'ouvrage (référence, manuel scolaire, encyclopédie, dictionnaire, atlas, manuel, guide, autre)

## 2. Modifications des Revues (Magazines)

### Nouveaux Champs Ajoutés
- **Nom de la revue** : Champ obligatoire pour le nom de la revue
- **Numéro du volume** : Pour spécifier le numéro du volume
- **Numéro de classification** : Pour la classification de la revue

### Structure Modifiée
- Le formulaire d'ajout de revue a été restructuré pour mettre en avant le nom de la revue
- Le titre devient optionnel (pour les articles individuels)
- L'auteur devient optionnel

## 3. Modifications des Journaux

### Titres Limités
Les journaux sont maintenant limités à deux titres uniquement :
- **Ech-Chaab** (الشعب)
- **Journal Officiel** (الجريدة الرسمية)

### Nouveau Champ
- **Numéro du journal** : Champ obligatoire pour renseigner le numéro du journal

### Validation
- L'utilisateur ne peut sélectionner que l'un des deux titres autorisés
- Le numéro du journal est obligatoire

## 4. Modifications de la Base de Données

### Nouvelles Colonnes Ajoutées
```sql
ALTER TABLE items ADD COLUMN book_type VARCHAR(100);
ALTER TABLE items ADD COLUMN magazine_name VARCHAR(255);
ALTER TABLE items ADD COLUMN volume_number VARCHAR(50);
ALTER TABLE items ADD COLUMN newspaper_number VARCHAR(50);
```

### Structure Complète de la Table `items`
```sql
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lang VARCHAR(2) NOT NULL DEFAULT 'ar',
    type VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    publisher VARCHAR(255),
    year_pub INT,
    classification VARCHAR(100),
    field VARCHAR(50),
    copies_total INT DEFAULT 1,
    copies_in INT DEFAULT 1,
    format VARCHAR(50),
    isbn VARCHAR(20),
    pages INT,
    language VARCHAR(10),
    description TEXT,
    edition VARCHAR(100),
    series VARCHAR(255),
    frequency VARCHAR(50),
    issn VARCHAR(20),
    volume VARCHAR(50),
    issue VARCHAR(50),
    circulation VARCHAR(100),
    founded_year INT,
    book_type VARCHAR(100),
    magazine_name VARCHAR(255),
    volume_number VARCHAR(50),
    newspaper_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 5. Fichiers Modifiés

### Fichiers Principaux
1. **`modules/items/add_book.php`**
   - Catégories limitées aux 4 spécifiées
   - Ajout du champ "Type du livre"
   - Réorganisation des champs selon les spécifications

2. **`modules/items/add_magazine.php`**
   - Ajout des champs "Nom de la revue" et "Numéro du volume"
   - Restructuration du formulaire
   - Mise à jour de la requête SQL

3. **`modules/items/add_newspaper.php`**
   - Limitation aux deux titres autorisés
   - Ajout du champ "Numéro du journal"
   - Validation des titres autorisés

4. **`modules/items/list.php`**
   - Affichage adapté selon le type de matériau
   - Colonnes spécifiques pour chaque type
   - Informations limitées pour les livres selon les spécifications

### Fichiers de Support
5. **`update_database.php`**
   - Script pour mettre à jour la base de données
   - Ajout automatique des nouvelles colonnes
   - Vérification de l'intégrité de la base

6. **`check_database.php`**
   - Script de vérification de la structure de la base
   - Diagnostic des tables et colonnes

## 6. Instructions d'Installation

### Étape 1 : Mise à jour de la Base de Données
```bash
# Démarrer XAMPP
# Accéder à http://localhost/library_system/update_database.php
```

### Étape 2 : Vérification
```bash
# Accéder à http://localhost/library_system/check_database.php
```

### Étape 3 : Test des Fonctionnalités
1. Ajouter un livre avec les nouvelles catégories
2. Ajouter une revue avec les nouveaux champs
3. Ajouter un journal avec les titres limités
4. Vérifier l'affichage dans les listes

## 7. Fonctionnalités Conservées

- Interface multilingue (arabe/français)
- Système d'authentification
- Gestion des prêts
- Gestion des emprunteurs
- Interface responsive
- Validation des formulaires
- Messages d'erreur et de succès

## 8. Notes Techniques

### Sécurité
- Toutes les entrées utilisateur sont échappées avec `htmlspecialchars()`
- Validation côté serveur maintenue
- Protection contre les injections SQL

### Compatibilité
- Compatible avec PHP 7.4+
- Compatible avec MySQL 5.7+
- Interface responsive pour mobile et desktop

### Performance
- Requêtes SQL optimisées
- Index sur les colonnes fréquemment utilisées
- Pagination pour les grandes listes

## 9. Support

Pour toute question ou problème :
1. Vérifier que XAMPP est démarré
2. Exécuter le script `update_database.php`
3. Vérifier les permissions des fichiers
4. Consulter les logs d'erreur PHP

---

**Date de modification :** <?= date('Y-m-d H:i:s') ?>
**Version :** 2.0
**Auteur :** Assistant IA 