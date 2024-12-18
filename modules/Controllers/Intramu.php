<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;

/**
 * Contrôleur de la page de connexion
 */
class Intramu {
    private Layout $layout;

    /**
     * Constructeur de la classe Intramu (contrôleur)
     * @param Layout $layout Instance de la classe Layout
     */
    public function __construct(Layout $layout) {
        $this->layout = $layout;
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
            unset($_SESSION['role_name']);
            unset($_SESSION['role_department']);
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
                $_SESSION['fullName'] = $loginModel->getFullName($identifierLogs);
                $_SESSION['roles'] = $loginModel->getRoles($identifierLogs);
                $_SESSION['role_name'] = $loginModel->getHighestRole($identifierLogs);
                $_SESSION['role_department'] = $loginModel->getRole_department($identifierLogs);
                $_SESSION['address'] = $loginModel->getAddress($identifierLogs);
                header('Location: /homepage');
                exit();
            } else {
                $errorMessage = 'Identifiant ou mot de passe incorrect';
            }
        }

        $title = "Connexion";
        $cssFilePath = '_assets/styles/login.css';
        $jsFilePath = '';
        $view = new \Blog\Views\Intramu($errorMessage);

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}