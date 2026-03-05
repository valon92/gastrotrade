# Konfigurimi i AronTrade në cPanel (arontrade.net)

## Problemi: "Index of /" në vend të faqes

Nëse shikon listën e dosjeve (app, bootstrap, config, public, vendor...) në https://www.arontrade.net/, document root i domain-it **nuk** tregon në folderin `public` të Laravel.

## Zgjidhja (pa prekur kodin lokal)

### Hapi 1: Ndrysho Document Root në cPanel

1. Hyr në **cPanel**.
2. Shko te **Domains** → **Domains** (ose **Addon Domains** nëse arontrade.net është addon domain).
3. Gjej **arontrade.net** (ose www.arontrade.net) dhe kliko **Manage** / **Manage Domain**.
4. Te **Document Root** (ose "Root Domain", "Website Root"):
   - Nëse tani është diçka si: `public_html` ose `arontrade.net`
   - Ndryshoje në: **`public_html/public`** (ose `arontrade.net/public` – rruga ku ndodhet folderi `public` i projektit).
5. Ruaj ndryshimet.

**Shembull:**  
Nëse projekti është ngarkuar në `public_html/` (dhe brenda ke `app/`, `bootstrap/`, `public/`, `vendor/`...), document root duhet të jetë:

- `public_html/public`

Kështu serveri servon vetëm përmbajtjen e `public/` (index.php, build/, images/), jo rrënjën e projektit.

### Hapi 2: Sigurohu që .env ekziston në server

- Në rrënjën e projektit (një nivel mbi `public`) duhet të ketë skedarin **`.env`** (jo vetëm `.env.example`).
- Nëse nuk e ke: kopjo `.env.example` si `.env`, përshtat `APP_URL`, databazën, etj., dhe në SSH/terminal të serverit ekzekuto:  
  `php artisan key:generate`

### Hapi 3: Permissions

Në SSH (nëse ke):

```bash
cd /path/te/projekti  # p.sh. public_html ose ~/arontrade.net
chmod -R 755 storage bootstrap/cache
```

Nëse nuk ke SSH, zakonisht cPanel i vendos permissions të mjaftueshme; nëse ke gabime 500, kontrollo që `storage` dhe `bootstrap/cache` të jenë të shkrueshme.

---

## Nëse nuk mund të ndryshosh Document Root

Disa hoste (p.sh. kur "No configuration options currently exist" për domenin kryesor) nuk lejojnë Document Root të jetë `public_html/public`. Përdor këto hapa në **File Manager** të cPanel.

### Hapa (projekti mbetet në `public_html`)

1. **Kopjo përmbajtjen e `public/` në rrënjë (`public_html/`)**  
   - Hap **public_html/public/**.
   - Zgjidh **të gjitha** skedarët dhe folderat: `index.php`, `.htaccess`, `build/`, `images/`, `favicon.svg`, `robots.txt`, `check_env.php` (nëse e ke) – çdo gjë që është brenda `public/`.
   - Kopjo (Copy) dhe shko te **public_html/** (një nivel më lart) dhe Paste.  
   - Konfirmo kur të pyesë për mbishkrim: po për `.htaccess` dhe `index.php` (në `public_html` do të ketë tani edhe `index.php` dhe `.htaccess` nga `public/`).

2. **Vendos index.php që funksionon nga rrënja**  
   - Në projektin tënd lokal ekziston **`public/index_for_document_root.php`** (version i `index.php` që e përdor `public_html` si rrënjë, pa `../`).
   - Në server: në **public_html/** ngarko këtë skedar si **`index.php`** (ose kopjo përmbajtjen e `index_for_document_root.php` mbi `public_html/index.php`).  
   - Kështu kur hapet https://www.arontrade.net/ serveri ekzekuton `public_html/index.php`, i cili i gjen `vendor/`, `bootstrap/`, `storage/` në të njëjtin folder (`public_html`).

3. **Sigurohu që ke `.env` dhe `APP_KEY`** në `public_html/` (shiko Hapi 2 më lart). Fshi **`bootstrap/cache/config.php`** nëse ekziston.

Pas kësaj, https://www.arontrade.net/ duhet të hapë Laravel, jo "Index of /". Kjo bëhet vetëm në server; kodi lokal nuk ndryshohet.

---

## Imazhet e produkteve (kese, gota, etj.)

Nëse foto të produkteve (kese, gota, pallomat, etj.) **nuk shfaqen** në faqe, sigurohu që folderi **`public/images/`** nga projekti lokal është ngarkuar në server:

- **Ku:** Në cPanel File Manager, në **public_html/images/** (ose **public_html/public/images/** nëse document root është `public_html/public`).
- **Çfarë:** Të gjithë nënfolderët dhe skedarët: `IMG_6814.jpeg`, `IMG_6815.jpeg`, `IMG_6818.jpeg`, `Pallomat/`, `Gota Plastike/`, etj. – çdo gjë që ke në `public/images/` lokalisht.
- Rrugët në databazë janë si `/images/...`; serveri i servon nga `public_html/images/` (ose `public_html/public/images/`).

Nëse ke kopjuar tashmë përmbajtjen e `public/` në `public_html/`, kontrollo që **public_html/images/** përmban të njëjtat foto si lokalisht.

---

**Pas ndryshimit të document root në `public`, https://www.arontrade.net/ duhet të hapë aplikacionin Laravel, jo listën e dosjeve.**
