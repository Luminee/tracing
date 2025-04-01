<?php

namespace Luminee\Tracing\DataCollectors\Database;

use Illuminate\Database\Connection;
use Luminee\Tracing\DataCollectors\_DataEntity;
use Luminee\Tracing\DataFormatters\DataFormatter;
use Luminee\Tracing\DataFormatters\SqlFormatter;

class Query extends _DataEntity
{
    use SqlFormatter, DataFormatter;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $bindings;

    /**
     * @var float
     */
    protected $time;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $driver;

    public function __construct($uuid, $query, $type, $bindings, $time, $connection)
    {
        $this->uuid = $uuid;
        $this->query = $query;
        $this->type = $type;
        $this->bindings = $bindings;
        $this->time = $time;
        $this->connection = $connection;
        $this->driver = $this->connection->getConfig('driver');
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function getData(): array
    {
        return [
            'uuid' => $this->uuid,
            'query' => $this->query,
            'type' => $this->type,
            'bindings' => $this->bindings,
            'real_sql' => $this->toRealSql(),
            'time' => $this->time,
            'time_str' => $this->formatDuration($this->time),
            'connection' => $this->connection->getName(),
            'driver' => $this->driver
        ];
    }
}
