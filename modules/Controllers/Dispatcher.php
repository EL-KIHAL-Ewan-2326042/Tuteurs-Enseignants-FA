<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;
use PDO;

class Dispatcher {
    /**
     * @return void
     */
    public function association($db, $dispatcherModel): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Internship_identifier']) && isset($_POST['Id_teacher']) && $_POST['Internship_identifier'] !== '' && $_POST['Id_teacher'] !== '') {

            $listTeacher = $dispatcherModel->createListTeacher();
            $listStudent = $dispatcherModel->createListInternship();
            $listAssociate = $dispatcherModel->createListAssociate();

            if (in_array($_POST['Id_teacher'], $listTeacher) && in_array($_POST['Internship_identifier'], $listStudent)){
                if (!(in_array([$_POST['Id_teacher'], $_POST['Internship_identifier']], $listAssociate))) {
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

    public function show(): void {

        if (isset($_SESSION['role_name']) && (
                (is_array($_SESSION['role_name']) && in_array('Admin_dep', $_SESSION['role_name'])) ||
                ($_SESSION['role_name'] === 'Admin_dep'))) {

            $db = Database::getInstance();
            $globalModel = new \Blog\Models\GlobalModel($db);
            $dispatcherModel = new \Blog\Models\Dispatcher($db, $globalModel);
            $errorMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action']) && $_POST['action'] === 'save') {

                    $coefficients = [];
                    foreach ($_POST['coef'] as $criteria => $coef) {
                        $coefficients[$criteria] = $coef;
                    }

                    $dispatcherModel->saveCoefficients($coefficients, $_SESSION['identifier']);
                }

                if (isset($_POST['Internship_identifier']) && isset($_POST['Id_teacher'])) {
                    $errorMessage = $this->association($db, $dispatcherModel);
                }

                if (isset($_POST['id_teacher'])) {
                    $dispatcherModel->insertIs_responsible();
                }
            }


            $title = "Dispatcher";
            $cssFilePath = '_assets/styles/dispatcher.css';
            $jsFilePath = '_assets/scripts/dispatcher.js';
            $view = new \Blog\Views\Dispatcher($dispatcherModel, $errorMessage);

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