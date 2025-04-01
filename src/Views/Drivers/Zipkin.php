<?php

namespace Luminee\Tracing\Views\Drivers;

use Luminee\Tracing\Enums\CollectorEnum;
use Luminee\Tracing\Views\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Tracing;
use Zipkin\TracingBuilder;

class Zipkin extends View
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var mixed
     */
    protected $port;

    /**
     * @var string
     */
    protected $reporter_str = 'http://%s:%s/api/v2/spans';

    /**
     * @var Tracing
     */
    protected $tracing;

    public function __construct($app = null)
    {
        parent::__construct($app);

        $this->host = $this->config->get('tracing.zipkin.connection.host');
        $this->port = $this->config->get('tracing.zipkin.connection.port');

        $this->setUp();
    }

    public function isEnabled(): bool
    {
        return $this->app['config']->get('tracing.views.zipkin');
    }

    protected function setUp()
    {
        $this->tracing = TracingBuilder::create()
            ->havingLocalEndpoint($this->createEndpoint())
            ->havingSampler(BinarySampler::createAsAlwaysSample())
            ->havingTraceId128bits(true)
            ->havingReporter($this->createReporter())
            ->build();
    }

    protected function createEndpoint(): Endpoint
    {
        $ip = gethostbyname($host = $this->host);
        if ($ip !== $this->host) {
            $host = $ip;
        }

        return Endpoint::create(
            $this->config->get('tracing.zipkin.connection.serviceName'),
            $host,
            null,
            $this->port
        );
    }

    protected function createReporter(): Http
    {
        return new Http([
            'endpoint_url' => sprintf($this->reporter_str, $this->host, $this->port),
        ]);
    }

    public function toView(Request $request, Response $response, array $data)
    {
        $time = $data['time'];
        $this->timeToZipkin([
            [
                'uuid' => $data['__meta']['id'],
                'parent_id' => null,
                'label' => '__Request:' . $data['__meta']['id'],
                'start' => $time['start'],
                'end' => $time['end'],
                'duration' => $time['duration'],
                'duration_str' => $time['duration_str'],
                'memory' => $time['memory'],
                'children' => $time['measures_tree'],
            ]
        ]);
        $this->tracing->getTracer()->flush();
    }

    protected function timeToZipkin($measures, $parentId = null)
    {
        $tracer = $this->tracing->getTracer();
        foreach ($measures as $measure) {
            $span = is_null($parentId) ? $tracer->newTrace() : $tracer->newChild($parentId);
            $span->setName($measure['label']);
            if (!empty($measure['memory'])) {
                $span->tag('memory.usage', $measure['memory']['memory_usage_str']);
            }
            if (!empty($measure['collector']) && $measure['collector'] == CollectorEnum::DB) {
                $__query = $measure['params']['__query'];
                $span->tag('db.type', $__query['type']);
                $span->tag('db.sql', $__query['real_sql']);
                $span->tag('db.connection', $__query['connection']);
                $span->tag('db.driver', $__query['driver']);
            }
            $span->start(intval($measure['start'] * 1000000));

            if (!empty($measure['children'])) {
                $this->timeToZipkin($measure['children'], $span->getContext());
            }
            $span->finish(intval($measure['end'] * 1000000));
        }
    }
}
