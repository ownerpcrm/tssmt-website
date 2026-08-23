# TSSMT NGO Portal

Standalone PHP 8.1+/MySQL 8 application for tssmt.org. It does not connect to Own ERP.

## What is included

- Public NGO website: about, contact, notices, activities, registration and donation information.
- Member panel: profile, membership payments, donations, notices and printable acknowledgements.
- Admin panel: membership approval, payment verification, donation verification, site settings, notices and activities.

## Run locally

1. Install PHP 8.1+ and MySQL 8.
2. Create database `tssmt_db` with utf8mb4 and import `database/schema.sql`.
3. Copy `.env.example` to `.env` and set the database credentials.
4. Run `php scripts/create_admin.php admin@example.com StrongPassword "TSSMT Admin"`.
5. Double-click `start-server.bat`, then open `http://localhost:8000`.

The public site is at `/`; the administrator panel is `/admin/login.php`; member login is `/login.php`.

## Production install
1. Place this folder at `/var/www/tssmt.org` and set the web root to `public/`.
2. Create database `tssmt_db` with utf8mb4 and import `database/schema.sql`.
3. Copy `.env.example` to `.env`, set database and SMTP credentials, then run `php scripts/create_admin.php admin@example.com 'StrongPassword' 'TSSMT Admin'`.
4. Make `public/uploads/` writable by the web-server user. Configure HTTPS and point PHP `open_basedir` to this project.

Manual payments remain pending until an administrator verifies them. Receipt printing uses the browser print dialog; save as PDF is supported there.
