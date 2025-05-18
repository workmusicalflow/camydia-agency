# Correction du déploiement sur LWS

Ce document détaille les étapes nécessaires pour corriger l'erreur 500 sur le site déployé.

## Problèmes identifiés

1. **Structure des dossiers incorrecte**:
   - Le fichier `index.php` pointe vers le mauvais chemin pour le dossier private
   - Les fichiers de configuration utilisent des chemins qui ne correspondent pas à la structure de production

2. **Configuration des routes**:
   - Le fichier `routes.php` utilise `$router` alors que `index.php` de production utilise `$app`
   - Double définition des gestionnaires d'erreur

3. **Chemins d'assets incorrects**:
   - Les assets sont référencés avec des chemins comme `/css/` alors qu'ils devraient être `/public/css/`

## Fichiers corrigés

Nous avons préparé plusieurs fichiers corrigés pour la production:

1. `production-version-index.php` - Fichier index.php corrigé pour la racine de public_html
2. `production-version-routes.php` - Routes sans redéfinition des gestionnaires d'erreur
3. `production-routes.php` - Classe Routes avec chemins d'assets corrigés
4. `htaccess-root.txt` - Fichier .htaccess pour la racine de public_html
5. `htaccess-public.txt` - Fichier .htaccess pour le dossier public

## Étapes de correction

1. **Connectez-vous via SSH ou terminal cPanel**:
   ```bash
   ssh utilisateur@camydia-agency.site
   ```

2. **Sauvegardez les fichiers existants**:
   ```bash
   cd ~/public_html
   cp index.php index.php.bak
   cp -r private private.bak
   ```

3. **Modifiez le fichier index.php**:
   Remplacez le contenu par celui du fichier `production-version-index.php`
   ```bash
   # Via FTP ou éditeur cPanel
   nano index.php
   ```

4. **Corrigez le fichier routes.php**:
   ```bash
   cp /chemin/vers/production-version-routes.php ~/public_html/private/src/routes.php
   ```

5. **Corrigez la classe Routes**:
   ```bash
   cp /chemin/vers/production-routes.php ~/public_html/private/src/App/Config/Routes.php
   ```

6. **Mettez en place les fichiers .htaccess**:
   ```bash
   # .htaccess principal à la racine
   cp /chemin/vers/htaccess-root.txt ~/public_html/.htaccess
   
   # .htaccess pour le dossier public
   cp /chemin/vers/htaccess-public.txt ~/public_html/public/.htaccess
   ```

7. **Vérifiez les permissions**:
   ```bash
   chmod 755 ~/public_html/index.php
   find ~/public_html/public -type d -exec chmod 755 {} \;
   find ~/public_html/public -type f -exec chmod 644 {} \;
   find ~/public_html/private -type d -exec chmod 755 {} \;
   find ~/public_html/private -type f -exec chmod 644 {} \;
   chmod -R 775 ~/public_html/private/cache
   chmod -R 775 ~/public_html/private/logs
   ```

8. **Vérifiez les dossiers nécessaires**:
   ```bash
   mkdir -p ~/public_html/private/cache/twig
   mkdir -p ~/public_html/private/logs
   ```

9. **Videz les caches**:
   ```bash
   rm -rf ~/public_html/private/cache/twig/*
   ```

## Vérification

Après avoir effectué ces modifications, visitez votre site pour vérifier que l'erreur 500 est résolue:

```
https://www.camydia-agency.site/
```

Si le problème persiste, consultez les logs d'erreur:
```bash
tail -50 ~/logs/error_log
```