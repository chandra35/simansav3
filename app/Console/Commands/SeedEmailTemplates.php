<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use Illuminate\Console\Command;

class SeedEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:seed-templates {--force : Force update existing templates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default email templates to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding email templates...');
        
        EmailTemplate::seedDefaults();
        
        $count = EmailTemplate::count();
        $this->info("Done! Total templates: {$count}");
        
        // List all templates
        $templates = EmailTemplate::all(['code', 'name', 'is_active']);
        $this->table(['Code', 'Name', 'Active'], $templates->map(fn($t) => [
            $t->code,
            $t->name,
            $t->is_active ? 'Yes' : 'No'
        ])->toArray());
        
        return Command::SUCCESS;
    }
}
