# Instructions finales pour le déploiement

Après avoir analysé et corrigé les problèmes du site, voici la liste complète des modifications à apporter sur le serveur de production.

## 1. Correction des chemins d'images

### Solution pour HomeController.php

Le problème principal avec les logos des partenaires est lié au chemin relatif utilisé. Remplacez le contenu du fichier `HomeController.php` par celui du fichier `fix-partenaires-chemin.php`.

```bash
# Sur le serveur
cd ~/public_html/private/src/App/Controllers/
nano HomeController.php

# Remplacez tout le contenu avec celui du fichier fix-partenaires-chemin.php
```

Le changement principal est:
```php
// AVANT: 
$partenairesDir = __DIR__ . '/../../../public/images/content/partenaires';

// APRÈS:
$partenairesDir = $_SERVER['DOCUMENT_ROOT'] . '/public/images/content/partenaires';
```

### Solution pour les autres images

Pour les images référencées dans les templates Twig, modifiez `Routes.php`:

```bash
# Sur le serveur
cd ~/public_html/private/src/App/Config/
nano Routes.php

# Remplacez tout le contenu avec celui du fichier production-routes.php
```

Le changement principal est d'ajouter `/public` au début de tous les chemins d'images:
```php
// AVANT: 
const ASSETS = [
    'CSS' => '/css/main.css',
    'JS' => '/js/main.js',
    'LOGO' => '/images/logo/logo-camydia.jpg',
];

// APRÈS:
const ASSETS = [
    'CSS' => '/public/css/main.css',
    'JS' => '/public/js/main.js',
    'LOGO' => '/public/images/logo/logo-camydia.jpg',
];
```

### Modification directe des templates

Pour les images référencées directement dans les templates:

```bash
# Pour about.twig
cd ~/public_html/private/views/
nano about.twig

# Recherchez toutes les occurrences de /images/ et remplacez-les par /public/images/
```

```bash
# Pour contact.twig
cd ~/public_html/private/views/
nano contact.twig

# Remplacez la ligne 218
<img src="/images/content/about/others/82407.png" alt="...">

# Par
<img src="/public/images/content/about/others/82407.png" alt="...">
```

## 2. Vider le cache Twig

Après avoir effectué toutes ces modifications, videz le cache Twig:

```bash
rm -rf ~/public_html/private/cache/twig/*
```

## 3. Vérifications

Après avoir appliqué ces modifications, vérifiez les points suivants:

1. Les logos des partenaires s'affichent sur la page d'accueil
2. Les images sur la page "À propos" s'affichent correctement
3. L'image de carte sur la page Contact s'affiche correctement
4. Les autres assets (CSS, JS) sont chargés correctement

## Solution alternative avec .htaccess

Si vous préférez ne pas modifier tous les fichiers PHP et templates, vous pouvez ajouter une règle de redirection dans le fichier .htaccess à la racine:

```bash
cd ~/public_html/
nano .htaccess

# Ajoutez cette règle avant la règle RewriteRule principale:
RewriteCond %{REQUEST_URI} ^/images/(.*)$
RewriteRule ^images/(.*)$ public/images/$1 [L]
```

Cette solution est moins propre mais peut servir de correctif temporaire.

## Conclusion

Ces modifications corrigent les problèmes d'affichage d'images en ajustant les chemins pour qu'ils correspondent à la structure réelle de votre hébergement LWS, où les assets sont accessibles via `/public/`. 

La cause principale était l'incohérence entre:
1. La structure en développement: `/images/` (racine web = dossier public)
2. La structure en production: `/public/images/` (racine web = dossier public_html)