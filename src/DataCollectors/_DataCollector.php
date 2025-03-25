<?php

namespace Luminee\Tracing\DataCollectors;

use Luminee\Tracing\DataCollectors\Concerns\DataCollectorDecorator;
use Luminee\Tracing\Generators\UuidGenerator;

abstract class _DataCollector implements DataCollectorDecorator
{
    use UuidGenerator;

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}