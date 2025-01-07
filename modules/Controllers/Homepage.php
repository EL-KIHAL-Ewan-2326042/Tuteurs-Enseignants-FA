<?php
namespace Blog\Controllers;

use Blog\Views\layout\Layout;
use Includes\Database;

class Homepage {
    private Layout $layout;

    /**
     * Constructeur de la classe Homepage (controller)
     * @param Layout $layout Instance de la classe Layout
     */
    public function __construct(Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * Controlleur de la homepage.
     * Elle gere des requetes post, via le model, pour recuperer des informations
     * tels que les resultats de recherche ou les informations de l'etudiant selectione
     * @return void
     */
    public function show(): void {

        if (!isset($_SESSION['identifier'])) {
            header('Location: /intramu');
            return;
        }

        $db = Database::getInstance();
        $globalModel = new \Blog\Models\GlobalModel($db);
        $homepageModel = new \Blog\Models\Homepage($db, $globalModel);
        $view = new \Blog\Views\homepage\Homepage($homepageModel, $globalModel);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'search' && isset($_POST['search'])) {
                    $results = $homepageModel->correspondTerms();
                    header('Content-Type: application/json');
                    echo json_encode($results);
                    return;
                }

                if ($_POST['action'] === 'select_student' && isset($_POST['student_id']) && isset($_POST['student_firstName']) && isset($_POST['student_lastName'])) {
                    $studentId = $_POST['student_id'];
                    $firstName = $_POST['student_firstName'];
                    $secondName = $_POST['student_lastName'];
                    $address = $homepageModel->getStudentAddress($studentId);

                    $_SESSION['selected_student'] = [
                        'id' => $studentId,
                        'firstName' => $firstName,
                        'lastName' => $secondName,
                        'address' => $address
                    ];
                }
            }

            if (isset($_POST['durationMin'])) {
                echo 'ici';
            }
        }

        $title = "Accueil";
        $cssFilePath = '/_assets/styles/homepage.css';
        $jsFilePath = '/_assets/scripts/homepage.js';

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}