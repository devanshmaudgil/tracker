# RADiiX INFINITEii Tracker — Deployment Guide (Bluehost)

This document covers the technology stack, dependencies, architecture, and step-by-step installation for deploying the **RADiiX INFINITEii Tracker** to **Bluehost** production hosting.

---

## Table of contents

1. [Application overview](#1-application-overview)
2. [Technology stack](#2-technology-stack)
3. [Dependencies](#3-dependencies)
4. [Architecture & features](#4-architecture--features)
5. [Bluehost hosting requirements](#5-bluehost-hosting-requirements)
6. [Pre-deployment checklist](#6-pre-deployment-checklist)
7. [Installation guide (Bluehost)](#7-installation-guide-bluehost)
8. [Environment configuration](#8-environment-configuration)
9. [AI setup for production](#9-ai-setup-for-production)
10. [Post-deployment steps](#10-post-deployment-steps)
11. [Security checklist](#11-security-checklist)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. Application overview

| Property | Value |
|----------|--------|
| **Product name** | RADiiX INFINITEii Tracker |
| **Type** | Internal recruiter / staffing workspace (web app) |
| **Framework** | Laravel 12 (PHP) |
| **Target host** | **Bluehost** (shared, VPS, or dedicated) |
| **Primary URL (example)** | `https://yourdomain.com` |

### Core modules

| Module | Route | Description |
|--------|-------|-------------|
| Login | `/login` | Staff authentication |
| Recruiter Workspace | `/tracker/info` | Demand tracker, pipeline, tabs, import/export |
| Clients | `/clients/info` | Client management |
| Regions | `/region` | Region / city management |
| Candidates | `/candidate/info` | Candidate database |
| Resume Fit Analysis | `/resume-analysis` | AI-powered JD vs resume matching |
| Find Candidates | `/candidate-search` | External candidate search (SerpAPI) |
| Staff users | `/users` | User administration |
| Months | `/months` | Month reference data |

---

## 2. Technology stack

### Backend

| Layer | Technology | Version |
|-------|------------|---------|
| Language | PHP | **8.2+** (8.3 recommended) |
| Framework | Laravel | **12.x** |
| ORM | Eloquent | Included with Laravel |
| Auth | Session-based (custom `user_login` + staff users) | — |
| Excel import/export | PhpSpreadsheet | ^5.4 |
| PDF text extraction | smalot/pdfparser | ^2.12 |
| Optional PDF fallback | Python 3 + `pypdf` | Optional |
| Queue (optional) | Database driver | Laravel queues |
| Cache / sessions | Database | MySQL tables |

### Frontend

| Layer | Technology | Notes |
|-------|------------|-------|
| Templates | Blade (`.blade.php`) | Server-rendered UI |
| CSS | Inline + page-scoped styles | Teal/gold RADiiX branding |
| Fonts | Google Fonts (Outfit) | Loaded via CDN |
| JavaScript | jQuery 3.6, Select2 | Loaded via CDN |
| Build tool | Vite + Tailwind 4 | **Optional** — main app pages do not require Vite build |

### Database

| Environment | Engine |
|-------------|--------|
| Local dev | SQLite or MySQL |
| **Bluehost production** | **MySQL / MariaDB** (included with Bluehost) |

### External services (optional / production)

| Service | Purpose | Required? |
|---------|---------|-----------|
| **Google Gemini API** | Resume AI analysis (recommended on Bluehost) | Yes for AI on shared hosting |
| **Ollama** | Local AI (dev / VPS only) | No on Bluehost shared |
| **SerpAPI** | Find Candidates search | Optional |
| **AWS S3** | File storage (if configured) | Optional |

---

## 3. Dependencies

### 3.1 PHP extensions (required)

Enable these in **Bluehost → Advanced → MultiPHP INI Editor** or **Select PHP Version → Extensions**:

| Extension | Purpose |
|-----------|---------|
| `openssl` | HTTPS, encryption |
| `pdo` + `pdo_mysql` | MySQL database |
| `mbstring` | String handling |
| `tokenizer` | Laravel |
| `xml` / `dom` | XML/Excel |
| `ctype` | Laravel |
| `json` | API / config |
| `fileinfo` | File uploads |
| `zip` | PhpSpreadsheet |
| `curl` | HTTP client (AI, SerpAPI) |
| `bcmath` | Spreadsheet math |
| `gd` | Recommended (PDF/image; may be optional with platform flags) |

### 3.2 Composer packages (production)

From `composer.json`:

```json
"php": "^8.2",
"laravel/framework": "^12.0",
"laravel/tinker": "^2.10.1",
"league/flysystem-aws-s3-v3": "3.0",
"phpoffice/phpspreadsheet": "^5.4",
"smalot/pdfparser": "^2.12"
```

Install on server (SSH) or locally before upload:

```bash
composer install --no-dev --optimize-autoloader
```

### 3.3 Node.js (optional)

Only needed if you rebuild frontend assets. The live app uses Blade + CDN assets.

```bash
npm install
npm run build   # optional
```

### 3.4 Python (optional — secured PDFs)

For Dice-style locked PDFs, the app can fall back to:

```bash
pip install pypdf
```

Script: `scripts/extract_pdf_text.py`

> **Bluehost shared hosting** often does **not** allow Python CLI. Most PDFs work via `smalot/pdfparser` alone.

### 3.5 Server tools

| Tool | Bluehost shared | Bluehost VPS |
|------|-----------------|--------------|
| SSH | Plan-dependent | Yes |
| Composer | Via SSH or deploy locally | Yes |
| Cron | Yes (cPanel) | Yes |
| MySQL | Yes | Yes |
| Custom long PHP timeout | Limited (see §5) | Configurable |

---

## 4. Architecture & features

### 4.1 High-level diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     Browser (Recruiter)                      │
└───────────────────────────┬─────────────────────────────────┘
                            │ HTTPS
┌───────────────────────────▼─────────────────────────────────┐
│              Bluehost — public/ (document root)                │
│              Laravel 12 Application                            │
├─────────────────────────────────────────────────────────────┤
│  Controllers: Tracker, ResumeAnalysis, Candidates, Clients…   │
│  Services: ResumeAnalysisService, ResumeMatchScorer, AiManager │
└───────┬─────────────────┬──────────────────┬────────────────┘
        │                 │                  │
        ▼                 ▼                  ▼
   MySQL DB          Local storage      External APIs
 (tracker, users,   (PDF uploads,      (Gemini / SerpAPI)
  sessions, cache)    logs, exports)
```

### 4.2 Resume Fit Analysis pipeline

1. User uploads **PDF resume** + pastes **job description**
2. **PdfTextExtractor** reads PDF text (PHP parser, optional Python fallback)
3. **ResumeMatchScorer** calculates match % from must-haves / skills (deterministic)
4. **AI narrative** (Gemini or Ollama) writes summary, strengths, gaps
5. **Streamed progress UI** shows step-by-step loader to the browser
6. Report displayed with **percentage ring**, recommendation, numbered lists

### 4.3 Design system (UI)

| Token | Value | Usage |
|-------|-------|--------|
| Teal deep | `#0a2d29` | Sidebar, headings |
| Teal matte | `#0f3d37` | Gradients |
| Gold | `#f1cd86` | Accents, borders |
| Font | **Outfit** (Google Fonts) | Global typography |
| Logo | `public/logo.png` | Sidebar, login, resume analysis watermark |
| Favicon | `public/favicon.webp` | Browser tab |

Key views:

- `resources/views/layouts/app.blade.php` — main shell (sidebar, topbar)
- `resources/views/auth/login.blade.php` — branded login
- `resources/views/tracker/index.blade.php` — Recruiter Workspace
- `resources/views/resume/analysis.blade.php` — Resume Fit Analysis

---

## 5. Bluehost hosting requirements

### Recommended plan

| Plan | Suitability |
|------|-------------|
| **Shared Plus / Choice Plus** | Possible with Gemini AI; watch PHP time limits |
| **VPS / Dedicated** | **Recommended** for AI workloads, SSH, longer timeouts |

### Critical Bluehost limitations

| Topic | Detail |
|-------|--------|
| **Ollama** | **Cannot run on Bluehost shared** — no local GPU/CPU model server. Use **Gemini** in production. |
| **PHP `max_execution_time`** | Resume analysis may need **120–300 seconds**. Increase in MultiPHP INI or use VPS. |
| **Document root** | Must point to Laravel **`public/`** folder, not project root. |
| **`.env` file** | Must live **outside** `public_html` or be protected; never commit to Git. |

### Minimum server specs (production)

- PHP **8.2+**
- MySQL **5.7+** or MariaDB **10.3+**
- **512 MB+** PHP memory (`memory_limit=256M` minimum, `512M` preferred)
- **SSL certificate** (Bluehost free Let's Encrypt)

---

## 6. Pre-deployment checklist

- [ ] Bluehost domain + SSL enabled (`https://`)
- [ ] MySQL database + user created in cPanel
- [ ] PHP version set to **8.2** or **8.3**
- [ ] Required PHP extensions enabled
- [ ] **Gemini API key** obtained (for Resume Analysis on Bluehost)
- [ ] **SerpAPI key** (optional, for Find Candidates)
- [ ] Git repo or ZIP of application ready
- [ ] `.env` prepared for production (never upload `.env` to public repos)
- [ ] `APP_DEBUG=false` for production
- [ ] Strong staff passwords configured (not default seed passwords)

---

## 7. Installation guide (Bluehost)

### Option A — Deploy via SSH (recommended if available)

#### Step 1: Upload code

```bash
# On your local machine
git clone <your-repo-url>
cd Radiix_infiniteii_tracker
composer install --no-dev --optimize-autoloader
```

Upload the project to Bluehost (e.g. `/home/username/infiniteii_tracker/`) using SFTP, Git, or File Manager.  
**Do not** put the full Laravel app inside `public_html` unless you restructure the docroot.

#### Step 2: Point document root to `public/`

In Bluehost **cPanel → Domains → your domain → Document Root**, set:

```
/home/username/infiniteii_tracker/public
```

Alternatively, if you must use `public_html`, move contents of `public/` into `public_html/` and adjust `index.php` paths (not recommended — prefer docroot method).

#### Step 3: Create MySQL database

1. cPanel → **MySQL Databases**
2. Create database: e.g. `username_infiniteii`
3. Create user with strong password
4. Add user to database with **ALL PRIVILEGES**

#### Step 4: Configure environment

```bash
cd /home/username/infiniteii_tracker
cp .env.example .env
nano .env   # or edit via File Manager
```

See [§8 Environment configuration](#8-environment-configuration).

Generate app key:

```bash
php artisan key:generate
```

#### Step 5: Run migrations

```bash
php artisan migrate --force
```

Optional seed (demo data — **change passwords after**):

```bash
php artisan db:seed --force
```

#### Step 6: Storage link & permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

Ensure web server user can write to `storage/` and `bootstrap/cache/`.

#### Step 7: Optimize for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 8: Verify

Visit `https://yourdomain.com/login` and sign in.

---

### Option B — Shared hosting without SSH

1. Run `composer install --no-dev --optimize-autoloader` **locally**
2. Upload entire project via FTP to `/home/username/infiniteii_tracker/`
3. Set document root to `public/` (Bluehost domain settings)
4. Create `.env` on server via File Manager (copy from `.env.example`)
5. Upload a pre-generated `APP_KEY` or use a one-time PHP script to run `key:generate` (remove script after)
6. Import database: run migrations locally against production DB **or** use a temporary deploy script
7. Set folder permissions: `storage/` and `bootstrap/cache/` → **775**

> Without SSH, Laravel migrations are harder. Prefer **Option A** or Bluehost **VPS**.

---

### Apache / LiteSpeed rewrite (`.htaccess`)

The file `public/.htaccess` ships with Laravel. Ensure **mod_rewrite** is enabled and AllowOverride is permitted (default on Bluehost).

If the site returns 404 on all routes except `/`, confirm document root is `public/` and `.htaccess` exists.

---

## 8. Environment configuration

Production `.env` example for Bluehost:

```env
APP_NAME="RADiiX INFINITEii"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_infiniteii
DB_USERNAME=username_dbuser
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# Production AI — use Gemini on Bluehost (Ollama will NOT work on shared hosting)
AI_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.5-flash
GEMINI_TIMEOUT=90

# Leave Ollama unset on Bluehost shared
# OLLAMA_BASE_URL=http://127.0.0.1:11434

# Optional: Find Candidates module
SERPAPI_API_KEY=your_serpapi_key
```

### Environment variables reference

| Variable | Description |
|----------|-------------|
| `APP_URL` | Full production URL with `https://` |
| `APP_DEBUG` | **Must be `false`** in production |
| `DB_*` | Bluehost MySQL credentials |
| `SESSION_DRIVER` | `database` (requires `sessions` table — Laravel default migration) |
| `CACHE_STORE` | `database` (requires `cache` table) |
| `AI_PROVIDER` | `gemini` for Bluehost production |
| `GEMINI_API_KEY` | Google AI Studio API key |
| `OLLAMA_*` | Local dev only (VPS with Ollama installed) |
| `SERPAPI_API_KEY` | Optional candidate search |

After changing `.env`:

```bash
php artisan config:clear
php artisan config:cache
```

---

## 9. AI setup for production

### Recommended: Google Gemini (Bluehost)

1. Go to [Google AI Studio](https://aistudio.google.com/apikey)
2. Create an API key
3. Set in `.env`:

```env
AI_PROVIDER=gemini
GEMINI_API_KEY=your_key_here
```

4. The Resume Analysis page shows **RADiiX Intelligence · Ready** when the provider responds

### Not recommended on Bluehost shared: Ollama

Ollama runs as a **local daemon** on your machine. Bluehost shared servers cannot host it. Use Ollama only for:

- Local development
- A separate VPS where Ollama is installed (then set `OLLAMA_BASE_URL` to that server’s URL and ensure firewall/security)

### Resume analysis timeouts

Add or increase in Bluehost **MultiPHP INI Editor**:

```ini
max_execution_time = 300
memory_limit = 512M
upload_max_filesize = 8M
post_max_size = 10M
```

Resume analysis calls `set_time_limit(300)` in the controller; server INI must allow it.

---

## 10. Post-deployment steps

### 10.1 Create admin user

If not using seeders, create a staff login via database or seeder:

```bash
php artisan db:seed --class=UserLoginSeeder --force
```

**Change default passwords immediately** after first login.

### 10.2 Cron job (optional — queues)

If using database queues, add cPanel cron:

```cron
* * * * * cd /home/username/infiniteii_tracker && php artisan schedule:run >> /dev/null 2>&1
```

For queue worker (VPS only, long-running):

```cron
* * * * * cd /home/username/infiniteii_tracker && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### 10.3 Verify modules

| Test | Expected |
|------|----------|
| Login | Branded page, redirect to Recruiter Workspace |
| Tracker | Demands load, tabs work, month picker works |
| Import/export Excel | XLSX upload/download |
| Resume Analysis | Upload PDF + JD → streamed progress → fit report |
| Find Candidates | Returns results if `SERPAPI_API_KEY` set |

### 10.4 Files to exclude from public access

Ensure these are **not** web-accessible (docroot = `public/` only):

- `.env`
- `vendor/`
- `storage/` (except via symlink `public/storage`)
- `database/`
- `composer.json` / `composer.lock`

---

## 11. Security checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] HTTPS enforced (Bluehost SSL + `APP_URL=https://...`)
- [ ] Strong MySQL password
- [ ] Unique `APP_KEY` per environment
- [ ] API keys only in `.env`, never in Git
- [ ] Default seed passwords changed
- [ ] File upload limit: resume PDF max **5 MB** (app validation)
- [ ] Restrict `/users` to trusted staff only
- [ ] Regular Bluehost / Laravel security updates

---

## 12. Troubleshooting

### 500 Internal Server Error

- Check `storage/logs/laravel.log`
- Verify `storage/` and `bootstrap/cache/` permissions (775)
- Run `php artisan config:clear`
- Confirm PHP 8.2+ and required extensions

### 404 on all routes

- Document root must be `public/`
- Confirm `public/.htaccess` exists
- Enable mod_rewrite

### Resume Analysis: “Analysis engine unavailable”

- **Bluehost:** set `AI_PROVIDER=gemini` and valid `GEMINI_API_KEY`
- Ollama will not work on shared hosting
- Check `storage/logs/laravel.log` for API errors

### Resume Analysis: timeout

- Increase `max_execution_time` in Bluehost PHP settings
- Consider VPS plan or shorter JD/resume size
- Gemini is typically faster than local Ollama

### PDF text extraction failed

- Re-save PDF without password protection
- On VPS: install `pip install pypdf` for secured PDF fallback
- Ensure `fileinfo` extension enabled

### Blank page after deploy

- Temporarily set `APP_DEBUG=true` to see error (disable after fixing)
- Run `composer install --no-dev`
- Verify `vendor/autoload.php` exists

### Progress bar stuck (Resume Analysis)

- Production uses **streamed NDJSON** on the same POST request
- Ensure proxy/CDN does not buffer responses
- Disable Cloudflare “Rocket Loader” or similar for `/resume-analysis`

### Database connection refused

- Use `DB_HOST=localhost` on Bluehost (not `127.0.0.1` in some cases — try both)
- Confirm database user privileges in cPanel

---

## Quick reference commands

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Application setup
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Production optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear caches (after .env changes)
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## Support contacts

| Item | Location |
|------|----------|
| Application logs | `storage/logs/laravel.log` |
| Bluehost support | Bluehost cPanel / live chat |
| Gemini API | [Google AI Studio](https://aistudio.google.com/) |
| SerpAPI | [serpapi.com](https://serpapi.com/) |

---

**Document version:** 1.0  
**Application:** RADiiX INFINITEii Tracker  
**Target host:** Bluehost  
**Last updated:** June 2026
