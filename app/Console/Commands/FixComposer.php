<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixComposer extends Command
{
    protected $signature = 'fix:composer';
    protected $description = 'Ensure custom dev script remains unchanged and update dependencies';

    private static bool $alreadyExecuted = false;

    public function handle()
    {
        // Prevent infinite loop
        if (self::$alreadyExecuted) {
            $this->warn('⚠️ fix:composer is already running. Exiting to prevent loop.');
            return;
        }

        self::$alreadyExecuted = true; // Set flag

        $composerPath = base_path('composer.json');

        // Validate if composer.json exists
        if (!File::exists($composerPath)) {
            $this->error('❌ composer.json not found!');
            return;
        }

        $composerContent = json_decode(File::get($composerPath), true);

        // Validate composer.json format
        if (!is_array($composerContent)) {
            $this->error('❌ composer.json is malformed. Aborting.');
            return;
        }

        // Clear Laravel cache before making changes
        $this->info('🔄 Clearing Laravel cache...');
        shell_exec('php artisan config:clear');
        shell_exec('php artisan cache:clear');
        shell_exec('php artisan route:clear');
        shell_exec('php artisan view:clear');

        // Restore custom dev script
        $composerContent['scripts']['dev'] = [
            "Composer\\Config::disableProcessTimeout",
            "php artisan serve & php artisan queue:listen --tries=1 & (npm run dev || echo Vite not installed, skipping...) > nul 2>&1"
        ];

        $updatedContent = json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Ensure JSON encoding was successful
        if ($updatedContent === false) {
            $this->error('❌ Failed to encode composer.json. Aborting.');
            return;
        }

        File::put($composerPath, $updatedContent);
        $this->info('✅ Restored custom dev script in composer.json!');

        // Ensure composer dependencies are installed before updating
        if (!File::exists(base_path('vendor/autoload.php'))) {
            $this->executeCommand('composer install', '🔄 Installing Composer dependencies...');
        }

        // Run Composer update
        if ($this->executeCommand('composer update', '🔄 Updating Composer dependencies...')) {
            $this->info('🚀 Composer dependencies updated successfully!');
        } else {
            $this->error('❌ Failed to update Composer dependencies.');
            return;
        }

        // Ensure NPM dependencies are updated if node_modules exists
        if (File::exists(base_path('node_modules'))) {
            if ($this->executeCommand('npm outdated', '🔄 Checking outdated NPM dependencies...')) {
                $this->executeCommand('npm update --no-save', '🔄 Updating NPM dependencies...');
            }
        }

        // Refresh Composer autoload
        if ($this->executeCommand('composer dump-autoload', '🚀 Refreshing autoload...')) {
            $this->info('✅ Composer autoload refreshed!');
        } else {
            $this->error('❌ Failed to refresh Composer autoload.');
        }

        $this->info('🚀 All dependencies updated successfully!');
    }

    /**
     * Run shell command with logging and error handling.
     */
    protected function executeCommand(string $command, string $message): bool
    {
        $this->info($message);
        $output = shell_exec($command);

        if ($output === null) {
            $this->error('❌ Command failed: ' . $command);
            return false;
        }

        return true;
    }
}
