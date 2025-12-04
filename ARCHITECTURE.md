# System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         REST API ARCHITECTURE                                │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────┐
│   Client    │
│  (Postman,  │
│   cURL,     │
│   Mobile)   │
└──────┬──────┘
       │
       │ POST /api/register
       │ {name, email, password}
       │
       ▼
┌──────────────────────────────────────────────────────────────────────┐
│                         Laravel Application                           │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  routes/api.php                                              │   │
│  │  Route: POST /api/register                                   │   │
│  └───────────────────────┬─────────────────────────────────────┘   │
│                          │                                           │
│                          ▼                                           │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  app/Http/Controllers/Api/RegisterController.php            │   │
│  │                                                              │   │
│  │  1. Validate Request                                        │   │
│  │     - Name: required                                        │   │
│  │     - Email: required, unique                               │   │
│  │     - Password: required, confirmed, min:8                  │   │
│  │                                                              │   │
│  │  2. Create User                                             │   │
│  │     - Hash password                                         │   │
│  │     - Save to database                                      │   │
│  │                                                              │   │
│  │  3. Queue Email Notification                                │   │
│  │     - Non-blocking operation                                │   │
│  │     - Job stored in 'jobs' table                            │   │
│  │                                                              │   │
│  │  4. Return JSON Response                                    │   │
│  │     - Status: 201 Created                                   │   │
│  │     - User data                                             │   │
│  └──────────┬───────────────────────┬────────────────────────┘   │
│             │                       │                              │
│             │                       │                              │
│             ▼                       ▼                              │
│  ┌──────────────────┐   ┌─────────────────────────────────┐      │
│  │   PostgreSQL     │   │  app/Notifications/             │      │
│  │                  │   │  WelcomeEmailNotification.php   │      │
│  │  - users         │   │                                 │      │
│  │  - jobs          │   │  implements ShouldQueue         │      │
│  │  - failed_jobs   │   │                                 │      │
│  └──────────────────┘   └──────────────┬──────────────────┘      │
│                                         │                          │
└─────────────────────────────────────────┼──────────────────────────┘
                                          │
                                          │ Job stored in database
                                          │
                                          ▼
                           ┌──────────────────────────────┐
                           │   Queue Worker               │
                           │   php artisan queue:work     │
                           │                              │
                           │   - Polls jobs table         │
                           │   - Processes queued jobs    │
                           │   - Sends emails             │
                           └──────────────┬───────────────┘
                                          │
                                          │ Send email via SMTP
                                          │
                                          ▼
                           ┌──────────────────────────────┐
                           │   Gmail SMTP Server          │
                           │   smtp.gmail.com:587         │
                           │                              │
                           │   - TLS encryption           │
                           │   - App password auth        │
                           └──────────────┬───────────────┘
                                          │
                                          │ Email delivered
                                          │
                                          ▼
                           ┌──────────────────────────────┐
                           │   User's Email Inbox         │
                           │   Welcome Email Received     │
                           └──────────────────────────────┘


═══════════════════════════════════════════════════════════════════════════
                              DATA FLOW SEQUENCE
═══════════════════════════════════════════════════════════════════════════

Step 1: Client Request
┌─────────┐
│ Client  │ ──POST /api/register──> 
└─────────┘

Step 2: Validation & User Creation (FAST - <500ms)
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Validate    │ ──> │ Create User  │ ──> │ Queue Email  │
│  Input       │     │ in Database  │     │ Job          │
└──────────────┘     └──────────────┘     └──────────────┘

Step 3: Immediate Response (Non-blocking)
┌─────────┐
│ Client  │ <──201 Created (User data)──
└─────────┘
   ↑
   └── Response sent WITHOUT waiting for email

Step 4: Background Email Processing (Asynchronous)
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ Queue Worker │ ──> │ Send Email   │ ──> │ Gmail SMTP   │
│ Picks Job    │     │ via Gmail    │     │ Delivers     │
└──────────────┘     └──────────────┘     └──────────────┘


═══════════════════════════════════════════════════════════════════════════
                         DATABASE SCHEMA
═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────┐
│          users table                │
├─────────────────────────────────────┤
│ id               BIGINT (PK)        │
│ name             VARCHAR(255)       │
│ email            VARCHAR(255) UNIQUE│
│ password         VARCHAR(255) HASHED│
│ email_verified_at TIMESTAMP NULL    │
│ created_at       TIMESTAMP          │
│ updated_at       TIMESTAMP          │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│          jobs table                 │
├─────────────────────────────────────┤
│ id               BIGINT (PK)        │
│ queue            VARCHAR(255)       │
│ payload          TEXT                │
│ attempts         TINYINT            │
│ reserved_at      TIMESTAMP NULL     │
│ available_at     TIMESTAMP          │
│ created_at       TIMESTAMP          │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│       failed_jobs table             │
├─────────────────────────────────────┤
│ id               BIGINT (PK)        │
│ uuid             VARCHAR(255) UNIQUE│
│ connection       TEXT                │
│ queue            TEXT                │
│ payload          TEXT                │
│ exception        TEXT                │
│ failed_at        TIMESTAMP          │
└─────────────────────────────────────┘


═══════════════════════════════════════════════════════════════════════════
                      REQUEST/RESPONSE FLOW TIMING
═══════════════════════════════════════════════════════════════════════════

WITHOUT QUEUE (Blocking - Slow):
┌──────────┐ ┌──────────┐ ┌──────────────────────┐ ┌──────────┐
│ Validate │→│  Create  │→│ Send Email (2-5 sec) │→│ Response │
│  50ms    │ │ User 50ms│ │      WAITING...      │ │ 3-5 sec  │
└──────────┘ └──────────┘ └──────────────────────┘ └──────────┘
Total Response Time: 3-5 seconds ❌


WITH QUEUE (Non-blocking - Fast):
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│ Validate │→│  Create  │→│ Queue Job│→│ Response │
│  50ms    │ │ User 50ms│ │   50ms   │ │  <500ms  │
└──────────┘ └──────────┘ └──────────┘ └──────────┘
                                ↓
                          [Background]
                      ┌──────────────────┐
                      │ Queue Worker     │
                      │ Sends Email      │
                      │ 2-5 seconds      │
                      └──────────────────┘
Total Response Time: <500ms ✅


═══════════════════════════════════════════════════════════════════════════
                           SECURITY LAYERS
═══════════════════════════════════════════════════════════════════════════

Input Layer:
┌─────────────────────────────────────┐
│ • Email format validation           │
│ • Password strength (min 8 chars)   │
│ • Password confirmation required    │
│ • SQL injection protection          │
└─────────────────────────────────────┘

Database Layer:
┌─────────────────────────────────────┐
│ • Password hashing (Bcrypt)         │
│ • Unique email constraint           │
│ • Prepared statements (Eloquent)    │
└─────────────────────────────────────┘

Email Layer:
┌─────────────────────────────────────┐
│ • TLS encryption (port 587)         │
│ • App password (not real password)  │
│ • Gmail OAuth2 support              │
└─────────────────────────────────────┘

Application Layer:
┌─────────────────────────────────────┐
│ • .env file (credentials hidden)    │
│ • CSRF protection (Laravel)         │
│ • Exception handling                │
└─────────────────────────────────────┘
```
