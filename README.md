# Symfony SaaS Boilerplate

> Base SaaS Symfony + Tailwind pour construire rapidement des produits modernes et extensibles.
> Utilisé pour propulser [PostGenius] — Générateur de posts LinkedIn optimisés via IA.

---

## 🚀 Stack technique

- **Framework** : Symfony 7.2+
- **Live Components** : Symfony UX LiveComponent
- **UI** : Tailwind CSS + Stimulus
- **Base de données** : Doctrine ORM (MySQL/PostgreSQL)
- **Paiement** : Stripe Checkout & Billing Portal
- **Authentification** :
  - Email + mot de passe (avec validation)
  - Google OAuth (via OAuth2 Client)
- **Infrastructure** :
  - Docker + Compose (`php`, `mysql`, `mailpit`)
  - Webpack Encore

---

## 🛠️ Installation locale

```bash
# Clone le projet
git clone https://github.com/charlesen/symfony-sass-starter.git
cd symfony-sass-starter

# Lancer les conteneurs Docker
docker compose up -d

# Installer les dépendances PHP
docker compose exec php composer install

# Installer les dépendances JS
docker compose exec php yarn install
docker compose exec php yarn dev

# Créer la base et lancer les migrations
docker compose exec php bin/console doctrine:database:create
docker compose exec php bin/console doctrine:migrations:migrate

# Accéder au projet
https://localhost:8000
```

## ✅ Fonctionnalités SaaS

### 🧑‍💻 Authentification

- [x] Connexion / Inscription par email
- [x] Confirmation d'adresse email
- [ ] Mot de passe oublié
- [ ] Connexion via Google
- [ ] Connexion via LinkedIn

### ⚙️ Compte & Préférences

- [ ] Modification des infos utilisateur
- [ ] Préférences de langue / thème
- [ ] Suppression du compte

### 🧾 Abonnement / Stripe

- [ ] Intégration Stripe Checkout
- [ ] Plans mensuels / annuels
- [ ] Portail client (Stripe billing portal)
- [ ] Webhooks Stripe : création, annulation, renouvellement
- [ ] Factures téléchargeables
- [ ] Essai gratuit (trial)

### 📬 Emails

- [ ] Email de bienvenue
- [ ] Confirmation d’email
- [ ] Notifications (réinitialisation, abonnement)

### 🧱 Permissions

- [x] `ROLE_USER` & `ROLE_ADMIN`
- [ ] Gestion d'équipes
- [ ] Multi-tenant support

### 📊 Dashboard

- [ ] Aperçu des derniers contenus générés
- [ ] Statistiques d’usage

### 💡 Modules IA

- [x] Génération de posts LinkedIn
- [x] Historique des posts
- [x] Favoris
- [x] Édition de contenu
- [x] Infinite scroll + modale responsive
