# Courier Management API

A Laravel JSON API for managing couriers, including levels 1-5, searchable and
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
php artisan db:seed
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
| `POST` | `/api/couriers` | Create a courier (authentication required) |
| `GET` | `/api/couriers/{courier}` | Show one courier |
| `PUT`, `PATCH` | `/api/couriers/{courier}` | Update a courier (authentication required) |
| `DELETE` | `/api/couriers/{courier}` | Delete a courier (authentication required) |
| `POST` | `/api/login` | Log in and issue an API token |
| `POST` | `/api/logout` | Revoke the current API token |

### Authentication

Courier list and detail endpoints are public. Creating, updating, or deleting a
courier requires a Sanctum personal access token. There is no public registration
endpoint; provision accounts through a trusted administrative process.

Log in with an existing user's email and password:

```text
POST /api/login
```

```json
{
  "email": "admin@example.com",
  "password": "your-password"
}
```

The response contains a token and basic user details. Send that token on
protected requests as `Authorization: Bearer <token>`. Log out with
`POST /api/logout` using the same bearer token; logout revokes that token.
Login is limited to five attempts per minute per client.

Create an initial trusted user from the project directory with Tinker:

```powershell
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Administrator',
    'email' => 'admin@example.com',
    'password' => 'replace-with-a-strong-password',
]);
```

The user password is hashed by the model. Do not commit real credentials.

For local development, `php artisan db:seed` creates 20 sample couriers and a
demo login account: `test@example.com` / `password`. This default account is for
local development only; do not use it in production.

### List options

- `search`: match every whitespace-separated term against the courier name.
  For example, `?search=budi+agung` can match `Budiono Hadi Agung`.
- `level`: comma-separated levels from 1 through 5, for example `?level=2,3`.
- `sort`: choose `name` (default), `level`, or `registered_at`. The default
  direction is ascending for name/level and descending (newest first) for
  registration time. Use `direction=asc` or `direction=desc` to override it.
- `per_page`: page size from 1 to 100 (default 15); Laravel pagination links
  and metadata are included in the response.

Example:

```text
GET /api/couriers?search=budi+agung&level=2,3&sort=level&direction=desc&per_page=10
```

### Courier fields

- `name` (required, maximum 150 characters)
- `phone` (required, unique, maximum 30 characters)
- `email` (optional, valid and unique)
- `address` (optional)
- `vehicle_type` (optional, maximum 80 characters)
- `plate_number` (optional, maximum 20 characters, unique)
- `level` (required integer from 1 to 5)
- `is_active` (optional boolean; defaults to `true`)

Create and update requests are validated. Validation errors return HTTP 422;
successful creation returns HTTP 201, and deletion returns HTTP 204.

To update a courier's plate number and active status, send
`PATCH /api/couriers/{courier}` with a bearer token and JSON such as:

```json
{
  "plate_number": "B 1234 XYZ",
  "is_active": false
}
```
