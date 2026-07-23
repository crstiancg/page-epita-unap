---
name: project-onboarding
description: "Steps required after cloning or pulling this repository (page-epita-unap) before the app runs locally: backend dependency install, Passport encryption keys, database seeding, and how to wire the Angular frontend to the Passport password-grant login. Activate when the user mentions cloning the repo, pulling latest changes, setting up a new machine/environment, 'no me funciona el login', missing oauth keys errors, invalid_client errors, or configuring Angular auth against this backend."
---

# Onboarding this project after clone/pull

This repo has two apps: `backend-page` (Laravel 13 + `spatie/laravel-permission` + `laravel/passport`, password grant) and `frontend-page` (Angular 22, standalone, skeleton — no auth code written yet). A few backend artifacts are **intentionally not committed** (encryption keys, OAuth client rows live in the DB), so every fresh clone / fresh DB needs a short setup sequence.

## Backend (`backend-page`)

Run once after cloning, and again any time the database is recreated (`migrate:fresh`, new environment, new machine):

```bash
composer install          # laravel/passport and spatie/laravel-permission are already
                           # pinned in composer.json/composer.lock — do NOT run
                           # `composer require laravel/passport` again, it's not needed.

cp .env.example .env       # only if .env doesn't exist yet
php artisan key:generate   # only if APP_KEY is empty

php artisan migrate        # creates oauth_* tables and the permission tables

php artisan passport:keys  # REQUIRED every fresh clone/DB — storage/oauth-*.key are
                           # gitignored (`/storage/*.key` in .gitignore) and are
                           # per-machine/per-environment. Without this you get
                           # "Encryption keys not found" errors.

php artisan db:seed        # runs RolesAndPermissionsSeeder (roles, permissions,
                           # admin user) and PassportClientSeeder (creates the
                           # password-grant OAuth client and prints its Client ID).
```

`PassportClientSeeder` is idempotent — re-running `db:seed` won't create a duplicate client, it reuses the first client that has the `password` grant type. But if the DB is wiped (`migrate:fresh`), a **new** client id is generated, and any frontend config pointing at the old id will start failing with `invalid_client`.

Seeded credentials for local testing: `admin@epita-unap.test` / `password` (role `Administrador`).

### Common pitfalls

- **"Encryption keys not found"** → forgot `php artisan passport:keys`.
- **`invalid_client` on login** → the frontend's `oauthClientId` doesn't match the client currently in `oauth_clients` (usually after `migrate:fresh`). Re-copy the id printed by `PassportClientSeeder`.
- **Re-installing Passport/Spatie** → not needed on pull, they're already in `composer.lock`; just `composer install`.

## Frontend (`frontend-page`)

The Angular skeleton has no HTTP client or auth wiring yet (`app.config.ts` only has `provideRouter`). This is the pattern to follow when building it, matching how the backend is configured (password grant against Passport's native `/oauth/token`, no `client_secret` — the seeded client is public):

1. **Environment config** (`ng generate environments` if `src/environments/` doesn't exist yet):

   ```ts
   // src/environments/environment.ts
   export const environment = {
     apiUrl: 'http://127.0.0.1:8000',
     oauthClientId: '<Client ID printed by PassportClientSeeder>',
   };
   ```

2. **Register HttpClient** in `app.config.ts` (currently missing):

   ```ts
   providers: [
     provideBrowserGlobalErrorListeners(),
     provideRouter(routes),
     provideHttpClient(withInterceptors([authInterceptor])),
   ]
   ```

3. **Login** — `POST {apiUrl}/oauth/token`:

   ```ts
   this.http.post(`${environment.apiUrl}/oauth/token`, {
     grant_type: 'password',
     client_id: environment.oauthClientId,
     username,
     password,
     scope: '',
   });
   ```

   Store `access_token` / `refresh_token` from the response (e.g. a signal-based `AuthService` backed by `localStorage` so the session survives reloads).

4. **Attach the token** — a functional interceptor (`HttpInterceptorFn`) adding `Authorization: Bearer <access_token>` to requests going to `environment.apiUrl`.

5. **Hydrate roles/permissions after login** — `GET {apiUrl}/api/user` returns `{ user, roles, permissions }`; keep this in the same `AuthService` state and use it for route guards / conditional UI.

6. **On 401** — either call `/oauth/token` again with `grant_type=refresh_token` or clear the session and redirect to the login route.
