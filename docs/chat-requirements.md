# Workspace Chat — Requirements & Setup Checklist

Everything the HRP chat needs to run correctly: real-time messaging, presence, unread badges,
attachments, ticket links and (Phase 2) voice/video calls. Work through it top to bottom on every
new machine or server.

> Quick test that it all works: log in as two different staff in two browsers, send a message from
> one and confirm the other sees it **instantly** (no refresh), with the bubble badge and toast.

---

## 1. Software

| Requirement | Version | Why |
|---|---|---|
| PHP | 8.2+ (8.4 in dev) | Laravel 12 |
| PHP extensions | `openssl`, `mbstring`, `fileinfo`, `gd` or `imagick`, `pcntl` | Encryption, MIME checks, image sizes, Reverb process signals |
| Composer packages | `laravel/reverb ^1.0` (plus the existing Livewire 3) | WebSocket server |
| Node | 20+ | Vite build |
| npm packages | `laravel-echo ^2.6`, `pusher-js ^8.6`, `emoji-picker-element ^1.29` | Browser WebSocket client and emoji picker |
| Database | MySQL 8 / MariaDB 10.6+ | Chat tables use UUIDs and JSON-free indexes |

```bash
composer install
npm install
npm run build        # or npm run dev while developing
```

---

## 2. Environment (`.env`)

### Broadcasting (required — without this nothing is real-time)

```dotenv
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=...          # any number
REVERB_APP_KEY=...         # random string
REVERB_APP_SECRET=...      # random string
REVERB_HOST="localhost"    # production: your public domain, e.g. hrp.example.com
REVERB_PORT=8080           # production behind nginx: 443
REVERB_SCHEME=http         # production: https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

- The `VITE_*` values are **baked into the JS at build time**. After changing any of them, run
  `npm run build` again (or restart `npm run dev`).
- ⚠️ `.env.example` still has `BROADCAST_CONNECTION=log` and no `REVERB_*` keys. Copy the block
  above into it so new installs don't silently lose real-time updates.
- Generate fresh keys per environment: `php artisan reverb:install` or any random strings.

### Other settings the chat relies on

```dotenv
APP_URL=https://hrp.example.com   # must match the URL users open
SESSION_DRIVER=database           # channel auth uses the logged-in session
QUEUE_CONNECTION=database         # department-channel sync job runs on the queue
FILESYSTEM_DISK=local             # attachments are encrypted on the private local disk
```

### Optional chat tuning (defaults shown, see `config/chat.php`)

```dotenv
CHAT_EDIT_WINDOW_MINUTES=15   # how long you can edit/delete your own message
CHAT_GROUP_MEMBER_CAP=250     # max members in a group
CHAT_RETENTION_YEARS=7        # chat:prune deletes older messages and files
```

### Phase 2 — voice/video calls (not built yet, configure when calls ship)

```dotenv
CHAT_STUN_URLS=stun:stun.l.google.com:19302
CHAT_TURN_URLS=turn:turn.example.com:3478?transport=udp,turns:turn.example.com:5349?transport=tcp
CHAT_TURN_SECRET=...          # same value as coturn's static-auth-secret
CHAT_TURN_TTL=3600            # lifetime of per-call TURN credentials (seconds)
```

After any `.env` change: `php artisan config:clear` (or `config:cache` in production).

---

## 3. Database

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder   # adds the Chat permissions
php artisan chat:sync-departments              # creates one channel per department and adds staff
```

Tables created: `chat_conversations`, `chat_members`, `chat_messages`, `chat_attachments`,
`chat_reactions`, plus `users.last_seen_at`.

### Data the chat depends on

- **Every person who should chat needs a user account linked to an employee** (`employees.user_id`).
  Staff without a login can't appear in search or department channels.
- **Employees need a department** (`employees.department_id`) to be placed in their department channel.
- **Only active employees** are added to department channels; termination removes access.
- **Department supervisors** become admins of their department channel (pin, moderate). Make sure
  supervisors are set on departments.

### Permissions

| Permission | Gives |
|---|---|
| *(none)* | Every logged-in employee can chat |
| `view-chat-analytics` | `/chat/analytics` page |
| `manage-chat` | Moderate and archive any room |

Assign these to the right roles in Access Control after seeding.

---

## 4. Processes that must be running

| Process | Command | If it's not running |
|---|---|---|
| Web server | `php artisan serve` / nginx + PHP-FPM | App down |
| **Reverb (WebSocket)** | `php artisan reverb:start` | No live messages, presence, typing, badges — you only see new messages after a refresh |
| Queue worker | `php artisan queue:work` | Department channels don't update after hires/transfers |
| Scheduler | `* * * * * php artisan schedule:run` (cron) | Nightly `chat:sync-departments` (01:00) and `chat:prune` (02:00) never run |
| Vite (dev only) | `npm run dev` | JS/CSS changes not loaded |

In development **`composer dev`** starts all of these together (server, queue, logs, vite, reverb).

---

## 5. File uploads (attachments up to 25 MB)

The app accepts files up to **25 MB** (PDF, Office, images, CSV, TXT), but PHP's own limits are lower:

| Setting | Current dev value | Required |
|---|---|---|
| `upload_max_filesize` | **2M** ⚠️ | `26M` |
| `post_max_size` | **8M** ⚠️ | `30M` |
| nginx `client_max_body_size` | — | `30M` |

Until these are raised, **any file over 2 MB will fail to upload**. Edit the `php.ini` used by the
web server (`php --ini` shows it; FPM has its own under `/etc/php/8.4/fpm/php.ini`) and restart PHP.

