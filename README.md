# REST API - User Registration with Email Notification

A Laravel REST API that handles user registration and sends welcome emails asynchronously using Gmail API with OAuth2 authentication.

## Features

- ✅ RESTful API design with JSON responses
- ✅ User registration endpoint (`POST /api/register`)
- ✅ Asynchronous email sending using queues (non-blocking)
- ✅ **Gmail API with OAuth2** for sending emails (as per assignment requirement)
- ✅ PostgreSQL database
- ✅ Comprehensive error handling and validation
- ✅ Well-commented code
- ✅ **Automated testing with PHPUnit (12 test cases)**
- ✅ **Docker support** for easy deployment

## Requirements

### Local Development
- PHP >= 8.1
- Composer
- PostgreSQL
- Laravel 10.x
- Google Cloud Project with Gmail API enabled
- Google OAuth2 credentials (Client ID, Client Secret, Refresh Token)
- PHPUnit (included with Laravel)

### Docker Development (Recommended)
- Docker Desktop or Docker Engine
- Docker Compose
- Google OAuth2 credentials (Client ID, Client Secret, Refresh Token)

## Installation & Setup

### Option A: Using Docker (Recommended)

#### 1. Clone the Repository

```bash
git clone <your-repo-url>
cd Rest_api_fresh
```

#### 2. Configure Environment for Docker

Create a `.env` file from `.env.example`:

```bash
cp .env.example .env
```

Update the following in `.env`:

```env
# Application
APP_NAME=Laravel
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

# Database (Docker)
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=rest_api_db
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Queue
QUEUE_CONNECTION=database

# Gmail API
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/gmail/callback
GOOGLE_REFRESH_TOKEN=your_refresh_token
```

#### 3. Build and Start Docker Containers

```bash
docker-compose up --build
```

This command will:
- Build the Laravel application container
- Start PostgreSQL database container
- Run database migrations automatically
- Start nginx web server on port 8000
- Start PHP-FPM for processing PHP requests
- Start queue worker for processing emails in background

#### 4. Access the Application

The API will be available at: `http://localhost:8000`

#### 5. Stop the Application

```bash
# Stop containers
docker-compose down

# Stop and remove volumes (WARNING: This deletes database data)
docker-compose down -v
```

#### Docker Useful Commands

```bash
# View logs
docker-compose logs -f

# View app logs only
docker-compose logs -f app

# Access app container shell
docker-compose exec app sh

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan test

# Restart containers
docker-compose restart

# Rebuild after code changes
docker-compose up --build
```

---

### Option B: Local Installation (Manual Setup)

### 1. Clone the Repository

```bash
git clone <your-repo-url>
cd Rest_api_fresh
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the `.env` file and update the following configurations:

#### Database Configuration (PostgreSQL)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rest_api_db
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

#### Gmail API Configuration (OAuth2)

```env
GOOGLE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/gmail/callback
GOOGLE_REFRESH_TOKEN=your_refresh_token
```

### 📧 Gmail API Setup - Complete OAuth2 Guide

This project uses **Gmail API with OAuth2** for sending emails as per assignment requirements. Follow these steps to configure Gmail API credentials from Google Cloud Console.

#### Step 1: Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **Create Project**
3. Enter project name: `Laravel REST API`
4. Click **Create**

#### Step 2: Enable Gmail API
1. In your project, go to **APIs & Services** → **Library**
2. Search for **Gmail API**
3. Click on it and click **Enable**

#### Step 3: Configure OAuth Consent Screen
1. Go to **APIs & Services** → **OAuth consent screen**
2. User Type: **External** → Click **Create**
3. Fill in required fields:
   - App name: `Laravel REST API`
   - User support email: Your email
   - Developer contact: Your email
4. Click **Save and Continue**
5. Scopes: Skip this step (click **Save and Continue**)
6. Test users: Click **+ ADD USERS**
   - Add your Gmail address (the one you'll use to send emails)
   - Click **Add** → **Save and Continue**

#### Step 4: Create OAuth 2.0 Credentials
1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **OAuth client ID**
3. Application type: **Web application**
4. Name: `REST API Email Sender`
5. Authorized redirect URIs: Click **+ ADD URI**
   - Add: `http://localhost:8000/api/gmail/callback`
