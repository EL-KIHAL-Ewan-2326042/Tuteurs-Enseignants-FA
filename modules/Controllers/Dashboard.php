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
        $cssFilePath = '_assets/styles/dashboard.css';
        $jsFilePath = '';

        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['csv_file'])) {
            $csvFile = $_FILES['csv_file']['tmp_name'];
            $model = new \Blog\Models\Dashboard(\Database::getInstance());
            $model->uploadCsv($csvFile);
        }

        $view = new \Blog\Views\Dashboard();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}