<?php
/**
 * Point d'entrée principal de l'application TutorMap.
 *
 * Gère la redirection vers les contrôleurs appropriés en fonction de l'URI fournie.
 *
 * PHP version 8.3
 *
 * @category Routing
 * @package  TutorMap
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

use Includes\Autoloader;

require_once '_assets/includes/Autoloader.php';
Autoloader::register();

/**
 * S
 */
class Router
{
    private string $url;
    private array $routes = [];
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Pour un lien donné via un get, défini un chemin avec une fonction associée
     *
     * @param  $path
     * @param  $callable
     * @return route
     */
    public function get($path, $callable): route
    {
        $route = new route($path, $callable);

        $this->routes["GET"][] = $route;
        return $route;
    }

    /**
     * Pour un lien donné via un post, défini un chemin avec une fonction associée
     *
     * @param  $path
     * @param  $callable
     * @return route
     */
    public function post($path, $callable): route
    {
        $route = new route($path, $callable);
        $this->routes["POST"][] = $route;
        return $route;
    }

    /**
     * Cherche la page correspondante au lien demandé
     * à partir de toutes les routes possibles
     *
     * @throws RouterException si erreur il y a
     */
    public function run(): void
    {
        if (!isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException("REQUEST_METHOD n'existe pas");
        }

        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->match($this->url)) {
                $route->call();
                return;
            }
        }
        throw new RouterException("Erreur 404");
    }
}

/**
 * Initialisation de la session qu'importe le header
 * id_admin correspond à la session administrateur
 */
session_start();

/**
 * Initialisation du routage des URI
 */
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$router = new Router($uri);

function createAction($uri, $layout): Closure
{
    if ($uri === '/') {
        $uri = 'homepage';
    }
    $uri = str_replace('-', '', $uri);

    $controllerName = ucfirst(ltrim($uri, '/'));
    $className = "Blog\\Controllers\\$controllerName";

    if (!(class_exists($className))) {
        $className = "Blog\\Controllers\\Error404";
    }

    return function () use ($className, $layout) {
        (new $className($layout))->show();
    };
}

$layout = new \Blog\Views\Layout();
$action = createAction($uri, $layout);
$router->get($uri, $action);
$router->post($uri, $action);

try {
    $router->run();
} catch (RouterException $e) {
    echo $e->getMessage();
    return;
}
