# Mon App — Full-Stack Authentication Platform

A full-stack monorepo with a **Laravel 10** backend (API, PostgreSQL, Docker) and an **Angular 18** frontend (standalone, Vite).

## Live Demo

- **Frontend:** [https://projetcs-frontend.vercel.app](https://projetcs-frontend.vercel.app)
- **Backend API:** [https://projetcs-backend-1.onrender.com](https://projetcs-backend-1.onrender.com)

---

## Features

- User registration with email/password and email verification
- Local login (email/password) with Sanctum token authentication
- Google OAuth 2.0 login
- Profile management (view, edit name/email, upload avatar)
- Password change (authenticated)
- Password reset via email (forgot password flow)
- Account deletion
- Protected routes and guest-only routes
- Auth interceptor (auto-attaches Bearer token, handles 401)

---

## Tech Stack

| Layer          | Technology                              |
| -------------- | --------------------------------------- |
| Backend        | PHP 8.3, Laravel 10                     |
| Frontend       | Angular 18 (standalone, Vite)           |
| Database       | PostgreSQL (Render)                     |
| Auth           | Laravel Sanctum (tokens), Socialite     |
| Email          | SendGrid SMTP                           |
| Deployment     | Render (Docker) + Vercel                |
| Runtime        | nginx + PHP-FPM + Supervisor (Docker)   |

---

## Database Schema

### `users`

| Column             | Type         | Constraints                  |
| ------------------ | ------------ | ---------------------------- |
| `id`               | bigint       | PK, auto-increment           |
| `name`             | string(255)  | required                     |
| `email`            | string(255)  | required, unique             |
| `email_verified_at`| timestamp    | nullable                     |
| `password`         | string(255)  | required (hashed)            |
| `google_id`        | string(255)  | nullable, unique             |
| `avatar`           | string(255)  | nullable (path to storage)   |
| `remember_token`   | string(100)  | nullable                     |
| `created_at`       | timestamp    | auto                         |
| `updated_at`       | timestamp    | auto                         |

### `personal_access_tokens` (Sanctum)

| Column             | Type         | Constraints                  |
| ------------------ | ------------ | ---------------------------- |
| `id`               | bigint       | PK, auto-increment           |
| `tokenable_type`   | string       | required                     |
| `tokenable_id`     | bigint       | required, FK → users.id      |
| `name`             | string       | required                     |
| `token`            | string(64)   | required, unique             |
| `abilities`        | text         | nullable                     |
| `last_used_at`     | timestamp    | nullable                     |
| `created_at`       | timestamp    | auto                         |
| `updated_at`       | timestamp    | auto                         |

### `password_reset_tokens`

| Column             | Type         | Constraints                  |
| ------------------ | ------------ | ---------------------------- |
| `email`            | string(255)  | primary                      |
| `token`            | string(255)  | required                     |
| `created_at`       | timestamp    | nullable                     |

---

## Authentication Flows

### 1. Registration + Email Verification

```
Frontend → POST /api/register { name, email, password, password_confirmation }
  ├─ If MAIL_MAILER=log: auto-verifies, returns token → redirect to /dashboard
  └─ If SMTP is configured: returns success message, sends verification email
       └─ User clicks link → GET /api/email/verify/{id}/{hash}
            └─ Redirects to /verify-email?status=success&token=...
                 └─ Token stored, redirect to /dashboard
```

**Password requirements:** min 8 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special character (`@$!%*?&`).

### 2. Local Login

```
Frontend → POST /api/login { email, password }
  └─ Validates credentials
  └─ Checks email is verified (if SMTP configured)
       └─ Returns { user, token }
            └─ Token stored in localStorage, redirect to /dashboard
```

### 3. Google OAuth 2.0

```
Frontend → GET /api/auth/google
  └─ Redirects to Google consent screen
  └─ After consent → GET /api/auth/google/callback
       ├─ New user: creates account, redirects to /set-password?email=...
       └─ Existing user: updates google_id, returns token, redirects to /login?token=...
```

Google users can set a password via `POST /api/auth/google/set-password` to enable email/password login later.

### 4. Password Reset

```
Frontend → POST /api/password/forgot { email }
  └─ Sends reset link via email (if SMTP configured)
  └─ User clicks link → /reset-password?token=...&email=...
       └─ POST /api/password/reset { token, email, password, password_confirmation }
            └─ Password updated, redirect to /login
```

### 5. Session Management

- **Token-based:** Each login creates a new Sanctum token stored in `localStorage`.
- **Auth Interceptor:** Automatically attaches `Authorization: Bearer <token>` to all API requests.
- **Auth Guard:** Redirects unauthenticated users to `/login`.
- **Guest Guard:** Redirects authenticated users to `/dashboard`.
- **Logout:** `POST /api/logout` deletes the current token, clears `localStorage`.

---

## API Routes

### Public (no auth required)

| Method | Path                              | Description            |
| ------ | --------------------------------- | ---------------------- |
| GET    | `/api/health`                     | Health check           |
| POST   | `/api/register`                   | Register a new user    |
| POST   | `/api/login`                      | Login                  |
| POST   | `/api/password/forgot`            | Send reset link        |
| POST   | `/api/password/reset`             | Reset password         |
| GET    | `/api/email/verify/{id}/{hash}`   | Verify email           |
| GET    | `/api/auth/google`                | Redirect to Google     |
| GET    | `/api/auth/google/callback`       | Google callback        |
| POST   | `/api/auth/google/set-password`   | Set password (Google)  |

### Authenticated (requires Bearer token)

| Method | Path                 | Description          |
| ------ | -------------------- | -------------------- |
| POST   | `/api/logout`        | Logout               |
| GET    | `/api/user`          | Get profile          |
| PUT    | `/api/user/update`   | Update profile       |
| POST   | `/api/user/avatar`   | Upload avatar        |
| DELETE | `/api/user`          | Delete account       |
| PUT    | `/api/password/change` | Change password    |

---

## Frontend Routes

| Path               | Component        | Guard      |
| ------------------ | ---------------- | ---------- |
| `/login`           | LoginComponent   | guest      |
| `/register`        | RegisterComponent| guest      |
| `/forgot-password` | ForgotPassword   | guest      |
| `/reset-password`  | ResetPassword    | guest      |
| `/verify-email`    | VerifyEmail      | none       |
| `/set-password`    | SetPassword      | none       |
| `/dashboard`       | Dashboard        | auth       |
| `/profile`         | Profile          | auth       |
| `/change-password` | ChangePassword   | auth       |

---

## Local Setup

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+
- PostgreSQL

### Backend

```bash
cd backend
cp .env.example .env
# Edit .env with your local database credentials

composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
ng serve
```

The app will be available at `http://localhost:4200` with the backend at `http://localhost:8000`.

### Docker (Render)

The backend includes a Dockerfile for deployment:

```bash
cd backend
docker build -t mon-app-backend .
docker run -p 8080:8080 mon-app-backend
```

---

## Environment Variables

### Backend (`.env`)

| Variable              | Description                    |
| --------------------- | ------------------------------ |
| `APP_URL`             | Backend URL                    |
| `FRONTEND_URL`        | Frontend URL (CORS)            |
| `DB_CONNECTION`       | Database driver (pgsql)        |
| `DB_HOST`/`DB_PORT`/`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` | Database credentials |
| `MAIL_MAILER`         | Mail driver (smtp or log)      |
| `MAIL_HOST`/`MAIL_PORT`/`MAIL_USERNAME`/`MAIL_PASSWORD` | SMTP credentials  |
| `MAIL_FROM_ADDRESS`   | Sender email address           |
| `GOOGLE_CLIENT_ID`    | Google OAuth client ID         |
| `GOOGLE_CLIENT_SECRET`| Google OAuth client secret     |
| `GOOGLE_REDIRECT_URI` | Google OAuth callback URL      |
| `CORS_ALLOWED_ORIGINS`| Comma-separated CORS origins   |

### Frontend (`environments/`)

| Variable  | Description    |
| --------- | -------------- |
| `apiUrl`  | Backend API URL|

---

## Deployment

### Backend (Render)

1. Connect your GitHub repository
2. Create a **Web Service** → select **Docker**
3. Set the root directory to `backend/`
4. Set build command: `docker build -t app .`
5. Set start command: (handled by entrypoint)
6. Add environment variables from `.env.example`
7. Create a **PostgreSQL** database and link it to the service (auto-sets `DATABASE_URL`)

### Frontend (Vercel)

1. Import your GitHub repository
2. Set root directory to `frontend/`
3. Framework preset: **Angular**
4. Build command: `ng build --configuration production`
5. Output directory: `dist/frontend/browser`
6. Add environment variables
