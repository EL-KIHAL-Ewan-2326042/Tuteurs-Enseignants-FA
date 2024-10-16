<?php

require_once '_assets/includes/Autoloader.php';
autoloader::register();
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
$router = new Router(strtok($_SERVER["REQUEST_URI"], '?'));
$getRoutes = [
    '/' => function () {
        (new \Blog\Controllers\Homepage())->show();
    },
    '/homepage' => function () {
        (new \Blog\Controllers\Homepage())->show();
    },
    '/dashboard' => function() {
        (new  \Blog\Controllers\Dashboard())->show();
    },
    '/intramu' => function () {
        (new \Blog\Controllers\Intramu())->show();
    },
    '/hello' => function() { echo 'Hello World';
    },
    '/aboutus' => function () {
        (new \Blog\Controllers\AboutUs())->show();
    },
    '/mentions-legales' => function () {
        (new \Blog\Controllers\MentionLeg())->show();
    }
];

$postRoutes = [
    '/intramu' => function () {
        (new \Blog\Controllers\Intramu())->show();
    },
    '/dashboard' => function () {
        (new \Blog\Controllers\Dashboard())->show();
}
];

foreach ($getRoutes as $uri => $action) {
    $router->get('/' . $uri, $action);
}

foreach ($postRoutes as $uri => $action) {
    $router->post('/' . $uri, $action);
}
try {
    $router->run();
} catch (RouterException $e) {
    echo $e->getMessage();
    return;
}