<?php

namespace Luminee\Tracing\Supports;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;

trait App
{
    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var Dispatcher|null
     */
    protected $events;

    public function bootApplication(Application $app = null)
    {
        $this->app = $app ?? app();
        $this->config = $this->app['config'];
        $this->events = $this->app['events'] ?? null;
    }
}