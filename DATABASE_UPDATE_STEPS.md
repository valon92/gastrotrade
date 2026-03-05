# Si të bësh update të databazës (të gjitha produktet + barcode)

Nëse tani ke vetëm 10 produkte dhe do të kesh të gjitha 35 + barcode/specs, ndiq këto hapa **një nga një** dhe **ndalo pas çdo hapi** përpara se të kalosh te tjetri.

---

## Hapi 1 – Hap cPanel dhe phpMyAdmin

1. Hyr në **cPanel** (adresa që përdor për hosting).
2. Gjej ikonën **phpMyAdmin** dhe kliko që të hapet.
3. Nëse të kërkon të identifikohesh, vendos përdoruesin dhe fjalëkalimin e cPanel.

**→ Ndalo këtu. Sigurohu që phpMyAdmin është i hapur.**

---

## Hapi 2 – Zgjidh databazën

1. Në **majtë** të phpMyAdmin shikon listën e databazave.
2. Kliko mbi **`aronqbxm_arontrade`** që të zgjidhet.
3. Në qendër do të shikosh listën e tabelave (users, products, categories, etj.).

**→ Ndalo këtu. Sigurohu që databaza e zgjedhur është aronqbxm_arontrade.**

---

## Hapi 3 – (Opsional) Bëj backup

Nëse ke shtuar **klientë, porosi** ose të dhëna të tjera që nuk do t’i humbësh:

1. Me databazën **aronqbxm_arontrade** të zgjedhur, kliko tab-in **Export** (Eksporto) lart.
2. Lëre **Quick** dhe **SQL**.
3. Kliko **Go** (Shko) dhe ruaj skedarin në kompjuter (p.sh. `backup_arontrade_4mars.sql`).

Nëse **nuk** ke të dhëna të rëndësishme, mund ta kapërcesh këtë hap.

**→ Ndalo këtu. Pastaj vazhdo me Hapi 4.**

---

## Hapi 4 – Fshi tabelat që do të rifillohen

Zgjidh **njërin** nga dy variantet më poshtë. **Ndalo** pas çdo nën-hapi.

**Varianti A – Re-import i plotë (rifilloj çdo gjë; humbën klientët/porositë nëse i ke):**

1. Në phpMyAdmin, me **aronqbxm_arontrade** të zgjedhur, kliko tab-in **Structure** (Struktura).
2. Poshtë, te "Check All" / "Zgjidh të gjitha", zgjidh **të gjitha** tabelat.
3. Në dropdown-in pranë (zakonisht "With selected"), zgjidh **Drop** (Fshi).
4. Konfirmo me **OK** kur të pyesë.

**→ Ndalo.** Pas kësaj databaza është bosh. Vazhdo me **Hapi 5A** më poshtë.

---

**Varianti B – Fshi vetëm products dhe product_images (ruaj përdoruesit, kategoritë, klientët, porositë):**

1. Kliko mbi tabelën **`product_images`** (në listën e tabelave majtas).
2. Shko te tab-i **Operations** (Operacionet).
3. Poshtë, te "Remove table" / "Drop the table", kliko **Drop** dhe konfirmo me **OK**.
4. Kthehu te lista e tabelave dhe kliko mbi **`products`**.
5. Përsëri **Operations** → **Drop the table** → **OK**.

**→ Ndalo.** Tani nuk ke më tabela `products` dhe `product_images`. Vazhdo me **Hapi 5B** më poshtë.

---

## Hapi 5A – Importo skedarin e plotë (vetëm nëse ke bërë Variantin A)

1. Në phpMyAdmin, me **aronqbxm_arontrade** të zgjedhur, kliko tab-in **Import** (Importo).
2. Kliko **Choose File** dhe zgjidh skedarin **`database/arontrade_mysql_import.sql`** nga projekti AronTrade.
3. Lëre **Format: SQL**, **Character set: utf-8**. Kliko **Go** (Shko).
4. Prit derisa të shfaqet mesazhi jeshil (Import successfully finished).

**→ Ndalo këtu. Nëse ka error të kuq, mos vazhdo – më shkruaj.**

---

## Hapi 5B – Importo skedarin plotësues (vetëm nëse ke bërë Variantin B)

1. Në phpMyAdmin, me **aronqbxm_arontrade** të zgjedhur, kliko tab-in **Import** (Importo).
2. Kliko **Choose File** dhe zgjidh skedarin **`database/arontrade_mysql_supplement.sql`** (jo `arontrade_mysql_import.sql`).
3. Lëre **Format: SQL**, **Character set: utf-8**. Kliko **Go** (Shko).
4. Prit derisa të shfaqet mesazhi jeshil.

**→ Ndalo këtu. Nëse del error (p.sh. tabela suppliers nuk ekziston), sigurohu që ke bërë një herë import të plotë më parë ose përdor Hapi 5A.**

---

## Hapi 6 – Fshi cache në server

1. Hap **cPanel → File Manager**.
2. Shko te **public_html** → **bootstrap** → **cache**.
3. Nëse ekziston skedari **`config.php`**, fshije (Delete).

**→ Ndalo këtu.**

---

## Hapi 7 – Kontrollo në faqe

1. Hap në shfletues: **https://www.arontrade.net/**
2. Kontrollo që në **Home** shfaqen **të gjitha produktet** (përfshirë kese, gota, etj.).
3. Hap një produkt dhe kontrollo që shfaqen **barcode**, **size**, **liters** (ku aplikohet).
4. Kontrollo që **imazhet** shfaqen (nëse ke ngarkuar folderin **images** në server si në CPANEL_SETUP.md).

**→ Pas këtij hapi update-i i databazës për produkte dhe barcode është mbaruar.**

---

## Përmbledhje

| Hapi | Çfarë bën |
|------|-----------|
| 1 | Hap cPanel dhe phpMyAdmin |
| 2 | Zgjidh databazën aronqbxm_arontrade |
| 3 | (Opsional) Export backup |
| 4 | **A:** Fshi të gjitha tabelat **ose** **B:** Fshi vetëm `products` dhe `product_images` |
| 5A | Import → `arontrade_mysql_import.sql` (vetëm pas Variantit A) |
| 5B | Import → `arontrade_mysql_supplement.sql` (vetëm pas Variantit B) |
| 6 | Fshi public_html/bootstrap/cache/config.php |
| 7 | Kontrollo faqen dhe barcode/imazhet |
