<?php

namespace Luminee\Tracing\Middlewares;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Luminee\Tracing\LaravelTracing;

class TraceRequests
{
    /**
     * @var LaravelTracing
     */
    protected $tracing;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $excepts = [];

    /**
     * TraceRequests constructor.
     * @param LaravelTracing $tracing
     * @param Repository $config
     */
    public function __construct(LaravelTracing $tracing, Repository $config)
    {
        $this->tracing = $tracing;
        $this->config = $config;
        $this->excepts = config('tracing.middleware.excluded_paths') ?? [];
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->tracing->isEnabled() || $this->shouldBeExcluded($request)) {
            return $next($request);
        }

        $this->tracing->boot();

        $response = $next($request);

        // Handle tracing data
        $this->tracing->toView($request, $response);

        return $response;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function shouldBeExcluded(Request $request): bool
    {
        foreach ($this->excepts as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }

}
