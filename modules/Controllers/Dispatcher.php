<?php

namespace Blog\Controllers;

use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Student;
use Blog\Models\Teacher;
use Blog\Models\User;
use Blog\Views\layout\Layout;
use includes\Database;

class Dispatcher
{
    public function show(): void
    {
        if (isset($_SESSION['role_name']) && ((is_array($_SESSION['role_name']) && in_array('Admin_dep', $_SESSION['role_name'])) || ($_SESSION['role_name'] === 'Admin_dep'))) {
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

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['associateTeachers'])) {
                $internshipId = $_POST['internship_id'] ?? null;
                if ($internshipId) {
                    $tmpmessage = $this->associateTeachers($teacherModel, $internshipModel, $internshipId);
                    $errorMessageAfterSort = $tmpmessage[0];
                    $checkMessageAfterSort = $tmpmessage[1];
                }
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

    private function associateTeachers(Teacher $teacherModel, Internship $internshipModel, string $internshipId): array
    {
        $returnErrorMessage = '';
        $returnCheckMessage = '';

        if (isset($_POST['listTupleAssociate'])) {
            foreach ($_POST['listTupleAssociate'] as $tupleAssociate) {
                $tmp = explode("$", $tupleAssociate);
                $teacher = $tmp[0];
                $internship = $tmp[1];
                $score = floatval($tmp[2]);

                if ($internshipModel->insertIsResponsible($teacher, $internship, $score)) {
                    $returnCheckMessage .= "Association réussie pour l'enseignant $teacher et le stage $internship.<br>";
                } else {
                    $returnErrorMessage .= "Échec de l'association pour l'enseignant $teacher et le stage $internship.<br>";
                }
            }
        } else {
            $returnErrorMessage = "Aucune donnée d'association soumise.";
        }

        return [$returnErrorMessage, $returnCheckMessage];
    }
}
