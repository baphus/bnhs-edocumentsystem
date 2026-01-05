<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductionOptimizationService;

class OptimizeProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:production {--clear : Clear caches before optimization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application for production deployment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            if ($this->option('clear')) {
                $this->info('Clearing all caches...');
                ProductionOptimizationService::clearAllCaches();
            }

            $this->info('Starting production optimization...');
            ProductionOptimizationService::optimize();

            $this->info('✓ Production optimization completed successfully');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Optimization failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
