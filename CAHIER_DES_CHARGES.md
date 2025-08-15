# 📚 CAHIER DES CHARGES - SYSTÈME DE GESTION DE BIBLIOTHÈQUE

## 🎯 PRÉSENTATION GÉNÉRALE

### 1.1 Contexte et Objectifs

Le système de gestion de bibliothèque est une application web moderne développée en PHP, conçue pour répondre aux besoins d'une bibliothèque multilingue (Arabe/Français). Le système offre une interface utilisateur contemporaine avec un contrôle d'accès granulaire et une gestion complète des ressources documentaires.

### 1.2 Public Cible

- **Bibliothécaires** : Gestion quotidienne des collections
- **Administrateurs** : Gestion des utilisateurs et des permissions
- **Employés** : Accès limité selon leurs rôles
- **Utilisateurs finaux** : Consultation des catalogues

### 1.3 Objectifs Principaux

- ✅ Gestion complète des collections (livres, magazines, journaux)
- ✅ Système d'authentification et de permissions granulaire
- ✅ Interface bilingue (Arabe/Français) avec support RTL/LTR
- ✅ Design moderne et responsive
- ✅ Sécurité renforcée avec logging des accès
- ✅ Gestion des acquisitions et des prêts

## 🏗️ ARCHITECTURE TECHNIQUE

### 2.1 Technologies Utilisées

| Composant | Technologie | Version |
|-----------|-------------|---------|
| **Backend** | PHP | 7.4+ |
| **Base de données** | MySQL/MariaDB | 5.7+ |
| **Frontend** | HTML5, CSS3, JavaScript | ES6+ |
| **Serveur Web** | Apache/Nginx | - |
| **Design** | CSS Grid, Flexbox | - |
| **Icônes** | Font Awesome | 6.4.0 |
| **Typographie** | Google Fonts (Inter) | - |

### 2.2 Structure du Projet

```
library_system/
├── config/
│   ├── database.php          # Configuration DB sécurisée
│   └── db.php               # Configuration simple
├── includes/
│   ├── auth.php             # Authentification
│   ├── permissions.php      # Système de permissions
│   ├── security.php         # Sécurité générale
│   ├── module_security.php  # Sécurité des modules
│   └── functions.php        # Fonctions utilitaires
├── public/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   ├── dashboard.php        # Tableau de bord principal
│   ├── login.php           # Page de connexion
│   ├── router.php          # Routeur sécurisé
│   ├── error.php           # Gestion des erreurs
│   └── [modules]           # Pages des modules
├── modules/
│   ├── acquisitions/        # Gestion des acquisitions
│   ├── items/              # Gestion des items
│   └── loans/              # Gestion des prêts
├── lang/
│   ├── ar.php              # Traductions arabes
│   └── fr.php              # Traductions françaises
├── logs/                   # Fichiers de logs
├── uploads/                # Fichiers uploadés
└── temp/                   # Fichiers temporaires
```

### 2.3 Architecture de Sécurité

#### 2.3.1 Authentification
- Session PHP sécurisée
- Protection contre les attaques CSRF
- Validation des entrées utilisateur
- Logging des tentatives d'accès

#### 2.3.2 Système de Permissions
- Contrôle granulaire par type d'item
- Niveaux d'accès hiérarchiques
- Permissions spécifiques par action
- Interface adaptative selon les droits

## 🔐 SYSTÈME D'AUTHENTIFICATION ET PERMISSIONS

### 3.1 Niveaux d'Accès

| Niveau | Permissions | Description |
|--------|-------------|-------------|
| **admin** | Toutes les permissions | Accès complet au système |
| **librarian** | Gestion des items | Ajout, modification (pas de suppression) |
| **assistant** | Consultation + modification limitée | Accès en lecture et modification limitée |
| **reception** | Consultation uniquement | Accès en lecture seule |

### 3.2 Permissions par Type d'Item

#### 3.2.1 Livres (Books)
```php
$permissions = [
    'إدارة الكتب',           // Gestion des livres (AR)
    'Gestion des livres',    // Gestion des livres (FR)
    'عرض الكتب',            // Consultation des livres (AR)
    'Consultation des livres' // Consultation des livres (FR)
];
```

