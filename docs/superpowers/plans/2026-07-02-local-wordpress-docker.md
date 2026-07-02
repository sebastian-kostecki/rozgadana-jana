# Local WordPress Docker Development Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a Docker-based local WordPress development environment on the `development` branch, with Makefile shortcuts, wp-config template, and deploy/dev documentation.

**Architecture:** Official `wordpress:8.3-apache` and `mysql:8.0` images with the full repo bind-mounted to `/var/www/html`. Environment-specific secrets live in `.env` and `wp-config.php` (both gitignored). WP-CLI runs via a separate `wpcli` service for future DB import workflows.

**Tech Stack:** Docker Compose, WordPress 8.3, MySQL 8.0, phpMyAdmin, WP-CLI, Make, Bash

**Spec:** `docs/superpowers/specs/2026-07-02-local-wordpress-docker-design.md`

---

## File Map

| File | Responsibility |
|---|---|
| `docker-compose.yml` | Defines wordpress, db, phpmyadmin, wpcli services |
| `.env.example` | Committed template for local Docker credentials |
| `wp-config-docker.php` | Committed wp-config template reading Docker env vars |
| `Makefile` | Developer shortcuts (`up`, `down`, `wp`, etc.) |
| `scripts/setup.sh` | First-run bootstrap: `.env` and `wp-config.php` |
| `scripts/dumps/.gitkeep` | Keeps dumps directory in git without SQL files |
| `.gitignore` | Ignore local secrets and SQL dumps; allow `.env.example` |
| `docs/LOCAL-DEV.md` | How to start local environment from scratch |
| `docs/DEPLOY.md` | Git branch flow + FTP upload rules |

---

### Task 1: Fix `.gitignore` for Docker files

**Files:**
- Modify: `.gitignore`

The existing `.env.*` pattern incorrectly ignores `.env.example`. Add explicit Docker/dump rules.

- [ ] **Step 1: Update `.gitignore`**

Append after line 3 (after `.env.*`):

```gitignore
!.env.example

# Docker local dev
scripts/dumps/*.sql
scripts/dumps/*.sql.gz
.docker/
```

Note: `wp-config.php` and `.env` are already ignored (lines 1–2). Global `*.sql` (line 15) also covers dumps, but explicit `scripts/dumps/` rules make intent clear.

- [ ] **Step 2: Verify `.env.example` is not ignored**

Run: `git check-ignore -v .env.example || echo "NOT IGNORED"`
Expected: `NOT IGNORED` (exit 0, prints "NOT IGNORED")

- [ ] **Step 3: Commit**

```bash
git add .gitignore
git commit -m "fix: allow .env.example and ignore Docker dump files"
```

---

### Task 2: Add `.env.example`

**Files:**
- Create: `.env.example`

- [ ] **Step 1: Create `.env.example`**

```env
# Docker Compose environment (copy to .env — never commit .env)
COMPOSE_PROJECT_NAME=rozgadana-jana

WORDPRESS_DB_HOST=db
WORDPRESS_DB_NAME=wordpress
WORDPRESS_DB_USER=wordpress
WORDPRESS_DB_PASSWORD=changeme
MYSQL_ROOT_PASSWORD=changeme_root
WORDPRESS_TABLE_PREFIX=wp_

WP_HOME=http://localhost:8080
WP_SITEURL=http://localhost:8080
```

- [ ] **Step 2: Verify file is trackable**

Run: `git check-ignore -v .env.example || echo "NOT IGNORED"`
Expected: `NOT IGNORED`

- [ ] **Step 3: Commit**

```bash
git add .env.example
git commit -m "feat: add Docker environment variable template"
```

---

### Task 3: Add `wp-config-docker.php`

**Files:**
- Create: `wp-config-docker.php`

- [ ] **Step 1: Create `wp-config-docker.php`**

```php
<?php
/**
 * Local Docker wp-config template.
 * Copied to wp-config.php by scripts/setup.sh (wp-config.php is gitignored).
 */

define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) ?: 'wordpress' );
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) ?: 'wordpress' );
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) ?: '' );
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ?: 'db' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = getenv( 'WORDPRESS_TABLE_PREFIX' ) ?: 'wp_';

define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

$wp_home    = getenv( 'WP_HOME' ) ?: 'http://localhost:8080';
$wp_siteurl = getenv( 'WP_SITEURL' ) ?: 'http://localhost:8080';
define( 'WP_HOME', $wp_home );
define( 'WP_SITEURL', $wp_siteurl );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
```

- [ ] **Step 2: Commit**

```bash
git add wp-config-docker.php
git commit -m "feat: add Docker wp-config template"
```

---

### Task 4: Add `docker-compose.yml`

**Files:**
- Create: `docker-compose.yml`

- [ ] **Step 1: Create `docker-compose.yml`**

