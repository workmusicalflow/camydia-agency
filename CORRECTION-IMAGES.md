# Correction des problèmes d'affichage d'images

## Problèmes identifiés
1. Sur le site de production, les chemins d'images doivent commencer par `/public/images/` et non par `/images/`
2. Ce problème affecte plusieurs fichiers:
   - Les templates Twig (about.twig, contact.twig)
   - La classe Routes.php pour les constantes d'images
   - Le HomeController.php pour les images des partenaires

## Fichiers à modifier

### 1. HomeController.php 
Ce fichier génère les chemins pour les logos des partenaires affichés sur la page d'accueil.

**Ligne à modifier:** 37
**Ancien chemin:** `/images/content/partenaires/`
**Nouveau chemin:** `/public/images/content/partenaires/`

```php
// De:
'path' => '/images/content/partenaires/' . $file,

// À:
'path' => '/public/images/content/partenaires/' . $file,
```

### 2. Routes.php
Ce fichier centralise tous les chemins d'assets du site.

**Lignes à modifier:** 25-44
**Modification:** Ajouter le préfixe `/public` à tous les chemins d'images

```php
// De:
const ASSETS = [
    'CSS' => '/css/main.css',
    'JS' => '/js/main.js',
    'LOGO' => '/images/logo/logo-camydia.jpg',
];

// À:
const ASSETS = [
    'CSS' => '/public/css/main.css',
    'JS' => '/public/js/main.js',
    'LOGO' => '/public/images/logo/logo-camydia.jpg',
];

// Et aussi pour IMAGES:
const IMAGES = [
    'HOME' => [
        'HERO' => '/public/images/content/home/hero-min.png',
        'TESTIMONIALS_BKG' => '/public/images/content/home/testimonials-bkg.jpg',
    ],
    ...
];
```

### 3. Templates Twig
Modifier directement les chemins d'images dans les templates:

#### about.twig
Lignes: 60-69, 85, 115, 158-162

```twig
<!-- De: -->
<img src="/images/content/about/others/fe1c7.jpg" alt="...">

<!-- À: -->
<img src="/public/images/content/about/others/fe1c7.jpg" alt="...">
```

#### contact.twig
Ligne: 218

```twig
<!-- De: -->
<img src="/images/content/about/others/82407.png" alt="...">

<!-- À: -->
<img src="/public/images/content/about/others/82407.png" alt="...">
```

## Fichiers production prêts à l'emploi

Nous avons préparé les versions corrigées des fichiers suivants:

1. `production-HomeController.php` - Version corrigée de HomeController.php
2. `production-routes.php` - Version corrigée de Routes.php
3. `production-version-index.php` - Version corrigée d'index.php pour la racine

## Instructions de déploiement

1. **Connectez-vous via SSH ou terminal cPanel**:
   ```bash
   ssh utilisateur@camydia-agency.site
   ```

2. **Sauvegardez les fichiers existants**:
   ```bash
   cd ~/public_html
   mkdir -p backups
   cp private/src/App/Controllers/HomeController.php backups/
   cp private/src/App/Config/Routes.php backups/
   ```

3. **Déployez les fichiers corrigés**:
   ```bash
   # Copier HomeController.php
   cp /chemin/vers/production-HomeController.php ~/public_html/private/src/App/Controllers/HomeController.php
   
   # Copier Routes.php
   cp /chemin/vers/production-routes.php ~/public_html/private/src/App/Config/Routes.php
   ```

4. **Modifier les templates Twig si nécessaire**:
   ```bash
   # Chercher tous les chemins d'images directs
   grep -r "/images/" ~/public_html/private/views/
   
   # Modifier ces fichiers pour ajouter /public/ avant /images/
   ```

5. **Vider le cache Twig**:
   ```bash
   rm -rf ~/public_html/private/cache/twig/*
   ```

6. **Vérifier que les images s'affichent correctement**:
   - Vérifier la page d'accueil pour les logos partenaires
   - Vérifier la page à propos pour les images d'équipe
   - Vérifier la page contact pour l'image de localisation

## Alternative: Solution avec .htaccess

Si vous préférez ne pas modifier le code source, vous pouvez ajouter cette règle de redirection dans le fichier .htaccess à la racine:

```apache
# Ajouter dans public_html/.htaccess
# Redirection des chemins /images/ vers /public/images/
RewriteRule ^images/(.*)$ public/images/$1 [L]
```

Cette solution est moins propre mais peut servir de correctif temporaire.