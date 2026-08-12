# Skill Matching System — System Documentation

## 1. Overview

The **Skill Matching System** is a campus-based peer-to-peer skill exchange platform built with **Laravel 10**. It enables students to request help for skills they need and automatically matches them with verified student providers using a weighted scoring algorithm.

The system supports full request-to-completion lifecycles, real-time messaging, post-service reviews, moderation, and admin analytics.

---

## 2. Key Features

- **Skill Catalog** — 400+ pre-seeded skills across 15+ categories with subcategories.
- **Auto-Matching Engine** — Weighted multi-dimensional scoring (skill overlap, category coverage, rating, profile quality).
- **Request Lifecycle** — Post requests, receive matches, apply, accept/reject, complete, review.
- **Messaging** — Direct messaging between users with AJAX polling.
- **Reviews & Ratings** — 1–5 star ratings with comments; updates provider averages.
- **Moderation** — 3-strike violation escalation (Warning → Suspension → Ban) with audit logging.
- **Admin Dashboard** — User management, skill CRUD, verification, report resolution, analytics.
- **Search** — Advanced search across requests and providers by category, subcategory, service mode, and keyword.
- **Notifications** — Real-time notifications for matches, assignments, reports, verification, and penalties.

---

## 3. Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 10.x (PHP 8.1+) |
| Frontend | Blade + Vite + Axios |
| Database | MySQL 8.0 (primary), SQLite (testing) |
| Cache / Queue | Redis (fallback: file/database) |
| Authentication | Custom session-based auth + Laravel Sanctum (API) |
| Testing | PHPUnit 10.x |
| Code Style | Laravel Pint |
| Containerization | Docker + Docker Compose |
| Email (dev) | Mailhog |

---

## 4. Project Structure

```
skill_matching/
├── app/
│   ├── Enums/                      # State enums (AccountStatus, etc.)
│   ├── Exceptions/
│   │   └── Handler.php             # Global exception handler
│   ├── Http/
│   │   ├── Controllers/            # 16 controllers
│   │   │   ├── HomeController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── RequestController.php
│   │   │   ├── MatchController.php
│   │   │   ├── AssignmentController.php
│   │   │   ├── SearchController.php
│   │   │   ├── SkillController.php
│   │   │   ├── MessageController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── ReportController.php
│   │   │   ├── ReviewController.php
│   │   │   ├── AdminController.php
│   │   │   └── AnalyticsController.php
│   │   ├── Kernel.php              # Middleware stack
│   │   └── Middleware/
│   │       ├── VerifyUser.php
│   │       ├── AdminMiddleware.php
│   │       └── RedirectIfAuthenticated.php
│   ├── Models/                     # 11 Eloquent models
│   │   ├── User.php
│   │   ├── Skill.php
│   │   ├── UserSkill.php
│   │   ├── SkillRequest.php
│   │   ├── UserMatch.php
│   │   ├── Assignment.php
│   │   ├── Message.php
│   │   ├── Notification.php
│   │   ├── Review.php
│   │   ├── Report.php
│   │   └── AdminActionLog.php
│   ├── Services/
│   │   ├── Matching/
│   │   │   └── Recommender.php     # Core matching algorithm
│   │   ├── Analytics/
│   │   │   └── AnalyticsService.php
│   │   └── Moderation/
│   │       └── ModerationService.php
│   └── Providers/
├── config/
│   ├── matching.php                 # Recommender weights & thresholds
│   ├── app.php
│   ├── auth.php
│   └── database.php
├── database/
│   ├── migrations/                  # 21 migrations
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   └── SkillSeeder.php          # 400+ skills
│   └── factories/
├── docs/                            # Documentation
├── routes/
│   ├── web.php                      # All web routes
│   ├── api.php                      # Sanctum API routes
│   └── channels.php
├── resources/
│   └── views/                       # 26 Blade templates
├── tests/                           # PHPUnit tests
├── public/
│   └── index.php                    # Application entry point
├── composer.json
├── package.json
├── docker-compose.yml
├── Dockerfile
└── README_LARAVEL.md                # Quick-start guide
```

---

## 5. Installation & Setup

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js (for Vite assets)
- Git Bash (on Windows)

### Quick Start (Git Bash)

```bash
cd /e/XAMPP/htdocs/skill_matching
bash start.sh
```

