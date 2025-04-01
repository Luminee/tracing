<?php

namespace Luminee\Tracing\DataCollectors\Concerns;

interface DataEntityDecorator
{
    public function getData(): array;
}