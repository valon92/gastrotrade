# Migrimi: cPanel → Vercel + Render + Namecheap DNS

AronTrade ndahet në dy pjesë:

| Pjesa | Platformë | URL |
|--------|-----------|-----|
| **Frontend** (Vue SPA, foto statike) | **Vercel** | `https://www.arontrade.net` dhe `https://arontrade.net` |
| **Backend** (Laravel API, upload foto) | **Render** (Docker) | `https://api.arontrade.net` |
| **Databaza** | **Render PostgreSQL** | (internal) |
| **Domeni** | **Namecheap** | DNS records |

Vercel bën **proxy** të `/api/*` drejt Render — frontend mbetet same-origin (`/api/...`), pa ndryshime të mëdha në kod.

---

## Arkitektura

```
Përdoruesi
    │
    ▼
arontrade.net / www.arontrade.net  ──►  Vercel (public/, index.html, /images/*)
    │                                      │
    │                                      ├─ /api/*  ──proxy──►  api.arontrade.net (Render)
    │                                      └─ /images/Uploads/* ──proxy──► Render (disk)
    ▼
api.arontrade.net  ──►  Render Web Service (Dockerfile, Laravel, /up)
                            │
                            ├─ PostgreSQL (Render)
                            └─ Disk: public/images/Uploads (foto të reja admin)
```

---

## Hapi 1 — Eksporto nga cPanel

### Databaza (MySQL)
1. cPanel → **phpMyAdmin** → databaza `aronqbxm_arontrade`
2. **Export** → SQL → shkarko `arontrade-mysql.sql`

### Skedarët e ngarkuar
Kopjo nga serveri (FTP/File Manager):
- `public_html/images/Uploads/` → ruaje lokalisht

### `.env` i prodhimit
Ruaj vlerat: `APP_KEY`, kredencialet e email-it (`MAIL_*`, `OFFICIAL_ORDER_EMAIL`).

---

## Hapi 2 — Render (API + databazë)

