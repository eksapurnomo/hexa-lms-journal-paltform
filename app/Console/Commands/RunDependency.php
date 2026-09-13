<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunDependency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:dependency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Dependency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        exec('npm run build');
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--class' => 'CODSeeder']);
    }
}
