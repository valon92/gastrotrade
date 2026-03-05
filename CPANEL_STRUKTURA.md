# Kontrolli i strukturës së skedarëve në cPanel (arontrade.net)

Për të funksionuar si duhet, skedarët duhet të jenë **saktësisht** kështu në server.

---

## Ku duhet të jetë çdo gjë

**Rrënja e projektit** = `public_html` (pra `/home/aronqbxm/public_html`).

**Document root** i domain-it duhet të jetë: **`public_html/public`** (jo vetëm `public_html`).

---

## Struktura e saktë

```
public_html/                    ← Rrënja e projektit Laravel (ku është .env)
├── .env                        ← OBORSHËM: APP_KEY, DB_*, APP_URL
├── .env.example
├── artisan
├── app/
├── bootstrap/
│   └── cache/                  ← Duhet të shkruhet (755). Fshi config.php nëse jep 500.
├── config/
├── database/
├── public/                     ← Document root i arontrade.net duhet të tregojë KËTU
│   ├── .htaccess
│   ├── index.php
│   ├── build/                  ← Asetet e Vite (CSS/JS)
│   ├── images/
│   ├── favicon.ico
│   ├── favicon.svg
│   └── robots.txt
├── resources/
├── routes/
├── storage/                    ← Duhet të shkruhet (755)
│   ├── app/
│   ├── framework/
│   └── logs/                   ← laravel.log është këtu
├── vendor/
├── composer.json
├── composer.lock
└── ... (package.json, vite.config.js, etj. – opsionale për prod)
```

---

## Checklist – a janë vendosur saktë?

Kontrollo në **cPanel → File Manager → public_html**:

| # | Kontrolli | Saktë? |
|---|-----------|--------|
| 1 | **.env** është **brenda public_html** (një nivel mbi `public`), jo brenda `public/` | ☐ |
| 2 | **APP_KEY=base64:...** është në `.env` dhe nuk është bosh | ☐ |
| 3 | **Document root** i domain-it është **public_html/public** (në cPanel → Domains) | ☐ |
| 4 | **public_html/public/index.php** ekziston | ☐ |
| 5 | **public_html/public/.htaccess** ekziston | ☐ |
| 6 | **public_html/public/build/** ekziston (pas `npm run build`) | ☐ |
| 7 | **public_html/vendor/** ekziston (pas `composer install`) | ☐ |
| 8 | **public_html/storage** dhe **public_html/bootstrap/cache** kanë leje 755 (ose 775) | ☐ |
| 9 | Në **public_html/bootstrap/cache/** **nuk** ka skedar **config.php** (ose e fshinë për të hequr cache të vjetër) | ☐ |
| 10 | **DB_DATABASE**, **DB_USERNAME**, **DB_PASSWORD** në `.env` janë të plotësuar me të dhënat nga cPanel → MySQL | ☐ |

---

## Gabimet e zakonshme

- **.env brenda public/** → Laravel nuk e lexon. Duhet të jetë në **public_html/.env**.
- **Document root = public_html** → Shfaqet "Index of /". Duhet **public_html/public**.
- **APP_KEY bosh** → Gabim 500 "No application encryption key". Vendos APP_KEY në `.env` dhe fshi **bootstrap/cache/config.php**.
- **config.php i fshehur** në bootstrap/cache → Laravel përdor konfigurimin e vjetër. Fshi **config.php** që të lexojë përsëri nga `.env`.

---

Nëse të gjitha pikët sipër janë ✓, skedarët janë vendosur korrekt dhe faqja duhet të ngarkojë (përveç nëse mungojnë tabelat e databazës – atëherë duhen migracionet).
