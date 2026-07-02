# Local WordPress Docker Development Environment

**Date:** 2026-07-02  
**Status:** Approved  
**Branch:** `development`

## Goal

Enable local WordPress development using Docker on the `development` branch, while keeping staging and production on classic shared hosting (no Docker on server). Developers work on themes, plugins, and other `wp-content` changes locally, then promote code via git merges and manual FTP deploy.

## Requirements Summary

| Area | Decision |
|---|---|
| Repository | Full WordPress in git (core + `wp-content`); Docker bind-mounts entire project |
| Local environment | Docker with PHP 8.3 |
| Staging / production | Classic shared hosting, no Docker |
| Deploy method | Manual FTP/SFTP, `wp-content` only |
| Local database | Clean install initially; optional staging import later |
| Git workflow | `development` → `staging` → `main`; deploy from matching branch |

## Architecture

```
┌─────────────────────────────────────────────────┐
│  LOCAL (branch: development)                    │
│  docker compose up                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────────┐  │
│  │ WordPress│  │  MySQL   │  │ phpMyAdmin   │  │
│  │ PHP 8.3  │──│  8.0     │  │ (optional)   │  │
│  └────┬─────┘  └──────────┘  └──────────────┘  │
│       │ bind mount: ./ → /var/www/html          │
└───────┼─────────────────────────────────────────┘
        │ git merge + FTP (wp-content only)
        ▼
┌──────────────────┐     ┌──────────────────┐
│ STAGING          │     │ PRODUCTION       │
│ branch: staging  │────▶│ branch: main     │
│ shared hosting   │     │ shared hosting   │
└──────────────────┘     └──────────────────┘
```

### Key Principles

1. **Full repo in git** — WordPress core version is consistent across developers and tracked in version control.
2. **Deploy only `wp-content`** — Core on the server is managed separately (hosting panel or deliberate manual updates).
3. **Environment-specific config stays out of git** — `wp-config.php` and `.env` are never committed.
4. **Git is the source of truth** — Code flows `development` → `staging` → `main`; FTP uploads come from the corresponding branch.

## Docker Configuration

### Services

| Service | Image | Purpose |
|---|---|---|
| `wordpress` | `wordpress:8.3-apache` | Application, port `8080:80` |
| `db` | `mysql:8.0` | Local database |
| `phpmyadmin` | `phpmyadmin:latest` | DB admin UI, port `8081:80` (optional) |
| `wpcli` | `wordpress:cli-php8.3` | WP-CLI for scripts and future DB import |

### Volumes

```yaml
volumes:
  - .:/var/www/html
  - db_data:/var/lib/mysql
```

- **Code** — bind-mounted from repo; edits are immediately visible in the container.
- **Database** — named volume `db_data` persists across `docker compose down` (cleared only with `docker compose down -v`).

### Environment Variables

`.env.example` (committed):

```env
WORDPRESS_DB_HOST=db
WORDPRESS_DB_NAME=wordpress
WORDPRESS_DB_USER=wordpress
WORDPRESS_DB_PASSWORD=changeme
MYSQL_ROOT_PASSWORD=changeme
WORDPRESS_TABLE_PREFIX=wp_

WP_HOME=http://localhost:8080
WP_SITEURL=http://localhost:8080
```

`.env` is gitignored. Each developer copies `.env.example` → `.env`.

### wp-config Strategy

`wp-config.php` is gitignored. A committed template `wp-config-docker.php` will:

1. Read database credentials from Docker environment variables via `getenv()`.
2. Set `WP_DEBUG=true` and `WP_DEBUG_LOG=true` for local development.
3. Require `wp-settings.php` at the end.

`scripts/setup.sh` copies `wp-config-docker.php` → `wp-config.php` on first run if `wp-config.php` does not exist.

Production and staging `wp-config.php` files live only on their respective servers (or in a local notes file outside the repo).

### Developer Commands (Makefile)

