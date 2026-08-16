<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,10');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/pending-verification', function () {
    if (Auth::check() && Auth::user()->Is_Verified) {
        return redirect()->route('dashboard');
    }

    return view('auth.pending');
})->name('pending.verification')->middleware('auth');

Route::get('/account/suspended', function () {
    return view('auth.suspended');
})->name('account.suspended')->middleware('auth');

Route::get('/account/banned', function () {
    return view('auth.banned');
})->name('account.banned')->middleware('auth');

Route::middleware(['auth', 'verified.user', 'user.active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile/{id?}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/{id}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/{id}/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/{id}/adjust-picture', [ProfileController::class, 'adjustPicture'])->name('profile.adjustPicture');
    Route::post('/profile/skill/add', [ProfileController::class, 'addSkill'])->name('profile.skill.add');
    Route::post('/profile/skill/remove', [ProfileController::class, 'removeSkill'])->name('profile.skill.remove');
    Route::post('/requests/{id}/complete', [RequestController::class, 'complete'])->name('requests.complete');
    Route::post('/requests/{id}/fail', [RequestController::class, 'fail'])->name('requests.fail');
    Route::get('/requests/{id}/provider-feedback', [RequestController::class, 'providerFeedback'])->name('requests.provider-feedback');
    Route::post('/requests/{id}/provider-feedback', [RequestController::class, 'submitProviderFeedback'])->name('requests.submit-provider-feedback');
    Route::resource('requests', RequestController::class)->only(['index', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/{id}', [MatchController::class, 'show'])->name('matches.show');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::post('/search/categories', [SearchController::class, 'addCategory'])->name('search.categories.add')->middleware('admin');
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/apply', [AssignmentController::class, 'apply'])->name('assignments.apply');
    Route::post('/assignments/{id}/accept', [AssignmentController::class, 'accept'])->name('assignments.accept');
    Route::post('/assignments/{id}/reject', [AssignmentController::class, 'reject'])->name('assignments.reject');
    Route::post('/assignments/{id}/complete', [AssignmentController::class, 'complete'])->name('assignments.complete');
    Route::post('/assignments/{id}/review', [AssignmentController::class, 'review'])->name('assignments.review');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reports', function () {
        $uid = Auth::id();
        $reviewsReceived = \App\Models\Review::where('Reviewed_User_ID', $uid)->with('reviewer')->latest()->get();
        $reviewsGiven = \App\Models\Review::where('Reviewer_ID', $uid)->with('reviewedUser')->latest()->get();

        return view('reports.index', compact('reviewsReceived', 'reviewsGiven'));
    })->name('reports.index');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::post('/skills/add-to-me', [SkillController::class, 'addToMe'])->name('skills.addToMe');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
        Route::post('/admin/skills', [AdminController::class, 'addSkill'])->name('admin.skills');
        Route::post('/admin/users/register', [AdminController::class, 'registerUser'])->name('admin.users.register');
        Route::post('/admin/users/verify', [AdminController::class, 'verifyUser'])->name('admin.users.verify');
        Route::post('/admin/reports/resolve', [AdminController::class, 'resolveReport'])->name('admin.reports.resolve')->middleware('throttle:10,1');
        Route::post('/admin/users/delete', [AdminController::class, 'deleteUser'])->name('admin.users.delete')->middleware('throttle:5,1');
    });
});
