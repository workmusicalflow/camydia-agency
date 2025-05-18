#!/bin/bash
# Script de mise à jour du site Camydia Agency
# À exécuter via le terminal cPanel pour mettre à jour le site après des modifications

# Couleurs pour les messages
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Script de mise à jour du site Camydia Agency ===${NC}\n"

# Vérifier si nous sommes dans le bon environnement
if [ ! -d "private" ] && [ ! -d "public_html" ]; then
  echo -e "${RED}Erreur: Ce script doit être exécuté depuis le répertoire racine de l'hébergement${NC}"
  echo -e "${YELLOW}Veuillez vous placer dans le répertoire où se trouvent les dossiers 'private' et 'public_html'${NC}"
  exit 1
fi

# Mettre à jour depuis Git si disponible
if [ -d "private/.git" ]; then
  echo -e "${YELLOW}Mise à jour depuis Git...${NC}"
  cd private
  git pull
  cd ..
  echo -e "${GREEN}✓ Code source mis à jour depuis Git${NC}"
else
  echo -e "${YELLOW}Info: Pas de dépôt Git détecté. Utilisez FTP pour mettre à jour les fichiers.${NC}"
fi

# Mettre à jour les dépendances Composer
echo -e "\n${YELLOW}Mise à jour des dépendances Composer...${NC}"
cd private

if [ -f "composer.json" ]; then
  if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
  else
    if [ -f "composer.phar" ]; then
      php composer.phar install --no-dev --optimize-autoloader
    else
      echo -e "${YELLOW}Installation de Composer...${NC}"
      curl -sS https://getcomposer.org/installer | php
      php composer.phar install --no-dev --optimize-autoloader
    fi
  fi
  echo -e "${GREEN}✓ Dépendances Composer mises à jour${NC}"
else
  echo -e "${RED}Erreur: Fichier composer.json non trouvé${NC}"
fi

# Vider les caches
echo -e "\n${YELLOW}Nettoyage des caches...${NC}"
if [ -d "cache/twig" ]; then
  rm -rf cache/twig/*
  echo -e "${GREEN}✓ Cache Twig vidé${NC}"
else
  mkdir -p cache/twig
  echo -e "${GREEN}✓ Dossier cache Twig créé${NC}"
fi

# Créer le dossier logs s'il n'existe pas
if [ ! -d "logs" ]; then
  mkdir -p logs
  echo -e "${GREEN}✓ Dossier logs créé${NC}"
fi

# Définir les permissions correctes
echo -e "\n${YELLOW}Mise à jour des permissions...${NC}"
find ../public_html -type d -exec chmod 755 {} \;
find ../public_html -type f -exec chmod 644 {} \;
chmod 755 ../public_html/index.php

find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 cache
chmod -R 775 logs

echo -e "${GREEN}✓ Permissions mises à jour${NC}"

# Vérifier la configuration
echo -e "\n${YELLOW}Vérification de la configuration...${NC}"
if [ -f ".env" ]; then
  echo -e "${GREEN}✓ Fichier .env détecté${NC}"
  
  # Vérifier la base de données SQLite
  DB_PATH=$(grep DB_DATABASE .env | cut -d '=' -f2)
  if [[ "$DB_PATH" == *"sqlite"* ]]; then
    if [ -f "$DB_PATH" ]; then
      echo -e "${GREEN}✓ Base de données SQLite trouvée à $DB_PATH${NC}"
      # Vérifier que le fichier n'est pas dans public_html
      if [[ "$DB_PATH" == *"public_html"* ]]; then
        echo -e "${RED}⚠️ ATTENTION: La base de données SQLite est dans le dossier public!${NC}"
        echo -e "${YELLOW}   Déplacez-la dans le dossier private pour plus de sécurité.${NC}"
      fi
    else
      echo -e "${RED}⚠️ Base de données SQLite non trouvée à $DB_PATH${NC}"
      # Vérifier si elle est dans un autre emplacement courant
      if [ -f "../public_html/database.sqlite" ]; then
        echo -e "${YELLOW}   Base de données trouvée dans public_html/database.sqlite${NC}"
        echo -e "${YELLOW}   Considérez la déplacer vers private/ et mettre à jour le chemin dans .env${NC}"
      elif [ -f "database.sqlite" ]; then
        echo -e "${YELLOW}   Base de données trouvée dans private/database.sqlite${NC}"
        echo -e "${YELLOW}   Mettez à jour le chemin dans .env pour pointer vers ce fichier${NC}"
      fi
    fi
  fi
else
  echo -e "${RED}Attention: Fichier .env non trouvé!${NC}"
  echo -e "${YELLOW}Créez un fichier .env avec la configuration appropriée pour l'environnement de production${NC}"
fi

if [ -f "../public_html/.htaccess" ]; then
  echo -e "${GREEN}✓ Fichier .htaccess détecté${NC}"
else
  echo -e "${RED}Attention: Fichier .htaccess non trouvé dans public_html!${NC}"
  echo -e "${YELLOW}Créez un fichier .htaccess pour assurer le bon fonctionnement des routes${NC}"
fi

# Test d'accès au site
echo -e "\n${YELLOW}Test d'accès au site...${NC}"
if command -v curl &> /dev/null; then
  SITE_URL=$(grep APP_URL .env 2>/dev/null | cut -d '=' -f2)
  if [ -n "$SITE_URL" ]; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL")
    if [ "$HTTP_CODE" -eq 200 ]; then
      echo -e "${GREEN}✓ Site accessible (HTTP 200 OK)${NC}"
    else
      echo -e "${RED}Attention: Site inaccessible ou erreur HTTP $HTTP_CODE${NC}"
    fi
  else
    echo -e "${YELLOW}Info: URL du site non définie dans .env${NC}"
  fi
else
  echo -e "${YELLOW}Info: curl non disponible, impossible de tester l'accès au site${NC}"
fi

echo -e "\n${GREEN}=== Mise à jour terminée! ===${NC}"
echo -e "${YELLOW}N'oubliez pas de vérifier manuellement que le site fonctionne correctement.${NC}"
echo -e "${YELLOW}Si vous rencontrez des problèmes, consultez le fichier DEPLOYMENT.md pour des solutions.${NC}"