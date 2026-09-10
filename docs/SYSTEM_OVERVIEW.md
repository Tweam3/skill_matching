# Skill Matching System — How It Works

## 1. System Overview

The Skill Matching System is a web platform that connects students, faculty, and staff who need a specific skill or service with verified providers who can fulfill that request. It is built with Laravel (PHP), uses a relational database (PostgreSQL in production, MySQL/MariaDB locally), and is deployed on Render.com.

The platform's core workflow is:

1. A user creates a **service request** describing a skill they need.
2. The system's **recommender engine** automatically matches the request with eligible providers.
3. Providers **apply** to requests they are interested in.
4. The requester **reviews applications** and accepts one provider.
5. Both parties **complete the transaction**.
6. The requester **leaves a review**, which updates the provider's rating.
7. The entire process is monitored by an **admin panel** with analytics and moderation tools.

---

## 2. User Roles

The system defines four user roles:

### Student
- Default role for new registrations.
- Can create service requests, apply to requests, receive matches, send reviews, and message other users.
- Requires a unique student ID for registration.
- Student and Faculty roles must select a council (HBM, CSC, BIT, EDUC, or Unaffiliated).

### Faculty
- Can create requests, apply to requests, and participate in transactions.
- Requires a council selection.

### Staff
- Can create requests and participate in the platform.
- Does not require a council.

### Admin
- Full control over the platform.
- Can manually register users, verify pending accounts, approve/reject reports, add skills, delete users, and view analytics.
- Admin accounts are created only by an existing admin (never through public registration).

---

## 3. Authentication & Account Management

### Registration
- Public users can create an account through the registration page.
- Registration requires:
  - Full name
  - Email (must be unique)
  - Student ID (must be unique and follow the `YYYY-XXXX-X` format)
  - Password (minimum 8 characters, must include uppercase, lowercase, numbers, and symbols)
  - Password confirmation
  - Council (required for Student and Faculty roles)
- The student ID placeholder shows only the format (`YYYY-XXXX-X`), not a real working ID, so users cannot copy a valid ID from the form.
- The `Student_ID` column has a database-level unique constraint, so no two accounts can ever share the same ID (case-insensitive).
- New public registrations start with `Is_Verified = false` and `Account_Status = Pending`.
- After registration, the user is redirected to a "pending verification" page until an admin approves the account.

### Login
- Users log in with email and password.
- Failed login attempts are logged with IP address and user agent for security monitoring.
- After login:
  - Admins are redirected to the admin panel.
  - Verified users are redirected to the dashboard.
  - Unverified users are redirected to the pending verification page.

### Account Statuses
Accounts can be in one of several states:
- **Pending**: Awaiting admin verification.
- **Active**: Fully functional account.
- **Warning**: First violation; account still usable.
- **Suspended**: Temporarily locked for 3 days.
- **Banned**: Permanently locked.

### Suspension Auto-Recovery
- Suspended accounts automatically reactivate after 3 days.
- The middleware checks the `Suspended_At` timestamp on every request.
- If 3 days have passed, the account is automatically set back to `Active`.

### Pending Verification
- Users who are not yet verified can only access the pending verification page.
- They cannot access the dashboard, create requests, or use any other platform features.

### Account Pages
- **Suspended page**: Shown to users whose account is suspended.
- **Banned page**: Shown to users whose account is permanently banned.

---

## 4. User Profile System

### Profile Viewing
- Every user has a public profile page.
- Profiles display:
  - Name and profile picture
  - Bio and headline
  - Skills with proficiency levels
  - Average rating
  - Total completed transactions
  - Verification status
  - Service modes offered
  - Reviews received

### Profile Editing
Users can edit:
- Full name
- Email
- Bio (max 500 characters)
- Headline
- Hourly rate
- Service modes (Remote, Face-to-Face, Hybrid)
- Profile picture (with drag-to-pan crop tool)
- Password (requires current password confirmation)

