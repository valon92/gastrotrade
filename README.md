# GastroTrade

Platform moderne e-commerce për menaxhimin e produkteve, klientëve dhe porosive, e ndërtuar me Laravel 10 dhe Vue.js 3.

## 🚀 Teknologjitë

- **Backend:** Laravel 10
- **Frontend:** Vue.js 3 + Vue Router
- **Styling:** Tailwind CSS
- **Database:** MySQL/PostgreSQL (SQLite për development)
- **Build Tool:** Vite

## ✨ Karakteristikat

### Menaxhimi i Produkteve
- Kategorizim i produkteve
- Imazhe të shumta për produkt
- Produkte të veçanta (featured products)
- Menaxhim i paketave dhe sasive

### Menaxhimi i Klientëve
- Identifikim i klientëve sipas emrit të biznesit
- Çmime të personalizuara për klientë
- Numër fiskal për klientë
- Opsion për shitje me copa ose paketa

### Menaxhimi i Porosive
- Ruajtje e porosive në bazën e të dhënave
- Historia e porosive për klientë
- Printim i faturave
- Gjurmim i statusit të pagesës
- Statusi i porosive (ruajtur, konfirmuar, dërguar, kompletuar, anuluar)

### Sistemi i Zbritjeve
- Zbritje për produkte të veçanta (% ose €)
- Zbritje e përgjithshme për faturë (% ose €)
- Llogaritje automatike e totalit

### Panel Admin
- Autentifikim për administratorë
- Menaxhim i klientëve dhe çmimeve
- Menaxhim i porosive
- Editim dhe fshirje e porosive

## 📋 Kërkesat

- PHP >= 8.1
- Composer
- Node.js >= 16
- MySQL/PostgreSQL ose SQLite

## 🔧 Instalimi

1. Klononi repository-n:
```bash
git clone https://github.com/username/gastrotrade.git
cd gastrotrade
```

2. Instaloni varësitë PHP:
```bash
composer install
```

3. Instaloni varësitë JavaScript:
```bash
npm install
```

4. Kopjoni skedarin `.env.example` në `.env`:
```bash
cp .env.example .env
```

5. Gjeneroni çelësin e aplikacionit:
```bash
php artisan key:generate
```

6. Konfiguroni bazën e të dhënave në `.env`

7. Ekzekutoni migracionet dhe seeders:
```bash
php artisan migrate --seed
```

8. Ndërtoni asetet frontend:
```bash
npm run build
```

9. Nisni serverin e zhvillimit:
```bash
php artisan serve
```

Dhe në një terminal tjetër:
```bash
npm run dev
```

## 👤 Kredencialet e Admin

- **Email:** svalon95@gmail.com
- **Password:** Valon123

## 📁 Struktura e Projektit

```
gastrotrade/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/          # API Controllers
│   │   └── Requests/         # Form Requests
│   └── Models/               # Eloquent Models
├── database/
│   ├── migrations/           # Database Migrations
│   └── seeders/              # Database Seeders
├── resources/
│   ├── js/
│   │   ├── components/      # Vue Components
│   │   ├── views/            # Vue Views
│   │   ├── router/           # Vue Router
│   │   └── store/            # Vue Store (Cart)
│   └── css/                  # Styles
└── routes/
    ├── api.php               # API Routes
    └── web.php               # Web Routes
```

## 🔐 API Endpoints

### Produktet
- `GET /api/products` - Lista e të gjitha produkteve
- `GET /api/products/{slug}` - Detajet e një produkti

### Kategoritë
- `GET /api/categories` - Lista e kategorive

### Klientët
- `POST /api/clients` - Krijo klient të ri
- `GET /api/clients` - Lista e klientëve (admin)
- `PUT /api/clients/{id}` - Përditëso klient
- `GET /api/clients/find-by-business-name` - Gjej klient sipas emrit të biznesit

### Çmimet e Klientëve
- `GET /api/clients/{id}/prices` - Çmimet e klientit
- `POST /api/clients/{id}/prices` - Shto çmim të ri
- `PUT /api/client-prices/{id}` - Përditëso çmim
- `DELETE /api/client-prices/{id}` - Fshi çmim

### Porositë
- `POST /api/orders` - Krijo porosi të re
- `GET /api/orders/history` - Historia e porosive
- `GET /api/orders/{id}` - Detajet e porosisë
- `PUT /api/orders/{id}` - Përditëso porosi (admin)
- `DELETE /api/orders/{id}` - Fshi porosi (admin)

### Autentifikimi
- `POST /api/admin/login` - Login për admin

## 📝 Licenca

Projekti është i licencuar sipas [MIT License](https://opensource.org/licenses/MIT).

## 👨‍💻 Autor

Valon Sylejmani

## 📧 Kontakt

- Email: svalon95@gmail.com
- Telefon: 048 75 66 46 / 044 82 43 14
- Viber: +383 48 75 66 46 / +383 44 82 43 14
- Adresa: Ferizaj, Kosovë, Rruga Lidhja E Prizerent
