# Biometrix IT Helpdesk (Ticketing)

Internal **IT helpdesk** system for **Biometrix**: staff submit issues online, the **Front Desk** logs phone and walk-in requests, and **IT** and **admin** users manage tickets through a realistic **Open → In Progress → Resolved → Closed** workflow. Optional real-time behavior via **Laravel Reverb**; email can be tested with **Mailtrap** (see `SETUP.md`).

## Who it is for

| Area        | Purpose |
|------------|---------|
| Employees  | Self-service tickets and tracking |
| Front desk | Log call and walk-in requests for others |
| IT staff   | Assign, update, and resolve tickets |
| Admins     | Full access and configuration |

## Documentation

- **`OVERVIEW.md`** — roles, status flow, comments, and history  
- **`SETUP.md`** — local install, Reverb, and environment notes  

## Stack

- **PHP** 8.2+, **Laravel** 12, **MySQL** (typical)  
- **Laravel Reverb** for WebSocket/realtime where enabled  
- **Vite** + **Tailwind** for frontend assets  

## Quick start

1. `composer install` and `npm install`  
2. Copy `.env.example` to `.env` and configure database, `APP_NAME`, mail, and Reverb if used.  
3. `php artisan key:generate`  
4. `php artisan migrate` (and seeders if you use them)  
5. `npm run build` or `npm run dev`  
6. `php artisan serve` (and run Reverb/queue workers per `SETUP.md` if needed)

## License

Project code is for the Biometrix helpdesk effort. Framework and dependencies follow their upstream licenses.