6. Click **Create**
7. Copy the **Client ID** and **Client Secret**

#### Step 5: Add Credentials to .env
```env
GOOGLE_CLIENT_ID=294704329553-xxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URI=http://localhost:8000/api/gmail/callback
```

#### Step 6: Generate Refresh Token

**For Docker:**
1. Start containers: `docker-compose up --build`
2. Visit in browser: `http://localhost:8000/api/gmail/auth`
3. Follow steps 3-9 below

**For Local Development:**
1. Start Laravel server: `php artisan serve`
2. Visit in browser: `http://127.0.0.1:8000/api/gmail/auth`

**Common Steps:**
3. Copy the `authorization_url` from JSON response
4. Open that URL in browser
5. Select your Gmail account
6. Click **Allow** to grant Gmail send permission
7. You'll be redirected to callback URL with refresh token
8. Copy the `refresh_token` from JSON response
9. Add to `.env`:
```env
GOOGLE_REFRESH_TOKEN=1//0g-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```
10. If using Docker, restart: `docker-compose restart`

**Important Notes:**
- ✅ Uses Gmail API OAuth2 (not SMTP) as per assignment requirement
- ✅ Client ID and Client Secret authenticate your application
- ✅ Refresh token allows sending emails without repeated authorization
- ⚠️ Keep refresh token secure - it provides ongoing access
- ⚠️ Never commit actual credentials to version control

#### Queue Configuration

```env
QUEUE_CONNECTION=database
```

### 4. Setup PostgreSQL Database

#### Option A: Using pgAdmin (GUI - Recommended for Beginners)
1. Open **pgAdmin** (comes with PostgreSQL installation)
2. Connect to your PostgreSQL server
3. Right-click on **Databases** → **Create** → **Database**
4. Enter database name: `rest_api_db`
5. Click **Save**

#### Option B: Using Command Line (psql)
```bash
# Windows (if PostgreSQL is in PATH)
psql -U postgres

# Or specify full path
"C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres

# Inside psql, create database
CREATE DATABASE rest_api_db;

# Exit
\q
```

#### Option C: Using Laragon (if PostgreSQL module installed)
1. Open Laragon
2. Click **Database** → **PostgreSQL**
3. Create new database named `rest_api_db`

#### Enable PostgreSQL Extension in PHP
If you get "could not find driver" error:

**For Laragon users:**
1. Laragon → Menu → PHP → php.ini
2. Find these lines and remove the semicolon (;):
   ```ini
   ;extension=pdo_pgsql
   ;extension=pgsql
   ```
   Change to:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Save and restart Laragon

**For XAMPP/Other:**
1. Open `php.ini` file
2. Enable the same extensions as above
3. Restart Apache/Web server

### 5. Run Migrations

```bash
php artisan migrate
```

This will create:
- `users` table
- `jobs` table (for queue management)
- `failed_jobs` table
- Other Laravel default tables

### 6. Start the Queue Worker

The queue worker processes background jobs (email sending). Run this in a separate terminal:

```bash
php artisan queue:work
```

**Keep this terminal running** to process queued emails.

### 7. Start the Development Server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`

## API Documentation

### Register User

Creates a new user account and sends a welcome email asynchronously.

**Endpoint:** `POST /api/register`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "User registered successfully. A welcome email has been sent.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2025-12-03T10:30:00.000000Z"
        }
    }
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password field confirmation does not match."]
    }
}
```

**Server Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Registration failed",
    "error": "Error message details"
}
```

### Field Validations

- `name`: Required, string, max 255 characters
- `email`: Required, valid email format, unique in database, max 255 characters
- `password`: Required, min 8 characters, must be confirmed
- `password_confirmation`: Required, must match password

