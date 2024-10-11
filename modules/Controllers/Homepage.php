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

            if (isset($_POST['search'])) {
                $results = $homepageModel->correspondTerms();

                header('Content-Type: application/json');
                echo json_encode($results);
                return;
            }

            if (isset($_POST['student_id']) && isset($_POST['student_name'])) {
                $studentId = $_POST['student_id'];
                $studentName = $_POST['student_name'];

                $_SESSION['selected_student'] = ['id' => $studentId, 'name' => $studentName];

                echo "Étudiant sélectionné : " . htmlspecialchars($studentName);
                return;
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
