# Quick Start with Docker

This guide will help you get the REST API up and running with Docker in minutes.

## Prerequisites

- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose
- Gmail API credentials (Client ID, Client Secret, Refresh Token)

## Step-by-Step Setup

### 1. Clone the Repository

```bash
git clone https://github.com/Polash-Islam/rest_api_registration.git
cd rest_api_registration
```

### 2. Configure Environment

Copy the Docker environment template:

```bash
cp .env.docker .env
```

Edit `.env` and add your Gmail API credentials:

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/gmail/callback
GOOGLE_REFRESH_TOKEN=your_google_refresh_token
```

**Note:** If you don't have Gmail API credentials yet, see the [Gmail API Setup Guide](README.md#-gmail-api-setup---complete-oauth2-guide) in README.md

### 3. Generate Application Key (Optional - First Time Only)

If you need to generate APP_KEY:

```bash
# On Windows PowerShell
docker run --rm -v ${PWD}:/app -w /app php:8.1-cli php artisan key:generate --show

# Copy the generated key to .env file
APP_KEY=base64:your_generated_key_here
```

### 4. Start the Application

```bash
docker-compose up --build
```

This will:
- Build the Laravel application container
- Start PostgreSQL database
- Run migrations automatically
- Start nginx, PHP-FPM, and queue worker
- Make API available at http://localhost:8000

### 5. Test the API

**Using cURL:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Expected Response:**
```json
{
    "success": true,
    "message": "User registered successfully. A welcome email has been sent.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2025-12-05T10:30:00.000000Z"
        }
    }
}
```

### 6. Generate Gmail Refresh Token (If Needed)

If you haven't generated a refresh token yet:

1. Visit: http://localhost:8000/api/gmail/auth
2. Copy the `authorization_url` from response
3. Open that URL in browser and authorize
4. Copy the `refresh_token` from callback response
5. Add to `.env` and restart:

```bash
docker-compose restart
```

## Common Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Access container shell
docker-compose exec app sh

# Run tests
docker-compose exec app php artisan test

# Run migrations
docker-compose exec app php artisan migrate

# Check supervisor status
docker-compose exec app supervisorctl status
```

## Troubleshooting

### Port 8000 already in use

Edit `docker-compose.yml` and change the port:

```yaml
ports:
  - "8080:8000"  # Change 8080 to any available port
```

### Database connection error

Ensure `DB_HOST=postgres` in `.env` (not `127.0.0.1`)

### Permission errors

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Emails not sending

1. Verify Gmail API credentials are correct in `.env`
2. Check queue worker: `docker-compose exec app supervisorctl status queue-worker`
3. View logs: `docker-compose logs app`

## What's Running?

- **Nginx** - Web server (port 8000)
- **PHP-FPM** - PHP processor
- **Queue Worker** - Background email processing via Gmail API
- **PostgreSQL** - Database (port 5432)
- **Supervisor** - Process manager for all services

All services run in a single container for simplicity!

## Next Steps

- Read the full [README.md](README.md) for detailed documentation
- Check [ARCHITECTURE.md](ARCHITECTURE.md) for system design details
- Run tests: `docker-compose exec app php artisan test`
- View API documentation in README.md

## Support

If you encounter any issues:
1. Check logs: `docker-compose logs -f`
2. Review troubleshooting section in README.md
3. Ensure all environment variables are set correctly
