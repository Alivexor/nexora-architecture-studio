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

### English home

<p align="center"><img src="docs/showcase/en-home-desktop.png" alt="NEXORA English desktop homepage" width="100%"></p>
<p align="center"><img src="docs/showcase/en-home-mobile.png" alt="NEXORA English mobile homepage" width="320"></p>

### صفحهٔ اصلی فارسی

<p align="center"><img src="docs/showcase/fa-home-desktop.png" alt="صفحه اصلی فارسی NEXORA در دسکتاپ" width="100%"></p>
<p align="center"><img src="docs/showcase/fa-home-mobile.png" alt="صفحه اصلی فارسی NEXORA در موبایل" width="320"></p>

## Selected screens

<p align="center"><img src="docs/showcase/projects-archive.png" alt="Projects archive" width="100%"></p>
<p align="center"><img src="docs/showcase/studio.png" alt="NEXORA studio page" width="100%"></p>
<p align="center"><img src="docs/showcase/mobile-navigation.png" alt="RTL mobile navigation" width="320"></p>

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
