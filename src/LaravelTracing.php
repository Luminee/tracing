<?php

namespace Luminee\Tracing;

use Closure;
use ErrorException;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Luminee\Tracing\DataCollectors\_DataCollector;
use Luminee\Tracing\DataCollectors\TimeDataCollector;
use Luminee\Tracing\Exceptions\CollectorException;
use Luminee\Tracing\Exceptions\MeasureException;
use Luminee\Tracing\Supports\App;
use Luminee\Tracing\Views\View;
use Luminee\Tracing\Views\ViewManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LaravelTracing extends Tracing
{
    use App;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Normalized Laravel Version
     *
     * @var string
     */
    protected $version;

    /**
     * True when booted.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * True when enabled, false disabled on null for still unknown
     *
     * @var bool
     */
    protected $enabled = null;

    /**
     * Views for tracing display
     *
     * @var View[]
     */
    protected $views = [];

    /**
     * @param Application|null $app
     */
    public function __construct(Application $app = null)
    {
        $this->bootApplication($app);

        $this->version = $this->app->version();
        $this->request = $this->app['request'];
    }

    protected function init()
    {
        // Set custom error handler
        if ($this->config->get('tracing.error_handler', false)) {
            set_error_handler([$this, 'handleError']);
        }

        $this->getCurrentRequestId();

        $this->initViews();
    }

    protected function initViews()
    {
        $viewManager = new ViewManager();

        $this->views = $viewManager->bootViews($this->app);
    }

    /**
     * Boot the Tracing (add collectors, renderer and listener)
     *
     * @throws CollectorException|MeasureException
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->init();

        $this->addTimeCollector();

        $this->booted = true;
    }

    /**
     * Enable the Tracing and boot, if not already booted.
     *
     * @throws CollectorException|MeasureException
     */
    public function enable()
    {
        $this->enabled = true;

        if (!$this->booted) {
            $this->boot();
        }
    }

    public function shouldCollect($name, $default = false)
    {
        return $this->app['config']->get('tracing.collectors.' . $name, $default);
    }

    /**
     * Adds a data collector
     *
     * @param _DataCollector $collector
     *
     * @return $this
     * @throws
     */
    public function addCollector(_DataCollector $collector): Tracing
    {
        parent::addCollector($collector);

        if (method_exists($collector, 'useHtmlVarDumper')) {
            $collector->useHtmlVarDumper();
        }

        return $this;
    }

    /**
     * @throws MeasureException
     * @throws CollectorException
     */
    protected function addTimeCollector()
    {
        if (!$this->shouldCollect('time', true)) {
            return;
        }

        $startTime = $this->app['request']->server('REQUEST_TIME_FLOAT');
        $this->addCollector(new TimeDataCollector($startTime, $this->getCurrentRequestId()));

        if ($startTime) {
            $this->app->booted(
                function () use ($startTime) {
                    $this->addMeasure('Booting', $startTime);
                }
            );
        }

        $this->startMeasure('Application');
    }

    /**
     * Handle silenced errors
     *
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @throws ErrorException
     */
    public function handleError($level, $message, string $file = '', int $line = 0, array $context = [])
    {
        $exception = new ErrorException($message, 0, $level, $file, $line);
        if (error_reporting() & $level) {
            throw $exception;
        }
    }

    /**
     * Starts a measure
     *
     * @param string|null $label Public name
     * @param string|null $parent_uuid
     * @param string|null $collector
     * @return string|null
     * @throws CollectorException
     */
    public function startMeasure(string $label = null, string $parent_uuid = null, string $collector = null)
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $time */
            $time = $this->getCollector('time');

            $uuid = $time->startMeasure($label, null, $parent_uuid, $collector);
        }
        return $uuid ?? null;
    }

    /**
     * Stop the measure
     *
     * @param string $uuid
     * @param null $end
     * @param array $params
     * @throws CollectorException
     * @throws MeasureException
     */
    public function stopMeasure(string $uuid, $end = null, array $params = array())
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $time */
            $time = $this->getCollector('time');

            $time->stopMeasure($uuid, $end ?? microtime(true), $params);
        }
    }

    /**
     * Get the current measure uuid
     * @throws CollectorException
     */
    public function getCurrentMeasureUuid()
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $time */
            $time = $this->getCollector('time');
            return $time->getCurrentMeasureUuid();
        }

        return null;
    }


    /**
     * Check if the Tracing is enabled
     * @return boolean
     */
    public function isEnabled(): bool
    {
        if ($this->enabled === null) {
            /** @var Repository $config */
            $config = $this->app['config'];
            $configEnabled = value($config->get('tracing.enabled'));

            if ($configEnabled === null) {
                $configEnabled = $config->get('app.debug');
            }

            $this->enabled = $configEnabled &&
                !$this->app->runningInConsole() &&
                !$this->app->environment('testing');
        }

        return $this->enabled;
    }


    protected function getRequestVariables(): array
    {
        return [
            'method' => $this->request->getMethod(),
            'uri' => $this->request->getRequestUri(),
            'ip' => $this->request->getClientIp()
        ];
    }


    /**
     * Disable the Tracing
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Adds a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     * @param string|null $parent_uuid
     * @param array|null $params
     * @param string|null $collector
     * @throws CollectorException
     * @throws MeasureException
     */
    public function addMeasure(
        string $label,
        float $start,
        float $end = null,
        string $parent_uuid = null,
        array $params = [],
        string $collector = null
    ) {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $time */
            $time = $this->getCollector('time');

            $time->addMeasure($label, $start, $end, $parent_uuid, $params, $collector);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param Closure $closure
     * @param string|null $parent_uuid
     * @param string|null $collector
     * @return mixed
     * @throws CollectorException
     * @throws MeasureException
     */
    public function measure(string $label, Closure $closure, string $parent_uuid = null, string $collector = null)
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $time */
            $time = $this->getCollector('time');
            $result = $time->measure($label, $closure, $parent_uuid, $collector);
        } else {
            $result = $closure();
        }

        return $result;
    }

    /**
     * @throws MeasureException
     */
    public function toView(Request $request, Response $response): Response
    {
        if (!$this->isEnabled() || !$this->booted) {
            return $response;
        }

        $data = $this->getData();

        foreach ($this->views as $view) {
            $view->toView($request, $response, $data);
        }

        return $response;
    }

}