Choose **option 1** to start MySQL + Laravel server.

### Manual Setup

```bash
# 1. Start MySQL
mysqld --console --datadir="/e/XAMPP/mysql/data" &

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Run migrations
php artisan migrate --force

# 6. Seed database (400+ skills)
php artisan db:seed --force

# 7. Build assets
npm run build

# 8. Start server
php artisan serve --host=0.0.0.0 --port=8000
```

### Access
- Homepage: `http://localhost:8000`
- Login: `http://localhost:8000/login`

### Default Admin Account
- Email: `admin@campus.local`
- Password: `admin123`

---

## 6. User Roles & Permissions

| Role | Description | Key Permissions |
|------|-------------|-----------------|
| **Student** | Primary platform user | Post requests, browse matches, apply, message, review, report |
| **Faculty** | Verified academic staff | Same as Student |
| **Staff** | Verified campus staff | Same as Student |
| **Admin** | Platform administrator | User management, skill CRUD, verification, report resolution, analytics |

### Verification
- Self-registered users start as **unverified** and are shown a pending page until an admin approves them.
- Admin-created users can be auto-verified.
- Verified users are protected by the `verified.user` middleware.

---

## 7. Database Schema

### Core Tables

| Table | Primary Key | Purpose |
|-------|-------------|---------|
| `users` | `User_ID` | Core user entity |
| `skills` | `Skill_ID` | Skill catalog (Category → Subcategory) |
| `user_skills` | `User_Skill_ID` | M:N pivot: user ↔ skill |
| `skill_requests` | `Request_ID` | Help requests |
| `request_skills` | `Request_Skill_ID` | M:N pivot: request ↔ skill |
| `request_assignments` | `Assignment_ID` | Application lifecycle for a match |
| `matches` | `Match_ID` | Generated provider matches |
| `messages` | `Message_ID` | Direct messages |
| `notifications` | `Notif_ID` | User notifications |
| `reviews` | `Review_ID` | Post-completion ratings |
| `reports` | `Report_ID` | Moderation reports |
| `admin_action_logs` | `Log_ID` | Admin audit trail |

### Key Columns

**users**
- `User_ID`, `Full_Name`, `Email` (unique), `Password_Hash`, `Role`, `Council`, `Is_Verified`, `Account_Status` (Active/Warning/Suspended/Banned), `Warning_Count`, `Avg_Rating`, `Total_Completed`

**skills**
- `Skill_ID`, `Skill_Title` (unique), `Category`, `Subcategory`

**skill_requests**
- `Request_ID`, `User_ID`, `Skill_ID`, `Title`, `Description`, `Status` (Open/Assigned/Completed/Cancelled/Pending), `Service_Mode` (Remote/Face-to-Face/Hybrid)

**request_assignments**
- `Assignment_ID`, `Request_ID`, `User_ID`, `Status` (Pending/Accepted/Rejected/Active/Completed/Failed)

**matches**
- `Match_ID`, `Matched_User_ID`, `Request_ID`, `Match_Score`, `Matched_At`

**reviews**
- `Review_ID`, `Reviewed_User_ID`, `Reviewer_ID`, `Request_ID`, `Rating` (1–5), `Comment`

**reports**
- `Report_ID`, `Reporter_ID`, `Reported_User_ID`, `Request_ID`, `Reason`, `Status` (Pending/Dismissed/Action_Taken)

### Relationship Diagram

```
users ──┬── user_skills ◄──► skills
        ├── skill_requests (1:N)
        │     └── request_skills ◄──► skills
        │     ├── request_assignments (1:N)
        │     ├── matches (1:N)
        │     ├── reviews (1:N)
        │     └── reports (1:N)
        ├── matches (1:N as Matched_User_ID)
        ├── messages (1:N as Sender/Receiver)
        ├── notifications (1:N)
        ├── reviews (1:N as Reviewer)
        ├── reports (1:N as Reporter/Reported)
        └── admin_action_logs (1:N)
```

---

## 8. Core Workflows

### 8.1 Request & Auto-Matching

