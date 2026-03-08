# Baza e të dhënave – Identifikimi i klientëve (AronTrade)

Kjo dokumentacion përshkruan tabelat e nevojshme për identifikimin e klientëve (regjistrim, kyçje, token).

## Tabelat e nevojshme

### 1. `clients`

Përdoret për regjistrim dhe kyçje me **email + fjalëkalim**.

| Kolona            | Lloji        | Shënim |
|-------------------|-------------|--------|
| id                | bigint PK   | |
| name              | string      | |
| store_name        | string NULL | |
| city              | string NULL | |
| street_number     | string NULL | |
| unit              | string NULL | |
| phone             | string NULL | |
| viber             | string NULL | |
| email             | string NULL UNIQUE | **Përdoret për kyçje** |
| fiscal_number     | string NULL | |
| notes             | text NULL   | |
| password          | string NULL | **Hashed (bcrypt)** |
| is_active         | boolean     | Default true |
| allow_piece_sales | boolean     | Default false |
| created_at        | timestamp   | |
| updated_at        | timestamp   | |
| deleted_at        | timestamp NULL | Soft deletes |

**Migrimet:** `create_clients_table`, `add_fiscal_number`, `update_clients_table_business_name_required`, `add_street_number_and_unit`, `add_deleted_at`, `add_allow_piece_sales`, `add_password_to_clients_table`, `clients_email_login_and_nullable_store_name`.

### 2. `personal_access_tokens` (Sanctum)

Përdoret për tokenat e klientëve (dhe adminëve) – API bearer auth.

| Kolona         | Lloji     | Shënim |
|----------------|-----------|--------|
| id             | bigint PK | |
| tokenable_type | string    | P.sh. `App\Models\Client` |
| tokenable_id   | bigint    | |
| name           | string    | P.sh. `client-cart` |
| token          | string 64 | Unique, hash |
| abilities      | text NULL | |
| last_used_at   | timestamp NULL | |
| expires_at     | timestamp NULL | |
| created_at     | timestamp | |
| updated_at     | timestamp | |

**Migrimi:** `create_personal_access_tokens_table`.

### 3. Tabela për çmimet e klientit (jo për auth, por për shportë)

- **client_prices** – lidhje klient–produkt–çmim (për çmimet e personalizuara në shportë).
- **client_locations** (opsionale) – pika/ njësi për klientët me më shumë adresa.

## Si të ekzekutosh migrimet

```bash
php artisan migrate
```

Për të krijuar klientë test (email + fjalëkalim):

```bash
php artisan db:seed --class=ClientSeeder
```

Për të vendosur/ndryshuar fjalëkalimin e një klienti nga terminali:

```bash
php artisan client:set-password email@shembull.com FjalekalimiIRi
```
