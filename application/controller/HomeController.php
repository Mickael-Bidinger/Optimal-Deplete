<?php

namespace App\controller;

use MB\ControllerInterface;

/**
 * Class HomeController
 * @package App\controller
 */
class HomeController implements ControllerInterface
{
    /**
     * @requestMethod("get")
     * @subRoute("index")
     * @param array $parameters
     * @return array
     */
    public function index(array $parameters): array
    {
        return [
            '_title' => 'World of Warcraft statistics for Mythic+',
//            '_view' => 'Home',
//            '_stylesheets' => [],
//            '_scripts' => [],
//            '_layoutUsed' => true,
        ];
    }

}