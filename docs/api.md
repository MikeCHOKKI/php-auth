# API Documentation — php-auth

> API REST d'authentification RBAC avec JWT RS256
> Version: 1.0.0 | Date: 2026-04-20

---

## Base URL

| Environnement | URL                       |
|---------------|---------------------------|
| Development   | `http://localhost:8080`   |
| Production    | `https://api.example.com` |

---

## Authentification

L'API utilise **JWT RS256** (asymétrique) pour l'authentification.

### Header Authorization

```
Authorization: Bearer <access_token>
```

### Types de Tokens

| Token         | Durée             | Usage                  |
|---------------|-------------------|------------------------|
| Access Token  | 15 minutes (900s) | Authentification API   |
| Refresh Token | 7 jours (604800s) | Renouvellement session |

---

## Endpoints

### Authentification

#### POST `/api/auth/login`

Authentification avec email et password. Retourne access token + refresh token.

**Requête:**

```json
{
  "email": "user@example.com",
  "password": "securepassword123"
}
```

**Réponse 200:**

```json
{
  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refreshToken": "a1b2c3d4e5f6...",
  "expiresIn": 900,
  "tokenType": "Bearer"
}
```

**Réponse 401 (Invalid Credentials):**

```json
{
  "error": "Invalid email or password"
}
```

---

#### POST `/api/auth/refresh`

Rotation du refresh token. Génère nouveau access token + nouveau refresh token.

**Requête:**

```json
{
  "refreshToken": "a1b2c3d4e5f6..."
}
```

**Réponse 200:**

```json
{
  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refreshToken": "g7h8i9j0k1l2...",
  "expiresIn": 900,
  "tokenType": "Bearer"
}
```

**Réponse 401 (Token Reuse Detected):**

```json
{
  "error": "Token reuse detected - all sessions revoked for security"
}
```

---

#### POST `/api/auth/logout`

Révocation de tous les refresh tokens de l'utilisateur.

**Headers:**

```
Authorization: Bearer <access_token>
```

**Réponse 200:**

```json
{
  "message": "Logged out successfully"
}
```

---

### Utilitaires (Protégés)

#### GET `/api/me`

Informations sur l'utilisateur connecté et ses permissions actives.

**Headers:**

```
Authorization: Bearer <access_token>
```

**Réponse 200:**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "permissions": [
    {
      "id": "abc123",
      "resource": "user",
      "action": "read",
      "identifier": "user:read"
    }
  ],
  "roles": [
    {
      "id": "def456",
      "name": "admin",
      "description": "Administrateur système"
    }
  ]
}
```

---

#### GET `/api/permissions`

Liste des permissions actives de l'utilisateur connecté (endpoint utilitaire frontend).

**Headers:**

```
Authorization: Bearer <access_token>
```

**Réponse 200:**

```json
{
  "permissions": [
    {
      "id": "abc123",
      "resource": "user",
      "action": "read",
      "identifier": "user:read"
    },
    {
      "id": "def456",
      "resource": "role",
      "action": "read",
      "identifier": "role:read"
    }
  ],
  "count": 2
}
```

---

## Codes d'Erreur

| Code | Description                                       |
|------|---------------------------------------------------|
| 400  | Bad Request — Données invalides                   |
| 401  | Unauthorized — Token manquant, invalide ou expiré |
| 403  | Forbidden — Permission insuffisante               |
| 404  | Not Found — Endpoint inexistant                   |
| 500  | Internal Server Error                             |

---

## Exemples cURL

### Login

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### Utiliser le token

```bash
curl http://localhost:8080/api/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

### Refresh

```bash
curl -X POST http://localhost:8080/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{"refreshToken":"a1b2c3d4e5f6..."}'
```

---

## Modèle de Données

### JWT Payload (Access Token)

```json
{
  "sub": "550e8400-e29b-41d4-a716-446655440000",
  "email": "user@example.com",
  "roles": ["admin", "user"],
  "iat": 1713640800,
  "exp": 1713641700,
  "type": "access"
}
```

### RBAC

- **Users** — Entités avec email/password
- **Roles** — Groupes (admin, user)
- **Permissions** — Granulaires (`resource:action`, ex: `user:read`)

---

## Références

- [RFC 7519 — JSON Web Token (JWT)](https://tools.ietf.org/html/rfc7519)
- [RFC 7518 — JSON Web Algorithms (JWA)](https://tools.ietf.org/html/rfc7518)
- [OWASP JWT Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/JSON_Web_Token_for_Java_Cheat_Sheet.html)
