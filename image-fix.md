# Correction des chemins d'images pour le site Camydia

## Problème identifié
Les images ne s'affichent pas correctement sur les pages contact et about car leurs chemins commencent par `/images/` alors qu'ils devraient commencer par `/public/images/` sur le serveur de production.

## Solution
Il existe deux approches pour résoudre ce problème:

### Solution 1: Modifier les templates Twig
Modifier tous les fichiers Twig pour changer les chemins d'images de `/images/` vers `/public/images/`.

1. Fichiers à modifier:
   - `views/contact.twig`: ligne 218
   - `views/about.twig`: lignes 60-69, 85, 115, 158-162
   - Et tout autre fichier faisant référence à des images

2. Exemple pour about.twig:
```twig
<img src="/public/images/content/about/others/fe1c7.jpg" alt="Événement organisé par Camydia Agency" class="w-full h-full object-cover object-top transition-transform duration-500 ease-in-out group-hover:scale-105">
```

### Solution 2: Modifier la classe Routes.php (recommandée)
Modifier la classe `Routes.php` pour centraliser les changements de chemins d'assets:

1. Mettre à jour le fichier `src/App/Config/Routes.php` avec le contenu que nous avons déjà préparé dans `production-routes.php`:
```bash
cp /chemin/vers/production-routes.php ~/public_html/private/src/App/Config/Routes.php
```

### Solution 3: Configurer un alias dans .htaccess (alternative)
Si vous préférez ne pas modifier le code source, vous pouvez configurer un alias dans le fichier .htaccess à la racine:

```apache
# Ajouter dans public_html/.htaccess
# Redirection des chemins /images/ vers /public/images/
RewriteRule ^images/(.*)$ public/images/$1 [L]
```

## Étapes à suivre

1. Connectez-vous au serveur via SSH ou terminal cPanel
2. Utilisez la solution 2 (recommandée):
   ```bash
   cp /chemin/vers/production-routes.php ~/public_html/private/src/App/Config/Routes.php
   ```
3. Vérifiez les templates Twig qui utilisent directement des chemins d'images (sans passer par la classe Routes):
   ```bash
   grep -r "/images/" ~/public_html/private/views/
   ```
4. Modifiez ces templates pour utiliser le préfixe `/public/images/`
5. Videz le cache Twig:
   ```bash
   rm -rf ~/public_html/private/cache/twig/*
   ```
6. Testez à nouveau les pages pour vérifier que les images s'affichent correctement

## Test des URLs d'images
Pour vérifier que les images sont accessibles:

✅ Chemin fonctionnel: `https://camydia-agency.site/public/images/content/about/others/82407.png`
❌ Chemin incorrect: `https://camydia-agency.site/images/content/about/others/82407.png`