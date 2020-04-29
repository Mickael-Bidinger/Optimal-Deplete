<?php


namespace MB;

class Kernel
{
    /**
     * Kernel constructor.
     * définition de la fonction d'autoload
     */
    public function __construct()
    {
        \spl_autoload_register([$this, 'autoLoad']);
    }

    /**
     * @param string $class
     */
    private function autoLoad(string $class)
    {
        // utilisation des namespaces pour l'autoload
        $class = \mb_ereg_replace('\\\\', '/', $class);
        // MB pour le dossier framework
        $class = \mb_ereg_replace('MB', 'framework', $class);
        // App pour le dossier application
        $class = \mb_ereg_replace('App', 'application', $class);
        $filePath = ROOT_PATH . "/$class.php";

        // chargement de la classe
        include $filePath;
    }

    public function run()
    {
        // le kernel utilise le front controller pour tout affichage
        $frontController = new FrontController();
        $queue = new Queue();

        $queue->prepare();
        $frontController->run()->render();

    }

}