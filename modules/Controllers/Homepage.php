<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Database;

class Homepage {

    /**
     * Controlleur de la homepage
     * @return void
     */
    public function show(): void {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance();
            $homepageModel = new \Blog\Models\Homepage($db);

            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'search' && isset($_POST['search'])) {
                    $results = $homepageModel->correspondTerms();
                    header('Content-Type: application/json');
                    echo json_encode($results);
                    return;
                }
            }

            if ($_POST['action'] === 'select_student' && isset($_POST['student_id']) && isset($_POST['student_firstName']) && isset($_POST['student_lastName'])) {
                $studentId = $_POST['student_id'];
                $firstName = $_POST['student_firstName'];
                $secondName = $_POST['student_lastName'];

                $_SESSION['selected_student'] = [
                    'id' => $studentId,
                    'firstName' => $firstName,
                    'lastName' => $secondName
                ];
            }
        }

        $title = "Accueil";
        $cssFilePath = '/_assets/styles/homepage.css';
        $jsFilePath = '/_assets/scripts/homepage.js';

        $view = new \Blog\Views\Homepage();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}
