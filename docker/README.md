# Docker Setup for Skill Matching System

## Quick Start

```bash
# Clone and navigate to the project
cd skill_matching

# Start all services
docker-compose up -d

# Wait for MySQL to be ready (10-30 seconds)
docker-compose logs -f mysql

# Run migrations (in a separate terminal)
docker-compose exec app php artisan migrate:fresh --seed

# Generate application key
docker-compose exec app php artisan key:generate
```

## Services

| Service    | Port  | Description                          |
|------------|-------|--------------------------------------|
| app        | 8080  | Laravel application (PHP 8.1 + Apache)|
| mysql      | 3306  | MySQL 8.0 database                   |
| redis      | 6379  | Redis cache and session store        |
| phpmyadmin | 8081  | MySQL admin interface                |
| mailhog    | 8025  | Mail catcher (web UI) / 1025 (SMTP)  |

## Common Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan test
docker-compose exec app php artisan tinker

# Rebuild the app image (after Dockerfile changes)
docker-compose up -d --build

# Remove all data (fresh start)
docker-compose down -v
docker-compose up -d --build
```

## Environment

The `.env.docker` file contains the exact environment variables used in containers.
For production, copy `.env.example` to `.env` and update credentials.

## Admin Credentials (seeded)

- Email: admin@campus.local
- Password: admin123
