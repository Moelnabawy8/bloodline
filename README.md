# Bloodline

**Blood donation coordination with donor, hospital and administrator workflows.**

[Overview](#overview) · [Local setup](#local-setup) · [Code guide](#code-guide) · [Verification](#verification)

## Overview

A Laravel application for managing blood requests, donor participation and donation campaigns. It brings public request listings together with separate workspaces for donors, hospitals and administrators.

## Features

- Public blood requests, urgent requests, campaigns and hospital map.
- Donor registration, profiles, donation history and reward points.
- Hospital dashboards for requests, donation campaigns and blood transport vehicles.
- Role-based access for donors, hospitals and administrators.
- Donor matching by blood type and location, with notification preferences.

## Technology

| Layer | Stack |
| --- | --- |
| Backend | PHP `^8.2` · Laravel `^12.0` |
| Interface and tooling | Blade · Tailwind CSS 4 · Vite 7 |
| Data | Eloquent ORM · SQLite by default; other configured drivers are available |

Version constraints above come from [composer.json](composer.json) and [package.json](package.json). Use a PHP runtime compatible with `composer.lock`, Composer 2, and a Node.js/npm version compatible with the declared asset tooling. Enable the PHP extensions required by Composer and the selected database driver.

## Local setup

```bash
git clone https://github.com/Moelnabawy8/bloodline.git
cd bloodline
composer install
cp .env.example .env
php artisan key:generate
npm install
```

Create the SQLite file if it does not already exist, and point `DB_DATABASE` in your local `.env` to its absolute path:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

Keep `DB_CONNECTION=sqlite` for this setup. Set `APP_URL=http://127.0.0.1:8000`. For local mail inspection, set `MAIL_MAILER=log` instead of configuring a live mail provider.

```bash
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Open [the local application](http://127.0.0.1:8000). For asset hot reload, run `npm run dev` in a second terminal while the PHP server remains running.

## Application areas

| Area | Entry point | Purpose |
| --- | --- | --- |
| Public | `/requests`, `/urgent`, `/campaigns`, `/map` | Discover requests and campaigns |
| Donor | `/donor/dashboard` | Review requests and manage donations |
| Hospital | `/hospital/dashboard` | Manage blood requests and vehicles |
| Administrator | `/admin/dashboard` | Manage hospital accounts |

Use `/register` to create a donor account. Hospital and administrator accounts follow separate provisioning flows; inspect the seeders before using them in a local demo.

## Code guide

| Path | Responsibility |
| --- | --- |
| [`routes/web.php`](routes/web.php) | Public pages and role-specific routes |
| [`app/Services/DonorMatcher.php`](app/Services/DonorMatcher.php) | Donor matching and distance calculations |
| [`app/Models/`](app/Models/) | Requests, donors, hospitals, campaigns and donations |
| [`tests/Feature/`](tests/Feature/) | Smoke tests and geographic matching tests |

## Verification

Feature tests cover public pages, authentication, role access, donation flows and geographic matching. See [SmokeTest](tests/Feature/SmokeTest.php) and [GeoMatchingTest](tests/Feature/GeoMatchingTest.php).

Use an isolated testing database and review [phpunit.xml](phpunit.xml) before running the suite. Database-resetting tests must point to disposable data.

```bash
php artisan test
```

To check PHP formatting without modifying files:

```bash
./vendor/bin/pint --test
```

Build the front-end assets with `npm run build` after changing JavaScript, CSS or templates. Keep local configuration in `.env` and commit only configuration examples.
