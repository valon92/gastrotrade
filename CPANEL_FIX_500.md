# Zgjidhja e HTTP 500 në cPanel (arontrade.net)

Gabimi 500 zakonisht vjen nga: **.env i munguar/jo të plotë**, **APP_KEY bosh**, ose **leje të gabuara** për `storage` dhe `bootstrap/cache`.

---

## 1. Sigurohu që ekziston `.env` (në rrënjën e projektit)

- Document root është **public_html/public**, pra rrënja e projektit është **public_html**.
- Në **File Manager** hap **public_html** (një nivel mbi `public`).
- Duhet të ketë skedar **`.env`**. Nëse nuk ka:
  - Kopjo **`.env.example`** dhe riemëroje kopjen në **`.env`**.

---

## 2. Vendos APP_KEY në `.env`

Nëse **APP_KEY=** është bosh, Laravel jep 500.

**Opsioni A – Terminal në cPanel (nëse ke):**

1. cPanel → **Terminal** (ose **SSH**).
2. Shko te projekti:
   ```bash
   cd ~/public_html
   ```
3. Gjenero çelësin:
   ```bash
   php artisan key:generate
   ```
4. Kontrollo që në `.env` tani të ketë një rresht si:  
   `APP_KEY=base64:...`

**Opsioni B – Pa Terminal (manual):**

1. Hap **public_html/.env** me **Edit**.
2. Gjej rreshtin: `APP_KEY=`
3. Zëvendësoje me (kopjo të gjithë rreshtin):
   ```env
   APP_KEY=base64:2fl+Ktvk2UFH2e2Y2vT8kR2xH3nN0pL5qW7yZ9aB1cD3e=
   ```
   *(Ky është vetëm shembull – më mirë përdor `php artisan key:generate` nëse ke Terminal.)*  
   Ose gjenero një 32-byte base64 key online dhe vendose:  
   `APP_KEY=base64:XXXX...`

---

## 3. Konfiguro për production në `.env`

Në **public_html/.env** ndrysho/përditëso:

```env
APP_NAME="AronTrade"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.arontrade.net
```

Për databazën (nëse përdor MySQL në cPanel):

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=emri_i_database
DB_USERNAME=useri_i_database
DB_PASSWORD=fjalëkalimi
```

(Emrin e database, user dhe fjalëkalimin i gjen në cPanel → **MySQL® Databases**.)

---

## 4. Lejet për `storage` dhe `bootstrap/cache`

Laravel ka nevojë të shkruajë në këto foldera.

1. Në **File Manager** shko te **public_html**.
2. Kliko të djathtë te **storage** → **Change Permissions** (ose **Permissions**).
   - Vendos **755** ose **775**; nëse ke opsion “Recurse into subdirectories”, aktivizoje dhe apliko.
3. Të njëjtën gjë për **bootstrap/cache**: permissions **755** ose **775** (dhe nëndosjet, nëse ke recurse).

Nëse 775 jep ende gabim, provo 755; disa servera kërkojnë 755.

---

## 5. Kontrollo logun e gabimit

Për të parë shkakun e saktë të 500:

1. Hap **public_html/storage/logs/laravel.log** (skedari më të fundit, fundi i skedarit).
2. Shiko rreshtat që fillojnë me `[timestamp] production.ERROR:` – aty shkruhet stack trace dhe mesazhi i gabimit.

Shembuj:
- **"No application encryption key has been specified"** → APP_KEY bosh (hapi 2).
- **"Access denied for user"** / **"SQLSTATE"** → konfigurimi i databazës në `.env` (hapi 3).
- **"Permission denied"** / **"failed to open stream"** → lejet për `storage` ose `bootstrap/cache` (hapi 4).

---

## 6. Pastro cache (nëse ke Terminal)

```bash
cd ~/public_html
php artisan config:clear
php artisan cache:clear
```

Pastaj provo përsëri: https://www.arontrade.net/

---

## Checklist

- [ ] **public_html/.env** ekziston (kopjuar nga `.env.example` nëse duhet).
- [ ] **APP_KEY=** në `.env` nuk është bosh (key:generate ose vlerë manuale).
- [ ] **APP_ENV=production**, **APP_DEBUG=false**, **APP_URL=https://www.arontrade.net**.
- [ ] **DB_*** të konfiguruara nëse përdor MySQL.
- [ ] **storage** dhe **bootstrap/cache** me permissions 755 ose 775.
- [ ] Kontrolluar **storage/logs/laravel.log** për mesazhin e saktë të gabimit.

Pas këtyre hapave, 500 duhet të hiqet; nëse vazhdon, mesazhi i saktë nga **laravel.log** e tregon hapin tjetër.