## Testing the API

### Manual Testing

#### Using cURL

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Using Postman

1. Create a new POST request
2. URL: `http://127.0.0.1:8000/api/register`
3. Headers:
   - `Content-Type`: `application/json`
   - `Accept`: `application/json`
4. Body (raw JSON):
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Automated Testing

The project includes comprehensive automated tests using PHPUnit to ensure code quality and reliability.

#### Run All Tests

```bash
php artisan test
```

#### Run Specific Test Suite

```bash
# Run only registration tests
php artisan test --filter=RegistrationTest

# Run with detailed output
php artisan test --filter=RegistrationTest --verbose
```

#### Test Coverage

The test suite includes **12 test cases** covering:

1. ✅ Successful registration with valid data
2. ✅ Registration fails with duplicate email
3. ✅ Registration fails with invalid email format
4. ✅ Registration fails with short password (< 8 characters)
5. ✅ Registration fails when password confirmation doesn't match
6. ✅ Registration fails with missing required fields
7. ✅ Registration fails with name exceeding 255 characters
8. ✅ Welcome email notification is queued after registration
9. ✅ Notification is sent to the correct user
10. ✅ Validation errors have correct JSON structure
11. ✅ User count increases after successful registration
12. ✅ Registration works with special characters in name

#### Test Results

```
Tests:    12 passed (45 assertions)
Duration: ~3-4 seconds
```

All tests validate:
- ✅ API response structure and status codes
- ✅ Database integrity and data persistence
- ✅ Validation rules enforcement
- ✅ Email notification queuing
- ✅ Error handling and messages

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── RegisterController.php       # Handles user registration
│           └── GmailAuthController.php      # OAuth2 authorization flow
├── Models/
│   └── User.php                             # User model with Notifiable trait
├── Notifications/
│   ├── WelcomeEmailNotification.php         # Welcome email notification (uses Gmail API)
│   └── GmailApiChannel.php                  # Custom Gmail API notification channel
└── Services/
    └── GmailApiService.php                  # Gmail API OAuth2 service (email sending)

routes/
├── api.php                                  # API routes (register, OAuth2 auth/callback)
└── web.php                                  # Web routes

config/
├── services.php                             # Google OAuth2 configuration
└── queue.php                                # Queue driver configuration

database/
├── migrations/
│   ├── *_create_users_table.php             # User table schema
│   ├── *_create_jobs_table.php              # Queue jobs table
│   └── *_create_failed_jobs_table.php       # Failed jobs tracking
└── seeders/
    └── DatabaseSeeder.php                   # Database seeding

tests/
└── Feature/
    └── RegistrationTest.php                 # Automated test suite (12 tests)

docker/
├── nginx/
│   ├── nginx.conf                           # Nginx main configuration
│   └── default.conf                         # Laravel site configuration
├── supervisor/
│   └── supervisord.conf                     # Supervisor config (nginx, php-fpm, queue worker)
└── docker-entrypoint.sh                     # Container startup script

Dockerfile                                   # Docker image definition
docker-compose.yml                           # Docker services orchestration
.dockerignore                                # Files excluded from Docker build
.env                                         # Environment variables (Gmail API credentials)
composer.json                                # PHP dependencies (includes google/apiclient)
```

## How It Works

### Standard Flow
1. **User sends registration request** → API receives POST data
2. **Validation** → Checks if email is unique and password is confirmed
3. **User creation** → Creates user record in PostgreSQL database
4. **Queue notification** → Dispatches email job to queue (non-blocking)
5. **Immediate response** → Returns success response to user
6. **Background email** → Queue worker picks up job and sends email via **Gmail API OAuth2**

### Docker Architecture
```
┌─────────────────────────────────────────┐
│         Docker Compose Network          │
│                                         │
│  ┌───────────────┐  ┌──────────────┐  │
│  │  App Container│  │   PostgreSQL │  │
│  │               │  │   Container  │  │
│  │ - Nginx :8000 │◄─┤              │  │
│  │ - PHP-FPM     │  │   Port 5432  │  │
│  │ - Queue Worker│  │              │  │
│  │ - Supervisor  │  └──────────────┘  │
│  └───────────────┘                     │
│         │                               │
└─────────┼───────────────────────────────┘
          │
     Port 8000
          │
    ┌─────▼──────┐
    │   Client   │
    └────────────┘
