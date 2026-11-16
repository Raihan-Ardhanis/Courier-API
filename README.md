PS C:\Project\Courir> git init
Initialized empty Git repository in C:/Project/Courir/.git/
PS C:\Project\Courir> git status --short --ignored
?? .editorconfig
?? .env.example
?? .gitattributes
?? .gitignore
?? .npmrc
?? AGENTS.md
?? CLAUDE.md
?? README.md
?? app/
?? artisan
?? bootstrap/
?? composer.json
?? composer.lock
?? config/
?? database/
?? package.json
?? phpunit.xml
?? public/
?? resources/
?? routes/
?? storage/
?? tests/
?? vite.config.js
!! .env
!! .phpunit.result.cache
!! bootstrap/cache/packages.php
!! bootstrap/cache/services.php
!! database/database.sqlite
!! storage/framework/views/aca99790dc77e6415939620349ca43ee.php
!! vendor/PS C:\Project\Courir> git init
Initialized empty Git repository in C:/Project/Courir/.git/
PS C:\Project\Courir> git status --short --ignored
?? .editorconfig
?? .env.example
?? .gitattributes
?? .gitignore
?? .npmrc
?? AGENTS.md
?? CLAUDE.md
?? README.md
?? app/
?? artisan
?? bootstrap/
?? composer.json
?? composer.lock
?? config/
?? database/
?? package.json
?? phpunit.xml
?? public/
?? resources/
?? routes/
?? storage/
?? tests/
?? vite.config.js
!! .env
!! .phpunit.result.cache
!! bootstrap/cache/packages.php
!! bootstrap/cache/services.php
!! database/database.sqlite
!! storage/framework/views/aca99790dc77e6415939620349ca43ee.php
!! vendor/# Courier Management API

A Laravel JSON API for managing couriers, including levels 1–5, searchable and
filterable listings, pagination, and CRUD operations.

## Requirements

- PHP 8.3 or later
- Composer

## Setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
```

The project is configured to use SQLite by default. If
`database/database.sqlite` does not exist, create an empty file there before
running migrations.

Run the API locally:

```powershell
php artisan serve
```

Run the test suite:

```powershell
php artisan test
```

## API

All endpoints are under `/api`. Send JSON request bodies with
`Content-Type: application/json` and request JSON responses with
`Accept: application/json`.

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/couriers` | List couriers |
| `POST` | `/api/couriers` | Create a courier |
| `GET` | `/api/couriers/{courier}` | Show one courier |
| `PUT`, `PATCH` | `/api/couriers/{courier}` | Update a courier |
| `DELETE` | `/api/couriers/{courier}` | Delete a courier |

### List options

- `search`: match every whitespace-separated term against the courier name.
  For example, `?search=budi+agung` can match `Budiono Hadi Agung`.
- `level`: comma-separated levels from 1 through 5, for example `?level=2,3`.
- `sort=registered_at`: sort by registration time (newest first by default).
  Use `direction=asc` or `direction=desc` to choose the date order.
- `per_page`: page size from 1 to 100 (default 15); Laravel pagination links
  and metadata are included in the response.

Example:

```text
GET /api/couriers?search=budi+agung&level=2,3&sort=registered_at&per_page=10
```

### Courier fields

- `name` (required, maximum 150 characters)
- `phone` (required, unique, maximum 30 characters)
- `email` (optional, valid and unique)
- `address` (optional)
- `vehicle_type` (optional, maximum 80 characters)
- `level` (required integer from 1 to 5)
- `is_active` (optional boolean; defaults to `true`)

Create and update requests are validated. Validation errors return HTTP 422;
successful creation returns HTTP 201, and deletion returns HTTP 204.