### 2.1 Krijo shërbimin
1. [render.com](https://render.com) → **New** → **Blueprint**
2. Lidhe repo-n GitHub `gastrotrade` / AronTrade
3. Render lexon `render.yaml` dhe krijon:
   - `arontrade-api` (web, Docker)
   - `arontrade-db` (PostgreSQL)

### 2.2 Variablat që duhen vendosur manualisht (Render Dashboard → Environment)
| Variabël | Vlera |
|----------|--------|
| `MAIL_USERNAME` | Gmail / SMTP user |
| `MAIL_PASSWORD` | App Password |
| `MAIL_FROM_ADDRESS` | adresa dërguese |
| `OFFICIAL_ORDER_EMAIL` | inbox i porosive |

Të tjerat janë në `render.yaml`.

### 2.3 Migrimi i të dhënave MySQL → PostgreSQL

**Opsioni A — Projekt i ri (më i thjeshtë)**  
Pas deploy-it të parë:
```bash
# Në Render Shell (Dashboard → arontrade-api → Shell)
php artisan migrate --force
php artisan db:seed --force   # vetëm nëse ke seeder për prod
```
Pastaj importo produktet/klientët manualisht ose me skript.

**Opsioni B — Import i plotë nga MySQL**  
Përdor [pgloader](https://pgloader.io/) ose eksporto CSV nga phpMyAdmin dhe importo në PostgreSQL.  
Kontrollo tipet (`boolean`, `json`, `datetime`) pas importit.

**Opsioni C — Mbaj MySQL të jashtëm**  
Nëse do të mbash MySQL (p.sh. PlanetScale / Aiven):
1. Fshi seksionin `databases:` nga `render.yaml`
2. Vendos në Render: `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

### 2.4 Uploads
Para switch-it DNS, ngarko në Render disk:
- Kopjo përmbajtjen e `images/Uploads/` në `/var/www/html/public/images/Uploads` (Render Shell ose rsync nëse ke SSH).

### 2.5 Custom domain API
1. Render → `arontrade-api` → **Settings** → **Custom Domains**
2. Shto: `api.arontrade.net`
3. Render jep target CNAME (p.sh. `arontrade-api.onrender.com`)

---

## Hapi 3 — Vercel (frontend)

### 3.1 Importo projektin
1. [vercel.com](https://vercel.com) → **Add New Project** → GitHub repo
2. Framework: **Other**
3. Build Command: `npm ci && npm run build:vercel` (ose lexohet nga `vercel.json`)
4. Output Directory: `public`

### 3.2 Environment Variables (Vercel)
| Variabël | Vlera | Shënim |
|----------|--------|--------|
| `VITE_API_URL` | *(bosh)* | Proxy në `vercel.json` — mos e vendos në prod |

Për testim direkt API pa proxy (debug): `VITE_API_URL=https://api.arontrade.net`

### 3.3 Përditëso proxy në `vercel.json`
Nëse Render jep URL tjetër para DNS:
```json
"destination": "https://YOUR-SERVICE.onrender.com/api/:path*"
```
Pas custom domain: `https://api.arontrade.net/api/:path*`

### 3.4 Domene në Vercel
1. Vercel → Project → **Settings** → **Domains**
2. Shto: `arontrade.net` dhe `www.arontrade.net`
3. Vercel jep rekordet DNS (A / CNAME)

---

## Hapi 4 — Namecheap DNS

Hyr te **Namecheap** → Domain List → `arontrade.net` → **Advanced DNS**.

### Frontend (Vercel)
| Type | Host | Value |
|------|------|--------|
| A | `@` | IP që jep Vercel për apex |
| CNAME | `www` | `cname.vercel-dns.com` (ose vlera nga Vercel) |

*(Vercel Dashboard tregon saktë rekordet për domain-in tënd.)*

### API (Render)
| Type | Host | Value |
|------|------|--------|
| CNAME | `api` | `arontrade-api.onrender.com` (ose vlera nga Render) |

### Email (mos e prish)
Mbaj rekordet **MX**, **TXT** (SPF/DKIM) siç janë nëse përdor email te Namecheap/Google.

### Redirect www ↔ apex (opsional)
Në Vercel: vendos `www.arontrade.net` si primary; Vercel bën redirect automatik.

---

## Hapi 5 — Test para switch-it

1. **Render**: `https://arontrade-api.onrender.com/up` → `200 OK`
2. **API**: `https://api.arontrade.net/api/products` → JSON
3. **Vercel preview URL**: hap `/katalogu`, login admin, krijo porosi test
4. **Foto**: produktet statike nga Vercel `/images/...`; upload i ri nga admin → `/images/Uploads/...` (proxy Render)

---

## Hapi 6 — Switch DNS (go-live)

1. Ul TTL në Namecheap (5 min) 24h para switch-it
2. Përditëso rekordet sipas Hapit 4
3. Prit propagim (5–60 min)
4. Testo `https://www.arontrade.net/katalogu`
5. **Çaktivizo** hosting cPanel (ose lëre vetëm për email nëse përdoret)

---

## Deploy i përditshëm

| Ndryshim | Ku shfaqet |
|----------|------------|
| Push në `main` | Vercel + Render auto-deploy (nëse lidhur me GitHub) |
| Vetëm frontend (Vue) | Vercel rebuild |
| Vetëm backend (PHP) | Render rebuild Docker image |

Workflow i vjetër FTP (`deploy-cpanel.yml`) është **manual** (`workflow_dispatch`) — mos e përdor pas migrimit.

---

## Komanda lokale

```bash
# Frontend si në Vercel
npm ci && npm run build:vercel

# Test Docker si Render
docker build -t arontrade-api .
docker run --rm -p 8080:10000 \
  -e APP_KEY=base64:... \
  -e DB_CONNECTION=sqlite \
  -e DB_DATABASE=/var/www/html/database/database.sqlite \
  arontrade-api
```

---

## Troubleshooting

| Problem | Zgjidhje |
|---------|----------|
| 502 / API nuk përgjigjet | Render logs; kontrollo `APP_KEY`, DB credentials |
| CORS error | Vendos `FRONTEND_URL=https://www.arontrade.net` në Render; ose përdor proxy Vercel |
| Foto upload nuk shfaqen | Disk Render i montuar te `public/images/Uploads`; kontrollo rewrite në `vercel.json` |
| 419 / CSRF | API përdor Bearer token — nuk duhet CSRF për `/api/*` |
| Session admin humbet | `SESSION_DRIVER=database` + tabela `sessions` në DB |
| Render cold start | Plan Starter; konsidero paid për më pak latency |

---

## Skedarët e infrastrukturës në repo

| Skedar | Qëllimi |
|--------|---------|
| `Dockerfile` | Image Laravel për Render |
| `render.yaml` | Blueprint (API + PostgreSQL + disk) |
| `vercel.json` | Build SPA + proxy API |
| `scripts/prepare-vercel.mjs` | Gjeneron `public/index.html` |
| `docker/nginx.conf` | Web server në container |
| `config/cors.php` | CORS për thirrje direkte API (fallback) |
