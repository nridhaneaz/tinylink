# TinyLink API

A simple and secure RESTful URL Shortener API built with **Laravel**. The API allows authenticated users to create and manage shortened URLs, track click statistics, and redirect users through public short links.

## Live API

**Base URL:**
`https://tinylink-production-feda.up.railway.app/`

**Health Check:**
`https://tinylink-production-feda.up.railway.app/`

---

## Features

* User registration and login
* Laravel Sanctum API authentication
* Bearer token authentication
* Authenticated user profile
* Create shortened URLs
* Automatically generated unique short codes
* List user's own URLs with pagination
* View URL details
* Delete user's own URLs
* Public short URL redirection
* Click count tracking
* URL statistics
* User-based authorization
* Request validation
* Standardized JSON API responses

---

## Tech Stack

* **Framework:** Laravel
* **Language:** PHP
* **Database:** MySQL
* **Authentication:** Laravel Sanctum
* **ORM:** Eloquent
* **API:** RESTful API
* **API Testing:** Postman
* **Deployment:** Railway

---

## Requirements

* PHP 8.2+
* Composer
* MySQL
* Laravel
* Postman (for API testing)

---

## Local Setup

### 1. Clone the Repository

```bash
git clone YOUR_GITHUB_REPOSITORY_URL
cd tinylink-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Create the `.env` file:

```bash
cp .env.example .env
```

For Windows:

```bash
copy .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Configure Database

Update the database settings in `.env`:

```env
DB_HOST="${{MySQL.MYSQLHOST}}"
DB_PORT="${{MySQL.MYSQLPORT}}"
DB_DATABASE="${{MySQL.MYSQLDATABASE}}"
DB_USERNAME="${{MySQL.MYSQLUSER}}"
DB_PASSWORD="${{MySQL.MYSQLPASSWORD}}"
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Start the Application

```bash
php artisan serve
```

The local API will be available at:

```text
http://127.0.0.1:8000
```

---

## Authentication

The API uses **Laravel Sanctum** for authentication.

After a successful login, an access token is returned. Protected endpoints require the token using the following header:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
```

Passwords are securely hashed before being stored in the database.

---

## API Endpoints

### Authentication

| Method | Endpoint        | Auth | Description                        |
| ------ | --------------- | ---- | ---------------------------------- |
| POST   | `/api/register` | No   | Register a new user                |
| POST   | `/api/login`    | No   | Login and receive an access token  |
| POST   | `/api/logout`   | Yes  | Logout the authenticated user      |
| GET    | `/api/me`       | Yes  | Get authenticated user information |

### URL Management

| Method | Endpoint               | Auth | Description            |
| ------ | ---------------------- | ---- | ---------------------- |
| POST   | `/api/urls`            | Yes  | Create a shortened URL |
| GET    | `/api/urls`            | Yes  | List user's URLs       |
| GET    | `/api/urls/{id}`       | Yes  | View URL details       |
| DELETE | `/api/urls/{id}`       | Yes  | Delete own URL         |
| GET    | `/api/urls/{id}/stats` | Yes  | View URL statistics    |

### Public Endpoint

| Method | Endpoint        | Auth | Description                  |
| ------ | --------------- | ---- | ---------------------------- |
| GET    | `/{short_code}` | No   | Redirect to the original URL |

---

## URL Shortening Flow

The URL shortening process works as follows:

1. An authenticated user submits an original URL.
2. The API validates the URL.
3. A unique short code is generated.
4. The shortened URL is stored with the authenticated user's ID.
5. The API returns the shortened URL information.
6. When the public short URL is visited, the click count is incremented.
7. The visitor is redirected to the original URL.

---

## Authorization

Users can only manage URLs that belong to their own account.

For example:

* User A can view User A's URLs.
* User A can delete User A's URLs.
* User A cannot view User B's URLs.
* User A cannot delete User B's URLs.

Ownership checks are applied to protected URL resources.

---

## Validation

The API validates incoming requests before processing them.

Examples include:

* Name is required during registration.
* Email must be valid.
* Email uniqueness is enforced where applicable.
* Password confirmation is required during registration.
* Login credentials must be valid.
* URL is required when creating a shortened URL.
* URL must be valid.
* Short codes must be unique.

Validation errors are returned as JSON responses.

---

## Database Structure

The main `urls` table contains:

| Field          | Description           |
| -------------- | --------------------- |
| `id`           | Primary key           |
| `user_id`      | URL owner             |
| `original_url` | Original long URL     |
| `short_code`   | Unique short code     |
| `click_count`  | Number of visits      |
| `created_at`   | Creation timestamp    |
| `updated_at`   | Last update timestamp |

The `user_id` column references the `users` table through a foreign key relationship.

---

## Eloquent Relationships

### User → URLs

A user can have multiple shortened URLs.

```php
public function urls()
{
    return $this->hasMany(Url::class);
}
```

### URL → User

Each shortened URL belongs to one user.

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

---

## API Response Format

### Success

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {}
}
```

### Error

```json
{
    "success": false,
    "message": "Something went wrong"
}
```

Validation responses may include additional validation error details.

---

## Postman Collection

A complete Postman Collection is included for testing the API.

```text
postman/
└── TinyLink_API.postman_collection.json
```

The collection contains requests for:

* Registration
* Login
* Authenticated user information
* URL creation
* URL listing
* URL details
* URL statistics
* URL deletion
* Public short URL redirection
* Logout

The API requests in the collection are configured for the deployed Railway application.

**Live Base URL:**

```text
https://tinylink-production-feda.up.railway.app
```

##

---

## Testing

The API was tested using Postman against the deployed Railway application.

The following functionality was tested:

* User registration
* User login
* Sanctum authentication
* Authenticated user information
* URL creation
* Multiple URL creation
* URL listing
* Pagination
* URL details
* URL statistics
* Public short URL redirection
* Click count tracking
* URL deletion
* Logout
* Protected endpoint access
* User ownership and authorization

---

## Deployment

The application is deployed on **Railway** and is accessible through the following URL:

```text
https://tinylink-production-feda.up.railway.app
```

The deployed API can be tested directly using the included Postman Collection.

---

## Assumptions

* Each shortened URL belongs to the authenticated user who created it.
* Short codes are unique.
* Click count starts from `0`.
* Click count increases when the public short URL is accessed.
* Protected endpoints require a valid Sanctum Bearer token.
* Users can only manage their own URLs.
* The public redirect endpoint does not require authentication.
* The API is designed to be consumed by web, mobile, or other API clients.

---

## API Documentation

For complete request bodies, headers, authentication configuration, and test requests, import the following Postman Collection into Postman:

```text
postman/TinyLink_API.postman_collection.json
```

**Live API:**
`https://tinylink-production-feda.up.railway.app`

##
