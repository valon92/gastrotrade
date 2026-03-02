# 🏗️ Krahasimi: cPanel vs AWS për AronTrade

Ky dokument ofron një krahasim të detajuar midis cPanel dhe AWS për deployment të projektit AronTrade.

---

## 📊 Krahasimi i Shpejtë

| Karakteristikë | cPanel | AWS |
|---------------|--------|-----|
| **Lehtësia e përdorimit** | ⭐⭐⭐⭐⭐ Shumë e lehtë | ⭐⭐ Kompleks |
| **Kosto (fillim)** | €3-10/muaj | €5-20/muaj (EC2) |
| **Kosto (rritje)** | €10-50/muaj | €20-200+/muaj |
| **Kontrolli** | I kufizuar | I plotë |
| **Skalueshmëria** | E kufizuar | E pakufishme |
| **Support** | Teknik i kufizuar | Dokumentacion i gjerë |
| **Setup Time** | 1-2 orë | 4-8 orë |

---

## 🎯 cPanel Hosting

### ✅ Avantazhet:

1. **Lehtësia e përdorimit**
   - Interface grafike (GUI) - nuk nevojitet terminal
   - File Manager për upload/manage skedarëve
   - Database management me phpMyAdmin
   - One-click SSL installation
   - Email management i integruar

2. **Kosto e ulët**
   - €3-10/muaj për shared hosting
   - €15-30/muaj për VPS me cPanel
   - Nuk ka kosto të fshehura

3. **Setup i shpejtë**
   - 1-2 orë për setup të plotë
   - Nuk nevojitet njohuri e thellë teknike
   - Support nga hosting provider

4. **Më pak mirëmbajtje**
   - Updates automatikë të sistemit
   - Backup automatik (në shumicën e rasteve)
   - Security patches automatikë

### ❌ Disavantazhet:

1. **Kontroll i kufizuar**
   - Nuk mund të konfigurosh serverin plotësisht
   - Versionet e PHP/MySQL janë të fiksuara
   - Nuk mund të instalosh software të ri pa leje

2. **Performancë e kufizuar**
   - Shared resources me klientë të tjerë
   - CPU/RAM të kufizuara
   - Nuk mund të skalosh lehtë

3. **Node.js problemi**
   - Shumica e cPanel hosting nuk mbështesin Node.js
   - Duhet të build-osh lokal dhe të upload-osh assets
   - Nuk mund të ekzekutosh `npm run build` në server

4. **Kufizime teknike**
   - Nuk mund të konfigurosh Nginx/Apache plotësisht
   - Queue workers të vështira për konfigurim
   - Cron jobs të kufizuara

### 💰 Kosto e vlerësuar:

- **Shared Hosting**: €5-10/muaj
- **VPS me cPanel**: €20-40/muaj
- **Domain**: €10-15/vit

**Total fillestar**: ~€15-25/muaj

### 🎯 Kur të përdorësh cPanel:

- ✅ Projekti është i vogël deri në mesatar
- ✅ Nuk ke eksperiencë me server management
- ✅ Budget i kufizuar
- ✅ Nuk ke nevojë për skalueshmëri të madhe
- ✅ Build-on assets lokal dhe upload-on në server

---

## ☁️ AWS Hosting

### ✅ Avantazhet:

1. **Kontroll i plotë**
   - Kontroll i plotë mbi serverin
   - Mund të instalosh çdo software
   - Konfigurim i plotë i Nginx/Apache
   - Versionet e PHP/MySQL që dëshiron

2. **Skalueshmëri**
   - Mund të rrisësh resources kur nevojitet
   - Load balancing për trafik të lartë
   - Auto-scaling groups
   - CDN me CloudFront

3. **Performancë e lartë**
   - Dedicated resources
   - SSD storage
   - Network e shpejtë
   - Caching me Redis/ElastiCache

4. **Funksionalitete të avancuara**
   - RDS për database managed
   - S3 për storage të imazheve
   - CloudFront për CDN
   - Route 53 për DNS
   - SES për email

5. **Node.js support**
   - Mund të ekzekutosh `npm run build` direkt në server
   - Mund të konfigurosh CI/CD pipelines
   - Git deployment automatik

### ❌ Disavantazhet:

1. **Kompleksitet**
   - Kërkon njohuri teknike
   - Setup i gjatë (4-8 orë)
   - Konfigurim manual i shumë gjërave

2. **Kosto më e lartë**
   - €20-50/muaj për EC2 t2.micro
   - €50-200+/muaj për instanca më të mëdha
   - Kosto shtesë për RDS, S3, CloudFront

3. **Mirëmbajtje**
   - Duhet të bësh updates manualisht
   - Security patches manual
   - Monitoring dhe backup manual

4. **Kurba e mësimit**
   - Duhet të mësosh AWS services
   - Dokumentacion i gjerë por kompleks
   - Mund të bësh gabime që kushtojnë

### 💰 Kosto e vlerësuar:

