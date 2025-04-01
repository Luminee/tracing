<?php

namespace Luminee\Tracing\DataCollectors\Concerns;

interface DataCollectorDecorator
{
    public function collect(): array;
}