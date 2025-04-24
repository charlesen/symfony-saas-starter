#!/bin/bash

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}🛑 Arrêt de l'environnement de développement...${NC}"

# Arrêter les containers
docker compose down

echo -e "${GREEN}✅ Environnement arrêté avec succès${NC}"
