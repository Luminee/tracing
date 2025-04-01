<?php

namespace Luminee\Tracing;

use ArrayAccess;
use Luminee\Tracing\DataCollectors\_DataCollector;
use Luminee\Tracing\Exceptions\CollectorException;
use Luminee\Tracing\Exceptions\MeasureException;
use Luminee\Tracing\Exceptions\TracingException;
use Luminee\Tracing\Generators\RequestIdGenerator;

class Tracing implements ArrayAccess
{
    use RequestIdGenerator;

    /**
     * @var _DataCollector[]
     */
    protected $collectors = array();

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $requestId;

    protected $storage;

    /**
     * Adds a data collector
     *
     * @param _DataCollector $collector
     *
     * @return $this
     * @throws CollectorException
     */
    public function addCollector(_DataCollector $collector): Tracing
    {
        if ($collector->getName() === '__meta') {
            throw new CollectorException("'__meta' is a reserved name and cannot be used as a collector name");
        }

        if (isset($this->collectors[$collector->getName()])) {
            throw new CollectorException("'{$collector->getName()}' is already a registered collector");
        }

        $this->collectors[$collector->getName()] = $collector;

        return $this;
    }

    /**
     * Checks if a data collector has been added
     *
     * @param string $name
     * @return boolean
     */
    public function hasCollector(string $name): bool
    {
        return isset($this->collectors[$name]);
    }

    /**
     * Returns a data collector
     *
     * @param string $name
     * @return _DataCollector
     * @throws CollectorException
     */
    public function getCollector(string $name): _DataCollector
    {
        if (!isset($this->collectors[$name])) {
            throw new CollectorException("'$name' is not a registered collector");
        }

        return $this->collectors[$name];
    }

    /**
     * Returns an array of all data collectors
     *
     * @return _DataCollector[]
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }

    /**
     * Returns the id of the current request
     *
     * @return string
     */
    public function getCurrentRequestId(): string
    {
        if ($this->requestId === null) {
            $this->requestId = $this->generateRequestId();
        }
        return $this->requestId;
    }

    protected function getMeta($request_variables): array
    {
        $meta = [
            'id' => $this->getCurrentRequestId(),
            'datetime' => date('Y-m-d H:i:s'),
            'unix_time' => microtime(true)
        ];

        return array_merge($meta, $request_variables);
    }

    protected function getRequestVariables(): array
    {
        if (php_sapi_name() === 'cli') {
            return [
                'method' => 'CLI',
                'uri' => isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : null,
                'ip' => ($ip = gethostname()) ? gethostbyname($ip) : '127.0.0.1'
            ];
        }

        return [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ];
    }

    /**
     * Collects the data from the collectors
     *
     * @return array
     * @throws MeasureException
     */
    public function collect(): array
    {
        $this->data = array(
            '__meta' => $this->getMeta($this->getRequestVariables())
        );

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        // Remove all invalid (non UTF-8) characters
        array_walk_recursive($this->data, function (&$item) {
            if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });

        if ($this->storage !== null) {
            $this->storage->save($this->getCurrentRequestId(), $this->data);
        }

        return $this->data;
    }

    /**
     * Returns collected data
     *
     * Will collect the data if none have been collected yet
     *
     * @return array
     * @throws MeasureException
     */
    public function getData(): array
    {
        if ($this->data === null) {
            $this->collect();
        }

        return $this->data;
    }

    // --------------------------------------------
    // ArrayAccess implementation

    /**
     * @param $offset
     * @param $value
     * @return void
     * @throws TracingException
     */
    public function offsetSet($offset, $value)
    {
        throw new TracingException("Tracing[$offset] is read-only");
    }

    /**
     * @param $offset
     * @return _DataCollector
     * @throws CollectorException
     */
    public function offsetGet($offset): _DataCollector
    {
        return $this->getCollector($offset);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->hasCollector($offset);
    }

    /**
     * @param $offset
     * @return void
     * @throws TracingException
     */
    public function offsetUnset($offset)
    {
        throw new TracingException("Tracing[] is read-only");
    }
}
