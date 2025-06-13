<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupDb extends Command
{
    protected $signature = 'setup:db';
    protected $description = 'Switch .env to database-driven config, update composer.json, and validate changes';

    public function handle()
    {
        // Define the updates for environment files.
        // This command forces these values only when executed.
        // Manual modifications remain untouched until this command is run again.
        $updates = [
            'SESSION_DRIVER=file' => 'SESSION_DRIVER=database',
            'QUEUE_CONNECTION=sync' => 'QUEUE_CONNECTION=database',
            'CACHE_STORE=file' => 'CACHE_STORE=database'
        ];

        // First, check if .env files are updated.
        if ($this->envIsUpdated() && $this->composerScriptsAreUpdated()) {
            $this->info("INFO: Nothing to change. Both .env and composer.json scripts are up-to-date.");
            return;
        }

        // Update .env and .env.example if needed.
        if (!$this->envIsUpdated()) {
            $filesToUpdate = [base_path('.env'), base_path('.env.example')];

            foreach ($filesToUpdate as $filePath) {
                if (!File::exists($filePath)) {
                    $this->error("❌ {$filePath} file not found!");
                    continue;
                }

                $content = File::get($filePath);
                foreach ($updates as $search => $replace) {
                    if (str_contains($content, $search)) {
                        $content = str_replace($search, $replace, $content);
                    }
                }

                File::put($filePath, $content);
                $this->info("✅ Updated {$filePath} for database-driven sessions, queue, and cache.");
            }

            // Verify that the desired values are present.
            foreach ($updates as $replace) {
                if (!str_contains(File::get(base_path('.env')), $replace)) {
                    $this->error("❌ Failed to update .env: {$replace}");
                    return;
                }
                if (!str_contains(File::get(base_path('.env.example')), $replace)) {
                    $this->error("❌ Failed to update .env.example: {$replace}");
                    return;
                }
            }
        } else {
            $this->info("INFO: .env files are already updated.");
        }

        // Update composer.json scripts if needed.
        if (!$this->composerScriptsAreUpdated()) {
            $composerPath = base_path('composer.json');

            if (!File::exists($composerPath)) {
                $this->error('❌ composer.json file not found!');
                return;
            }

            $composerContent = json_decode(File::get($composerPath), true);
            if (!is_array($composerContent)) {
                $this->error('❌ composer.json is malformed. Aborting.');
                return;
            }

            $desiredPostCreate = [
                "@php artisan key:generate --ansi",
                "@php -r \"file_exists('database/database.mysql') || touch('database/database.mysql');\"",
                "@php artisan migrate --graceful --ansi"
            ];
            $desiredDev = [
                "Composer\\Config::disableProcessTimeout",
                "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" --names='server,queue,vite'"
            ];

            $composerContent['scripts']['post-create-project-cmd'] = $desiredPostCreate;
            $composerContent['scripts']['dev'] = $desiredDev;

            File::put($composerPath, json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('✅ Updated composer.json scripts for migration setup and optimized the dev script.');
        } else {
            $this->info("INFO: composer.json scripts already up-to-date.");
        }

        // Verify composer.json changes.
        $composerPath = base_path('composer.json');
        foreach (['php artisan migrate', 'npx concurrently'] as $check) {
            if (!str_contains(File::get($composerPath), $check)) {
                $this->error("❌ Failed to update composer.json: {$check}");
                return;
            }
        }

        // Clear Laravel configuration and application caches.
        if (!$this->executeShellCommand('php artisan config:clear', '🔄 Clearing Laravel config cache...')) {
            return;
        }
        if (!$this->executeShellCommand('php artisan cache:clear', '🔄 Clearing Laravel cache...')) {
            return;
        }
        if (!$this->executeShellCommand('composer dump-autoload', '🔄 Refreshing Composer autoload...')) {
            return;
        }

        $this->info('🚀 Database setup completed successfully!');
    }

    /**
     * Checks if both .env and .env.example already contain the desired settings.
     */
    protected function envIsUpdated(): bool
    {
        $desired = [
            'SESSION_DRIVER=database',
            'QUEUE_CONNECTION=database',
            'CACHE_STORE=database'
        ];

        $envFiles = [base_path('.env'), base_path('.env.example')];

        foreach ($envFiles as $filePath) {
            if (!File::exists($filePath)) {
                return false;
            }
            $content = File::get($filePath);
            foreach ($desired as $line) {
                if (!str_contains($content, $line)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Checks if composer.json scripts for post-create and dev are already updated.
     */
    protected function composerScriptsAreUpdated(): bool
    {
        $composerPath = base_path('composer.json');
        if (!File::exists($composerPath)) {
            return false;
        }
        $composerContent = json_decode(File::get($composerPath), true);
        if (!is_array($composerContent) || !isset($composerContent['scripts'])) {
            return false;
        }
        $desiredPostCreate = [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.mysql') || touch('database/database.mysql');\"",
            "@php artisan migrate --graceful --ansi"
        ];
        $desiredDev = [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" --names='server,queue,vite'"
        ];

        if (
            !isset($composerContent['scripts']['post-create-project-cmd']) ||
            $composerContent['scripts']['post-create-project-cmd'] !== $desiredPostCreate
        ) {
            return false;
        }
        if (
            !isset($composerContent['scripts']['dev']) ||
            $composerContent['scripts']['dev'] !== $desiredDev
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the .env file appears correctly formatted.
     */
    protected function isValidEnvFile(string $filePath): bool
    {
        $content = File::get($filePath);
        return str_contains($content, 'APP_KEY') && str_contains($content, 'DB_CONNECTION');
    }

    /**
     * Runs shell commands with proper error handling.
     */
    protected function executeShellCommand(string $command, string $message): bool
    {
        $this->info($message);
        $output = shell_exec($command);
        if ($output === null) {
            $this->error("❌ Command failed: {$command}");
            return false;
        }
        return true;
    }
}
