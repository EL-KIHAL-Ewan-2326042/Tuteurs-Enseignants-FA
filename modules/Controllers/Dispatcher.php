<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;
use PDO;

class Dispatcher {
    /**
     * @return void
     */
    public function association_direct($dispatcherModel): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['searchInternship']) && isset($_POST['searchTeacher']) && $_POST['searchInternship'] !== '' && $_POST['searchTeacher'] !== '') {

            $listTeacher = $dispatcherModel->createListTeacher();
            $listStudent = $dispatcherModel->createListInternship();
            $listAssociate = $dispatcherModel->createListAssociate();

            if (in_array($_POST['searchTeacher'], $listTeacher) && in_array($_POST['searchInternship'], $listStudent)){
                if (!(in_array([$_POST['searchTeacher'], $_POST['searchInternship']], $listAssociate))) {
                    return $dispatcherModel->insertResponsible();
                }
                else {
                    return "Cette association existe déjà";
                }
            }
            else {
                return "Internship_identifier ou Id_Teacher inexistant dans ce departement";
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
            return "Merci de remplir tout les champs";
        }
        return '';
    }

    /**
     * @return void
     */
    public function association_after_sort($dispatcherModel): string {
        $listTeacher = $dispatcherModel->createListTeacher();
        $listInternship = $dispatcherModel->createListInternship();
        $listAssociate = $dispatcherModel->createListAssociate();

        $returnMessage = '';
        foreach($_POST['listTupleAssociate'] as $tupleAssociate){
            $tmp = explode("$", $tupleAssociate);
            if (in_array($tmp[0], $listTeacher) && in_array($tmp[1], $listInternship)){
                if (!(in_array([$tmp[0], $tmp[1]], $listAssociate))) {
                    $returnMessage .= $dispatcherModel->insertIs_responsible($tmp[0], $tmp[1], floatval($tmp[2]));
                }
                else {
                    $returnMessage .= $tmp[0] . " et " . $tmp[1] . ", cette association existe déjà<br>";
                }
            }
            else {
                $returnMessage .=  $tmp[0] . "ou" . $tmp[1] . ", inexistant dans ce departement<br>";
            }
        }
        return $returnMessage;
    }

    public function show(): void {

        if (isset($_SESSION['role_name']) && (
                (is_array($_SESSION['role_name']) && in_array('Admin_dep', $_SESSION['role_name'])) ||
                ($_SESSION['role_name'] === 'Admin_dep'))) {
            $db = Database::getInstance();
            $globalModel = new \Blog\Models\GlobalModel($db);
            $dispatcherModel = new \Blog\Models\Dispatcher($db, $globalModel);
            $errorMessage1 = '';
            $errorMessage2 = '';

            if (isset($_POST['searchType']) && ($_POST['searchType'] === 'searchInternship' || $_POST['searchType'] === 'searchTeacher')) {
                $results = $dispatcherModel->correspondTerms();

                header('Content-Type: application/json');
                echo json_encode($results);
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
                $errorMessage1 = $this->association_direct($dispatcherModel);
            }

            if (isset($_POST['selectStudentSubmitted']) && isset($_POST['listTupleAssociate'])) {
                $errorMessage2 = $this->association_after_sort($dispatcherModel);
            }

            $title = "Dispatcher";
            $cssFilePath = '_assets/styles/dispatcher.css';
            $jsFilePath = '_assets/scripts/dispatcher.js';
            $view = new \Blog\Views\Dispatcher($dispatcherModel, $errorMessage1, $errorMessage2);

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