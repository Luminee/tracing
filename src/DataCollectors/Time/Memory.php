<?php

namespace Luminee\Tracing\DataCollectors\Time;

use Luminee\Tracing\DataCollectors\_DataEntity;
use Luminee\Tracing\DataFormatters\DataFormatter;

class Memory extends _DataEntity
{
    use DataFormatter;

    /**
     * @var int
     */
    protected $start_memory;

    /**
     * @var int
     */
    protected $end_memory;

    /**
     * @var int
     */
    protected $memory_usage;

    public function __construct($start_memory = null)
    {
        $this->start_memory = $start_memory ?? memory_get_usage();
    }

    public function memoryUsage($end_memory = null)
    {
        $this->end_memory = $end_memory ?? memory_get_usage();
        $this->memory_usage = $this->end_memory - $this->start_memory;
    }

    public function getData(): array
    {
        return [
            'start_memory' => $this->start_memory,
            'end_memory' => $this->end_memory,
            'memory_usage' => $this->memory_usage,
            'memory_usage_str' => $this->formatBytes($this->memory_usage)
        ];
    }
}