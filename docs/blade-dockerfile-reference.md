# Blade & Dockerfile Reference

Unique patterns used in Blade templates, Dockerfiles, and shell scripts. No duplicates.

---

## HTML

### Blade Template Structure

```blade
{{-- Base layout --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Page Title</h2>
  ...
</div>
@endsection

@push('scripts')
<script>
  // inline JavaScript
</script>
@endpush
```

### Conditional Rendering

```blade
{{-- Auth check --}}
@auth
  ...
@endauth

@guest
  ...
@endguest

{{-- Role check --}}
@if (auth()->user()->Role === 'Admin')
  ...
@endif

{{-- Error display --}}
@if ($errors->any())
  @foreach ($errors->all() as $err)
    <div class="alert alert-danger">{{ $err }}</div>
  @endforeach
@endif

{{-- Session flashes --}}
@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Collection check --}}
@if ($items->isEmpty())
  <div class="alert alert-info">No items found.</div>
@else
  @foreach ($items as $item)
    ...
  @endforeach
@endif

{{-- Inline PHP --}}
@php $slug = Illuminate\Support\Str::slug($category); @endphp
@php $checked = in_array($cat, $categories) ? 'checked' : ''; @endphp
@php $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values(); @endphp
```

### Loops

```blade
{{-- Standard foreach --}}
@foreach ($items as $item)
  <div class="card">{{ $item->Name }}</div>
@endforeach

{{-- Forelse with empty --}}
@forelse ($providers as $p)
  <tr>...</tr>
@empty
  <tr><td colspan="6">No providers found.</td></tr>
@endforelse

{{-- With index --}}
@foreach ($items as $index => $item)
  <div>{{ $index }}. {{ $item->Name }}</div>
@endforeach
```

### Forms

```blade
{{-- Standard form with CSRF --}}
<form method="POST" action="{{ route('name') }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')
  <input type="text" name="field" value="{{ old('field', $model->field) }}" required>
  <button type="submit" class="btn btn-primary">Save</button>
</form>

{{-- GET form --}}
<form method="GET" action="{{ route('search.index') }}">
  @csrf
  <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Search...">
  <button type="submit" class="btn btn-primary">Search</button>
</form>

{{-- Inline delete form --}}
<form method="post" action="{{ route('admin.users.delete') }}" style="display:inline;" onsubmit="return confirm('Delete this user?');">
  @csrf
  <input type="hidden" name="user_id" value="{{ $u->User_ID }}">
  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
</form>
```

### Links and Routes

```blade
<a href="{{ route('profile.show', $p->User_ID) }}" class="btn btn-secondary btn-sm">View Profile</a>
<a href="{{ route('messages.index', ['with' => $p->User_ID]) }}" class="btn btn-primary btn-sm">Message</a>
<a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-secondary btn-sm">View</a>
<a href="{{ url()->current() }}">Current URL</a>
```

### Asset and Auth Helpers

```blade
<img src="{{ $user->Profile_Picture ? asset('storage/' . $user->Profile_Picture) : asset('images/default-avatar.png') }}" alt="{{ $user->Full_Name }}">

@if (Auth::check() && Auth::id() !== $p->User_ID)
  <a href="{{ route('messages.index', ['with' => $p->User_ID]) }}">Message</a>
@endif
```

### Inline Styles (Common Patterns)

```blade
<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-top:16px;">
  <div style="display:flex;flex-direction:column;gap:4px;">
    <input type="text" name="new_category" placeholder="New category" style="padding:6px;border-radius:4px;border:1px solid #ddd;">
    <button type="button" id="add-category-btn" class="btn btn-primary btn-sm">Add</button>
  </div>
</div>
```

### Data Attributes

```blade
<button type="button" class="toggle-subcats" data-category="{{ $cat }}" style="cursor:pointer;">[+]</button>
<span class="subcategory-list" data-category="{{ $cat }}" style="display:none;">...</span>
```

---

## DOCKERFILE PATTERNS

### Laravel Production Dockerfile

