<?php

namespace Luminee\Tracing\DataCollectors\Time;

use Luminee\Tracing\DataCollectors\_DataEntity;
use Luminee\Tracing\DataFormatters\DataFormatter;

class Measure extends _DataEntity
{
    use DataFormatter;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $parent_uuid;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var float
     */
    protected $start;

    /**
     * @var float
     */
    protected $end;

    /**
     * @var float
     */
    protected $duration;

    /**
     * @var Memory
     */
    protected $memory;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $collector;

    /**
     * @var string
     */
    protected $group;

    /**
     * @param string $uuid
     * @param string|null $label
     * @param float $start
     * @param string|null $parent_uuid
     * @param bool $memoryUsage
     * @param string|null $collector
     * @param string|null $group
     */
    public function __construct(
        string $uuid,
        string $label,
        float $start,
        string $parent_uuid = null,
        bool $memoryUsage = false,
        string $collector = null,
        string $group = null
    ) {
        $this->uuid = $uuid;
        $this->label = $label;
        $this->start = $start;
        $this->parent_uuid = $parent_uuid;
        $this->collector = $collector;
        $this->group = $group;

        if ($memoryUsage) {
            $this->memory = new Memory();
        }
    }

    /**
     * @param float $end
     * @param array $params
     * @return void
     */
    public function endMeasure(float $end, array $params = array())
    {
        $this->end = $end;
        $this->duration = $end - $this->start;
        $this->params = $params;

        if (!is_null($this->memory)) {
            $this->memory->memoryUsage();
        }
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getParentUuid()
    {
        return $this->parent_uuid;
    }

    public function getStart(): float
    {
        return $this->start;
    }

    public function getData(): array
    {
        return [
            'uuid' => $this->uuid,
            'parent_uuid' => $this->parent_uuid,
            'label' => $this->label,
            'start' => $this->start,
            'relative_start' => $this->start - ($this->params['requestStartTime'] ?: 0),
            'end' => $this->end,
            'relative_end' => $this->end - ($this->params['requestEndTime'] ?: 0),
            'duration' => $this->duration,
            'duration_str' => $this->formatDuration($this->duration),
            'memory' => $this->memory ? $this->memory->getData() : [],
            'params' => $this->params,
            'collector' => $this->collector,
            'group' => $this->group
        ];
    }
}