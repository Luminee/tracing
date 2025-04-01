<?php

namespace Luminee\Tracing\Facades;

use Illuminate\Support\Facades\Facade;
use Luminee\Tracing\LaravelTracing;

/**
 * @see LaravelTracing
 *
 * @method static string startMeasure(string $label = null, string $parent_uuid = null, string $collector = null)
 * @method static void stopMeasure(string $uuid, $end = null, array $params = array())
 * @method static string|null getCurrentMeasureUuid()
 */
class Tracing extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return LaravelTracing::class;
    }
}
