<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;
use PDO;

class Dispatcher {
    /**
     * Cette méthode permet d'associer directement un enseignant à un stage, en fonction des données soumises via un formulaire `POST`, elle renvoie des messages d'erreurs appropriées si besoin sinon un message de validation.
     *
     * @param object $dispatcherModel Le modèle de gestion des données qui contient les méthodes pour récupérer les listes et insérer l'association.
     *
     * @return array Retourne un message de succès ou d'erreur concernant l'association ou la demande de remplissage des champs.
     */
    public function association_direct($dispatcherModel): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['searchInternship']) && isset($_POST['searchTeacher']) && $_POST['searchInternship'] !== '' && $_POST['searchTeacher'] !== '') {

            $listTeacher = $dispatcherModel->createListTeacher();
            $listStudent = $dispatcherModel->createListInternship();
            $listAssociate = $dispatcherModel->createListAssociate();

            if (in_array($_POST['searchTeacher'], $listTeacher) && in_array($_POST['searchInternship'], $listStudent)){
                if (!(in_array([$_POST['searchTeacher'], $_POST['searchInternship']], $listAssociate))) {
                    return ["", $dispatcherModel->insertResponsible()];
                }
                else {
                    return ["Cette association existe déjà", ""];
                }
            }
            else {
                return ["Internship_identifier ou Id_Teacher inexistant dans ce departement", ""];
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
            return ["Merci de remplir tout les champs", ""];
        }
        return ['',''];
    }

    /**
     * Cette méthode permet d'associer un enseignant à un stage en fonction des données reçues depuis un formulaire (via `$_POST`), elle renvoie des messages d'erreurs appropriées si besoin sinon un message de validation.
     *
     * @param object $dispatcherModel Modèle de gestion des données qui contient les méthodes pour récupérer les listes et insérer les associations.
     *
     * @return array Retourne une chaîne de caractères contenant des messages d'information ou d'erreur concernant l'état des associations.
     */
    public function association_after_sort($dispatcherModel): array {
        $listTeacher = $dispatcherModel->createListTeacher();
        $listInternship = $dispatcherModel->createListInternship();
        $listAssociate = $dispatcherModel->createListAssociate();
        $returnErrorMessage = '';
        $returnCheckMessage = '';

        foreach($_POST['listTupleAssociate'] as $tupleAssociate){
            $tmp = explode("$", $tupleAssociate);
            if (in_array($tmp[0], $listTeacher) && in_array($tmp[1], $listInternship)){
                if (!(in_array([$tmp[0], $tmp[1]], $listAssociate))) {
                    $returnCheckMessage .= $dispatcherModel->insertIs_responsible($tmp[0], $tmp[1], floatval($tmp[2]));
                }
                else {
                    $returnErrorMessage .= $tmp[0] . " et " . $tmp[1] . ", cette association existe déjà<br>";
                }
            }
            else {
                $returnErrorMessage .=  $tmp[0] . "ou" . $tmp[1] . ", inexistant dans ce departement<br>";
            }
        }
        return [$returnErrorMessage, $returnCheckMessage];
    }

    /**
     * Méthode `show` utilisée pour gérer l'affichage et la gestion des actions du tableau de bord lors de l'administration des départements, association après tri et avant tri, redirection sur la page de connexion en cas d'utilisateur non connecté, définition des chemins vers les fichiers utiles et gestion des $_POST.
     *
     * @return void
     */
    public function show(): void {

        if (isset($_SESSION['role_name']) && (
                (is_array($_SESSION['role_name']) && in_array('Admin_dep', $_SESSION['role_name'])) ||
                ($_SESSION['role_name'] === 'Admin_dep'))) {
            $db = Database::getInstance();
            $globalModel = new \Blog\Models\GlobalModel($db);
            $dispatcherModel = new \Blog\Models\Dispatcher($db, $globalModel);
            $errorMessageDirectAssoc = '';
            $checkMessageDirectAssoc = '';
            $errorMessageAfterSort = '';
            $checkMessageAfterSort = '';

            if (isset($_POST['action']) && ($_POST['action'] === 'search') && isset($_POST['searchType']) && ($_POST['searchType'] === 'searchInternship' || $_POST['searchType'] === 'searchTeacher')) {
                $results = $dispatcherModel->correspondTerms();

                header('Content-Type: application/json');
                echo json_encode($results);
                exit();
            }

            if (isset($_POST['action']) && ($_POST['action'] === 'TeachersForinternship') && isset($_POST['Internship_identifier']) && isset($_POST['dicoCoef'])) {
                $studentView = $dispatcherModel->RelevanceInternship($_POST['Internship_identifier'], $_POST['dicoCoef']);

                header('Content-Type: application/json');
                echo json_encode($studentView);
                exit();
            }

            if (isset($_POST['action-save']) && $_POST['action-save'] !== 'default') {
                $coefficients = [];
                foreach ($_POST['coef'] as $criteria => $coef) {
                    $coefficients[$criteria] = [
                        'coef' => $coef,
                        'is_checked' => $_POST['is_checked'][$criteria] ?? 0,
                        'name_criteria' => $criteria
                    ];
                }

                $dispatcherModel->saveCoefficients($coefficients, $_SESSION['identifier'], (int)$_POST['action-save']);
            }

            if (isset($_POST['searchInternship']) && isset($_POST['searchTeacher'])) {
                $tmpmessage = $this->association_direct($dispatcherModel);
                $errorMessageDirectAssoc = $tmpmessage[0];
                $checkMessageDirectAssoc = $tmpmessage[1];
            }

            if (isset($_POST['selectStudentSubmitted']) && isset($_POST['listTupleAssociate'])) {
                $tmpmessage = $this->association_after_sort($dispatcherModel);
                $errorMessageAfterSort = $tmpmessage[0];
                $checkMessageAfterSort = $tmpmessage[1];
            }

            $title = "Dispatcher";
            $cssFilePath = '_assets/styles/dispatcher.css';
            $jsFilePath = '_assets/scripts/dispatcher.js';
            $view = new \Blog\Views\Dispatcher($dispatcherModel, $errorMessageAfterSort, $errorMessageDirectAssoc, $checkMessageDirectAssoc, $checkMessageAfterSort);

            $layout = new Layout();
            $layout->renderTop($title, $cssFilePath);
            $view->showView();
            $layout->renderBottom($jsFilePath);
        }
        else {
            header('Location: /homepage');
        }
    }
}