Also make sure `storage/app/private` is writable by the web user and **is backed up** — attachments
are encrypted with `APP_KEY`, so **never change `APP_KEY`** or every message body and file becomes unreadable.

---

## 6. Production server

### HTTPS (mandatory)

- Browsers only allow WebSockets from an HTTPS page over `wss://`, and **microphone/camera access
  (Phase 2) only works on HTTPS**. Get a certificate (e.g. Let's Encrypt).

### nginx: proxy WebSockets to Reverb

```nginx
server {
    listen 443 ssl;
    server_name hrp.example.com;
    client_max_body_size 30M;

    # ... existing PHP-FPM config ...

    location /app {
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_pass http://127.0.0.1:8080;
    }

    location /apps {
        proxy_pass http://127.0.0.1:8080;
    }
}
```

With this, set `REVERB_HOST=hrp.example.com`, `REVERB_PORT=443`, `REVERB_SCHEME=https`, but keep
Reverb itself listening locally (`REVERB_SERVER_HOST=0.0.0.0`, `REVERB_SERVER_PORT=8080`). Rebuild JS.

### Keep Reverb and the queue alive (supervisor)

```ini
[program:hrp-reverb]
command=php /var/www/hrp/artisan reverb:start
autostart=true
autorestart=true
user=www-data
minfds=10000
stdout_logfile=/var/log/hrp-reverb.log

[program:hrp-queue]
command=php /var/www/hrp/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/hrp-queue.log
```

After every deploy: `php artisan reverb:restart` and `php artisan queue:restart`.

### Scale notes

- Each open browser tab holds one WebSocket. Raise the open-file limit (`ulimit -n 10000`, `minfds`
  above) for more than ~1,000 concurrent users.
- With several app servers, enable Reverb's Redis scaling (`REVERB_SCALING_ENABLED=true` + Redis).

### Firewall

| Port | For |
|---|---|
| 443/tcp | HTTPS + WebSockets (via nginx) |
| 8080/tcp | Reverb — **local only**, don't expose |
| 3478/udp+tcp, 5349/tcp, 49152–65535/udp | TURN server (Phase 2 only) |

---

## 7. Phase 2 — voice & video calls (when built)

| Requirement | Detail |
|---|---|
| HTTPS | Mandatory for camera/mic |
| TURN server | **coturn** on a server with a public IP; needed for staff calling from outside the office network |
| coturn config | `use-auth-secret`, `static-auth-secret=<CHAT_TURN_SECRET>`, `realm=hrp.example.com`, TLS cert for `turns:` |
| Bandwidth | ~50 kbps per audio call, 0.5–2.5 Mbps per video call (relayed calls go through the TURN server) |
| Browser permissions | Users must allow microphone/camera; pop-ups allowed for the call window |
| Group calls (Phase 3) | Need a media server (LiveKit); peer-to-peer doesn't scale past ~4 people |

---

## 8. Browser & user requirements

- Current Chrome, Edge, Firefox or Safari (WebSocket + ES modules). IE is not supported.
- JavaScript enabled; corporate proxies must allow WebSocket upgrade on 443.
- Desktop notifications and sound are opt-in — users accept the browser prompt.
- Ad-blockers/privacy extensions sometimes block WebSockets; whitelist the HRP domain.

---

## 9. Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| New messages only appear after refresh | Reverb not running, or `BROADCAST_CONNECTION` not `reverb` | Start Reverb; check `.env`; `php artisan config:clear` |
| Browser console: WebSocket connection failed | Wrong `VITE_REVERB_*` baked in, or nginx not proxying `/app` | Fix `.env`, `npm run build`, check nginx |
| Browser console: 403 on `/broadcasting/auth` | User isn't a member of that room, or session expired | Re-login; check membership |
| Everyone shows offline | Presence channel can't connect (same as above) | Fix the WebSocket connection |
| `/chat` is empty / no department channels | Sync never ran or employees have no `user_id`/department | `php artisan chat:sync-departments`; fix employee records |
| Transfers don't move people between channels | Queue worker not running | Start `queue:work` |
| Upload fails for bigger files | PHP/nginx upload limits | Section 5 |
| Messages show as unreadable/decrypt errors | `APP_KEY` changed | Restore the original key |
| Ticket hover card says "Restricted" | Viewer lacks access to that ticket | Expected behaviour |

Useful checks:

```bash
php artisan about | grep -i broadcast     # should say reverb
php artisan reverb:start --debug          # shows every connection and message
php artisan route:list --path=chat        # chat routes registered
php artisan schedule:list | grep chat     # nightly jobs scheduled
php artisan test tests/Feature/Chat       # automated chat tests
```

---

## 10. Go-live checklist

- [ ] `composer install`, `npm install`, `npm run build`
- [ ] `.env`: `BROADCAST_CONNECTION=reverb`, `REVERB_*` and `VITE_REVERB_*` set for the real domain
- [ ] `php artisan migrate`, `PermissionSeeder`, `chat:sync-departments`
- [ ] Employees linked to user accounts and departments; department supervisors set
- [ ] Chat permissions assigned to roles
- [ ] PHP `upload_max_filesize=26M`, `post_max_size=30M`, nginx `client_max_body_size 30M`
- [ ] HTTPS certificate installed
- [ ] nginx proxies `/app` and `/apps` to Reverb
- [ ] supervisor runs `reverb:start` and `queue:work`; cron runs `schedule:run`
- [ ] `storage/app/private` writable and backed up; `APP_KEY` backed up and never rotated
- [ ] Two-browser test: instant message, badge, toast, typing, "Seen" ticks, presence, file upload
- [ ] (Phase 2) coturn running, `CHAT_TURN_*` set, test a call between two different networks
