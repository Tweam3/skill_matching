# PHP Code Reference

Unique PHP patterns used across controllers, models, services, and migrations. No duplicates.

---

## CONTROLLER PATTERNS

### Basic Index Method (fetch all + view)

```php
public function index()
{
    $users = User::all();
    $pendingUsers = User::where('Is_Verified', false)->get();
    $skills = Skill::orderBy('Category')->orderBy('Skill_Title')->get();

    return view('admin.index', compact('users', 'pendingUsers', 'skills'));
}
```

### Index with Joins and Selects

```php
public function index()
{
    $reports = Report::select('reports.*', 'u1.Full_Name as Reporter', 'u2.Full_Name as Reported')
        ->join('users as u1', 'u1.User_ID', '=', 'reports.Reporter_ID')
        ->join('users as u2', 'u2.User_ID', '=', 'reports.Reported_User_ID')
        ->latest('reports.Created_At')
        ->get();

    return view('admin.index', compact('reports'));
}
```

### Show Form Method

```php
public function showCreateUserForm()
{
    return view('admin.users.create');
}
```

### Validation + Create with try/catch

```php
public function registerUser(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,Email'],
        'password' => ['required', 'string', 'min:8', 'confirmed',
            Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        'role' => ['required', 'in:Student,Faculty,Staff,Admin'],
        'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated',
            'required_if:role,Student,Faculty'],
    ]);
    try {
        $council = in_array($validated['role'], ['Student', 'Faculty'])
            ? $validated['council'] : null;
        User::create([
            'Full_Name' => $validated['name'],
            'Email' => $validated['email'],
            'Password_Hash' => bcrypt($validated['password']),
            'Role' => $validated['role'],
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Council' => $council,
        ]);
        $this->logAction('register_user', 'Registered user: '.$validated['email']);
    } catch (\Exception $e) {
        Log::error('Admin register user failed', [
            'email' => $validated['email'],
            'error' => $e->getMessage(),
            'admin_id' => Auth::id(),
        ]);
        return back()->with('error', 'Failed to register user.');
    }

    return back()->with('success', 'User registered successfully.');
}
```

### Decision Branching (Approve/Reject)

```php
public function verifyUser(Request $request)
{
    $request->validate([
        'user_id' => ['required', 'integer', 'exists:users,User_ID'],
        'decision' => ['required', 'in:approve,reject'],
    ]);
    $user = User::findOrFail($request->user_id);
    if ($request->decision === 'approve') {
        $user->update(['Is_Verified' => true, 'Account_Status' => 'Active']);
        Notification::create([...]);
        $this->logAction('verify_user', 'Approved user ID: '.$request->user_id);
    } else {
        $user->update(['Is_Verified' => false, 'Rejection_Reason' => $request->input('rejection_reason')]);
        Notification::create([...]);
        $this->logAction('verify_user', 'Rejected user ID: '.$request->user_id);
    }

    return back();
}
```

### DB Transaction for Complex Operations

```php
DB::transaction(function () use ($uid) {
    UserSkill::where('User_ID', $uid)->delete();
    Assignment::where('User_ID', $uid)->delete();
    Message::where('Sender_ID', $uid)->orWhere('Receiver_ID', $uid)->delete();
    Notification::where('User_ID', $uid)->delete();
    Review::where('Reviewer_ID', $uid)->orWhere('Reviewed_User_ID', $uid)->delete();
    UserMatch::where('Matched_User_ID', $uid)->delete();
    Report::where('Reporter_ID', $uid)->orWhere('Reported_User_ID', $uid)->delete();
    SkillRequest::where('User_ID', $uid)->delete();
    User::where('User_ID', $uid)->delete();
});
```

### Login with Suspension Check

