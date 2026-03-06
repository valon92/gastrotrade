# Si të bësh update në server (cPanel) kur bën ndryshime në localhost

Kur ndryshon diçka në projekt **lokalisht** dhe do që **www.arontrade.net** të pasqyrojë ndryshimet, ndiq këto hapa **me radhë**. Ndalo pas çdo hapi nëse do të kontrollosh rezultatin.

---

## Deploy automatik: push në GitHub → ndryshimet në arontrade.net

Nëse ke konfiguruar **GitHub Actions** me FTP (shiko më poshtë), mjafton:

1. **Terminal (lokalisht):** bëj ndryshimet, pastaj  
   `git add .` → `git commit -m "Përshkrim"` → `git push origin main`
2. **GitHub** ekzekuton automatikisht workflow-in: bën **build** (npm run build), përdor **index.php** të saktë për document root, dhe **ngarkon** në **public_html** në cPanel përmes FTP.
3. Pas 1–2 minutash **https://www.arontrade.net/** pasqyron ndryshimet (përfshirë footer, Vue, PHP). Nuk ke nevojë të bësh Pull/Deploy ose ngarkim manual në cPanel.

### Konfigurim një herë (FTP + GitHub Secrets)

1. **Krijo llogari FTP në cPanel** (opsional por i rekomanduar): cPanel → **FTP Accounts** → **Add FTP Account**:
   - **Log In:** p.sh. `deploy` (do të bëhet `deploy@arontrade.net`; ky është përdoruesi FTP).
   - **Password:** vendos një fjalëkalim të fortë dhe ruaje (do ta përdorësh në GitHub si `FTP_PASSWORD`).
   - **Directory:** lërë **/home/aronqbxm/** (që llogaria të ketë qasje në `public_html`). Mos e kufizosh vetëm në një nënfolder nëse do të ngarkosh në `public_html`.
   - Kliko **Create FTP Account**. Ose përdor llogarinë kryesore **aronqbxm** (Special FTP Account) me fjalëkalimin e cPanel; atëherë në GitHub vendos `FTP_USERNAME` = `aronqbxm` dhe `FTP_PASSWORD` = fjalëkalimi i cPanel.
   - **Adresa e serverit FTP** zakonisht është **ftp.arontrade.net** ose **arontrade.net** (për FTP); për disa hoste është emri i serverit (p.sh. `premium132.web-hosting.com`). Mund ta provosh me një klient FTP (FileZilla) për të konfirmuar.
   - **Nëse GitHub Actions jep "530 Login authentication failed":** provo me **FileZilla** nga kompjuteri yt: Host = adresa FTP, User = përdoruesi, Password = fjalëkalimi. Çfarë funksionon atje (saktësisht) vendose në GitHub Secrets. Për llogarinë kryesore shumë hoste duan vetëm **aronqbxm**; për llogari shtesë **deploy@arontrade.net**. Fjalëkalimi pa hapësira. Ruaj Secrets dhe bëj push.
2. **Shto Secrets në GitHub:** hap repozitorin **valon92/gastrotrade** → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**. Krijo tre secrets:
   - **FTP_SERVER** = adresa e serverit FTP (p.sh. `ftp.arontrade.net` ose `premium132.web-hosting.com`, pa `ftp://`)
   - **FTP_USERNAME** = përdoruesi FTP
   - **FTP_PASSWORD** = fjalëkalimi FTP
3. Ruaj. Workflow-i `.github/workflows/deploy-cpanel.yml` ekzekutohet çdo herë që bën **push** në **main**; ai bën build dhe ngarkon në **public_html** (pa prekur **.env** ose **vendor** në server).

Nëse nuk ke FTP ose do të përdorësh vetëm Pull + Deploy/kopjim manual, përdor udhëzimet më poshtë.

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

**Ku ndodhet Deploy në cPanel?**  
cPanel → **Tools** (ose **Files**) → **Git™ Version Control** → kliko mbi repozitorin **arontrade** (ose **Manage**) → në faqen **Manage Repository** shikon seksionin **"Pull or Deploy"**; aty janë butonat **Pull** dhe **Deploy**. Fillimisht bëj **Pull**, pastaj **Deploy**.

**Pas Deploy del HTTP 500?** Nëse document root është **public_html** (jo public_html/public), Deploy mbishkruan **index.php** me versionin standard të Laravel (që pret folderin `public/`). Duhet të përdorësh **index_for_document_root.php** si **index.php**. Në cPanel File Manager: hap **public_html** → hap **index_for_document_root.php**, kopjo të gjithë përmbajtjen → hap **index.php** → zëvendësoje të gjithë përmbajtjen me atë të kopjuar → Ruaj. Ose riemërto **index_for_document_root.php** në **index.php** (pas backup të index.php aktual). Pas kësaj faqja duhet të ngarkojë përsëri.

**Përse ndryshimet nuk duken në www.arontrade.net pas Pull?**  
Pull përditëson vetëm **arontrade_git**. Faqja e gjallë xhiron nga **public_html**, prandaj duhet të **kopjosh** kodin nga arontrade_git në public_html (me dorë ose me deploy-from-git.php). Për ndryshime në Vue/CSS (p.sh. ngjyra e footerit) duhet edhe **public/build/** i ri në server (build lokalisht + ngarkim në public_html/build/).

**Nëse "Deploy" nuk klikohet ose thotë "The system cannot deploy":** Përdor njërën nga këto:
- **Alternativa 1 – Deploy me një klik:** pas **një herë** kopjimi manual (që të ketë deploy-from-git.php në public_html), ndrysho në skedar `public_html/deploy-from-git.php` rreshtin `$DEPLOY_KEY = '...'` me një fjalë sekrete, ruaj; pastaj çdo herë pas Pull hap në shfletues: **https://www.arontrade.net/deploy-from-git.php?key=JEKODI** (zëvendëso JEKODI me atë fjalë). Skedari kopjon nga `arontrade_git` në `public_html`.
- **Alternativa 2 – Kopjim manual:** pas Pull, **File Manager** → hap **arontrade_git** → zgjidh të gjitha folderat dhe skedarët (app, bootstrap, config, database, public, resources, routes, artisan, composer.json, etj.; mund të lësh .git) → **Copy** → shko te **public_html** → **Paste** (mbishkruaj). Mos mbishkrua **.env** në public_html (nëse të pyet, jo).

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

1. Hyr në **cPanel** (p.sh. https://premium132.web-hosting.com:2083/).
2. Në menynë e majtë (ose në kërkim) gjej **Git™ Version Control** dhe kliko mbi të.
3. Në listën **List Repositories** do të shikosh repozitorin **arontrade** (path: `/home/aronqbxm/arontrade_git`). Kliko mbi emrin e repozitorit **arontrade** (ose butonin **Manage** pranë tij) që të hapet faqja e menaxhimit.
4. Në faqen **Manage Repository** ke seksionin **"Pull or Deploy"**. Atje:
   - Së pari kliko **Pull** (ose **Update**) që të tërhiqen ndryshimet e fundit nga GitHub. Prit derisa të mbarojë.
   - Pastaj, **në të njëjtin seksion "Pull or Deploy"**, kliko **Deploy**. Kjo ekzekuton skedarin **`.cpanel.yml`** dhe kopjon automatikisht kodin nga `arontrade_git` në `public_html`. **`.env`** në server nuk mbishkruhet.
5. Nëse **Deploy** nuk shfaqet ose **nuk klikohet** (butoni është i çaktivizuar):
   - **Opsioni A:** Pas Pull, hap në shfletues **https://www.arontrade.net/deploy-from-git.php?key=JEKODI**. Në File Manager hap **public_html/deploy-from-git.php**, ndrysho rreshtin `$DEPLOY_KEY = 'NdryshojeKeteFjalen';` dhe vendos një fjalë sekrete (p.sh. `$DEPLOY_KEY = 'FjaleSekrete123';`), ruaj; pastaj në URL përdor `?key=FjaleSekrete123`. Skedari kopjon nga `arontrade_git` në `public_html`. Për siguri, fshije `deploy-from-git.php` nga serveri kur nuk e përdor më.
   - **Opsioni B:** Kopjim manual: **File Manager** → **arontrade_git** → zgjidh të gjitha → **Copy** → **public_html** → **Paste** (mbishkruaj).

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
