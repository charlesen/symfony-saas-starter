#!/bin/bash

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}🔄 Réinitialisation de l'environnement de développement...${NC}"

# Demander confirmation
read -p "⚠️  Cette action va supprimer toutes les données (volumes, cache, etc.). Continuer ? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo -e "${RED}❌ Opération annulée${NC}"
    exit 1
fi

# Arrêter et supprimer les containers
echo -e "${YELLOW}📦 Suppression des containers...${NC}"
docker compose down -v

# Nettoyer le cache Symfony
echo -e "${YELLOW}🧹 Nettoyage du cache Symfony...${NC}"
rm -rf var/cache/*

# Nettoyer les dépendances
echo -e "${YELLOW}🧹 Nettoyage des dépendances...${NC}"
rm -rf vendor node_modules

# Supprimer les fichiers générés
echo -e "${YELLOW}🧹 Suppression des fichiers générés...${NC}"
rm -rf public/build

echo -e "${GREEN}✅ Environnement réinitialisé avec succès${NC}"
echo -e "${YELLOW}💡 Utilisez ./docker/scripts/start.sh pour redémarrer l'environnement${NC}"
