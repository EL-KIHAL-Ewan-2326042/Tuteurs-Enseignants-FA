<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Database;
use PDO;

class Dispatcher {
    /**
     *
     * @return void
     */

    public function association($db): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Student_number']) && isset($_POST['Id_teacher']) && isset($_POST['Start_date']) && isset($_POST['End_date'])
            && $_POST['Student_number'] !== '' && $_POST['Id_teacher'] !== '' && $_POST['Start_date'] !== '' && $_POST['End_date'] !== '') {
            $query = 'SELECT Teacher.Id_teacher FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                    where Department_name = :Role_department';
            $stmt = $db->getConn()->prepare($query);
            $stmt->bindParam(':Role_department', $_SESSION['role_department']);
            $stmt->execute();
            $listTeacher = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $query = 'SELECT Student.Student_number FROM Student JOIN Study_at ON Student.Student_number = Study_at.Student_number
                    where Department_name = :Role_department';
            $stmt = $db->getConn()->prepare($query);
            $stmt->bindParam(':Role_department', $_SESSION['role_department']);
            $stmt->execute();
            $listStudent = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $query = 'SELECT Is_responsible.Student_number, Is_responsible.Id_teacher FROM Is_responsible JOIN Study_at ON Is_responsible.Student_number = Study_at.Student_number
                    where Department_name = :Role_department';
            $stmt = $db->getConn()->prepare($query);
            $stmt->bindParam(':Role_department', $_SESSION['role_department']);
            $stmt->execute();
            $listAssociate = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $_POST['Start_date']) && preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $_POST['Start_date'])) {
                if (in_array($_POST['Id_teacher'], $listTeacher) && in_array($_POST['Student_number'], $listStudent)){
                    print_r($listAssociate);
                    print_r([$_POST['Id_teacher'], $_POST['Student_number']]);
                    if (!(in_array([$_POST['Id_teacher'], $_POST['Student_number']], $listAssociate))) {

                        $query = 'INSERT INTO Is_responsible (Id_teacher, Student_number, responsible_start_date, responsible_end_date) VALUES (:Id_teacher, :Student_number, :Start_date, :End_date)';
                        $stmt = $db->getConn()->prepare($query);
                        $stmt->bindParam(':Student_number', $_POST['Student_number']);
                        $stmt->bindParam(':Id_teacher', $_POST['Id_teacher']);
                        $stmt->bindParam(':Start_date', $_POST['Start_date']);
                        $stmt->bindParam(':End_date', $_POST['End_date']);
                        $stmt->execute();
                        return "Association " . $_POST['Id_teacher'] . " et " . $_POST['Student_number'] . " enregistré.";
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
        $dispatcherModel = new \Blog\Models\Dispatcher($db);
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Student_number']) && isset($_POST['Id_teacher']) && isset($_POST['Start_date']) && isset($_POST['End_date'])){
            $errorMessage = $this->association($db);
        }

        $db = Database::getInstance();
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