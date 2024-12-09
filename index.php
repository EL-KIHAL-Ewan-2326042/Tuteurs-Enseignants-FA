<?php
use Includes\Autoloader;
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

//Instanciation de classes nécessaires pour About Us
$layout = new \Blog\Views\Layout();
$aboutUsView = new \Blog\Views\Aboutus();
$aboutUsController = new \Blog\Controllers\Aboutus($layout,$aboutUsView);

//Instanciation de classes nécessaires pour le Dashboard
$dashboardView = new \Blog\Views\Dashboard();
$dashboardController = new \Blog\Controllers\Dashboard($layout,$dashboardView);

//Instanciation de classes nécessaire pour Intramu
$errorMessage = '';
$db = \Includes\Database::getInstance();
$intramuView = new \Blog\Views\Intramu($errorMessage);
$intramuModel = new \Blog\Models\Intramu($db);
$intramuController = new \Blog\Controllers\Intramu($layout,$intramuView,$intramuModel);

//Instanciation de classes nécessaires pour Homepage
$homepageModel = new \Blog\Models\Homepage($db);
$homepageView = new \Blog\Views\Homepage($homepageModel);
$homepageController = new \Blog\Controllers\Homepage($layout,$homepageView);

//Instanciation de classe nécessaires pour Mentionslegales
$mentionLegView = new \Blog\Views\Mentionslegales();
$mentionLegController = new \Blog\Controllers\Mentionslegales($layout,$mentionLegView);

/**
 * Initialisation du routage des URI
 */
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$router = new Router($uri);

function createAction($uri,$layout): Closure {
    if ($uri === '/') {
        $uri = 'homepage';
    }
    $uri = str_replace('-', '', $uri);

    $controllerName = ucfirst(ltrim($uri, '/'));

    $className = "Blog\\Controllers\\$controllerName";

    if (class_exists($className)) {
        return function() use ($className,$layout,$controllerName) {
            if($className === 'Blog\Controllers\Intramu') {
                $db = \Includes\Database::getInstance();
                $view = new \Blog\Views\Intramu('');
                $model = new \Blog\Models\Intramu($db);
                $controller = new $className($layout,$view,$model);
            } else if ($className === 'Blog\Controllers\Homepage'){
                $db = \Includes\Database::getInstance();
                $model = new \Blog\Models\Homepage($db);
                $view = new \Blog\Views\Homepage($model);
                $controller = new $className($layout,$view);
            } else {
                $view = "Blog\\Views\\$controllerName";
                $viewInstance = new $view();
                $controller = new $className($layout,$viewInstance);
            }
            $controller->show();
        };
    } elseif (!(class_exists($className))) {
        $className = "Blog\\Controllers\\Error404";
    }

    return function() use ($className) {
        (new $className())->show();
    };
}

$action = createAction($uri,$layout);
$router->get($uri, $action);
$router->post($uri, $action);

try {
    $router->run();
} catch (RouterException $e) {
    echo $e->getMessage();
    return;
}