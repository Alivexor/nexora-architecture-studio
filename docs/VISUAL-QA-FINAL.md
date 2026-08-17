# NEXORA — Visual QA record

## Scope and evidence

- Live target: `http://localhost/nexora/` (local Apache/WordPress)
- Browser: local Google Chrome driven by Playwright
- Viewports exercised: 1920×1080, 1440×900, 1366×768, 1024×768, 768×1024, 430×932, 390×844 and 360×800.
- Captures: 14 original-state captures in `docs/visual-audit/before/`; 56 pass-one captures, one pass-two capture and one pass-three capture in `docs/visual-audit/`; 14 curated handoff captures in `docs/showcase/`.
- Route/function checks: FA and EN home, Studio, Contact, Projects archive, Services archive, Journal, Search and 404. The Projects filter was exercised; its selected state left 3 matching cards. Empty contact submission correctly stays invalid. All tested routes returned their expected 200 status (the intentional nonexistent route returned 404).
- Responsive checks on every route above recorded `document.documentElement.scrollWidth <= window.innerWidth`.

## Issues found and fixed

1. **Hero media could leave a blank frame.** The primary image used a clip reveal which sometimes did not receive an `IntersectionObserver` callback despite already being visible, especially on mobile. The page opened with an oversized dark gap.
   - Fixed in `assets/css/main.css`: the primary hero is now immediately visible while editorial image reveals elsewhere remain intact.
   - The mobile hero image is capped to a purposeful 400px rather than inheriting the 520px tablet height.

2. **RTL mobile drawer inherited a desktop auto margin.** During the open transition the fixed navigation could be offset outside the viewport, making menu copy appear cropped.
   - Fixed in `assets/css/main.css` with a mobile fixed-drawer reset (`width:100%` and `margin-inline-start:0`).
   - Rechecked after transition: drawer rect is exactly 390px wide at x=0 and horizontal overflow is zero.

3. **Favicon request.** The theme had no explicit site icon, allowing browser favicon discovery to request a missing root asset.
   - Added `assets/images/brand/favicon.svg` and its header declaration. A final isolated homepage run reported no console errors.

## Interaction, motion and RTL

- Existing production animation architecture was retained: passive scroll plus `requestAnimationFrame`, `IntersectionObserver` reveal/stagger, transform/opacity/clip-path transitions, and `prefers-reduced-motion` fallbacks.
- The targeted change prevents motion from hiding critical above-the-fold content; hover, magnetic button, project/media and image reveals remain available.
- FA screenshots were checked with RTL layout, stats, CTA, contact form and drawer navigation; EN home was independently loaded and captured at desktop and mobile.

## Curated handoff

`docs/showcase/` includes FA/EN home desktop and mobile, Projects, Project case study, Project gallery, Before/After, Studio, Services, Journal, Contact, Search and mobile navigation. These are browser captures of the running local WordPress installation, not mockups.

## Constraints / remaining work

- This pass does not claim a literal complete visual inspection of every one of the 11 project records in both languages or logged-in WordPress-admin screens. No authenticated session was supplied, and the requested full 40–60 screenshot *pre-fix* matrix was not available before the first production correction; the actual original-state evidence is preserved rather than manufactured.
- The source backup is stored under `.codex-backups/`. The workspace is not a Git checkout, so no baseline commit/tag could be created.
