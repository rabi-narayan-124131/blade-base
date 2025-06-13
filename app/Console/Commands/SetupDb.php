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
        // Files to update: .env and .env.example
        $filesToUpdate = [base_path('.env'), base_path('.env.example')];

        foreach ($filesToUpdate as $filePath) {
            if (!File::exists($filePath)) {
                $this->error("❌ {$filePath} file not found!");
                continue;
            }

            $content = File::get($filePath);
            $updates = [
                'SESSION_DRIVER=file' => 'SESSION_DRIVER=database',
                'QUEUE_CONNECTION=sync' => 'QUEUE_CONNECTION=database',
                'CACHE_STORE=file' => 'CACHE_STORE=database'
            ];

            foreach ($updates as $search => $replace) {
                if (str_contains($content, $search)) {
                    $content = str_replace($search, $replace, $content);
                }
            }

            File::put($filePath, $content);
            $this->info("✅ Updated {$filePath} for database-driven sessions, queue, and cache.");
        }

        // Verify .env changes
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

        // Update composer.json for database migration and modify dev script
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

        $composerContent['scripts']['post-create-project-cmd'] = [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.mysql') || touch('database/database.mysql');\"",
            "@php artisan migrate --graceful --ansi"
        ];

        $composerContent['scripts']['dev'] = [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" --names='server,queue,vite'"
        ];

        File::put($composerPath, json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('✅ Updated composer.json to enable migration setup and optimize the dev script.');

        // Verify composer.json changes
        foreach (['php artisan migrate', 'npx concurrently'] as $check) {
            if (!str_contains(File::get($composerPath), $check)) {
                $this->error("❌ Failed to update composer.json: {$check}");
                return;
            }
        }

        // Clear Laravel configuration cache
        $this->call('config:clear');

        // Clear Laravel cache
        $this->call('cache:clear');

        $this->info('🚀 Database setup completed successfully!');
    }
}
