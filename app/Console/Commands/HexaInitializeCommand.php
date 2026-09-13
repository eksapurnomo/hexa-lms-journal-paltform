<?php

namespace App\Console\Commands;

use App\Services\Initialization\PaymentGatewayInitializer;
use App\Services\Initialization\RolePermissionInitializer;
use App\Services\Initialization\SettingInitializer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HexaInitializeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hexa:initialize {--dry-run : Perform a dry run without modifying the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely initialize HexaLMS required foundation data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=====================================');
        $this->info('       HexaLMS Initialization        ');
        $this->info('=====================================');

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN ENABLED - No database changes will be made.');
        }

        $initializers = [
            new SettingInitializer(),
            new RolePermissionInitializer(),
            new PaymentGatewayInitializer(),
        ];

        foreach ($initializers as $initializer) {
            $this->info(PHP_EOL . '>>> Running: ' . $initializer->getName());

            try {
                if (!$isDryRun) {
                    DB::beginTransaction();
                }

                $result = $initializer->initialize($isDryRun);

                foreach ($result->getLogs() as $log) {
                    if (str_starts_with($log, '[CREATE]')) {
                        $this->line('<fg=green>' . $log . '</>');
                    } elseif (str_starts_with($log, '[SKIP]')) {
                        $this->line('<fg=yellow>' . $log . '</>');
                    } else {
                        $this->line($log);
                    }
                }

                if (!$isDryRun) {
                    DB::commit();
                }
            } catch (\Exception $e) {
                if (!$isDryRun) {
                    DB::rollBack();
                }
                
                $this->error('ERROR in ' . $initializer->getName() . ': ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $this->info(PHP_EOL . '=====================================');
        if ($isDryRun) {
            $this->info('No database changes were made.');
        } else {
            $this->info('Initialization completed safely.');
        }

        return Command::SUCCESS;
    }
}
