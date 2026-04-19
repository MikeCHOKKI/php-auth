# php-auth

> Stack : php | none | none | none

> Architecture professionnelle de type RBAC (Role-Based Access Control) avec JWT RS256 (asymétrique), rotation sécurisée des refresh tokens, et conformité PSR-12 / SOLID.

## 🚀 Caractéristiques

- **RBAC Développé** — Users, Roles, Permissions avec relations many-to-many
- **JWT RS256** — Signatures RSA 2048 bits (clés privée/publique)
- **Rotation Refresh Tokens** — Invalidation automatique avec détection de réutilisation
- **SOLID & PSR-12** — Clean Architecture, Repository Pattern, Dependency Injection
- **Tests Automatisés** — PHPUnit, couverture > 80% objectif
- **CI/CD** — GitHub Actions (lint, analyse statique, tests, audit sécurité)
- **Dockerisé** — PHP 8.3-FPM + PostgreSQL 15 + Nginx

## 📁 Structure du Projet

```
php-auth/
├── src/
│   ├── Domain/           # Entités, Value Objects, Repository Interfaces
│   ├── Application/      # Services métier, DTOs, Ports
│   ├── Infrastructure/   # Repositories PostgreSQL, JWT, Controllers
│   └── Config/
├── tests/
│   ├── Unit/            # Tests unitaires
│   ├── Integration/     # Tests d'intégration
│   └── Functional/      # Tests fonctionnels
├── docs/
│   ├── uml/             # Diagrammes Mermaid
│   ├── decisions/       # ADRs
│   └── architecture.md
├── docker/              # Dockerfile PHP, config Nginx
├── migrations/          # SQL PostgreSQL
├── public/index.php     # Point d'entrée
└── docker-compose.yml
```

## ⚡ Démarrage Rapide

### Prérequis
- Docker & Docker Compose
- Make

### Installation

```bash
# 1. Cloner et entrer dans le projet
cd php-auth

# 2. Copier la configuration
cp .env.example .env

# 3. Générer les clés RSA pour JWT
make keys

# 4. Démarrer l'environnement
make dev

# 5. Lancer les migrations (auto via Docker entrypoint)
make db-migrate
```

L'API est disponible sur : `http://localhost:8080`

## 🔐 Endpoints API

### Authentification
| Méthode | Endpoint            | Description                     |
|---------|---------------------|---------------------------------|
| POST    | `/api/auth/login`   | Connexion (email/password)      |
| POST    | `/api/auth/refresh` | Rotation du refresh token       |
| POST    | `/api/auth/logout`  | Déconnexion (révocation tokens) |

### Utilitaires (protégés)
| Méthode | Endpoint           | Description                          |
|---------|--------------------|--------------------------------------|
| GET     | `/api/me`          | Informations utilisateur connecté    |
| GET     | `/api/permissions` | Permissions actives de l'utilisateur |

### Exemples

```bash
# Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Réponse : {"accessToken":"...","refreshToken":"...","expiresIn":900}

# Accéder à /api/me (protégé)
curl http://localhost:8080/api/me \
  -H "Authorization: Bearer <accessToken>"

# Refresh token
curl -X POST http://localhost:8080/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{"refreshToken":"<refreshToken>"}'
```

## 🧪 Tests

```bash
# Tous les tests
make test

# Tests unitaires uniquement
make test-unit

# Couverture
make coverage

# Lint PSR-12
make lint

# Analyse statique (PHPStan level 8)
make static-analysis

# Pipeline CI complète
make ci
```

## 🏗️ Architecture

### Clean Architecture
```
┌─────────────────────────────────────┐
│  Infrastructure                     │  ← Controllers, Repositories, JWT
│  (Frameworks, Drivers)             │
├─────────────────────────────────────┤
│  Application                        │  ← Services, DTOs, Ports
│  (Use Cases)                        │
├─────────────────────────────────────┤
│  Domain                             │  ← Entities, Value Objects
│  (Enterprise Business Rules)        │
└─────────────────────────────────────┘
```

### Flux JWT (RS256)
1. **Login** → Vérification credentials → Génération access token + refresh token
2. **API Call** → Validation JWT via clé publique → Extraction claims
3. **Refresh** → Vérification refresh token → Rotation (nouveau token, ancien révoqué)
4. **Reuse Detection** → Si token déjà utilisé → Révocation de toute la famille

### Sécurité
- Passwords hashés avec Argon2id
- Refresh tokens stockés avec SHA-256 (pas en clair)
- Headers de sécurité (CSP, HSTS, X-Frame-Options)
- Rate limiting configurable
- Pas de secrets dans le code

## 📚 Documentation

- [implementation_plan.md](implementation_plan.md) — Plan de développement
- [docs/architecture.md](docs/architecture.md) — Décisions architecturales
- [docs/api.md](docs/api.md) — Documentation API complète
- [docs/uml/](docs/uml/) — Diagrammes UML (classes, ERD, séquence)

## 🛠️ Scripts Make

| Commande | Description |
|----------|-------------|
| `make dev` | Démarrer Docker (dev) |
| `make install` | Installer Composer dependencies |
| `make test` | Lancer tests PHPUnit |
| `make lint` | Linter PSR-12 |
| `make lint-fix` | Correction auto PSR-12 |
| `make keys` | Générer clés RSA JWT |
| `make db-migrate` | Lancer migrations |
| `make ci` | Pipeline complète (lint + analyse + test) |

## 📄 License

MIT — Voir [LICENSE](LICENSE)

---

*Architecture conçue pour démontrer les bonnes pratiques PHP professionnelles.*

Ce projet utilise Windsurf. Prompt d'init :
> "Prends connaissance de `.windsurfrules` et `PROJET.md`, lis `task.md` et `walkthrough.md`, puis confirme ton état de compréhension."

Commandes disponibles : `/audit` `/feat` `/fix` `/ui` `/securite` `/roadmap` et plus dans [COMMANDES.md](COMMANDES.md).
