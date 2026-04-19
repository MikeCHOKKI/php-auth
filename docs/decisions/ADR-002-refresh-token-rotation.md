# ADR-002 — Rotation des Refresh Tokens avec Détection de Réutilisation

## Statut

Accepté

## Contexte

Les refresh tokens longue durée présentent un risque de sécurité s'ils sont volés. Nous devons minimiser la fenêtre d'exposition.

## Décision

Nous implémentons la **rotation des refresh tokens** avec détection de réutilisation.

## Mécanisme

1. Chaque utilisation d'un refresh token génère un nouveau token
2. L'ancien token est marqué comme "remplacé" (pas supprimé)
3. Si un token déjà remplacé/révoqué est réutilisé → révocation de toute la famille

## Conséquences

### Positives

- **Protection contre vol** : Un token volé ne peut être utilisé qu'une fois
- **Détection d'attaque** : La réutilisation indique une compromission
- **Invalidation en cascade** : Tous les tokens d'un utilisateur sont révoqués en cas d'attaque détectée

### Négatives

- **Complexité** : Nécessite une table de tokens avec état
- **Stockage** : Les tokens remplacés doivent être conservés temporairement

## Implémentation

- Table `refresh_tokens` avec colonnes : `replaced_by`, `revoked_at`, `is_revoked`
- Logique dans `TokenRotationService::detectReuse()`

## Références

- [OWASP — Token Rotation](https://cheatsheetseries.owasp.org/cheatsheets/JSON_Web_Token_for_Java_Cheat_Sheet.html#token-storage)
