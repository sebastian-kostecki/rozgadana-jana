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
