<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Database;

/**
 * Contrôleur de la page de connexion
 */
class Intramu {

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

        $view = new \Blog\Views\Intramu($errorMessage);

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}