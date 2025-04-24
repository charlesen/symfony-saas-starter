#!/bin/bash

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}🚀 Démarrage de l'environnement de développement...${NC}"

# Vérifier si .env.local existe, sinon le créer à partir de .env.local.example
if [ ! -f .env.local ]; then
    echo -e "${YELLOW}⚙️  Fichier .env.local non trouvé, création à partir de .env.local.example...${NC}"
    if [ -f .env.local.example ]; then
        cp .env.local.example .env.local
        echo -e "${GREEN}✅ Fichier .env.local créé${NC}"
        echo -e "${YELLOW}⚠️  N'oubliez pas de configurer vos variables dans .env.local${NC}"
    else
        echo -e "${RED}❌ Fichier .env.local.example non trouvé${NC}"
        exit 1
    fi
fi

# Générer APP_SECRET si non défini
if ! grep -q "^APP_SECRET=" .env.local || grep -q "^APP_SECRET=!ChangeThis!" .env.local; then
    echo -e "${YELLOW}🔑 Génération d'un nouveau APP_SECRET...${NC}"
    NEW_SECRET=$(openssl rand -hex 16)
    sed -i "s/^APP_SECRET=.*$/APP_SECRET=$NEW_SECRET/" .env.local
    echo -e "${GREEN}✅ Nouveau APP_SECRET généré${NC}"
fi

# Démarrer les containers
echo -e "${YELLOW}📦 Démarrage des containers Docker...${NC}"
docker compose up -d

# Attendre que les services soient prêts
echo -e "${YELLOW}⏳ Attente de la disponibilité des services...${NC}"
timeout=120
elapsed=0
while ! docker compose exec -T database mysqladmin ping -h localhost --silent; do
    sleep 1
    elapsed=$((elapsed+1))
    if [ "$elapsed" -ge "$timeout" ]; then
        echo -e "${RED}❌ Timeout en attendant MySQL${NC}"
        exit 1
    fi
done

# Installation des dépendances
echo -e "${YELLOW}📚 Installation des dépendances PHP...${NC}"
docker compose exec -T php composer install

echo -e "${YELLOW}📚 Installation des dépendances Node.js...${NC}"
docker compose exec -T php yarn install

# Construction des assets
echo -e "${YELLOW}🔨 Construction des assets...${NC}"
docker compose exec -T php yarn dev

# Migrations de la base de données
echo -e "${YELLOW}🔄 Exécution des migrations...${NC}"
docker compose exec -T php bin/console doctrine:migrations:migrate --no-interaction

echo -e "${GREEN}✅ Environnement de développement prêt !${NC}"
echo -e "${GREEN}📝 Services disponibles :${NC}"
echo -e "   • Application : ${YELLOW}http://localhost:8080${NC}"
echo -e "   • Adminer    : ${YELLOW}http://localhost:8081${NC}"
echo -e "   • Mailhog    : ${YELLOW}http://localhost:8025${NC}"
echo -e "   • MySQL      : ${YELLOW}localhost:3306${NC}"
echo -e "   • Redis      : ${YELLOW}localhost:6379${NC}"
