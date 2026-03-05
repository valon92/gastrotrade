# Si të bësh update në server (cPanel) kur bën ndryshime në localhost

Kur ndryshon diçka në projekt **lokalisht** dhe do që **www.arontrade.net** të pasqyrojë ndryshimet, ndiq këto hapa **me radhë**. Ndalo pas çdo hapi nëse do të kontrollosh rezultatin.

---

## Përmbledhje e shpejtë

| Hapi | Ku e bën | Çfarë bën |
|------|----------|-----------|
| 1 | Lokal | Ndryshon kodin, pastaj `npm run build` |
| 2 | Lokal | (Opsional) `git add` + `git commit` + `git push` |
| 3 | cPanel | Ngarkon skedarët e ndryshuar në `public_html` |
| 4 | cPanel | Nuk mbishkruan `.env`, fshin `bootstrap/cache/config.php` nëse duhet |

---

## Hapi 1 – Ndryshimet lokale dhe build

### 1.1 Bëj ndryshimet në kodin tënd

Hap dhe ndrysho skedarët në projektin **AronTrade** në kompjuterin tënd (Vue, PHP, CSS, etj.). Ruaj çdo skedar.

**→ Ndalo.** Sigurohu që të gjitha ndryshimet janë ruajtur.

---

### 1.2 Build i frontend (obligativ kur ndryshon Vue/JS/CSS)

Nëse ke ndryshuar diçka në:
- `resources/js/` (Vue, komponente, views)
- `resources/css/`
- `vite.config.js` ose përdorimin e aseteve

duhet të ekzekutosh **build** që të krijohen skedarët e rinj në `public/build/`.

1. Hap **Terminal** në kompjuterin tënd (jo në cPanel).
2. Shko te folderi i projektit:
   ```bash
   cd /path/tek/AronTrade
   ```
   (zëvendëso me rrugën reale, p.sh. `cd ~/Projects/AronTrade`).
3. Ekzekuto:
   ```bash
   npm run build
   ```
4. Prit derisa të mbarojë pa gabime. Duhet të shikosh diçka si: `built in ... ms`.

**→ Ndalo.** Kontrollo që ekziston folderi **`public/build/`** dhe brenda ka skedarë si `app-....js` dhe `app-....css`.

- Nëse **nuk** ke ndryshuar asgjë në JS/CSS/Vue, mund ta kapërcesh këtë nën-hap, por zakonisht është më sigurt ta bësh gjithmonë `npm run build` para upload-it.

---

## Hapi 2 – (Opsional) Ruaj në GitHub

Nëse përdor Git dhe do t’i mbash ndryshimet në GitHub:

```bash
cd /path/tek/AronTrade
git add .
git status
git commit -m "Përshkrim i ndryshimit"
git push origin main
```

**→ Ndalo.** Tani kodi i përditësuar është edhe në GitHub (nëse e ke bërë këtë hap).

---

## Hapi 3 – Ngarko ndryshimet në cPanel

Në **cPanel** ke dy mënyra: **File Manager** (ngarkim manual) ose **FTP**. Struktura në server është: rrënja e projektit = **`public_html`** (document root është `public_html` ose `public_html/public` sipas konfigurimit tënd).

### 3.1 Hap File Manager në cPanel

1. Hyr në **cPanel**.
2. Hap **File Manager**.
3. Shko te **public_html** (rrënja e faqes arontrade.net).

**→ Ndalo.** Sigurohu që je brenda `public_html`.

---

### 3.2 Çfarë të ngarkosh (çfarë ke ndryshuar)

Ngarko **vetëm** pjesët që ke ndryshuar, duke **mbishkruar** skedarët ekzistues në server. Mos mbishkruaj skedarë që nuk i ke prekur.

