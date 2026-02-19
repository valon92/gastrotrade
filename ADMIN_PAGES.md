# 📄 Faqet e Admin Panel dhe Kontrolli i Aksesit

Ky dokument përmban listën e plotë të faqeve të admin panel dhe kontrollin e aksesit për secilën faqe.

---

## 🔐 Sistemi i Autentifikimit

Të gjitha faqet e admin (përveç login) kërkojnë:
- ✅ Token valid në `localStorage` (`admin_token`)
- ✅ Përdorues i autentifikuar me `is_admin = 1`
- ✅ Rol valid (`admin` ose `order_manager`)

---

## 📋 Lista e Faqeve të Admin

### 1. **Admin Login** (`/admin/login`)
- **URL**: `/admin/login`
- **Kontrolli**: ❌ Nuk kërkon autentifikim
- **Përshkrimi**: Faqja e kyçjes për admina
- **Akses**: Të gjithë mund të hyjnë
- **Ridrejtim**: Nëse je i kyçur, të çon në `/admin/clients` (admin) ose `/admin/sales` (order_manager)

---

### 2. **Admin Clients** (`/admin/clients`)
- **URL**: `/admin/clients`
- **Kontrolli**: ✅ `requiresAuth: true`
- **Akses**: Vetëm **Admin** (`role = 'admin'`)
- **Order Manager**: ❌ Nuk ka akses (ridrejtohet në `/admin/sales`)
- **Funksionaliteti**:
  - Shikim i të gjithë klientëve
  - Krijim i klientëve të rinj
  - Editim i klientëve
  - Fshirje e klientëve
  - Menaxhim i çmimeve për klientë
  - Shikim i lokacioneve të klientëve

---

### 3. **Admin Client Prices** (`/admin/clients/:clientId/prices`)
- **URL**: `/admin/clients/:clientId/prices`
- **Kontrolli**: ✅ `requiresAuth: true`
- **Akses**: Vetëm **Admin** (`role = 'admin'`)
- **Order Manager**: ❌ Nuk ka akses
- **Funksionaliteti**:
  - Shikim i çmimeve të personalizuara për një klient
  - Shtim i çmimeve të reja
  - Editim i çmimeve ekzistuese
  - Fshirje i çmimeve

---

### 4. **Admin Products** (`/admin/products`)
- **URL**: `/admin/products`
- **Kontrolli**: ✅ `requiresAuth: true`
- **Akses**: Vetëm **Admin** (`role = 'admin'`)
- **Order Manager**: ❌ Nuk ka akses
- **Funksionaliteti**:
  - Shikim i të gjithë produkteve
  - Krijim i produkteve të rinj
  - Editim i produkteve
  - Fshirje e produkteve (soft delete)
  - Menaxhim i kategorive
  - Upload i imazheve për produkte
  - Menaxhim i paketave dhe sasive

---

### 5. **Admin Stock** (`/admin/stock`)
- **URL**: `/admin/stock`
- **Kontrolli**: ✅ `requiresAuth: true`
- **Akses**: Vetëm **Admin** (`role = 'admin'`)
- **Order Manager**: ❌ Nuk ka akses
- **Funksionaliteti**:
  - Shikim i stokut të produkteve
  - Përditësim i stokut
  - Menaxhim i hyrjeve/daljeve të stokut
  - Historiku i ndryshimeve të stokut

---

### 6. **Admin Supplier Invoices** (`/admin/supplier-invoices`)
- **URL**: `/admin/supplier-invoices`
- **Kontrolli**: ✅ `requiresAuth: true`
- **Akses**: Vetëm **Admin** (`role = 'admin'`)
- **Order Manager**: ❌ Nuk ka akses
- **Funksionaliteti**:
  - Shikim i faturave të furnitorëve
  - Krijim i faturave të reja
  - Editim i faturave
  - Menaxhim i furnitorëve

---

### 7. **Admin Trash** (`/admin/trash`)
- **URL**: `/admin/trash`
- **Kontrolli**: ✅ `requiresAuth: true`
- **Akses**: Vetëm **Admin** (`role = 'admin'`)
- **Order Manager**: ❌ Nuk ka akses
- **Funksionaliteti**:
  - Shikim i produkteve të fshira (soft delete)
  - Restaurim i produkteve
  - Fshirje e përhershme (hard delete)

---

### 8. **Admin Sales** (`/admin/sales`) ⭐
- **URL**: `/admin/sales`
- **Kontrolli**: ✅ `requiresAuth: true`
- **Akses**: ✅ **Admin** dhe ✅ **Order Manager**
- **Funksionaliteti**:
  - Shikim i të gjithë porosive/shitjeve
  - Filtrim sipas statusit, pagesës, datës
  - Shikim i detajeve të porosive
  - Printim i faturave
  - Statistikat e shitjeve
  - Kthim mbrapa në faqen e mëparshme

**Shënim**: Kjo është faqja e vetme që Order Manager mund ta shohë dhe përdorë.

---

### 9. **Admin Users** (`/admin/users`)
- **URL**: `/admin/users`
- **Kontrolli**: ✅ `requiresAuth: true` + ✅ `requiresAdmin: true`
- **Akses**: Vetëm **Admin** (`role = 'admin'`)
- **Order Manager**: ❌ Nuk ka akses (ridrejtohet në `/admin/sales`)
- **Funksionaliteti**:
  - Shikim i të gjithë adminave
  - Krijim i adminave të rinj
  - Editim i adminave (përfshirë rolet)
  - Fshirje e adminave
  - Menaxhim i roleve (admin/order_manager)

