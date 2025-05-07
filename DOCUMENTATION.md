# Documentation du Projet Camydia Agency

## Introduction

Ce document présente la structure et l'organisation du site web de Camydia Agency, une agence d'hôtesses professionnelles basée en Côte d'Ivoire. Cette documentation est destinée aux développeurs qui travaillent sur le projet.

## Architecture du Projet

### Structure Générale

Le projet est basé sur une architecture MVC (Modèle-Vue-Contrôleur) utilisant le framework Slim PHP avec Twig comme moteur de templates.

```
camydia-agency/
├── public/              # Point d'entrée public du site
├── src/                 # Code source PHP
│   ├── App/             # Classes spécifiques à l'application
│   │   ├── Config/      # Fichiers de configuration
│   │   ├── Controllers/ # Contrôleurs
│   │   ├── Twig/        # Extensions Twig personnalisées
│   │   └── Utilities/   # Classes utilitaires
│   ├── assets/          # Ressources source (avant compilation)
│   │   ├── css/         # Fichiers CSS source (Tailwind)
│   │   └── js/          # Fichiers JavaScript source
│   └── routes.php       # Définition des routes
├── vendor/              # Dépendances (Composer)
└── views/               # Templates Twig
    └── partials/        # Composants de template réutilisables
```

### Technologies Principales

- **Backend**: PHP 8.x avec Slim 4.x
- **Frontend**:
  - Tailwind CSS pour le styling
  - Alpine.js pour les interactions côté client
  - GSAP pour les animations
- **Templates**: Twig
- **Build Tools**: NPM et Tailwind CLI

## Système de Routing

### Configuration des Routes

Les routes sont centralisées dans la classe `App\App\Config\Routes` qui définit toutes les URL du site sous forme de constantes pour éviter les liens codés en dur dans le code.

```php
// Exemple d'utilisation dans src/App/Config/Routes.php
class Routes
{
    const HOME = '/';
    const ABOUT = '/about';
    const SERVICES = '/services';
    const CONTACT = '/contact';
    // ...
}
```

### Utilisation dans les Templates Twig

Une extension Twig personnalisée (`App\App\Twig\RouteExtension`) fournit plusieurs fonctions qui permettent d'accéder aux routes depuis les templates :

```twig
{# Utilisation des routes #}
<a href="{{ route('home') }}">Accueil</a>
<a href="{{ route('contact') }}">Contact</a>

{# Utilisation des assets #}
<img src="{{ asset('logo') }}" alt="Logo">
<link rel="stylesheet" href="{{ asset('css') }}">

{# Utilisation des images #}
<img src="{{ image('home', 'hero') }}" alt="Image hero">

{# Utilisation des liens externes #}
<a href="{{ external('facebook') }}">Facebook</a>
```

## Structure des Templates

### Layout de Base

Le template de base (`layout.twig`) définit la structure générale des pages avec les sections suivantes :

- `<head>` avec métadonnées et assets
- Header (inclus depuis `partials/header.twig`)
- Bloc `content` pour le contenu spécifique à chaque page
- Footer (inclus depuis `partials/footer.twig`)

### Pages Principales

- **Accueil** (`index.twig`): Page d'accueil avec sections Hero, Services, Témoignages et Partenaires
- **Notre Histoire** (`about.twig`): Présentation de l'agence
- **Services** (`services.twig`): Détail des services proposés
- **Contact** (`contact.twig`): Formulaire de contact et informations

### Composants Réutilisables

Les composants réutilisables sont stockés dans le dossier `views/partials/` :

- `header.twig`: En-tête du site avec menu de navigation
- `footer.twig`: Pied de page avec liens utiles et informations de contact

## Fonctionnalités Clés

### Gestion des Partenaires

La section partenaires sur la page d'accueil est générée dynamiquement à partir des images dans le dossier `/public/images/content/partenaires/`. Il suffit d'ajouter une image à ce dossier pour qu'elle apparaisse automatiquement dans la section.

```php
// Dans HomeController.php
$partenairesDir = __DIR__ . '/../../../public/images/content/partenaires';
$partenaires = [];

if (is_dir($partenairesDir)) {
    $files = scandir($partenairesDir);

    foreach ($files as $file) {
        // ... traitement des fichiers
        $partenaires[] = [
            'file' => $file,
            'path' => '/images/content/partenaires/' . $file,
            'name' => $name
        ];
    }
}
```

### Responsive Design

Le site est entièrement responsive grâce à Tailwind CSS :

- Utilisation de classes conditionnelles (`sm:`, `md:`, `lg:`, `xl:`) pour adapter l'affichage
- Menu mobile avec Alpine.js
- Images optimisées pour différentes tailles d'écran

## Workflow de Développement

### Installation et Configuration

1. Cloner le dépôt
2. Installer les dépendances Composer : `composer install`
3. Installer les dépendances NPM : `npm install`
4. Lancer le serveur de développement

### Commandes NPM

```bash
# Mode développement avec watch pour Tailwind CSS
npm run dev

# Build pour la production
npm run build

# Copier les fichiers JS de src vers public
npm run js-build
```

## Bonnes Pratiques

### Organisation du Code

- Suivre le pattern MVC
- Centraliser les routes et les configurations
- Utiliser les fonctions Twig personnalisées pour la cohérence

### Standards de Codage

- PSR-4 pour l'autoloading
- PSR-12 pour le style de code PHP
- Commentaires PHPDoc pour les classes et méthodes

### Gestion des Assets

- Les assets source sont dans `/src/assets/`
- Les assets compilés sont dans `/public/`
- Utiliser les helpers Twig pour référencer les assets

## Maintenance

### Mise à jour des Partenaires

Pour ajouter un nouveau partenaire :

1. Ajouter l'image au format jpg/png dans `/public/images/content/partenaires/`
2. L'image apparaîtra automatiquement sur la page d'accueil

### Modification des Routes

Pour ajouter ou modifier une route :

1. Mettre à jour la constante dans `src/App/Config/Routes.php`
2. Ajouter la route correspondante dans `src/routes.php` si nécessaire

### Personnalisation du Thème

Les couleurs principales sont définies dans `tailwind.config.js` :

- Primary: #ed1e79
- Secondary: #808080
- Accent: #ff9b2e

## Contacts

- Site Web : [Camydia Agency](https://www.camydia-agency.site)
- Développement : Topdigitalevel & Thalamus Côte d'Ivoire