```yaml
services:
  wordpress:
    image: wordpress:8.3-apache
    ports:
      - "8080:80"
    env_file:
      - .env
    environment:
      WORDPRESS_DB_HOST: ${WORDPRESS_DB_HOST:-db}
      WORDPRESS_DB_NAME: ${WORDPRESS_DB_NAME:-wordpress}
      WORDPRESS_DB_USER: ${WORDPRESS_DB_USER:-wordpress}
      WORDPRESS_DB_PASSWORD: ${WORDPRESS_DB_PASSWORD:-changeme}
      WORDPRESS_TABLE_PREFIX: ${WORDPRESS_TABLE_PREFIX:-wp_}
      WP_HOME: ${WP_HOME:-http://localhost:8080}
      WP_SITEURL: ${WP_SITEURL:-http://localhost:8080}
    volumes:
      - .:/var/www/html
    depends_on:
      db:
        condition: service_healthy
    restart: unless-stopped

  db:
    image: mysql:8.0
    env_file:
      - .env
    environment:
      MYSQL_DATABASE: ${WORDPRESS_DB_NAME:-wordpress}
      MYSQL_USER: ${WORDPRESS_DB_USER:-wordpress}
      MYSQL_PASSWORD: ${WORDPRESS_DB_PASSWORD:-changeme}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD:-changeme_root}
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 5s
      timeout: 5s
      retries: 10
    restart: unless-stopped

  phpmyadmin:
    image: phpmyadmin:latest
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: ${WORDPRESS_DB_USER:-wordpress}
      PMA_PASSWORD: ${WORDPRESS_DB_PASSWORD:-changeme}
    depends_on:
      db:
        condition: service_healthy
    restart: unless-stopped

  wpcli:
    image: wordpress:cli-php8.3
    user: "33:33"
    env_file:
      - .env
    environment:
      WORDPRESS_DB_HOST: ${WORDPRESS_DB_HOST:-db}
      WORDPRESS_DB_NAME: ${WORDPRESS_DB_NAME:-wordpress}
      WORDPRESS_DB_USER: ${WORDPRESS_DB_USER:-wordpress}
      WORDPRESS_DB_PASSWORD: ${WORDPRESS_DB_PASSWORD:-changeme}
      WORDPRESS_TABLE_PREFIX: ${WORDPRESS_TABLE_PREFIX:-wp_}
    volumes:
      - .:/var/www/html
    depends_on:
      db:
        condition: service_healthy
      wordpress:
        condition: service_started

volumes:
  db_data:
```

- [ ] **Step 2: Validate compose file syntax**

Run: `docker compose config --quiet`
Expected: exit 0, no output (requires `.env` to exist — run `cp .env.example .env` first if missing)

- [ ] **Step 3: Commit**

```bash
git add docker-compose.yml
git commit -m "feat: add Docker Compose stack for local WordPress dev"
```

---

### Task 5: Add `scripts/setup.sh` and dumps directory

**Files:**
- Create: `scripts/setup.sh`
- Create: `scripts/dumps/.gitkeep`

- [ ] **Step 1: Create `scripts/dumps/.gitkeep`**

Empty file (0 bytes).

- [ ] **Step 2: Create `scripts/setup.sh`**

```bash
#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

if [[ ! -f wp-config.php ]]; then
  cp wp-config-docker.php wp-config.php
  echo "Created wp-config.php from wp-config-docker.php"
fi

echo "Setup complete. Run: make up"
```

- [ ] **Step 3: Make script executable**

Run: `chmod +x scripts/setup.sh`

- [ ] **Step 4: Commit**

```bash
git add scripts/setup.sh scripts/dumps/.gitkeep
git commit -m "feat: add local dev bootstrap script and dumps directory"
```

---

### Task 6: Add `Makefile`

**Files:**
- Create: `Makefile`

- [ ] **Step 1: Create `Makefile`**

```makefile
.PHONY: setup up down logs shell wp reset config

setup:
	./scripts/setup.sh

up: setup
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f wordpress

shell:
	docker compose exec wordpress bash

wp:
	docker compose run --rm wpcli wp $(ARGS)

reset:
	docker compose down -v
	@echo "Local database volume removed. Run 'make up' to start fresh."

config:
	docker compose config
```

- [ ] **Step 2: Verify make targets are listed**

Run: `make -n up`
Expected: prints `./scripts/setup.sh` then `docker compose up -d` (dry run, no execution)

- [ ] **Step 3: Commit**

```bash
git add Makefile
git commit -m "feat: add Makefile shortcuts for local Docker dev"
```

---

### Task 7: Add `docs/LOCAL-DEV.md`

**Files:**
- Create: `docs/LOCAL-DEV.md`

- [ ] **Step 1: Create `docs/LOCAL-DEV.md`**