#### 3.2.2 Magazines
```php
$permissions = [
    'إدارة المجلات',         // Gestion des magazines (AR)
    'Gestion des magazines', // Gestion des magazines (FR)
    'عرض المجلات',          // Consultation des magazines (AR)
    'Consultation des revues' // Consultation des revues (FR)
];
```

#### 3.2.3 Journaux (Newspapers)
```php
$permissions = [
    'إدارة الصحف',           // Gestion des journaux (AR)
    'Gestion des journaux',  // Gestion des journaux (FR)
    'عرض الصحف',            // Consultation des journaux (AR)
    'Consultation des journaux' // Consultation des journaux (FR)
];
```

### 3.3 Contrôle d'Accès

#### 3.3.1 Vérification des Permissions
```php
// Vérification avant accès
if (!hasAnyPermission($requiredPermissions) && $accessLevel !== 'admin') {
    header('Location: error.php?code=403&lang=' . $lang);
    exit;
}
```

#### 3.3.2 Interface Adaptative
- Boutons actifs/inactifs selon les permissions
- Messages d'aide sur les permissions manquantes
- Indicateurs visuels de statut des permissions

## 📊 GESTION DES DONNÉES

### 4.1 Structure de la Base de Données

#### 4.1.1 Table `users`
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'librarian', 'assistant', 'reception') DEFAULT 'reception',
    permissions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 4.1.2 Table `books`
```sql
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    isbn VARCHAR(20),
    language ENUM('ar', 'fr', 'en') DEFAULT 'ar',
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 4.1.3 Table `magazines`
```sql
CREATE TABLE magazines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    publisher VARCHAR(255),
    issn VARCHAR(20),
    language ENUM('ar', 'fr', 'en') DEFAULT 'ar',
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 4.1.4 Table `newspapers`
```sql
CREATE TABLE newspapers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    publisher VARCHAR(255),
    language ENUM('ar', 'fr', 'en') DEFAULT 'ar',
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 4.2 Gestion des Connexions

#### 4.2.1 Classe Database
- Pattern Singleton pour la connexion
- Gestion sécurisée des requêtes préparées
- Logging des erreurs de base de données
- Support des transactions

#### 4.2.2 Configuration Sécurisée
```php
class Database {
    private static $instance = null;
    private $pdo;
    
    // Configuration via variables d'environnement
    private function getConfig() {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'dbname' => $_ENV['DB_NAME'] ?? 'library_db',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
        ];
    }
}
```

## 🎨 INTERFACE UTILISATEUR

### 5.1 Design System

#### 5.1.1 Palette de Couleurs
```css
:root {
    --primary-color: #2563eb;      /* Bleu principal */
    --secondary-color: #059669;    /* Vert secondaire */
    --accent-color: #7c3aed;      /* Violet accent */
    --success-color: #10b981;      /* Vert succès */
    --warning-color: #f59e0b;      /* Jaune avertissement */
    --error-color: #ef4444;        /* Rouge erreur */
    --text-primary: #1f2937;       /* Texte principal */
    --text-secondary: #6b7280;     /* Texte secondaire */
    --bg-primary: #ffffff;         /* Fond principal */
    --bg-secondary: #f9fafb;       /* Fond secondaire */
    --border-color: #e5e7eb;       /* Couleur des bordures */
}
```

#### 5.1.2 Typographie
- **Famille** : Inter (Google Fonts)
- **Poids** : 400, 500, 600, 700
- **Responsive** : Adaptation automatique selon l'écran

#### 5.1.3 Composants
- **Cards** : Conteneurs élevés avec ombres
- **Boutons** : Styles multiples (primary, secondary, outline)
- **Formulaires** : Validation moderne avec feedback
- **Tableaux** : Responsive avec effets hover
- **Navigation** : Navigation propre avec états actifs

### 5.2 Support Multilingue

#### 5.2.1 Langues Supportées
- **Arabe (ar)** : Direction RTL, interface complète
- **Français (fr)** : Direction LTR, interface complète

#### 5.2.2 Système de Traduction
```php
// Chargement des traductions
$translations = include "lang/{$lang}.php";

