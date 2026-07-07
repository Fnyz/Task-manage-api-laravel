# Task Manager API

A RESTful API built with **Laravel 13** for managing tasks and categories. Features token-based authentication via Laravel Sanctum, email verification, soft deletes, in-app notifications, and is deployed on Render using Docker.

**Live URL:** https://task-manage-api-laravel.onrender.com

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.3+ |
| Database | PostgreSQL |
| Auth | Laravel Sanctum (API tokens) |
| Containerization | Docker (php:8.4-fpm-alpine + nginx + supervisor) |
| Deployment | Render |

---

## Features

- User registration & login with **Laravel Sanctum** token authentication
- **Email verification** required before accessing protected resources
- Full **CRUD** for Tasks and Categories (per-user, policy-enforced)
- **Soft deletes** with restore endpoint for both tasks and categories
- Task **completion notification** (database notification when a task is marked complete)
- Task **filtering** by category, completion status, date range, and free-text search
- **Sorting & pagination** on task and category listings
- Rate limiting on auth and API routes

---

## Local Setup

### Prerequisites

- PHP 8.3+
- Composer
- PostgreSQL (or MySQL)

### Installation

```bash
git clone https://github.com/Fnyz/Task-manage-api-laravel.git
cd Task-manage-api-laravel

composer install

cp .env.example .env
php artisan key:generate
```

### Configure `.env`

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_manager
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_user
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=no-reply@example.com
```

### Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed   # optional — seeds users, categories, and tasks
```

### Start the Development Server

```bash
php artisan serve
```

---

## Docker (Production)

The app is containerised using a custom Dockerfile based on `php:8.4-fpm-alpine` with nginx and supervisor.

```bash
docker build -t task-manager .
docker run -p 80:80 --env-file .env task-manager
```

On container start, `docker/start.sh` runs:
1. `php artisan config:cache`
2. `php artisan route:cache`
3. `php artisan migrate --force`
4. Starts nginx + php-fpm via supervisord

---

## API Reference

All endpoints are prefixed with `/api/v1`.

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/register` | Register a new user | No |
| `POST` | `/login` | Login and receive an API token | No |
| `POST` | `/logout` | Revoke the current token | Yes |

**Register request body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Login request body:**
```json
{
  "email": "john@example.com",
  "password": "password"
}
```

**Login response:**
```json
{
  "message": "Login successful",
  "user": { ... },
  "token": "1|abc123..."
}
```

Include the token in subsequent requests:
```
Authorization: Bearer {token}
```

---

### Email Verification

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/email/verify/{id}/{hash}` | Verify email via signed link |
| `POST` | `/email/verification-notification` | Resend verification email |

> Tasks and categories require a verified email address.

---

### Tasks

> All task endpoints require `Authorization: Bearer {token}` and a verified email.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/tasks` | List authenticated user's tasks (paginated) |
| `POST` | `/tasks` | Create a new task |
| `GET` | `/tasks/{id}` | Get a single task |
| `PUT/PATCH` | `/tasks/{id}` | Update a task |
| `DELETE` | `/tasks/{id}` | Soft-delete a task |
| `POST` | `/tasks/{id}/restore` | Restore a soft-deleted task |

**Query parameters for `GET /tasks`:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `category_id` | integer | Filter by category |
| `is_completed` | boolean | Filter by completion status |
| `search` | string | Search title, description, or category name |
| `from_date` | date | Filter tasks created on or after this date |
| `to_date` | date | Filter tasks created on or before this date |
| `sort` | string | Sort field: `title`, `created_at`, `updated_at`, `is_completed` |
| `order` | string | `asc` or `desc` (default: `desc`) |
| `per_page` | integer | Items per page (default: 5) |

**Task fields:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | Yes | Max 255 characters |
| `description` | string | No | Task description |
| `category_id` | integer | No | Must reference an existing category |
| `is_completed` | boolean | No | Default: `false` |
| `due_date` | date | No | Due date for the task |

---

### Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/categories` | List authenticated user's categories (paginated) |
| `POST` | `/categories` | Create a new category |
| `GET` | `/categories/{id}` | Get a single category |
| `PUT/PATCH` | `/categories/{id}` | Update a category |
| `DELETE` | `/categories/{id}` | Soft-delete a category |
| `POST` | `/categories/{id}/restore` | Restore a soft-deleted category |

**Category fields:**

| Field | Type | Required |
|-------|------|----------|
| `name` | string | Yes |

---

### Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/notifications` | List all notifications for the authenticated user |
| `POST` | `/notifications/{id}/read` | Mark a notification as read |

A database notification is sent automatically when a task's `is_completed` is set to `true`.

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/        # AuthController, TaskController, CategoryController
│   ├── Requests/           # Form request validation classes
│   └── Resources/          # API resource transformers
├── Models/                 # User, Task, Category
├── Notifications/          # TaskCompletedNotification
└── Policies/               # TaskPolicy, CategoryPolicy
database/
├── migrations/
└── seeders/
routes/
└── api/v1.php              # All API routes
docker/
├── nginx.conf
├── supervisord.conf
└── start.sh
scripts/
└── 00-laravel-deploy.sh    # Runs on container startup
```

---

## Running Tests

```bash
php artisan test
```

---

## Deployment (Render)

1. Create a **Web Service** on Render pointing to this repository
2. Set **Environment** to `Docker`
3. Create a **PostgreSQL** service on Render and link it to the web service (auto-injects `DATABASE_URL`)
4. Add the following environment variables in Render:

| Variable | Value |
|----------|-------|
| `APP_KEY` | Generate with `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `pgsql` |
| `MAIL_*` | Your mail provider credentials |

Render will build the Docker image and run migrations automatically on each deploy.

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# Task-manage-api-laravel