| Command | Action |
|---|---|
| `make up` | `docker compose up -d` |
| `make down` | `docker compose down` |
| `make logs` | Follow WordPress container logs |
| `make shell` | Bash into WordPress container |
| `make wp ARGS="..."` | Run WP-CLI command |
| `make reset` | `docker compose down -v` (destroys local DB) |

### First-Time Setup

```bash
cp .env.example .env
make up
# Open http://localhost:8080 → WordPress install wizard
# Or: make wp ARGS="core install --url=http://localhost:8080 ..."
```

## Git + FTP Deploy Workflow

### Branch Flow

```
feature/* ──merge──▶ development ──merge──▶ staging ──merge──▶ main
                     (local dev)            (staging FTP)     (prod FTP)
```

### FTP Upload Rules

**Always upload when changed:**

```
wp-content/themes/<custom-theme>/
wp-content/plugins/<custom-plugin>/
wp-content/mu-plugins/
```

**Never upload via this workflow:**

```
wp-config.php
.env
wp-content/uploads/
wp-content/cache/
wp-content/upgrade/
wp-admin/
wp-includes/
```

**Third-party plugins** (`litespeed-cache`, `google-site-kit`, etc.):

- Upload the plugin folder only when its files changed locally.
- When installing a new plugin locally, also install it on the server (FTP or hosting panel) to keep versions in sync.

### Staging Deploy Checklist

```bash
git checkout development && git status          # clean working tree
git checkout staging && git merge development
git diff main -- wp-content/                  # review changes
# FTP upload changed wp-content folders to staging server
git push origin staging
```

### Production Deploy Checklist

```bash
git checkout main && git merge staging
git diff HEAD~1 -- wp-content/                # review changes
# FTP upload changed wp-content folders to production server
git push origin main
```

## Future: Staging Database Import

Infrastructure is prepared but not implemented in the initial setup.

```bash
# 1. Export from staging (hosting phpMyAdmin) → scripts/dumps/staging.sql
# 2. Import locally:
make wp ARGS="db import scripts/dumps/staging.sql"
make wp ARGS="search-replace 'https://staging.example.com' 'http://localhost:8080' --all-tables"
# 3. Optionally download wp-content/uploads/ from staging via FTP
```

`scripts/dumps/*.sql` and `scripts/dumps/*.sql.gz` are gitignored.

## Repository File Structure (New Files)

```
rozgadana-jana/
├── docker-compose.yml
├── .env.example
├── Makefile
├── wp-config-docker.php
├── scripts/
│   ├── setup.sh
│   └── dumps/
│       └── .gitkeep
└── docs/
    ├── DEPLOY.md
    └── LOCAL-DEV.md
```

Existing WordPress core and `wp-content/` remain unchanged.

## .gitignore Additions

```gitignore
# Docker / local dev
.env
wp-config.php
scripts/dumps/*.sql
scripts/dumps/*.sql.gz
.docker/
```

## Verification Checklist (Post-Implementation)

1. `make up` — containers start without errors.
2. `http://localhost:8080` — WordPress install wizard or existing site loads.
3. Edit a file in `wp-content/themes/lowfi/` — change visible after browser refresh.
4. `make wp ARGS="plugin list"` — WP-CLI works.
5. `make down` + `make up` — database persists (`db_data` volume).
6. `git status` — `.env` and `wp-config.php` are not tracked.

## Out of Scope (Initial Implementation)

- CI/CD or automated FTP deploy
- Staging database import (infrastructure only)
- Node/npm build step for themes
- Multisite, Redis, reverse proxy
- Server-side staging/production configuration changes

## Approach Considered

**Selected:** `docker-compose` with official WordPress and MySQL images.

**Rejected:**
- **DDEV** — extra dependency, less aligned with full core-in-repo + manual FTP workflow.
- **Custom Dockerfile (nginx + php-fpm)** — unnecessary complexity for shared hosting deploy of `wp-content` only.
