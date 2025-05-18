# Guide de nettoyage de la codebase Camydia Agency

## 1. Documentation importante à conserver

Ces fichiers contiennent des informations importantes pour le développement et le déploiement:

| Fichier | Description | Action recommandée |
|---------|-------------|-------------------|
| DEPLOYMENT.md | Documentation de déploiement | À ajouter à Git |
| INSTRUCTIONS-FINALES.md | Corrections pour le déploiement | Fusionner avec DEPLOYMENT.md |
| CORRECTION-IMAGES.md | Détails sur la correction des images | Fusionner avec DEPLOYMENT.md |
| DB_MIGRATE.php | Script de migration de base de données | À ajouter à Git |
| SMTP_TEST.php | Script de test SMTP | À ajouter à Git |
| SMTP_TEST.md | Documentation du test SMTP | À ajouter à Git |
| update.sh | Script de mise à jour du site | À ajouter à Git |

## 2. Fichiers de production à conserver en local (ne pas committer)

Ces fichiers sont utiles en tant que référence mais ne devraient pas être sous contrôle de version:

| Fichier | Description | Action recommandée |
|---------|-------------|-------------------|
| production-HomeController.php | Version de prod du contrôleur | Conserver en local |
| production-routes.php | Version de prod des routes | Conserver en local |
| production-version-index.php | Version de prod de index.php | Conserver en local |
| production-version-routes.php | Version de prod des routes | Conserver en local |
| .env.production | Variables d'environnement de prod | Conserver en local |
| .htaccess-version-production | Version de prod du .htaccess | Conserver en local |
| htaccess-public.txt | .htaccess pour dossier public | Conserver en local |
| htaccess-root.txt | .htaccess pour racine | Conserver en local |

## 3. Fichiers temporaires à supprimer

Ces fichiers sont temporaires ou peuvent être recréés à partir d'autres sources:

| Fichier | Description | Action recommandée |
|---------|-------------|-------------------|
| .DS_Store | Fichiers macOS temporaires | Ajouter à .gitignore et supprimer |
| public/.DS_Store | Fichiers macOS temporaires | Ajouter à .gitignore et supprimer |
| public/images/.DS_Store | Fichiers macOS temporaires | Ajouter à .gitignore et supprimer |
| public/images/content/about/.DS_Store | Fichiers macOS temporaires | Ajouter à .gitignore et supprimer |
| public/images/content/services/.DS_Store | Fichiers macOS temporaires | Ajouter à .gitignore et supprimer |
| src/.DS_Store | Fichiers macOS temporaires | Ajouter à .gitignore et supprimer |
| fix-partenaires-chemin.php | Notes temporaires | Supprimer après intégration |
| fix-partenaires.md | Notes temporaires | Supprimer après intégration |
| image-fix.md | Notes temporaires | Supprimer après intégration |
| page-specific-meta.md | Notes temporaires | Conserver comme référence |
| CORRECTION-DEPLOIEMENT.md | Informations déjà fusionnées | Supprimer après intégration |

## 4. Modifications à valider

Ces fichiers modifiés devraient être validés par Git:

| Fichier | Description | Action recommandée |
|---------|-------------|-------------------|
| views/layout.twig | Template principal avec balises meta | Valider |
| public/.htaccess | Configuration des redirections | Valider |
| public/css/main.css | Styles CSS | Valider |

## 5. Plan d'action pour le nettoyage

1. **Fusionner la documentation**:
   ```bash
   # Créer une version finale de la documentation de déploiement
   cat DEPLOYMENT.md INSTRUCTIONS-FINALES.md > DEPLOYMENT_FINAL.md
   # Éditer et formater ce fichier selon vos besoins
   ```

2. **Ajouter les fichiers importants à Git**:
   ```bash
   git add DEPLOYMENT_FINAL.md DEVBOOK.md DB_MIGRATE.php SMTP_TEST.php SMTP_TEST.md update.sh
   ```

3. **Valider les modifications des fichiers de code**:
   ```bash
   git add views/layout.twig public/.htaccess public/css/main.css
   ```

4. **Mettre à jour .gitignore pour ignorer les fichiers temporaires**:
   ```bash
   echo ".DS_Store" >> .gitignore
   echo "**/.DS_Store" >> .gitignore
   echo ".env.production" >> .gitignore
   echo "*production*" >> .gitignore
   ```

5. **Supprimer les fichiers .DS_Store**:
   ```bash
   find . -name ".DS_Store" -type f -delete
   ```

6. **Conserver une copie des fichiers de production dans un dossier dédié**:
   ```bash
   mkdir -p production-files
   cp production-*.php .htaccess-version-production .env.production htaccess-*.txt production-files/
   ```

7. **Effectuer un commit avec un message clair**:
   ```bash
   git commit -m "[DEPLOY] Amélioration de la documentation et ajout des balises meta OpenGraph"
   ```

## 6. Pratiques recommandées pour l'avenir

1. **Documenter tous les changements** dans un fichier CHANGELOG.md
2. **Maintenir la documentation à jour** avec les nouveaux déploiements
3. **Utiliser des branches Git** pour les développements de fonctionnalités
4. **Eviter de modifier directement** les fichiers sur le serveur de production
5. **Automatiser le déploiement** avec le script update.sh
6. **Vider le cache Twig** après chaque déploiement
7. **Tester les changements** dans un environnement de préproduction avant la mise en ligne

Cette procédure permettra de garder votre codebase propre et bien organisée pour les développements futurs.