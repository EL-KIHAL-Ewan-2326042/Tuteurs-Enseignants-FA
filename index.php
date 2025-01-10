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
 * Permet de définir des routes gérer les requêtes vers le TutorMap
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/layout
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Router
{
    private string $_url;
    private array $_routes = [];

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param string $_url l'url fournie par l'utilisateur
     */
    public function __construct(string $_url)
    {
        $this->_url = $_url;
    }

    /**
     * Ajoute une route pour une requête HTTP de type GET.
     *
     * Cette méthode permet de définir une route associée à une URL spécifique
     * et à un contrôleur ou une fonction de rappel qui sera exécutée
     * lorsque cette URL est sollicitée via une requête GET.
     *
     * @param string                $path     L'URL associée à
     *                                        la route.
     * @param callable|class-string $callable Le contrôleur ou
     *                                        la fonction associée à la route.
     *
     * @return route L'objet route correspondant à la nouvelle route ajoutée.
     */
    public function get($path, $callable): route
    {
        $route = new route($path, $callable);

        $this->_routes["GET"][] = $route;
        return $route;
    }
    /**
     * Ajoute une route pour une requête HTTP de type POST.
     *
     * Cette méthode permet de définir une route associée à une URL spécifique
     * et à un contrôleur ou une fonction de rappel qui sera appelée
     * lorsque cette URL est sollicitée via une requête POST.
     *
     * @param string                $path     L'URL associée
     *                                        à la route.
     * @param callable|class-string $callable Le contrôleur
     *                                        ou la fonction associée à la route.
     *
     * @return route L'objet route correspondant à la nouvelle route ajoutée.
     */
    public function post(string $path, callable|string $callable): route
    {
        $route = new route($path, $callable);
        $this->_routes["POST"][] = $route;
        return $route;
    }


    /**
     * Cherche la page correspondante au lien demandé
     * à partir de toutes les routes possibles
     *
     * @throws RouterException si erreur il y a
     *
     * @return void
     */
    public function run(): void
    {
        if (!isset($this->_routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException("REQUEST_METHOD n'existe pas");
        }

        foreach ($this->_routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->match($this->_url)) {
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

/**
 * Crée une action associée à une URI donnée.
 *
 * Cette fonction génère une closure qui
 * instancie un contrôleur basé sur l'URI fournie
 * et exécute la méthode `show()` du contrôleur avec la mise en page spécifiée.
 * Si aucun contrôleur correspondant n'existe,
 * un contrôleur de type "Error404" est utilisé.
 *
 * @param string $uri    L'URI pour laquelle
 *                       l'action doit être
 *                       créée (e.g.,
 *                       '/about-us').
 * @param mixed  $layout La mise en page
 *                       à utiliser pour
 *                       l'affichage du
 *                       contenu.
 *
 * @return Closure La closure qui, une fois appelée,
 * instancie le contrôleur approprié
 * et exécute sa méthode `show()`.
 */
function createAction(string $uri, mixed $layout): Closure
{
    if ($uri === '/') {
        $uri = 'homepage';
    }
    $uri = preg_replace_callback(
        '/-(.)/', function ($matches) {
            return strtoupper($matches[1]);
        }, $uri
    );


    $controllerName = ucfirst(ltrim($uri, '/'));
    $className = "Blog\\Controllers\\$controllerName";

    if (!(class_exists($className))) {
        $className = "Blog\\Controllers\\Error404";
    }

    return function () use ($className, $layout) {
        (new $className($layout))->show();
    };
}

$layout = new \Blog\Views\layout\Layout();
$action = createAction($uri, $layout);
$router->get($uri, $action);
$router->post($uri, $action);

try {
    $router->run();
} catch (RouterException $e) {
    echo $e->getMessage();
    return;
}
