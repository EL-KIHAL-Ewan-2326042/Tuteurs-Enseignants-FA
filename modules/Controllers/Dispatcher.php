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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_number']) && isset($_POST['id_teacher']) && isset($_POST['start_date']) && isset($_POST['end_date'])
            && $_POST['student_number'] !== '' && $_POST['id_teacher'] !== '' && $_POST['start_date'] !== '' && $_POST['end_date'] !== '') {

            $listTeacher = $dispatcherModel->createListTeacher();
            $listStudent = $dispatcherModel->createListStudent();
            $listAssociate = $dispatcherModel->createListAssociate();


            if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $_POST['start_date']) && preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $_POST['start_date'])) {
                if (in_array($_POST['id_teacher'], $listTeacher) && in_array($_POST['student_number'], $listStudent)){
                    print_r($listAssociate);
                    print_r([$_POST['id_teacher'], $_POST['student_number']]);
                    if (!(in_array([$_POST['id_teacher'], $_POST['student_number']], $listAssociate))) {
                        return $dispatcherModel->msgSave();
                    }
                    else {
                        return "Cette association existe déjà";
                    }
                }
                else {
                    return "Student_number ou Id_Teacher inexistant dans ce departement";
                }
            } else {
                return "Date format non valide (format YYYY-MM-DD)";
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
            return "Merci de remplir tout les champs";
        }
        return '';
    }

    public function show(): void {
        $db = Database::getInstance();
        $globalModel = new \Blog\Models\GlobalModel($db);
        $dispatcherModel = new \Blog\Models\Dispatcher($db, $globalModel);
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['student_number']) && isset($_POST['id_teacher']) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
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
}