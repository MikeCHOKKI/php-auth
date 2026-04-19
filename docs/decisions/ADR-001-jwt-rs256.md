# ADR-001 — Utilisation de JWT RS256 (Asymétrique)

## Statut

Accepté

## Contexte

Nous avons besoin d'un mécanisme d'authentification stateless pour l'API REST. Plusieurs options d'algorithmes JWT sont disponibles.

## Décision

Nous utilisons **RS256** (RSA avec SHA-256) au lieu de HS256 (HMAC avec SHA-256).

## Conséquences

### Positives

- **Séparation des clés** : Clé privée pour signer (serveur uniquement), clé publique pour vérifier (peut être distribuée)
- **Évolutivité** : Les microservices peuvent vérifier les tokens sans connaître la clé privée
- **Sécurité** : Moins de risque d'exposition car la clé privée reste sur le serveur d'authentification

### Négatives

- **Complexité** : Nécessite de gérer une paire de clés RSA
- **Performance** : Légèrement plus lent que HMAC (négligeable pour notre usage)

## Alternatives Considérées

- **HS256** : Plus simple mais même clé pour signer/vérifier → risque de fuite
- **ES256** (ECDSA) : Plus rapide, clés plus courtes, mais moins supporté

## Références

- RFC 7518 — JSON Web Algorithms (JWA)
- [Auth0 — RS256 vs HS256](https://auth0.com/blog/rs256-vs-hs256-whats-the-difference/)