### Profile Picture
- Users can upload a profile picture (max 2 MB, image file only).
- The system includes a client-side crop tool with zoom and drag-to-pan.
- Supports both mouse and touch (mobile) interactions.
- Old pictures are deleted from storage when replaced or removed.
- Pictures are stored on a persistent disk in production.

### Skill Management on Profile
- Users can add skills from the global skill catalog.
- Each added skill can be assigned a proficiency level (1–5).
- Users can remove skills they no longer have.
- Only the profile owner or an admin can modify a user's skills.

---

## 5. Skills System

### Skill Catalog
- The platform maintains a global catalog of skills.
- Each skill has:
  - A title (e.g., "PHP", "JavaScript", "Python")
  - A category (e.g., "Programming", "Database", "Design")
  - An optional subcategory (e.g., "Backend", "Frontend")
- Skills are grouped and displayed by category and subcategory.

### Skill Management
- **Admins** can add new skills with a title, category, and optional subcategory.
- **Users** can add skills from the catalog to their own profile.
- Skills are searchable and filterable.

### Skill Picker
- When creating a service request, users select skills from an interactive picker.
- The picker supports:
  - Search/filter by skill name
  - Category grouping
  - Multi-select
  - Selected skills shown as removable chips

---

## 6. Service Request System

### Creating a Request
Users can create a service request with:
- A title
- A description
- One primary skill
- Multiple additional skills
- A service mode: Remote, Face-to-Face, or Hybrid

When a request is created:
1. The request is saved with status `Open`.
2. The recommender engine immediately scores all eligible providers.
3. The top matching providers are saved to the matches table.
4. Each matched provider receives a notification.

### Request Statuses
Requests move through these statuses:
- **Open**: Accepting applications.
- **Pending**: An applicant has been accepted; work is in progress.
- **Completed**: Transaction finished successfully.
- **Failed**: Transaction was marked as failed.
- **Cancelled**: Request was cancelled (status option exists).

### Request Lifecycle
1. **Create**: Requester posts a request.
2. **Match**: System recommends providers.
3. **Apply**: Providers submit applications.
4. **Accept/Reject**: Requester reviews and decides.
5. **Complete/Fail**: Either party marks the transaction outcome.
6. **Review**: Requester rates the provider.

### Request Editing & Deletion
- Requesters can edit their own requests (title, description, skills, service mode).
- Requesters can delete their own requests.
- Deleting a request also removes all related matches, assignments, reviews, and reports (data integrity).

### Request Details
The request detail page shows:
- Full request information
- Requester information
- Match score breakdown (for providers viewing a match)
- List of applicants (for the requester)
- Actions: accept, reject, complete, fail, review

---

## 7. Matching & Recommender System

The platform uses a weighted multi-factor recommender engine to match service requests with providers.

### Matching Factors
The engine scores each provider against a request using six dimensions:

| Factor | Weight | Description |
|---|---|---|
| Skill Overlap | 30% | How many of the requested skills the provider has (Jaccard similarity) |
| Category Coverage | 20% | How many of the requested skill categories the provider covers |
| Service Mode | 15% | Whether the provider offers the requested service mode |
| Profile Tags | 15% | Whether the provider has Competent (level 3+) proficiency in requested skills |
| Rating | 12% | Provider's average rating with a confidence factor |
| Profile Quality | 8% | Verification status, account health, and experience |

### Cold-Start Handling
- Providers with zero completed transactions receive a neutral prior rating.
- This prevents new providers from being unfairly excluded.
- The prior rating is configurable in the matching configuration.

### Candidate Filtering
Only eligible providers are considered:
- Role must be Student.
- Account must be Active.
- Provider cannot be the requester.
- Provider must have at least one skill related to the request.

### Match Threshold
- Providers must meet a minimum match score to be recommended.
- Results are sorted by score in descending order.
- A configurable limit controls how many matches are persisted (default 20).

### Match Persistence
- Match results are stored in the `matches` table.
- Each match records the provider, request, and match score.
- Matches are used to populate the provider's "matched requests" view.