**Opsioni 1: EC2 Basic (t2.micro)**
- EC2 Instance: €15-25/muaj
- RDS MySQL (db.t3.micro): €15-20/muaj
- S3 Storage: €1-5/muaj
- Data Transfer: €5-10/muaj
- **Total**: €36-60/muaj

**Opsioni 2: EC2 Medium (t3.medium)**
- EC2 Instance: €40-60/muaj
- RDS MySQL (db.t3.small): €30-40/muaj
- S3 Storage: €5-10/muaj
- CloudFront CDN: €10-20/muaj
- **Total**: €85-130/muaj

**Opsioni 3: AWS Lightsail (më e lehtë)**
- Lightsail 2GB: €10/muaj
- Lightsail Database: €15/muaj
- **Total**: €25/muaj (më e lirë se EC2)

### 🎯 Kur të përdorësh AWS:

- ✅ Projekti ka nevojë për skalueshmëri
- ✅ Ke eksperiencë me server management
- ✅ Budget më i madh
- ✅ Nevojë për performancë të lartë
- ✅ Dëshiron kontroll të plotë

---

## 🏆 Rekomandimi për AronTrade

### Për fillim: **cPanel Shared Hosting** ✅

**Arsyet:**

1. **Projekti është i vogël-mesatar**
   - Nuk ka nevojë për skalueshmëri të madhe fillimisht
   - Trafiku fillestar do të jetë i kufizuar

2. **Kosto e ulët**
   - €5-10/muaj vs €36-60/muaj në AWS
   - Budget më i mirë për fillim

3. **Setup i shpejtë**
   - 1-2 orë për të qenë live
   - Më pak kompleksitet

4. **Node.js workaround**
   - Build-on assets lokal me `npm run build`
   - Upload `public/build` në server
   - Nuk është problem për këtë projekt

### Kur të kalosh në AWS:

Konsidero AWS kur:
- Trafiku rritet mbi 10,000 vizitorë/ditë
- Nevojë për performancë më të lartë
- Nevojë për skalueshmëri automatike
- Budget rritet dhe mund të mbështesë kosto më të lartë

---

## 📝 Udhëzime për Deployment

### cPanel Deployment:

1. **Build assets lokal:**
   ```bash
   npm run build
   ```

2. **Upload në cPanel:**
   - Upload të gjitha skedarët përmes File Manager
   - Vendos `.env` manualisht (mos e upload-osh nga Git)

3. **Konfiguro database:**
   - Krijo database nga cPanel
   - Vendos credentials në `.env`

4. **Set permissions:**
   - `storage/` dhe `bootstrap/cache/` → 775

5. **SSL:**
   - Install Let's Encrypt nga cPanel

### AWS Deployment:

Shiko `DEPLOYMENT.md` për udhëzime të detajuara.

---

## 🔄 Migrimi nga cPanel në AWS

Kur të jesh gati të migrosh:

1. **Backup:**
   - Database dump
   - Storage files
   - `.env` configuration

2. **Setup AWS:**
   - EC2 instance
   - RDS database
   - S3 për storage

3. **Deploy:**
   - Upload kode
   - Import database
   - Konfiguro `.env`

4. **DNS:**
   - Update DNS records
   - Point domain në AWS

---

## 📊 Tabela e Vendimit

| Kriteri | cPanel | AWS |
|---------|--------|-----|
| **Budget fillestar** | ✅ Më i lirë | ❌ Më i shtrenjtë |
| **Lehtësia** | ✅ Shumë e lehtë | ❌ Kompleks |
| **Skalueshmëria** | ❌ E kufizuar | ✅ E pakufishme |
| **Performancë** | ⚠️ Mesatare | ✅ E lartë |
| **Kontrolli** | ❌ I kufizuar | ✅ I plotë |
| **Support** | ⚠️ Varësisht nga provider | ✅ Dokumentacion i gjerë |

---

## 🎯 Konkluzioni

**Për AronTrade, rekomandoj cPanel për fillim:**

1. ✅ Kosto më e ulët
2. ✅ Setup më i shpejtë
3. ✅ Më pak kompleksitet
4. ✅ Mjafton për nevojat fillestare

**Kaloni në AWS kur:**
- Projekti rritet
- Trafiku rritet
- Nevojë për performancë më të lartë
- Budget rritet

---

## 📞 Hosting Providers të Rekomanduar

### cPanel Hosting:
- **Namecheap** - €3-5/muaj, i mirë për fillim
- **SiteGround** - €5-10/muaj, support i mirë
- **Hostinger** - €2-5/muaj, shumë i lirë
- **DigitalOcean App Platform** - €5-12/muaj, më modern

### AWS Alternatives:
- **AWS Lightsail** - €10-40/muaj, më e lehtë se EC2
- **DigitalOcean Droplets** - €6-12/muaj, më e thjeshtë se AWS
- **Linode** - €5-10/muaj, alternativë e mirë
- **Vultr** - €6-12/muaj, performancë e mirë

---

**Shënim**: Çmimet janë të përafërta dhe mund të ndryshojnë. Kontrollo çmimet aktuale në website-et e provider-ëve.
