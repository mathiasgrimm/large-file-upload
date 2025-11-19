<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->largeFileUploadWorkaround();
    }

    /**
     * Injects addition options to the default disk to allow faster Storage::copy and Storage::move
     */
    protected function largeFileUploadWorkaround()
    {
        $defaultDisk = config('filesystems.default');

        if (laravel_cloud() && ! config("filesystems.disks.{$defaultDisk}.options")) {
            config()->set("filesystems.disks.{$defaultDisk}.options", [
                'mup_threshold' => 1024 * 1024 * 16,
                'concurrency' => 50,
            ]);
        }
    }
}
