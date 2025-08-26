# CarPlatform - Plateforme d'Affichage de Voitures

Une plateforme moderne permettant aux agences d'afficher leurs voitures à vendre et à louer, et aux clients de les contacter directement.

## 🚀 Fonctionnalités

### Pour les Clients
- **Navigation et recherche** : Parcourir toutes les voitures disponibles
- **Filtres avancés** : Filtrer par marque, carburant, type (vente/location), prix
- **Contact direct** : Contacter les agences par téléphone, email ou message intégré
- **Inscription simple** : Création de compte client en quelques clics

### Pour les Agences
- **Gestion complète** : Ajouter, modifier, supprimer des voitures
- **Tableau de bord** : Vue d'ensemble des voitures et statistiques
- **Gestion des messages** : Recevoir et répondre aux messages des clients
- **Profil d'agence** : Informations complètes (adresse, téléphone, site web)

## 🛠️ Technologies Utilisées

- **Frontend** : Next.js 14, React, TypeScript, Tailwind CSS
- **Backend** : Next.js API Routes
- **Base de données** : SQLite avec Prisma ORM
- **Authentification** : JWT avec bcrypt
- **UI/UX** : Lucide React pour les icônes, design responsive

## 📦 Installation

1. **Cloner le projet**
   ```bash
   git clone <repository-url>
   cd car-platform
   ```

2. **Installer les dépendances**
   ```bash
   npm install
   ```

3. **Configurer la base de données**
   ```bash
   npx prisma generate
   npx prisma migrate dev --name init
   ```

4. **Ajouter des données de test (optionnel)**
   ```bash
   npx tsx src/lib/seed.ts
   ```

5. **Lancer l'application**
   ```bash
   npm run dev
   ```

L'application sera accessible sur [http://localhost:3000](http://localhost:3000)

## 👥 Comptes de Test

Après avoir exécuté le script de seed, vous pouvez utiliser ces comptes :

### Agences
- **Auto Plus** : `contact@autoplus.fr` / `password123`
- **Car Auto** : `info@carauto.fr` / `password123`

### Client
- **Client Test** : `client@test.fr` / `password123`

## 🗂️ Structure du Projet

```
src/
├── app/                    # Pages Next.js (App Router)
│   ├── api/               # API Routes
│   │   ├── auth/          # Authentification
│   │   ├── cars/          # Gestion des voitures
│   │   └── messages/      # Système de messages
│   ├── dashboard/         # Tableau de bord agences
│   ├── login/             # Page de connexion
│   ├── register/          # Page d'inscription
│   └── add-car/           # Ajout de voiture
├── components/            # Composants React
│   ├── Navbar.tsx         # Navigation
│   └── CarCard.tsx        # Carte de voiture
├── contexts/              # Contextes React
│   └── AuthContext.tsx    # Gestion de l'authentification
├── lib/                   # Utilitaires
│   ├── prisma.ts          # Client Prisma
│   ├── auth.ts            # Utilitaires d'authentification
│   └── seed.ts            # Données de test
└── prisma/
    └── schema.prisma      # Schéma de base de données
```

## 🔐 Sécurité

- **Mots de passe** : Hachage avec bcrypt
- **Authentification** : Tokens JWT sécurisés
- **Autorisation** : Vérification des rôles et propriétés
- **Validation** : Validation des données côté serveur

## 🎨 Interface

L'interface est entièrement responsive et moderne avec :
- Design épuré et professionnel
- Navigation intuitive
- Formulaires optimisés
- Feedback utilisateur en temps réel
- Thème cohérent avec Tailwind CSS

## 🚗 Fonctionnement

### Flux Utilisateur Client
1. Navigation sur la page d'accueil
2. Utilisation des filtres pour trouver une voiture
3. Clic sur "Contacter" pour voir les informations de l'agence
4. Envoi d'un message via le formulaire intégré (si connecté)
5. Contact direct par téléphone ou email

### Flux Utilisateur Agence
1. Inscription en tant qu'agence
2. Connexion au tableau de bord
3. Ajout de voitures via le formulaire
4. Gestion des voitures (modification, suppression, masquage)
5. Consultation et réponse aux messages des clients

## 📱 Responsive Design

L'application est entièrement responsive et s'adapte à :
- Desktop (1200px+)
- Tablette (768px - 1199px)
- Mobile (< 768px)

## 🔄 API Endpoints

### Authentification
- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion

### Voitures
- `GET /api/cars` - Liste des voitures (avec filtres)
- `POST /api/cars` - Ajouter une voiture (agences)
- `GET /api/cars/my-cars` - Voitures d'une agence
- `GET /api/cars/[id]` - Détails d'une voiture
- `PATCH /api/cars/[id]` - Modifier une voiture
- `DELETE /api/cars/[id]` - Supprimer une voiture

### Messages
- `GET /api/messages` - Messages reçus (agences)
- `POST /api/messages` - Envoyer un message

## 🚀 Déploiement

L'application peut être déployée sur :
- **Vercel** (recommandé pour Next.js)
- **Netlify**
- **Heroku**
- Tout hébergeur supportant Node.js

Pour un déploiement en production, configurez :
1. Variables d'environnement (`JWT_SECRET`, `DATABASE_URL`)
2. Base de données PostgreSQL (recommandée en production)
3. Domaine personnalisé

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commit vos changements
4. Push vers la branche
5. Ouvrir une Pull Request

## 📞 Support

Pour toute question ou problème, contactez-nous à [support@carplatform.fr](mailto:support@carplatform.fr)
