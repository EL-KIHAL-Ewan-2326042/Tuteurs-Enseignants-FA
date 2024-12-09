<?php
use Includes\Autoloader;
use Includes\Database;

require_once '_assets/includes/Autoloader.php';
Autoloader::register();
class Router {
    private string $url;
    private array $routes = [];
    public function __construct($url) {
        $this->url = $url;
    }

    /**
     * Pour un lien donné via un get, défini un chemin avec une fonction associée
     * @param $path
     * @param $callable
     * @return route
     */
    public function get($path, $callable): route {
        $route = new route($path, $callable);

        $this->routes["GET"][] = $route;
        return $route;
    }

    /**
     * Pour un lien donné via un post, défini un chemin avec une fonction associée
     * @param $path
     * @param $callable
     * @return route
     */
    public function post($path, $callable): route {
        $route = new route($path, $callable);
        $this->routes["POST"][] = $route;
        return $route;
    }

    /**
     * Cherche la page correspondante au lien demandé
     * à partir de toutes les routes possibles
     * @throws RouterException si erreur il y a
     */
    public function run(): void {
        if(!isset($this->routes[$_SERVER['REQUEST_METHOD']])){
            throw new RouterException("REQUEST_METHOD n'existe pas");
        }

        foreach($this->routes[$_SERVER['REQUEST_METHOD']] as $route){
            if($route->match($this->url)){
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

function createAction($uri, $layout): Closure {
    if ($uri === '/') {
        $uri = 'homepage';
    }
    $uri = str_replace('-', '', $uri);

    $controllerName = ucfirst(ltrim($uri, '/'));
    $className = "Blog\\Controllers\\$controllerName";

    if (!(class_exists($className))) {
        $className = "Blog\\Controllers\\Error404";
    }

    return function() use ($className, $layout) {
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