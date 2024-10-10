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
     * @return void
     */
    public function show(): void {
        $title = "Connexion";
        $cssFilePath = '_assets/styles/login.css';
        $jsFilePath = '';
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['identifier']) && isset($_POST['password'])) {
            $identifierLogs = $_POST['identifier'];
            $passwordLogs = $_POST['password'];
            $db = Database::getInstance();

            $loginModel = new \Blog\Models\Intramu($db);

            if ($loginModel->doLogsExist($identifierLogs, $passwordLogs)) {
                $_SESSION['identifier'] = $identifierLogs;
                header('Location: /homepage');
                exit();
            } else {
                $errorMessage = 'Pseudo ou mot de passe incorrect';
            }
        }

        $view = new \Blog\Views\Intramu($errorMessage);

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}