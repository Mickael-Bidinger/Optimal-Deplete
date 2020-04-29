<?php


namespace MB;


class Router
{
    private static $askedRoute;
    private static $controller;
    private static $requestMethod;
    private static $isInit = false;
    private static $method;

    static public function init()
    {
        self::setAskedRoute();
        self::setController();
        self::initController();
        self::setRequestMethod();
        self::setMethod();
        self::$isInit = true;
    }

    static public function getController(): ControllerInterface
    {
        if (!self::$isInit) {
            throw new \ErrorException('Router must be initialized before getting any route informations.');
        }
        return self::$controller;
    }

    static public function getControllerMethod(): string
    {
        if (!self::$isInit) {
            throw new \ErrorException('Router must be initialized before getting any route informations.');
        }
        return self::$method;
    }

    static public function getParameters(): array
    {
        if (!self::$isInit) {
            throw new \ErrorException('Router must be initialized before getting any route informations.');
        }
        return self::$askedRoute;
    }

    private static function setAskedRoute()
    {
        // si l'utilisateur accede directement à public/ ou public/index.php
        // il ne sera pas redirigé, REDIRECT_URL n'existera pas, $askedRoute sera vide
        $askedRoute = '';

        // sinon on récupère l'url demandé à partir du dossier public non inclu, et on lowercase
        if (\array_key_exists('REDIRECT_URL', $_SERVER)) {
            $askedRoute = \mb_strtolower(
                \mb_eregi_replace(RELATIVE_ROOT_PATH . '/(.*)', '\\1', $_SERVER['REDIRECT_URL']),
                'UTF-8'
            );
        }

        // on supprime un éventuel / de fin
        if (\mb_substr($askedRoute, -1, 1) === '/') {
            $askedRoute = \mb_substr($askedRoute, 0, \mb_strlen($askedRoute) - 1);
        }

        // et on en fait un tableau de string
        self::$askedRoute = \mb_split('/', $askedRoute);
    }

    static private function setController()
    {
        // on load le fichier routes.json
        $routes = \json_decode(\file_get_contents(ROOT_PATH . '/config/routes.json'), true);

        // on regarde dans ROUTES si un path correspond
        foreach ($routes as $route) {
            if ($route['path'] === self::$askedRoute[0]) {
                \array_shift(self::$askedRoute);
                self::$controller = "App\\controller\\{$route['Controller']}";
                return;
            }
        }

        // si aucun ne correspondait, on prends celui de la racine
        foreach ($routes as $route) {
            if ($route['path'] === '') {
                self::$controller = "App\\controller\\{$route['Controller']}";
                return;
            }
        }

        // si toujours pas de correspondance => DomainException
        throw new \DomainException(
            'Routing error : No default (empty string) route nor any configured for "'
            . self::$askedRoute[0] . '"'
        );
    }

    private static function initController()
    {
        // on vérifie que le nom du controller demandé dans config/routes.php est alphanumérique
        if (!\ctype_alnum(\mb_ereg_replace('\\\\', '', self::$controller))) {
            throw new \ErrorException('Invalid controller name or path : \'' . self::$controller . '\'.');
        }

        // on essaye d'instancier le controller
        try {
            $controllerInstance = new self::$controller();
        } catch (\Error $error) {
            throw new \ErrorException('\'' . self::$controller . '.php\' must be a class.');
        }

        // on vérifie que le controller implemente le ControllerInterface
        if (!$controllerInstance instanceof ControllerInterface) {
            throw new \ErrorException('\'' . self::$controller . '.php\' must implement ControllerInterface.');
        }

        // si tout se passe bien, self::controller deviens une instance du controller au lieu d'un string
        self::$controller = $controllerInstance;
    }

    private static function setRequestMethod()
    {
        //on récupère la méthode utilisée par le naviguateur
        self::$requestMethod = \mb_strtolower($_SERVER['REQUEST_METHOD'], 'UTF-8');

    }

    private static function setMethod()
    {

        // on commence par créer un tableau des méthodes du controller
        $methods = [];
        $reflectionClass = new \ReflectionClass(\get_class(self::$controller));

        foreach ($reflectionClass->getMethods() as $method) {
            // pour chaque méthode du controller on vérifie son DocComment

            // -quelles requestMethods sont acceptées par la méthode du controller
            \preg_match_all('/^.*@requestMethod\((.*)\).*$/mu', $method->getDocComment(), $matches);
            // --si il n'y en a pas, la requestMethod de la method est considérée get
            $requestMethod = empty($matches[1]) ? ['get'] : \json_decode($matches[1][0]);
            // --Pour simplifier l'utilisation, on peux donner un string au lieu de l'array en cas de méthode unique
            $requestMethod = \is_array($requestMethod) ? $requestMethod : [$requestMethod];
            // ---si la requestMethod demandée n'est pas get, ni l'une de celle de la method, inutile de la stocker
            if (!\in_array(self::$requestMethod, $requestMethod)) {
                continue;
            }
            $methods[$method->getName()]['requestMethod'] =
                \is_array($requestMethod) ? $requestMethod : [$requestMethod];

            // -quelle subRoute est acceptée par la méthode du controller
            \preg_match_all('/^.*@subRoute\((.*)\).*$/mu', $method->getDocComment(), $matches);
            // --si il n'y en a pas, subRoute est set à null
            $methods[$method->getName()]['subRoute'] = empty($matches[1]) ? null : \json_decode($matches[1][0]);

        }

        foreach ($methods as $methodName => $method) {
            // on vérifie ensuite pour chaques méthodes si la subRoute correspond à celle de l'url
            if (
                !empty(self::$askedRoute)
                &&
                $method['subRoute'] === self::$askedRoute[0]
            ) {
                // on supprime la subRoute des parameters
                \array_shift(self::$askedRoute);
                // et on récupère le nom de la méthode du controller
                self::$method = $methodName;
                return;
            }
            // si une subroute était définie pour la method, et ne correspond pas à celle demandée
            // on la supprime de la liste
            if ($method['subRoute'] !== null) {
                unset($methods[$methodName]);
            }
        }

        // si aucune subroute n'as été reconnue
        if (count($methods) === 1) {
            self::$method = \array_key_first($methods);
            return;
        }

        // uniquement si nous sommes en get
        // on renvoie vers index
        if (self::$requestMethod === 'get') {
            self::$method = 'index';
            return;
        }

        // si le naviguateur a envoyé une requestMethod inconnue ou essayé d'acceder à index autrement qu'en get
        // on le renvoie vers l'accueil du site
        Http::redirectTo('');

    }

}