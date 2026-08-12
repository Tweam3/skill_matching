<?php

namespace App\Console\Commands;

use App\Models\SkillRequest;
use App\Services\Matching\Recommender;
use Illuminate\Console\Command;

class RegenerateMatchesCommand extends Command
{
    protected $signature = 'matches:regenerate
                            {--limit= : Limit the number of open requests to process}
                            {--quiet-output : Suppress per-request progress output}';

    protected $description = 'Recompute match scores for open requests using the recommender algorithm';

    public function handle(Recommender $recommender): int
    {
        $limit = (int) ($this->option('limit') ?: 0);

        $query = SkillRequest::where('Status', 'Open')
            ->with(['skill', 'skills'])
            ->latest('Request_ID');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $requests = $query->get();

        if ($requests->isEmpty()) {
            $this->info('No open requests to process.');

            return self::SUCCESS;
        }

        $this->info("Processing {$requests->count()} open request(s)…");

        $bar = $this->output->createProgressBar($requests->count());
        $total = 0;

        foreach ($requests as $request) {
            $count = $recommender->persistMatches($request);
            $total += $count;
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("Done. {$total} total match(es) persisted across {$requests->count()} request(s).");

        return self::SUCCESS;
    }
}
