<?php

namespace Luminee\Tracing;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Luminee\Tracing\Middlewares\TraceRequests;

class TracingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole() && function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/tracing.php' => config_path('tracing.php'),
            ]);
        }

        $this->app[Kernel::class]->pushMiddleware(TraceRequests::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/tracing.php', 'tracing');

        $this->app->singleton(LaravelTracing::class, function ($app) {
            return new LaravelTracing($app);
        });

        $this->app->alias(LaravelTracing::class, 'tracing');

    }
}
