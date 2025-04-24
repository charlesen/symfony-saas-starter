# Contributing to Symfony SaaS Starter

Thank you for your interest in contributing to this public Symfony SaaS Starter project! Your help is invaluable to make this project robust, modern, and useful to the community.

## Quick Start

- **Project Repo:** https://github.com/charlesen/symfony-sass-starter
- **Stack:** Symfony 7.2+, UX LiveComponent, Tailwind CSS, Stimulus, Doctrine ORM (MySQL), Docker, Webpack Encore

---

## How to Contribute

### 1. Fork & Clone
- Fork the repository to your own GitHub account.
- Clone your fork locally:
  ```bash
  git clone https://github.com/<your-username>/symfony-sass-starter.git
  cd symfony-sass-starter
  ```

### 2. Set Up the Project
- Copy the example environment file:
  ```bash
  cp .env.example .env
  ```
- Start the stack (requires Docker & Docker Compose):
  ```bash
  docker compose up --build
  ```
- Install Composer dependencies:
  ```bash
  docker compose exec php composer install
  ```
- Install JS/CSS dependencies:
  ```bash
  docker compose exec node npm install
  docker compose exec node npm run build
  ```
- The app should be running at [http://localhost:8080](http://localhost:8080)

### 3. Create a Branch
- Always create a new branch for your feature or fix:
  ```bash
  git checkout -b feat/my-feature
  # or
  git checkout -b fix/issue-description
  ```

### 4. Make Your Changes
- Follow the coding standards (PSR-12 for PHP, Prettier for JS/Twig).
- Do not commit secrets or credentials.
- Add or update tests if relevant.
- Keep JS logic in Stimulus controllers, not inline in HTML (see Windsurf rule).

### 5. Test Your Changes
- Run tests:
  ```bash
  docker compose exec php bin/phpunit
  ```
- Manually test your feature in the browser.

### 6. Commit & Push
- Write clear, descriptive commit messages.
- Push to your fork:
  ```bash
  git push origin <branch-name>
  ```

### 7. Open a Pull Request (PR)
- Go to the original repo: https://github.com/charlesen/symfony-sass-starter
- Click "Compare & pull request".
- Fill in the PR template:
  - **What does this PR do?**
  - **How to test/review?**
  - **Related issues/links**
- Link to any relevant issues.
- Be open to feedback and ready to improve your PR!

---

## Code of Conduct
- Be respectful and constructive.
- All contributions are welcome: code, docs, tests, bugs, ideas.
- No harassment, trolling, or discrimination will be tolerated.

## Good Practices
- Keep PRs focused and atomic (one feature/fix per PR).
- Write and update documentation when needed.
- Add comments where the logic is non-trivial.
- Use English for code and docs, except for explicit user-facing translations.

## Need Help?
- Open an issue on GitHub.
- Or contact the maintainers directly.

---

Thank you for helping to make this Symfony SaaS Starter better for everyone! 🚀
