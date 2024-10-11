<?php

namespace Blog\Controllers;

use Blog\Views\Layout;

class Dashboard {
    /**
     * Controlleur de la dashboard
     * @return void
     */
    public function show(): void {

        $title = "Dashboard";
        $cssFilePath = '';
        $jsFilePath = '';

        $view = new \Blog\Views\Dashboard();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }


    /**
     * Controlleur de la gestion de l'importation du fichier CSV
     * @return void
     */
    public function uploadCsv(): void {
        if (isset($_POST['submit']) && isset($_FILES['csv_file'])) {
            $db = Database::getInstance();
            $csvFilePath = $_FILES['csv_file']['tmp_name'];

            // appel modele
            $uploadModel = new \Blog\Models\Dashboard($db);
            if($uploadModel->uploadCsv($csvFilePath)) {
                echo "Le fichier CSV a été importé avec succès.";
            } else {
                echo "Erreur lors de l'imporation du fichier CSV.";
            }
        }
    }
}