```markdown
# Local Development

## Prerequisites

- Docker and Docker Compose
- Make
- Git

## First-Time Setup

```bash
git checkout development
cp .env.example .env          # or: make setup
make up
```

Open http://localhost:8080 and complete the WordPress install wizard.

phpMyAdmin: http://localhost:8081

## Daily Commands

| Command | Description |
|---|---|
| `make up` | Start containers |
| `make down` | Stop containers |
| `make logs` | Follow WordPress logs |
| `make shell` | Shell into WordPress container |
| `make wp ARGS="plugin list"` | Run WP-CLI |
| `make reset` | Destroy containers and local DB volume |

## Theme and Plugin Development

Edit files under `wp-content/themes/` and `wp-content/plugins/`. Changes are visible immediately via the bind mount — no container rebuild needed.

## Files Not in Git

| File | Purpose |
|---|---|
| `.env` | Local Docker credentials |
| `wp-config.php` | Local WordPress config (copied from `wp-config-docker.php`) |
| `wp-content/uploads/` | Media files |
| `scripts/dumps/*.sql` | Database dumps for import |

## Troubleshooting

**Port 8080 already in use:** Change `8080:80` in `docker-compose.yml` and update `WP_HOME` / `WP_SITEURL` in `.env`.

**Permission errors in wpcli:** The `wpcli` service runs as `www-data` (uid 33). If file permission issues occur after `make wp`, fix ownership: `sudo chown -R $USER:$USER .`

**Database connection error:** Ensure `make up` completed and `db` container is healthy: `docker compose ps`
```

- [ ] **Step 2: Commit**

```bash
git add docs/LOCAL-DEV.md
git commit -m "docs: add local Docker development guide"
```

---

### Task 8: Add `docs/DEPLOY.md`

**Files:**
- Create: `docs/DEPLOY.md`

- [ ] **Step 1: Create `docs/DEPLOY.md`**

```markdown
# Deploy Workflow (Git + FTP)

## Branch Flow

```
development → staging → main
```

- **Local dev:** `development` branch + Docker
- **Staging server:** deploy from `staging` branch
- **Production server:** deploy from `main` branch

## What to Upload via FTP/SFTP

### Always (when changed)

- `wp-content/themes/<theme>/`
- `wp-content/plugins/<plugin>/`
- `wp-content/mu-plugins/`

### Never

- `wp-config.php` — separate config per environment
- `.env`
- `wp-content/uploads/`
- `wp-content/cache/`
- `wp-content/upgrade/`
- `wp-admin/`, `wp-includes/` — WordPress core stays on server

### Third-Party Plugins

When you install a plugin locally, also install it on the server (FTP or hosting panel). Upload plugin folder only when its files changed.

## Staging Deploy

```bash
git checkout development
git status                                    # must be clean

git checkout staging
git merge development
git diff main -- wp-content/                  # review changes

# Upload changed wp-content folders to staging server via FTP

git push origin staging
```

## Production Deploy

```bash
git checkout main
git merge staging
git diff HEAD~1 -- wp-content/                # review changes

# Upload changed wp-content folders to production server via FTP

git push origin main
```

## Future: Import Staging Database Locally

```bash
# 1. Export from staging phpMyAdmin → scripts/dumps/staging.sql
# 2. Import:
make wp ARGS="db import scripts/dumps/staging.sql"
make wp ARGS="search-replace 'https://your-staging-domain.com' 'http://localhost:8080' --all-tables"
# 3. Optionally download wp-content/uploads/ from staging via FTP
```
```

- [ ] **Step 2: Commit**

```bash
git add docs/DEPLOY.md
git commit -m "docs: add git and FTP deploy workflow guide"
```

---

### Task 9: End-to-End Verification

**Files:** None (verification only)

- [ ] **Step 1: Bootstrap local environment**

```bash
git checkout development
make setup
make up
```

Expected: containers `wordpress`, `db`, `phpmyadmin`, `wpcli` start; `docker compose ps` shows all running (wpcli exits after run — that's normal).

- [ ] **Step 2: Verify WordPress responds**

Run: `curl -s -o /dev/null -w "%{http_code}" http://localhost:8080`
Expected: `200` or `302` (install wizard redirect)

- [ ] **Step 3: Verify WP-CLI works**

Run: `make wp ARGS="core version"`
Expected: prints WordPress version (e.g. `7.0`)

- [ ] **Step 4: Verify bind mount (live edit)**

Run: `echo "/* docker-dev-test */" >> wp-content/themes/lowfi/style.css`
Then: `curl -s http://localhost:8080 | head -20` (or check in browser that site still loads)
Revert: `git checkout -- wp-content/themes/lowfi/style.css`

- [ ] **Step 5: Verify DB persistence**

```bash
make down
make up
make wp ARGS="core version"
```

Expected: same result as Step 3 (DB volume survived restart)

- [ ] **Step 6: Verify git ignores secrets**

Run: `git status --short | grep -E 'wp-config.php|\.env$' || echo "SECRETS NOT TRACKED"`
Expected: `SECRETS NOT TRACKED`

- [ ] **Step 7: Final commit (if any fixups needed)**

Only if verification revealed issues. Otherwise, no commit needed for this task.

---

## Spec Coverage Check

| Spec requirement | Task |
|---|---|
| docker-compose with wordpress, mysql, phpmyadmin, wpcli | Task 4 |
| .env.example template | Task 2 |
| wp-config-docker.php template | Task 3 |
| scripts/setup.sh bootstrap | Task 5 |
| Makefile shortcuts | Task 6 |
| .gitignore updates | Task 1 |
| docs/LOCAL-DEV.md | Task 7 |
| docs/DEPLOY.md | Task 8 |
| scripts/dumps/ for future import | Task 5 |
| Verification checklist | Task 9 |

All spec requirements covered. No gaps.