```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
    $user = User::where('Email', $credentials['email'])->first();
    if (! $user || ! Hash::check($credentials['password'], $user->Password_Hash)) {
        Log::warning('Failed login attempt', [...]);
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    // Suspension check with 3-day auto-recovery
    $status = strtolower($user->Account_Status ?? 'active');
    if ($status === 'suspended') {
        if ($user->Suspended_At && $user->Suspended_At->copy()->addDays(3)->isPast()) {
            $user->update(['Account_Status' => 'Active', 'Suspended_At' => null]);
        } else {
            return back()->withErrors(['email' => 'Account suspended.']);
        }
    }

    Auth::login($user);
    $request->session()->regenerate();

    return $user->Is_Verified
        ? redirect()->route($user->Role === 'Admin' ? 'admin.index' : 'dashboard')
        : redirect()->route('pending.verification');
}
```

### Registration with Approval Logic

```php
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,Email'],
        'student_id' => ['required', 'string', 'max:50', 'unique:users,Student_ID',
            'regex:/^\d{4}-\d{4}-[A-Z]$/i'],
        'password' => ['required', 'string', 'confirmed',
            Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        'role' => ['nullable', 'string', 'in:Student,Faculty,Staff,Admin'],
        'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated',
            'required_if:role,Student,Faculty'],
    ], [
        'password.confirmed' => 'Password confirmation does not match.',
        'student_id.regex' => 'Student ID must follow: YYYY-XXXX-X (e.g., 2023-1234-M).',
    ]);

    $verified = false;
    $accountStatus = 'Pending';
    if (Auth::check() && Auth::user()->Role === 'Admin') {
        $role = $request->input('role', 'Student');
        $verified = $request->has('as_admin');
        $accountStatus = $verified ? 'Active' : 'Pending';
    } elseif (in_array(strtoupper($validated['student_id']), $approvedStudentIds)) {
        $verified = true;
        $accountStatus = 'Active';
    }

    $user = User::create([
        'Full_Name' => $validated['name'],
        'Email' => $validated['email'],
        'Student_ID' => $validated['student_id'],
        'Password_Hash' => Hash::make($validated['password']),
        'Role' => $validated['role'] ?? 'Student',
        'Is_Verified' => $verified,
        'Account_Status' => $accountStatus,
    ]);
    Auth::login($user);
}
```

### Search with Dynamic Query Building

```php
public function index(Request $request)
{
    $searchIn = $request->input('search_in', 'requests');

    $query = SkillRequest::where('Status', 'Open')->with(['skill', 'skills', 'user']);

    if ($serviceMode) {
        $query->where('Service_Mode', $serviceMode);
    }

    if (! empty($categories)) {
        $query->where(function ($q) use ($categories) {
            $q->whereIn('Skill_ID', Skill::whereIn('Category', $categories)->pluck('Skill_ID'))
                ->orWhereHas('skills', function ($sq) use ($categories) {
                    $sq->whereIn('Category', $categories);
                });
        });
    }

    $results = $query->orderBy('Created_At', 'desc')->paginate(20)->withQueryString();

    if (in_array($searchIn, ['providers', 'both'])) {
        $providerQuery = User::where('Role', '!=', 'Admin')
            ->where('Account_Status', 'Active');

        if ($keyword) {
            $providerQuery->where(function ($q) use ($keyword) {
                $q->where('Full_Name', 'like', '%'.$keyword.'%')
                    ->orWhere('Email', 'like', '%'.$keyword.'%')
                    ->orWhereHas('skills', function ($sq) use ($keyword) {
                        $sq->where('Skill_Title', 'like', '%'.$keyword.'%');
                    });
            });
        }

        $providers = $providerQuery->with('skills')->orderByDesc('Avg_Rating')
            ->paginate(20, ['*'], 'page_providers')->withQueryString();
    }

    return view('search.index', compact(
        'serviceMode', 'categories', 'subcategories', 'keyword',
        'allCategories', 'allSubcategories', 'results', 'providers'
    ));
}
```

### File Download (CSV Export)

