# NEXORA — Bilingual Architecture Studio

> A premium, editorial WordPress portfolio for an architecture and spatial-design studio. Built for Persian (RTL) and English (LTR) audiences with Polylang.

[نسخهٔ فارسی](README.fa.md) · [Visual QA report](docs/VISUAL-QA-FINAL.md)

## Highlights

- Independent FA/EN experience with real RTL and LTR layouts
- Editorial project archive, filters, detailed case studies, galleries and before/after comparison
- Studio, services, journal, search and validated inquiry flow
- Responsive design for desktop, tablet and mobile
- Lightweight interactions: IntersectionObserver reveals, staggered cards, project hover states, header transition, subtle parallax and reduced-motion support
- Custom WordPress theme plus `Nexora Core` companion plugin

## Live design preview

### English

| Desktop | Mobile |
| --- | --- |
| ![NEXORA English desktop homepage](docs/showcase/en-home-desktop.png) | ![NEXORA English mobile homepage](docs/showcase/en-home-mobile.png) |

### فارسی

| دسکتاپ | موبایل |
| --- | --- |
| ![صفحه اصلی فارسی NEXORA در دسکتاپ](docs/showcase/fa-home-desktop.png) | ![صفحه اصلی فارسی NEXORA در موبایل](docs/showcase/fa-home-mobile.png) |

## Selected screens

| Projects | Case study |
| --- | --- |
| ![Projects archive](docs/showcase/projects-archive.png) | ![Project case study](docs/showcase/project-case-study.png) |

| Studio | Contact |
| --- | --- |
| ![Studio](docs/showcase/studio.png) | ![Contact](docs/showcase/contact.png) |

| Mobile navigation | Before / After |
| --- | --- |
| ![Mobile navigation](docs/showcase/mobile-navigation.png) | ![Before and after](docs/showcase/before-after.png) |

## Project structure

```text
wp-content/
├── themes/nexora/       # Front-end theme, templates, styles and interactions
└── plugins/nexora-core/ # Content types, demo content, inquiry and admin features
docs/
├── showcase/            # Curated browser screenshots used in this README
└── VISUAL-QA-FINAL.md   # Runtime and visual QA record
scripts/                 # Playwright-driven local capture utilities
```

## Local setup

1. Put the project in your local WordPress environment, for example `C:\\xampp\\htdocs\\nexora`.
2. Create/import the local WordPress database and set its connection in `wp-config.php` (intentionally not tracked).
3. Activate **Nexora** and **Nexora Core** from WordPress Admin.
4. Activate Polylang and configure Persian and English translations.
5. Open `http://localhost/nexora/`.

## Quality notes

The included QA record documents real Chrome checks across eight viewports, responsive overflow checks, route validation, project filtering and contact-form client validation. It also records the targeted fixes for the hero reveal, RTL mobile navigation and favicon handling.

## Tech

WordPress · PHP · Polylang · Vanilla JavaScript · CSS Custom Properties · Playwright (local visual QA)

---

Designed and developed for **NEXORA**.