| Ndryshove këtë lokalisht | Ngarko në server këto |
|--------------------------|------------------------|
| **Vue / JS / CSS** (resources/js, resources/css) | Pas `npm run build`: ngarko **tërë përmbajtjen** e `public/build/` në **public_html/build/** (ose në **public_html/public/build/** nëse document root është `public_html/public`). |
| **PHP** (app/, routes/, config/) | Ngarko skedarët ose folderat përkatëse në **public_html/app/**, **public_html/routes/**, **public_html/config/**, etj. |
| **Blade / views** (resources/views/) | Ngarko në **public_html/resources/views/**. |
| **Udhëzime / dokumente** (.md) | Mund t’i ngarkosh në **public_html/** nëse do t’i kishe edhe atje (opsional). |

Rëndësi:

- **Mos mbishkrua** skedarin **`public_html/.env`** me `.env` nga lokali. Në server mbetet `.env` i cPanel-it (me DB, APP_KEY, APP_URL për production).
- Nëse ke kopjuar përmbajtjen e `public/` në rrënjë të `public_html` (si në CPANEL_SETUP), atëherë:
  - **Build:** ngarko përmbajtjen e `public/build/` (lokalisht) në **public_html/build/**.
  - **index.php** në rrënjë: nëse e ke ndryshuar, ngarko versionin që funksionon nga rrënja (p.sh. `index_for_document_root.php` të ruajtur si `index.php`).

---

### 3.3 Si të ngarkosh me File Manager

1. Në File Manager, hap folderin **destinacion** (p.sh. `public_html/build`, ose `public_html/app`, etj.).
2. Kliko **Upload** (Ngarko).
3. Zgjidh skedarët ose folderat nga kompjuteri yt (mund të zgjidhësh të gjithë përmbajtjen e `public/build/` pas build, ose skedarët e ndryshuar nga `app/`, `routes/`, etj.).
4. Konfirmo **mbishkrim** (Replace / Overwrite) për skedarët që ekzistojnë.
5. Prit derisa të mbarojë upload-i.

**→ Ndalo.** Kontrollo në File Manager që skedarët e rinj kanë datën e sotme.

---

### 3.4 Nëse përdor FTP në vend të File Manager

1. Hap një klient FTP (p.sh. FileZilla) dhe lidhu me kredencialet e cPanel (përdoruesi dhe fjalëkalimi i hostingut).
2. Në server, shko te folderi që korrespondon me `public_html` (zakonisht quhet `public_html` ose `www`).
3. Ngarko në të njëjtat rrugë si më sipër (p.sh. `public/build/` → `public_html/build/`), duke zëvendësuar skedarët ekzistues.
4. Mos zëvendëso `.env` në server me atë nga lokali.

**→ Ndalo.** Pas upload-it, vazhdo me Hapi 4.

---

## Hapi 4 – Pas upload-it në server

### 4.1 Fshi cache të konfigurimit (e nevojshme shpesh)

Nëse ndryshove **config**, **routes** ose **.env** (vetëm nëse e ndryshove drejt në server), Laravel mund të ketë fshehur konfigurin e vjetër.

1. Në cPanel File Manager, shko te **public_html/bootstrap/cache/**.
2. Nëse ekziston skedari **config.php**, **fshije** (Delete).

**→ Ndalo.** Kjo e detyron Laravel të lexojë përsëri `config/` dhe `.env`.

---

### 4.2 Kontrollo në faqe

1. Hap në shfletues: **https://www.arontrade.net/**
2. Bëj një **rifreskim të fortë** (Ctrl+F5 ose Cmd+Shift+R) që të mos përdoret cache i shfletuesit.
3. Kontrollo që ndryshimet e tua shfaqen (faqe, butona, stile, etj.).

Nëse shfaqet gabim 500 ose faqja nuk ndryshon:
- Shiko **public_html/storage/logs/laravel.log** (rreshtat e fundit) për gabimin e saktë.
- Sigurohu që nuk ke mbishkruar `.env` në server dhe që ke fshirë `bootstrap/cache/config.php` nëse ekzistonte.

---

## Skema e shpejtë (pa GitHub)

```
[Localhost]
  1. Ndrysho kod
  2. npm run build
  3. Hap cPanel → File Manager → public_html
  4. Upload: public/build/*  →  public_html/build/
       (dhe nëse ke ndryshuar: app/, routes/, config/, resources/views/, etj.)
  5. Mos prek .env në server
  6. Fshi public_html/bootstrap/cache/config.php (nëse ka)
  7. Rifresko https://www.arontrade.net/
```

---

## Kur ndryshon vetëm databaza (jo kodi)

Nëse ndryshon **vetëm të dhënat** (produkte, kategori, përdorues) në databazë dhe **jo** kodin ose frontend-in:

- Nuk ke nevojë për `npm run build`.
- Mund të eksportosh nga lokali (phpMyAdmin ose `mysqldump`) dhe të importosh në cPanel → phpMyAdmin në databazën **aronqbxm_arontrade**, ose të përdorësh skedarët SQL që ke (`arontrade_mysql_import.sql` / `arontrade_mysql_supplement.sql`) sipas **DATABASE_UPDATE_STEPS.md**.

---

## Kur ke Terminal/SSH në cPanel (opsional)

Nëse një ditë të aktivizohet **Terminal** në cPanel, mund të përdorësh edhe këtë për update (në vend të upload manual):

```bash
cd ~/public_html
git pull origin main
npm run build
rm -f bootstrap/cache/config.php
```

Kjo zëvendëson hapat e upload-it me Git + build direkt në server. `.env` në server mbetet i njëjtë.

---

# Si të bësh update nga GitHub në cPanel

Kur kodi i përditësuar është tashmë në **GitHub** dhe do ta nxjerrësh në server (cPanel), ke dy mënyra kryesore. Zgjidh njërën sipas mundësive të hostingut tënd.

**Me skedarin `.cpanel.yml` (në repo):** pas çdo **Pull** në cPanel, kliko **Deploy** — cPanel kopjon automatikisht nga `arontrade_git` në `public_html`. Nuk ke nevojë të kopjosh me dorë. Për ndryshime në Vue/JS/CSS duhet gjithashtu të ngarkosh **public/build/** nga lokali (pas `npm run build`).

---

## Mënyra 1 – cPanel Git Version Control (nëse e ofron hostingu)

Shumë hoste me cPanel kanë **Git™ Version Control**. Kjo mënyrë është më e shpejtë: clone një herë, pastaj çdo update bëhet me një "Pull" nga ndërfaqja.

> **Nëse del:** *"You cannot use the /home/aronqbxm/public_html directory because it already contains files"* — Përdor **Repository Path:** `arontrade_git`. Pas çdo Pull kliko **Deploy** (nëse ke skedarin `.cpanel.yml` në repo) që të kopjohen automatikisht ndryshimet në `public_html`; përndryshe kopjo me dorë nga `arontrade_git` në `public_html`.

### A. Konfigurimi i parë (vetëm një herë)

1. Hyr në **cPanel**.
2. Shko te **Tools → Files → Git™ Version Control** (ose kërko "Git" në cPanel).
3. Kliko **Create** (Krijo).
4. Aktivizo **Clone a Repository** (klonimi i një repozitoriu).
5. Plotëso (vlera të gatshme për projektin AronTrade):
   - **Clone URL:**  
     `https://github.com/valon92/gastrotrade.git`  
     (nëse repozitoriu është **private**, përdor: `https://USERNAME:TOKEN@github.com/valon92/gastrotrade.git` — zëvendëso USERNAME me emrin tënd GitHub dhe TOKEN me një [Personal Access Token](https://github.com/settings/tokens)).
   - **Repository Path:**  
     `arontrade_git`  
     **E rëndësishme:** Mos përdor `public_html` — cPanel nuk lejon klonim në një folder që tashmë ka skedarë. Klono në një folder të ri (p.sh. `arontrade_git`). Pas çdo Pull do të kopjosh përmbajtjen nga ky folder në `public_html` (shiko hapat më poshtë).
   - **Repository Name:**  
     `arontrade`  
     (vetëm emër në listë, nuk ndikon në rrugë).
6. Nëse repozitoriu është **private**, zakonisht duhet të shtosh **Credentials** (përdorues GitHub ose Personal Access Token) në fushën e duhur në cPanel.
7. Kliko **Create**.

**Pas klonimit të parë** — kopjimi nga `arontrade_git` në `public_html`:

- Repozitoriu do të jetë në `/home/aronqbxm/arontrade_git/`. Faqja e vërtetë xhiron nga `public_html`, prandaj duhet të kopjosh kodin atje.
- Në **File Manager**: hap `arontrade_git`, zgjidh **të gjithë** skedarët dhe folderat (përveç `.git` nëse nuk do ta përdorësh për ndonjë arsye — mund ta lësh). Kliko **Copy**.
- Shko te `public_html` dhe **Paste** (mbishkruaj kur të pyetet). Kjo e përditëson kodin në faqe duke mbajtur `public_html/.env` nëse nuk e mbishkruan eksplicit një skedar `.env` nga `arontrade_git` (zakonisht nga GitHub nuk vjen `.env`, vetëm `.env.example`).
- Nëse ke strukturën me document root = `public_html` (përmbajtja e `public/` e kopjuar në rrënjë), sigurohu që në `public_html` ke `index.php` të saktë (p.sh. nga `public/index_for_document_root.php` të ruajtur si `index.php`) dhe folderin `build/` — shiko **CPANEL_SETUP.md**.
- **Build:** bëje lokalisht `npm run build` dhe ngarko `public/build/` në `public_html/build/`. Pastaj fshi `public_html/bootstrap/cache/config.php` nëse ekziston dhe rifresko faqen.

### B. Çdo përditësim (Pull + Deploy nga GitHub)

1. Hyr në **cPanel → Git™ Version Control**.
2. Zgjidh repozitorin **arontrade** (ose emrin që ke dhënë).
3. Kliko **Manage** / **Update** ose **Pull** që të tërhiqen ndryshimet e fundit nga GitHub. Prit derisa të mbarojë.
4. Kliko **Deploy** (Pull or Deploy). Nëse repozitoriu ka skedarin **`.cpanel.yml`**, cPanel do të ekzekutojë detyrat e deployment-it dhe do të kopjojë automatikisht kodin nga `arontrade_git` në `public_html`. **`.env`** në server nuk mbishkruhet (nuk është në repo).
5. Nëse **Deploy** nuk ofrohet ose jep gabim, kopjo me dorë: **File Manager** → **arontrade_git** → zgjidh të gjitha → **Copy** → **public_html** → **Paste** (mbishkruaj).

Pas çdo Pull + Deploy:
- **Build i frontend:** në cPanel zakonisht **nuk** ka Node/npm. Prandaj:
  - Ose bën **build lokalisht** (në kompjuterin tënd: `npm run build` në projektin e përditësuar nga GitHub), pastaj ngarkon **vetëm** përmbajtjen e **`public/build/`** në **public_html/build/** (ose në **public_html/public/build/** nëse e ke strukturën me `public`).
  - Ose, nëse hostingu ka Node/npm dhe ke mundësi të ekzekutosh komanda (p.sh. Node.js Selector ose SSH), atëherë: `cd public_html && npm ci && npm run build`.
- **Mos mbishkrua** `.env` në server me asnjë skedar nga GitHub.
- Fshi **cache** në server: në File Manager, te **public_html/bootstrap/cache/**, fshi **config.php** nëse ekziston.
- Rifresko faqen **https://www.arontrade.net/** (Ctrl+F5).

---

## Mënyra 2 – Shkarkim ZIP nga GitHub + ngarkim në cPanel

Nëse **nuk** ke Git Version Control në cPanel, mund të shkarkosh kodin si ZIP nga GitHub dhe ta ngarkosh me File Manager (ose FTP).

### A. Shkarkimi nga GitHub

1. Hap repozitorin në GitHub: `https://github.com/EMRI_I_PËRDORUESIT/AronTrade`.
2. Kliko butonin e gjelbër **Code**.
3. Zgjidh **Download ZIP**.
4. Shkarko ZIP-in dhe hape/çpakoho në kompjuterin tënd.

### B. Çfarë të ngarkosh në cPanel

- **Mos zëvendëso** tërë `public_html` me përmbajtjen e ZIP-it në një goditje, sepse do të humbësh **`.env`** dhe mund të prishësh konfigurimin e production.
- Ngarko **vetëm** folderat dhe skedarët që kanë **ndryshuar** nga versioni i vjetër, duke mbishkruar skedarët ekzistues në **public_html**:
  - `app/`
  - `config/`
  - `database/` (migrations, seeders – kujdes nëse ke të dhëna të rëndësishme vetëm në server)
  - `public/` (përveç `.env` – në rrënjë të serverit është zakonisht përmbajtja e `public/`, jo folderi `public` i plotë; shiko **CPANEL_SETUP.md**)
  - `resources/`
  - `routes/`
  - `bootstrap/`
  - `composer.json`, `composer.lock`, `artisan`, etj. nëse i ke ndryshuar
- **Mos ngarko** `.env` nga ZIP në server – mbani `.env` e cPanel-it.
- **Build:** ZIP nga GitHub **nuk** përmban zakonisht `public/build/` (ose përmban një version të vjetër). Prandaj:
  1. Lokalisht: pas çpakimit të ZIP-it, ekzekuto `npm install` dhe `npm run build` në atë kopje.
  2. Ngarko përmbajtjen e **`public/build/`** nga ajo kopje në **public_html/build/** (ose ku ke document root për asetet).

### C. Pas ngarkimit

1. Në cPanel File Manager, shko te **public_html/bootstrap/cache/** dhe **fshi** **config.php** nëse ekziston.
2. Kontrollo që **`.env`** në server është ende ai i production (me `APP_KEY`, databazë, etj.).
3. Hap **https://www.arontrade.net/** dhe bëj një rifreskim të fortë (Ctrl+F5).

---

## Përmbledhje: GitHub → cPanel

| Hapi | Mënyra 1 (Git në cPanel) | Mënyra 2 (ZIP) |
|------|---------------------------|----------------|
| 1 | cPanel → Git Version Control → Pull | GitHub → Code → Download ZIP → çpako |
| 2 | Lokalisht: `npm run build` → ngarko `public/build/` | Lokalisht: `npm run build` → ngarko `public/build/` |
| 3 | Mos prek `.env` në server | Mos ngarko `.env` nga ZIP |
| 4 | Fshi `bootstrap/cache/config.php` | Fshi `bootstrap/cache/config.php` |
| 5 | Rifresko faqen | Rifresko faqen |

Në të dyja mënyrat, **build i frontend** duhet bërë (lokalisht ose në server nëse ke Node), dhe **`.env`** në server duhet të mbetet ai i production.
