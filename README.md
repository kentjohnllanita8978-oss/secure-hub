# SecureTS

SecureTS is a Laravel and Livewire prototype for passwordless, dual-device authentication. Users start a sign-in request on desktop and approve it from a trusted mobile device.

## What’s included

- Responsive SecureTS landing page
- Interactive dual-device sign-in simulation
- Device pairing and phishing-resistance demonstrations
- Early-access waitlist form

## Local setup

1. Install PHP and Node.js dependencies:

   ```bash
   composer install
   npm install
   ```

2. Create your environment file and application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Configure your database in `.env`, then run migrations:

   ```bash
   php artisan migrate
   ```

4. Start the application and asset server in separate terminals:

   ```bash
   php artisan serve
   npm run dev
   ```

Open `http://localhost:8000` in your browser.

## Useful commands

```bash
npm run build
php artisan test
php artisan pint
```
