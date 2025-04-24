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

# Charger les variables d'environnement
if [ -f .env.local ]; then
    export $(cat .env.local | grep -v '^#' | xargs)
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

# Installation des dépendances Node.js
echo -e "${YELLOW}📚 Installation des dépendances Node.js...${NC}"
docker compose exec -T php yarn install

# Création de la base de données avec l'utilisateur root
echo -e "${YELLOW}🗄️  Création de la base de données...${NC}"
if ! docker compose exec -T database bash -c "mysql -u root -p\${MYSQL_ROOT_PASSWORD} -e \"CREATE DATABASE IF NOT EXISTS \\\`\${MYSQL_DATABASE}\\\`\""; then
    echo -e "${RED}❌ Erreur lors de la création de la base de données${NC}"
    exit 1
fi

# Attribution des droits à l'utilisateur app
echo -e "${YELLOW}👤 Configuration des droits utilisateur...${NC}"
if ! docker compose exec -T database bash -c "mysql -u root -p\${MYSQL_ROOT_PASSWORD} -e \"GRANT ALL PRIVILEGES ON \\\`\${MYSQL_DATABASE}\\\`.* TO '\${MYSQL_USER}'@'%'\""; then
    echo -e "${RED}❌ Erreur lors de l'attribution des droits${NC}"
    exit 1
fi

# Rafraîchir les privilèges
docker compose exec -T database bash -c "mysql -u root -p\${MYSQL_ROOT_PASSWORD} -e \"FLUSH PRIVILEGES\""

echo -e "${GREEN}✅ Base de données prête${NC}"

# Application des migrations
echo -e "${YELLOW}🔄 Application des migrations...${NC}"
docker compose exec -T php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Migrations appliquées${NC}"
else
    echo -e "${RED}❌ Erreur lors de l'application des migrations${NC}"
    exit 1
fi

# Chargement des fixtures en dev
if [ "${APP_ENV:-dev}" = "dev" ] && [ -d "src/DataFixtures" ]; then
    echo -e "${YELLOW}🌱 Chargement des fixtures...${NC}"
    docker compose exec -T php bin/console doctrine:fixtures:load --no-interaction
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Fixtures chargées${NC}"
    else
        echo -e "${RED}❌ Erreur lors du chargement des fixtures${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✅ Environnement de développement prêt !${NC}"
echo -e "${YELLOW}📝 Services disponibles :${NC}"
echo -e "   • Application : http://localhost:8080"
echo -e "   • Adminer    : http://localhost:8081"
echo -e "   • Mailhog    : http://localhost:8025"
echo -e "   • MySQL      : localhost:3306"
echo -e "   • Redis      : localhost:6379"