### Match Evaluation
The recommender can be evaluated against test cases using standard information retrieval metrics:
- Precision@K
- Recall@K
- Mean Reciprocal Rank (MRR)
- Mean Average Precision (MAP)

---

## 8. Assignment & Transaction Flow

### Applying to a Request
- Providers can apply to any `Open` request.
- A provider can only apply once per request.
- Applying creates an assignment record with status `Pending`.
- The requester receives a notification.

### Reviewing Applications
- The requester sees all pending and accepted applicants.
- The requester can:
  - **Accept** one applicant: Sets the assignment to `Accepted`, moves the request to `Pending`, and automatically rejects all other pending applicants (with notifications).
  - **Reject** an applicant: Sets the assignment to `Rejected`. If no other applicants remain, the request returns to `Open`.

### Completing a Transaction
- Either the provider or the requester can mark the assignment as completed or failed.
- Completing updates both the assignment and the request status.
- The requester is then prompted to leave a review.

### Failed Transactions
- If a transaction fails, the provider is notified and directed to a feedback page.
- The provider can choose to:
  - **Rate** the requester (1–5 stars with a comment)
  - **Report** the requester (with a reason and optional proof)

---

## 9. Reviews & Ratings

### Leaving a Review
- After a completed transaction, the requester can rate the provider.
- Reviews include:
  - Rating (1–5 stars)
  - Written comment
  - Request reference
  - Reviewer and reviewed user IDs

### Rating Calculation
- A user's average rating is recalculated after every new review.
- The `Total_Completed` counter increments when a review is submitted.
- Ratings influence the recommender's scoring.

### Review History
- Users can view:
  - Reviews they received
  - Reviews they gave
- The reviews page also shows which completed transactions still need a review.

---

## 10. Reporting & Moderation

### Reporting a User
Users can report another user with:
- Reported user ID
- Reason for the report
- Optional proof
- Optional related request ID

### Admin Report Resolution
Admins can resolve reports in two ways:

**Dismiss**
- Marks the report as dismissed.
- Notifies the reporter that the report was reviewed and dismissed.

**Action Taken**
- Triggers the moderation escalation system.
- Notifies both the reporter and the reported user.
- The reported user receives a penalty based on their violation history.

### Moderation Escalation
The moderation system applies escalating penalties:

| Violation Count | Action | Result |
|---|---|---|
| 1st violation | Warning | Account status set to `Warning`, warning count incremented |
| 2nd violation | Suspension | Account status set to `Suspended` for 3 days |
| 3rd+ violation | Ban | Account status set to `Banned` permanently |

### Admin Action Logs
- Every significant admin action is logged:
  - User registration
  - User verification/rejection
  - Report resolution
  - User deletion
  - Skill addition
  - Role updates
- Logs record the admin ID, action type, details, and timestamp.

---

## 11. Messaging System

### Conversations
- Users can message any other user on the platform.
- Messages are stored as a conversation history between two users.
- The message list shows all conversation partners.

### Message Sending
- Messages are sent via AJAX (no page reload).
- Each message records:
  - Sender
  - Receiver
  - Message text
  - Sent timestamp
  - Read timestamp

### Read Receipts
- Messages show "Sent" or "Seen" status.
- When a user opens a conversation, all unread messages from the other party are marked as read.

### Real-Time Updates
- The chat uses polling to fetch new messages every few seconds.
- New messages are appended to the chat without refreshing the page.
- The chat auto-scrolls to the bottom when new messages arrive.

### Mobile Support
- The messaging interface adapts to mobile screens.
- On mobile, selecting a conversation opens a full-screen chat view.
- A back button returns to the conversation list.

---

## 12. Notifications

### Notification Types
The system generates notifications for:
- New match recommendations
- New applications on a request
- Application accepted/rejected
- Verification approved/rejected
- Report reviewed/dismissed
- Penalties (warning, suspension, ban)
- Failed requests

### Notification Behavior
- Notifications are stored per user.
- Each notification has a type, message, and target URL.
- Unread notifications are counted and displayed in the navbar.
- Opening the notifications page marks all notifications as read.

