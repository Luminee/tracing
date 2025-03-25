<?php

namespace Luminee\Tracing\DataCollectors;

use Closure;
use Luminee\Tracing\DataCollectors\Time\Measure;
use Luminee\Tracing\DataFormatters\DataFormatter;
use Luminee\Tracing\Exceptions\MeasureException;

/**
 * Collects info about the request duration as well as providing
 * a way to log duration of any operations
 */
class TimeDataCollector extends _DataCollector
{
    use DataFormatter;

    /**
     * @var string
     */
    protected $name = 'time';

    /**
     * @var float
     */
    protected $requestStartTime;

    /**
     * @var float
     */
    protected $requestEndTime;

    /**
     * @var string
     */
    protected $rootMeasureUuid;

    /**
     * @var string
     */
    protected $currentMeasureUuid;

    /**
     * @var array<string, Measure>
     */
    protected $startedMeasures = array();

    /**
     * @var Measure[]
     */
    protected $measures = array();

    /**
     * @param float|null $requestStartTime
     */
    public function __construct(float $requestStartTime = null, string $requestId = null)
    {
        if ($requestStartTime === null) {
            $requestStartTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        }

        $this->requestStartTime = (float)$requestStartTime;
        $this->rootMeasureUuid = $requestId;
    }

    /**
     * Starts a measure
     *
     * @param string $label Public name
     * @param float|null $start
     * @param string|null $parent_uuid
     * @param string|null $collector The source of the collector
     * @param string|null $group The group for aggregates
     *
     * @return string
     */
    public function startMeasure(
        string $label,
        float $start = null,
        string $parent_uuid = null,
        string $collector = null,
        string $group = null
    ): string {
        $uuid = $this->generateUuid();

        $this->startedMeasures[$uuid] = new Measure(
            $uuid,
            $label,
            $start ?? microtime(true),
            $parent_uuid ?? $this->rootMeasureUuid,
            $collector,
            $group
        );

        return $this->currentMeasureUuid = $uuid;
    }

    /**
     * Check a measure exists
     *
     * @param string $uuid
     * @return bool
     */
    public function hasStartedMeasure(string $uuid): bool
    {
        return isset($this->startedMeasures[$uuid]);
    }

    /**
     * Stops a measure
     *
     * @param string $uuid
     * @param null $end
     * @param array $params
     * @throws MeasureException
     */
    public function stopMeasure(string $uuid, $end = null, array $params = array())
    {
        if (!$this->hasStartedMeasure($uuid)) {
            throw new MeasureException("Failed stopping measure '$uuid' because it hasn't been started");
        }

        $measure = $this->startedMeasures[$uuid];

        $params['requestStartTime'] = $this->requestStartTime;
        $params['requestEndTime'] = $this->requestEndTime;
        $measure->endMeasure($end ?? microtime(true), $params);

        $this->measures[] = $measure;
        $this->currentMeasureUuid = $measure->getParentUuid();
        unset($this->startedMeasures[$uuid]);
    }

    /**
     * @throws MeasureException
     */
    public function addMeasure(
        $label,
        $start = null,
        $end = null,
        string $parent_uuid = null,
        $params = array(),
        $collector = null
    ) {
        $uuid = $this->startMeasure($label, $start, $parent_uuid, $collector);
        $this->stopMeasure($uuid, $end, $params);
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param Closure $closure
     * @param string|null $parent_uuid
     * @param string|null $collector
     *
     * @return mixed
     * @throws MeasureException
     */
    public function measure(string $label, Closure $closure, string $parent_uuid = null, string $collector = null)
    {
        $uuid = $this->startMeasure($label, null, $parent_uuid, $collector);

        $result = $closure();

        $params = is_array($result) ? $result : array();
        $this->stopMeasure($uuid, null, $params);

        return $result;
    }

    public function getCurrentMeasureUuid(): string
    {
        return $this->currentMeasureUuid;
    }

    /**
     * Returns an array of all measures
     *
     * @return array
     */
    public function getMeasures(): array
    {
        return $this->measures;
    }

    /**
     * Returns the request start time
     *
     * @return float
     */
    public function getRequestStartTime(): float
    {
        return $this->requestStartTime;
    }

    /**
     * Returns the request end time
     *
     * @return float
     */
    public function getRequestEndTime(): float
    {
        return $this->requestEndTime;
    }

    /**
     * Returns the duration of a request
     *
     * @return float
     */
    public function getRequestDuration(): float
    {
        if ($this->requestEndTime !== null) {
            return $this->requestEndTime - $this->requestStartTime;
        }
        return microtime(true) - $this->requestStartTime;
    }

    /**
     * @return array
     * @throws MeasureException
     */
    public function collect(): array
    {
        $this->requestEndTime = microtime(true);
        $duration = $this->getRequestDuration();

        foreach (array_keys($this->startedMeasures) as $uuid) {
            $this->stopMeasure($uuid);
        }

        usort($this->measures, function ($a, $b) {
            if ($a->getStart() == $b->getStart()) {
                return 0;
            }
            return $a->getStart() < $b->getStart() ? -1 : 1;
        });

        $measures = [];
        foreach ($this->measures as $measure) {
            $measures[$measure->getUuid()] = $measure->getData();
        }

        $measures_tree = $this->measureTree($measures, $this->rootMeasureUuid);

        return [
            'name' => $this->getName(),
            'start' => $this->requestStartTime,
            'end' => $this->requestEndTime,
            'duration' => $duration,
            'duration_str' => $this->formatDuration($duration),
            'measures' => $measures,
            'measures_tree' => $measures_tree,
        ];
    }

    protected function measureTree($measures, $root_uuid): array
    {
        $children = [];
        foreach ($measures as $measure) {
            if ($measure['parent_uuid'] === $root_uuid) {
                $measure['children'] = $this->measureTree($measures, $measure['uuid']);
                $children[] = $measure;
            }
        }
        return $children;
    }

}
