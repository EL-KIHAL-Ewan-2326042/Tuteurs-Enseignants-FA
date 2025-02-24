<?php
/**
 * Fichier contenant le contrôleur de la page 'Accueil'
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
use Blog\Models\Internship;
use Blog\Models\Student;
use Blog\Models\Teacher;
use Blog\Views\layout\Layout;
use includes\Database;

/**
 * Classe gérant les échanges de données entre
 * le modèle et la vue de la page 'Accueil'
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
class Homepage
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
     * Controlleur de la homepage.
     * Elle gere des requêtes post, via le modèle, pour récupérer
     * des informations telles que les résultats de recherche
     * ou les informations de l'étudiant selectioné
     *
     * @return void
     */
    public function show(): void
    {

        if (!isset($_SESSION['identifier'])) {
            header('Location: /intramu');
            return;
        }

        $db = Database::getInstance();
        $internshipModel = new Internship($db);
        $studentModel = new Student($db);
        $teacherModel = new Teacher($db);
        $departmentModel = new Department($db);

        $view = new \Blog\Views\homepage\Homepage(
            $internshipModel, $studentModel, $teacherModel, $departmentModel
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'search' && isset($_POST['search'])) {
                    $results = $studentModel->correspondTerms();
                    header('Content-Type: application/json');
                    echo json_encode($results);
                    return;
                }

                if ($_POST['action'] === 'select_student'
                    && isset($_POST['student_id'])
                    && isset($_POST['student_firstName'])
                    && isset($_POST['student_lastName'])
                ) {
                    $studentId = $_POST['student_id'];
                    $firstName = $_POST['student_firstName'];
                    $secondName = $_POST['student_lastName'];
                    $address = $internshipModel->getInternshipAddress($studentId);

                    $_SESSION['selected_student'] = [
                        'id' => $studentId,
                        'firstName' => $firstName,
                        'lastName' => $secondName,
                        'address' => $address
                    ];
                }
            }
        }

        $title = "Accueil";
        $cssFilePath = '/_assets/styles/homepage.css';
        $jsFilePath = '/_assets/scripts/homepage.js';

        $this->_layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->_layout->renderBottom($jsFilePath);
    }
}