---

## 13. Search System

### Search Scope
Users can search in three modes:
- **Requests**: Find open service requests.
- **Providers**: Find users who offer specific skills.
- **Both**: Search both requests and providers.

### Search Filters
Search supports filtering by:
- Keyword (title, description, skill name, user name, or email)
- Service mode (Remote, Face-to-Face, Hybrid)
- Category
- Subcategory

### Search Results
- Results are paginated (20 per page).
- Request results show the request title, description, skills, and requester.
- Provider results show name, skills, rating, completed count, and verification status.
- Filters are preserved in pagination links.

### Category Management
- Admins can add new categories and subcategories through the search page.
- Categories and subcategories are dynamically listed for filtering.

---

## 14. Admin Panel

The admin panel is the central management hub for the platform.

### Dashboard Tabs
The admin panel has several tabs:

**Pending Accounts**
- Lists all users waiting for verification.
- Admins can approve or reject each account.
- Rejections can include a reason.
- Approved users are notified and moved to `Active`.

**All Users**
- Full user directory with search and filtering.
- Shows ID, name, email, role, council, rating, completed count, status, and verification.
- Admins can edit or delete users.
- Includes an "Add Student Manually" button for admin-created accounts.

**Reports**
- Lists all user reports.
- Shows reporter, reported user, request, warning count, and account status.
- Admins can dismiss reports or take action.

**Skills**
- View and manage the skill catalog.
- Admins can add new skills with category and subcategory.

**Logs**
- View the audit trail of admin actions.
- Shows admin ID, action, details, and timestamp.

### Manual User Registration
- Admins can create accounts directly from the admin panel.
- The "Add Student Manually" button opens a dedicated registration form.
- Admin-created accounts are immediately verified and active.
- Admins can choose the role (Student, Faculty, Staff, Admin) and council.

### User Deletion
- Admins can delete any user.
- Deletion is transactional and removes all related data:
  - User skills
  - Assignments (as applicant and as request owner)
  - Messages
  - Notifications
  - Reviews (given and received)
  - Matches
  - Reports (filed and received)
  - Admin action logs
  - Service requests and all related pivot/child records
- This prevents orphaned records and foreign key violations.

---

## 15. Analytics Dashboard

The analytics dashboard provides platform-wide insights for admins.

### Summary Metrics
- Total service requests
- Total active providers
- Total matches
- Transaction completion rate
- Active providers (last 30 days)

### Charts & Trends
- Monthly request volume (last 12 months)
- Monthly match volume (last 12 months)
- Average rating trend (last 12 months)
- Completion rate by skill category

### Rankings
- Top-rated providers (min 3 completed transactions)
- Most active providers (by completed count)
- Most requested skill categories

### CSV Export
- Admins can download the full analytics report as a CSV file.
- The CSV includes all summary metrics, trends, and rankings.

### Caching
- Analytics dashboard data is cached for 60 seconds to reduce database load.
- The CSV export bypasses the cache to ensure fresh data.

---

## 16. Settings

Users can manage personal preferences:

- **Dark mode**: Toggles the site theme (persisted to local storage and session).
- **Email notifications**: Toggle for email alerts (preference stored; actual email sending is not implemented).
- **Profile visibility**: Public or private profile setting.
- **Language**: Preferred language setting (stored for future localization).

Settings are stored as a JSON object on the user record.

---

## 17. Security & Quality Features

