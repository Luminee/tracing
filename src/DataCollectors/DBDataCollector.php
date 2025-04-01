<?php

namespace Luminee\Tracing\DataCollectors;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;
use Luminee\Tracing\DataCollectors\Database\Query;
use Luminee\Tracing\Enums\CollectorEnum;
use Luminee\Tracing\Exceptions\MeasureException;
use Luminee\Tracing\Generators\UuidGenerator;

class DBDataCollector extends _DataCollector
{
    use UuidGenerator;

    /**
     * @var string
     */
    protected $name = CollectorEnum::DB;

    protected $timeCollector;

    /**
     * @var Query[]
     */
    protected $queries = [];

    public function __construct(TimeDataCollector $timeCollector = null)
    {
        $this->timeCollector = $timeCollector;
    }


    /**
     *
     * @param QueryExecuted $query
     * @throws MeasureException
     */
    public function addQuery(QueryExecuted $query)
    {
        $time = $query->time / 1000;
        $endTime = microtime(true);
        $startTime = $endTime - $time;

        $uuid = $this->generateUuid();
        $this->queries[$uuid] = $queryEntity = new Query(
            $uuid,
            $sql = $query->sql,
            'query',
            $query->connection->prepareBindings($query->bindings),
            $time,
            $query->connection
        );

        if ($this->timeCollector !== null) {
            $this->timeCollector->addMeasure(Str::limit($sql), $startTime, $endTime,
                $this->timeCollector->getCurrentMeasureUuid(),
                ['__query' => $queryEntity->getData()], 'db', 'Database Query');
        }
    }


    public function collect(): array
    {
        usort($this->queries, function ($a, $b) {
            if ($a->getTime() == $b->getTime()) {
                return 0;
            }
            return $a->getTime() > $b->getTime() ? -1 : 1;
        });

        $queries = [];
        foreach ($this->queries as $query) {
            $queries[] = $query->getData();
        }

        return [
            'name' => $this->getName(),
            'queries' => $queries,
        ];
    }
}