1. A verified user creates a `SkillRequest` with title, description, primary skill, additional skills, and service mode.
2. `RequestController::store()` triggers `Recommender::rankForRequest()`.
3. The recommender queries verified student providers and scores them using:
   - **Skill Overlap (40%)** — Jaccard similarity between requested and provider skill sets
   - **Category Coverage (25%)** — Percentage of requested categories covered
   - **Rating (20%)** — Normalized average rating × confidence factor
   - **Profile Quality (15%)** — Verification status, account health, experience saturation
4. Top 20 candidates (above `min_match_score`) are persisted as `UserMatch` records.
5. Matched providers receive "Match" notifications.

### 8.2 Assignment Lifecycle

```
Open → Pending → Accepted → Active → Completed
                → Rejected
```

- **Provider applies** → `Assignment` created with status `Pending`
- **Owner accepts** → Assignment becomes `Accepted`; all other pending applicants are auto-rejected
- **Active** → Either party marks the assignment as `Active`
- **Completed / Failed** → Either party closes the transaction
- **Review** → On completion, the request owner is prompted to submit a review

### 8.3 Reviews & Ratings

- Only completed assignments are reviewable.
- Reviews update the reviewed user's `Avg_Rating` and `Total_Completed`.
- Ratings are 1–5 stars with optional comments.

### 8.4 Moderation & Reports

- Users can report others with a reason (optionally linked to a request).
- Admins resolve reports as `Dismissed` or `Action_Taken`.
- `ModerationService` applies a 3-strike escalation:
  1. **Warning** — logged in `AdminActionLog`
  2. **Suspension** — `Account_Status` set to `Suspended`
  3. **Ban** — `Account_Status` set to `Banned`

### 8.5 Messaging

- Direct messages between any two authenticated users.
- AJAX polling endpoint (`?ajax=1&since=<message_id>`) enables near-real-time updates.
- Partner list is derived from message history.

---

## 9. Routes Reference

### Public

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/` | `home` | Landing page |
| GET | `/login` | `login` | Show login form |
| POST | `/login` | — | Authenticate (throttled: 5/min) |
| GET | `/register` | `register` | Show registration form |
| POST | `/register` | — | Register user (throttled: 3/10min) |
| POST | `/logout` | `logout` | Logout |
| GET | `/pending-verification` | `pending.verification` | Awaiting admin approval |

### Authenticated + Verified

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/dashboard` | `dashboard` | User dashboard |
| GET/POST | `/profile/{id?}` | `profile.show` | View profile |
| POST | `/profile/{id}/update` | `profile.update` | Update profile |
| POST | `/profile/skill/add` | `profile.skill.add` | Add skill to profile |
| POST | `/profile/skill/remove` | `profile.skill.remove` | Remove skill from profile |
| GET/POST | `/requests` | `requests.index` | List requests |
| POST | `/requests` | `requests.store` | Create request |
| GET | `/requests/{id}` | `requests.show` | View request |
| GET/PUT | `/requests/{id}/edit` | `requests.edit` | Edit request |
| PUT | `/requests/{id}` | `requests.update` | Update request |
| DELETE | `/requests/{id}` | `requests.destroy` | Delete request |
| POST | `/requests/{id}/complete` | `requests.complete` | Mark request complete |
| GET | `/matches` | `matches.index` | Browse matches |
| GET | `/matches/{id}` | `matches.show` | View match details |
| GET | `/search` | `search.index` | Advanced search |
| GET | `/assignments` | `assignments.index` | My assignments |
| POST | `/assignments/apply` | `assignments.apply` | Apply for request |
| POST | `/assignments/{id}/accept` | `assignments.accept` | Accept application |
| POST | `/assignments/{id}/reject` | `assignments.reject` | Reject application |
| POST | `/assignments/{id}/complete` | `assignments.complete` | Complete assignment |
| POST | `/assignments/{id}/review` | `assignments.review` | Submit review |
| GET | `/messages` | `messages.index` | Message inbox |
| POST | `/messages` | `messages.store` | Send message |
| GET | `/reviews` | `reviews.index` | Pending reviews |
| POST | `/reviews` | `reviews.store` | Submit review |
| GET | `/reports` | `reports.index` | My reviews (reports page) |
| POST | `/reports` | `reports.store` | Submit report |
| GET | `/skills` | `skills.index` | Browse skills |
| POST | `/skills` | `skills.store` | Add skill (admin) |
| POST | `/skills/add-to-me` | `skills.addToMe` | Add skill to my profile |
| GET | `/notifications` | `notifications.index` | View notifications |

