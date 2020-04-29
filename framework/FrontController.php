<?php


namespace MB;


class FrontController
{
    private $viewData = [];

    public function render()
    {
        // on commence par des vérifications de bases

        // Si on ne le précise pas, utiliser le layout
        if (!\array_key_exists('_layoutUsed', $this->viewData)) {
            $this->viewData['_layoutUsed'] = true;
        }

        // Si on ne le précise pas, la vue aura le meme nom que le controller
        if (!\array_key_exists('_view', $this->viewData)) {
            \preg_match('/^.*\\\\(.+)Controller$/u', \get_class(Router::getController()), $matches);
            $this->viewData['_view'] = $matches[1];
        }

        // Si on ne le précise pas, il n'y aura pas de stylesheet secondaire
        if (!\array_key_exists('_stylesheets', $this->viewData)) {
            $this->viewData['_stylesheets'] = [];
        }

        // Si on ne le précise pas, il n'y aura pas de script secondaire
        if (!\array_key_exists('_scripts', $this->viewData)) {
            $this->viewData['_scripts'] = [];
        }

        // on créé le chemin d'acces pour la vue - dans le dossier template
        $this->viewData['_view'] = ROOT_PATH . '/template/view/' . $this->viewData['_view'] . 'View.phtml';

        // vérifier que le fichier demandé existe
        if (!\file_exists($this->viewData['_view'])) {
            throw new \DomainException("No view found at {$this->viewData['_view']}");
        }

        // on récupère toutes les données passées à la vue, pour les utiliser sous forme de variables
        \extract($this->viewData, EXTR_OVERWRITE);

        // si l'utilisateur a choisit de ne pas utiliser le layout, on envoie la vue directement
        if (!$this->viewData['_layoutUsed']) {
            include $this->viewData['_view'];
            return;
        }

        // sinon, on envoie le layout, qui incluera lui meme la vue
        include ROOT_PATH . '/template/Layout.phtml';
    }

    public function run()
    {
        // On commence par faire tourner le code utilisateur
        Router::init();
        $this->viewData = \array_merge(
            $this->viewData,
            (array)Router::getController()->{Router::getControllerMethod()}(Router::getParameters())
        );

        // Puis on gère les fichiers de session
        $sessionFiles = \array_diff(\scandir(ROOT_PATH . '/application/session'), ['..', '.']);
        foreach ($sessionFiles as $sessionFile) {
            // on récupère le nom du fichier
            $fileName = \mb_substr($sessionFile, 0, \mb_strlen($sessionFile) - 4, 'UTF-8');
            // et sa classe
            $sessionClass = "App\session\\$fileName";
            // on essaye d'acceder à la méthode statique isInit de la class
            // si elle existe, et que la session est initialisée, on instancie la class pour l'affichage
            try {
                if ($sessionClass::isInit()) {
                    $this->viewData = \array_merge(
                        $this->viewData,
                        ["_$fileName" => new $sessionClass()]
                    );
                    // Au cas ou la class aurai une méthode statique isInit mais
                    // n'implémenterai pas SessionHandlerInterface
                    if (!$this->viewData["_$fileName"] instanceof SessionHandlerInterface) {
                        throw new \ErrorException("$sessionFile must implement \MB\SessionHandlerInterface");
                    }
                }
                // si $sessionClass n'est pas une class ou ne possède pas de méthode isInit
            } catch (\Error $error) {
                throw new \ErrorException("$sessionFile must be a session handler class implementing \MB\SessionHandlerInterface");
            }
        }

        // le flashBag
        if (FlashBag::isInit()) {
            // si oui, on l'instancie pour affichage
            $this->viewData = \array_merge(
                $this->viewData,
                ['_flashBag' => new FlashBag()]
            );
        }

        $this->viewData['_display'] = new Displayer();

        return $this;
    }

}