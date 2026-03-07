# FTP në cPanel + GitHub Actions – Hap pas hapi (nga fillimi)

Ky udhëzues të çon hap pas hapi: fshirja e llogarisë FTP të vjetër, krijimi i një të re me qasje në `public_html`, dhe lidhja me GitHub Secrets që deploy automatik të funksionojë.

---

## Pjesa 1 – cPanel: Fshirja e të gjitha llogarive FTP (jo Special)

1. Hyr në **cPanel**: https://premium132.web-hosting.com:2083/
2. Shkruaj **FTP** në kërkim dhe kliko **FTP Accounts**.
3. Nën **FTP Accounts** (jo "Special FTP Accounts") shiko listën – çdo llogari që ke krijuar ti (p.sh. `macbook@arontrade.net`, `deploy@arontrade.net`).
4. Për **çdo** llogari: kliko **Delete** → kur të pyesë "permanently remove all files in the following directory", konfirmo (**Yes** / **Delete**). Skedarët e faqes janë në **public_html**, jo në atë folder.
5. Përsërit derisa të mos mbetet asnjë llogari në **FTP Accounts** (vetëm **Special FTP Accounts** – aronqbxm, aronqbxm_logs – mbeten; ato nuk fshihen). **Ndalo** – të gjitha llogaritë e krijuara nga ti janë fshirë.

---

## Pjesa 2 – cPanel: Krijimi i llogarisë FTP të re

1. Në të njëjtën faqe **FTP Accounts**, shko te **Add FTP Account** (sipër).
2. Plotëso:
   - **Log In:** shkruaj vetëm **`arontrade`** (pa @, pa hapësira). cPanel e bën automatikisht **arontrade@arontrade.net**. Ky do të jetë përdoruesi FTP (nuk përdor emra si macbook).
   - **Password:** kliko **Password Generator**, gjenero një fjalëkalim të fortë, kopjoje dhe **ruaje në një vend të sigurt** (do ta përdorësh në GitHub). Ose vendos një fjalëkalim të thjeshtë por të fortë (p.sh. 12+ karaktere, shkronja + numra).
   - **Password (Again):** përsërit të njëjtin fjalëkalim.
   - **Directory:** kjo është **shumë e rëndësishme**. Duhet të jetë **rrënja e llogarisë**, që të kesh qasje në `public_html`. Vendos:
     ```
     /home/aronqbxm/
     ```
     Sigurohu që **nuk** e ke diçka si `/home/aronqbxm/arontrade.net/deploy` – në atë rast përdoruesi nuk sheh `public_html` dhe deploy dështon. Duhet **saktësisht** `/home/aronqbxm/` (me slash në fund).
   - **Quota:** mund ta lësh **Unlimited**.
3. Kliko **Create FTP Account**.
4. **Ndalo** – shëno:
   - **FTP Username:** `arontrade@arontrade.net` (ose siç e tregon cPanel).
   - **FTP server:** `ftp.arontrade.net`.
   - **FTP & explicit FTPS port:** `21`.
   - **Fjalëkalimin** që ke vendosur (ruaje në sigurt).

---

## Pjesa 3 – GitHub: Vendosja e Secrets (FTP_SERVER, FTP_USERNAME, FTP_PASSWORD)

1. Hap repozitorin në GitHub: **https://github.com/valon92/gastrotrade**
2. Kliko **Settings** (skeda në menü).
3. Në menünë e majtë, nën **Security**, kliko **Secrets and variables** → **Actions**.
4. Nën **Repository secrets** do të shikosh **FTP_SERVER**, **FTP_USERNAME**, **FTP_PASSWORD** (nëse i ke krijuar më parë). Përditësoji një nga një:
   - Kliko mbi **FTP_SERVER** → **Update** → në fushën **Value** shkruaj: **`ftp.arontrade.net`** (pa hapësira, pa `ftp://`) → **Update secret**.
   - Kliko mbi **FTP_USERNAME** → **Update** → shkruaj: **`arontrade@arontrade.net`** (saktësisht siç e tregon cPanel, me @) → **Update secret**.
   - Kliko mbi **FTP_PASSWORD** → **Update** → ngjit **fjalëkalimin** që ke vendosur për llogarinë FTP të re (pa hapësira para ose pas) → **Update secret**.
5. Nëse ndonjë secret nuk ekziston ende, kliko **New repository secret** dhe krijo:
   - **Name:** `FTP_SERVER`  → **Secret:** `ftp.arontrade.net`
   - **Name:** `FTP_USERNAME` → **Secret:** `arontrade@arontrade.net`
   - **Name:** `FTP_PASSWORD` → **Secret:** fjalëkalimi i llogarisë FTP
6. **Ndalo** – të tre secretet duhet të jenë të përditësuar / të krijuar.

---

## Pjesa 4 – Nisja e deploy-it (trigger i workflow-it)

1. Në kompjuterin tënd, hap **Terminal** dhe shko te folderi i projektit:
   ```bash
   cd /Users/valonsylejmani/AronTrade
   ```
2. Bëj një commit bosh dhe push që të nisë workflow-in:
   ```bash
   git commit --allow-empty -m "Trigger deploy - FTP arontrade"
   git push origin main
   ```
3. Hap **https://github.com/valon92/gastrotrade/actions** dhe prit 20–40 sekonda.
4. Duhet të shfaqet run i ri (**Trigger deploy - FTP arontrade**). Kliko mbi të.
5. Kontrollo:
   - Nëse **të gjitha hapat** janë me ✓ jeshil (përfshirë **Deploy to cPanel via FTP**) → deploy-i ka funksionuar. Hap **https://www.arontrade.net/** dhe rifresko; ndryshimet duhet të jenë aty.
   - Nëse **Deploy to cPanel via FTP** jep **530 Login authentication failed** → kontrollo përsëri në cPanel që **Directory** e llogarisë është **/home/aronqbxm/** dhe në GitHub që **FTP_USERNAME** dhe **FTP_PASSWORD** janë saktësisht siç i ke vendosur (pa hapësira). Ndrysho fjalëkalimin e llogarisë FTP në cPanel në diçka të thjeshtë (vetëm shkronja dhe numra), përditëso **FTP_PASSWORD** në GitHub dhe bëj përsëri push.

---

## Përmbledhje e shpejtë

| Ku | Çfarë |
|----|--------|
| **cPanel → FTP Accounts** | Fshi të gjitha llogaritë e krijuara (macbook@..., deploy@...). Krijo të re: Log In = **arontrade**, Directory = **/home/aronqbxm/** |
| **GitHub → Settings → Secrets and variables → Actions** | FTP_SERVER = **ftp.arontrade.net**, FTP_USERNAME = **arontrade@arontrade.net**, FTP_PASSWORD = fjalëkalimi i ri |
| **Terminal** | `git commit --allow-empty -m "Trigger deploy"` → `git push origin main` |
| **GitHub → Actions** | Shiko run-in e ri; nëse Deploy është jeshil, faqja është e përditësuar. |

---

**Shënim sigurie:** Nëse ke shkruar fjalëkalimin e FTP në ndonjë chat ose dokument, ndërroje menjëherë në cPanel (FTP Accounts → Change Password për **arontrade@arontrade.net**) dhe përditëso **FTP_PASSWORD** në GitHub me të rinj.