// Utilisation
echo $translations['dashboard_title'];
```

#### 5.2.3 Adaptation Culturelle
- **Arabe** : Support RTL complet, éléments culturels appropriés
- **Français** : Design occidental standard
- **Icônes** : Adaptation selon la langue

### 5.3 Responsive Design

#### 5.3.1 Breakpoints
```css
/* Mobile First */
@media (min-width: 768px) { /* Tablet */ }
@media (min-width: 1024px) { /* Desktop */ }
@media (min-width: 1280px) { /* Large Desktop */ }
```

#### 5.3.2 Adaptation par Écran
- **Desktop** : Interface complète avec sidebars
- **Tablet** : Layout adapté avec éléments tactiles
- **Mobile** : Layout single-column optimisé

## 🔧 FONCTIONNALITÉS PRINCIPALES

### 6.1 Gestion des Items

#### 6.1.1 Ajout d'Items
- Formulaire de saisie avec validation
- Upload de fichiers (covers, documents)
- Gestion des métadonnées (ISBN, ISSN, etc.)
- Support multilingue des champs

#### 6.1.2 Consultation des Items
- Liste paginée avec filtres
- Recherche en temps réel
- Tri par différents critères
- Export des données

#### 6.1.3 Modification d'Items
- Édition en place ou formulaire dédié
- Historique des modifications
- Validation des changements
- Notifications de mise à jour

### 6.2 Système de Prêts

#### 6.2.1 Gestion des Prêts
- Création de prêts avec validation
- Calcul automatique des dates de retour
- Gestion des retards et pénalités
- Historique des prêts par utilisateur

#### 6.2.2 Retours et Renouvellements
- Processus de retour simplifié
- Renouvellement automatique si possible
- Notifications de retard
- Calcul des pénalités

### 6.3 Gestion des Utilisateurs

#### 6.3.1 Création de Comptes
- Formulaire d'inscription sécurisé
- Validation des données
- Attribution automatique des rôles
- Activation par email (optionnel)

#### 6.3.2 Gestion des Profils
- Modification des informations personnelles
- Changement de mot de passe
- Gestion des préférences
- Historique des activités

### 6.4 Tableau de Bord

#### 6.4.1 Statistiques
- Nombre total d'items par catégorie
- Prêts actifs et en retard
- Utilisateurs actifs
- Activité récente

#### 6.4.2 Actions Rapides
- Ajout rapide d'items
- Consultation des prêts
- Gestion des utilisateurs
- Accès aux rapports

## 🛡️ SÉCURITÉ ET PERFORMANCE

### 7.1 Sécurité

#### 7.1.1 Protection contre les Attaques
- **SQL Injection** : Requêtes préparées
- **XSS** : Échappement des sorties
- **CSRF** : Tokens de protection
- **Session Hijacking** : Sessions sécurisées

#### 7.1.2 Validation des Données
```php
// Validation côté serveur
function validateInput($data, $rules) {
    $errors = [];
    foreach ($rules as $field => $rule) {
        if (!validateField($data[$field], $rule)) {
            $errors[] = "Erreur dans le champ $field";
        }
    }
    return $errors;
}
```

#### 7.1.3 Logging de Sécurité
- Logs d'accès autorisés/non autorisés
- Traçabilité des actions utilisateur
- Surveillance des tentatives d'intrusion
- Alertes en cas d'anomalie

### 7.2 Performance

#### 7.2.1 Optimisations Base de Données
- Index sur les champs de recherche
- Requêtes optimisées
- Pagination des résultats
- Cache des requêtes fréquentes

#### 7.2.2 Optimisations Frontend
- CSS et JS minifiés
- Images optimisées
- Lazy loading des contenus
- Compression gzip

#### 7.2.3 Monitoring
- Surveillance des performances
- Logs d'erreurs détaillés
- Métriques d'utilisation
- Alertes de performance

## 📋 FONCTIONNALITÉS AVANCÉES

### 8.1 Système de Rapports

#### 8.1.1 Rapports Standard
- Rapport d'inventaire
- Rapport des prêts
- Rapport des utilisateurs
- Rapport des acquisitions

#### 8.1.2 Rapports Personnalisés
- Création de rapports sur mesure
- Export en différents formats (PDF, Excel)
- Planification de rapports automatiques
- Partage de rapports

### 8.2 Notifications

#### 8.2.1 Types de Notifications
- Retards de prêts
- Nouveaux items disponibles
- Modifications de compte
- Alertes système

#### 8.2.2 Canaux de Notification
- Notifications in-app
- Emails automatiques
- SMS (optionnel)
- Push notifications (optionnel)

### 8.3 API REST

#### 8.3.1 Endpoints Principaux
```php
// Items
GET    /api/items              // Liste des items
POST   /api/items              // Créer un item
GET    /api/items/{id}         // Détails d'un item
PUT    /api/items/{id}         // Modifier un item
DELETE /api/items/{id}         // Supprimer un item

