<?php

namespace Luminee\Tracing\Generators;

trait UuidGenerator
{
    public function generateUuid($prefix = ''): string
    {
        return uniqid($prefix);
    }
}
