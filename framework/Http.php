<?php


namespace MB;


class Http
{

    static public function getQueryFields(): array
    {
        return $_GET;
    }

    static public function getFormFields(): array
    {
        return $_POST;
    }

    static public function getJSONFromRequest()
    {
        return \json_decode(\file_get_contents('php://input'), true);
    }


    static public function redirectTo(string $route, bool $isUrl = true, array $params = [])
    {
        if (!$isUrl) {
            $routes = \json_decode(\file_get_contents(ROOT_PATH . '/config/routes.json'), true);
            if (!\array_key_exists($route, $routes)) {
                throw new \DomainException("$route is not a valid route name.");
            }
            $route = $routes[$route]['path'];
        }

        if (\mb_substr($route, 0, 1, 'UTF-8') !== '/') {
            $route = "/$route";
        }

        if (!empty($params)) {
            $route .= '/' . implode('/', $params);
        }

        \header('Location: //' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . RELATIVE_ROOT_PATH . $route);
        exit;
    }

    static public function sendJsonResponse($data)
    {
        \header('Content-Type: application/json; charset=UTF-8');
        echo \json_encode($data);
        exit;
    }

}