# Muthaka API — Couples Widget Backend

Muthaka is a widget-first couples app for Kenya ("we are here" 💛). The main experience is the
partner on your phone home screen — moods, notes, doodles, snaps, distance, countdowns and daily
prompts — without opening a chat. This repository is the **Laravel API backend**.

- Product blueprint: [`docs/PROJECT_BLUEPRINT.md`](docs/PROJECT_BLUEPRINT.md)
- Design / layout spec: [`docs/APP_LAYOUT_SPEC.md`](docs/APP_LAYOUT_SPEC.md)
- VPS + MySQL ops guide: [`docs/SYSTEM_DESIGN_VPS_MYSQL.md`](docs/SYSTEM_DESIGN_VPS_MYSQL.md)
- Admin portal + API reference: [`docs/ADMIN_PORTAL_AND_API_FEATURES.md`](docs/ADMIN_PORTAL_AND_API_FEATURES.md)
- Android app (companion repo): `../MuthakaApp`

## Stack

| Layer | Choice |
| --- | --- |
| Framework | Laravel ^13.8 (PHP ^8.3) |
| Auth | Laravel Sanctum (bearer tokens) |
| Real-time | Laravel Reverb ^1.11 (WebSocket) |
| Database | MySQL (`DB_CONNECTION=mysql`) |
| Queue / cache / session | `database` driver |
| Media | `local` filesystem → `storage/app/private` |
| Deployment | Ubuntu VPS + nginx + PHP-FPM + supervisor |

## Requirements

```bash
php >= 8.3       # 8.3 verified; multi-version servers must match the PHP the site runs under
composer
mysql 8
```

## Setup

```bash
cp .env.example .env
php artisan key:generate
# configure DB_*, then:
php artisan migrate
# seed demo users/couple AND the daily-prompt library:
php artisan db:seed
# run everything (dev):
npm run dev            # vite (optional for API work)
php artisan serve
php artisan queue:work database --sleep=3 --tries=3
php artisan reverb:start
```

## API surface (`/api/v1`)

All routes are grouped under `api` prefix + `v1`. Protected routes use `auth:sanctum` and expect
`Authorization: Bearer <token>`. Responses use the `{ data, message }` envelope from
`app/Helpers/ApiResponse`.

**Auth** — `POST /auth/register`, `POST /auth/verify-email`, `POST /auth/resend-email-otp`,
`POST /auth/login`, `POST /auth/google` (ID token), `POST /auth/forgot-password`,
`POST /auth/reset-password`. Authenticated: `GET /auth/me`, `PUT /auth/profile`,
`POST /auth/logout`, `POST /auth/refresh`.

**Couple** — `POST /couple/invite` (code-only or email), `POST /couple/accept/{code}`,
`POST /couple/reject/{code}`, `POST /couple/cancel`, `DELETE /couple/disconnect` (soft),
`POST /couple/block`, `GET /couple/status`, `GET /couple/partner`.

**Content** (all require an active couple):
- Moods — `POST /moods`, `GET /moods`, `GET /moods/unseen`, `PUT /moods/mark-seen`
- Notes — `POST /notes`, `GET /notes`, `GET /notes/unseen`, `PUT /notes/{id}/seen`
- Snaps — `POST /snaps` (multipart `image`, `duration`, `caption`), `GET /snaps`,
  `GET /snaps/unseen`, `GET /snaps/{id}/view`, `DELETE /snaps/{id}`,
  `GET /snaps/{id}/image` (streams the private-disk file)
- Doodles — `POST /doodles` (multipart `image`, `duration`, `stroke_count`), `GET /doodles`,
  `GET /doodles/unseen`, `GET /doodles/{id}/image`, `PUT /doodles/{id}/seen`
- Countdowns — `GET /countdowns`, `POST /countdowns`, `PUT /countdowns/{id}`,
  `DELETE /countdowns/{id}`, `GET /countdowns/active`
- Distance — `POST /distance`, `GET /distance`, `GET /distance/history`
- **Prompts** — `GET /prompts/daily`, `POST /prompts/{id}/answers`, `GET /prompts/history`
  (`GET /prompts/daily` also returns `streak_days`; rotation is driven by the
  `prompts:dispatch-daily` scheduler, see below)

**Widget / misc** — `GET /widget-state`, `GET /widget-state/version`; devices
(`POST /devices/register`, `DELETE /devices/unregister`, `GET /devices`); subscriptions
(`GET /subscriptions/current`); payments (`POST /payments/initiate`).

**Broadcasting auth** — `POST /api/broadcasting/auth` (Sanctum; registered by
`withBroadcasting` in `bootstrap/app.php`).

## Media storage

Snap/doodle images are stored on the `local` disk (root `storage/app/private/couples/{couple_id}/...`)
so they have **no public URL**. They are streamed back through authenticated endpoints
(`GET /snaps/{id}/image`, `GET /doodles/{id}/image`) after an active-couple scope check.
`tuko:cleanup-expired-snaps` prunes expired snaps and their files.

nginx must allow uploads larger than Android sends — set `client_max_body_size 10M;` **inside the
vhost `server {}` block** (the http-block default is too small and 413s), and keep
`upload_max_filesize`/`post_max_size` ≥ 10M in the PHP that the site actually runs under.

## Real-time (Reverb)

`CoupleUpdated` is broadcast on `private-couple.{couple_id}` after mood/note/snap/doodle/countdown/
prompt activity and invite accept. Channels are authorized in `routes/channels.php`.

The companion Android app connects over `wss://muthaka.duckdns.org` (nginx proxies `/app/` →
Reverb on 8080):

```nginx
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    client_max_body_size 0;
}
```

Server `.env` essentials (`REVERB_*` must match what the app uses):

```ini
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...        # the key the app connects with
REVERB_APP_SECRET=...     # used to sign private-channel auth
REVERB_HOST=139.59.84.75  # what clients resolve/connect to
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

## Scheduler, queue & cron

Scheduled commands (registered in `bootstrap/app.php`):

- `tuko:cleanup-expired-invites` — hourly, expires stale invite codes
- `tuko:cleanup-expired-snaps` — hourly, deletes expired snaps + media
- `prompts:dispatch-daily` — daily, marks the next active daily prompt as today's prompt so all
  couples see the same question and it advances each day (idempotent)

On the server, Laravel's scheduler must run every minute:

```cron
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

Queue workers and Reverb are kept alive by supervisor. See `deploy/supervisor/muthaka.conf`
(copy to `/etc/supervisor/conf.d/muthaka.conf`). `.github/workflows/deploy.yml` SSHs to the droplet
and restarts both after deploys.

## Testing

```bash
composer test        # PHPUnit suite
php artisan test --filter=Prompt
```

## License

Proprietary — Muthaka. Content on the server belongs to its users; see the app privacy policy.