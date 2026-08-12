# Skill Matching System - Laravel

## Requirements
- Git Bash (installed)
- PHP 8.1+ (via XAMPP)
- MySQL (via XAMPP)

## Quick Start with Git Bash

1. **Open Git Bash**
2. **Navigate to the project:**
   ```bash
   cd /e/XAMPP/htdocs/skill_matching
   ```
3. **Run the startup script:**
   ```bash
   bash start.sh
   ```
4. **Choose option 1** to start MySQL + Laravel server

## Manual Commands

If you prefer to run commands manually in Git Bash:

```bash
# Start MySQL
mysqld --console --datadir="/e/XAMPP/mysql/data" &

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000
```

## Access the App

- Homepage: http://localhost:8000
- Login: http://localhost:8000/login

## Default Admin Account
- Email: admin@campus.local
- Password: admin123

## Notes
- No XAMPP Apache needed (Laravel's built-in server is used)
- MySQL must be running for the app to work
- Original files are backed up in `skill_matching_backup/`
