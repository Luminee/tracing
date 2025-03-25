<?php

namespace Luminee\Tracing\DataCollectors\Time;

use Luminee\Tracing\DataFormatters\DataFormatter;

class Measure
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
    protected $relative_start;

    /**
     * @var float
     */
    protected $end;

    /**
     * @var float
     */
    protected $relative_end;

    /**
     * @var float
     */
    protected $duration;

    /**
     * @var string
     */
    protected $duration_str;

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
     * @param string|null $collector
     * @param string|null $group
     */
    public function __construct(
        string $uuid,
        string $label,
        float $start,
        string $parent_uuid = null,
        string $collector = null,
        string $group = null
    ) {
        $this->uuid = $uuid;
        $this->label = $label;
        $this->start = $start;
        $this->parent_uuid = $parent_uuid;
        $this->collector = $collector;
        $this->group = $group;
    }

    /**
     * @param float $end
     * @param array $params
     * @return void
     */
    public function endMeasure(float $end, array $params = array())
    {
        $this->relative_start = $this->start - ($params['requestStartTime'] ?: 0);
        $this->end = $end;
        $this->relative_end = $end - ($params['requestEndTime'] ?: 0);
        $this->duration = $end - $this->start;
        $this->duration_str = $this->formatDuration($this->duration);
        $this->params = $params;
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
            'relative_start' => $this->relative_start,
            'end' => $this->end,
            'relative_end' => $this->relative_end,
            'duration' => $this->duration,
            'duration_str' => $this->duration_str,
            'params' => $this->params,
            'collector' => $this->collector,
            'group' => $this->group
        ];
    }
}