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
