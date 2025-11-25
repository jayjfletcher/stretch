<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use JayI\Stretch\StretchServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    protected array $serviceProviders = [
        StretchServiceProvider::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        collect($this->serviceProviders)->each(function ($provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        });
    }
}