// Users
GET    /api/users              // Liste des utilisateurs
POST   /api/users              // Créer un utilisateur
GET    /api/users/{id}         // Détails d'un utilisateur
PUT    /api/users/{id}         // Modifier un utilisateur

// Loans
GET    /api/loans              // Liste des prêts
POST   /api/loans              // Créer un prêt
PUT    /api/loans/{id}/return  // Retourner un prêt
```

#### 8.3.2 Authentification API
- Tokens JWT
- Rate limiting
- Documentation Swagger
- Tests automatisés

## 🚀 DÉPLOIEMENT ET MAINTENANCE

### 9.1 Environnements

#### 9.1.1 Développement
- Serveur local (XAMPP/WAMP)
- Base de données de test
- Logs détaillés
- Mode debug activé

#### 9.1.2 Production
- Serveur dédié ou VPS
- Base de données optimisée
- Logs d'erreurs uniquement
- Mode production sécurisé

### 9.2 Procédures de Déploiement

#### 9.2.1 Prérequis
```bash
# Serveur web
- Apache 2.4+ ou Nginx 1.18+
- PHP 7.4+
- MySQL 5.7+ ou MariaDB 10.3+

# Extensions PHP requises
- PDO
- PDO_MySQL
- mbstring
- json
- fileinfo
```

#### 9.2.2 Installation
```bash
# 1. Cloner le projet
git clone [repository-url]
cd library_system

# 2. Configurer la base de données
mysql -u root -p < database/schema.sql

# 3. Configurer les variables d'environnement
cp .env.example .env
# Éditer .env avec les bonnes valeurs

# 4. Définir les permissions
chmod 755 public/
chmod 644 config/
chmod 777 logs/
chmod 777 uploads/

# 5. Tester l'installation
php test_installation.php
```

### 9.3 Sauvegarde et Restauration

#### 9.3.1 Sauvegarde Automatique
```bash
#!/bin/bash
# Script de sauvegarde quotidienne
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p library_db > backup_$DATE.sql
tar -czf backup_$DATE.tar.gz backup_$DATE.sql
rm backup_$DATE.sql
```

#### 9.3.2 Restauration
```bash
# Restaurer une sauvegarde
mysql -u root -p library_db < backup_20231201_143000.sql
```

### 9.4 Monitoring et Maintenance

#### 9.4.1 Surveillance
- Monitoring des performances
- Surveillance des logs d'erreur
- Vérification de l'espace disque
- Contrôle de la connectivité DB

#### 9.4.2 Maintenance Préventive
- Nettoyage des logs anciens
- Optimisation de la base de données
- Mise à jour des dépendances
- Tests de sécurité réguliers

## 📊 TESTS ET QUALITÉ

### 10.1 Tests Unitaires

#### 10.1.1 Tests des Fonctions Critiques
```php
class DatabaseTest extends PHPUnit\Framework\TestCase {
    public function testConnection() {
        $db = Database::getInstance();
        $this->assertNotNull($db->getConnection());
    }
    
