<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;
use Blog\Views\Intramu as IntramuView;
use Blog\Models\Intramu as IntramuModel;

/**
 * Contrôleur de la page de connexion
 */
class Intramu {
    private Layout $layout;
    private IntramuView $view;
    private IntramuModel $model;
    /**
     * Constructeur de la classe Intramu (contrôleur)
     * @param Layout $layout Instance de la classe Layout
     * @param IntramuView $view Instance de la classe IntramuView
     * @param IntramuModel $model Instance de la classe IntramuModel
     */
    public function __construct(Layout $layout, IntramuView $view, IntramuModel $model) {
        $this->layout = $layout;
        $this->view = $view;
        $this->model = $model;
    }

    /**
     * Liaison entre la vue et le layout et affichage
     * Gestion de la soumission du formulaire de connexion
     * Si l'utilisateur est connecte, alors il est deconnecte
     * @return void
     */
    public function show(): void {
        if (isset($_SESSION['identifier'])) {
            unset($_SESSION['identifier']);
            unset($_SESSION['role']);
            header('Location: /homepage');
            exit();
        }

        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['identifier']) && isset($_POST['password'])) {
            $identifierLogs = $_POST['identifier'];
            $passwordLogs = $_POST['password'];
            $db = Database::getInstance();

            $loginModel = new \Blog\Models\Intramu($db);

            if ($loginModel->doLogsExist($identifierLogs, $passwordLogs)) {
                $_SESSION['identifier'] = $identifierLogs;

                $row = $loginModel->fetchAll($identifierLogs);
                $_SESSION['role'] = $loginModel->getRole($identifierLogs);
                $_SESSION['role_department'] = $loginModel->getRole_department($identifierLogs);
                $_SESSION['address'] = $row['adresse'];
                header('Location: /homepage');
                exit();
            } else {
                $errorMessage = 'Identifiant ou mot de passe incorrect';
            }
        }

        $title = "Connexion";
        $cssFilePath = '_assets/styles/login.css';
        $jsFilePath = '';

        $this->view = new IntramuView($errorMessage);
        $this->layout->renderTop($title, $cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}