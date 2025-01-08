<?php
namespace Blog\Controllers;

use Blog\Models\Teacher;
use Blog\Models\User;
use Blog\Views\layout\Layout;
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

            $userModel = new User($db);
            $teacherModel = new Teacher($db);

            if ($userModel->doLogsExist($identifierLogs, $passwordLogs)) {
                $_SESSION['identifier'] = $identifierLogs;
                $_SESSION['fullName'] = $teacherModel->getFullName($identifierLogs);
                $_SESSION['roles'] = $userModel->getRoles($identifierLogs);
                $_SESSION['role_name'] = $userModel->getHighestRole($identifierLogs);
                $_SESSION['role_department'] = $userModel->getRole_department($identifierLogs);
                $_SESSION['address'] = $teacherModel->getAddress($identifierLogs);
                header('Location: /homepage');
                exit();
            } else {
                $errorMessage = 'Identifiant ou mot de passe incorrect';
            }
        }

        $title = "Connexion";
        $cssFilePath = '_assets/styles/login.css';
        $jsFilePath = '';
        $view = new \Blog\Views\intramu\Intramu($errorMessage);

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}