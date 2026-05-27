# 🎨 Portfolio Template Symfony

Un template de portfolio professionnel moderne et entièrement configurable, basé sur Symfony 7.2 avec une interface d'administration EasyAdmin intuitive.

## ✨ Fonctionnalités

### 🎯 Portfolio Complet
- **Page d'accueil moderne** : Section Hero/About fusionnée avec animations AOS
- **Projets** : Galerie de projets avec technologies, images et descriptions détaillées
- **Articles/Blog** : Section blog avec éditeur riche, blocs de contenu mixtes (texte, image, code) et mise en page en colonnes
- **Parcours professionnel** : Gestion complète de l'éducation et de l'expérience
- **Compétences** : Affichage dynamique des technologies et soft skills
- **Contact** : Formulaire de contact fonctionnel avec envoi d'emails

### ⚙️ Administration Facile
- **Interface EasyAdmin** : Administration complète via `/admin`
- **Gestion des médias** : Upload d'images avec VichUploader
- **Liens sociaux dynamiques** : Configuration des réseaux sociaux
- **Système de rôles** : USER, ADMIN, SUPER_ADMIN
- **CRUD complet** : Interface intuitive pour tous les contenus

### 🎨 Design Moderne
- **Responsive Design** : Compatible mobile, tablette et desktop
- **Tailwind CSS + DaisyUI** : Framework CSS moderne et customisable
- **Animations** : Transitions et animations AOS
- **Thèmes** : Support des thèmes clairs/sombres
- **Iconographie** : Intégration Symfony UX Icons

## 🛠️ Technologies Utilisées

### Backend
- **Symfony 7.4** (PHP 8.4)
- **Doctrine ORM** avec migrations
- **EasyAdmin 4.24** pour l'administration
- **VichUploader** pour la gestion des fichiers
- **Gedmo Extensions** (Timestamps, Blameable, SoftDelete)
- **Symfony Mailer** pour les emails

### Frontend
- **Webpack Encore** pour l'asset management
- **Vue.js 3** pour les composants interactifs
- **Tailwind CSS + DaisyUI** pour le styling
- **Symfony UX** (Live Components, Turbo, Icons)
- **AOS** pour les animations
- **Highlight.js** pour la coloration syntaxique des blocs code

### DevOps
- **Docker** avec Docker Compose
- **Makefile** pour l'automatisation
- **ESLint + Prettier** pour la qualité du code
- **PHPStan + PHP-CS-Fixer** pour l'analyse statique

## 🚀 Installation Rapide

### Prérequis
- PHP 8.4 ou supérieur
- Composer
- Node.js & NPM
- Docker & Docker Compose

### 1. Cloner le projet
```bash
git clone <votre-repo-url> mon-portfolio
cd mon-portfolio
```

### 2. Installation automatique
```bash
make setup
```

Cette commande exécute automatiquement :
- Installation des dépendances PHP et NPM
- Configuration de l'environnement
- Création de la base de données
- Exécution des migrations
- Build des assets

### 3. Créer un utilisateur administrateur
```bash
php bin/console security:hash-password
```
Entrez votre mot de passe et copiez le hash généré.

Ensuite, créez manuellement un utilisateur en base avec les rôles `ROLE_ADMIN` et `ROLE_SUPER_ADMIN`.

### 4. Démarrer le serveur
```bash
make start
```

Votre portfolio sera accessible sur `http://localhost:8000` et l'administration sur `http://localhost:8000/admin`.

## 📁 Structure du Projet

### Entités Principales

#### 📄 **AboutMe**
Profil principal du portfolio avec informations personnelles et liens sociaux intégrés.

#### 🚀 **Project**
Gestion des projets avec :
- Technologies utilisées
- Images multiples
- Dates de début/fin
- Descriptions détaillées
- Statut de publication

#### 📝 **Article & ArticleContent**
Système de blog avec blocs de contenu modulaires :
- **Blocs mixtes** : chaque article est composé de blocs indépendants (paragraphe, image, code)
- **Éditeur riche** : Trix WYSIWYG natif EasyAdmin pour les blocs texte (gras, italique, listes…)
- **Coloration syntaxique** : highlight.js (200+ langages) pour les blocs code
- **Mise en page CSS Grid** : chaque bloc configurable en pleine largeur, 1/3 ou 2/3 de colonne
- **Responsive** : layout en colonnes activé uniquement ≥ 1280px (xl), 1 colonne sur mobile/tablette
- **Ordre d'affichage** : champ `displayOrder` pour ordonner les blocs librement
- **Image principale** : bannière séparée du contenu de l'article
- **Gestion des brouillons** : publication manuelle avec date configurable

