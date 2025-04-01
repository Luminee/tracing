<?php

namespace Luminee\Tracing\Views;

use Luminee\Tracing\Supports\App;
use Luminee\Tracing\Views\Drivers\Json;
use Luminee\Tracing\Views\Drivers\Zipkin;

class ViewManager
{
    use App;

    /**
     * Boot the views
     *
     * @param $app
     * @return array
     */
    public function bootViews($app = null): array
    {
        $this->bootApplication($app);

        $views = [];

        foreach ($this->app['config']->get('tracing.views', []) as $driver => $enable) {
            if ($enable) {
                $func = 'create' . ucfirst($driver) . 'View';
                $views[$driver] = $this->$func();
            }
        }

        return $views;
    }

    /**
     * Create Zipkin view
     *
     * @param $app
     * @return Zipkin
     */
    public function createZipkinView($app = null): Zipkin
    {
        return new Zipkin($app);
    }

    public function createJsonView($app = null): Json
    {
        return new Json($app);
    }

}