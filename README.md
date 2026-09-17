# TinyLink — URL Shortener REST API

TinyLink is a backend-only REST API built with **Laravel 11**, **PHP 8.2**, **MySQL**, and **Laravel Sanctum**. It allows authenticated users to create shortened URLs, manage only their own links, and track how many times each short URL has been visited.

This project was built as a technical assessment covering API design, Sanctum authentication, Eloquent ORM, Form Request validation, Policy-based authorization, and clean Laravel conventions.

---

## Technologies

| Technology | Version |
|---|---|
| PHP | ≥ 8.2 |
| Laravel | 11.x |
| MySQL | 8.0+ |
| Laravel Sanctum | 4.x (bundled) |
| Eloquent ORM | Bundled with Laravel |
| Composer | 2.x |

---

## Requirements

- **PHP** ≥ 8.2 (with `pdo_mysql`, `mbstring`, `openssl` extensions)
- **Composer** ≥ 2.x
- **MySQL** 8.0+
- **Laravel** 11.x

---

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd tinylink
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Copy the environment file

```bash
cp .env.example .env
```

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Configure the database

Open `.env` and set your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tinylink
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database in MySQL:

```sql
CREATE DATABASE tinylink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run migrations and seed demo data

```bash
php artisan migrate --seed
```

### 7. Start the development server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

---

## Authentication

TinyLink uses **Laravel Sanctum** for token-based API authentication.

### Flow

1. **Register** → `POST /api/register` → receive a Bearer token
2. **Use the token** in all protected requests:
   ```
   Authorization: Bearer <your-token>
   ```
3. **Logout** → `POST /api/logout` → token is revoked

> Tokens are stored in the `personal_access_tokens` table and are tied to the user's account.

---

## API Endpoints

### Authentication

| Method | Endpoint | Auth Required | Description |
|--------|----------|:---:|-------------|
| POST | `/api/register` | No | Register a new user and receive a token |
| POST | `/api/login` | No | Login and receive a token |
| POST | `/api/logout` | Yes | Revoke the current access token |
| GET | `/api/me` | Yes | Get the authenticated user's information |

### URL Management

| Method | Endpoint | Auth Required | Description |
|--------|----------|:---:|-------------|
| POST | `/api/urls` | Yes | Create a new short URL |
| GET | `/api/urls` | Yes | List all URLs belonging to the authenticated user |
| GET | `/api/urls/{id}` | Yes | View details of a specific URL (own URLs only) |
| DELETE | `/api/urls/{id}` | Yes | Delete a specific URL (own URLs only) |

### Bonus Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|:---:|-------------|
| GET | `/api/urls/{id}/stats` | Yes | View click statistics for a URL (own URLs only) |

### Public Redirect

| Method | Endpoint | Auth Required | Description |
|--------|----------|:---:|-------------|
| GET | `/{short_code}` | No | Redirect to the original URL and increment click count |

---

## Example Requests & Responses

### Register

**Request:**
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "User registered successfully.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "1|abc123..."
    }
}
```

---

### Login

**Request:**
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "2|xyz789..."
    }
}
```

---

### Create Short URL

**Request:**
```http
POST /api/urls
Authorization: Bearer <token>
Content-Type: application/json

{
    "url": "https://example.com/this-is-a-very-long-url"
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "URL shortened successfully.",
    "data": {
        "id": 1,
        "original_url": "https://example.com/this-is-a-very-long-url",
        "short_code": "aB92xZ",
        "click_count": 0
    }
}
```

---

### Create Short URL with Custom Code (Bonus 1)

```http
POST /api/urls
Authorization: Bearer <token>
Content-Type: application/json

{
    "url": "https://example.com",
    "custom_code": "my-link"
}
```

Then visit: `GET /my-link` → redirects to `https://example.com`

---

### List URLs (with pagination)

```http
GET /api/urls?page=1&per_page=10
Authorization: Bearer <token>
```

---

### View URL Stats (Bonus 2)

```http
GET /api/urls/1/stats
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "message": "URL statistics retrieved successfully.",
    "data": {
        "url": "https://example.com",
        "short_code": "aB92xZ",
        "click_count": 25
    }
}
```

---

### Short URL Redirect

```http
GET /aB92xZ
```

→ Redirects (302) to `https://example.com/this-is-a-very-long-url`  
→ Increments `click_count` atomically in the database

---

## Authorization

TinyLink uses **Laravel Policies** (`UrlPolicy`) to enforce ownership-based authorization:

- ✅ User A can view, delete, and view stats of **their own** URLs
- ❌ User A **cannot** view, delete, or view stats of **User B's** URLs
- Authorization is enforced **server-side** on every protected endpoint
- 403 Forbidden is returned for unauthorized access attempts

---

## Seeder

The `DatabaseSeeder` creates the following demo data:

| User | Email | Password |
|------|-------|----------|
| Alice Demo | alice@example.com | password |
| Bob Demo | bob@example.com | password |

Each user has 5 sample shortened URLs. An additional 3 random users with URLs are also created.

To re-seed:
```bash
php artisan migrate:fresh --seed
```

---

## Consistent Response Structure

All API responses follow this structure:

**Success:**
```json
{
    "success": true,
    "message": "Human-readable message.",
    "data": {}
}
```

**Error:**
```json
{
    "success": false,
    "message": "Human-readable error message."
}
```

**HTTP Status Codes used:**

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 401 | Unauthenticated |
| 403 | Forbidden (wrong owner) |
| 404 | Not Found |
| 422 | Validation Error |

---

## Postman Collection

A Postman collection is included in the repository root:

```
TinyLink.postman_collection.json
```

**Environment variables used:**
- `base_url` — e.g. `http://localhost:8000`
- `token` — the Bearer token received after login/register

Import the collection into Postman and set these two variables to start testing immediately.

---

## Assumptions

1. Short codes are 6 random alphanumeric characters by default (generated with `Str::random(6)`)
2. Custom codes must be 3–20 characters, alphanumeric with dashes/underscores
3. Click count is incremented using a DB-level atomic `increment()` to avoid race conditions
4. Pagination defaults to 10 items per page, maximum 100
5. Password hashing is handled automatically by Laravel's `hashed` cast on the User model
6. The public redirect route (`/{short_code}`) is on the `web` router and is NOT behind Sanctum
7. Stack traces and sensitive data are never exposed in API responses
8. Sanctum is used in SPA/token mode (not cookie-based stateful mode), returning plain text tokens
