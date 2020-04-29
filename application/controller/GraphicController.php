<?php

namespace App\controller;

use App\services\CountingService;
use App\services\RenderingService;
use MB\ControllerInterface;
use MB\Http;

/**
 * Class HomeController
 * @package App\controller
 */
class GraphicController implements ControllerInterface
{
    public function index(array $parameters): array
    {
        Http::redirectTo('');
    }

    /**
     * @requestMethod("post")
     * @param array $parameters
     */
    public function post(array $parameters)
    {
        $request = Http::getJSONFromRequest();

        $counts = (new CountingService())->run($request['filters'], $request['sorting']);

        Http::sendJsonResponse(\array_merge(['id' => $request['id']], (new RenderingService())->run($counts)));
    }

}