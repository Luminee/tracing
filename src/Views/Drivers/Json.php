<?php

namespace Luminee\Tracing\Views\Drivers;

use Illuminate\Http\JsonResponse;
use Luminee\Tracing\Views\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Json extends View
{
    public function isEnabled(): bool
    {
        return $this->app['config']->get('views.json');
    }

    public function toView(Request $request, Response $response, array $data)
    {
        if (get_class($response) !== JsonResponse::class) {
            return $response;
        }

        $response->setData(array_merge($response->getData(true), ['__tracing' => $data]));
        return $response;
    }
}
