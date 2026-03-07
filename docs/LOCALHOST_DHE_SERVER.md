# Localhost dhe server (arontrade.net) – njësoj

Ky dokument përshkruan si projekti mbahet i njëjtë në localhost dhe në server, dhe çfarë **nuk duhet ndryshuar** që të mos prishet asnjëra anë.

---

## Parimi

- **Kodi** është i njëjti: i njëjti `welcome.blade.php`, i njëjti Vue (Navbar me shportë), i njëjti workflow deploy.
- **Dallimi** është vetëm ku ndodhen skedarët në server (document root = `public_html`, gjithçka e ngjitur atje), ndaj Laravel duhet të “shohë” `build/` nga rrënja e projektit në server.

---

## Localhost

- Struktura standarde Laravel: `public/build/`, `public/index.php`, `app/`, `bootstrap/`, etj.
- `base_path('build/manifest.json')` **nuk ekziston** (manifest është në `public/build/`).
- `@vite(['resources/css/app.css', 'resources/js/app.js'])` në `welcome.blade.php` përdor `public_path('build/manifest.json')` → funksionon normalisht.
- **Nuk prek:** `resources/views/welcome.blade.php` – mbetet vetëm me `@vite(...)`, pa logjikë PHP për manifest.

---

## Server (arontrade.net)

- Deploy ngarkon në `public_html/`: aty janë `build/`, `index.php`, `app/`, `bootstrap/`, etj. (pra “rrënja” e aplikacionit = `public_html`).
- `index.php` në server është `index_for_document_root.php` (kopjuar nga workflow) që e bën `$base = __DIR__` (= `public_html`).
- `base_path('build/manifest.json')` **ekziston** në server (sepse `build/` është direkt në `public_html/`).
- **welcome.blade.php:** nëse ekziston `base_path('build/manifest.json')`, manifest lexohet dhe nxirren `<link>` / `<script>` me prefix `/build/` (pa thirrje të `@vite()` që kërkon manifest në `public_html/public/build/` dhe mund të jepë 500).
- Rezultat: e njëjta faqe dhe i njëjti Vue (përfshirë shportën në navbar) si në localhost.

---

## Çfarë nuk duhet bërë (që të mos prishet localhost ose serveri)

1. **Mos shto në `welcome.blade.php`:**
   - Lexim të manifest-it me `@php` dhe nxjerrje manuale të `<script>` / `<link>` (mund të çojë në gabime me `@vite()` në Laravel 12 dhe të “thyejë” localhost).

2. **Mos përdor** `usePublicPath(base_path())` në AppServiceProvider – në server mund të jepë 500. Përdoret vetëm fallback-i në view.

3. **Mos ndrysho** strukturën e deploy-it: përmbajtja e `public/` duhet të dalë në rrënjë të `deploy/` (dhe pastaj në `public_html/`), që në server të ketë `public_html/build/`, `public_html/index.php`, etj.

4. **Pas deploy në server:** mund të thirret `clear-cache.php?key=...` dhe të hapet faqja në incognito / hard refresh, që të ngarkohet kodi i ri (Vue, navbar, shportë).

---

## Përmbledhje

| Ku            | Manifest / public path                    | Si mbahet njësoj |
|---------------|-------------------------------------------|-------------------|
| Localhost     | `public/build/manifest.json`             | `@vite()` standard, pa ndryshime në view. |
| Server        | `public_html/build/manifest.json`        | Në view: nëse `base_path('build/manifest.json')` ekziston, nxirren `<link>`/`<script>` me `/build/`. |

Kështu projekti mbetet i njëjtë në të dyja anët, me kujdes që të mos fshihet ose ndryshohet ky mekanizëm.