```php
public function report(AnalyticsService $analytics)
{
    $metrics = $analytics->allMetrics();
    $csv = [];
    $csv[] = ['Summary', 'Total Service Requests', $metrics['total_requests']];
    $csv[] = ['', 'Total Active Providers', $metrics['total_providers']];

    $headers = [
        'Content-type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="analytics-report.csv"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, must-revalidate',
        'Expires' => 'Mon, 26 Jul 1976 12:00:00 GMT',
    ];

    $callback = function () use ($csv) {
        $file = fopen('php://output', 'w');
        foreach ($csv as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
```

### Request Authorization Check

```php
public function complete($id)
{
    $request = SkillRequest::with('assignments')->findOrFail($id);
    $uid = Auth::id();
    if ($uid != $request->User_ID) {
        abort(403);
    }
    // ... operation
}
```

### Request with Multiple Status Filtering

```php
$appliedRequests = SkillRequest::whereHas('assignments', function ($q) use ($uid) {
    $q->where('User_ID', $uid)->where('Status', '!=', 'Rejected');
})
    ->with(['skill', 'assignments' => function ($q) use ($uid) {
        $q->where('User_ID', $uid)->where('Status', '!=', 'Rejected');
    }])
    ->get()
    ->sortBy(function ($r) {
        $order = ['Pending' => 1, 'Completed' => 2, 'Cancelled' => 3];
        return $order[$r->Status] ?? 4;
    })
    ->sortByDesc(function ($r) { return $r->Request_ID; })
    ->values();
```

---

## MODEL PATTERNS

### User Model with Custom Primary Key and Casts

```php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'User_ID';
    public $timestamps = false;

    protected $fillable = [
        'Full_Name', 'Email', 'Password_Hash', 'Role', 'Council',
        'Student_ID', 'Profile_Picture', 'Bio', 'Created_At',
        'Avg_Rating', 'Total_Completed', 'Is_Verified',
        'Account_Status', 'Warning_Count', 'Suspended_At',
        'Rejection_Reason', 'settings', 'Service_Modes',
    ];

    protected $hidden = ['Password_Hash', 'remember_token'];

    protected $casts = [
        'Is_Verified' => 'boolean',
        'Created_At' => 'datetime',
        'settings' => 'array',
        'Service_Modes' => 'array',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->Full_Name;
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['Password_Hash'] = bcrypt($value);
        }
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills', 'User_ID', 'Skill_ID')
            ->withPivot('Proficiency');
    }
}
```

### Generic Model with Casts

```php
class UserSkill extends Model
{
    protected $table = 'user_skills';
    public $timestamps = false;

    protected $fillable = ['User_ID', 'Skill_ID', 'Proficiency'];
}
```

### Model with Casts and Timestamps

```php
class UserMatch extends Model
{
    protected $table = 'matches';
    public $timestamps = false;

    protected $fillable = ['Matched_User_ID', 'Request_ID', 'Match_Score', 'Matched_At'];

    protected $casts = [
        'Matched_At' => 'datetime',
        'Match_Score' => 'float',
    ];
}
```

### Model with Relationships

```php
class SkillRequest extends Model
{
    protected $table = 'skill_requests';
    public $timestamps = false;

    protected $fillable = ['User_ID', 'Skill_ID', 'Title', 'Description', 'Status', 'Service_Mode', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'Skill_ID', 'Skill_ID');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'request_skills', 'Request_ID', 'Skill_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'Request_ID', 'Request_ID');
    }

    public function matches()
    {
        return $this->hasMany(UserMatch::class, 'Request_ID', 'Request_ID');
    }
}
```

### Model with BelongsTo Relationship

```php
class AdminActionLog extends Model
{
    protected $table = 'admin_action_logs';
    protected $primaryKey = 'Log_ID';
    public $timestamps = false;

    protected $fillable = ['Admin_ID', 'Action', 'Details', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Admin_ID', 'User_ID');
    }
}
```

---

## SERVICE PATTERNS

### Recommender Scoring with Weighted Dimensions

