<?php

namespace Luminee\Tracing\Generators;

trait RequestIdGenerator
{
    public function generateRequestId(): string
    {
        return 'X' . bin2hex(random_bytes(16));
    }
}