    public function testQueryExecution() {
        $db = Database::getInstance();
        $result = $db->fetchAll("SELECT 1 as test");
        $this->assertEquals(1, $result[0]['test']);
    }
}
```

#### 10.1.2 Tests de Sécurité
- Tests d'injection SQL
- Tests XSS
- Tests CSRF
- Tests d'authentification

### 10.2 Tests d'Intégration

#### 10.2.1 Tests des Workflows
- Processus d'ajout d'item
- Processus de prêt/retour
- Processus d'authentification
- Processus de gestion des permissions

#### 10.2.2 Tests de Performance
- Tests de charge
- Tests de temps de réponse
- Tests de mémoire
- Tests de base de données

### 10.3 Tests Utilisateur

#### 10.3.1 Tests d'Interface
- Tests de navigation
- Tests de formulaires
- Tests de responsive design
- Tests d'accessibilité

#### 10.3.2 Tests d'Acceptation
- Validation des fonctionnalités
- Tests des cas d'usage
- Tests des scénarios d'erreur
- Tests de satisfaction utilisateur

## 📈 ÉVOLUTIONS FUTURES

### 11.1 Fonctionnalités Planifiées

#### 11.1.1 Court Terme (3-6 mois)
- [ ] Système de réservation d'items
- [ ] Notifications push
- [ ] Export PDF des rapports
- [ ] Interface mobile native

#### 11.1.2 Moyen Terme (6-12 mois)
- [ ] Système de recommandations
- [ ] Intégration avec d'autres bibliothèques
- [ ] API publique
- [ ] Système de badges/récompenses

#### 11.1.3 Long Terme (12+ mois)
- [ ] Intelligence artificielle pour les recommandations
- [ ] Système de prêt inter-bibliothèques
- [ ] Application mobile complète
- [ ] Intégration avec des systèmes externes

### 11.2 Améliorations Techniques

#### 11.2.1 Performance
- [ ] Cache Redis pour les requêtes fréquentes
- [ ] CDN pour les assets statiques
- [ ] Optimisation des requêtes DB
- [ ] Compression des réponses

#### 11.2.2 Sécurité
- [ ] Authentification à deux facteurs
- [ ] Chiffrement des données sensibles
- [ ] Audit trail complet
- [ ] Détection d'intrusion

## 📝 DOCUMENTATION

### 12.1 Documentation Technique

#### 12.1.1 Architecture
- Diagrammes UML
- Documentation de l'API
- Guide de déploiement
- Guide de maintenance

#### 12.1.2 Code
- Commentaires dans le code
- Documentation PHPDoc
- Standards de codage
- Guide de contribution

### 12.2 Documentation Utilisateur

#### 12.2.1 Guides Utilisateur
- Guide d'utilisation complet
- Tutoriels vidéo
- FAQ
- Guide de dépannage

#### 12.2.2 Documentation Administrative
- Guide d'administration
- Procédures de sécurité
- Guide de configuration
- Procédures de sauvegarde

## 🎯 CONCLUSION

Ce cahier des charges présente un système de gestion de bibliothèque moderne, sécurisé et évolutif. Le système répond aux besoins actuels tout en prévoyant les évolutions futures. L'architecture modulaire et le système de permissions granulaire permettent une adaptation facile aux besoins spécifiques de chaque bibliothèque.

### Points Forts du Système

1. **Sécurité Renforcée** : Authentification multi-niveaux avec permissions granulaire
2. **Interface Moderne** : Design responsive et bilingue
3. **Performance Optimisée** : Architecture scalable avec cache
4. **Maintenance Facile** : Code modulaire et documentation complète
5. **Évolutivité** : Architecture extensible pour les futures fonctionnalités

### Recommandations

1. **Phase 1** : Déploiement et formation des utilisateurs
2. **Phase 2** : Optimisation des performances et ajout de fonctionnalités
3. **Phase 3** : Intégration avec d'autres systèmes et API publique

Le système est prêt pour un déploiement en production avec un support complet et une roadmap d'évolution claire.

---

**Document créé le :** <?= date('d/m/Y') ?>  
**Version :** 1.0  
**Auteur :** Équipe de développement  
**Statut :** Approuvé ✅ 