```php
class Recommender
{
    public const WEIGHT_SKILL = 'skill_overlap';
    public const WEIGHT_CATEGORY = 'category_coverage';
    public const WEIGHT_SERVICE_MODE = 'service_mode';
    public const WEIGHT_PROFILE_TAGS = 'profile_tags';
    public const WEIGHT_RATING = 'rating';
    public const WEIGHT_PROFILE = 'profile_quality';

    protected array $weights;
    protected float $minMatchScore;
    protected int $maxCompletedForConfidence;
    protected int $maxCompletedForExperience;
    protected float $maxRating;
    protected int $defaultLimit;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('matching') ?? [];
        $this->weights = $config['weights'] ?? [
            self::WEIGHT_SKILL => 0.30,
            self::WEIGHT_CATEGORY => 0.20,
            self::WEIGHT_SERVICE_MODE => 0.15,
            self::WEIGHT_PROFILE_TAGS => 0.15,
            self::WEIGHT_RATING => 0.12,
            self::WEIGHT_PROFILE => 0.08,
        ];
        $this->minMatchScore = $config['min_match_score'] ?? 15.0;
    }

    protected function weightedScore(float $skill, float $category, float $serviceMode,
        float $profileTags, float $rating, float $profile): float
    {
        $weights = $this->weights;
        $total = array_sum([
            $weights[self::WEIGHT_SKILL] ?? 0,
            $weights[self::WEIGHT_CATEGORY] ?? 0,
            $weights[self::WEIGHT_SERVICE_MODE] ?? 0,
            $weights[self::WEIGHT_PROFILE_TAGS] ?? 0,
            $weights[self::WEIGHT_RATING] ?? 0,
            $weights[self::WEIGHT_PROFILE] ?? 0,
        ]);
        if ($total <= 0) return 0.0;

        return (($skill * ($weights[self::WEIGHT_SKILL] ?? 0))
            + ($category * ($weights[self::WEIGHT_CATEGORY] ?? 0))
            + ($serviceMode * ($weights[self::WEIGHT_SERVICE_MODE] ?? 0))
            + ($profileTags * ($weights[self::WEIGHT_PROFILE_TAGS] ?? 0))
            + ($rating * ($weights[self::WEIGHT_RATING] ?? 0))
            + ($profile * ($weights[self::WEIGHT_PROFILE] ?? 0))) / $total;
    }
}
```

### Cold-Start Rule (Prior Rating)

```php
protected function ratingScore(User $provider): float
{
    $hasRatings = (int) $provider->Total_Completed > 0 && (float) $provider->Avg_Rating > 0;

    if (! $hasRatings) {
        // Cold-start: unrated providers receive neutral prior rating
        $prior = config('matching.cold_start_prior_rating', 3.0);
        $normalized = $this->maxRating > 0 ? (float) $prior / $this->maxRating : 0.0;
        return $normalized * 0.5;
    }

    $normalized = $this->maxRating > 0 ? (float) $provider->Avg_Rating / $this->maxRating : 0.0;
    $confidence = min((int) $provider->Total_Completed / $this->maxCompletedForConfidence, 1.0);
    return $normalized * (0.5 + 0.5 * $confidence);
}
```

### Service Mode Compatibility

```php
protected function serviceModeScore(SkillRequest $request, User $provider): float
{
    $requestMode = $request->Service_Mode;
    if (! $requestMode) return 0.5;

    $providerModes = $provider->Service_Modes;
    if (empty($providerModes)) return 0.5;

    if (in_array($requestMode, $providerModes, true)) return 1.0;

    if ($requestMode === 'Hybrid') {
        $hasRemote = in_array('Remote', $providerModes, true);
        $hasF2F = in_array('Face-to-Face', $providerModes, true);
        if ($hasRemote || $hasF2F) return 1.0;
    }

    return 0.0;
}
```

### Profile Quality with Trust Calculation

```php
protected function profileQualityScore(User $provider): float
{
    $isVerified = $provider->Is_Verified ? 1.0 : 0.0;
    $isActive = $provider->Account_Status === 'Active' ? 1.0 : 0.0;
    $experience = min((int) $provider->Total_Completed / $this->maxCompletedForExperience, 1.0);
    $trust = $isVerified * 0.6 + $isActive * 0.4;
    return $trust * (0.3 + 0.7 * $experience);
}
```

