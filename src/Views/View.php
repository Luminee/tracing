<?php

namespace Luminee\Tracing\Views;

use Luminee\Tracing\Supports\App;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class View
{
    use App;

    public function __construct($app = null)
    {
        $this->bootApplication($app);
    }

    /**
     * Get this is enabled
     *
     * @return mixed
     */
    abstract public function isEnabled();

    abstract public function toView(Request $request, Response $response, array $data);

}
