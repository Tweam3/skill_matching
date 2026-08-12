<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsService;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index(AnalyticsService $analytics)
    {
        $metrics = Cache::remember('analytics.dashboard', 60, fn () => $analytics->allMetrics());

        return view('admin.analytics', compact('metrics'));
    }
}
