<?php

namespace Luminee\Tracing\Supports;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;

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

    public function bootApplication(Application $app = null)
    {
        $this->app = $app ?? app();
        $this->config = $this->app['config'];
    }
}