### Admin Only

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/admin` | `admin.index` | Admin dashboard |
| GET | `/analytics` | `admin.analytics` | Analytics dashboard |
| POST | `/admin/skills` | `admin.skills` | Add new skill |
| POST | `/admin/users/register` | `admin.users.register` | Register new user |
| POST | `/admin/users/verify` | `admin.users.verify` | Verify user |
| POST | `/admin/reports/resolve` | `admin.reports.resolve` | Resolve report (throttled: 10/min) |
| POST | `/admin/users/delete` | `admin.users.delete` | Delete user (throttled: 5/min) |
| POST | `/search/categories` | `search.categories.add` | Add category (admin) |

---

## 10. Configuration

### Recommender Configuration (`config/matching.php`)

| Setting | Default | Description |
|---------|---------|-------------|
| `weights.skill_overlap` | `0.40` | Jaccard similarity weight |
| `weights.category_coverage` | `0.25` | Category coverage weight |
| `weights.rating` | `0.20` | Provider rating weight |
| `weights.profile_quality` | `0.15` | Profile quality weight |
| `max_rating` | `5` | Maximum star rating |
| `max_completed_for_confidence` | `5` | Transactions needed for full rating confidence |
| `max_completed_for_experience` | `10` | Transactions needed for full experience bonus |
| `min_match_score` | `15.0` | Minimum score (0–100) to store a match |
| `default_limit` | `20` | Max matches generated per request |

### Middleware Groups

| Middleware | Route Group | Purpose |
|-----------|-------------|---------|
| `auth` | Authenticated | Ensures user is logged in |
| `verified.user` | Verified | Checks `Is_Verified` flag on user |
| `admin` | Admin | Checks `Role === 'Admin'` |
| `throttle:5,1` | Login | Limits login attempts to 5 per minute |
| `throttle:3,10` | Register | Limits registration to 3 per 10 minutes |

---

## 11. Services

### Recommender (`App\Services\Matching\Recommender`)

The core matching algorithm. Accepts a `SkillRequest` and returns ranked providers.

**Scoring Dimensions:**
1. **Skill Overlap (40%)** — Jaccard index: `|intersection| / |union|` of provider vs requested skills.
2. **Category Coverage (25%)** — Fraction of requested categories present in provider skills.
3. **Rating (20%)** — `(Avg_Rating / max_rating) × (min(Total_Completed, max_completed) / max_completed_for_confidence)`.
4. **Profile Quality (15%)** — Composite of verification status, account health (Active), and experience saturation.

### Analytics Service (`App\Services\Analytics\AnalyticsService`)

Computes cached (60s) KPIs:
- Monthly request and match volume
- Top-rated providers
- Transaction completion rate (overall + by category)
- Active provider count (last 30 days)

### Moderation Service (`App\Services\Moderation\ModerationService`)

Handles 3-strike escalation:
1. First violation → `Account_Status = Warning`
2. Second violation → `Account_Status = Suspended`
3. Third violation → `Account_Status = Banned`

All actions are logged in `AdminActionLog`.

---

## 12. Testing

The project uses **PHPUnit 10.x**.

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=UserTest
```

### Test Structure
- Located in `tests/`
- Database uses SQLite in memory (`:memory:`) for test isolation
- Model factories available in `database/factories/`

---

## 13. Deployment Notes

- The application is stateless except for the database, Redis cache, and session store.
- `APP_KEY` must be set in production.
- Set `APP_DEBUG=false` and configure a production mail driver.
- Ensure `.env` is not committed to version control.
- Run `php artisan migrate --force` and `php artisan db:seed --force` during deployment.
- Build frontend assets with `npm run build`.

---

## 14. Troubleshooting

| Issue | Solution |
|-------|----------|
| `SQLSTATE[HY000] [2002]` | MySQL is not running. Start MySQL service or run `mysqld`. |
| `Class not found` | Run `composer dump-autoload`. |
| `Vite manifest not found` | Run `npm run build` or `npm run dev`. |
| `Permission denied` on storage | Ensure `storage/` and `bootstrap/cache/` are writable. |
| `Queue not working` | Ensure Redis is running or configure database queue driver. |

---

## 15. License

This project is an academic/campus system. Refer to your institution's policies for usage rights.
