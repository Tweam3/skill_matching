<?php

namespace App\Services\Analytics;

use App\Models\Assignment;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Monthly request volume — count of requests created per month
     * over the last $months periods.
     */
    public function monthlyRequestVolume(int $months = 12): array
    {
        return $this->monthlyCounts('skill_requests', 'Created_At', $months);
    }

    /**
     * Monthly match volume — count of provider matches created per month
     * over the last $months periods.
     */
    public function monthlyMatchVolume(int $months = 12): array
    {
        return $this->monthlyCounts('matches', 'Matched_At', $months);
    }

    /**
     * Top-rated providers — users with the highest average rating who have
     * completed at least 3 transactions.
     */
    public function topRatedProviders(int $limit = 5): Collection
    {
        return User::query()
            ->where('Role', 'Student')
            ->where('Account_Status', 'Active')
            ->where('Total_Completed', '>=', 3)
            ->orderByDesc('Avg_Rating')
            ->orderByDesc('Total_Completed')
            ->limit($limit)
            ->with('skills')
            ->get();
    }

    /**
     * Transaction completion rate — percentage of resolved assignments
     * (Completed out of Completed + Failed) across the platform.
     */
    public function transactionCompletionRate(): float
    {
        $resolved = Assignment::whereIn('Status', ['Completed', 'Failed'])->count();
        $completed = Assignment::where('Status', 'Completed')->count();

        if ($resolved === 0) {
            return 0.0;
        }

        return round(($completed / $resolved) * 100, 1);
    }

    /**
     * Completion rate broken down by skill category.
     */
    public function completionRateByCategory(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $results = DB::select("
                SELECT s.\"Category\", COUNT(*) as total, SUM(CASE WHEN a.Status = 'Completed' THEN 1 ELSE 0 END) as completed
                FROM skills as s
                INNER JOIN skill_requests as r ON s.\"Skill_ID\" = r.\"Skill_ID\"
                INNER JOIN request_assignments as a ON r.\"Request_ID\" = a.\"Request_ID\"
                WHERE a.\"Status\" IN ('Completed', 'Failed')
                GROUP BY s.\"Category\"
                ORDER BY s.\"Category\" ASC
            ");
        } else {
            $results = DB::table('skills as s')
                ->join('skill_requests as r', 's.Skill_ID', '=', 'r.Skill_ID')
                ->join('request_assignments as a', 'r.Request_ID', '=', 'a.Request_ID')
                ->selectRaw('s.Category, COUNT(*) as total, SUM(CASE WHEN a.Status = \'Completed\' THEN 1 ELSE 0 END) as completed')
                ->whereIn('a.Status', ['Completed', 'Failed'])
                ->groupBy('s.Category')
                ->orderBy('s.Category')
                ->get();
        }

        $labels = [];
        $data = [];

        foreach ($results as $row) {
            $labels[] = $row->Category;
            $rate = $row->total > 0 ? (float) $row->completed / $row->total * 100 : 0;
            $data[] = round($rate, 1);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Active provider count — unique providers who applied to requests
     * in the last $days days.
     */
    public function activeProviderCount(int $days = 30): int
    {
        return (int) Assignment::where('Created_At', '>=', now()->subDays($days))
            ->where('Status', '!=', 'Rejected')
            ->pluck('User_ID')
            ->unique()
            ->count();
    }

    /**
     * Aggregate all KPIs into a single payload for the dashboard view.
     */
    public function allMetrics(): array
    {
        $months = 12;
        $labels = $this->generateMonthLabels($months);

        return [
            'monthly_request_volume' => [
                'labels' => $labels,
                'data' => $this->monthlyRequestVolume($months)['data'],
            ],
            'top_rated_providers' => $this->topRatedProviders(),
            'transaction_completion_rate' => $this->transactionCompletionRate(),
            'monthly_match_volume' => [
                'labels' => $labels,
                'data' => $this->monthlyMatchVolume($months)['data'],
            ],
            'completion_rate_by_category' => $this->completionRateByCategory(),
            'active_provider_count' => $this->activeProviderCount(),
            'total_requests' => SkillRequest::count(),
            'total_providers' => User::where('Role', 'Student')->where('Account_Status', 'Active')->count(),
            'total_assignments' => Assignment::count(),
            'total_matches' => UserMatch::count(),
        ];
    }

    /**
     * Generate month labels like ["Sep 2025", "Oct 2025", ...] for the last $months.
     */
    protected function generateMonthLabels(int $months): array
    {
        return collect(range($months - 1, 0))
            ->map(fn ($i) => now()->subMonths($i)->format('M Y'))
            ->toArray();
    }

    /**
     * Query a table for counts grouped by month over the last $months periods.
     */
    protected function monthlyCounts(string $table, string $column, int $months): array
    {
        $since = now()->subMonths($months)->startOfMonth();
        $driver = DB::connection()->getDriverName();
        $monthExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char(\"{$column}\", 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };

        $query = DB::table($table)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count");

        if ($driver === 'pgsql') {
            $query->whereRaw("\"{$column}\" >= ?", [$since]);
        } else {
            $query->where($column, '>=', $since);
        }

        $rawData = $query
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
}
