# Local setup

## 1. Database (MySQL)

The app uses **MySQL**. Create a database and set credentials in `.env`:

1. In MySQL (e.g. phpMyAdmin or CLI), create a database:
   ```sql
   CREATE DATABASE laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. In **`.env`** set:
   - `DB_DATABASE=laravel` (or your database name)
   - `DB_USERNAME=` your MySQL user
   - `DB_PASSWORD=` your MySQL password

## 2. PHP zip extension (for Composer)

If `composer install` is very slow, enable the **zip** extension:

1. Open **`C:\xampp\php\php.ini`**.
2. Find: `;extension=zip` and change to: `extension=zip`.

## 3. Install and run

```powershell
cd d:\Biometrix\Ticketing\my-project
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Open http://127.0.0.1:8000.

## 4. Real-time updates (optional)

When an admin or user creates or updates a ticket, other users (e.g. employees) see the change **immediately** without reloading, as long as real-time is enabled.

1. In **`.env`** set:
   - `BROADCAST_CONNECTION=reverb`
   - `REVERB_APP_ID=my-app-id`
   - `REVERB_APP_KEY=my-app-key`
   - `REVERB_APP_SECRET=my-app-secret`
   - `REVERB_HOST=127.0.0.1`
   - `REVERB_PORT=8080`
   - `REVERB_SCHEME=http`

2. In a **second terminal**, start the Reverb WebSocket server:
   ```powershell
   cd d:\Biometrix\Ticketing\my-project
   php artisan reverb:start
   ```

3. Keep that terminal open while using the app. The tickets list and dashboard will then update automatically when tickets are created or updated.

If Reverb is not running, the app still refreshes the tickets list every 15 seconds and you can use the **Refresh** button.
