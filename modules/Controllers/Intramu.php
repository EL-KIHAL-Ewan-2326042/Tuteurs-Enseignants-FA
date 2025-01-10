<?php
/**
 * Fichier contenant le contrôleur de la page de connexion à l'Intramu
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Controllers;

use Blog\Models\Teacher;
use Blog\Models\User;
use Blog\Views\layout\Layout;
use Includes\Database;

/**
 * Classe gérant les échanges de données entre
 * le modèle et la vue de la page de connexion à l'Intramu
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Intramu
{
    private Layout $_layout;

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Layout $layout Instance de la classe Layout
     *                       servant de vue pour la mise en page
     */
    public function __construct(Layout $layout)
    {
        $this->_layout = $layout;
    }

    /**
     * Liaison entre la vue et le layout, et affichage
     * Gestion de la soumission du formulaire de connexion
     * Si l'utilisateur est connecté, alors il est deconnecté
     *
     * @return void
     */
    public function show(): void
    {
        if (isset($_SESSION['identifier'])) {
            unset($_SESSION['identifier']);
            unset($_SESSION['role_name']);
            unset($_SESSION['role_department']);
            header('Location: /homepage');
            exit();
        }

        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['identifier'])
            && isset($_POST['password'])
        ) {
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
                $_SESSION['role_department'] = $userModel
                    ->getRoleDepartment($identifierLogs);
                $_SESSION['address'] = $teacherModel->getAddress($identifierLogs);
                header('Location: /homepage');
                exit();
            } else {
                $errorMessage = 'Identifiant ou mot de passe incorrect';
            }
        }

        $title = "Connexion";
        $cssFilePath = '_assets/styles/intramu.css';
        $jsFilePath = '';
        $view = new \Blog\Views\intramu\Intramu($errorMessage);

        $this->_layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->_layout->renderBottom($jsFilePath);
    }
}