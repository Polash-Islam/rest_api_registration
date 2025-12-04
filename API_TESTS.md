# REST API Test Examples

## Test Registration Endpoint

### Example 1: Successful Registration
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

Expected Response (201):
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

### Example 2: Duplicate Email
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Expected Response (422):
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

### Example 3: Password Mismatch
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "differentpassword"
  }'
```

Expected Response (422):
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "password": ["The password field confirmation does not match."]
    }
}
```

### Example 4: Invalid Email Format
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "notanemail",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Expected Response (422):
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field must be a valid email address."]
    }
}
```

### Example 5: Missing Required Fields
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jane Doe"
  }'
```

Expected Response (422):
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

## PowerShell Examples

### Successful Registration
```powershell
$body = @{
    name = "John Doe"
    email = "john@example.com"
    password = "password123"
    password_confirmation = "password123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/register" `
  -Method Post `
  -ContentType "application/json" `
  -Body $body
```

## Postman Collection

Import this JSON into Postman:

```json
{
    "info": {
        "name": "REST API - User Registration",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Register User - Success",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    },
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"name\": \"John Doe\",\n    \"email\": \"john@example.com\",\n    \"password\": \"password123\",\n    \"password_confirmation\": \"password123\"\n}"
                },
                "url": {
                    "raw": "http://127.0.0.1:8000/api/register",
                    "protocol": "http",
                    "host": ["127", "0", "0", "1"],
                    "port": "8000",
                    "path": ["api", "register"]
                }
            }
        },
        {
            "name": "Register User - Duplicate Email",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    },
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"name\": \"Jane Doe\",\n    \"email\": \"john@example.com\",\n    \"password\": \"password123\",\n    \"password_confirmation\": \"password123\"\n}"
                },
                "url": {
                    "raw": "http://127.0.0.1:8000/api/register",
                    "protocol": "http",
                    "host": ["127", "0", "0", "1"],
                    "port": "8000",
                    "path": ["api", "register"]
                }
            }
        }
    ]
}
```
