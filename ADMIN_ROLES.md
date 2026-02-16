# Sistemi i Roleve për Admina

## Përmbledhje

Sistemi i roleve lejon krijimin e admina me të drejta të ndryshme:
- **order_manager**: Mund të bëjë vetëm porosi dhe të shohë shitjet
- **admin**: Ka të drejtë për menaxhimin e plotë të platformës

## Rolet

### 1. Order Manager (`order_manager`)
**Të drejtat:**
- ✅ Mund të shohë dhe printojë faktura nga `/admin/sales`
- ✅ Mund të bëjë porosi (nëse ka akses në shportë)
- ❌ Nuk mund të menaxhojë klientët
- ❌ Nuk mund të menaxhojë produktet
- ❌ Nuk mund të menaxhojë stokun
- ❌ Nuk mund të shohë historinë e fshirjeve

### 2. Admin (`admin`)
**Të drejtat:**
- ✅ Të gjitha të drejtat e Order Manager
- ✅ Mund të menaxhojë klientët
- ✅ Mund të menaxhojë produktet
- ✅ Mund të menaxhojë stokun
- ✅ Mund të shohë historinë e fshirjeve
- ✅ Mund të menaxhojë furnitorët
- ✅ Mund të menaxhojë fatura të furnitorëve

## Konfigurimi

### 1. Migration
Ekzekuto migration për të shtuar fushën `role`:
```bash
php artisan migrate
```

### 2. Krijimi i përdoruesve

#### Përdorues me rol Admin (menaxhim i plotë):
```sql
UPDATE users SET role = 'admin', is_admin = 1 WHERE email = 'admin@example.com';
```

#### Përdorues me rol Order Manager (vetëm porosi):
```sql
UPDATE users SET role = 'order_manager', is_admin = 1 WHERE email = 'order@example.com';
```

### 3. Ndryshimi i rolit të një përdoruesi
```sql
-- Ndrysho në admin
UPDATE users SET role = 'admin' WHERE email = 'user@example.com';

-- Ndrysho në order_manager
UPDATE users SET role = 'order_manager' WHERE email = 'user@example.com';
```

## Implementimi Teknik

### Backend

1. **Model User** (`app/Models/User.php`):
   - Metoda `isFullAdmin()`: Kontrollon nëse përdoruesi është admin
   - Metoda `isOrderManager()`: Kontrollon nëse përdoruesi është order manager
   - Metoda `canManage()`: Kontrollon nëse përdoruesi mund të menaxhojë

2. **Middleware** (`app/Http/Middleware/CheckAdminRole.php`):
   - Kontrollon rolin e përdoruesit para se të lejojë akses në routes të mbrojtura

3. **AuthController** (`app/Http/Controllers/Api/AuthController.php`):
   - Kthen rolin e përdoruesit në përgjigjen e login

### Frontend

1. **Store** (`resources/js/stores/adminStore.js`):
   - Ruan informacionin e përdoruesit dhe rolin
   - Metoda për kontrollimin e të drejtave:
     - `canManage()`: Kontrollon nëse mund të menaxhojë
     - `canViewSales()`: Kontrollon nëse mund të shohë shitjet
     - `canManageClients()`: Kontrollon nëse mund të menaxhojë klientët
     - `canManageProducts()`: Kontrollon nëse mund të menaxhojë produktet
     - `canManageStock()`: Kontrollon nëse mund të menaxhojë stokun

2. **Komponentët Admin**:
   - Përdorin `v-if` për të shfaqur/fshehur funksione bazuar në rol
   - Kontrollojnë rolin në `mounted()` dhe ridrejtojnë nëse nuk kanë akses

## Shembuj

### Krijimi i një përdoruesi të ri me rol Order Manager
```php
User::create([
    'name' => 'Order Manager',
    'email' => 'order@example.com',
    'password' => Hash::make('password'),
    'is_admin' => true,
    'role' => 'order_manager'
]);
```

### Krijimi i një përdoruesi të ri me rol Admin
```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'is_admin' => true,
    'role' => 'admin'
]);
```

## Shënime

- Të gjithë përdoruesit admin duhet të kenë `is_admin = 1`
- Roli default është `order_manager` nëse nuk specifikohet
- Order managers ridrejtohen automatikisht në `/admin/sales` pas login
- Admina kanë akses në të gjitha faqet e menaxhimit

