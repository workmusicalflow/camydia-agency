# Guide de Déploiement - Camydia Agency

Ce document détaille la procédure de déploiement du site Camydia Agency sur l'hébergement LWS avec le domaine `camydia-agency.site`.

## Table des matières

- [Prérequis](#prérequis)
- [Préparation au déploiement](#étape-1--préparation-au-déploiement)
- [Configuration du serveur](#étape-2--configuration-du-serveur)
- [Transfert des fichiers](#étape-3--transfert-des-fichiers)
- [Installation des dépendances](#étape-4--installation-des-dépendances)
- [Configuration de la base de données](#étape-5--configuration-de-la-base-données)
- [Vérifications et tests](#étape-6--vérifications-et-tests)
- [Maintenance et mises à jour](#maintenance-et-mises-à-jour)
- [Résolution des problèmes](#résolution-des-problèmes)
- [Ressources utiles](#ressources-utiles)

## Prérequis

- Accès FTP à l'hébergement LWS (identifiants fournis par LWS)
- Accès au cPanel de l'hébergement et au terminal SSH
- Client FTP (FileZilla recommandé)
- Site complet en local, prêt pour la production
- PHP 8.0+ sur le serveur d'hébergement
- Extensions PHP requises: pdo_sqlite/pdo_mysql, mbstring, json, openssl

## Étape 1 : Préparation au déploiement

### 1.1. Compiler les assets pour la production

```bash
# Construire les CSS pour la production
npm run build

# Optimiser et copier les fichiers JS
npm run js-build
```

### 1.2. Configurer le fichier d'environnement

Créez un fichier `.env.production` pour l'environnement de production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.camydia-agency.site

# Configuration SQLite
DB_CONNECTION=sqlite
DB_DATABASE=/home/username/public_html/private/database.sqlite

# Configuration SMTP
MAIL_HOST=mail.camydia-agency.site
MAIL_PORT=465
MAIL_USERNAME=contact@camydia-agency.site
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=contact@camydia-agency.site
MAIL_FROM_NAME="Camydia Agency"
```

### 1.3. Préparer les fichiers .htaccess

Sur l'hébergement LWS, vous aurez besoin de deux fichiers `.htaccess` distincts:

#### 1.3.1. Fichier `.htaccess` principal (à la racine de `public_html/`)

Ce fichier gère les redirections vers `index.php` et la protection du dossier `private`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Redirection HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Protection du dossier private
    RewriteRule ^private(/.*)?$ - [F,L]

    # Servir les fichiers statiques du dossier public directement
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    # Rediriger toutes les autres requêtes vers index.php
    RewriteRule ^ index.php [QSA,L]
</IfModule>

# Optimisation PHP
<IfModule mod_php8.c>
    php_value upload_max_filesize 64M
    php_value post_max_size 64M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>

# Désactiver la signature du serveur
ServerSignature Off

# Protection des fichiers sensibles
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "(^#.*#|\.(bak|config|sql|sqlite|fla|psd|ini|log|sh|inc|swp|dist)|~)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### 1.3.2. Fichier `.htaccess` secondaire (dans le dossier `public_html/public/`)

Ce fichier gère le cache et la compression des assets statiques:

```apache
# Compression GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json application/xml
</IfModule>

# Cache des ressources statiques
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
</IfModule>

# Headers de sécurité pour les assets statiques
<IfModule mod_headers.c>
    # Désactiver le MIME-sniffing
    Header set X-Content-Type-Options "nosniff"

    # Protection contre le clickjacking
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
```

## Étape 2 : Configuration du serveur

### 2.1. Structure des répertoires

Organisez votre hébergement selon la structure existante sur LWS:

```
public_html/               # Racine web publique
    index.php              # Point d'entrée (voir section 2.2)
    public/                # Dossier pour les fichiers publics
        css/               # Fichiers CSS compilés
        js/                # Fichiers JavaScript
        images/            # Images et médias
        .htaccess          # Configuration Apache
    private/               # Dossier pour les fichiers sensibles
        src/               # Code source PHP
        views/             # Templates Twig
        cache/             # Dossier pour les caches (à créer)
        logs/              # Dossier pour les logs (à créer)
        composer.json      # Dépendances PHP
        composer.lock      # Verrou des versions
        .env               # Configuration d'environnement
```

### 2.2. Adapter le fichier index.php

Créez ou modifiez le fichier `public_html/index.php` pour qu'il pointe vers les bons chemins selon la structure LWS:

```php
<?php

// Chemin vers le dossier privé (à l'intérieur de public_html)
define('APP_ROOT', __DIR__ . '/private');

// Autoloader Composer
require APP_ROOT . '/vendor/autoload.php';

// Configuration de l'application
require_once APP_ROOT . '/src/App/Config/app.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\App\Controllers\HomeController;
use App\App\Controllers\ContactController;

// Container DI
$container = new Container();

// Configuration Twig
$container->set('view', function () {
    $twig = Twig::create(APP_ROOT . '/views', [
        'cache' => APP_ROOT . '/cache/twig',  // Activer le cache en production
        'debug' => false
    ]);
    $twig->addExtension(new \App\App\Twig\RouteExtension());
    return $twig;
});

// Injection des contrôleurs
$container->set(HomeController::class, function ($container) {
    return new HomeController($container->get('view'));
});
$container->set(ContactController::class, function ($container) {
    return new ContactController($container->get('view'));
});

// Application Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

// Middleware Twig
$app->add(TwigMiddleware::createFromContainer($app));

// Configuration des erreurs en production
$app->addErrorMiddleware(false, true, true);

// Chargement des routes
require APP_ROOT . '/src/routes.php';

// Lancement de l'application
$app->run();
```

### 2.3. Créer les dossiers cache et logs

```bash
# Via le terminal cPanel
mkdir -p ~/public_html/private/cache/twig
mkdir -p ~/public_html/private/logs
chmod -R 775 ~/public_html/private/cache
chmod -R 775 ~/public_html/private/logs
```

## Étape 3 : Transfert des fichiers

### 3.1. Connexion FTP

Connectez-vous au serveur avec FileZilla:

- Hôte: ftp.camydia-agency.site
- Identifiant: votre_identifiant_ftp
- Mot de passe: votre_mot_de_passe_ftp
- Port: 21

### 3.2. Méthode de transfert

1. Créez les dossiers `public_html/public` et `public_html/private` s'ils n'existent pas
2. Transférez le contenu du dossier `public` local vers `public_html/public` sur le serveur
3. Placez le fichier index.php à la racine de `public_html`
4. Transférez les dossiers suivants dans `public_html/private`:
   - `src/`
   - `views/`
   - `composer.json`
   - `composer.lock`
   - `.env.production` (renommez-le en `.env` sur le serveur)

> **Important**: Ne transférez PAS le dossier `vendor` - il sera installé directement sur le serveur.

## Étape 4 : Installation des dépendances

### 4.1. Via Terminal SSH (recommandé)

```bash
# Connexion SSH
ssh utilisateur@camydia-agency.site

# Navigation vers le dossier private
cd ~/public_html/private

# Installation de Composer (si nécessaire)
curl -sS https://getcomposer.org/installer | php
mv composer.phar composer

# Installation des dépendances pour la production
php composer install --no-dev --optimize-autoloader
```

### 4.2. Alternative: Installation locale et transfert

Si l'accès SSH n'est pas disponible:

1. Installez les dépendances localement:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. Compressez le dossier vendor:

   ```bash
   tar -czf vendor.tar.gz vendor
   ```

3. Transférez ce fichier via FTP dans `private/`

4. Utilisez le gestionnaire de fichiers cPanel pour extraire l'archive

## Étape 5 : Configuration de la base données

### 5.1. Utilisation de SQLite (simple)

1. Placez votre fichier `database.sqlite` dans le dossier `private`:

   ```bash
   # Via le terminal cPanel
   # Si le fichier est à la racine de public_html
   mv ~/public_html/database.sqlite ~/public_html/private/
   chmod 664 ~/public_html/private/database.sqlite
   ```

2. Mettez à jour le chemin dans le fichier `.env`:
   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=/home/username/public_html/private/database.sqlite
   ```
   (Remplacez `username` par votre nom d'utilisateur d'hébergement)

### 5.2. Utilisation de MySQL (recommandé pour la production)

1. Créez une base de données MySQL via cPanel

2. Utilisez le script de migration pour convertir vos données SQLite:

   ```bash
   cd ~/public_html/private
   php DB_MIGRATE.php
   ```

3. Importez le fichier SQL généré via phpMyAdmin

4. Mettez à jour votre fichier `.env`

## Étape 6 : Vérifications et tests

### 6.1. Permissions des fichiers

Selon la structure réelle constatée sur l'hébergement LWS où le dossier `private` est à l'intérieur de `public_html`:

```bash
# Structure:
# /home/username/public_html/
#    - public/        # Fichiers publics (CSS, JS, images)
#    - private/       # Code source et fichiers sensibles
#    - index.php      # Point d'entrée

# Configuration des permissions des fichiers publics
find ~/public_html/public -type d -exec chmod 755 {} \;
find ~/public_html/public -type f -exec chmod 644 {} \;
chmod 755 ~/public_html/index.php

# Configuration des permissions des fichiers privés
find ~/public_html/private -type d -exec chmod 755 {} \;
find ~/public_html/private -type f -exec chmod 644 {} \;
chmod -R 775 ~/public_html/private/cache
chmod -R 775 ~/public_html/private/logs
```

### 6.2. Configuration SSL

1. Activez Let's Encrypt dans cPanel (SSL/TLS → Let's Encrypt SSL)
2. Vérifiez la redirection HTTPS dans le fichier `.htaccess`

### 6.3. Tests de fonctionnement

Après déploiement, vérifiez:

- Chargement de toutes les pages
- Affichage correct des assets (CSS, JS, images)
- Fonctionnement du formulaire de contact
- Performance et responsive design

### 6.4. Vérification des emails

Testez l'envoi d'emails avec le script fourni:

```bash
cd ~/public_html/private
php SMTP_TEST.php contact@votredomaine.com
```

## Maintenance et mises à jour

### Script automatisé de mise à jour

Un script `update.sh` est disponible pour automatiser les mises à jour:

```bash
#!/bin/bash
# update.sh - Script de mise à jour automatisée pour Camydia Agency

echo "Mise à jour de Camydia Agency..."

# Accès au dossier privé
cd ~/public_html/private

# Mise à jour des dépendances
echo "Mise à jour des dépendances Composer..."
php composer update --no-dev --optimize-autoloader

# Nettoyage des caches
echo "Vidage des caches..."
rm -rf cache/twig/*

# Vérification des permissions
echo "Configuration des permissions..."
find ~/public_html/public -type d -exec chmod 755 {} \;
find ~/public_html/public -type f -exec chmod 644 {} \;
chmod 755 ~/public_html/index.php
chmod -R 775 ~/public_html/private/cache
chmod -R 775 ~/public_html/private/logs

echo "Mise à jour terminée!"
```

Pour utiliser ce script:

1. Téléchargez-le sur votre serveur
2. Rendez-le exécutable: `chmod +x update.sh`
3. Exécutez-le: `./update.sh`

## Résolution des problèmes

### Pages 404 / Routes non fonctionnelles

- Vérifiez la configuration du fichier `.htaccess`
- Assurez-vous que mod_rewrite est activé
- Vérifiez les chemins dans index.php

### Problèmes d'affichage CSS/JS

- Vérifiez que les assets ont été correctement compilés
- Assurez-vous que les chemins relatifs sont corrects

### Problèmes d'envoi d'emails

- Utilisez `SMTP_TEST.php` pour diagnostiquer:
  ```bash
  php SMTP_TEST.php destinataire@exemple.com smtp.hote.com 465 utilisateur motdepasse ssl
  ```

### Base de données inaccessible

- Vérifiez les permissions du fichier SQLite ou les identifiants MySQL
- Confirmez que les extensions PDO sont activées sur le serveur

## Ressources utiles

- [Documentation LWS](https://aide.lws.fr/)
- [Guide PHP sur les hébergements partagés](https://www.php.net/manual/fr/security.hiding.php)
- [Documentation Slim Framework](https://www.slimframework.com/docs/v4/)
- [Documentation Composer](https://getcomposer.org/doc/)
