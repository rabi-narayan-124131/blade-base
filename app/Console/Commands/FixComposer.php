<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixComposer extends Command
{
    protected $signature = 'fix:composer';
    protected $description = 'Ensure custom dev script remains unchanged and update dependencies';

    public function handle()
    {
        $composerPath = base_path('composer.json');

        if (!File::exists($composerPath)) {
            $this->error('❌ composer.json not found!');
            return;
        }

        $composerContent = json_decode(File::get($composerPath), true);

        if (!is_array($composerContent)) {
            $this->error('❌ composer.json is malformed. Aborting.');
            return;
        }

        // Restore custom dev script
        $composerContent['scripts']['dev'] = [
            "Composer\\Config::disableProcessTimeout",
            "php artisan serve & php artisan queue:listen --tries=1 & (npm run dev || echo Vite not installed, skipping...) > nul 2>&1"
        ];

        File::put($composerPath, json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('✅ Restored custom dev script in composer.json!');

        // Run Composer update directly without checking outdated packages
        $this->info('🔄 Updating Composer dependencies...');
        shell_exec('composer update');

        // Ensure NPM dependencies are updated but not installed automatically
        if (File::exists(base_path('node_modules'))) {
            $this->info('🔄 Checking outdated NPM dependencies...');
            shell_exec('npm outdated');

            $this->info('🔄 Updating NPM dependencies...');
            shell_exec('npm update --no-save');
        } else {
            $this->warn('⚠️ Skipping NPM update: No node_modules detected.');
        }

        $this->info('🚀 All dependencies updated successfully!');
    }
}