```

The email sending is asynchronous and uses Gmail API (not SMTP) as per assignment requirement, so the API response is fast and doesn't wait for the email to be sent.

## Troubleshooting

### Docker Issues

**Containers won't start:**
```bash
# Check logs
docker-compose logs -f

# Rebuild from scratch
docker-compose down -v
docker-compose up --build
```

**Database connection failed:**
```bash
# Verify postgres container is healthy
docker-compose ps

# Check postgres logs
docker-compose logs postgres

# Ensure DB_HOST=postgres in .env (not 127.0.0.1)
```

**Port 8000 already in use:**
```bash
# Stop existing services
docker-compose down

# Or change port in docker-compose.yml:
# ports:
#   - "8080:8000"
```

**Permission errors:**
```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Emails Not Sending

**For Docker:**
1. Check queue worker logs: `docker-compose logs -f app | grep queue`
2. Check supervisor status: `docker-compose exec app supervisorctl status`
3. View worker logs: `docker-compose exec app cat storage/logs/worker.log`
4. Verify Gmail API credentials in `.env`
5. Restart containers: `docker-compose restart`

**For Local Development:**
1. Check queue worker is running: `php artisan queue:work`
2. Verify Gmail API credentials in `.env` (Client ID, Client Secret, Refresh Token)
3. Ensure refresh token is valid (regenerate if needed via `/api/gmail/auth`)
4. Check `jobs` table for pending jobs: `SELECT * FROM jobs;`
5. Check `failed_jobs` table for errors: `SELECT * FROM failed_jobs;`
6. View Laravel logs: `storage/logs/laravel.log`

### Gmail API Issues

1. Verify OAuth2 credentials are correct
2. Check if test user is added in Google Cloud Console
3. Regenerate refresh token if expired: Visit `http://127.0.0.1:8000/api/gmail/auth`
4. Ensure Gmail API is enabled in Google Cloud Console

### Database Connection Issues

**For Docker:**
1. Verify postgres container is running: `docker-compose ps`
2. Check DB_HOST is set to `postgres` (not `127.0.0.1`)
3. Test connection: `docker-compose exec app php artisan migrate:status`

**For Local Development:**
1. Verify PostgreSQL is running
2. Check database credentials in `.env`
3. Ensure database `rest_api_db` exists
4. Test connection: `php artisan migrate:status`

### Queue Issues

**For Docker:**
1. Check supervisor logs: `docker-compose exec app supervisorctl tail -f queue-worker`
2. Restart queue worker: `docker-compose exec app supervisorctl restart queue-worker`
3. View failed jobs: `docker-compose exec app php artisan queue:failed`

**For Local Development:**
1. Clear failed jobs: `php artisan queue:flush`
2. Retry failed jobs: `php artisan queue:retry all`
3. Restart queue worker: Stop and restart `php artisan queue:work`

## Additional Commands

### Docker Commands

```bash
# Access container shell
docker-compose exec app sh

# Run artisan commands
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:list
docker-compose exec app php artisan migrate:fresh
docker-compose exec app php artisan test

# Check supervisor processes
docker-compose exec app supervisorctl status

# Restart specific service
docker-compose exec app supervisorctl restart queue-worker

# View real-time logs
docker-compose logs -f app

# Database access
docker-compose exec postgres psql -U postgres -d rest_api_db
```

### Local Development Commands

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# View routes
php artisan route:list

# Run in production with queue worker
php artisan queue:work --daemon

# Monitor queue in real-time
php artisan queue:listen
```



## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