### Evaluation Metrics (Precision@K, Recall@K, MRR, MAP)

```php
public function evaluate(array $testCases, int $k = 5): array
{
    $precisions = []; $recalls = []; $reciprocalRanks = []; $averagePrecisions = [];

    foreach ($testCases as $case) {
        $ranked = $this->rankForRequest($case['request']);
        $rankedIds = array_map(fn ($r) => $r['user']->User_ID, $ranked);

        $topK = array_slice($rankedIds, 0, $k);
        $hitsInTopK = count(array_intersect($topK, $case['successful_provider_ids']));
        $precisions[] = $hitsInTopK / $k;
        $recalls[] = min($hitsInTopK / count($case['successful_provider_ids']), 1.0);

        $rr = 0.0;
        foreach ($rankedIds as $rank => $id) {
            if (in_array($id, $case['successful_provider_ids'], true)) {
                $rr = 1.0 / ($rank + 1);
                break;
            }
        }
        $reciprocalRanks[] = $rr;

        $ap = 0.0; $hitCount = 0;
        foreach ($rankedIds as $rank => $id) {
            if (in_array($id, $case['successful_provider_ids'], true)) {
                $hitCount++;
                $ap += $hitCount / ($rank + 1);
            }
        }
        $averagePrecisions[] = $ap / min(count($case['successful_provider_ids']), count($rankedIds));
    }

    return [
        'precision_at_k' => round(array_sum($precisions) / count($precisions), 4),
        'recall_at_k' => round(array_sum($recalls) / count($recalls), 4),
        'mrr' => round(array_sum($reciprocalRanks) / count($reciprocalRanks), 4),
        'map' => round(array_sum($averagePrecisions) / count($averagePrecisions), 4),
    ];
}
```

### Analytics Service with Database Driver Detection

```php
public function completionRateByCategory(): array
{
    $driver = DB::connection()->getDriverName();

    if ($driver === 'pgsql') {
        $results = DB::select("
            SELECT s.\"Category\", COUNT(*) as total, SUM(CASE WHEN a.\"Status\" = 'Completed' THEN 1 ELSE 0 END) as completed
            FROM skills as s
            INNER JOIN skill_requests as r ON s.\"Skill_ID\" = r.\"Skill_ID\"
            INNER JOIN request_assignments as a ON r.\"Request_ID\" = a.\"Request_ID\"
            WHERE a.\"Status\" IN ('Completed', 'Failed')
            GROUP BY s.\"Category\"
        ");
    } else {
        $results = DB::table('skills as s')
            ->join('skill_requests as r', 's.Skill_ID', '=', 'r.Skill_ID')
            ->join('request_assignments as a', 'r.Request_ID', '=', 'a.Request_ID')
            ->selectRaw('s.Category, COUNT(*) as total, SUM(CASE WHEN a.Status = \'Completed\' THEN 1 ELSE 0 END) as completed')
            ->whereIn('a.Status', ['Completed', 'Failed'])
            ->groupBy('s.Category')
            ->get();
    }
    // ... process results
}
```

### Monthly Aggregations with Match Expression

```php
protected function monthlyCounts(string $table, string $column, int $months): array
{
    $monthExpr = match (DB::connection()->getDriverName()) {
        'sqlite' => "strftime('%Y-%m', {$column})",
        'pgsql' => "to_char(\"{$column}\", 'YYYY-MM')",
        default => "DATE_FORMAT({$column}, '%Y-%m')",
    };

    $rawData = DB::table($table)
        ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
        ->where($column, '>=', now()->subMonths($months)->startOfMonth())
        ->groupBy('month')
        ->pluck('count', 'month')
        ->toArray();

    $data = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $key = now()->subMonths($i)->format('Y-m');
        $data[] = (int) ($rawData[$key] ?? 0);
    }

    return ['labels' => $this->generateMonthLabels($months), 'data' => $data];
}
```

