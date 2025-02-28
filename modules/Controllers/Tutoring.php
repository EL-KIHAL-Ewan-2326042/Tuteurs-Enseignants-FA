<?php
/**
 * Fichier contenant le contrôleur de la page 'Stages'
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

use Blog\Models\Department;
use Blog\Models\User;
use Blog\Views\tutoring\Tutoring as TutoringView;
use Blog\Views\layout\Layout;
use includes\Database;

/**
 * Classe gérant les échanges de données entre
 * le modèle et la vue de la page 'Stages'
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
class Tutoring
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
     * Contrôleur de la page 'Stages'
     *
     * @return void
     */
    public function show(): void
    {

        if (isset($_SESSION['roles'])
            && ((is_array($_SESSION['roles'])
            && in_array('Admin_dep', $_SESSION['roles']))
            || ($_SESSION['roles'] === 'Admin_dep'))
        ) {

            $db = Database::getInstance();
            $userModel = new User($db);
            $departmentModel = new Department($db);

            $view = new TutoringView($userModel, $departmentModel);

            $title = "Stages";
            $cssFilePath = '_assets/styles/tutoring.css';
            $jsFilePath = '_assets/scripts/tutoring.js';

            $this->_layout->renderTop($title, $cssFilePath);
            $view->showView();
            $this->_layout->renderBottom($jsFilePath);
        } else {
            header('Location: /homepage');
        }
    }
}