### Password Security
- Minimum 8 characters.
- Must contain uppercase and lowercase letters.
- Must contain numbers and symbols.
- Checked against a compromised password database (Have I Been Pwned via Laravel's `uncompromised()` rule).
- Passwords are stored as bcrypt hashes.

### Failed Login Logging
- Failed login attempts are logged with email, IP address, and user agent.

### Rate Limiting
- Login: 5 attempts per minute.
- Registration: 3 attempts per 10 minutes.
- Admin report resolution: 10 attempts per minute.
- User deletion: 5 attempts per minute.
- Analytics report export: 5 attempts per minute.

### Security Headers
- A middleware adds security headers to all responses (e.g., XSS protection, clickjacking protection).

### Middleware Guards
- **Auth**: Requires login.
- **Verified User**: Requires account verification.
- **User Active**: Enforces account status (suspended/banned handling).
- **Admin**: Requires admin role.
- **Throttle**: Rate limiting.

### Data Integrity
- Database transactions wrap multi-step operations (request creation, user deletion, report resolution).
- Foreign key constraints protect referential integrity.
- Unique constraints prevent duplicate emails and student IDs.

---

## 18. Database Architecture

### Core Tables

**users**
- User accounts, profile data, ratings, account status, settings.

**skills**
- Global skill catalog with category and subcategory.

**user_skills**
- Many-to-many link between users and skills, with proficiency level.

**skill_requests**
- Service requests posted by users.

**request_skills**
- Many-to-many link between requests and skills (pivot).

**request_assignments**
- Applications from providers to requests, with status tracking.

**matches**
- Persisted recommender results (provider + request + score).

**reviews**
- Ratings and comments between users.

**reports**
- User reports with status and admin resolution.

**messages**
- Private messages between users.

**notifications**
- User notifications with read status.

**admin_action_logs**
- Audit trail of admin actions.

---

## 19. Deployment & Infrastructure

### Local Development
- Runs on XAMPP (Apache + MySQL/MariaDB + PHP).
- A `start.sh` script is available for Git Bash to start MySQL and the Laravel server.

### Production (Render.com)
- Deployed as a Docker container.
- Uses PostgreSQL as the database.
- Uses a persistent disk for profile picture storage.
- Environment variables configure database connection, app key, and session settings.
- The entrypoint script handles:
  - `.env` generation
  - Environment variable overrides
  - App key generation
  - Config and route cache clearing
  - Database migrations
  - Storage symlink creation
  - Server startup on the correct port

### Health Check
- A `/health` endpoint returns the service status and timestamp.
- Used by Render to verify the service is running.

### Sitemap
- A `/sitemap.xml` endpoint provides the site map for search engines.

---

## 20. Key User Journeys

### Journey 1: New User Registration
1. User visits the registration page.
2. Enters name, email, student ID, password, and council.
3. Account is created with `Pending` status.
4. User is redirected to the pending verification page.
5. Admin reviews and approves the account.
6. User receives a notification and can now log in.

### Journey 2: Creating a Service Request
1. Verified user goes to the dashboard or requests page.
2. Clicks "Create Request".
3. Enters title, description, skills, and service mode.
4. Submits the request.
5. System scores providers and saves matches.
6. Matched providers receive notifications.

### Journey 3: Provider Application
1. Provider sees a matched request in the matches page.
2. Reviews the request details and match score breakdown.
3. Clicks "Apply".
4. Application is saved with `Pending` status.
5. Requester receives a notification.

### Journey 4: Completing a Transaction
1. Requester reviews applications and accepts one provider.
2. Request status moves to `Pending`.
3. Provider and requester communicate via messages.
4. Either party marks the assignment as completed.
5. Request status moves to `Completed`.
6. Requester leaves a review.
7. Provider's rating and completed count are updated.

### Journey 5: Reporting a User
1. User submits a report with a reason.
2. Report appears in the admin panel.
3. Admin reviews and takes action or dismisses.
4. If action is taken, the moderation system escalates the penalty.
5. Both reporter and reported user are notified.

---

## 21. Summary

The Skill Matching System is a full-featured platform that manages the entire lifecycle of skill-based service exchange:

- **Identity**: Registration, verification, roles, account status.
- **Discovery**: Search, filters, skill catalog.
- **Matching**: Weighted multi-factor recommender with cold-start support.
- **Transactions**: Applications, assignments, completion, failure.
- **Trust**: Reviews, ratings, reporting, moderation.
- **Communication**: Private messaging with read receipts.
- **Administration**: User management, skill management, audit logs, analytics.
