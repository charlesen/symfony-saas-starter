# Symfony SaaS Starter

Boilerplate Symfony pour applications SaaS modernes.

## ✨ Fonctionnalités principales

- 👤 **Authentification complète**
  - Inscription/Connexion par email
  - Réinitialisation de mot de passe
  - Vérification d'email
  - OAuth (Google, GitHub)

- 💳 **Gestion des abonnements**
  - Intégration Stripe
  - Plans et tarification
  - Facturation récurrente
  - Période d'essai

- 👥 **Gestion des utilisateurs**
  - Profils utilisateurs
  - Rôles et permissions
  - Préférences utilisateur
  - Multi-compte

- 🎛️ **Dashboard**
  - Vue d'ensemble
  - Statistiques d'utilisation
  - Gestion des abonnements
  - Historique des paiements

- 🔧 **Administration**
  - Gestion des utilisateurs
  - Monitoring
  - Configuration système
  - Logs et audit

- 📧 **Notifications**
  - Emails transactionnels
  - Notifications in-app
  - Templates personnalisables
  - File d'attente des messages

## 🚀 Installation

1. Clonez le dépôt
```bash
git clone https://github.com/votre-repo/symfony-sass-starter.git
cd symfony-sass-starter
```

2. Lancez l'environnement de développement
```bash
./docker/scripts/start.sh
```

Le script va :
- Créer un fichier .env.local si nécessaire
- Démarrer les conteneurs Docker
- Installer les dépendances
- Créer la base de données
- Appliquer les migrations
- Charger les fixtures (en environnement de développement)

## 🔑 Comptes de test

Deux comptes sont créés automatiquement en environnement de développement :

### Administrateur
- Email : admin@example.com
- Mot de passe : admin
- Rôle : ROLE_ADMIN

### Utilisateur standard
- Email : user@example.com
- Mot de passe : user123
- Rôle : ROLE_USER

## 🛠️ Services disponibles

- Application : http://localhost:8080
- Adminer (gestion BDD) : http://localhost:8081
- Mailhog (emails) : http://localhost:8025
- MySQL : localhost:3306
- Redis : localhost:6379

## 📦 Stack technique

- Symfony 7.2+
- Symfony UX LiveComponent
- Tailwind CSS + Stimulus
- Doctrine ORM (MySQL)
- Docker + Compose
- Webpack Encore

## 🧪 Tests

```bash
# Tests unitaires
docker compose exec php bin/phpunit

# Tests fonctionnels
docker compose exec php bin/phpunit --testsuite=functional
```

## 🔄 Scripts utiles

- `./docker/scripts/start.sh` : Démarrer l'environnement
- `./docker/scripts/stop.sh` : Arrêter l'environnement
- `./docker/scripts/reset.sh` : Réinitialiser complètement l'environnement

## 🤝 Contribuer

Vous souhaitez participer à l'évolution du projet ? Consultez le fichier [CONTRIBUTING.md](CONTRIBUTING.md) pour connaître les bonnes pratiques, le workflow de contribution et les règles de collaboration.

## 📝 Documentation

Pour plus d'informations sur le développement, consultez [DEVBOOK.md](DEVBOOK.md).

## 📄 Licence

Ce projet est distribué sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.
