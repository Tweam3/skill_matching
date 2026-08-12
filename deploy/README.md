# Deployment

This directory contains deployment-specific configuration for Render.com.

## Files

- `render.yaml` — Render.com Blueprint file. It defines the web service and PostgreSQL database.
- `Procfile` — Process file for Render.com web service startup.

## How to deploy

1. Push your repo to GitHub.
2. In Render.com, click **New** → **Blueprint**.
3. Connect your GitHub repo.
4. Render will detect `render.yaml` and create the service + database automatically.
5. After the first deploy, run migrations if needed:
   ```bash
   php artisan migrate --force
   ```

## Local development

For local MySQL development, keep your root `.env` with:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skill_matching_system
DB_USERNAME=root
DB_PASSWORD=
```

The `.env.example` in the project root uses PostgreSQL because Render.com provides a managed PostgreSQL database.