#### 💻 **Technology & SkillCategory**
Système de compétences avec :
- Catégorisation des technologies
- Niveaux de maîtrise (1-5 étoiles)
- Icônes personnalisables
- Images ou icônes Symfony UX

#### 🔗 **Social**
Liens sociaux dynamiques avec :
- Icônes personnalisables
- Ordre d'affichage configurable
- Activation/désactivation

#### 🎓 **Education & Experience**
Parcours professionnel avec :
- Timeline automatique
- Descriptions riches
- Dates flexibles

## 🎨 Personnalisation

### 1. Configuration du Profil
1. Connectez-vous à `/admin`
2. Remplissez la section "À Propos"
3. Ajoutez vos liens sociaux
4. Configurez vos informations de contact

### 2. Ajout de Projets
1. Aller dans "Projets" → "Projets"
2. Créer un nouveau projet
3. Ajouter les technologies utilisées
4. Uploader les images
5. Publier le projet

### 3. Rédaction d'Articles
1. Aller dans "Articles" → "Articles"
2. Créer un nouvel article (titre, slug, image principale, technologies)
3. Dans la section "Contenu de l'article", ajouter des blocs :
   - **Paragraphe** : éditeur Trix avec mise en forme (gras, italique, listes, liens)
   - **Image** : upload d'image avec texte alternatif
   - **Code** : coller du code source avec le langage (ex: `php`, `javascript`, `bash`)
4. Configurer la **Largeur** de chaque bloc : Pleine largeur / 1/3 / 2/3
5. Ajuster l'**Ordre** pour contrôler la disposition des blocs
6. Publier l'article

> Les blocs 1/3 et 2/3 s'affichent côte à côte sur les écrans ≥ 1280px et s'empilent sur mobile/tablette.

### 4. Gestion des Compétences
1. Créer des catégories de compétences
2. Ajouter les technologies avec niveaux
3. Configurer les soft skills
4. Utiliser les icônes Symfony UX recommandées

### 5. Personnalisation Visuelle
- Modifier les couleurs dans `tailwind.config.js`
- Personnaliser les thèmes DaisyUI
- Ajuster les animations AOS

## 🔧 Commandes Utiles

```bash
# Développement
make start          # Démarrer tous les services
make stop           # Arrêter tous les services
make install        # Installer les dépendances
make build          # Build de production

# Base de données
make db-reset       # Réinitialiser la base
make db-migrate     # Exécuter les migrations

# Qualité du code
make test           # Lancer les tests
make cs-fix         # Fixer le style de code
make stan           # Analyse statique
```

## 📊 Icônes Recommandées

Pour une cohérence visuelle, utilisez ces icônes Symfony UX :

### Technologies
- `skill-icons:javascript`
- `skill-icons:php-dark`
- `skill-icons:symfony-dark`
- `skill-icons:react-dark`
- `skill-icons:vue-dark`

### Réseaux Sociaux
- `skill-icons:github-dark`
- `skill-icons:linkedin`
- `skill-icons:twitter`
- `logos:youtube-icon`
- `skill-icons:instagram`

### Catégories
- `la:tools` (Outils)
- `hugeicons:developer` (Développement)
- `ph:devices` (Appareils)

## 🌍 Internationalisation

Le projet supporte le français et l'anglais :
- Fichiers de traduction dans `/translations`
- Interface d'administration en français
- Frontend adaptable selon la locale

## 🔒 Sécurité

- Authentification Symfony Security
- Protection CSRF sur les formulaires
- Validation des données d'entrée
- Upload de fichiers sécurisé
- Système de rôles granulaire

## 📈 Performance

- Assets optimisés avec Webpack Encore
- Images redimensionnées automatiquement
- Lazy loading des images
- Cache Symfony activé en production
- Progressive Web App ready

## 🤝 Contribution

Ce template est conçu pour être facilement personnalisable. N'hésitez pas à :
- Forker le projet
- Adapter le design à vos besoins
- Ajouter de nouvelles fonctionnalités
- Partager vos améliorations

## 📄 Licence

Ce projet est sous licence propriétaire. Vous êtes libre de l'utiliser pour créer votre portfolio personnel.

## 🆘 Support

Pour toute question ou problème :
1. Vérifiez la documentation
2. Consultez les logs d'erreur
3. Utilisez les commandes de debug Symfony

---

**Créé avec ❤️ et Symfony**

*Un template portfolio moderne pour développeurs exigeants*