**Shënim**: Kjo është faqja më e mbrojtur - kërkon `requiresAdmin: true` në router.

---

## 🔒 Tabela e Aksesit

| Faqja | URL | Admin | Order Manager | Kontrolli Router |
|-------|-----|-------|---------------|-----------------|
| Login | `/admin/login` | ✅ | ✅ | ❌ Nuk kërkon auth |
| Clients | `/admin/clients` | ✅ | ❌ | `requiresAuth: true` |
| Client Prices | `/admin/clients/:id/prices` | ✅ | ❌ | `requiresAuth: true` |
| Products | `/admin/products` | ✅ | ❌ | `requiresAuth: true` |
| Stock | `/admin/stock` | ✅ | ❌ | `requiresAuth: true` |
| Supplier Invoices | `/admin/supplier-invoices` | ✅ | ❌ | `requiresAuth: true` |
| Trash | `/admin/trash` | ✅ | ❌ | `requiresAuth: true` |
| **Sales** | `/admin/sales` | ✅ | ✅ | `requiresAuth: true` |
| **Users** | `/admin/users` | ✅ | ❌ | `requiresAuth: true` + `requiresAdmin: true` |

---

## 🛡️ Kontrolli në Frontend

### Metodat e `adminStore`:

```javascript
// Kontrollo nëse mund të menaxhojë (vetëm admin)
canManage() → role === 'admin'

// Kontrollo nëse mund të shohë shitjet (admin dhe order_manager)
canViewSales() → role === 'admin' || role === 'order_manager'

// Kontrollo nëse mund të menaxhojë klientët (vetëm admin)
canManageClients() → role === 'admin'

// Kontrollo nëse mund të menaxhojë produktet (vetëm admin)
canManageProducts() → role === 'admin'

// Kontrollo nëse mund të menaxhojë stokun (vetëm admin)
canManageStock() → role === 'admin'

// Kontrollo nëse mund të menaxhojë furnitorët (vetëm admin)
canManageSuppliers() → role === 'admin'

// Kontrollo nëse mund të shohë trash (vetëm admin)
canViewTrash() → role === 'admin'
```

---

## 🔄 Ridrejtimet Automatike

### Pas Login:
- **Admin** (`role = 'admin'`) → `/admin/clients`
- **Order Manager** (`role = 'order_manager'`) → `/admin/sales`

### Kur Order Manager përpiqet të hyjë në faqe të mbrojtura:
- `/admin/clients` → `/admin/sales`
- `/admin/products` → `/admin/sales`
- `/admin/stock` → `/admin/sales`
- `/admin/users` → `/admin/sales`
- `/admin/trash` → `/admin/sales`
- `/admin/supplier-invoices` → `/admin/sales`

### Kur nuk je i autentifikuar:
- Çdo faqe admin → `/admin/login`

---

## 🛠️ Kontrolli në Backend

### Middleware: `CheckAdminRole`

```php
// Në routes/api.php
Route::middleware([\App\Http\Middleware\CheckAdminRole::class . ':admin'])->group(function () {
    // Routes që kërkojnë rol admin
});
```

### Kontrolli në Controllers:

```php
// Kontrollo nëse është admin
if (!$request->user()->isFullAdmin()) {
    return response()->json(['message' => 'Unauthorized'], 403);
}

// Kontrollo nëse është order manager
if (!$request->user()->isOrderManager()) {
    return response()->json(['message' => 'Unauthorized'], 403);
}
```

---

## 📝 Shënime të Rëndësishme

1. **Order Manager** mund të shohë vetëm:
   - `/admin/sales` - Shitjet dhe porositë
   - Mund të printojë fatura
   - Nuk mund të bëjë ndryshime në sistem

2. **Admin** ka akses të plotë:
   - Të gjitha faqet e admin
   - Menaxhim i plotë i sistemit
   - Menaxhim i adminave të tjerë

3. **Router Guard** kontrollon:
   - Autentifikimin (`requiresAuth`)
   - Rolin (`requiresAdmin`)
   - Ridrejton automatikisht nëse nuk ka akses

4. **Frontend Components** kontrollojnë:
   - Butonat dhe linkat shfaqen vetëm për përdoruesit me akses
   - Përdor `v-if="canManage()"` për të fshehur elemente

---

## 🔍 Si të Testosh Kontrollin

### Test për Admin:
1. Kyçu me admin (`role = 'admin'`)
2. Provoni të hyni në të gjitha faqet
3. Duhet të kesh akses në të gjitha

### Test për Order Manager:
1. Kyçu me order manager (`role = 'order_manager'`)
2. Provoni të hyni në `/admin/sales` → ✅ Duhet të funksionojë
3. Provoni të hyni në `/admin/clients` → ❌ Duhet të ridrejtohet në `/admin/sales`
4. Provoni të hyni në `/admin/users` → ❌ Duhet të ridrejtohet në `/admin/sales`

---

**Data e përditësimit**: 2025-02-16  
**Version**: 1.0
