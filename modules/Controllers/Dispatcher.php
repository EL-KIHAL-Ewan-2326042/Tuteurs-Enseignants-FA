<?php
/**
 * Fichier contenant le contrôleur de la page 'Répartiteur'
 *
 * PHP version 8.3
 *
 * @category View
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
use Blog\Models\User;
use Blog\Views\layout\Layout;
use Includes\Database;

/**
 * Classe gérant les échanges de données entre
 * le modèle et la vue de la page 'Répartiteur'
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
class Dispatcher
{
    /**
     * Cette méthode permet d'associer directement un enseignant à un stage,
     * en fonction des données soumises via un formulaire `POST`, elle renvoie
     * des messages d'erreurs appropriées si besoin sinon un message de validation.
     *
     * @param Teacher    $teacherModel    Instance de la classe Teacher
     *                                    servant de modèle
     * @param Internship $internshipModel Instance de la classe Internship
     *                                    servant de modèle
     *
     * @return array Retourne un message de succès ou d'erreur concernant
     * l'association ou la demande de remplissage des champs.
     */
    public function associationDirect(Teacher $teacherModel,
        Internship $internshipModel
    ): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['searchInternship'])
            && isset($_POST['searchTeacher'])
            && $_POST['searchInternship'] !== ''
            && $_POST['searchTeacher'] !== ''
        ) {

            $listTeacher = $teacherModel->createListTeacher();
            $listStudent = $internshipModel->createListInternship();
            $listAssociate = $internshipModel->createListAssociate();

            if (in_array($_POST['searchTeacher'], $listTeacher)
                && in_array($_POST['searchInternship'], $listStudent)
            ) {
                if (!(in_array(
                    [$_POST['searchTeacher'],
                    $_POST['searchInternship']],
                    $listAssociate
                ))
                ) {
                    return ["", $internshipModel->insertResponsible()];
                } else {
                    return ["Cette association existe déjà", ""];
                }
            } else {
                return
                ["Internship_identifier ou Id_Teacher "
                . "inexistant dans ce departement", ""];
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return ["Merci de remplir tout les champs", ""];
        }
        return ['',''];
    }

    /**
     * Cette méthode permet d'associer un enseignant à un stage en fonction des
     * données reçues depuis un formulaire (via `$_POST`), elle renvoie des messages
     * d'erreurs appropriées si besoin sinon un message de validation.
     *
     * @param Teacher    $teacherModel    Instance de la classe Teacher
     *                                    servant de modèle
     * @param Internship $internshipModel Instance de la classe Internship
     *                                    servant de modèle
     *
     * @return array Retourne une chaîne de caractères contenant des
     * messages d'information ou d'erreur concernant l'état des associations.
     */
    public function associationAfterSort(
        Teacher $teacherModel,
        Internship $internshipModel
    ): array {
        $listTeacher = $teacherModel->createListTeacher();
        $listInternship = $internshipModel->createListInternship();
        $listAssociate = $internshipModel->createListAssociate();
        $returnErrorMessage = '';
        $returnCheckMessage = '';

        foreach ($_POST['listTupleAssociate'] as $tupleAssociate) {
            $tmp = explode("$", $tupleAssociate);
            if (in_array($tmp[0], $listTeacher)
                && in_array($tmp[1], $listInternship)
            ) {
                if (!(in_array([$tmp[0], $tmp[1]], $listAssociate))) {
                    $returnCheckMessage .= $internshipModel
                        ->insertIsResponsible($tmp[0], $tmp[1], floatval($tmp[2]));
                } else {
                    $returnErrorMessage .= $tmp[0] . " et "
                        . $tmp[1] . ", cette association existe déjà<br>";
                }
            } else {
                $returnErrorMessage .=  $tmp[0] . " ou "
                    . $tmp[1] . ", inexistant dans ce departement<br>";
            }
        }
        return [$returnErrorMessage, $returnCheckMessage];
    }

    /**
     * Méthode `show` utilisée pour gérer l'affichage et la gestion des actions
     * du tableau de bord lors de l'administration des départements, association
     * après tri et avant tri, redirection sur la page de connexion en cas
     * d'utilisateur non connecté, définition des chemins vers les fichiers
     * utiles et gestion des $_POST.
     *
     * @return void
     */
    public function show(): void
    {


        if (isset($_SESSION['role_name'])
            && ((is_array($_SESSION['role_name'])
            && in_array('Admin_dep', $_SESSION['role_name']))
            || ($_SESSION['role_name'] === 'Admin_dep'))
        ) {
            $db = Database::getInstance();

            $teacherModel = new Teacher($db);
            $studentModel = new Student($db);
            $internshipModel = new Internship($db);
            $userModel = new User($db);
            $departmentModel = new Department($db);

            $errorMessageDirectAssoc = '';
            $checkMessageDirectAssoc = '';
            $errorMessageAfterSort = '';
            $checkMessageAfterSort = '';

            if (isset($_POST['action'])
                && ($_POST['action'] === 'search')
                && isset($_POST['searchType'])
                && ($_POST['searchType'] === 'searchInternship'
                || $_POST['searchType'] === 'searchTeacher')
            ) {

                if ($_POST['searchType'] === 'searchTeacher') {
                    $results = $teacherModel->correspondTermsTeacher();
                } else {
                    $results = $studentModel->correspondTermsStudent();
                }

                header('Content-Type: application/json');
                echo json_encode($results);
                exit();
            }

            if (isset($_POST['action'])
                && ($_POST['action'] === 'TeachersForinternship')
                && isset($_POST['Internship_identifier'])
                && isset($_POST['dicoCoef'])
            ) {
                $dictCoef = json_decode($_POST['dicoCoef'], true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo json_encode(['error' => 'Invalid dicoCoef data.']);
                    exit();
                }

                $studentView = $internshipModel->relevanceInternship(
                    $_POST['Internship_identifier'],
                    $dictCoef
                );

                header('Content-Type: application/json');
                echo json_encode($studentView);
                exit();
            }

            if (isset($_POST['action'])
                && ($_POST['action'] === 'getDistance')
                && isset($_POST['Internship_identifier'])
                && isset($_POST['Id_teacher'])
            ) {

                $distance = $internshipModel->getDistance(
                    $_POST['Internship_identifier'],
                    $_POST['Id_teacher'], false
                );

                header('Content-Type: application/json');
                echo json_encode($distance);
                exit();
            }

            if (isset($_POST['action'])
                && ($_POST['action'] === 'getHistory')
                && isset($_POST['Student_number'])
            ) {

                $history = $internshipModel
                    ->getStudentHistory($_POST['Student_number']);

                header('Content-Type: application/json');
                echo json_encode($history);
                exit();
            }

            if (isset($_POST['action'])
                && ($_POST['action'] === 'getDisciplines')
                && isset($_POST['Id_teacher'])
            ) {

                $discipline = $teacherModel->getDisciplines($_POST['Id_teacher']);
                header('Content-Type: application/json');
                echo json_encode($discipline);
                exit();
            }

            if (isset($_POST['action'])
                && ($_POST['action'] === 'getTeacherAddresses')
                && isset($_POST['Id_teacher'])
            ) {

                $teacherAdresses = $teacherModel->getAddress($_POST['Id_teacher']);

                header('Content-Type: application/json');
                echo json_encode($teacherAdresses);
                exit();
            }

            if (isset($_POST['action-save'])
                && $_POST['action-save'] !== 'default'
            ) {
                $coefficients = [];
                foreach ($_POST['coef'] as $criteria => $coef) {
                    $coefficients[$criteria] = [
                        'coef' => $coef,
                        'is_checked' => $_POST['is_checked'][$criteria] ?? 0,
                        'name_criteria' => $criteria
                    ];
                }

                $userModel->saveCoefficients(
                    $coefficients,
                    $_SESSION['identifier'],
                    (int)$_POST['action-save']
                );
            }

            if (isset($_POST['searchInternship'])
                && isset($_POST['searchTeacher'])
            ) {
                $tmpmessage = $this
                    ->associationDirect($teacherModel, $internshipModel);
                $errorMessageDirectAssoc = $tmpmessage[0];
                $checkMessageDirectAssoc = $tmpmessage[1];
            }

            if (isset($_POST['selectStudentSubmitted'])
                && isset($_POST['listTupleAssociate'])
            ) {
                $tmpmessage = $this
                    ->associationAfterSort($teacherModel, $internshipModel);
                $errorMessageAfterSort = $tmpmessage[0];
                $checkMessageAfterSort = $tmpmessage[1];
            }

            $title = "Repartiteur";
            $cssFilePath = '_assets/styles/dispatcher.css';
            $jsFilePath = '_assets/scripts/dispatcher.js';
            $view = new \Blog\Views\dispatcher\Dispatcher(
                $internshipModel, $userModel, $teacherModel,
                $departmentModel, $errorMessageAfterSort,
                $errorMessageDirectAssoc, $checkMessageDirectAssoc,
                $checkMessageAfterSort
            );

            $layout = new Layout();
            $layout->renderTop($title, $cssFilePath);
            $view->showView();
            $layout->renderBottom($jsFilePath);
        } else {
            header('Location: /homepage');
        }
    }
}