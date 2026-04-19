# Design System — php-auth

> Option A — Minimal Pro | Backend API (documentation technique)
> Date: 2026-04-20

---

## Option Choisie

[x] **A — Minimal Pro** | [ ] B — Dark Premium | [ ] C — Editorial Bold

**Description:** Monochrome sobre avec accent vibrant. Optimisé pour interfaces techniques et documentation.

---

## Palette

| Token | Valeur | Usage |
|-------|--------|-------|
| **Primary** | `#37371F` | Headers, titres, accents sombres (vert-olive foncé) |
| **Accent** | `#EA9010` | Actions, liens, badges (orange vif) |
| **Background** | `#FAFAF9` | Fond principal (gris très clair chaud) |
| **Surface** | `#FFFFFF` | Cartes, modales, contenus |
| **Text** | `#1C1917` | Texte principal (noir chaud) |
| **Text Muted** | `#78716C` | Texte secondaire (gris taupe) |
| **Border** | `#E7E5E4` | Bordures, séparateurs |
| **Success** | `#15803D` | États positifs |
| **Error** | `#DC2626` | Erreurs, alertes |

### Utilisation API Documentation

Les couleurs sont appliquées dans :
- **Diagrammes UML** — bordures et accents
- **Documentation Markdown** — syntax highlighting, badges
- **Interfaces Admin** — si développement frontend ultérieur

---

## Typographie

| Rôle | Font | Taille | Grammage | Usage |
|------|------|--------|----------|-------|
| **Titre H1** | Inter | 2.25rem (36px) | 700 | Titres pages |
| **Titre H2** | Inter | 1.5rem (24px) | 600 | Sections |
| **Titre H3** | Inter | 1.25rem (20px) | 600 | Sous-sections |
| **Corps** | Inter | 1rem (16px) | 400 | Texte standard |
| **Code** | JetBrains Mono | 0.875rem (14px) | 400 | Blocs code, endpoints |
| **Label** | Inter | 0.75rem (12px) | 500 | Badges, métadonnées |

### Stack Font

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
font-family: 'JetBrains Mono', 'Fira Code', monospace; /* Code */
```

---

## Composants Markdown

### Badges API

![GET](https://img.shields.io/badge/GET-37371F?style=flat-square)
![POST](https://img.shields.io/badge/POST-EA9010?style=flat-square)

### Blocs Code

```json
{
  "primary": "#37371F",
  "accent": "#EA9010"
}
```

### Tableaux

| Méthode | Endpoint | Auth |
|---------|----------|------|
| POST | `/api/auth/login` | Non |
| GET | `/api/me` | Bearer JWT |

---

## Accessibilité

- **Contraste** — Ratio minimum 4.5:1 pour texte
- **Focus** — Outline accent `#EA9010` sur éléments interactifs
- **Code** — Syntax highlighting avec contraste élevé

---

## Ressources

- [Inter Font](https://rsms.me/inter/)
- [JetBrains Mono](https://www.jetbrains.com/lp/mono/)
- [WCAG 2.1 Contrast](https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html)