```dockerfile
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libpq-dev \
    zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd mbstring exif pcntl bcmath sockets

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html/

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
```

---

## SHELL SCRIPT PATTERNS

### Docker Entrypoint Script

```bash
#!/bin/bash
set -e

# Copy .env if not present
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Override config from environment variables
if [ -n "$DB_CONNECTION" ]; then
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=$DB_CONNECTION/" /var/www/html/.env
fi
if [ -n "$DB_HOST" ]; then
    sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" /var/www/html/.env
fi
# ... repeat for DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Laravel setup
php artisan key:generate --force
php artisan config:clear
php artisan route:clear
php artisan migrate --force
php artisan db:seed --force
rm -f /var/www/html/public/storage
php artisan storage:link

exec "$@"
```

### Local Development Script (Git Bash)

```bash
#!/bin/bash

# Add XAMPP tools to PATH
export PATH="/e/XAMPP/php:$PATH"
export PATH="/e/XAMPP/mysql/bin:$PATH"

MYSQL_DATADIR="/e/XAMPP/mysql/data"

start_mysql() {
    echo "Starting MySQL..."
    if ! netstat -ano | grep -q ':3306'; then
        mysqld --console --datadir="$MYSQL_DATADIR" &
        sleep 5
        echo "MySQL started."
    else
        echo "MySQL is already running."
    fi
}

stop_mysql() {
    echo "Stopping MySQL..."
    taskkill /F /IM mysqld.exe 2>/dev/null
    echo "MySQL stopped."
}

# Menu
echo "1. Start MySQL + Laravel Server"
echo "2. Start MySQL only"
echo "3. Stop MySQL"
echo "4. Run migrations"
echo "5. Seed database"

read -p "Choose an option: " choice

case $choice in
    1) start_mysql; echo "Starting Laravel server..."; php artisan serve --host=0.0.0.0 --port=8000 ;;
    2) start_mysql ;;
    3) stop_mysql ;;
    4) php artisan migrate --force ;;
    5) php artisan db:seed --force ;;
    *) echo "Invalid option."; exit 1 ;;
esac
```

### Process Guard Pattern

```bash
# Check if a port is in use
if ! netstat -ano | grep -q ':3306'; then
    # Start the process
    mysqld --console --datadir="$MYSQL_DATADIR" &
fi

# Kill a process by name
taskkill /F /IM mysqld.exe 2>/dev/null
```

---

## RENDER.COM CONFIGURATION (render.yaml)

```yaml
services:
  - type: web
    name: skill-matching
    env: docker
    dockerfilePath: ./Dockerfile
    disk:
      name: skill-matching-storage
      mountPath: /var/www/html/storage
      size: 1
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: APP_KEY
        generateValue: true
      - key: APP_URL
        value: https://skill-matching-eg0c.onrender.com
      - key: DB_CONNECTION
        value: pgsql
      - key: DB_HOST
        value: dpg-d9vtio5bedkc739dbuqg-a
      - key: DB_PORT
        value: "5432"
      - key: DB_DATABASE
        value: skill_matching_system_t808
      - key: DB_USERNAME
        value: skill_matching_user
      - key: DB_PASSWORD
        value: ldcpUO7lzzCaZzcwHF4comYLfKW74lTG
      - key: SESSION_DRIVER
        value: file
      - key: SESSION_SECURE_COOKIE
        value: true
      - key: CACHE_DRIVER
        value: file
      - key: QUEUE_CONNECTION
        value: sync

databases:
  - name: skill-matching-db
    plan: free
    databaseName: skill_matching_system_t808
    user: skill_matching_user
```

## COMPOSER.JSON (Key Scripts)

```json
{
    "scripts": {
        "post-autoloaddump": [
            "Illuminate\\Foundation\\ProviderRepository::compileManifest",
            "@php artisan package:discover --ansi"
        ]
    }
}
```

## .dockerignore PATTERNS

```
.git
.gitignore
node_modules/
vendor/
.env
.env.backup
Dockerfile
docker-compose.yml
*.md
docs/
tests/
.phpunit.result.cache
```
