<?php

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
     * @return Route
     */
    public function get($path, $callable): Route {
        $route = new Route($path, $callable);

        $this->routes["GET"][] = $route;
        return $route;
    }

    /**
     * Pour un lien donné via un post, défini un chemin avec une fonction associée
     * @param $path
     * @param $callable
     * @return Route
     */
    public function post($path, $callable): Route {
        $route = new Route($path, $callable);
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
        throw new RouterException('Pas de routes');
    }
}

/**
 * Initialisation de la session qu'importe le header
 */
session_start();
if (!isset($_SESSION['id_admin'])) {
    $_SESSION['id_admin'] = '';
}

/**
 * Initialisation du routage des URI
 */
$router = new Router(strtok($_SERVER["REQUEST_URI"], '?'));
$router->get('/', function(){ (new \Blog\Controllers\Homepage())->show(); });
$router->get('/homepage', function(){ (new \Blog\Controllers\Homepage())->show();  });

try {
    $router->run();
} catch (RouterException $e) {
    echo $e->getMessage();
    return;
}