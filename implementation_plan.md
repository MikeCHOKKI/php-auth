# Plan d'Implémentation — php-auth

> Architecture RBAC + JWT RS256 + Rotation Refresh Tokens
> Date : 2026-04-20

---

## 🎯 Objectif

Système d'authentification professionnel démontrant :

- Architecture **RBAC découplée** (Users ↔ Roles ↔ Permissions)
- **JWT RS256** asymétrique avec clés RSA
- **Rotation sécurisée** des refresh tokens (invalidation par réutilisation)
- **SOLID**, **PSR-12**, **Clean Architecture**
- Tests automatisés + CI/CD
- Dockerisé et prêt pour production

---

## 📋 Phases d'Implémentation

### Phase 1 — Modélisation (Diagrammes UML)

- [x] Diagramme de classes (Domain, Application, Infrastructure)
- [x] ERD PostgreSQL (RBAC relations many-to-many)
- [x] Diagramme de séquence (login → refresh → validate)
- [x] Use case (authentification, autorisation, gestion RBAC)

### Phase 2 — Infrastructure Docker

- [ ] docker-compose.yml (PHP 8.3-FPM, PostgreSQL 15, Nginx)
- [ ] Dockerfile PHP (extensions: pdo_pgsql, sodium, opcache)
- [ ] Configuration Nginx (PHP-FPM, headers sécurité)
- [ ] .env.example adapté (PHP/PostgreSQL, pas Node.js)
- [ ] Makefile corrigé pour PHP

### Phase 3 — Configuration Projet

- [ ] composer.json avec autoloading PSR-4
- [ ] Dépendances : firebase/php-jwt, vlucas/phpdotenv, psr/http-message
- [ ] phpunit.xml configuration
- [ ] phpcs.xml (PSR-12 linting)
- [ ] Scripts Composer (test, lint)

### Phase 4 — Domain Layer (Core)

- [ ] Entities : User, Role, Permission, RefreshToken
- [ ] Value Objects : Email, PasswordHash, JwtToken, UserId
- [ ] Repository Interfaces (Ports) : UserRepository, RoleRepository, etc.
- [ ] Domain Services : PasswordHasher (Argon2id), JwtSignerInterface

### Phase 5 — Application Layer

- [ ] DTOs : LoginRequest, TokenResponse, RegisterRequest
- [ ] Services : AuthenticationService, AuthorizationService, TokenRotationService
- [ ] Ports : TokenGenerator, TokenVerifier, RefreshTokenStorage

### Phase 6 — Infrastructure Layer

- [ ] PostgreSQL Repositories (implementations)
- [ ] JwtServiceRS256 (clés RSA)
- [ ] RefreshTokenRepository (rotation + détection réutilisation)
- [ ] BcryptPasswordHasher
- [ ] PSR-15 Middleware JWT Validation
- [ ] Controllers : AuthController, UserController

### Phase 7 — API Layer

- [ ] Router PSR-7 compatible
- [ ] Endpoints auth : POST /api/auth/login, /api/auth/refresh, /api/auth/logout
- [ ] Endpoints utilitaires : GET /api/me, GET /api/permissions
- [ ] Error handling JSON standardisé
- [ ] Input validation

### Phase 8 — Tests

- [ ] Tests unitaires : Entities, Value Objects, Domain Services
- [ ] Tests intégration : Repositories PostgreSQL, JWT service
- [ ] Tests fonctionnels : Endpoints API (HTTP)
- [ ] Mocks : DB in-memory, clés RSA test

### Phase 9 — CI/CD

- [ ] .github/workflows/ci.yml (tests, lint PSR-12, audit sécurité)
- [ ] .github/workflows/cd.yml (docker build, push registry)
- [ ] Scripts de génération clés RSA pour CI

### Phase 10 — Documentation

- [ ] README.md : badges CI, installation rapide
- [ ] docs/api.md : endpoints complets avec exemples cURL
- [ ] docs/architecture.md : décisions techniques (RS256 vs HS256, rotation tokens)
- [ ] docs/security.md : modèle de menaces
- [ ] docs/decisions/ADR-001 à ADR-005

---

## 🏗️ Structure de Fichiers

```text
php-auth/
├── docker/
│   ├── php/Dockerfile
│   ├── php/php.ini
│   └── nginx/default.conf
├── src/
│   ├── Domain/
│   │   ├── Entity/
│   │   ├── ValueObject/
│   │   ├── Repository/
│   │   └── Service/
│   ├── Application/
│   │   ├── Dto/
│   │   ├── Port/
│   │   └── Service/
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   ├── Security/
│   │   └── Web/
│   └── Config/
├── tests/
│   ├── Unit/
│   ├── Integration/
│   └── Functional/
├── migrations/
├── keys/
│   ├── private.pem (gitignored)
│   └── public.pem (gitignored)
├── docs/
│   ├── uml/
│   ├── decisions/
│   ├── api.md
│   ├── architecture.md
│   └── security.md
├── public/
│   └── index.php
├── .github/
│   └── workflows/
├── .env.example
├── composer.json
├── phpunit.xml
├── phpcs.xml
├── docker-compose.yml
├── Makefile
└── README.md
```

---

## 🔐 Spécifications Sécurité

### JWT RS256

- Clés RSA 2048 bits minimum
- Private key : signing uniquement (serveur)
- Public key : verification (peut être distribuée)
- Algorithm forcé (pas de "none", pas d'alg confusion)

### Rotation Refresh Tokens

- 1 refresh token = 1 session
- Nouveau refresh token à chaque usage
- Ancien refresh token invalidé
- Détection de réutilisation : si token déjà utilisé → révocation de toute la famille

### RBAC

- Users → Roles (many-to-many)
- Roles → Permissions (many-to-many)
- Héritage possible via roles hiérarchiques
- Permissions granulaires (resource:action format)

---

## 📊 Métriques de Qualité Cible

| Métrique | Objectif |
|----------|----------|
| Couverture tests | > 80% |
| Lint PSR-12 | 0 erreurs |
| Temps réponse API | < 100ms (p95) |
| Rotation tokens | 100% implémentée |

---

## ⚡ Ordre de Développement

1. Infrastructure Docker (phase 2)
2. Composer + autoloading (phase 3)
3. Domain layer (phase 4) - cœur métier
4. Application layer (phase 5) - logique JWT/RBAC
5. Infrastructure layer (phase 6) - PostgreSQL, JWT RS256
6. API layer (phase 7) - endpoints
7. Tests (phase 8)
8. CI/CD (phase 9)
9. Documentation finale (phase 10)