### Month Label Generation

```php
protected function generateMonthLabels(int $months): array
{
    return collect(range($months - 1, 0))
        ->map(fn ($i) => now()->subMonths($i)->format('M Y'))
        ->toArray();
}
```

---

## MIGRATION PATTERNS

### Creating Tables with Foreign Keys

```php
Schema::create('users', function (Blueprint $table) {
    $table->id('User_ID');
    $table->string('Full_Name');
    $table->string('Email')->unique();
    $table->string('Password_Hash');
    $table->string('Role')->default('Student');
    $table->string('Student_ID')->nullable();
});
```

```php
Schema::create('skill_requests', function (Blueprint $table) {
    $table->id('Request_ID');
    $table->foreignId('User_ID')->constrained('users', 'User_ID');
    $table->foreignId('Skill_ID')->constrained('skills', 'Skill_ID');
    $table->string('Title');
    $table->text('Description');
    $table->string('Status')->default('Open');
    $table->string('Service_Mode')->default('Remote');
    $table->timestamp('Created_At')->useCurrent();
});
```

### Adding JSON Columns

```php
Schema::table('users', function (Blueprint $table) {
    $table->json('Service_Modes')->nullable()->after('Bio');
});
```

### Adding Indexes (Idempotent Pattern)

```php
$INDEXES = [
    'user_skills' => ['User_ID', 'Skill_ID'],
    'matches' => ['Request_ID', 'Matched_User_ID'],
    'reviews' => ['Reviewed_User_ID', 'Created_At'],
    'reports' => ['Reported_User_ID', 'Status'],
    'notifications' => ['User_ID', 'Status'],
    'admin_action_logs' => ['Admin_ID'],
];

foreach ($INDEXES as $table => $columns) {
    foreach ($columns as $column) {
        if (! Schema::hasTable($table)) continue;
        $indexName = $table.'_'.$column.'_index';
        if (! $this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                $table->index($column, $indexName);
            });
        }
    }
}
```

### Index Existence Check (Database-Agnostic)

```php
private function indexExists(string $table, string $indexName): bool
{
    $driver = Schema::getConnection()->getDriverName();

    if ($driver === 'pgsql') {
        $result = DB::select(
            'SELECT 1 FROM pg_indexes WHERE schemaname = ? AND tablename = ? AND indexname = ?',
            ['public', $table, $indexName]
        );
    } else {
        $result = DB::select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
            [$indexName]
        );
    }

    return ! empty($result);
}
```

## MIDDLEWARE/POLICY PATTERNS

### Account Status Check with Suspension

```php
// In EnsureUserIsActive middleware or AuthController
$status = strtolower($user->Account_Status ?? 'active');
if ($status === 'suspended') {
    $suspendedAt = $user->Suspended_At;
    if ($suspendedAt && $suspendedAt->copy()->addDays(3)->isPast()) {
        $user->update(['Account_Status' => 'Active', 'Suspended_At' => null]);
    } else {
        Auth::logout();
        return redirect()->route('login')->withErrors(['email' => 'Account suspended.']);
    }
}
if ($status === 'banned') {
    Auth::logout();
    return redirect()->route('login')->withErrors(['email' => 'Account banned.']);
}
```

### Authorization Guard

```php
if ($uid != $request->User_ID) {
    abort(403);
}
```

---

## LOG AND ERROR HANDLING

```php
// Failed login logging
Log::warning('Failed login attempt', [
    'email' => $credentials['email'],
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

```php
// Error handling in try/catch
try {
    // ... operation
} catch (\Exception $e) {
    Log::error('Operation failed', [
        'user_id' => $uid,
        'admin_id' => Auth::id(),
        'error' => $e->getMessage(),
    ]);
    return back()->with('error', 'Operation failed. Please try again.');
}
```

## HEALTH CHECK

```php
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
})->name('health');
```
