<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;
use Blog\Views\Intramu as IntramuView;

/**
 * Contrôleur de la page de connexion
 */
class Intramu {
    private Layout $layout;
    private IntramuView $view;
    /**
     * Constructeur de la classe Intramu (contrôleur)
     * @param Layout $layout Instance de la classe Layout
     * @param IntramuView $view Instance de la classe IntramuView
     */
    public function __construct(Layout $layout, IntramuView $view) {
        $this->layout = $layout;
        $this->view = $view;
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

            // Gestion Admin(temporaire)
            if ($identifierLogs == 'superAdmin' && $passwordLogs == '8exs7JcEpGVfsI') {
                $_SESSION['identifier'] = $identifierLogs;
                $_SESSION['role'] = 'admin';
                header('Location: /homepage');
                exit();
            }

            $db = Database::getInstance();

            $loginModel = new \Blog\Models\Intramu($db);

            if ($loginModel->doLogsExist($identifierLogs, $passwordLogs)) {
                $_SESSION['identifier'] = $identifierLogs;
                $_SESSION['role'] = 'teacher';
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