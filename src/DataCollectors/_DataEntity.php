<?php

namespace Luminee\Tracing\DataCollectors;

use Luminee\Tracing\DataCollectors\Concerns\DataEntityDecorator;

abstract class _DataEntity implements DataEntityDecorator
{
    /**
     * @var string
     */
    protected $uuid;
}