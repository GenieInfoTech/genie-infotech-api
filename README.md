# Genie InfoTech API & Admin Panel

Laravel 11 backend with Filament admin panel for Genie InfoTech website.

## Features

- **Lead Management**: Capture, track, and manage leads
- **Blog Management**: Create and publish blog posts with SEO
- **Auto-Responder**: Automatic welcome emails to new leads
- **Analytics Dashboard**: Lead metrics and conversion tracking
- **Secure API**: Rate-limited, authenticated REST API
- **Filament Admin**: Beautiful, modern admin interface

## Tech Stack

- Laravel 11
- Filament 3 (Admin Panel)
- Laravel Sanctum (API Authentication)
- MySQL/MariaDB
- Spatie packages (Activity Log, Permissions, Honeypot)

## Security Features

- CSRF Protection
- Rate Limiting (Login: 5/min, API: 60/min)
- Honeypot spam protection
- Security headers (CSP, XSS, HSTS)
- Encrypted sessions
- Activity logging
- Brute-force protection

## Installation

### 1. Clone and Install Dependencies

```bash
cd genie-api
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your settings:
- Database credentials
- SMTP settings
- Frontend URL for CORS

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Create Admin User

```bash
php artisan db:seed
```

Default admin:
- Email: `admin@genieinfo.tech`
- Password: `ChangeMe123!`

**⚠️ CHANGE THE PASSWORD IMMEDIATELY!**

### 5. Start Queue Worker (for emails)

```bash
php artisan queue:work
```

For production, use Supervisor to keep the worker running.

## cPanel Deployment

### Directory Structure

```
public_html/
└── api.genieinfo.tech/    ← Laravel public folder contents
    └── .htaccess

/home/username/
└── genie-api/             ← Laravel app (outside public_html)
    ├── app/
    ├── config/
    ├── database/
    └── ...
```

### Steps

1. Upload Laravel files to `/home/username/genie-api/`
2. Create subdomain `api.genieinfo.tech`
3. Point subdomain document root to `/home/username/genie-api/public`
4. Create MySQL database in cPanel
5. Update `.env` with database credentials
6. Run migrations via SSH:
   ```bash
   cd ~/genie-api
   php artisan migrate --seed
   ```

### Cron Job (for scheduled tasks)

Add to cPanel Cron Jobs:
```
* * * * * cd /home/username/genie-api && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Worker

Option 1: Database queue with cron (simpler):
```
* * * * * cd /home/username/genie-api && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

Option 2: Use Supervisor if available on your hosting.

## API Endpoints

### Public

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/contact` | Submit contact form |
| POST | `/api/leads` | Capture lead |
| GET | `/api/blog` | List blog posts |
| GET | `/api/blog/{slug}` | Get blog post |
| GET | `/api/blog/categories` | List categories |

### Protected (Requires Auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/leads` | List leads |
| GET | `/api/leads/{id}` | Get lead |
| PUT | `/api/leads/{id}` | Update lead |
| DELETE | `/api/leads/{id}` | Delete lead |
| GET | `/api/analytics/leads` | Lead analytics |

## Admin Panel

Access at: `https://api.genieinfo.tech/admin`

Features:
- Dashboard with lead metrics
- Lead management with filters
- Blog post editor with SEO
- Settings management
- Activity logs

## Environment Variables

```env
# App
APP_NAME="Genie InfoTech API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.genieinfo.tech

# Frontend (CORS)
FRONTEND_URL=https://genieinfo.tech

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=genie_api
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=contact@genieinfo.tech
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl

# Notifications
NOTIFICATION_EMAILS=contact@genieinfo.tech,genie.projectmanager@gmail.com

# Security
SANCTUM_STATEFUL_DOMAINS=genieinfo.tech,www.genieinfo.tech
```

## License

Proprietary - Genie InfoTech
