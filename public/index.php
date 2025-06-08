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

use Blog\Controllers\AjaxController;
use Blog\Models\Teacher;
use Blog\Views;
use Blog\Views\components\ViewStage;
use Blog\Views\dashboard\Export;
use Blog\Views\dashboard\Import;
use includes\Autoloader;
use includes\Database;
use includes\exceptions\RouterException;
use includes\Route;
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../includes/Autoloader.php';
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
        foreach ($this->_routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->match($this->_url)) {
                $route->call();
                return;
            }
        }
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

// Redirection vers dashboard si on recharge une des sous-pages du dashboard
if (preg_match('#^/dashboard/([^/]+)#', $uri)) {
    header('Location: /dashboard');
    exit;
}

if (isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'internship=') !== false) {
    $queryParams = [];
    parse_str($_SERVER['QUERY_STRING'], $queryParams);

    // Supprimer le paramètre internship
    unset($queryParams['internship']);

    // Reconstruire l'URL sans ce paramètre
    $newUrl = $uri;
    if (!empty($queryParams)) {
        $newUrl .= '?' . http_build_query($queryParams);
    }

    header('Location: ' . $newUrl);
    exit;
}

$router = new Router($uri);

$router->get('/api/datatable/ask', function() {
    (new \Blog\Controllers\AjaxController())->handleDataTable("ask");
});

$router->post('/api/datatable/ask', function() {
    (new \Blog\Controllers\AjaxController())->handleDataTable("ask");
});

$router->get('/api/datatable/account', function() {
    (new \Blog\Controllers\AjaxController())->handleDataTable("account");
});

$router->post('/api/datatable/account', function() {
    (new \Blog\Controllers\AjaxController())->handleDataTable("account");
});

$router->get('/api/datatable/stage/([A-Za-z0-9]+)', function() {
    $search = $_POST['search']['value'] ?? '';
    $order = [
        'column' => $_POST['order'][0]['column'] ?? 0,
        'dir' => $_POST['order'][0]['dir'] ?? 'ASC'
    ];
    (new \Blog\Controllers\AjaxController())->getViewStage( basename($_SERVER['REQUEST_URI']), $search, $order );
});
$router->post('/api/datatable/stage/([A-Za-z0-9]+)', function() {
    $search = $_POST['search']['value'] ?? '';
    $order = [
        'column' => $_POST['order'][0]['column'] ?? 0,
        'dir' => $_POST['order'][0]['dir'] ?? 'ASC'
    ];
    (new \Blog\Controllers\AjaxController())->getViewStage( basename($_SERVER['REQUEST_URI']), $search, $order );
});
$router->get('/api/viewStage/([A-Za-z0-9]+)', function () {
    \Blog\Views\components\ViewStage::render(basename($_SERVER['REQUEST_URI']));
});
$router->get('/api/dispatcherViewStage/([A-Za-z0-9]+)', function () {
    \Blog\Views\components\DispatcherViewStage::render(basename($_SERVER['REQUEST_URI']));
});
$router->post('/api/update-internship-request', function() {
    $db = Database::getInstance();
    $internshipModel = new \Blog\Models\Internship($db);

    $teacher = $_POST['teacher'] ?? null;
    $internship = $_POST['internship'] ?? null;
    if ($teacher && $internship) {
        $result = $internshipModel->updateSearchedStudentInternship(true, $teacher, $internship);
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Request updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => $result]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    }
});


$router->post('/api/dispatch-list', function () {
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search = $_POST['search']['value'] ?? '';
    $order = [
        'column' => $_POST['order'][0]['column'] ?? 0,
        'dir' => $_POST['order'][0]['dir'] ?? 'ASC'
    ];

    (new \Blog\Controllers\AjaxController())->getDispatchList($start, $length, $search, $order);
});



$router->post('/api/update-teacher-capacity', function () {
    $db = Database::getInstance();
    $model = new Teacher($db);

    $idTeacher = $_POST['searchTeacher'] ?? null;
    $maxStage = $_POST['maxInterns'] ?? null;
    $maxAlternance = $_POST['maxApprentices'] ?? null;

    if ($idTeacher !== null && ($maxStage !== null || $maxAlternance !== null)) {
        $result = $model->updateCapacities($idTeacher, $maxStage, $maxAlternance);
        if ($result === true) {
            // Redirection vers le tableau de bord
            header("Location: /dashboard");
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => $result]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    }
});


$router->get('/api/import', function() {
    $category = $_GET['category'] ?? '';
    $view = new Import($category);
    $view->showView();
});

$router->get('/api/export', function() {
    $category = $_GET['category'] ?? '';
    $view = new Export($category);
    $view->showView();
});

$router->get('/api/association', function() {
    $view = new \Blog\Views\dashboard\Association();
    $view->showView();
});

$router->get('/api/parametrage', function() {
    $view = new \Blog\Views\dashboard\Setting();
    $view->showView();
});

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
        $uri = 'intramu';
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

$layout =  new Views\layout\Layout();
$action = createAction($uri, $layout);
$router->get($uri, $action);
$router->post($uri, $action);

try {
    $router->run();
} catch (RouterException $e) {
    echo $e->getMessage();
    return;
}
