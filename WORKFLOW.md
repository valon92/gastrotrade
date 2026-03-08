# Workflow – AronTrade

## Rregulli kryesor: Localhost fillimisht, pastaj server

Çdo rregullim, detyrë ose zgjidhje:

1. **Rregullo / implemento** në kodin lokal.
2. **Testo** në localhost (p.sh. `npm run dev`, `php artisan serve`) dhe sigurohu që çdo detaj të funksionojë.
3. **Vetëm kur gjithçka është në rregull në localhost:** bëj push në server (`git push origin main` ose deploy manual në cPanel).

Nuk bëhet push në server përpara se të jetë konfirmuar që ndryshimet funksionojnë lokalisht.
